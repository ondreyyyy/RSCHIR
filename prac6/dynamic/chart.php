<?php
require_once __DIR__ . '/bootstrap.php';

$fileName = (string) ($_GET['file'] ?? '');
if ($fileName === '' || !preg_match('/^[a-zA-Z0-9_]+\.png$/', $fileName)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid file name.';
    exit;
}

$directory = appStorageRoot() . DIRECTORY_SEPARATOR . 'charts';
$fullPath = $directory . DIRECTORY_SEPARATOR . $fileName;

if (!is_file($fullPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Chart not found.';
    exit;
}

header('Content-Type: image/png');
header('Content-Length: ' . (string) filesize($fullPath));
readfile($fullPath);
exit;
