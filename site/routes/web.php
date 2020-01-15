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
Route::get('aai', 'HomeController@index')->name('aai')->middleware('check_aai');

// Localization route
Route::get('lang/{locale}', 'LocalizationController@index');

// Register with an invitation
Route::middleware(['has_invitation'])->group(function () {
    Route::get('invitations/register', 'InvitationController@register')->name('invitations.register');
    Route::post('invitations/user/create', 'InvitationController@createInvitationUser')->name('invitations.user.create');
});

// Standard routes
Route::middleware(['auth', 'app'])->group(function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::resource('invitations', 'InvitationController');
    Route::get('invitations/{invitation}/mail', 'InvitationController@mail')->name('send.invite');

    Route::get('users/{user}/profile', 'UserController@edit')->name('users.profile');
    Route::put('users/{user}/update', 'UserController@update')->name('users.profile.update');

    Route::resource('cards', 'CardController');

    Route::resource('courses', 'CourseController');
});

// Administration routes
Route::group(['prefix' => 'admin',  'as' => 'admin.', 'middleware' => ['auth', 'app', 'is_admin']], function() {
    Route::get('/', 'AdminController@index')->name('index');

    Route::get('/invitations', 'InvitationController@index')->name('invitations.index');
    Route::get('/invitations/create', 'InvitationController@create')->name('invitations.create');

    Route::resource('users', 'UserController');
    Route::get('/users/{user}/extend', 'UserController@extend')->name('users.extend');

    Route::get('/courses/create', 'CourseController@create')->name('courses.create');
    Route::delete('/courses/{course}', 'CourseController@destroy')->name('courses.destroy');
});
