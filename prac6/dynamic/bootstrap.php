<?php
declare(strict_types=1);

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

function appStartSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (extension_loaded('redis')) {
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', 'tcp://redis:6379');
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_name('RSCHIRSESSID');
    session_start();
}

function appGetPdo(): PDO {
    static $pdo;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=db;dbname=weather;charset=utf8mb4', 'weatheruser', 'weatherpass', [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
        ]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

function appNormalizeLogin(?string $login): string {
    $login = trim(strip_tags((string) $login));
    return $login === '' ? 'гость' : $login;
}

function appNormalizeLanguage(?string $language): string {
    $language = strtolower(trim((string) $language));
    return in_array($language, ['ru', 'en'], true) ? $language : 'ru';
}

function appNormalizeTheme(?string $theme): string {
    $theme = strtolower(trim((string) $theme));
    return in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
}

function appPreferenceDefaults(): array {
    return [
        'login' => 'гость',
        'language' => 'ru',
        'theme' => 'light',
    ];
}

function appGetPreferences(): array {
    appStartSession();
    $defaults = appPreferenceDefaults();

    $preferences = [
        'login' => appNormalizeLogin($_SESSION['login'] ?? $_COOKIE['login'] ?? $defaults['login']),
        'language' => appNormalizeLanguage($_SESSION['language'] ?? $_COOKIE['language'] ?? $defaults['language']),
        'theme' => appNormalizeTheme($_SESSION['theme'] ?? $_COOKIE['theme'] ?? $defaults['theme']),
    ];

    $_SESSION['login'] = $preferences['login'];
    $_SESSION['language'] = $preferences['language'];
    $_SESSION['theme'] = $preferences['theme'];

    return $preferences;
}

function appSetPreferenceCookies(array $preferences): void {
    $expires = time() + (60 * 60 * 24 * 30);
    $cookieOptions = [
        'expires' => $expires,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    setcookie('login', $preferences['login'], $cookieOptions);
    setcookie('language', $preferences['language'], $cookieOptions);
    setcookie('theme', $preferences['theme'], $cookieOptions);
}

function appHandlePreferenceForm(): bool {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return false;
    }

    if (($_POST['action'] ?? '') !== 'preferences') {
        return false;
    }

    appStartSession();
    $preferences = [
        'login' => appNormalizeLogin($_POST['login'] ?? null),
        'language' => appNormalizeLanguage($_POST['language'] ?? null),
        'theme' => appNormalizeTheme($_POST['theme'] ?? null),
    ];

    if (empty($preferences['login']) || $preferences['login'] !== 'admin') {
        appClearAdminSession();
    }

    $_SESSION['login'] = $preferences['login'];
    $_SESSION['language'] = $preferences['language'];
    $_SESSION['theme'] = $preferences['theme'];
    appSetPreferenceCookies($preferences);

    $redirectTo = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: ' . $redirectTo);
    exit;
}

function appClearAdminSession(): void {
    appStartSession();
    unset($_SESSION['admin']);
    unset($_SESSION['admin_login']);
}

function appFetchWeatherRows(): array {
    $stmt = appGetPdo()->query('SELECT id, city, temperature, description, humidity, pressure, recorded_at FROM weather ORDER BY id');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function appStorageRoot(): string {
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';
}

function appPdfStorageDir(): string {
    return appStorageRoot() . DIRECTORY_SEPARATOR . 'pdfs';
}

function appEnsurePdfStorageDir(): string {
    $directory = appPdfStorageDir();
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return $directory;
}

function appSafePdfFileName(string $fileName): string {
    $fileName = basename($fileName);
    if ($fileName === '' || !preg_match('/\.pdf$/i', $fileName)) {
        throw new RuntimeException('Разрешены только PDF-файлы.');
    }

    if (str_contains($fileName, '..')) {
        throw new RuntimeException('Некорректное имя файла.');
    }

    return $fileName;
}

function appStorePdfUpload(array $file): array {
    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Не удалось загрузить файл.');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Временный файл не найден.');
    }

    $originalName = (string) ($file['name'] ?? 'document.pdf');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        throw new RuntimeException('Можно загружать только PDF-файлы.');
    }

    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $baseName) ?? 'document';
    $baseName = trim($baseName, '_');
    if ($baseName === '') {
        $baseName = 'document';
    }

    $storedName = date('Ymd_His') . '_' . $baseName . '.pdf';
    $targetPath = appEnsurePdfStorageDir() . DIRECTORY_SEPARATOR . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Не удалось сохранить PDF-файл.');
    }

    return [
        'stored_name' => $storedName,
        'original_name' => $originalName,
        'size' => (int) ($file['size'] ?? 0),
        'path' => $targetPath,
    ];
}

