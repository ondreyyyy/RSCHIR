<?php
declare(strict_types=1);

namespace App\Application\Pdf;

use App\Domain\PdfFile;
use App\Domain\PdfStorage;
use RuntimeException;

final class PdfService
{
    public function __construct(
        private PdfStorage $storage
    ) {
    }

    /** @return PdfFile[] */
    public function list(): array
    {
        return $this->storage->list();
    }

    /** @return array{name:string,size:int,original?:string} */
    public function upload(array $uploadedFile): array
    {
        $file = $this->storage->store($uploadedFile);
        return $file->toArray();
    }

    public function download(string $fileName): void
    {
        $this->storage->send($fileName);
    }
}
