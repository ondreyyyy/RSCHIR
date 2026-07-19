<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Container;
use App\Http\Response;

final class UploadsController extends AbstractController
{
    public function index(): void
    {
        $this->container->preferences()->startSession();
        $preferences = $this->preferences();
        $message = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'upload') {
            try {
                $file = $this->container->pdfService()->upload($_FILES['pdf_file'] ?? []);
                $message = $preferences->language === 'en'
                    ? 'PDF ' . ($_FILES['pdf_file']['name'] ?? $file['name']) . ' saved as ' . $file['name'] . '.'
                    : 'PDF ' . ($_FILES['pdf_file']['name'] ?? $file['name']) . ' сохранён как ' . $file['name'] . '.';
            } catch (\Throwable $exception) {
                $message = $exception->getMessage();
            }
        }

        $pdfFiles = $this->container->pdfService()->list();

        $this->renderPage('uploadTitle', 'uploads', [
            'message' => $message,
            'pdfFiles' => $pdfFiles,
        ]);
    }
}
