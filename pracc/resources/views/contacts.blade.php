@extends('layout')

@section('body')
<h1>{{ $ui['contactsTitle'] ?? 'Контакты' }}</h1>
<p>{{ $ui['contactsText'] ?? 'Связаться с нами: weather@example.com' }}</p>
@endsection
