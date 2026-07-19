<?php
declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function html(string $html, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    /**
     * @param mixed $data
     */
    public static function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function error(string $message, int $code = 400): void
    {
        self::json(['error' => $message], $code);
    }

    public static function notFound(string $message = 'Not Found'): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }

    public static function redirect(string $url, int $code = 302): void
    {
        header('Location: ' . $url, true, $code);
    }

    public static function image(string $path, string $mime = 'image/png'): void
    {
        if (!is_file($path)) {
            self::notFound('Файл не найден.');
            return;
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        readfile($path);
    }
}
