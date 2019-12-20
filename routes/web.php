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

    use App\Models\User;
    use Illuminate\Support\Facades\Mail;

    Route::get("/", "DefaultController@indexAction");
    Route::get("/documentation", "DocumentationController@readAsHtmlAction");
    Route::get("/documentation/raw", "DocumentationController@readAsYamlAction");

    Route::get('/test', function () {
        return (new \App\Exports\OrganisationVersionsExport())->query()->toSql();
    });
