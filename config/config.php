<?php
declare(strict_types=1);

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Ajustar según la carpeta dentro de htdocs
define('BASE_URL', '/canchaya/public');
define('ADMIN_URL', '/canchaya/admin');
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

// Reglas de reserva
define('HORA_INICIO', 16);            // primer turno 16:00
define('HORA_FIN', 24);               // último turno empieza 23:00
define('DIAS_ADELANTE', 30);          // hasta cuántos días se puede reservar
define('MAX_TURNOS_ACTIVOS', 3);      // anti-acaparamiento por usuario
define('SENA_PORCENTAJE', 30);        // % del total que se deja de seña
define('MINUTOS_VENCE_PENDIENTE', 30);// tiempo para pagar la seña
define('HORAS_MIN_CAMBIO', 12);       // hasta cuántas horas antes se puede modificar o cancelar

// Datos de contacto (completar con los reales)
define('CONTACTO_TEL', '0000-000000');
define('CONTACTO_MAIL', 'contacto@canchaya.com');
define('CONTACTO_IG', 'canchaya');

// true en desarrollo (XAMPP), false en producción
define('APP_DEBUG', true);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

/** Escapa texto para imprimirlo en HTML. */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** 30000 -> $30.000 */
function dinero($valor): string
{
    return '$' . number_format((float) $valor, 0, ',', '.');
}
