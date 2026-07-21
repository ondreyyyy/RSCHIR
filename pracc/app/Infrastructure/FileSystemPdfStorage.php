<?php

namespace App\Infrastructure;

use App\Domain\PdfFile;
use App\Domain\PdfStorage;
use RuntimeException;

final class FileSystemPdfStorage implements PdfStorage
{
    public function __construct(
        private AppConfig $config
    ) {
    }

    public function list(): array
    {
        $directory = $this->ensureDir();
        $files = glob($directory . DIRECTORY_SEPARATOR . '*.pdf') ?: [];
        sort($files);

        return array_map(
            static fn(string $path): PdfFile => new PdfFile(
                basename($path),
                filesize($path) ?: 0,
                filemtime($path) ?: time()
            ),
            $files
        );
    }

    public function store(array $uploadedFile): PdfFile
    {
        if (!isset($uploadedFile['error']) || (int) $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Не удалось загрузить файл.');
        }

        if (!isset($uploadedFile['tmp_name']) || !is_uploaded_file($uploadedFile['tmp_name'])) {
            throw new RuntimeException('Временный файл не найден.');
        }

        $originalName = (string) ($uploadedFile['name'] ?? 'document.pdf');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            throw new RuntimeException('Можно загружать только PDF-файлы.');
        }

        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $baseName) ?? 'document';
        $baseName = trim($baseName, '_');
        if ($baseName === '') {
            $baseName = 'document';
        }

        $storedName = date('Ymd_His') . '_' . $baseName . '.pdf';
        $targetPath = $this->ensureDir() . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
            throw new RuntimeException('Не удалось сохранить PDF-файл.');
        }

        return new PdfFile($storedName, (int) ($uploadedFile['size'] ?? 0), time());
    }

    public function send(string $fileName): void
    {
        $safeName = $this->safeFileName($fileName);
        $fullPath = $this->ensureDir() . DIRECTORY_SEPARATOR . $safeName;

        if (!is_file($fullPath)) {
            throw new RuntimeException('PDF-файл не найден.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . rawurlencode($safeName) . '"');
        header('Content-Length: ' . (string) filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function exists(string $fileName): bool
    {
        $safeName = $this->safeFileName($fileName);
        return is_file($this->ensureDir() . DIRECTORY_SEPARATOR . $safeName);
    }

    public function delete(string $fileName): void
    {
        $safeName = $this->safeFileName($fileName);
        $fullPath = $this->ensureDir() . DIRECTORY_SEPARATOR . $safeName;
        if (!is_file($fullPath)) {
            throw new RuntimeException('PDF-файл не найден.');
        }
        unlink($fullPath);
    }

    private function safeFileName(string $fileName): string
    {
        $fileName = basename($fileName);
        if ($fileName === '' || !preg_match('/\.pdf$/i', $fileName)) {
            throw new RuntimeException('Разрешены только PDF-файлы.');
        }

        if (str_contains($fileName, '..')) {
            throw new RuntimeException('Некорректное имя файла.');
        }

        return $fileName;
    }

    private function ensureDir(): string
    {
        $directory = $this->config->pdfDir();
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory;
    }
}