function appListPdfFiles(): array {
    $directory = appEnsurePdfStorageDir();
    $files = glob($directory . DIRECTORY_SEPARATOR . '*.pdf') ?: [];
    sort($files);

    $result = [];
    foreach ($files as $filePath) {
        $result[] = [
            'name' => basename($filePath),
            'size' => filesize($filePath) ?: 0,
            'modified' => filemtime($filePath) ?: time(),
        ];
    }

    return $result;
}

function appSendPdfFile(string $fileName): void {
    $safeName = appSafePdfFileName($fileName);
    $directory = appEnsurePdfStorageDir();
    $fullPath = $directory . DIRECTORY_SEPARATOR . $safeName;

    if (!is_file($fullPath)) {
        throw new RuntimeException('PDF-файл не найден.');
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . rawurlencode($safeName) . '"');
    header('Content-Length: ' . (string) filesize($fullPath));
    readfile($fullPath);
    exit;
}

function appVerifyAdminPassword(string $login, string $password): bool {
    $stmt = appGetPdo()->prepare('SELECT username, password FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $login]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return false;
    }

    $storedPassword = (string) $row['password'];
    if (str_starts_with($storedPassword, '{SHA}')) {
        $expected = substr($storedPassword, 5);
        return base64_encode(sha1($password, true)) === $expected;
    }

    return password_verify($password, $storedPassword);
}

