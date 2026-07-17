<?php
require_once __DIR__ . '/bootstrap.php';

$fileName = (string) ($_GET['file'] ?? '');
if ($fileName === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Не указан PDF-файл.';
    exit;
}

try {
    appSendPdfFile($fileName);
} catch (Throwable $exception) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo $exception->getMessage();
}
