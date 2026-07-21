<?php

namespace App\Domain;

interface PdfStorage
{
    /** @return \App\Domain\PdfFile[] */
    public function list(): array;

    public function store(array $uploadedFile): \App\Domain\PdfFile;

    public function send(string $fileName): void;

    public function exists(string $fileName): bool;

    public function delete(string $fileName): void;
}
