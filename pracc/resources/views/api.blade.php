@extends('layout')

@section('body')
<h1>{{ $ui['apiTitle'] ?? 'Интерфейс API' }}</h1>
<p>{{ $ui['apiIntro'] ?? 'Используйте JSON-запросы к' }} <code>/api/weather</code>, <code>/api/users</code> {{ $ui['apiAnd'] ?? 'и' }} <code>/api/uploads</code>.</p>
<h2>{{ $ui['apiWeatherHeader'] ?? 'Погода' }}</h2><ul>
    <li>GET /api/weather — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'list all records' : 'список всех записей' }}</li>
    <li>GET /api/weather/{id} — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'get record by id' : 'получить запись по id' }}</li>
    <li>POST /api/weather — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'create record' : 'создать запись' }}</li>
    <li>PUT /api/weather/{id} — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'update record' : 'обновить запись' }}</li>
    <li>DELETE /api/weather/{id} — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'delete record' : 'удалить запись' }}</li>
</ul>
<h2>{{ $ui['apiUsersHeader'] ?? 'Пользователи' }}</h2><ul>
    <li>GET /api/users — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'list users (without passwords)' : 'список пользователей (без паролей)' }}</li>
    <li>GET /api/users/{id} — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'get user' : 'получить пользователя' }}</li>
    <li>POST /api/users — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'create user' : 'создать пользователя' }}</li>
    <li>PUT /api/users/{id} — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'update user' : 'обновить пользователя' }}</li>
    <li>DELETE /api/users/{id} — {{ ($preferences['language'] ?? 'ru') === 'en' ? 'delete user' : 'удалить пользователя' }}</li>
</ul>
<h2>{{ $ui['apiPdfHeader'] ?? 'PDF-файлы' }}</h2><ul>
    <li>GET /api/uploads — {{ ($preferences['language'] === 'en') ? 'list uploaded PDF files' : 'список загруженных PDF' }}</li>
    <li>GET /api/uploads/{id} — {{ ($preferences['language'] === 'en') ? 'PDF metadata' : 'метаданные PDF' }}</li>
    <li>DELETE /api/uploads/{id} — {{ ($preferences['language'] === 'en') ? 'delete PDF file' : 'удалить PDF-файл' }}</li>
</ul>
<p>{{ $ui['apiNote'] ?? 'Для POST/PUT отправляйте JSON. Пример для создания погоды:' }}</p><pre>{"city":"Казань","temperature":20.5,"description":"Ясно","humidity":60,"pressure":1010,"recorded_at":"2026-07-19"}</pre>
@endsection
