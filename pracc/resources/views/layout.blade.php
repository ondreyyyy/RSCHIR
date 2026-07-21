<!DOCTYPE html>
<html lang="{{ $preferences['language'] ?? 'ru' }}" data-theme="{{ $preferences['theme'] ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'WeatherApp' }}</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body data-theme="{{ $preferences['theme'] ?? 'light' }}">
    @yield('body')
    <nav>
        <a href="/weather">{{ $ui['navWeather'] ?? 'Погода' }}</a> |
        <a href="/statistics">{{ $ui['navStats'] ?? 'Статистика' }}</a> |
        <a href="/uploads">{{ $ui['navPdf'] ?? 'PDF-файлы' }}</a> |
        <a href="/about">{{ $ui['navAbout'] ?? 'О погоде' }}</a> |
        <a href="/contacts">{{ $ui['navContacts'] ?? 'Контакты' }}</a> |
        <a href="/admin">{{ $ui['navAdmin'] ?? 'Админка' }}</a>
        @if(($preferences['login'] ?? '') === 'admin')
            | <a href="/api">{{ $ui['apiLink'] ?? 'API' }}</a>
        @endif
    </nav>
</body>
</html>
