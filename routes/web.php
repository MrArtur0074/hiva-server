<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ParserController;
use App\Http\Controllers\JsonController;

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

Route::get('/', function () {
    return view('welcome');
});

//Route::post('/webhook', [WebhookController::class, 'handle'])

Route::post('/webhook', [WebhookController::class, 'handle']);

Route::post('/parse', [ParserController::class, 'handle'])->name('parse.form');

Route::post('/parsetest', [ParserController::class, 'test']);

Route::get('/json-test', [JsonController::class, 'getJson']);