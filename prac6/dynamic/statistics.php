<?php
require_once __DIR__ . '/bootstrap.php';

appHandlePreferenceForm();
appEnsureFixtures(50);

$preferences = appGetPreferences();
$ui = appUiText($preferences['language']);
$rows = appFetchWeatherRows();
$charts = appBuildCharts($rows);

foreach ($charts as $chartPath) {
    appApplyWatermark($chartPath, 'MoiseevAM');
}

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($preferences['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
<head>
	<meta charset="UTF-8">
	<title><?php echo htmlspecialchars($ui['navStats'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
	<link rel="stylesheet" href="/static/style.css">
</head>
<body data-theme="<?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
	<h1><?php echo htmlspecialchars($ui['navStats'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
	<p><?php echo htmlspecialchars($preferences['language'] === 'en' ? 'Total records:' : 'Всего записей:', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo count($rows); ?></p>

	<?php if (!empty($charts['bar'])): ?>
		<h2><?php echo htmlspecialchars($preferences['language'] === 'en' ? 'Average temperature by city' : 'Средняя температура по городам', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
		<img src="/chart.php?file=<?php echo htmlspecialchars(basename($charts['bar']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="Bar chart">
	<?php endif; ?>

	<?php if (!empty($charts['line'])): ?>
		<h2><?php echo htmlspecialchars($preferences['language'] === 'en' ? 'Moscow temperature dynamics' : 'Динамика температуры Москвы', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
		<img src="/chart.php?file=<?php echo htmlspecialchars(basename($charts['line']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="Line chart">
	<?php endif; ?>

	<?php if (!empty($charts['pie'])): ?>
		<h2><?php echo htmlspecialchars($preferences['language'] === 'en' ? 'Temperature distribution' : 'Распределение по диапазонам температур', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
		<img src="/chart.php?file=<?php echo htmlspecialchars(basename($charts['pie']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="Pie chart">
	<?php endif; ?>

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
