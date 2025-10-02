<?php
require_once 'system_info.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
</head>
<body>
    <h1>System Admin</h1>

    <h2>Информация о системе:</h2>
    <p>ОС: <?php echo php_uname('s'); ?></p>
    <p>Пользователь: <?php echo getCurrentUser(); ?></p>
    <p>PHP версия: <?php echo phpversion(); ?></p>

    <h2>Команды:</h2>
    
    <h3>Файлы (ls):</h3>
    <pre><?php echo executeCommand('ls'); ?></pre>

    <h3>Процессы (ps aux):</h3>
    <pre><?php echo executeCommand('ps aux | head -10'); ?></pre>

    <h3>Пользователи (id):</h3>
    <pre><?php echo executeCommand('id'); ?></pre>

    <br>
    <a href="../">На главную</a>
</body>
</html>