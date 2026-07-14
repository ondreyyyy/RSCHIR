<?php
header('Content-Type: text/html; charset=utf-8');
$pdo = new PDO('mysql:host=db;dbname=weather;charset=utf8mb4', 'weatheruser', 'weatherpass', [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
]);
$stmt = $pdo->query('SELECT * FROM weather');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Погода</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>
<?php
$now = date('Y-m-d H:i:s');
echo "<h1>Погода</h1>";
echo "<p>Сгенерировано сервером: " . htmlspecialchars($now, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>";
echo "<ul>";
while ($row = $stmt->fetch()) {
    $city = htmlspecialchars($row['city'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $temp = htmlspecialchars($row['temperature'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $desc = htmlspecialchars($row['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<li>' . $city . ': ' . $temp . '°C, ' . $desc . '</li>';
}
echo "</ul>";
?>
    <nav>
        <a href="/static/about.html">О погоде</a> |
        <a href="/static/contacts.html">Контакты</a> |
        <a href="/admin/admin.php">Админка</a>
    </nav>
</body>
</html>
