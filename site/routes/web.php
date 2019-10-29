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
    Route::get('invitations/{id}/mail', 'InvitationController@mail')->name('send.invite');
});

// Administration routes
Route::group(['prefix' => 'admin',  'as' => 'admin.', 'middleware' => ['auth', 'app', 'is_admin']], function() {
    Route::get('/', 'AdminController@index')->name('index');

    Route::get('/invitations', 'InvitationController@index')->name('invitations.index');
    Route::get('/invitations/create', 'InvitationController@create')->name('invitations.create');

    Route::resource('users', 'UserController');
});
