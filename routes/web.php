<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/


Route::get('/', [
  'as'=>'list',
  'uses'=>'pagesController@getList'
])->name('list');


Route::get('list', [
  'as'=>'list',
  'uses'=>'pagesController@getList'
])->name('list');

Route::get('help', function () {
    return view('pages.help');
});

Route::get('me/settings', function () {
    return view('pages.help');
});

Route::resource('place','PlaceController');
