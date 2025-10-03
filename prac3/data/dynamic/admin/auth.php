<?php
// auth.php - basic auth + проверка в БД - пароль хранится как sha256
$host = 'db';
$db   = 'electronics';
$user = 'appuser';
$pass = 'apppass';
$dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "DB connection error";
    exit;
}

// получение заголовка authorization
$auth = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $hdrs = apache_request_headers();
    if (isset($hdrs['Authorization'])) $auth = $hdrs['Authorization'];
}

if (!$auth || stripos($auth, 'basic ') !== 0) {
    header('WWW-Authenticate: Basic realm="Admin area"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Требуется авторизация";
    exit;
}

$cred = base64_decode(substr($auth, 6));
list($username, $password) = array_pad(explode(':', $cred, 2), 2, '');

if ($username === '' || $password === '') {
    header('WWW-Authenticate: Basic realm="Admin area"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Неверные данные";
    exit;
}

// проверка в бд: сравниваем sha257(password) с password_hash (hex)
$stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE username = ? AND password_hash = UPPER(SHA2(?,256))");
$stmt->execute([$username, $password]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    header('WWW-Authenticate: Basic realm="Admin area"');
    header('HTTP/1.0 401 Unauthorized');
    echo "Неверный логин/пароль";
    exit;
}

// успешная авторизация — включаем админ-панель
// чтобы показать админ панель перенаправим на index.php в папке admin, но чтобы не создавать переадресацию в бесконечный цикл, просто include index.php
require __DIR__ . '/index.php';
