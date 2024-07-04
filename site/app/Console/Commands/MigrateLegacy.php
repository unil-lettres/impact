<?php

namespace App\Console\Commands;

use App\Card;
use App\Course;
use App\Enrollment;
use App\Enums\CourseType;
use App\Enums\EnrollmentRole;
use App\Enums\FileStatus;
use App\Enums\StatePermission;
use App\Enums\StateType;
use App\Enums\StoragePath;
use App\Enums\TranscriptionType;
use App\Enums\UserType;
use App\File;
use App\Folder;
use App\Services\FileStorageService;
use App\State;
use App\Tag;
use App\User;
use Assert\Assert;
use FFMpeg\FFProbe;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDO;

class MigrateLegacy extends Command
{
    const LEGACY_PRIVATE_ID = 0;

    const LEGACY_ARCHIVED_ID = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the legacy database and files to the new one.';

    /**
     * @var PDO|null Legacy database connection.
     */
    protected ?PDO $legacyConnection = null;

    /**
     * @var Collection<string, Collection> Map of legacy IDs to new IDs.
     */
    protected ?Collection $mapIds = null;

    protected FileStorageService $fileStorageService;

    protected Filesystem $legacyDisk;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $this->fileStorageService = new FileStorageService();

        // TODO uncomment
        // if ($this->confirm('Please confirm that you have checked that the transcription algorithm is correct (must reflect the one in Transcription.js).', false) === false) {
        //     $this->info('Aborting...');
        //     return 0;
        // }

        // $this->warn('Continue will ERASE the current database and all uploaded files. Make sure you have a backup of the current database and files before continuing.');
        // if ($this->confirm('Do you want to continue?', false) === false) {
        //     $this->info('Aborting...');
        //     return 0;
        // }

        // $invalidateMail = $this->confirm('Do you want to invalidate email adress? For testing purpose only.', true);
        $invalidateMail = true;

        $this->mapIds = collect([]);

        $this->prepareLegacyConnection();

        // TODO uncomment
        // $legacyStoragePath = $this->askNotNull('storage folder absolute path');

        // TODO bind dans le docker pour test
        $legacyStoragePath = '/Users/dmiserez/Downloads/impact_migration/legacy-data';

        $this->legacyDisk = Storage::build([
            'driver' => 'local',
            'root' => $legacyStoragePath,
        ]);

        $this->wipeDatabase();

        // TODO REMOVE
        User::create([
            'name' => 'Admin user',
            'email' => 'admin-user@example.com',
            'password' => Hash::make('password'),
            'admin' => true,
        ])->id;

        $this->migrateUsers($invalidateMail);
        $this->migrateCourses();
        $this->migrateFolders();
        $this->migrateStates();
        $this->migrateCards();
        $this->migrateEnrollments();
        $this->migrateTags();
        $this->migrateFiles();
        $this->migrateAttachments();

