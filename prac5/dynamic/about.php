<?php
require_once __DIR__ . '/bootstrap.php';

$preferences = appGetPreferences();
$ui = appUiText($preferences['language']);
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($preferences['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($ui['aboutTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
    <h1><?php echo htmlspecialchars($ui['aboutTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
    <p><?php echo htmlspecialchars($ui['aboutText'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <nav>
        <a href="/weather.php"><?php echo htmlspecialchars($ui['navWeather'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/uploads.php"><?php echo htmlspecialchars($ui['navPdf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/about.php"><?php echo htmlspecialchars($ui['navAbout'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/contacts.php"><?php echo htmlspecialchars($ui['navContacts'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/admin/admin.php"><?php echo htmlspecialchars($ui['navAdmin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>
    </nav>
</body>
</html>