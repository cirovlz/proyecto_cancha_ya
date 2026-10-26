<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/turnos.php';

$usuario = requerir_login();

// Cada cargo entra a su vista
if ($usuario['cargo'] === CARGO_ADMIN) {
    redirigir_url(ADMIN_URL . '/dashboard.php');
}
if (in_array($usuario['cargo'], [CARGO_CANCHERO, CARGO_ATENCION], true)) {
    redirigir_url(ADMIN_URL . '/turnos.php');
}

$pdo = (new Conexion())->getConexion();
$esCliente = $usuario['cargo'] === CARGO_CLIENTE;
$proximo = null;
$activos = 0;

if ($esCliente) {
    $st = $pdo->prepare(
        'SELECT t.ID_turno, t.fecha, t.estado, t.vence_en, t.ID_cancha
         FROM turno t WHERE t.ID_persona = :p AND t.fecha > ' . $pdo->quote(ahora()) . ' AND ' . sql_activo($pdo) . '
         ORDER BY t.fecha LIMIT 1'
    );
    $st->execute([':p' => $usuario['id']]);
    $proximo = $st->fetch() ?: null;
    $activos = turnos_activos_usuario($pdo, $usuario['id']);
}

$aviso = tomar_aviso();
$titulo = 'Mi panel';
$activo = 'panel';
require __DIR__ . '/../views/panel.view.php';
