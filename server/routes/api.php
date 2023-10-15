<?php

use App\Http\Controllers\DataController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('products', function () {
    return response(['Product 1', 'Product 2', 'Product 3'], 200);
});

Route::get('data', 'App\Http\Controllers\DataController@index');
Route::get('export-users', 'App\Http\Controllers\DataController@exportCsv');
Route::post('upload', 'App\Http\Controllers\DataController@upload');
