@extends('layout')

@section('body')
<h1>{{ $ui['adminTitle'] ?? 'Админ-панель' }}</h1>
<p>{{ $ui['adminWelcome'] ?? 'Добро пожаловать,' }} {{ $preferences['login'] ?? 'гость' }}</p>
<p>{{ $ui['adminStatus'] ?? 'Сгенерировано сервером:' }} {{ $now ?? '' }}</p>
<p>{{ $ui['adminUser'] ?? 'Пользователь:' }} {{ $preferences['login'] ?? 'гость' }}</p>
<p>{{ $ui['adminTheme'] ?? 'Тема:' }} {{ $preferences['theme'] ?? 'light' }}</p>
@endsection
