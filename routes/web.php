<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ParserController;
use App\Http\Controllers\JsonController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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

Route::get('/panel', [PanelController::class, 'index'])->name('panel');
Route::get('/load-pages/{siteId}', [PanelController::class, 'loadPages']);
Route::post('/generate-file', [PanelController::class, 'generateFile']);
Route::get('/files-list', [FileController::class, 'index'])->name('files');

Route::get('/download/{filename}', function ($filename) {
    $filePath = 'public/files/' . $filename; // Путь к файлу в хранилище

    // Проверяем существование файла
    if (Storage::exists($filePath)) {
        $fileContents = Storage::get($filePath);

        // Устанавливаем заголовок Content-Type с кодировкой UTF-8
        $headers = [
            'Content-Type' => 'text/plain; charset=utf-8',
        ];

        return Response::make($fileContents, 200, $headers);
    } else {
        abort(404); // Обработка случая, когда файл не найден
    }
})->name('downloadFile');

Route::group(['prefix' => 'api/v1'], function () {
    Route::get('/files', [FileController::class, 'api']);
    // Добавьте другие маршруты для этой группы по мере необходимости
    Route::get('/files/{id}', [FileController::class, 'oneFile']);
});

