<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

class Conexion
{
    private PDO $pdo;

    public function __construct()
    {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Conexión fallida: ' . $e->getMessage());
            http_response_code(500);
            die('No pudimos conectar con la base de datos. Intentá de nuevo en unos minutos.');
        }
    }

    public function getConexion(): PDO
    {
        return $this->pdo;
    }
}