function appUiText(string $language): array {
    $texts = [
        'ru' => [
            'weatherTitle' => 'Погода',
            'weatherIntro' => 'Сгенерировано сервером:',
            'loginLabel' => 'Логин',
            'languageLabel' => 'Язык',
            'themeLabel' => 'Тема',
            'saveButton' => 'Сохранить',
            'homeLink' => 'Главная',
            'weatherLink' => 'Погода',
            'uploadsLink' => 'PDF-файлы',
            'aboutLink' => 'О погоде',
            'contactsLink' => 'Контакты',
            'adminLink' => 'Админка',
            'apiLink' => 'API',
            'uploadTitle' => 'Загрузка PDF',
            'uploadHint' => 'PDF-файлы сохраняются в файловой системе сервера и отдаются по отдельной ссылке.',
            'uploadEmpty' => 'Пока нет загруженных PDF-файлов.',
            'uploadButton' => 'Загрузить PDF',
            'downloadButton' => 'Скачать',
            'fileLabel'      => 'PDF-файл',
            'availableFiles' => 'Доступные файлы',
            'chooseFile' => 'Выберите файл',
            'noFileChosen' => 'Файл не выбран',
            'aboutTitle' => 'О погоде',
            'aboutText' => 'Этот сайт показывает погоду в разных городах, хранит настройки пользователя в cookie и использует Redis для сессий.',
            'contactsTitle' => 'Контакты',
            'contactsText' => 'Связаться с нами: weather@example.com',
            'adminTitle' => 'Админ-панель',
            'adminWelcome' => 'Добро пожаловать,',
            'adminStatus' => 'Сгенерировано сервером:',
            'adminUser' => 'Пользователь:',
            'adminTheme' => 'Тема:',
            'adminLoginPrompt' => 'Войдите, чтобы открыть админку.',
            'passwordLabel' => 'Пароль',
            'enterButton' => 'Войти',
            'invalidCredentials' => 'Неверный логин или пароль.',
            'apiTitle' => 'Интерфейс API',
            'apiIntro' => 'Используйте JSON-запросы к',
            'apiAnd' => 'и',
            'apiOr' => 'или',
            'apiWeatherHeader' => 'Погода',
            'apiUsersHeader' => 'Пользователи',
            'apiPdfHeader' => 'PDF-файлы',
            'apiNote' => 'Для POST/PUT отправляйте JSON. Пример для создания погоды:',
            'greeting' => 'Добро пожаловать',
            'navWeather'     => 'Погода',
            'navPdf'         => 'PDF-файлы',
            'navAbout'       => 'О погоде',
            'navContacts'    => 'Контакты',
            'navAdmin'       => 'Админка',
            'navStats'       => 'Статистика',
            'weatherCityHeader' => 'Город',
            'weatherTempHeader' => 'Температура',
            'weatherDescHeader' => 'Описание',
            'weatherHumidityHeader' => 'Влажность',
            'weatherPressureHeader' => 'Давление',
            'weatherDateHeader' => 'Дата',
        ],
        'en' => [
            'weatherTitle' => 'Weather',
            'weatherIntro' => 'Generated by the server:',
            'loginLabel' => 'Login',
            'languageLabel' => 'Language',
            'themeLabel' => 'Theme',
            'saveButton' => 'Save',
            'homeLink' => 'Home',
            'weatherLink' => 'Weather',
            'uploadsLink' => 'PDF files',
            'aboutLink' => 'About weather',
            'contactsLink' => 'Contacts',
            'adminLink' => 'Admin',
            'apiLink' => 'API',
            'uploadTitle' => 'PDF upload',
            'uploadHint' => 'PDF files are stored on the server filesystem and served via a separate link.',
            'uploadEmpty' => 'No PDF files uploaded yet.',
            'uploadButton' => 'Upload PDF',
            'downloadButton' => 'Download',
            'fileLabel'      => 'PDF file',
            'availableFiles' => 'Available files',
            'chooseFile' => 'Choose file',
            'noFileChosen' => 'No file chosen',
            'aboutTitle' => 'About weather',
            'aboutText' => 'This site shows weather in different cities, stores user preferences in cookies, and uses Redis for sessions.',
            'contactsTitle' => 'Contacts',
            'contactsText' => 'Contact us: weather@example.com',
            'adminTitle' => 'Admin panel',
            'adminWelcome' => 'Welcome,',
            'adminStatus' => 'Generated by the server:',
            'adminUser' => 'User:',
            'adminTheme' => 'Theme:',
            'adminLoginPrompt' => 'Sign in to open the admin panel.',
            'passwordLabel' => 'Password',
            'enterButton' => 'Sign in',
            'invalidCredentials' => 'Invalid username or password.',
            'apiTitle' => 'API interface',
            'apiIntro' => 'Use JSON requests to',
            'apiAnd' => 'and',
            'apiOr' => 'or',
            'apiWeatherHeader' => 'Weather',
            'apiUsersHeader' => 'Users',
            'apiPdfHeader' => 'PDF files',
            'apiNote' => 'Send JSON for POST/PUT. Example for creating weather:',
            'greeting' => 'Welcome',
            'navWeather'     => 'Weather',
            'navPdf'         => 'PDF files',
            'navAbout'       => 'About weather',
            'navContacts'    => 'Contacts',
            'navAdmin'       => 'Admin',
            'navStats'       => 'Statistics',
            'weatherCityHeader' => 'City',
            'weatherTempHeader' => 'Temperature',
            'weatherDescHeader' => 'Description',
            'weatherHumidityHeader' => 'Humidity',
            'weatherPressureHeader' => 'Pressure',
            'weatherDateHeader' => 'Date',
        ],
    ];

    return $texts[$language] ?? $texts['ru'];
}

