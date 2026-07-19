<?php
declare(strict_types=1);

namespace App\Http\View;

final class View
{
    public function __construct(
        private string $templatesDir
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        return $this->capture($template, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function capture(string $template, array $data): string
    {
        $path = $this->templatesDir . DIRECTORY_SEPARATOR . $template . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("Шаблон не найден: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
