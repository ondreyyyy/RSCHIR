<?php
declare(strict_types=1);

namespace App\Infrastructure;

final class AppConfig
{
    public function pdoDsn(): string
    {
        $host = getenv('DB_HOST') ?: 'db';
        $dbName = getenv('DB_NAME') ?: 'weather';
        return "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
    }

    public function dbUser(): string
    {
        return getenv('DB_USER') ?: 'weatheruser';
    }

    public function dbPassword(): string
    {
        return getenv('DB_PASS') ?: 'weatherpass';
    }

    public function storageRoot(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage';
    }

    public function pdfDir(): string
    {
        return $this->storageRoot() . DIRECTORY_SEPARATOR . 'pdfs';
    }

    public function chartsDir(): string
    {
        return $this->storageRoot() . DIRECTORY_SEPARATOR . 'charts';
    }
}
