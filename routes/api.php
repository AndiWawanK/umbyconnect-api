<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::get('login', function (Request $request) {
//     // return $request->user();
// });

// authentication route
Route::group([
    'namespace' => 'App\Http\Controllers\API\Auth',
    'prefix' => 'v1'
],function(){
    // Route::post('/login', ['as' => 'login', 'uses' => 'LoginController@entry']);
    Route::post('/login', 'LoginController@entry');
    Route::post('/register', 'RegisterController@create');
});

// thread route
Route::group([
    'middleware' => 'auth:sanctum',
    'namespace' => 'App\Http\Controllers\API',
    'prefix' => 'v1'
], function(){
    Route::get('/thread', 'ThreadController@show');
    Route::get('/thread/{threadId}/comment', 'ThreadController@comment');
});

// profile route
Route::group([
    'middleware' => 'auth:sanctum',
    'namespace' => 'App\Http\Controllers\API',
    'prefix' => 'v1'
], function(){
    Route::get('/profile', 'ProfileController@show');
    Route::post('/set-avatar', 'ProfileController@setAvatar');
    
});