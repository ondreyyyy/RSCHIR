<?php
require_once __DIR__ . '/bootstrap.php';

appStartSession();
$preferences = appGetPreferences();
$ui = appUiText($preferences['language']);
$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    try {
        $storedFile = appStorePdfUpload($_FILES['pdf_file'] ?? []);
        $message = $preferences['language'] === 'en'
            ? 'PDF ' . $storedFile['original_name'] . ' saved as ' . $storedFile['stored_name'] . '.'
            : 'PDF ' . $storedFile['original_name'] . ' сохранён как ' . $storedFile['stored_name'] . '.';
    } catch (Throwable $exception) {
        $message = $exception->getMessage();
    }
}

$pdfFiles = appListPdfFiles();
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($preferences['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($ui['uploadTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
    <h1><?php echo htmlspecialchars($ui['uploadTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
    <p><?php echo htmlspecialchars($ui['uploadHint'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>

    <?php if ($message !== ''): ?>
        <p><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <p><label><?php echo htmlspecialchars($ui['fileLabel'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <input type="file" name="pdf_file" accept="application/pdf,.pdf" required></label></p>
        <p><button type="submit"><?php echo htmlspecialchars($ui['uploadButton'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></button></p>
    </form>

    <h2><?php echo htmlspecialchars($ui['availableFiles'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
    <ul>
        <?php foreach ($pdfFiles as $file): ?>
            <li>
                <?php echo htmlspecialchars($file['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                (<a href="/download.php?file=<?php echo rawurlencode($file['name']); ?>"><?php echo htmlspecialchars($ui['downloadButton'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>)
            </li>
        <?php endforeach; ?>
        <?php if ($pdfFiles === []): ?>
            <li><?php echo htmlspecialchars($ui['uploadEmpty'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
        <?php endif; ?>
    </ul>

    <nav>
		<a href="/weather.php"><?php echo htmlspecialchars($ui['navWeather'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/uploads.php"><?php echo htmlspecialchars($ui['navPdf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/about.php"><?php echo htmlspecialchars($ui['navAbout'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/contacts.php"><?php echo htmlspecialchars($ui['navContacts'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/admin/admin.php"><?php echo htmlspecialchars($ui['navAdmin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>
    </nav>
</body>
</html>
