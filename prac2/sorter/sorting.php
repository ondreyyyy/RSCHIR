<?php

function parseInput($input) {
    if (empty(trim($input))) return [];
    
    $elements = explode(',', $input);
    $array = [];
    
    foreach ($elements as $element) {
        $trimmed = trim($element);
        if (is_numeric($trimmed)) {
            $array[] = floatval($trimmed);
        } else {
            return null;
        }
    }
    
    return $array;
}

function mergeSort($array) {
    $length = count($array);
    if ($length <= 1) {
        return $array;
    }
    
    $mid = (int)($length / 2);
    $left = array_slice($array, 0, $mid);
    $right = array_slice($array, $mid);
    
    $left = mergeSort($left);
    $right = mergeSort($right);
    
    return merge($left, $right);
}

function merge($left, $right) {
    $result = [];
    $i = 0;
    $j = 0;
    
    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i];
            $i++;
        } else {
            $result[] = $right[$j];
            $j++;
        }
    }
    
    while ($i < count($left)) {
        $result[] = $left[$i];
        $i++;
    }
    
    while ($j < count($right)) {
        $result[] = $right[$j];
        $j++;
    }
    
    return $result;
}

?>