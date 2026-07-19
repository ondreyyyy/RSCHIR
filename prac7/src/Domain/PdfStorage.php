<?php
declare(strict_types=1);

namespace App\Domain;

interface PdfStorage
{
    /** @return PdfFile[] */
    public function list(): array;

    public function store(array $uploadedFile): PdfFile;

    public function send(string $fileName): void;

    public function exists(string $fileName): bool;
}
