<?php

namespace App\Services\Clone;

use App\Card;
use App\Course;
use App\Enrollment;
use App\Enums\CardBox;
use App\Enums\EnrollmentRole;
use App\Enums\FileStatus;
use App\Enums\StateType;
use App\Exceptions\CloneException;
use App\Folder;
use App\Services\FileStorageService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CloneCardService
{
    private Card $card;

    public function __construct(Card $card)
    {
        $this->card = $card;
    }

    /**
     * Check if the card can be cloned.
     *
     * Same params than CloneService::clone().
     *
     * @throws CloneException If the card cannot be cloned.
     */
    public function checkClone(
        ?Folder $destFolder = null,
        ?Course $destCourse = null,
    ): void {
        $cloneInAnotherSpace = (false
            || $destFolder && $destFolder->course->id !== $this->card->course->id
            || $destCourse && $destCourse->id !== $this->card->course->id
        );
        if ($cloneInAnotherSpace) {
            // Check that the source file is ready (not transcoding).
            if ($this->card->file && $this->card->file->status !== FileStatus::Ready) {
                throw new CloneException(
                    trans('courses.finder.clone_in.error'),
                );
            }
        }
    }

    /**
     * Clone this card.
     *
     * All attachments will be cloned as well (but not regular file).
     *
     * When cloned in a new course, all files and tags will be cloned.
     *
     * @param  Folder|null  $destFolder  The new parent folder. Null if the card
     *                                   should be cloned in the same parent folder.
     * @param  Course|null  $destCourse  The new course. Null if the card should be
     *                                   cloned in the same course.
     *
     * @throws InvalidArgumentException|CloneException If both $destFolder and $destCourse are
     *                                                 specified.
     */
    public function clone(
        ?Folder $destFolder = null,
        ?Course $destCourse = null,
    ): ?Card {
        $fileStorageService = new FileStorageService;

        $this->checkClone($destFolder, $destCourse);

        // Can specify only one of these attribute (course will be deduced from
        // folder if specified).
        if ($destFolder && $destCourse) {
            throw new InvalidArgumentException(
                'Cannot specify $destFolder and $destCourse at the same time.',
            );
        }

        if ($destCourse && $destCourse->id === $this->card->course->id) {
            $destCourse = null;
        }

        DB::beginTransaction();
        $values = [];
        if ($destCourse) {
            $values = [
                'course_id' => $destCourse->id,
                'folder_id' => null,
                'state_id' => $destCourse
                    ->states
                    ->where('type', StateType::Private)
                    ->first()
                    ->id,
            ];
        } elseif ($destFolder) {
            $values = [
                'course_id' => $destFolder->course->id,
                'folder_id' => $destFolder->id,
            ];

            if ($destFolder->course->id !== $this->card->course->id) {
                $values['state_id'] = $destFolder
                    ->course
                    ->states
                    ->where('type', StateType::Private)
                    ->first()
                    ->id;
            }
        } else {
            $copyLabel = trans('courses.finder.copy');
            $values = [
                'title' => "{$this->card->title} ($copyLabel)",
            ];
        }

        $copiedCard = $this->card->replicate(['position'])->fill($values);
        $copiedCard->save();
        $copiedCard->refresh();
        $destCourse = $copiedCard->course;

        $failed = false;
        $files = collect([]);

        // Is the card copied in another course?
        if ($copiedCard->course->id !== $this->card->course->id) {
            $existingNamesInDest = $destCourse->tags()->pluck('name');

            // Create tags that don't already exists in the destination course.
            $destCourse->tags()->createMany(
                collect($this->card->tags->toArray())
                    ->filter(fn ($tag) => ! $existingNamesInDest->contains($tag['name']))
                    ->toArray(),
            );

            // Attach tags to the new card.
            $destCourse
                ->tags
                ->filter(fn ($tag) => $this->card->tags->pluck('name')->contains($tag->name))
                ->each(fn ($tag) => $tag->cards()->attach($copiedCard->id));

            // Copy source file.
            static $alreadyCopiedFiles = [];
            if ($this->card->file) {
                if (array_key_exists($this->card->file->id, $alreadyCopiedFiles)) {
                    // Only copy source file once. Avoid having multiple copies of
                    // sources files when cloning multiple cards that have the same
                    // source file. Can still happen when cloning files from
                    // multiple requests.
                    $copiedCard->file_id = $alreadyCopiedFiles[$this->card->file->id];
                    $copiedCard->save();
                } else {
                    $copiedSourceFile = $fileStorageService->clone(
                        $this->card->file,
                    );

                    if ($copiedSourceFile) {
                        $files->push($copiedSourceFile);
                        $copiedSourceFile->course_id = $copiedCard->course->id;
                        $copiedSourceFile->save();
                        $copiedCard->file_id = $copiedSourceFile->id;
                        $copiedCard->save();

                        $alreadyCopiedFiles[$this->card->file->id] = $copiedSourceFile->id;
                    } else {
                        $failed = true;
                    }
                }
            }

            // Add holder.
            $currentUser = auth()->user();
            $enrollments = Enrollment::where('course_id', $destCourse->id)
                ->where('user_id', $currentUser->id)
                ->where('role', EnrollmentRole::Manager);

            // Current user becomes an holder of the new card. If the enrollment
            // exists and if the user is not an admin.
            if ($enrollments->exists() && ! $currentUser->admin) {
                $enrollments->first()->addCard($copiedCard);
            }
        } else {
            // Attach tags.
            $copiedCard->tags()->attach($this->card->tags->pluck('id'));

            // Attach holders.
            $this->card->enrollments()->each(
                fn ($enrollment) => $enrollment->addCard($copiedCard),
            );
        }

        // Remove base64 images from the card box3 & box4 content.
        $copiedCard->getBoxesContent([CardBox::Box3, CardBox::Box4])->each(
            function ($box) use ($copiedCard) {
                $box['content'] = preg_replace(
                    '/src="data:image\/[^;]+;base64,[^"]+"/',
                    'src="" alt="'.trans('messages.card.no.b64').'"',
                    $box['content'],
                );
                $copiedCard->{$box['name']} = $box['content'];

                $copiedCard->save();
            }
        );

        // Clone attachments.
        $this->card->attachments()->each(
            function ($attachment) use ($fileStorageService, $copiedCard, $files, $failed) {
                if ($failed) {
                    return;
                }

                $copiedFile = $fileStorageService->clone($attachment);

                if ($copiedFile) {
                    $files->push($copiedFile);
                    $copiedFile->card_id = $copiedCard->id;
                    $copiedFile->course_id = $copiedCard->course->id;
                    $copiedFile->save();
                } else {
                    $failed = true;
                }
            }
        );

        if ($failed) {
            $files->each(fn ($file) => $file->forceDelete());
            DB::rollBack();

            return null;
        } else {
            DB::commit();

            return $copiedCard;
        }
    }
}
