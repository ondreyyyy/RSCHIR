<?php

function executeCommand($command) {
    if (!function_exists('shell_exec')) {
        return "shell_exec недоступен";
    }
    
    $output = shell_exec($command . ' 2>&1');
    return htmlspecialchars($output ?: "Нет вывода");
}

function getCurrentUser() {
    $user = executeCommand('whoami');
    return trim($user);
}

?>