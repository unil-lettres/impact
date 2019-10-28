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
Auth::routes(['register' => false]);

// Register with an invitation
Route::middleware(['has_invitation'])->group(function () {
    Route::get('invitations/register', 'InvitationController@register')->name('invitations.register');
    Route::post('invitations/user/create', 'InvitationController@createInvitationUser')->name('invitations.user.create');
});

// Localization route
Route::get('lang/{locale}', 'LocalizationController@index');

// Standard routes
Route::middleware(['auth', 'app'])->group(function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::resource('invitations', 'InvitationController');
    Route::get('invitations/{id}/mail', 'InvitationController@mail')->name('send.invite');
});

// Administration routes
Route::group(['prefix' => 'admin',  'middleware' => ['auth', 'app', 'is_admin']], function() {
    Route::get('/', 'AdminController@index')->name('admin.index');
    Route::get('/users', 'AdminController@users')->name('admin.users');
    Route::get('/invitations', 'InvitationController@index')->name('admin.invitations.index');
    Route::get('/invitations/create', 'InvitationController@create')->name('admin.invitations.create');
});