function appWeatherRowsLocalized(string $language, ?array $rows = null): array {
    $rows = $rows ?? appFetchWeatherRows();
    $maps = [
        'ru' => [
            'cities' => [
                'Москва' => 'Москва',
                'Санкт-Петербург' => 'Санкт-Петербург',
                'Новосибирск' => 'Новосибирск',
                'Казань' => 'Казань',
            ],
            'descriptions' => [
                'Ясно' => 'Ясно',
                'Облачно' => 'Облачно',
                'Дождь' => 'Дождь',
                'Туман' => 'Туман',
                'Снег' => 'Снег',
            ],
        ],
        'en' => [
            'cities' => [
                'Москва' => 'Moscow',
                'Санкт-Петербург' => 'Saint Petersburg',
                'Новосибирск' => 'Novosibirsk',
                'Казань' => 'Kazan',
            ],
            'descriptions' => [
                'Ясно' => 'Clear',
                'Облачно' => 'Cloudy',
                'Дождь' => 'Rain',
                'Туман' => 'Fog',
                'Снег' => 'Snow',
            ],
        ],
    ];

    $map = $maps[$language] ?? $maps['ru'];
    $localized = [];
    foreach ($rows as $row) {
        $city = (string) $row['city'];
        $description = (string) $row['description'];
        $localized[] = [
            'id' => (int) $row['id'],
            'city' => $map['cities'][$city] ?? $city,
            'temperature' => $row['temperature'],
            'description' => $map['descriptions'][$description] ?? $description,
            'humidity' => $row['humidity'] ?? null,
            'pressure' => $row['pressure'] ?? null,
            'recorded_at' => $row['recorded_at'] ?? null,
        ];
    }

    return $localized;
}

function appFetchLatestPerCity(): array {
    $rows = appFetchWeatherRows();
    $latest = [];
    foreach ($rows as $row) {
        $city = (string) $row['city'];
        $recordedAt = (string) ($row['recorded_at'] ?? '');
        if (!isset($latest[$city]) || $recordedAt > ($latest[$city]['recorded_at'] ?? '')) {
            $latest[$city] = $row;
        }
    }

    return array_values($latest);
}

function appGenerateFixtures(int $count = 50): void {
    if (!class_exists(\Faker\Generator::class)) {
        return;
    }

    $pdo = appGetPdo();
    $faker = \Faker\Factory::create('ru_RU');
    $cities = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Казань'];
    $descriptions = ['Ясно', 'Облачно', 'Дождь', 'Снег', 'Туман'];
    $stmt = $pdo->prepare('INSERT INTO weather (city, temperature, description, humidity, pressure, recorded_at) VALUES (:city, :temperature, :description, :humidity, :pressure, :recorded_at)');

    for ($i = 0; $i < $count; $i++) {
        $stmt->execute([
            ':city' => $faker->randomElement($cities),
            ':temperature' => $faker->numberBetween(-10, 30),
            ':description' => $faker->randomElement($descriptions),
            ':humidity' => $faker->numberBetween(30, 95),
            ':pressure' => $faker->numberBetween(980, 1040),
            ':recorded_at' => $faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
        ]);
    }
}

function appEnsureFixtures(int $minCount = 50): void {
    $count = (int) appGetPdo()->query('SELECT COUNT(*) FROM weather')->fetchColumn();
    if ($count < $minCount) {
        appGenerateFixtures($minCount - $count);
    }
}

