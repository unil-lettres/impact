<?php

namespace App\Services\Clone;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\Exceptions\CloneException;
use App\Folder;
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
        Folder $destFolder = null,
        Course $destCourse = null,
    ): void {
        $cloneInAnotherSpace = (false
            || $destFolder && $destFolder->course->id !== $this->card->course->id
            || $destCourse && $destCourse->id !== $this->card->course->id
        );
        if ($cloneInAnotherSpace) {
            // Check that the source file is ready (not transcoding).
            if ($this->card->file->status !== FileStatus::Ready) {
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
     * @param  Folder|null  $destFolder The new parent folder. Null if the card
     * should be cloned in the same parent folder.
     * @param  Course|null  $course The new course. Null if the card should be
     * cloned in the same course.
     *
     * @throws InvalidArgumentException If both $destFolder and $destCourse are
     * specified.
     */
    public function clone(
        Folder $destFolder = null,
        Course $destCourse = null,
    ): void {
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
            ];
        } elseif ($destFolder) {
            $values = [
                'course_id' => $destFolder->course->id,
                'folder_id' => $destFolder->id,
            ];
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

            // Create tags that don't already exists in the destination course
            // and attach them to the card.
            $copiedCard->tags()->createMany(
                collect($this->card->tags->toArray())
                    ->filter(fn ($tag) => ! $existingNamesInDest->contains($tag['name']))
                    ->map(
                        function ($tag) use ($destCourse) {
                            $tag['course_id'] = $destCourse->id;

                            return $tag;
                        },
                    )
                    ->toArray(),
            );

            // Attach tags that already exists in the destination course to the
            // card.
            $destCourse
                ->tags
                ->filter(fn ($tag) => $existingNamesInDest->contains($tag->name))
                ->each(fn ($tag) => $tag->cards()->attach($copiedCard->id));

            // Copy source file.
            static $alreadyCopiedFiles = [];
            if (array_key_exists($this->card->file->id, $alreadyCopiedFiles)) {
                // Only copy source file once. Avoid having multiple copies of
                // sources files when cloning multiple cards that have the same
                // source file. Can still happen when cloning files from
                // multiple requests.
                $copiedCard->file_id = $alreadyCopiedFiles[$this->card->file->id];
                $copiedCard->save();
            } else {
                $copiedSourceFile = (new CloneFileService(
                    $this->card->file,
                ))->clone($copiedCard->id);

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
        } else {
            // Attach tags.
            $copiedCard->tags()->attach($this->card->tags->pluck('id'));

            // Attach editors.
            $this->card->enrollments()->each(
                fn ($enrollment) => $enrollment->addCard($copiedCard),
            );
        }

        // Clone attachments.
        $this->card->attachments()->each(
            function ($attachment) use ($copiedCard, $files, $failed) {
                if ($failed) {
                    return;
                }

                $copiedFile = (new CloneFileService(
                    $attachment,
                ))->clone($copiedCard->id);

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
        } else {
            DB::commit();
        }
    }
}
