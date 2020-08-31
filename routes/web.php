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

//nagios health check
Route::get('/', function(){ return ""; });

Route::get('/webhook/makeshop', 'WebhookController@orderStatus')->name('webhook.ms.order')->middleware('makeshop_api');