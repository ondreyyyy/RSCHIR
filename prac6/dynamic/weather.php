<?php
require_once __DIR__ . '/bootstrap.php';

appHandlePreferenceForm();

$preferences = appGetPreferences();
$ui = appUiText($preferences['language']);
$latestRows = appFetchLatestPerCity();
$rows = appWeatherRowsLocalized($preferences['language'], $latestRows);
$now = date('Y-m-d H:i:s');

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($preferences['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
<head>
	<meta charset="UTF-8">
	<title><?php echo htmlspecialchars($ui['weatherTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
	<link rel="stylesheet" href="/static/style.css">
</head>
<body data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
	<h1><?php echo htmlspecialchars($ui['weatherTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
	<p><?php echo htmlspecialchars($ui['weatherIntro'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($now, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>

	<table border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th><?php echo htmlspecialchars($ui['weatherCityHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th>
			<th><?php echo htmlspecialchars($ui['weatherTempHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th>
			<th><?php echo htmlspecialchars($ui['weatherDescHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th>
			<th><?php echo htmlspecialchars($ui['weatherHumidityHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th>
			<th><?php echo htmlspecialchars($ui['weatherPressureHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th>
			<th><?php echo htmlspecialchars($ui['weatherDateHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th>
		</tr>
		<?php foreach ($rows as $row): ?>
			<tr>
				<td><?php echo htmlspecialchars($row['city'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
				<td><?php echo htmlspecialchars((string) $row['temperature'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>°C</td>
				<td><?php echo htmlspecialchars($row['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
				<td><?php echo htmlspecialchars((string) ($row['humidity'] ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>%</td>
				<td><?php echo htmlspecialchars((string) ($row['pressure'] ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> hPa</td>
				<td><?php echo htmlspecialchars((string) ($row['recorded_at'] ?? 'N/A'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<form method="post">
		<input type="hidden" name="action" value="preferences">
		<label><?php echo htmlspecialchars($ui['loginLabel'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>: <input type="text" name="login" value="<?php echo htmlspecialchars($preferences['login'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"></label>
		<label><?php echo htmlspecialchars($ui['languageLabel'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>:
			<select name="language">
				<option value="ru"<?php echo $preferences['language'] === 'ru' ? ' selected' : ''; ?>>Русский</option>
				<option value="en"<?php echo $preferences['language'] === 'en' ? ' selected' : ''; ?>>English</option>
			</select>
		</label>
		<label><?php echo htmlspecialchars($ui['themeLabel'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>:
			<select name="theme">
				<option value="light"<?php echo $preferences['theme'] === 'light' ? ' selected' : ''; ?>>Light</option>
				<option value="dark"<?php echo $preferences['theme'] === 'dark' ? ' selected' : ''; ?>>Dark</option>
			</select>
		</label>
		<button type="submit"><?php echo htmlspecialchars($ui['saveButton'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></button>
	</form>

	<nav>
		<a href="/weather.php"><?php echo htmlspecialchars($ui['navWeather'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/statistics.php"><?php echo htmlspecialchars($ui['navStats'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/uploads.php"><?php echo htmlspecialchars($ui['navPdf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/about.php"><?php echo htmlspecialchars($ui['navAbout'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/contacts.php"><?php echo htmlspecialchars($ui['navContacts'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a> |
		<a href="/admin/admin.php"><?php echo htmlspecialchars($ui['navAdmin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>
	</nav>
</body>
</html>
