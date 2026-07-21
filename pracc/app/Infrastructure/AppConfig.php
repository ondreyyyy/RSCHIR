<?php

namespace App\Infrastructure;

final class AppConfig
{
    public function pdoDsn(): string
    {
        $host = env('DB_HOST', 'db');
        $dbName = env('DB_DATABASE', 'weather');
        return "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
    }

    public function dbUser(): string
    {
        return env('DB_USERNAME', 'weatheruser');
    }

    public function dbPassword(): string
    {
        return env('DB_PASSWORD', 'weatherpass');
    }

    public function storageRoot(): string
    {
        return storage_path('app');
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
