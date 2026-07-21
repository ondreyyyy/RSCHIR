<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\WeatherController;
use App\Infrastructure\AppConfig;
use App\Http\Middleware\AdminBasicAuth;
use App\Services\PdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeatherController::class, 'index']);
Route::post('/', [WeatherController::class, 'index']);
Route::get('/weather', [WeatherController::class, 'index']);
Route::post('/weather', [WeatherController::class, 'index']);
Route::get('/statistics', [StatisticsController::class, 'index']);
Route::get('/uploads', [UploadsController::class, 'index']);
Route::post('/uploads', [UploadsController::class, 'index']);
Route::get('/about', [AboutController::class, 'index']);
Route::get('/contacts', [ContactsController::class, 'index']);
Route::get('/admin', [AdminController::class, 'index'])->middleware(AdminBasicAuth::class);

Route::get('/api', [ApiController::class, 'docs']);
Route::get('/api/weather', [ApiController::class, 'weather'])->middleware(AdminBasicAuth::class);
Route::get('/api/weather/{id}', [ApiController::class, 'weather'])->middleware(AdminBasicAuth::class);
Route::post('/api/weather', [ApiController::class, 'weather'])->middleware(AdminBasicAuth::class);
Route::put('/api/weather/{id}', [ApiController::class, 'weather'])->middleware(AdminBasicAuth::class);
Route::delete('/api/weather/{id}', [ApiController::class, 'weather'])->middleware(AdminBasicAuth::class);
Route::get('/api/users', [ApiController::class, 'users'])->middleware(AdminBasicAuth::class);
Route::get('/api/users/{id}', [ApiController::class, 'users'])->middleware(AdminBasicAuth::class);
Route::post('/api/users', [ApiController::class, 'users'])->middleware(AdminBasicAuth::class);
Route::put('/api/users/{id}', [ApiController::class, 'users'])->middleware(AdminBasicAuth::class);
Route::delete('/api/users/{id}', [ApiController::class, 'users'])->middleware(AdminBasicAuth::class);
Route::get('/api/uploads', [ApiController::class, 'uploads'])->middleware(AdminBasicAuth::class);
Route::get('/api/uploads/{id}', [ApiController::class, 'uploads'])->middleware(AdminBasicAuth::class);
Route::delete('/api/uploads/{id}', [ApiController::class, 'uploads'])->middleware(AdminBasicAuth::class);

Route::get('/chart', function (AppConfig $config): Response {
    $fileName = (string) request()->query('file', '');
    if ($fileName === '' || !preg_match('/^[a-zA-Z0-9_]+\.png$/', $fileName)) {
        abort(404, 'Некорректное имя файла.');
    }
    $fullPath = $config->chartsDir() . DIRECTORY_SEPARATOR . $fileName;
    if (!is_file($fullPath)) {
        abort(404, 'Файл не найден.');
    }
    header('Content-Type: image/png');
    header('Content-Length: ' . (string) filesize($fullPath));
    readfile($fullPath);
    exit;
});

Route::get('/download', function (): Response {
    $fileName = (string) request()->query('file', '');
    if ($fileName === '') {
        abort(404, 'Не указан PDF-файл.');
    }
    try {
        (new PdfService(new \App\Infrastructure\FileSystemPdfStorage(new \App\Infrastructure\AppConfig())))->download($fileName);
    } catch (\Throwable $exception) {
        abort(404, $exception->getMessage());
    }
});
