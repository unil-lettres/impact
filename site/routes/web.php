<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Json\CardJsonController;
use App\Http\Controllers\Json\EnrollmentJsonController;
use App\Http\Controllers\Json\FileJsonController;
use App\Http\Controllers\Json\StateJsonController;
use App\Http\Controllers\Json\TagJsonController;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Authentication routes
Auth::routes();

// SwitchAAI authentication route
Route::get('aai', [CourseController::class, 'index'])
    ->name('aai')
    ->middleware('check_aai');

// Localization route
Route::get('lang/{locale}', [LocalizationController::class, 'index']);

// Register with an invitation routes
Route::middleware(['has_invitation'])->group(function () {
    Route::get('invitations/register', [InvitationController::class, 'register'])
        ->name('invitations.register');
    Route::post('invitations/user/create', [InvitationController::class, 'createInvitationUser'])
        ->name('invitations.user.create');
});

// Standard routes for authenticated users
Route::middleware(['auth', 'app'])->group(function () {
    // Homepage
    Route::get('/', [CourseController::class, 'index'])
        ->name('home');

    // Invitations
    Route::resource('invitations', InvitationController::class);
    Route::get('invitations/{invitation}/mail', [InvitationController::class, 'mail'])
        ->name('send.invite');

    // Users
    Route::get('users/{user}/profile', [UserController::class, 'profile'])
        ->name('users.profile');
    Route::put('users/{user}/update', [UserController::class, 'update'])
        ->name('users.profile.update');

    // Cards
    Route::get('cards/print', [CardController::class, 'print'])
        ->name('cards.print');
    Route::resource('cards', CardController::class)->except(['create', 'store']);
    Route::put('cards/{card}/unlink/file', [CardController::class, 'unlinkFile'])
        ->name('cards.unlink.file');
    Route::put('cards/{card}/editor', [CardJsonController::class, 'editor'])
        ->name('cards.editor');
    Route::put('cards/{card}/transcription', [CardJsonController::class, 'transcription'])
        ->name('cards.transcription');
    Route::post('cards/{card}/export', [CardController::class, 'export'])
        ->name('cards.export');
    Route::get('cards/{card}/tagsInline', [CardJsonController::class, 'tagsInline'])
        ->name('cards.tagsInline');

    // Folders
    Route::resource('folders', FolderController::class)->only(['show']);

    // Files
    Route::resource('files', FileController::class)->only([
        'index',
        'destroy',
    ]);
    Route::get('files/download', [FileController::class, 'download'])
        ->name('files.download');
    Route::post('files/upload', [FileJsonController::class, 'upload']);

    // Enrollments
    Route::get('enrollments', [EnrollmentJsonController::class, 'index']);
    Route::post('enrollments', [EnrollmentJsonController::class, 'store']);
    Route::delete('enrollments', [EnrollmentJsonController::class, 'destroy']);
    Route::put('enrollments/attach', [EnrollmentJsonController::class, 'attach'])
        ->name('enrollments.attach');
    Route::put('enrollments/detach', [EnrollmentJsonController::class, 'detach'])
        ->name('enrollments.detach');

    // Courses
    Route::get('courses/{course}', [CourseController::class, 'show'])
        ->name('courses.show');
    Route::get('courses/{course}/configure', [CourseController::class, 'editConfiguration'])
        ->name('courses.configure');
    Route::put('courses/{course}/configure', [CourseController::class, 'updateConfiguration'])
        ->name('courses.configure.update');
    Route::put('/courses/{course}/archive', [CourseController::class, 'archive'])
        ->name('courses.archive');
    Route::delete('/courses/{course}/disable', [CourseController::class, 'disable'])
        ->name('courses.disable');
    Route::get('courses/{course}/configure/tags', [TagController::class, 'index'])
        ->name('courses.configure.tags');
    Route::get('courses/{course}/configure/files', [FileController::class, 'index'])
        ->name('courses.configure.files');
    Route::get('courses/{course}/configure/registrations', [UserController::class, 'index'])
        ->name('courses.configure.registrations');
    Route::get('courses/{course}/configure/states', [StateController::class, 'index'])
        ->name('courses.configure.states');
    Route::post('courses/{course}/state', [StateController::class, 'store'])
        ->name('courses.create.state');
    Route::put('courses/{course}/state/{state}', [StateController::class, 'update'])
        ->name('courses.update.state');
    Route::delete('courses/{course}/state/{state}', [StateController::class, 'destroy'])
        ->name('courses.destroy.state');
    Route::put('courses/{course}/state/{state}/position', [StateJsonController::class, 'position'])
        ->name('courses.update.state.position');

    // Tags
    Route::resource('tags', TagController::class)->only([
        'store',
        'update',
        'destroy',
    ]);
    Route::post('tags/clone', [TagController::class, 'clone'])
        ->name('tags.clone');
    Route::post('tags/create', [TagJsonController::class, 'create'])
        ->name('tags.create');
    Route::put('tags/{tag}/attach/{card}', [TagJsonController::class, 'attach'])
        ->name('tags.attach.tag');
    Route::put('tags/{tag}/detach/{card}', [TagJsonController::class, 'detach'])
        ->name('tags.detach.tag');

    // Legacy
    Route::get('legacy', [LegacyController::class, 'redirect'])
        ->name('legacy.redirect');
});

// Administration routes
Route::prefix('admin')->name('admin.')->middleware('auth', 'app', 'is_admin')->group(function () {
    // Admin homepage
    Route::get('/', [AdminController::class, 'index'])
        ->name('index');

    // Invitations
    Route::get('/invitations', [InvitationController::class, 'manage'])
        ->name('invitations.manage');
    Route::get('/invitations/create', [InvitationController::class, 'create'])
        ->name('invitations.create');

    // Users
    Route::resource('users', UserController::class);
    Route::get('/users', [UserController::class, 'manage'])
        ->name('users.manage');
    Route::get('/users/{user}/extend', [UserController::class, 'extend'])
        ->name('users.extend');

    // Courses
    Route::resource('courses', CourseController::class);
    Route::get('/courses', [CourseController::class, 'manage'])
        ->name('courses.manage');
    Route::get('/courses/{course}/enable', [CourseController::class, 'enable'])
        ->name('courses.enable');
    Route::put('/courses/{course}/unsync', [CourseController::class, 'unsync'])
        ->name('courses.unsync');
    Route::get('/courses/{course}/mailConfirmDelete', [CourseController::class, 'mailConfirmDelete'])
        ->name('courses.send.confirm.delete');

    // Files
    Route::resource('files', FileController::class)->only([
        'edit',
        'update',
        'destroy',
    ]);
    Route::get('/files', [FileController::class, 'manage'])
        ->name('files.manage');

    // Mailing
    Route::get('/mailing', [AdminController::class, 'mailing'])
        ->name('mailing');
    Route::post('/mailing/send', [AdminController::class, 'mailMailing'])
        ->name('mailing.send');
});
