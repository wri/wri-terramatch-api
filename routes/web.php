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

Route::get("/", "DefaultController@indexAction");

Route::get("/documentation", "DocumentationController@readAsHtmlAction");
Route::get("/documentation/raw", "DocumentationController@readAsYamlAction");

Route::get("/users/{encrypted_id}/unsubscribe", "UsersController@unsubscribeAction");
Route::get("/admins/{encrypted_id}/unsubscribe", "AdminsController@unsubscribeAction");
