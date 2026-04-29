<?php
declare(strict_types=1);

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: 'db', getenv('DB_NAME') ?: 'game_profiles');
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: 'apppass';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $profiles = $pdo->query('SELECT id, nickname, game, level, created_at FROM profiles ORDER BY id ASC')->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>DB connection error</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    exit;
}
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <title>Игровые профили</title>
  </head>
  <body>
    <h1>Игровые профили</h1>
    <nav>
      <a href="/static/about.html">О проекте</a> |
      <a href="/static/contact.html">Контакты</a> |
      <a href="/admin/">Админка (Basic Auth)</a>
    </nav>
    <table border="1" cellpadding="6" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Ник</th>
          <th>Игра</th>
          <th>Уровень</th>
          <th>Создан</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($profiles as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><a href="/product.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['nickname'], ENT_QUOTES, 'UTF-8') ?></a></td>
          <td><?= htmlspecialchars($p['game'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= (int)$p['level'] ?></td>
          <td><?= htmlspecialchars($p['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </body>
</html>
