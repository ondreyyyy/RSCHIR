<?php

namespace App\Http\Controllers;

use App\Infrastructure\FileSystemPdfStorage;
use App\Services\PdfService;
use Illuminate\Http\Request;

class UploadsController extends Controller
{
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $preferences = $this->preferences($request);
        $message = '';

        if ($request->getMethod() === 'POST') {
            if ($request->input('action') === 'upload') {
                try {
                    $uploaded = $request->file('pdf_file');
                    $legacyFile = [
                        'error' => $uploaded->getError(),
                        'tmp_name' => $uploaded->getRealPath(),
                        'name' => $uploaded->getClientOriginalName(),
                        'size' => $uploaded->getSize(),
                    ];
                    $pdfService = new PdfService(new FileSystemPdfStorage(new \App\Infrastructure\AppConfig()));
                    $file = $pdfService->upload($legacyFile);
                    $message = $preferences->language === 'en'
                        ? 'PDF ' . $uploaded->getClientOriginalName() . ' saved as ' . $file['name'] . '.'
                        : 'PDF ' . $uploaded->getClientOriginalName() . ' сохранён как ' . $file['name'] . '.';
                } catch (\Throwable $exception) {
                    $message = $exception->getMessage();
                }
            } elseif ($request->input('action') === 'delete') {
                $fileName = (string) $request->input('file_name');
                if ($fileName !== '') {
                    try {
                        (new PdfService(new FileSystemPdfStorage(new \App\Infrastructure\AppConfig())))->delete($fileName);
                        $message = $preferences->language === 'en'
                            ? 'File ' . $fileName . ' deleted.'
                            : 'Файл ' . $fileName . ' удалён.';
                    } catch (\Throwable $exception) {
                        $message = $exception->getMessage();
                    }
                }
            }
        }

        $pdfService = new PdfService(new FileSystemPdfStorage(new \App\Infrastructure\AppConfig()));
        $pdfFiles = $pdfService->list();

        return $this->renderPage($request, 'uploadTitle', 'uploads', [
            'message' => $message,
            'pdfFiles' => $pdfFiles,
        ]);
    }
}
