<?php
$host = 'db';
$db   = 'electronics';
$user = 'appuser';
$pass = 'apppass';
$dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo "DB connection error: " . $e->getMessage();
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) {
    http_response_code(404);
    echo "Товар не найден";
    exit;
}
?>
<!doctype html>
<html lang="ru">
<head><meta charset="utf-8"><title><?php echo htmlspecialchars($p['name']); ?></title></head>
<body>
  <h1><?php echo htmlspecialchars($p['name']); ?></h1>
  <p><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
  <p>Цена: <?php echo htmlspecialchars($p['price']); ?> $</p>
  <a href="/index.php">Назад в каталог</a>
</body>
</html>
