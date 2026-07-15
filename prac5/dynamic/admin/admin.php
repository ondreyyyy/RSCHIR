<?php
require_once __DIR__ . '/../bootstrap.php';

appStartSession();
$authUser = $_SERVER['PHP_AUTH_USER'] ?? $_SERVER['REMOTE_USER'] ?? null;
if ($authUser === 'admin') {
    $_SESSION['admin'] = true;
    $_SESSION['login'] = 'admin';
}

$preferences = appGetPreferences();
$ui = appUiText($preferences['language']);
$now = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($preferences['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($ui['adminTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
    <h1><?php echo htmlspecialchars($ui['adminTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
    <p><?php echo htmlspecialchars($ui['adminWelcome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($preferences['login'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($ui['adminStatus'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($now, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($ui['adminUser'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($preferences['login'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <p><?php echo htmlspecialchars($ui['adminTheme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <nav>
        <a href="/weather.php"><?php echo htmlspecialchars($ui['navWeather'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/uploads.php"><?php echo htmlspecialchars($ui['navPdf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/about.php"><?php echo htmlspecialchars($ui['navAbout'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/contacts.php"><?php echo htmlspecialchars($ui['navContacts'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/admin/admin.php"><?php echo htmlspecialchars($ui['navAdmin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
        <a href="/api.php"><?php echo htmlspecialchars($ui['apiLink'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>
    </nav>
</body>
</html>
