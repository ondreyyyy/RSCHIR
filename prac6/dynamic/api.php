<?php
require_once __DIR__ . '/bootstrap.php';

function jsonResponse($data, int $code = 200): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function textResponse(string $html, int $code = 200): void {
    header('Content-Type: text/html; charset=utf-8');
    http_response_code($code);
    echo $html;
    exit;
}

function errorResponse(string $message, int $code = 400): void {
    jsonResponse(['error' => $message], $code);
}


function requireAdminSession(): void {
    appStartSession();
    if (empty($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        errorResponse('Доступ запрещён.', 403);
    }
}

function parsePath(): array {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $path = '';

    if (stripos($requestUri, $scriptName) === 0) {
        $path = substr($requestUri, strlen($scriptName));
    } elseif (!empty($_SERVER['PATH_INFO'])) {
        $path = $_SERVER['PATH_INFO'];
    }

    $path = trim($path, '/');
    if ($path === '') {
        return ['', null];
    }

    $parts = explode('/', $path, 2);
    $resource = $parts[0];
    $id = $parts[1] ?? null;
    if ($id !== null) {
        $id = trim($id, '/');
    }
    return [$resource, $id];
}

function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return $_POST;
    }

    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        return $json;
    }

    parse_str($raw, $data);
    return is_array($data) ? $data : [];
}

function validateWeatherData(array $data): array {
    $errors = [];
    if (empty($data['city'])) {
        $errors[] = 'Поле city обязательно.';
    }
    if (!isset($data['temperature']) || !is_numeric($data['temperature'])) {
        $errors[] = 'Поле temperature должно быть числом.';
    }
    if (empty($data['description'])) {
        $errors[] = 'Поле description обязательно.';
    }
    if ($errors) {
        errorResponse(implode(' ', $errors), 422);
    }
    return [
        'city' => trim($data['city']),
        'temperature' => (float) $data['temperature'],
        'description' => trim($data['description']),
    ];
}

function validateUserData(array $data, bool $requirePassword = true): array {
    $errors = [];
    if (empty($data['username'])) {
        $errors[] = 'Поле username обязательно.';
    }
    if ($requirePassword && empty($data['password'])) {
        $errors[] = 'Поле password обязательно.';
    }
    if ($errors) {
        errorResponse(implode(' ', $errors), 422);
    }
    $validated = [
        'username' => trim($data['username']),
    ];
    if (isset($data['password']) && $data['password'] !== '') {
        $validated['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return $validated;
}

function getWeatherItems(?int $id = null) {
    $pdo = appGetPdo();
    if ($id === null) {
        $stmt = $pdo->query('SELECT id, city, temperature, description FROM weather ORDER BY id');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmt = $pdo->prepare('SELECT id, city, temperature, description FROM weather WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        errorResponse('Запись погоды не найдена.', 404);
    }
    return $item;
}

function createWeatherItem(array $data) {
    $validated = validateWeatherData($data);
    $pdo = appGetPdo();
    $stmt = $pdo->prepare('INSERT INTO weather (city, temperature, description) VALUES (:city, :temperature, :description)');
    $stmt->execute($validated);
    $id = (int) $pdo->lastInsertId();
    return getWeatherItems($id);
}

function updateWeatherItem(int $id, array $data) {
    $validated = validateWeatherData($data);
    $pdo = appGetPdo();
    $stmt = $pdo->prepare('UPDATE weather SET city = :city, temperature = :temperature, description = :description WHERE id = :id');
    $stmt->execute([
        ':city' => $validated['city'],
        ':temperature' => $validated['temperature'],
        ':description' => $validated['description'],
        ':id' => $id,
    ]);
    if ($stmt->rowCount() === 0) {
        errorResponse('Не удалось обновить запись погоды. Возможно, запись не найдена.', 404);
    }
    return getWeatherItems($id);
}

function deleteWeatherItem(int $id): array {
    $pdo = appGetPdo();
    $stmt = $pdo->prepare('SELECT id FROM weather WHERE id = :id');
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        errorResponse('Запись погоды не найдена.', 404);
    }
    $stmt = $pdo->prepare('DELETE FROM weather WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return ['message' => 'Запись погоды удалена.', 'id' => $id];
}

function getUsers(?int $id = null) {
    $pdo = appGetPdo();
    if ($id === null) {
        $stmt = $pdo->query('SELECT id, username FROM users ORDER BY id');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        errorResponse('Пользователь не найден.', 404);
    }
    return $user;
}

function createUser(array $data) {
    $validated = validateUserData($data, true);
    $pdo = appGetPdo();
    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->execute([
            ':username' => $validated['username'],
            ':password' => $validated['password'],
        ]);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) {
            errorResponse('Пользователь с таким username уже существует.', 409);
        }
        throw $e;
    }
    $id = (int) $pdo->lastInsertId();
    return getUsers($id);
}

function updateUser(int $id, array $data) {
    $validated = validateUserData($data, false);
    $pdo = appGetPdo();
    $fields = ['username' => $validated['username']];
    $params = [':username' => $validated['username'], ':id' => $id];
    $sql = 'UPDATE users SET username = :username';
    if (isset($validated['password'])) {
        $sql .= ', password = :password';
        $params[':password'] = $validated['password'];
    }
    $sql .= ' WHERE id = :id';
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) {
            errorResponse('Пользователь с таким username уже существует.', 409);
        }
        throw $e;
    }
    if ($stmt->rowCount() === 0) {
        errorResponse('Не удалось обновить пользователя. Возможно, он не найден.', 404);
    }
    return getUsers($id);
}