function appBuildCharts(array $rows): array {
    $storageDir = appStorageRoot() . DIRECTORY_SEPARATOR . 'charts';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0775, true);
    }

    foreach (glob($storageDir . DIRECTORY_SEPARATOR . '*.png') as $oldChart) {
        @unlink($oldChart);
    }

    $baseName = date('Ymd_His');
    $charts = [];

    $cityStats = [];
    $cityCounts = [];
    foreach ($rows as $row) {
        $city = (string) $row['city'];
        $temp = (float) $row['temperature'];
        $cityStats[$city] = ($cityStats[$city] ?? 0) + $temp;
        $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
    }
    $cities = array_keys($cityStats);
    $avgs = [];
    foreach ($cities as $city) {
        $avgs[] = $cityStats[$city] / $cityCounts[$city];
    }
    $labels = array_map(static fn(string $city): string => match ($city) {
        'Москва' => "Москва\n(Moscow)",
        'Санкт-Петербург' => "Санкт-Петербург\n(Saint Petersburg)",
        'Новосибирск' => "Новосибирск\n(Novosibirsk)",
        'Казань' => "Казань\n(Kazan)",
        default => $city,
    }, $cities);

    $barPath = $storageDir . DIRECTORY_SEPARATOR . $baseName . '_bar.png';
    appGdBarChart($labels, $avgs, $barPath);
    $charts['bar'] = $barPath;

    usort($rows, static fn(array $a, array $b): int => ($a['recorded_at'] ?? '') <=> ($b['recorded_at'] ?? ''));
    $temps = array_map(static fn(array $row): float => (float) $row['temperature'], $rows);
    $labels = array_map(static fn(array $row): string => substr((string) $row['recorded_at'], 5, 5), $rows);

    $moscowRows = array_values(array_filter($rows, static fn(array $row): bool => $row['city'] === 'Москва'));
    usort($moscowRows, static fn(array $a, array $b): int => ($a['recorded_at'] ?? '') <=> ($b['recorded_at'] ?? ''));
    $moscowTemps = array_map(static fn(array $row): float => (float) $row['temperature'], $moscowRows);
    $moscowLabels = array_map(static fn(array $row): string => substr((string) $row['recorded_at'], 5, 5), $moscowRows);

    $linePath = $storageDir . DIRECTORY_SEPARATOR . $baseName . '_line.png';
    appGdLineChart($moscowLabels, $moscowTemps, $linePath);
    $charts['line'] = $linePath;

    $ranges = ['< 0°C' => 0, '0-15°C' => 0, '15-25°C' => 0, '> 25°C' => 0];
    foreach ($rows as $row) {
        $temp = (float) $row['temperature'];
        if ($temp < 0) {
            $ranges['< 0°C']++;
        } elseif ($temp < 15) {
            $ranges['0-15°C']++;
        } elseif ($temp < 25) {
            $ranges['15-25°C']++;
        } else {
            $ranges['> 25°C']++;
        }
    }

    $piePath = $storageDir . DIRECTORY_SEPARATOR . $baseName . '_pie.png';
    appGdPieChart(array_keys($ranges), array_values($ranges), $piePath);
    $charts['pie'] = $piePath;

    return $charts;
}

function appGdBarChart(array $labels, array $values, string $path): void {
    $width = 800;
    $height = 700;
    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 200, 200, 200);
    $barColors = [
        imagecolorallocate($image, 255, 127, 14),
        imagecolorallocate($image, 31, 119, 180),
        imagecolorallocate($image, 44, 160, 44),
        imagecolorallocate($image, 214, 39, 40),
    ];

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);

    $padding = 80;
    $chartWidth = $width - $padding * 2;
    $chartHeight = $height - $padding * 2;
    $maxValue = max($values) ?: 1;
    $barWidth = (int) ($chartWidth / count($values) * 0.7);
    $spacing = (int) ($chartWidth / count($values) * 0.3);

    for ($i = 0; $i < count($values); $i++) {
        $barHeight = (int) (($values[$i] / $maxValue) * $chartHeight);
        $x = (int) ($padding + $i * ($barWidth + $spacing));
        $y = (int) ($height - $padding - $barHeight);
        imagefilledrectangle($image, $x, $y, $x + $barWidth, $height - $padding, $barColors[$i % count($barColors)]);
        imagerectangle($image, $x, $y, $x + $barWidth, $height - $padding, $black);
        appGdText($image, 12, $x + 5, (int) ($height - $padding + 5), (string) round($values[$i], 1), $black);
        $labelLines = explode("\n", $labels[$i]);
        foreach ($labelLines as $lineIndex => $line) {
            appGdText($image, 12, $x + 5, (int) ($height - $padding + 30 + $lineIndex * 20), $line, $black);
        }
    }

    imagerectangle($image, $padding, $padding, $width - $padding, $height - $padding, $gray);
    imagepng($image, $path);
    imagedestroy($image);
}

