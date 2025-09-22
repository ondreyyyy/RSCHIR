<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mysqli = new mysqli("db", "user", "password", "appDB");
    $name = $mysqli->real_escape_string($_POST['name']);
    $surname = $mysqli->real_escape_string($_POST['surname']);
    $mysqli->query("INSERT INTO users (name, surname) VALUES ('$name', '$surname')");
    $mysqli->close();
    header("Location: index.php");
    exit;
}
?>
<html lang="en">
<head>
<title>Create User</title>
<link rel="stylesheet" href="style.css?v=1" type="text/css"/>
</head>
<body>
<h1>Добавить пользователя</h1>

<div class="nav-buttons">
    <a href="index.php">Список</a>
    <a href="update.php">Редактировать</a>
    <a href="delete.php">Удалить</a>
</div>

<form method="post">
    <label>Имя: <input type="text" name="name" required></label><br><br>
    <label>Фамилия: <input type="text" name="surname" required></label><br><br>
    <input type="submit" value="Добавить">
</form>
</body>
</html>

