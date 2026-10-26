<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/turnos.php';

/** [clave, texto, archivo, cargos que lo ven] */
function menu_admin(): array
{
    return [
        ['dashboard',     'Dashboard',            'dashboard.php',     [CARGO_ADMIN]],
        ['turnos',        'Turnos',               'turnos.php',        [CARGO_ADMIN, CARGO_CANCHERO, CARGO_ATENCION]],
        ['usuarios',      'Usuarios y empleados', 'usuarios.php',      [CARGO_ADMIN]],
        ['canchas',       'Canchas',              'canchas.php',       [CARGO_ADMIN, CARGO_CANCHERO, CARGO_MANTENIMIENTO]],
        ['productos',     'Productos',            'productos.php',     [CARGO_ADMIN, CARGO_ATENCION]],
        ['categorias',    'Categorías',           'categorias.php',    [CARGO_ADMIN, CARGO_ATENCION]],
        ['compras',       'Compras',              'compras.php',       [CARGO_ADMIN, CARGO_ATENCION]],
        ['mantenimiento', 'Mantenimiento',        'mantenimiento.php', [CARGO_ADMIN, CARGO_MANTENIMIENTO]],
    ];
}

/** Primera pantalla de cada cargo al iniciar sesión. */
function primera_ruta(int $cargo): string
{
    if ($cargo === CARGO_MANTENIMIENTO) {
        return 'mantenimiento.php';
    }
    foreach (menu_admin() as $m) {
        if (in_array($cargo, $m[3], true)) {
            return $m[2];
        }
    }
    return 'turnos.php';
}

function ir_admin(string $pagina, string $aviso = ''): never
{
    if ($aviso !== '') {
        $_SESSION['aviso'] = $aviso;
    }
    redirigir_url(ADMIN_URL . '/' . $pagina);
}

function largo_ok(string $v, int $max): bool
{
    return $v !== '' && mb_strlen($v) <= $max;
}
