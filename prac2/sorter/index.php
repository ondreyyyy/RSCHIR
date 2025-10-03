<?php
require_once 'sorting.php';

$input = isset($_GET['array']) ? $_GET['array'] : '';
$array = parseInput($input);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sorter</title>
</head>
<body>
    <h1>Sorter</h1>
    <h2>Вариант 3 - Сортировка Слиянием</h2>
    
    <form method="GET">
        <label>Массив (через запятую): </label>
        <input type="text" name="array" value="<?php echo htmlspecialchars($input); ?>" size="30" placeholder="1,2,3,4,5,6">
        <input type="submit" value="Сортировать">
    </form>

    <?php if ($array !== null): ?>
        <h2>Результат:</h2>
        <p>Исходный массив: [<?php echo implode(', ', $array); ?>]</p>
        <?php $sorted = mergeSort($array); ?>
        <p>Отсортированный массив: [<?php echo implode(', ', $sorted); ?>]</p>
    <?php else: ?>
        <p style="color: red;">Ошибка: проверьте корректность вводимых данных</p>
    <?php endif; ?>

    <br>
    <a href="../">На главную</a>
</body>
</html>