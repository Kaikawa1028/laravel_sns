<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:api']], function () {
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::post('/{article}/comments', 'CommentController@store')->name('comment.store');
    });
});

Route::prefix('articles')->name('articles.')->group(function () {
    Route::get('/{article}/comments', 'CommentController@index')->name('comment');
});

