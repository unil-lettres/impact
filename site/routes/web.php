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

// Standard routes
Route::middleware(['auth', 'app'])->group(function () {
    Route::get('/', 'HomeController@index')->name('home');
});

// Administration routes
Route::group(['prefix' => 'admin',  'middleware' => ['auth', 'app', 'is_admin']], function() {
    Route::get('/', 'AdminController@index')->name('admin');
});
