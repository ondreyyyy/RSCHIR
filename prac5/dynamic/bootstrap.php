<?php
declare(strict_types=1);

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
    $stmt = appGetPdo()->query('SELECT id, city, temperature, description FROM weather ORDER BY id');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function appWeatherStats(array $weatherRows): array {
    if ($weatherRows === []) {
        return [
            'count' => 0,
            'min' => null,
            'max' => null,
            'average' => null,
        ];
    }

    $temperatures = array_map(static fn(array $row): float => (float) $row['temperature'], $weatherRows);

    return [
        'count' => count($weatherRows),
        'min' => min($temperatures),
        'max' => max($temperatures),
        'average' => round(array_sum($temperatures) / count($temperatures), 1),
    ];
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

function appTranslation(string $language): array {
    $translations = [
        'ru' => [
            'portalTitle' => 'Погодный портал',
            'intro' => 'Сайт показывает погоду, персонализирует контент по cookie и хранит сессию в Redis.',
            'preferencesTitle' => 'Настройки пользователя',
            'loginLabel' => 'Логин',
            'languageLabel' => 'Язык',
            'themeLabel' => 'Тема',
            'saveButton' => 'Сохранить настройки',
            'themeLight' => 'Светлая',
            'themeDark' => 'Тёмная',
            'weatherTitle' => 'Погода в городах',
            'uploadTitle' => 'PDF-файлы',
            'fileLabel' => 'PDF-файл',
            'availableFiles' => 'Доступные файлы',
            'uploadButton' => 'Загрузить PDF',
            'downloadButton' => 'Скачать',
            'cityHeader' => 'Город',
            'weatherHeader' => 'Погода',
            'greeting' => 'Добро пожаловать',
            'navWeather' => 'Weather',
            'navPdf' => 'PDF files',
            'navAbout' => 'About weather',
            'navContacts' => 'Contacts',
            'navAdmin' => 'Admin',
            'navApi' => 'API',
        ],
        'en' => [
            'portalTitle' => 'Weather portal',
            'intro' => 'The site shows weather, personalizes content with cookies, and keeps the session in Redis.',
            'preferencesTitle' => 'User preferences',
            'loginLabel' => 'Login',
            'languageLabel' => 'Language',
            'themeLabel' => 'Theme',
            'saveButton' => 'Save preferences',
            'themeLight' => 'Light',
            'themeDark' => 'Dark',
            'weatherTitle' => 'Weather by city',
            'uploadTitle' => 'PDF files',
            'fileLabel' => 'PDF file',
            'availableFiles' => 'Available files',
            'uploadButton' => 'Upload PDF',
            'downloadButton' => 'Download',
            'cityHeader' => 'City',
            'weatherHeader' => 'Weather',
            'greeting' => 'Welcome',
            'navWeather' => 'Weather',
            'navPdf' => 'PDF files',
            'navAbout' => 'About weather',
            'navContacts' => 'Contacts',
            'navAdmin' => 'Admin',
            'navApi' => 'API',
        ],
    ];

    return $translations[$language] ?? $translations['ru'];
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

function appHandleAdminLoginForm(): bool {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return false;
    }

    if (($_POST['action'] ?? '') !== 'admin-login') {
        return false;
    }

    appStartSession();
    $login = appNormalizeLogin($_POST['login'] ?? null);
    $password = (string) ($_POST['password'] ?? '');

    if ($login === 'admin' && appVerifyAdminPassword($login, $password)) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_login'] = $login;
        $_SESSION['login'] = 'admin';
        header('Location: /admin/admin.php');
        exit;
    }

    return true;
}

function appIsAdminAuthenticated(): bool {
    appStartSession();
    return !empty($_SESSION['admin']) && $_SESSION['admin'] === true && (($_SESSION['admin_login'] ?? '') === 'admin');
}

function appFormatBytes(int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }

    return round($bytes / (1024 * 1024), 1) . ' MB';
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
            'apiWeatherHeader' => 'Погода',
            'apiUsersHeader' => 'Пользователи',
            'apiPdfHeader' => 'PDF-файлы',
            'apiNote' => 'Для POST/PUT отправляйте JSON. Пример для создания погоды:',
            'greeting' => 'Добро пожаловать',
            'fileLabel'      => 'PDF-файл',
            'availableFiles' => 'Доступные файлы',
            'navWeather'     => 'Погода',
            'navPdf'         => 'PDF-файлы',
            'navAbout'       => 'О погоде',
            'navContacts'    => 'Контакты',
            'navAdmin'       => 'Админка'
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
            'apiWeatherHeader' => 'Weather',
            'apiUsersHeader' => 'Users',
            'apiPdfHeader' => 'PDF files',
            'apiNote' => 'Send JSON for POST/PUT. Example for creating weather:',
            'greeting' => 'Welcome',
            'fileLabel'      => 'PDF file',
            'availableFiles' => 'Available files',
            'navWeather'     => 'Weather',
            'navPdf'         => 'PDF files',
            'navAbout'       => 'About weather',
            'navContacts'    => 'Contacts',
            'navAdmin'       => 'Admin'
        ],
    ];

    return $texts[$language] ?? $texts['ru'];
}

function appWeatherRowsLocalized(string $language): array {
    $rows = appFetchWeatherRows();
    $maps = [
        'ru' => [
            'cities' => [
                'Москва' => 'Москва',
                'Санкт-Петербург' => 'Санкт-Петербург',
                'Новосибирск' => 'Новосибирск',
            ],
            'descriptions' => [
                'Ясно' => 'Ясно',
                'Облачно' => 'Облачно',
                'Дождь' => 'Дождь',
            ],
        ],
        'en' => [
            'cities' => [
                'Москва' => 'Moscow',
                'Санкт-Петербург' => 'Saint Petersburg',
                'Новосибирск' => 'Novosibirsk',
            ],
            'descriptions' => [
                'Ясно' => 'Clear',
                'Облачно' => 'Cloudy',
                'Дождь' => 'Rain',
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
        ];
    }

    return $localized;
}
