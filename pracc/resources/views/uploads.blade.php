@extends('layout')

@section('body')
<h1>{{ $ui['uploadTitle'] ?? 'Загрузка PDF' }}</h1>
<p>{{ $ui['uploadHint'] ?? 'PDF-файлы сохраняются в файловой системе сервера и отдаются по отдельной ссылке.' }}</p>

@if(($message ?? '') !== '')
    <p>{{ $message }}</p>
@endif

<form method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="action" value="upload">
    <p>
        <label>{{ $ui['fileLabel'] ?? 'PDF-файл' }}
            <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf,.pdf" required style="display:none" onchange="document.getElementById('file_name').textContent = this.value ? this.value.split('\\').pop() : '{{ $ui['noFileChosen'] ?? 'Файл не выбран' }}'">
            <button type="button" onclick="document.getElementById('pdf_file').click()">{{ $ui['chooseFile'] ?? 'Выберите файл' }}</button>
            <span id="file_name">{{ $ui['noFileChosen'] ?? 'Файл не выбран' }}</span>
        </label>
    </p>
    <p><button type="submit">{{ $ui['uploadButton'] ?? 'Загрузить PDF' }}</button></p>
</form>

<h2>{{ $ui['availableFiles'] ?? 'Доступные файлы' }}</h2>
<ul>
    @foreach(($pdfFiles ?? []) as $file)
        <li>
            {{ $file->name }}
            (<a href="/download?file={{ rawurlencode($file->name) }}">{{ $ui['downloadButton'] ?? 'Скачать' }}</a>)
            <form method="post" style="display:inline;" onsubmit="return confirm('{{ $preferences['language'] === 'en' ? 'Delete file?' : 'Удалить файл?' }} {{ $file->name }}');">
                @csrf
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="file_name" value="{{ $file->name }}">
                <button type="submit">{{ $ui['deleteButton'] ?? 'Удалить' }}</button>
            </form>
        </li>
    @endforeach
    @if(empty($pdfFiles))
        <li>{{ $ui['uploadEmpty'] ?? 'Пока нет загруженных PDF-файлов.' }}</li>
    @endif
</ul>
@endsection