function appGdLineChart(array $labels, array $values, string $path): void {
    $width = 800;
    $height = 600;
    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $blue = imagecolorallocate($image, 31, 119, 180);
    $gray = imagecolorallocate($image, 200, 200, 200);

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);

    $padding = 60;
    $chartWidth = $width - $padding * 2;
    $chartHeight = $height - $padding * 2;
    $maxValue = max($values) ?: 1;
    $minValue = min($values);
    $range = $maxValue - $minValue ?: 1;
    $stepX = $chartWidth / max(1, count($values) - 1);

    for ($i = 0; $i < count($values); $i++) {
        $x = (int) ($padding + $i * $stepX);
        $y = (int) ($height - $padding - (($values[$i] - $minValue) / $range) * $chartHeight);
        if ($i > 0) {
            $prevX = (int) ($padding + ($i - 1) * $stepX);
            $prevY = (int) ($height - $padding - (($values[$i - 1] - $minValue) / $range) * $chartHeight);
            imageline($image, $prevX, $prevY, $x, $y, $blue);
        }
        imagefilledellipse($image, $x, $y, 6, 6, $blue);
        if ($i % max(1, (int) (count($values) / 10)) === 0) {
            appGdText($image, 8, $x - 10, (int) ($height - $padding + 5), $labels[$i], $black);
        }
    }

    imagerectangle($image, $padding, $padding, $width - $padding, $height - $padding, $gray);
    imagepng($image, $path);
    imagedestroy($image);
}

function appGdPieChart(array $labels, array $values, string $path): void {
    $width = 800;
    $height = 600;
    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $colors = [
        imagecolorallocate($image, 255, 127, 14),
        imagecolorallocate($image, 31, 119, 180),
        imagecolorallocate($image, 44, 160, 44),
        imagecolorallocate($image, 214, 39, 40),
    ];

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);

    $centerX = (int) ($width / 2);
    $centerY = (int) ($height / 2);
    $radius = (int) min($width, $height) / 3;
    $total = array_sum($values) ?: 1;
    $startAngle = 0;

    for ($i = 0; $i < count($values); $i++) {
        $sliceAngle = (int) (($values[$i] / $total) * 360);
        $endAngle = $startAngle + $sliceAngle;
        imagefilledarc($image, $centerX, $centerY, $radius * 2, $radius * 2, $startAngle, $endAngle, $colors[$i % count($colors)], IMG_ARC_PIE);
        $midAngle = deg2rad($startAngle + $sliceAngle / 2);
        $labelX = (int) ($centerX + cos($midAngle) * $radius * 0.6);
        $labelY = (int) ($centerY + sin($midAngle) * $radius * 0.6);
        appGdText($image, 12, $labelX - 10, $labelY - 10, $labels[$i], $black);
        $startAngle = $endAngle;
    }

    imagepng($image, $path);
    imagedestroy($image);
}

function appGdText(GdImage $image, int $size, int $x, int $y, string $text, int $color): void {
    static $fontFile = null;
    if ($fontFile === null) {
        $fontFile = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        if (!is_file($fontFile)) {
            $fontFile = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        }
    }
    if ($fontFile !== null && is_file($fontFile)) {
        imagettftext($image, $size, 0, $x, $y + $size, $color, $fontFile, $text);
    } else {
        imagestring($image, $size, $x, $y, $text, $color);
    }
}

function appApplyWatermark(string $imagePath, string $text): void {
    if (!is_file($imagePath) || !is_readable($imagePath)) {
        return;
    }

    $image = imagecreatefrompng($imagePath);
    if ($image === false) {
        return;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $fontFile = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
    if (!is_file($fontFile)) {
        $fontFile = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
    }
    $fontSize = 11;
    $box = imagettfbbox($fontSize, 0, $fontFile, $text);
    if ($box === false) {
        $color = imagecolorallocatealpha($image, 80, 80, 80, 40);
        imagestring($image, 5, max(0, $width - 80), max(0, $height - 20), $text, $color);
        imagepng($image, $imagePath);
        imagedestroy($image);
        return;
    }

    $textWidth = $box[2] - $box[0];
    $textHeight = $box[1] - $box[7];
    $x = $width - $textWidth - 10;
    $y = $height - $textHeight - 10;
    $color = imagecolorallocatealpha($image, 80, 80, 80, 40);
    imagettftext($image, $fontSize, 0, $x, $y, $color, $fontFile, $text);
    imagepng($image, $imagePath);
    imagedestroy($image);
}
