@extends('layout')

@section('body')
<h1>{{ $ui['statsTitle'] ?? 'Статистика' }}</h1>
<p>{{ $ui['statsTotal'] ?? 'Всего записей:' }} {{ count($rows ?? []) }}</p>

@if(!empty($chartUrls['bar']))
    <h2>{{ $ui['statsBarTitle'] ?? 'Средняя температура по городам' }}</h2>
    <img src="{{ $chartUrls['bar'] }}" alt="Bar chart">
@endif

@if(!empty($chartUrls['line']))
    <h2>{{ $ui['statsLineTitle'] ?? 'Динамика температуры Москвы' }}</h2>
    <img src="{{ $chartUrls['line'] }}" alt="Line chart">
@endif

@if(!empty($chartUrls['pie']))
    <h2>{{ $ui['statsPieTitle'] ?? 'Распределение по диапазонам температур' }}</h2>
    <img src="{{ $chartUrls['pie'] }}" alt="Pie chart">
@endif
@endsection
