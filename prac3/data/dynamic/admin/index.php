<?php
declare(strict_types=1);

$user = $_SERVER['PHP_AUTH_USER'] ?? 'unknown';
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <title>Админка</title>
  </head>
  <body>
    <h1>Админка</h1>
    <p>Вы вошли как: <strong><?= htmlspecialchars($user, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <p><a href="/">На главную</a></p>
  </body>
</html>
