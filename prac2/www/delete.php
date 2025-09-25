<?php
$mysqli = new mysqli("db", "user", "password", "appDB");

// удаление студента только если подтверждено
if (isset($_GET['id']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $id = (int)$_GET['id'];
    $mysqli->query("DELETE FROM users WHERE ID=$id");
    header("Location: delete.php");
    exit;
}

// если просто передан id показывать подтверждение
if (isset($_GET['id']) && !isset($_GET['confirm'])) {
    $id = (int)$_GET['id'];
    $result = $mysqli->query("SELECT * FROM users WHERE ID=$id");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        ?>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <title>Подтверждение удаления</title>
            <link rel='stylesheet' href='style.css' type='text/css'/>
        </head>
        <body>
        <h1>Подтверждение удаления</h1>
        <p>Вы действительно хотите удалить студента: <?php echo htmlspecialchars($user['name']) . ' ' . htmlspecialchars($user['surname']); ?>?</p>
        <a href='delete.php?id=<?php echo $id; ?>&confirm=yes'>Да, удалить</a> | 
        <a href='delete.php'>Нет, отмена</a>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Удалить студента</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>
<h1>Удаление студента</h1>

<div class="nav-buttons">
    <a href="index.php">Список</a>
    <a href="create.php">Добавить</a>
    <a href="update.php">Редактировать</a>
</div>

<h2>Выберите студента для удаления:</h2>
<ul>
    <?php
    $result = $mysqli->query("SELECT * FROM users ORDER BY ID ASC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['name']) . " " . htmlspecialchars($row['surname']) . 
                 " <a href='delete.php?id=" . $row['ID'] . "'>Удалить</a></li>";
        }
    } else {
        echo "<li>Нет студентов для удаления</li>";
    }
    ?>
</ul>
</body>

</html>

