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

// Authentication routes
Auth::routes();

// SwitchAAI authentication route
Route::get('aai', 'CourseController@index')->name('aai')->middleware('check_aai');

// Localization route
Route::get('lang/{locale}', 'LocalizationController@index');

// Register with an invitation routes
Route::middleware(['has_invitation'])->group(function () {
    Route::get('invitations/register', 'InvitationController@register')->name('invitations.register');
    Route::post('invitations/user/create', 'InvitationController@createInvitationUser')->name('invitations.user.create');
});

// Standard routes for authenticated users
Route::middleware(['auth', 'app'])->group(function () {
    Route::get('/', 'CourseController@index')->name('home');

    // Invitations
    Route::resource('invitations', 'InvitationController');
    Route::get('invitations/{invitation}/mail', 'InvitationController@mail')->name('send.invite');

    // Users
    Route::get('users/{user}/profile', 'UserController@profile')->name('users.profile');
    Route::put('users/{user}/update', 'UserController@update')->name('users.profile.update');

    // Cards
    Route::resource('cards', 'CardController');
    Route::put('cards/{card}/unlink/file', 'CardController@unlinkFile')->name('cards.unlink.file');

    // Folders
    Route::resource('folders', 'FolderController');

    // Files
    Route::resource('files', 'FileController')->only(['index', 'destroy']);
    Route::post('files/upload', 'FileController@upload');

    // Enrollments
    Route::get('enrollments', 'EnrollmentController@index');
    Route::post('enrollments', 'EnrollmentController@store');
    Route::get('enrollments/find', 'EnrollmentController@find');
    Route::put('enrollments/cards', 'EnrollmentController@cards');
    Route::delete('enrollments/{enrollment}', 'EnrollmentController@destroy');

    // Courses
    Route::get('courses/{course}', 'CourseController@show')->name('courses.show');
    Route::get('courses/{course}/configure', 'CourseController@configure')->name('courses.configure');
    Route::get('courses/{course}/configure/files', 'FileController@index')->name('courses.configure.files');
    Route::delete('/courses/{course}/disable', 'CourseController@disable')->name('courses.disable');
});

// Administration routes
Route::group(['prefix' => 'admin',  'as' => 'admin.', 'middleware' => ['auth', 'app', 'is_admin']], function() {
    Route::get('/', 'AdminController@index')->name('index');

    // Invitations
    Route::get('/invitations', 'InvitationController@manage')->name('invitations.manage');
    Route::get('/invitations/create', 'InvitationController@create')->name('invitations.create');

    // Users
    Route::resource('users', 'UserController');
    Route::get('/users', 'UserController@manage')->name('users.manage');
    Route::get('/users/{user}/extend', 'UserController@extend')->name('users.extend');

    // Courses
    Route::resource('courses', 'CourseController');
    Route::get('/courses', 'CourseController@manage')->name('courses.manage');
    Route::get('/courses/{course}/enable', 'CourseController@enable')->name('courses.enable');
    Route::get('/courses/{course}/mailConfirmDelete', 'CourseController@mailConfirmDelete')->name('courses.send.confirm.delete');

    // Files
    Route::resource('files', 'FileController');
    Route::get('/files', 'FileController@manage')->name('files.manage');
});
