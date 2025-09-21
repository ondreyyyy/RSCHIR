<?php
$mysqli = new mysqli("db", "user", "password", "appDB");
$user = null;

// если выбран пользователь через get загружаем его данные
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $mysqli->query("SELECT * FROM users WHERE ID=$id");
    $user = $result->fetch_assoc();
}

// обновление данных если форма отправлена (должно быть после загрузки данных пользователя)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $name = $mysqli->real_escape_string($_POST['name']);
    $surname = $mysqli->real_escape_string($_POST['surname']);
    $mysqli->query("UPDATE users SET name='$name', surname='$surname' WHERE ID=$id");
    header("Location: update.php");
    exit;
}
?>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать пользователя</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>
<h1>Редактирование пользователя</h1>

<div class="nav-buttons">
    <a href="index.php">Список</a>
    <a href="create.php">Добавить</a>
    <a href="delete.php">Удалить</a>
</div>

<?php if ($user): ?>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo $user['ID']; ?>">
        <label>Имя: <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required></label><br><br>
        <label>Фамилия: <input type="text" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required></label><br><br>
        <input type="submit" value="Изменить">
    </form>
    <p><a href="update.php">← Вернуться к списку</a></p>
<?php else: ?>
    <h2>Выберите пользователя для редактирования:</h2>
    <ul>
        <?php
        $result = $mysqli->query("SELECT * FROM users ORDER BY ID ASC");
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['name']} {$row['surname']} 
                  <a href='update.php?id={$row['ID']}'>Редактировать</a></li>";
        }
        ?>
    </ul>
<?php endif; ?>
</body>
</html>