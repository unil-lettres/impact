<?php

namespace App\Console\Commands;

use App\Card;
use App\Course;
use App\Enums\CourseType;
use App\Enums\StatePermission;
use App\Enums\StateType;
use App\Enums\TranscriptionType;
use App\Enums\UserType;
use App\Folder;
use App\State;
use App\User;
use Assert\Assert;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');

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

        $this->mapIds = collect([]);

        $this->prepareLegacyConnection();

        $this->wipeDatabase();

        // TODO REMOVE
        User::create([
            'name' => 'Admin user',
            'email' => 'admin-user@example.com',
            'password' => Hash::make('password'),
            'admin' => true,
        ])->id;

        $this->migrateUsers();
        $this->migrateCourses();
        $this->migrateFolders();
        $this->migrateStates();
        $this->migrateCards();

        $this->info('Migration complete!');
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
        $dbName = 'impact_legacy';
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

    protected function migrateUsers(): void
    {
        $this->info('Migrating users...');

        $this->mapIds->put('users', collect([]));

        $result = $this->legacyConnection->query('SELECT * FROM users');

        $warns = [];

        $this->withProgressBar(
            $result->fetchAll(),
            function ($legacyUser) use (&$warns) {

                $fullName = [];

                if (! empty($legacyUser['first_name'])) {
                    $fullName[] = $legacyUser['first_name'];
                }
                if (! empty($legacyUser['last_name'])) {
                    $fullName[] = $legacyUser['last_name'];
                }
                if (count($fullName) < 2) {
                    $warns[] = "User legacy id {$legacyUser['id']} miss a lastname, firstname or both.";
                }
                $fullName = implode(' ', $fullName);

                $user = User::create([
                    'name' => $fullName,
                    'email' => $legacyUser['email'],
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

                    // TODO
                    // 'file_id' => $cardLegacy['xxx'],
                    'state_id' => $state->id,

                    'options->no_emails' => $cardLegacy['emails_disabled'] === 1,

                    'options->presentation_date' => $cardLegacy['presentation_date'],

                    'options->box1->end' => $cardLegacy['video_end'],
                    'options->box1->hidden' => $cardLegacy['video_hidden'] === 1,
                    'options->box1->link' => $cardLegacy['video_url'],
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

                    // TODO video_upload_state pour la table file

                    'position' => $cardLegacy['position'],
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
                    'course_id' => $this->mapIds->get('courses')->get($folderLegacy['course_id']),
                ]);

                $this->mapIds->get('folders')->put($folderLegacy['id'], $folder->id);
            },
        );
        $this->newLine();

        $this->info('Associating folder\'s parent...');
        $this->withProgressBar(
            $folders,
            function ($folderLegacy) {
                $folder = Folder::find($this->mapIds->get('folders')->get($folderLegacy['id']));
                $folder->parent_id = $this->mapIds->get('folders')->get($folderLegacy['parent_id']);
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
                if (in_array($stateLegacy['id'], [self::LEGACY_PRIVATE_ID, self::LEGACY_ARCHIVED_ID])) {
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
                    'course_id' => $this->mapIds->get('courses')->get($stateLegacy['course_id']),
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

                $this->mapIds->get('states')->put($stateLegacy['id'], $state->id);
            },
        );
        $this->newLine();
        $this->info('Courses migrations complete.');
    }

    protected function parseTranscription(string $transcription): array
    {
        // TODO
        return [];
    }

    protected function askNotNull(string $question, $default = null): mixed
    {
        while (($answer = $this->ask("What is the $question?", $default)) === null) {
            $this->error("Please enter the $question!");
        }

        return $answer;
    }
}
