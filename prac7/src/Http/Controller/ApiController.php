<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Container;
use App\Http\Response;
use PDOException;
use Throwable;

final class ApiController extends AbstractController
{
    public function docs(): void
    {
        $this->renderPage('apiTitle', 'api');
    }

    public function weather(string $id = null): void
    {
        $this->requireAdmin();
        $method = $_SERVER['REQUEST_METHOD'];
        $body = $this->requestBody();

        try {
            switch ($method) {
                case 'GET':
                    Response::json($id === null ? $this->toArray($this->container->weatherService()->all())
                        : $this->weatherItem((int) $id));
                case 'POST':
                    Response::json($this->container->weatherService()->create($body)->toArray(), 201);
                case 'PUT':
                    $this->needId($id);
                    Response::json($this->container->weatherService()->update((int) $id, $body)->toArray());
                case 'DELETE':
                    $this->needId($id);
                    $this->container->weatherService()->delete((int) $id);
                    Response::json(['message' => 'Запись погоды удалена.', 'id' => (int) $id]);
                default:
                    Response::error('Метод не поддерживается для ресурса weather.', 405);
            }
        } catch (Throwable $e) {
            $this->handleException($e, 'weather');
        }
    }

    public function users(string $id = null): void
    {
        $this->requireAdmin();
        $method = $_SERVER['REQUEST_METHOD'];
        $body = $this->requestBody();

        try {
            switch ($method) {
                case 'GET':
                    Response::json($id === null ? $this->toArray($this->container->userService()->all())
                        : $this->userItem((int) $id));
                case 'POST':
                    Response::json($this->userItem($this->container->userService()->create($body)->id), 201);
                case 'PUT':
                    $this->needId($id);
                    Response::json($this->userItem($this->container->userService()->update((int) $id, $body)->id));
                case 'DELETE':
                    $this->needId($id);
                    $this->container->userService()->delete((int) $id);
                    Response::json(['message' => 'Пользователь удалён.', 'id' => (int) $id]);
                default:
                    Response::error('Метод не поддерживается для ресурса users.', 405);
            }
        } catch (Throwable $e) {
            $this->handleException($e, 'users');
        }
    }

    public function uploads(string $id = null): void
    {
        $this->requireAdmin();
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== 'GET') {
            Response::error('Метод не поддерживается для ресурса uploads.', 405);
            return;
        }

        $files = $this->container->pdfService()->list();
        if ($id === null) {
            Response::json(array_map(static fn($f): array => $f->toArray(), $files));
        }

        $file = $files[(int) $id] ?? null;
        if ($file === null) {
            Response::error('Файл не найден.', 404);
            return;
        }
        Response::json($file->toArray());
    }

    /**
     * @param iterable<\App\Domain\Weather|\App\Domain\User> $items
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

    private function weatherItem(int $id): array
    {
        $item = $this->container->weatherService()->find($id);
        if ($item === null) {
            Response::error('Запись погоды не найдена.', 404);
        }
        return $item->toArray();
    }

    private function userItem(?int $id): array
    {
        if ($id === null) {
            Response::error('Пользователь не найден.', 404);
        }
        $item = $this->container->userService()->find($id);
        if ($item === null) {
            Response::error('Пользователь не найден.', 404);
        }
        return $item->toArray();
    }

    private function needId(?string $id): void
    {
        if ($id === null) {
            Response::error('Идентификатор для операции не указан.', 400);
        }
    }

    private function handleException(Throwable $e, string $resource): void
    {
        if ($e instanceof PDOException) {
            Response::error('Внутренняя ошибка сервера: ' . $e->getMessage(), 500);
            return;
        }
        $code = method_exists($e, 'getCode') ? (int) $e->getCode() : 0;
        Response::error($e->getMessage(), ($code >= 400 && $code < 600) ? $code : 400);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return $_POST;
        }

        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        parse_str($raw, $data);
        return is_array($data) ? $data : [];
    }
}
