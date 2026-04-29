<?php
declare(strict_types=1);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Bad request';
    exit;
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: 'db', getenv('DB_NAME') ?: 'game_profiles');
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'apppass';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $st = $pdo->prepare('SELECT id, nickname, game, level, created_at FROM profiles WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'DB error';
    exit;
}
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <title><?= htmlspecialchars($p['nickname'], ENT_QUOTES, 'UTF-8') ?></title>
  </head>
  <body>
    <h1><?= htmlspecialchars($p['nickname'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Игра: <?= htmlspecialchars($p['game'], ENT_QUOTES, 'UTF-8') ?></p>
    <p>Уровень: <?= (int)$p['level'] ?></p>
    <p>Создан: <?= htmlspecialchars($p['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
    <p><a href="/">Назад</a></p>
  </body>
</html>
