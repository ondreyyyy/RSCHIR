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

require_once __DIR__ . '/vendor/autoload.php';

return function (): void {
    $container = new Container();
    $router = new Router();

    $router->get('/', [WeatherController::class, 'index']);
    $router->get('/index.php', [WeatherController::class, 'index']);

    $router->get('/weather.php', [WeatherController::class, 'index']);
    $router->get('/statistics.php', [StatisticsController::class, 'index']);
    $router->get('/uploads.php', [UploadsController::class, 'index']);
    $router->post('/uploads.php', [UploadsController::class, 'index']);
    $router->get('/about.php', [AboutController::class, 'index']);
    $router->get('/contacts.php', [ContactsController::class, 'index']);
    $router->get('/admin/admin.php', [AdminController::class, 'index']);
    $router->get('/admin', [AdminController::class, 'index']);

    $router->get('/api.php', [ApiController::class, 'docs']);
    $router->get('/api.php/weather', [ApiController::class, 'weather']);
    $router->get('/api.php/weather/{id}', [ApiController::class, 'weather']);
    $router->post('/api.php/weather', [ApiController::class, 'weather']);
    $router->put('/api.php/weather/{id}', [ApiController::class, 'weather']);
    $router->delete('/api.php/weather/{id}', [ApiController::class, 'weather']);
    $router->get('/api.php/users', [ApiController::class, 'users']);
    $router->get('/api.php/users/{id}', [ApiController::class, 'users']);
    $router->post('/api.php/users', [ApiController::class, 'users']);
    $router->put('/api.php/users/{id}', [ApiController::class, 'users']);
    $router->delete('/api.php/users/{id}', [ApiController::class, 'users']);
    $router->get('/api.php/uploads', [ApiController::class, 'uploads']);
    $router->get('/api.php/uploads/{id}', [ApiController::class, 'uploads']);

    $router->get('/chart.php', function (Container $container): void {
        $fileName = (string) ($_GET['file'] ?? '');
        if ($fileName === '' || !preg_match('/^[a-zA-Z0-9_]+\.png$/', $fileName)) {
            Response::notFound('Некорректное имя файла.');
            return;
        }
        $fullPath = $container->config()->chartsDir() . DIRECTORY_SEPARATOR . $fileName;
        Response::image($fullPath);
    });

    $router->get('/download.php', function (Container $container): void {
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
};
