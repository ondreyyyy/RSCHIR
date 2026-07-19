<?php
declare(strict_types=1);

use App\Http\Container;
use App\Http\Controller\AboutController;
use App\Http\Controller\AdminController;
use App\Http\Controller\ApiController;
use App\Http\Controller\ContactsController;
use App\Http\Controller\StatisticsController;
use App\Http\Controller\UploadsController;
use App\Http\Controller\WeatherController;
use App\Http\Response;
use App\Http\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$router = new Router();

$router->get('/', [WeatherController::class, 'index']);
$router->get('/weather', [WeatherController::class, 'index']);
$router->post('/weather', [WeatherController::class, 'index']);
$router->get('/statistics', [StatisticsController::class, 'index']);
$router->get('/uploads', [UploadsController::class, 'index']);
$router->post('/uploads', [UploadsController::class, 'index']);
$router->get('/about', [AboutController::class, 'index']);
$router->get('/contacts', [ContactsController::class, 'index']);
$router->get('/admin', [AdminController::class, 'index']);

$router->get('/api', [ApiController::class, 'docs']);
$router->get('/api/weather', [ApiController::class, 'weather']);
$router->get('/api/weather/{id}', [ApiController::class, 'weather']);
$router->post('/api/weather', [ApiController::class, 'weather']);
$router->put('/api/weather/{id}', [ApiController::class, 'weather']);
$router->delete('/api/weather/{id}', [ApiController::class, 'weather']);
$router->get('/api/users', [ApiController::class, 'users']);
$router->get('/api/users/{id}', [ApiController::class, 'users']);
$router->post('/api/users', [ApiController::class, 'users']);
$router->put('/api/users/{id}', [ApiController::class, 'users']);
$router->delete('/api/users/{id}', [ApiController::class, 'users']);
$router->get('/api/uploads', [ApiController::class, 'uploads']);
$router->get('/api/uploads/{id}', [ApiController::class, 'uploads']);

$router->get('/chart', function (Container $container): void {
    $fileName = (string) ($_GET['file'] ?? '');
    if ($fileName === '' || !preg_match('/^[a-zA-Z0-9_]+\.png$/', $fileName)) {
        Response::notFound('Некорректное имя файла.');
        return;
    }
    $fullPath = $container->config()->chartsDir() . DIRECTORY_SEPARATOR . $fileName;
    Response::image($fullPath);
});

$router->get('/download', function (Container $container): void {
    $fileName = (string) ($_GET['file'] ?? '');
    if ($fileName === '') {
        Response::notFound('Не указан PDF-файл.');
        return;
    }
    try {
        $container->pdfService()->download($fileName);
    } catch (Throwable $exception) {
        Response::notFound($exception->getMessage());
    }
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

$path = $requestUri;
if ($scriptName !== '' && str_ends_with($requestUri, $scriptName)) {
    $path = rtrim(substr($requestUri, 0, -strlen($scriptName)), '/') ?: '/';
}

$matched = $router->match($method, $path);
if ($matched === null) {
    Response::notFound('Страница не найдена.');
    return;
}

$handler = $matched['handler'];
$args = array_values($matched['params']);

try {
    if ($handler instanceof Closure) {
        $handler($container, ...$args);
        return;
    }

    [$handlerClass, $handlerMethod] = $handler;
    $controller = new $handlerClass($container);
    $controller->$handlerMethod(...$args);
} catch (Throwable $e) {
    if (!headers_sent()) {
        Response::error('Внутренняя ошибка сервера: ' . $e->getMessage(), 500);
    }
}
