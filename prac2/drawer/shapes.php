<?php

function drawShape($code) {  
    $shapeType = $code & 0x3;        // 2 бита - тип фигуры 
    $colorCode = ($code >> 2) & 0x7; // 3 бита - цвет 
    $size = (($code >> 5) & 0x7) + 1; // 3 бита - размер

    $colors = ['red', 'green', 'blue', 'yellow', 'purple', 'cyan', 'orange', 'gray'];
    $color = $colors[$colorCode];

    // SVG с фиксированной областью
    $svg = '<svg width="300" height="300" viewBox="0 0 100 100">';

    // Базовый масштаб (размер растёт пропорционально size)
    $scale = 0.5 + $size * 0.1;

    switch ($shapeType) {
        case 0: // круг
            $radius = 20 * $scale;
            $svg .= '<circle cx="50" cy="50" r="'.$radius.'" fill="'.$color.'"/>';
            break;

        case 1: // квадрат
            $side = 40 * $scale;
            $x = 50 - $side / 2;
            $y = 50 - $side / 2;
            $svg .= '<rect x="'.$x.'" y="'.$y.'" width="'.$side.'" height="'.$side.'" fill="'.$color.'"/>';
            break;

        case 2: // треугольник
            $side = 45 * $scale;
            $h = $side * sqrt(3) / 2;
            $points = 
                (50).",".(50 - $h/2)." ".
                (50 - $side/2).",".(50 + $h/2)." ".
                (50 + $side/2).",".(50 + $h/2);
            $svg .= '<polygon points="'.$points.'" fill="'.$color.'"/>';
            break;

        case 3: // звезда
            $points = starPoints(50, 50, 30, 15, 5);
            $svg .= '<polygon points="'.$points.'" fill="'.$color.'" transform="scale('.$scale.') translate('.(50*(1/$scale -1)).','.(50*(1/$scale -1)).')"/>';
            break;
    }

    $svg .= '</svg>';
    return $svg;
}

function starPoints($cx, $cy, $outerR, $innerR, $numPoints) {
    $points = "";
    $angle = pi() / $numPoints;
    for ($i = 0; $i < 2 * $numPoints; $i++) {
        $r = ($i % 2 == 0) ? $outerR : $innerR;
        $x = $cx + $r * cos($i * $angle - pi() / 2);
        $y = $cy + $r * sin($i * $angle - pi() / 2);
        $points .= $x.",".$y." ";
    }
    return trim($points);
}

function displayShapeInfo($code) {
    $shapeType = $code & 0x3;
    $colorCode = ($code >> 2) & 0x7;
    $size = (($code >> 5) & 0x7) + 1;

    $shapeNames = ['Круг', 'Квадрат', 'Треугольник', 'Звезда'];
    $colorNames = ['Красный', 'Зеленый', 'Синий', 'Желтый', 'Фиолетовый', 'Голубой', 'Оранжевый', 'Серый'];

    echo "<p>Фигура: ".$shapeNames[$shapeType]."</p>";
    echo "<p>Цвет: ".$colorNames[$colorCode]."</p>";
    echo "<p>Размер: $size</p>";
    echo "<p>Биты: ".str_pad(decbin($code), 8, '0', STR_PAD_LEFT)."</p>";
}

?>
