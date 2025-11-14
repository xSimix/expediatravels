<?php

namespace Aplicacion\BaseDatos;

use PDO;
use PDOException;

class Conexion
{
    private static ?PDO $pdo = null;

    public static function obtener(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::crearConexion();

            return self::$pdo;
        }

        try {
            $ping = self::$pdo->query('SELECT 1');
            if ($ping !== false) {
                $ping->closeCursor();
            }
        } catch (PDOException $exception) {
            self::$pdo = self::crearConexion();
        }

        return self::$pdo;
    }

    private static function crearConexion(): PDO
    {
        $host = self::obtenerVariableEntorno('DB_HOST', '127.0.0.1');
        $database = self::obtenerVariableEntorno('DB_DATABASE', 'xptravl');
        $username = self::obtenerVariableEntorno('DB_USERNAME', 'root');
        $password = self::obtenerVariableEntorno('DB_PASSWORD', '');
        $charset = self::obtenerVariableEntorno('DB_CHARSET', 'utf8mb4');
        $collation = self::obtenerVariableEntorno('DB_COLLATION', 'utf8mb4_unicode_ci');

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

    private static function obtenerVariableEntorno(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value !== false && $value !== null ? (string) $value : $default;
    }
}
