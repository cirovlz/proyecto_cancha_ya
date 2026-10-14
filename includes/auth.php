<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function redirigir(string $ruta): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($ruta, '/'));
    exit;
}

function usuario_actual(): ?array
{
    iniciar_sesion_segura();
    return $_SESSION['usuario'] ?? null;
}

/** Guarda al usuario en sesión y renueva el ID de sesión (evita session fixation). */
function loguear_usuario(array $persona): void
{
    iniciar_sesion_segura();
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id'       => (int) $persona['ID_persona'],
        'nombre'   => $persona['Nombre'],
        'apellido' => $persona['Apellido'],
        'cargo'    => (int) $persona['ID_Cargo'],
    ];
}

function requerir_login(): array
{
    $usuario = usuario_actual();
    if ($usuario === null) {
        redirigir('login.php');
    }
    return $usuario;
}

/** Ej.: requerir_rol([CARGO_ADMIN]) */
function requerir_rol(array $cargosPermitidos): array
{
    $usuario = requerir_login();
    if (!in_array($usuario['cargo'], $cargosPermitidos, true)) {
        http_response_code(403);
        die('No tenés permiso para ver esta página.');
    }
    return $usuario;
}

function cerrar_sesion(): void
{
    iniciar_sesion_segura();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}