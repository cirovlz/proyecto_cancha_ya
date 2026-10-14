<?php
declare(strict_types=1);

// Ajustar según la carpeta dentro de htdocs (http://localhost/canchaya/public)
define('BASE_URL', '/canchaya/public');
define('ASSETS_URL', '/canchaya/assets');

define('DB_HOST', 'localhost');
define('DB_NAME', 'canchas_futbol');
define('DB_USER', 'root');
define('DB_PASS', '');

// IDs de la tabla cargos
define('CARGO_ADMIN', 1);
define('CARGO_CANCHERO', 2);
define('CARGO_ATENCION', 3);
define('CARGO_MANTENIMIENTO', 4);
define('CARGO_CLIENTE', 5);

// true en desarrollo (XAMPP), false en producción
define('APP_DEBUG', true);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

/** Escapa texto para imprimirlo en HTML. */
function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}