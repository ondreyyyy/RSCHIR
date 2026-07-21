@extends('layout')

@section('body')
<h1>{{ $ui['weatherTitle'] ?? 'Погода' }}</h1>
<p>{{ $ui['weatherIntro'] ?? 'Сгенерировано сервером:' }} {{ $now ?? '' }}</p>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>{{ $ui['weatherCityHeader'] ?? 'Город' }}</th>
        <th>{{ $ui['weatherTempHeader'] ?? 'Температура' }}</th>
        <th>{{ $ui['weatherDescHeader'] ?? 'Описание' }}</th>
        <th>{{ $ui['weatherHumidityHeader'] ?? 'Влажность' }}</th>
        <th>{{ $ui['weatherPressureHeader'] ?? 'Давление' }}</th>
        <th>{{ $ui['weatherDateHeader'] ?? 'Дата' }}</th>
    </tr>
    @foreach(($rows ?? []) as $row)
        <tr>
            <td>{{ $row['city'] ?? '' }}</td>
            <td>{{ $row['temperature'] ?? '' }}°C</td>
            <td>{{ $row['description'] ?? '' }}</td>
            <td>{{ $row['humidity'] ?? 'N/A' }}%</td>
            <td>{{ $row['pressure'] ?? 'N/A' }} hPa</td>
            <td>{{ $row['recorded_at'] ?? 'N/A' }}</td>
        </tr>
    @endforeach
</table>

<form method="post">
    @csrf
    <input type="hidden" name="action" value="preferences">
    <label>{{ $ui['loginLabel'] ?? 'Логин' }}: <input type="text" name="login" value="{{ $preferences['login'] ?? 'гость' }}"></label>
    <label>{{ $ui['languageLabel'] ?? 'Язык' }}:
        <select name="language">
            <option value="ru"{{ ($preferences['language'] ?? 'ru') === 'ru' ? ' selected' : '' }}>Русский</option>
            <option value="en"{{ ($preferences['language'] ?? 'ru') === 'en' ? ' selected' : '' }}>English</option>
        </select>
    </label>
    <label>{{ $ui['themeLabel'] ?? 'Тема' }}:
        <select name="theme">
            <option value="light"{{ ($preferences['theme'] ?? 'light') === 'light' ? ' selected' : '' }}>Light</option>
            <option value="dark"{{ ($preferences['theme'] ?? 'light') === 'dark' ? ' selected' : '' }}>Dark</option>
        </select>
    </label>
    <button type="submit">{{ $ui['saveButton'] ?? 'Сохранить' }}</button>
</form>
@endsection
