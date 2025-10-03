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

$stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY id");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ru">
<head><meta charset="utf-8"><title>Каталог — Магазин электроники</title></head>
<body>
  <h1>Каталог товаров</h1>
  <ul>
  <?php foreach ($products as $p): ?>
    <li>
      <a href="/product.php?id=<?php echo htmlspecialchars($p['id']); ?>">
        <?php echo htmlspecialchars($p['name']); ?>
      </a> — <?php echo htmlspecialchars($p['price']); ?> $
    </li>
  <?php endforeach; ?>
  </ul>
  <a href="/about.html">О нас (статично)</a>
</body>
</html>