function deleteUser(int $id): array {
    $pdo = appGetPdo();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        errorResponse('Пользователь не найден.', 404);
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return ['message' => 'Пользователь удалён.', 'id' => $id];
}

function renderApiPage(array $preferences, string $language): void {
    $ui = appUiText($language);
    $html = '<!DOCTYPE html><html lang="' . htmlspecialchars($language, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-theme="' . htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"><head><meta charset="UTF-8"><title>' . htmlspecialchars($ui['apiTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</title><link rel="stylesheet" href="/static/style.css"></head><body data-theme="' . htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
    $html .= '<h1>' . htmlspecialchars($ui['apiTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h1>';
    $html .= '<p>' . htmlspecialchars($ui['apiIntro'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' <code>/api.php/weather</code>, <code>/api.php/users</code> и <code>/api.php/uploads</code>.</p>';
    $html .= '<h2>' . htmlspecialchars($ui['apiWeatherHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2><ul>';
    $html .= '<li>GET /api.php/weather — ' . htmlspecialchars($language === 'en' ? 'list all records' : 'список всех записей', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>GET /api.php/weather/{id} — ' . htmlspecialchars($language === 'en' ? 'get record by id' : 'получить запись по id', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>POST /api.php/weather — ' . htmlspecialchars($language === 'en' ? 'create record' : 'создать запись', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>PUT /api.php/weather/{id} — ' . htmlspecialchars($language === 'en' ? 'update record' : 'обновить запись', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>DELETE /api.php/weather/{id} — ' . htmlspecialchars($language === 'en' ? 'delete record' : 'удалить запись', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '</ul><h2>' . htmlspecialchars($ui['apiUsersHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2><ul>';
    $html .= '<li>GET /api.php/users — ' . htmlspecialchars($language === 'en' ? 'list users (without passwords)' : 'список пользователей (без паролей)', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>GET /api.php/users/{id} — ' . htmlspecialchars($language === 'en' ? 'get user' : 'получить пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>POST /api.php/users — ' . htmlspecialchars($language === 'en' ? 'create user' : 'создать пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>PUT /api.php/users/{id} — ' . htmlspecialchars($language === 'en' ? 'update user' : 'обновить пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>DELETE /api.php/users/{id} — ' . htmlspecialchars($language === 'en' ? 'delete user' : 'удалить пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '</ul><h2>' . htmlspecialchars($ui['apiPdfHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2><ul>';
    $html .= '<li>GET /api.php/uploads — ' . htmlspecialchars($language === 'en' ? 'list uploaded PDF files' : 'список загруженных PDF', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '<li>GET /api.php/uploads/{id} — ' . htmlspecialchars($language === 'en' ? 'PDF metadata' : 'метаданные PDF', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    $html .= '</ul><p>' . htmlspecialchars($ui['apiNote'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p><pre>{"city":"Казань","temperature":20.5,"description":"Солнечно"}</pre>';
    $html .= '<nav><a href="/weather.php">' . htmlspecialchars($ui['navWeather'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a> | <a href="/statistics.php">' . htmlspecialchars($ui['navStats'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a> | <a href="/uploads.php">' . htmlspecialchars($ui['navPdf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a> | <a href="/about.php">' . htmlspecialchars($ui['navAbout'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a> | <a href="/contacts.php">' . htmlspecialchars($ui['navContacts'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a> | <a href="/admin/admin.php">' . htmlspecialchars($ui['navAdmin'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a> | <a href="/api.php">' . htmlspecialchars($ui['apiLink'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a></nav>';
    $html .= '</body></html>';
    textResponse($html);
}

$method = $_SERVER['REQUEST_METHOD'];
[$resource, $idSegment] = parsePath();
requireAdminSession();
$id = null;
if ($idSegment !== null && $idSegment !== '') {
    if (!ctype_digit($idSegment)) {
        errorResponse('Идентификатор должен быть числом.', 400);
    }
    $id = (int) $idSegment;
}

$body = getRequestBody();

if ($resource === '' && $method === 'GET') {
    $preferences = appGetPreferences();
    renderApiPage($preferences, $preferences['language']);
}

try {
    switch ($resource) {
        case 'weather':
            switch ($method) {
                case 'GET':
                    jsonResponse($id === null ? getWeatherItems() : getWeatherItems($id));
                case 'POST':
                    jsonResponse(createWeatherItem($body), 201);
                case 'PUT':
                    if ($id === null) {
                        errorResponse('Идентификатор для обновления не указан.', 400);
                    }
                    jsonResponse(updateWeatherItem($id, $body));
                case 'DELETE':
                    if ($id === null) {
                        errorResponse('Идентификатор для удаления не указан.', 400);
                    }
                    jsonResponse(deleteWeatherItem($id));
                default:
                    errorResponse('Метод не поддерживается для ресурса weather.', 405);
            }
            break;
        case 'users':
            switch ($method) {
                case 'GET':
                    jsonResponse($id === null ? getUsers() : getUsers($id));
                case 'POST':
                    jsonResponse(createUser($body), 201);
                case 'PUT':
                    if ($id === null) {
                        errorResponse('Идентификатор для обновления не указан.', 400);
                    }
                    jsonResponse(updateUser($id, $body));
                case 'DELETE':
                    if ($id === null) {
                        errorResponse('Идентификатор для удаления не указан.', 400);
                    }
                    jsonResponse(deleteUser($id));
                default:
                    errorResponse('Метод не поддерживается для ресурса users.', 405);
            }
            break;
        default:
            errorResponse('Ресурс не найден. Используйте /api.php/weather или /api.php/users.', 404);
    }
} catch (PDOException $e) {
    errorResponse('Внутренняя ошибка сервера: ' . $e->getMessage(), 500);
}
