<?php

namespace App\Http\Controllers;

use App\Domain\Weather;
use App\Infrastructure\EloquentUserRepository;
use App\Infrastructure\EloquentWeatherRepository;
use App\Services\PdfService;
use App\Services\UserService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class ApiController extends Controller
{
    public function docs(Request $request): Response
    {
        return $this->renderPage($request, 'apiTitle', 'api');
    }

    public function weather(Request $request, ?string $id = null): JsonResponse
    {
        $this->requireAdmin($request);
        $method = $request->getMethod();
        $body = $this->requestBody($request);

        try {
            $weatherService = new WeatherService(new EloquentWeatherRepository());
            switch ($method) {
                case 'GET':
                    return response()->json($id === null ? $this->toArray($weatherService->all())
                        : $this->weatherItem($weatherService, (int) $id));
                case 'POST':
                    return response()->json($weatherService->create($body)->toArray(), 201);
                case 'PUT':
                    $this->needId($id);
                    return response()->json($weatherService->update((int) $id, $body)->toArray());
                case 'DELETE':
                    $this->needId($id);
                    $weatherService->delete((int) $id);
                    return response()->json(['message' => 'Запись погоды удалена.', 'id' => (int) $id]);
                default:
                    return response()->json(['error' => 'Метод не поддерживается для ресурса weather.'], 405);
            }
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], $this->statusCode($e));
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()], 500);
        }
    }

    public function users(Request $request, ?string $id = null): JsonResponse
    {
        $this->requireAdmin($request);
        $method = $request->getMethod();
        $body = $this->requestBody($request);

        try {
            $userService = new UserService(new EloquentUserRepository());
            switch ($method) {
                case 'GET':
                    return response()->json($id === null ? $this->toArray($userService->all())
                        : $this->userItem($userService, (int) $id));
                case 'POST':
                    return response()->json($this->userItem($userService, $userService->create($body)->id), 201);
                case 'PUT':
                    $this->needId($id);
                    return response()->json($this->userItem($userService, $userService->update((int) $id, $body)->id));
                case 'DELETE':
                    $this->needId($id);
                    $userService->delete((int) $id);
                    return response()->json(['message' => 'Пользователь удалён.', 'id' => (int) $id]);
                default:
                    return response()->json(['error' => 'Метод не поддерживается для ресурса users.'], 405);
            }
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], $this->statusCode($e));
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()], 500);
        }
    }

    public function uploads(Request $request, ?string $id = null): JsonResponse
    {
        $this->requireAdmin($request);
        $method = $request->getMethod();

        if ($method === 'GET') {
            $pdfService = new PdfService(new \App\Infrastructure\FileSystemPdfStorage(new \App\Infrastructure\AppConfig()));
            $files = $pdfService->list();
            if ($id === null) {
                return response()->json(array_map(static fn($f): array => $f->toArray(), $files));
            }

            $file = $files[(int) $id] ?? null;
            if ($file === null) {
                return response()->json(['error' => 'Файл не найден.'], 404);
            }
            return response()->json($file->toArray());
        }

        if ($method === 'DELETE') {
            $this->needId($id);
            $fileName = $files[(int) $id] ?? null;
            if ($fileName === null) {
                return response()->json(['error' => 'Файл не найден.'], 404);
            }
            try {
                (new PdfService(new \App\Infrastructure\FileSystemPdfStorage(new \App\Infrastructure\AppConfig())))->delete($fileName->name);
                return response()->json(['message' => 'Файл удалён.', 'name' => $fileName->name]);
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
        }

        return response()->json(['error' => 'Метод не поддерживается для ресурса uploads.'], 405);
    }

    /**
     * @param iterable<Weather|\App\Domain\User> $items
     * @return array<int, array<string, mixed>>
     */
    private function toArray(iterable $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $item->toArray();
        }
        return $result;
    }

    private function weatherItem(WeatherService $service, int $id): array
    {
        $item = $service->find($id);
        if ($item === null) {
            abort(404, 'Запись погоды не найдена.');
        }
        return $item->toArray();
    }

    private function userItem(UserService $service, int $id): array
    {
        $item = $service->find($id);
        if ($item === null) {
            abort(404, 'Пользователь не найден.');
        }
        return $item->toArray();
    }

    private function needId(?string $id): void
    {
        if ($id === null) {
            abort(400, 'Идентификатор для операции не указан.');
        }
    }

    private function requireAdmin(Request $request): void
    {
        if (!($request->session()->has('admin') && $request->session()->get('admin') === true)) {
            abort(403, 'Доступ запрещён.');
        }
    }

    private function statusCode(RuntimeException $e): int
    {
        $code = (int) $e->getCode();
        return ($code >= 400 && $code < 600) ? $code : 400;
    }

    private function requestBody(Request $request): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return $request->all();
        }

        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        parse_str($raw, $data);
        return is_array($data) ? $data : [];
    }
}
