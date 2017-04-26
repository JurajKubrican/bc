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

Route::get('/', 'HomeController@index');
Route::get('/welcome', 'HomeController@welcome');

Route::get('place/crawl', 'PlaceController@crawl');
Route::post('place/{id}', 'PlaceController@destroy');
Route::resource('place', 'PlaceController');

Route::get('/placeapi', 'PlaceController@apiGet');
Route::post('/placeapi/{place}', 'PlaceController@apiDelete');
Route::post('/placeapi/add/{place}', 'PlaceController@apiAdd');

Route::get('/tsp','HomeController@indexTsp');
Route::get('/tsp/solve','TSPController@run');
Route::post('/tsp/add/{place}','TSPController@add');
Route::post('/tsp/remove/{place}','TSPController@remove');


Route::get('/user/{id}', 'HomeController@indexUser');

Route::get('/city/{id}', 'HomeController@indexCity');


Route::resource('settings', 'SettingsController');

Route::get('backend', function() {
  return View::make('backend', array());
});


Auth::routes();
