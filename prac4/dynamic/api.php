<?php
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

function getPdo(): PDO {
    static $pdo;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=db;dbname=weather;charset=utf8mb4', 'weatheruser', 'weatherpass', [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
        ]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

function requireAdminSession(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
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
    $pdo = getPdo();
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
    $pdo = getPdo();
    $stmt = $pdo->prepare('INSERT INTO weather (city, temperature, description) VALUES (:city, :temperature, :description)');
    $stmt->execute($validated);
    $id = (int) $pdo->lastInsertId();
    return getWeatherItems($id);
}

function updateWeatherItem(int $id, array $data) {
    $validated = validateWeatherData($data);
    $pdo = getPdo();
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
    $pdo = getPdo();
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
    $pdo = getPdo();
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
    $pdo = getPdo();
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
    $pdo = getPdo();
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
    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        errorResponse('Пользователь не найден.', 404);
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return ['message' => 'Пользователь удалён.', 'id' => $id];
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
    $html = '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>API погоды</title></head><body>';
    $html .= '<h1>Интерфейс API</h1>';
    $html .= '<p>Используйте JSON-запросы к <code>/api.php/weather</code> и <code>/api.php/users</code>.</p>';
    $html .= '<h2>Погода</h2>';
    $html .= '<ul>';
    $html .= '<li>GET /api.php/weather — список всех записей</li>';
    $html .= '<li>GET /api.php/weather/{id} — получить запись по id</li>';
    $html .= '<li>POST /api.php/weather — создать запись</li>';
    $html .= '<li>PUT /api.php/weather/{id} — обновить запись</li>';
    $html .= '<li>DELETE /api.php/weather/{id} — удалить запись</li>';
    $html .= '</ul>';
    $html .= '<h2>Пользователи</h2>';
    $html .= '<ul>';
    $html .= '<li>GET /api.php/users — список пользователей (без паролей)</li>';
    $html .= '<li>GET /api.php/users/{id} — получить пользователя</li>';
    $html .= '<li>POST /api.php/users — создать пользователя</li>';
    $html .= '<li>PUT /api.php/users/{id} — обновить пользователя</li>';
    $html .= '<li>DELETE /api.php/users/{id} — удалить пользователя</li>';
    $html .= '</ul>';
    $html .= '<p>Для POST/PUT отправляйте JSON. Пример для создания погоды:</p>';
    $html .= '<pre>{"city":"Казань","temperature":20.5,"description":"Солнечно"}</pre>';
    $html .= '</body></html>';
    textResponse($html);
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
