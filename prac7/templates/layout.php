<?php
/** @var array $preferences */
/** @var array $ui */
/** @var string $title */
/** @var string $body */
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($preferences['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
    <?php echo $body; ?>
    <nav>
        <a href="/weather.php"><?php echo htmlspecialchars($ui['navWeather'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
        <a href="/statistics.php"><?php echo htmlspecialchars($ui['navStats'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
        <a href="/uploads.php"><?php echo htmlspecialchars($ui['navPdf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
        <a href="/about.php"><?php echo htmlspecialchars($ui['navAbout'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
        <a href="/contacts.php"><?php echo htmlspecialchars($ui['navContacts'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
        <a href="/admin/admin.php"><?php echo htmlspecialchars($ui['navAdmin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>
        <?php if (($preferences['login'] ?? '') === 'admin'): ?>
            | <a href="/api.php"><?php echo htmlspecialchars($ui['apiLink'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>
        <?php endif; ?>
    </nav>
</body>
</html>
