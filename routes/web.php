<?php

use App\Http\Controllers\AdminsController;
use App\Http\Controllers\DefaultController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\DocumentationVersionedController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [DefaultController::class, 'indexAction']);

Route::get('/documentation', [DocumentationController::class, 'readAsHtmlAction']);
Route::get('/documentation/{ui}', [DocumentationController::class, 'readAsHtmlAction'])
    ->where('ui', 'swagger|redoc');
Route::get('/documentation/raw', [DocumentationController::class, 'readAsYamlAction']);


Route::get('/documentation/{version}', [DocumentationVersionedController::class, 'readAsHtmlAction'])
    ->where('version', 'v1|v2|mobile');

Route::get('/documentation/{version}/{ui}', [DocumentationVersionedController::class, 'readAsHtmlAction'])
    ->where('version', 'v1|v2|mobile')
    ->where('ui', 'swagger|redoc');

Route::get('/documentation/{version}/raw', [DocumentationVersionedController::class, 'readAsYamlAction'])
    ->where('version', 'v1|v2|mobile');

Route::get('/users/{encrypted_id}/unsubscribe', [UsersController::class, 'unsubscribeAction']);
Route::get('/admins/{encrypted_id}/unsubscribe', [AdminsController::class, 'unsubscribeAction']);
