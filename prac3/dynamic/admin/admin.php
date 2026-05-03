<?php
$now = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
</head>
<body>
    <h1>Админ-панель</h1>
    <p>Добро пожаловать, администратор!</p>
    <p>Сгенерировано сервером: <?php echo htmlspecialchars($now, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <a href="/weather.php">Погода</a> |
    <a href="/static/about.html">О погоде</a> |
    <a href="/static/contacts.html">Контакты</a>
</body>
</html>
