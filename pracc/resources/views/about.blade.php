@extends('layout')

@section('body')
<h1>{{ $ui['aboutTitle'] ?? 'О погоде' }}</h1>
<p>{{ $ui['aboutText'] ?? 'Этот сайт показывает погоду в разных городах, хранит настройки пользователя в cookie и использует Redis для сессий.' }}</p>
@endsection
