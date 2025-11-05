<?php

namespace App\Database;

use PDO;

class Connection
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::createConnection();
        }

        return self::$pdo;
    }

    private static function createConnection(): PDO
    {
        $host = self::getEnv('DB_HOST', '127.0.0.1');
        $database = self::getEnv('DB_DATABASE', 'xptravl');
        $username = self::getEnv('DB_USERNAME', 'root');
        $password = self::getEnv('DB_PASSWORD', '');
        $charset = self::getEnv('DB_CHARSET', 'utf8mb4');
        $collation = self::getEnv('DB_COLLATION', 'utf8mb4_unicode_ci');

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $database, $charset);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, $username, $password, $options);
        $pdo->exec(sprintf("SET NAMES '%s' COLLATE '%s'", $charset, $collation));

        return $pdo;
    }

    private static function getEnv(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value !== false && $value !== null ? (string) $value : $default;
    }
}