        $this->info('Migration complete');
    }

    protected function prepareLegacyConnection(): void
    {
        // TODO uncomment
        // $dbHost = $this->askNotNull('legacy database host');
        // $dbName = $this->askNotNull('legacy database name');
        // $dbPort = $this->askNotNull('legacy database port', 3306);
        // $dbCharset = $this->askNotNull('legacy database charset', 'utf8mb4');
        // $dbUsername = $this->askNotNull('legacy database username');
        // $dbPassword = $this->askNotNull('legacy database password');
        $dbHost = 'impact-mysql';
        $dbName = 'impact_tmp';
        $dbCharset = 'utf8mb4';
        $dbPort = 3306;
        $dbUsername = 'root';
        $dbPassword = 'root';

        try {
            $this->legacyConnection = new PDO(
                "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=$dbCharset",
                $dbUsername,
                $dbPassword,
            );
        } catch (\PDOException $e) {
            $this->error('Could not connect to the legacy database.');
            $this->error($e->getMessage());
            exit(1);
        }
    }

    protected function wipeDatabase(): void
    {
        $this->info('Wipe current database...');
        Artisan::call('db:wipe');

        $this->info('Running migrations...');
        Artisan::call('migrate:fresh');
    }

    protected function migrateUsers(bool $invalidateMail): void
    {
        $this->info('Migrating users...');

        $this->mapIds->put('users', collect([]));

        $result = $this->legacyConnection->query('SELECT * FROM users');

        $warns = [];

        $this->withProgressBar(
            $result->fetchAll(),
            function ($legacyUser) use (&$warns, $invalidateMail) {

                $fullName = [];

                if (! empty($legacyUser['first_name'])) {
                    $fullName[] = $legacyUser['first_name'];
                }
                if (! empty($legacyUser['last_name'])) {
                    $fullName[] = $legacyUser['last_name'];
                }
                if (count($fullName) < 2) {
                    $warns[] = "User legacy id {$legacyUser['id']} misses a lastname, firstname or both.";
                }
                $fullName = implode(' ', $fullName);

                $user = User::create([
                    'name' => $fullName,

                    'email' => $invalidateMail
                        ? $legacyUser['email'].'@lettres-tst.ch'
                        : $legacyUser['email'],

                    'password' => $legacyUser['password'],
                    'type' => $legacyUser['password'] ? UserType::Local : UserType::Aai,
                    'admin' => $legacyUser['is_superuser'] === 1,
                    'creator_id' => null,
                    'validity' => now()->addYears(1),
                ]);

                $this->mapIds->get('users')->put($legacyUser['id'], $user->id);
            },
        );
        $this->newLine();

        foreach ($warns as $warn) {
            $this->warn($warn);
        }

        $this->info('Users migrations complete.');
    }

    protected function migrateCourses(): void
    {
        $this->info('Migrating courses...');

        $this->mapIds->put('courses', collect([]));

        $result = $this->legacyConnection->query('SELECT * FROM courses');

        $this->withProgressBar(
            $result->fetchAll(),
            function ($courseLegacy) {
                Assert::that($courseLegacy['box2_type'])->inArray(['text', 'trans']);

                $course = Course::create([
                    'name' => $courseLegacy['name'],
                    'description' => $courseLegacy['description'],
                    'type' => $courseLegacy['moodleid'] === 0 ? CourseType::Local : CourseType::External,
                    'external_id' => $courseLegacy['moodleid'] === 0 ? null : $courseLegacy['moodleid'],
                    'transcription' => $courseLegacy['box2_type'] === 'text' ? TranscriptionType::Text : TranscriptionType::Icor,
                    'legacy_id' => $courseLegacy['id'],
                ]);

                // Remove all custom states from the course, they will be
                // imported from the legacy data.
                $course->states()->where('type', StateType::Custom)->forceDelete();

                // Column deleted_at is not fillable.
                $course->deleted_at = $courseLegacy['to_delete_time'];
                $course->save();

                $this->mapIds->get('courses')->put($courseLegacy['id'], $course->id);
            },
        );
        $this->newLine();
        $this->info('Courses migrations complete.');
    }

    protected function migrateCards(): void
    {
        $this->info('Migrating cards...');

        $this->mapIds->put('cards', collect([]));

        $result = $this->legacyConnection->query('SELECT * FROM cards');

        $this->withProgressBar(
            $result->fetchAll(),
            function ($cardLegacy) {
                $course_id = $this->mapIds
                    ->get('courses')
                    ->get($cardLegacy['course_id']);

                $course = Course::withTrashed()->findOrFail($course_id);

                // As they are only one state private and archived in legacy for all
                // the courses but one per courses in the new system, we need to
                // find corresponding private / archived state for each courses.
                $state = match ($cardLegacy['state_id']) {

                    self::LEGACY_PRIVATE_ID => $course
                        ->states()
                        ->where('type', StateType::Private)->firstOrFail(),

                    self::LEGACY_ARCHIVED_ID => $course
                        ->states()
                        ->where('type', StateType::Archived)->firstOrFail(),

                    default => State::withTrashed()->findOrFail(
                        $this->mapIds
                            ->get('states')
                            ->get($cardLegacy['state_id'])
                    ),
                };

                $card = Card::create([
                    'title' => $cardLegacy['title'],
                    'box2->version' => 1,

                    'box2->text' => $course->transcription === TranscriptionType::Text
                        ? $cardLegacy['transcript']
                        : null,

                    'box2->icor' => $course->transcription === TranscriptionType::Icor
                        ? $this->parseTranscription($cardLegacy['transcript'] ?? '')
                        : null,

                    'box3' => $cardLegacy['text_1'],
                    'box4' => $cardLegacy['text_2'],
                    'course_id' => $course_id,

                    'folder_id' => $this->mapIds
                        ->get('folders')
                        ->get($cardLegacy['folder_id']),

                    'state_id' => $state->id,
                    'options->no_emails' => $cardLegacy['emails_disabled'] === 1,
                    'options->presentation_date' => $cardLegacy['presentation_date'],
                    'options->box1->end' => $cardLegacy['video_end'],
                    'options->box1->hidden' => $cardLegacy['video_hidden'] === 1,
                    'options->box1->start' => $cardLegacy['video_start'],
                    'options->box2->hidden' => $cardLegacy['transcript_hidden'] === 1,
                    'options->box2->sync' => $cardLegacy['transcript_sync_video'] === 1,
                    'options->box3->fixed' => $cardLegacy['text_1_height_fixed'] === 1,
                    'options->box3->hidden' => $cardLegacy['text_1_hidden'] === 1,
                    'options->box3->title' => $cardLegacy['text_1_title'],
                    'options->box4->fixed' => $cardLegacy['text_2_height_fixed'] === 1,
                    'options->box4->hidden' => $cardLegacy['text_2_hidden'] === 1,
                    'options->box4->title' => $cardLegacy['text_2_title'],
                    'options->box5->hidden' => $cardLegacy['attachments_hidden'] === 1,
                    'position' => $cardLegacy['position'],
                    'legacy_id' => $cardLegacy['id'],
                ]);

                $this->mapIds->get('cards')->put($cardLegacy['id'], $card->id);
            },
        );
        $this->newLine();
        $this->info('Cards migrations complete.');
    }

    protected function migrateFolders(): void
    {
        $this->info('Migrating folders...');

        $this->mapIds->put('folders', collect([]));

        $result = $this->legacyConnection->query('SELECT * FROM folders');

        $folders = $this->withProgressBar(
            $result->fetchAll(),
            function ($folderLegacy) {
                $folder = Folder::create([
                    'title' => $folderLegacy['title'],
                    'position' => $folderLegacy['position'],

                    'course_id' => $this->mapIds
                        ->get('courses')
                        ->get($folderLegacy['course_id']),

                    'legacy_id' => $folderLegacy['id'],
                ]);

                $this->mapIds->get('folders')->put($folderLegacy['id'], $folder->id);
            },
        );
        $this->newLine();

        $this->info('Associating folder\'s parent...');
        $this->withProgressBar(
            $folders,
            function ($folderLegacy) {
                $folder = Folder::find(
                    $this->mapIds->get('folders')->get($folderLegacy['id']),
                );

                $folder->parent_id = $this->mapIds
                    ->get('folders')
                    ->get($folderLegacy['parent_id']);

                $folder->save();
            },
        );
        $this->newLine();

        $this->info('Folders migrations complete.');
    }

    protected function migrateStates(): void
    {
        $this->info('Migrating states...');

        $this->mapIds->put('states', collect([]));

        $result = $this->legacyConnection->query(<<<'SQL'
            SELECT *
            FROM states
            LEFT JOIN actions ON actions.state_id = states.id
            WHERE actions.discr = 'email'
            GROUP BY states.id
            HAVING COUNT(states.id) > 1
        SQL);

        if (count($result->fetchAll()) > 0) {
            $this->error('Some states have multiple email actions. Please check and clean data.');
            $this->error('Aborting...');
            exit(1);
        }

        $result = $this->legacyConnection->query(<<<'SQL'
            SELECT
                states.*,
                actions.params
            FROM states
            LEFT JOIN actions ON TRUE
                AND actions.state_id = states.id
                AND actions.discr = 'email'
        SQL);

        $this->withProgressBar(
            $result->fetchAll(),
            function ($stateLegacy) {

                // Do not import private and archived states. They are not
                // implemented the same way on the new system (they are now
                // automatically created when courses are created).
                if (
                    in_array(
                        $stateLegacy['id'],
                        [self::LEGACY_PRIVATE_ID, self::LEGACY_ARCHIVED_ID],
                    )
                ) {
                    return;
                }

                $mapPermissions = [
                    'wl-' => StatePermission::HoldersCanShowAndEdit,
                    'w--' => StatePermission::HoldersCanShowAndEdit,
                    'ww-' => StatePermission::ManagersAndHoldersCanShowAndEdit,
                    'wwr' => StatePermission::AllCanShowManagersAndHoldersCanEdit,
                    'rwr' => StatePermission::AllCanShowManagersCanEdit,
                    '-w-' => StatePermission::ManagersCanShowAndEdit,
                ];

                $action_data = [];
                if (isset($stateLegacy['params'])) {
                    [$subject, $body] = mb_split('\n', $stateLegacy['params'], 2);
                    $body = str_replace('%titre', '{{title}}', $body);

                    // Remove the <> when exists because it do not display correctly in HTML email.
                    $body = str_replace('<%url>', '{{url}}', $body);
                    $body = str_replace('%url', '{{url}}', $body);

                    $action_data = [
                        'actions->data' => [
                            State::buildEmailAction($subject, $body),
                        ],
                    ];
                }

                $state = State::create([

                    'course_id' => $this->mapIds
                        ->get('courses')
                        ->get($stateLegacy['course_id']),

                    'name' => $stateLegacy['label'],
                    'position' => $stateLegacy['position'],
                    'description' => $stateLegacy['description'],
                    'managers_only' => $stateLegacy['for_teachers_only'] === 1,
                    'type' => StateType::Custom,
                    'permissions->box1' => $mapPermissions[$stateLegacy['box1_perms']],
                    'permissions->box2' => $mapPermissions[$stateLegacy['box2_perms']],
                    'permissions->box3' => $mapPermissions[$stateLegacy['box3_perms']],
                    'permissions->box4' => $mapPermissions[$stateLegacy['box4_perms']],
                    'permissions->box5' => $mapPermissions[$stateLegacy['box5_perms']],
                    ...$action_data,
                ]);

                $this->mapIds
                    ->get('states')
                    ->put($stateLegacy['id'], $state->id);
            },
        );
        $this->newLine();
        $this->info('Courses migrations complete.');
    }

    protected function migrateEnrollments(): void
    {
        $this->info('Migrating enrollments...');

        $result = $this->legacyConnection->query('SELECT * FROM enrollments');

        $mapStudentCourseToCards = $this->mapStudentCourseToCards();

        $this->withProgressBar(
            $result->fetchAll(),
            function ($courseLegacy) use ($mapStudentCourseToCards) {

                Assert::that($courseLegacy['role'])
                    ->inArray(['teacher', 'student']);

                $legacyCourseId = $courseLegacy['course_id'];
                $legacyUserId = $courseLegacy['user_id'];

                Enrollment::create([
                    'role' => $courseLegacy['role'] === 'teacher'
                        ? EnrollmentRole::Manager
                        : EnrollmentRole::Member,

                    'course_id' => $this->mapIds
                        ->get('courses')
                        ->get($legacyCourseId),

                    'user_id' => $this->mapIds
                        ->get('users')
                        ->get($legacyUserId),

                    'cards' => $mapStudentCourseToCards[$legacyUserId][$legacyCourseId] ?? null,
                ]);

            },
        );
        $this->newLine();

        $this->info('Enrollments migrations complete.');
    }

    protected function migrateTags(): void
    {
        $this->info('Migrating tags...');

        $result = $this->legacyConnection->query(<<<'SQL'
            SELECT
                cards.id AS card_id,
                course_id,
                cards.tags AS card_tags,
                courses.tags AS course_tags
            FROM
                cards
                INNER JOIN courses ON courses.id = cards.course_id
        SQL);

        $map = [];
        foreach ($result->fetchAll() as $item) {
            $map[$item['course_id']]['id'] = $item['course_id'];

            $map[$item['course_id']]['tags'] = explode(
                ',',
                $item['course_tags'],
            );

            $map[$item['course_id']]['cards'][$item['card_id']] = explode(
                ',',
                $item['card_tags'],
            );
        }

        $this->withProgressBar(
            $map,
            function ($course) {
                $newCourseId = $this->mapIds->get('courses')->get($course['id']);

                foreach ($course['tags'] as $tagCourse) {
                    if (! empty($tagCourse)) {
                        Tag::create([
                            'name' => $tagCourse,
                            'course_id' => $newCourseId,
                        ]);
                    }
                }

                foreach ($course['cards'] as $cardId => $cardTags) {
                    $newCardId = $this->mapIds->get('cards')->get($cardId);

                    foreach ($cardTags as $tagCard) {
                        if (! empty($tagCard)) {
                            $tag = Tag::firstOrCreate([
                                'name' => $tagCard,
                                'course_id' => $newCourseId,
                            ]);
                            Card::findOrFail($newCardId)->tags()->attach($tag);
                        }
                    }
                }
            },
        );
        $this->newLine();

        $this->info('Tags migrations complete.');
    }

    protected function migrateFiles(): void
    {
        $this->info('Migrating files...');

        $result = $this->legacyConnection->query(<<<'SQL'
            SELECT
                cards.id,
                cards.course_id,
                video_url,

                -- TODO valider avec des données de prod que cette statement est correcte.
                -- Not null or empty means that transcoding is in error or pending.
                video_upload_state
            FROM
                cards
        SQL);

        $this->withProgressBar(
            $result->fetchAll(),
            function ($legacyCard) {
                if (! empty($legacyCard['video_upload_state'])) {
                    $this->warn("Skipping media for card legacy id {$legacyCard['id']}.");
                    $this->warn("Transcoded state is '{$legacyCard['video_upload_state']}'");

                    return;
                }

                $card = Card::findOrFail(
                    $this->mapIds->get('cards')->get($legacyCard['id']),
                );

                $videoUrl = $legacyCard['video_url'];

                if (empty($videoUrl)) {
                    return;
                }

                $parsedVideoUrl = $this->parseFileUrl($videoUrl);

                if (is_null($parsedVideoUrl)) {

                    // If file_name could not be parsed but exists, it means
                    // its an external link.
                    if (! empty($videoUrl)) {
                        $card->options->box1->link = $videoUrl;
                        $card->save();
                        $card->refresh();
                    }

                    return;
                }
                [$legacyCourseId, $filename] = $parsedVideoUrl;
                Assert::that($legacyCourseId)->eq($legacyCard['course_id']);

                $fileName = $this->fileStorageService->getFileName($filename);

                // TODO vérifier si certain fichier sont nommés "xxx.mp4.mp4",
                // dans ce cas, il faut tj appondre .mp4 meme si getExtension retourne une extension.
                // donc remplacer la ligne ci-dessous par: $extension = ".mp4".
                $extension = $this->fileStorageService->getExtension($filename) ?: 'mp4';

                // TODO most url in the db don't have extension. see if they
                // have an extension on the server filename.
                $legacyPath = "media/$legacyCourseId/$filename.$extension";

                if ($this->legacyDisk->missing($legacyPath)) {
                    $this->warn("Skipping file for card legacy id {$legacyCard['id']}.");
                    $this->warn("File '$legacyPath' does not exist.");

                    return;
                }

                $newFileName = $this->generateNewFileName($extension);
                $newPath = StoragePath::UploadStandard."/$newFileName";

                $success = Storage::disk('public')->put(
                    $newPath,
                    $this->legacyDisk->get($legacyPath)
                );

                Assert::that($success)->true();

                $newAbsolutePath = Storage::disk('public')->path($newPath);
                $mimeType = Storage::disk('public')->mimeType($newPath);
                $mediaProperties = $this->getMediaProperties($newAbsolutePath);

                $file = File::create([
                    'name' => $fileName,
                    'filename' => $newFileName,
                    'type' => $this->fileStorageService->fileType(
                        $mimeType,
                        $newAbsolutePath,
                    ),
                    'size' => Storage::disk('public')->size($newPath),

                    'course_id' => $this->mapIds
                        ->get('courses')
                        ->get($legacyCard['course_id']),

                    'status' => FileStatus::Ready,
                    'progress' => 100,

                    // width, height, length
                    ...$mediaProperties,
                ]);

                $card->file_id = $file->id;
                $card->save();
                $card->refresh();
            },
        );
        $this->newLine();

        $this->info('Files migrations complete.');
    }

    protected function migrateAttachments(): void
    {
        $this->info('Migrating attachments...');

        $result = $this->legacyConnection->query(<<<'SQL'
            SELECT
                cards.id,
                cards.course_id,
                path,
            FROM
                card_attachments
                INNER JOIN cards ON cards.id = card_attachments.card_id
        SQL);

        $this->withProgressBar(
            $result->fetchAll(),
            function ($legacyCard) {

                $legacyPath = "attachments/{$legacyCard['id']}/{$legacyCard['path']}";

                if ($this->legacyDisk->missing($legacyPath)) {
                    $this->warn("Skipping attachments for card legacy id {$legacyCard['id']}.");
                    $this->warn("File '$legacyPath' does not exist.");

                    return;
                }
                $extension = $this->fileStorageService->getExtension($legacyPath);

                Assert::that($extension)->notEmpty();

                $newFileName = $this->generateNewFileName($extension);
                $newPath = StoragePath::UploadStandard."/$newFileName";

                $success = Storage::disk('public')->put(
                    $newPath,
                    $this->legacyDisk->get($legacyPath),
                );

                Assert::that($success)->true();

                $mimeType = Storage::disk('public')->mimeType($newPath);

                File::create([

                    'name' => $this->fileStorageService
                        ->getFileName($legacyPath),

                    'filename' => $newFileName,
                    'type' => $this->fileStorageService->fileType(
                        $mimeType,
                        Storage::disk('public')->path($newPath),
                    ),
                    'size' => Storage::disk('public')->size($newPath),

                    'card_id' => $this->mapIds
                        ->get('cards')
                        ->get($legacyCard['id']),

                    'course_id' => $this->mapIds
                        ->get('courses')
                        ->get($legacyCard['course_id']),

                    'status' => FileStatus::Ready,
                    'progress' => null,
                ]);
            },
        );
        $this->newLine();

        $this->info('Files migrations complete.');
    }

    protected function generateNewFileName($extension): string
    {
        $random = Str::random(40);

        return "legacy-$random.$extension";
    }

    protected function parseFileUrl($videoUrl): array
    {
        $pattern = "#^https://sepia\.unil\.ch/impact/media/([^/]+)/([^/]+)#";

        if (preg_match($pattern, $videoUrl, $matches)) {
            $id = $matches[1];
            $filename = $matches[2];

            return [$id, $filename];
        }

        return null;
    }

    protected function getMediaProperties($path): array
    {
        $ffprobe = FFProbe::create();

        // Get number of video track(s)
        $videoTracks = array_filter(
            $ffprobe
                ->streams($path)
                ->videos()
                ->all(),

            // Filter covers tracks.
            fn ($stream) => $stream->get('disposition')['attached_pic'] !== 1
        );

        // Get number of audio track(s)
        $audioTracks = $ffprobe
            ->streams($path)
            ->audios()
            ->count();

        if (count($videoTracks) > 0) {
            return $this->getVideoProperties($path);
        }

        if ($audioTracks > 0) {
            return $this->getAudioProperties($path);
        }

        $this->error("File '$path' is not a video or audio file. Please check.");
        $this->error('Aborting...');
        exit(1);
    }

    protected function getVideoProperties($path): array
    {
        $ffprobe = FFProbe::create();
        $videoStream = $ffprobe
            ->streams($path)
            ->videos()
            ->first();

        Assert::that($videoStream)->notNull();

        return [
            'length' => (int) $videoStream->get('duration'),
            'width' => $videoStream->getDimensions()->getWidth(),
            'height' => $videoStream->getDimensions()->getHeight(),
        ];
    }

    protected function getAudioProperties($path): array
    {
        $ffprobe = FFProbe::create();
        $audioStream = $ffprobe
            ->streams($path)
            ->audios()
            ->first();

        Assert::that($audioStream)->notNull();

        return [
            'length' => (int) $audioStream->get('duration'),
        ];
    }

    /**
     * Return array[legacy_student_id][legacy_course_id] => array(not legacy cards_id).
     */
    protected function mapStudentCourseToCards(): array
    {
        $result = $this->legacyConnection->query(<<<'SQL'
            SELECT
                student_id,
                course_id,
                GROUP_CONCAT(DISTINCT card_id SEPARATOR ',') as cards
            FROM
                students_have_cards
                INNER JOIN cards ON cards.id = students_have_cards.card_id
            GROUP BY
                student_id,
                course_id
        SQL);

        $map = [];
        foreach ($result->fetchAll() as $item) {
            $map[$item['student_id']][$item['course_id']] = array_map(
                fn ($cardId) => $this->mapIds->get('cards')->get(intval($cardId)),
                explode(',', $item['cards']),
            );
        }

        return $map;
    }

    protected function parseTranscription(string $transcription): array
    {
        $currentLineNumber = 1;
        $icor = [];

        foreach (preg_split('/\R/', $transcription) as $line) {
            $displayLineNo = true;
            $line = preg_replace_callback(
                '/^<wono>/',
                function ($match) use (&$displayLineNo) {
                    $displayLineNo = false;

                    return '';
                },
                $line,
            );

            $fields = explode("\t", $line, 2);
            if (count($fields) === 1) {
                $fields = ['', $fields[0]];
            }

            [$speaker, $speech] = $fields;

            foreach ($this->splitSpeech($speech) as $key => $lineSpeech) {
                $icor[] = [
                    'number' => $displayLineNo ? $currentLineNumber++ : null,
                    'speaker' => $key === 0 ? mb_substr($speaker, 0, 3) : '',
                    'speech' => $lineSpeech,
                    'linkedToPrevious' => $key !== 0,
                ];
            }
        }

        return $icor;
    }

    protected function splitSpeech(string $speech): array
    {
        // This algorithme is the PHP equivalent of the one in
        // Transcription.js::updateSectionSpeech().

        if (mb_strlen($speech) === 0) {
            return [''];
        }

        $remainingLine = str_replace("\n", '', $speech);
        $speechLines = [];

        while (mb_strlen($remainingLine) > 0) {
            $endWithWhitespace = preg_match(
                "/\s/",
                mb_substr($remainingLine, Card::MAX_CHARACTERS_LEGACY_SPEECH, 1),
            ) === 1;

            $line = mb_substr(
                $remainingLine,
                0,
                Card::MAX_CHARACTERS_LEGACY_SPEECH + ($endWithWhitespace ? 1 : 0),
            );

            if (mb_strlen($remainingLine) > Card::MAX_CHARACTERS_LEGACY_SPEECH) {

                $lastSpace = $this->findLastWhitespacePosition($line);
                $lastHyphen = mb_strrpos($line, '-');
                $lastBreak = max($lastSpace, $lastHyphen === false ? -1 : $lastHyphen);

                if ($lastBreak > -1) {
                    $line = mb_substr($line, 0, $lastBreak + 1);
                    $remainingLine = mb_substr($remainingLine, $lastBreak + 1);
                } else {
                    $line = mb_substr(
                        $line, 0, Card::MAX_CHARACTERS_LEGACY_SPEECH
                    );

                    $remainingLine = mb_substr(
                        $remainingLine, Card::MAX_CHARACTERS_LEGACY_SPEECH
                    );
                }

                $remainingLine = preg_replace('/^\s+/', '', $remainingLine);
            } else {
                $remainingLine = '';
            }

            $speechLines[] = $line;
        }

        return $speechLines;
    }

    protected function findLastWhitespacePosition(string $line): int
    {
        $matches = [];
        $lastPosition = -1;

        if (preg_match_all('/\s/', $line, $matches, PREG_OFFSET_CAPTURE)) {
            $lastMatch = end($matches[0]);
            // Here we must use substr and not mb_substr to cut the string at
            // the right position. Because preg_match_all is not multi byte.
            $lastPosition = mb_strlen(substr($line, 0, $lastMatch[1]));
        }

        return $lastPosition;
    }

    protected function askNotNull(string $question, $default = null): mixed
    {
        while (($answer = $this->ask("What is the $question?", $default)) === null) {
            $this->error("Please enter the $question!");
        }

        return $answer;
    }
}
