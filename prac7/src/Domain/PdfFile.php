<?php
declare(strict_types=1);

namespace App\Domain;

final class PdfFile
{
    public function __construct(
        public string $name,
        public int $size,
        public int $modified
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'size' => $this->size,
            'modified' => $this->modified,
        ];
    }
}
