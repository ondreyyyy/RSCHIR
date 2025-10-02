<?php
require_once 'shapes.php';

$num = isset($_GET['num']) ? intval($_GET['num']) : 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>SVG Drawer</title>
</head>
<body>
    <h1>SVG Drawer</h1>
    
    <form method="GET">
        <label>Введите число (0-255): </label>
        <input type="number" name="num" value="<?php echo $num; ?>" min="0" max="255">
        <input type="submit" value="Нарисовать">
    </form>

    <?php if (isset($_GET['num'])): ?>
        <h2>Результат:</h2>
        <?php echo drawShape($num); ?>
        
        <h3>Информация о фигуре:</h3>
        <?php displayShapeInfo($num); ?>
    <?php endif; ?>

    <br>
    <a href="../">На главную</a>
</body>
</html>