<?php
declare(strict_types=1);

namespace App\Infrastructure;

use PDO;

final class PdoFactory
{
    private static ?PDO $pdo = null;

    public static function get(AppConfig $config): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO($config->pdoDsn(), $config->dbUser(), $config->dbPassword(), [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            ]);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
    }
}
