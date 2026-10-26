<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/turnos.php';

$usuario = requerir_rol([CARGO_ADMIN]);
$pdo = (new Conexion())->getConexion();
$ahora = $pdo->quote(ahora());

$resumen = [
    'usuarios' => (int) $pdo->query('SELECT COUNT(*) FROM persona WHERE ID_Cargo = ' . CARGO_CLIENTE . ' AND activo = 1')->fetchColumn(),
    'turnos'   => (int) $pdo->query('SELECT COUNT(*) FROM turno WHERE fecha > ' . $ahora . ' AND ' . sql_activo($pdo))->fetchColumn(),
    'canchas'  => (int) $pdo->query('SELECT COUNT(*) FROM cancha WHERE activa = 1')->fetchColumn(),
    'senas'    => (float) $pdo->query("SELECT COALESCE(SUM(monto_sena), 0) FROM turno WHERE estado IN ('senado','confirmado') AND DATE_FORMAT(fecha, '%Y-%m') = " . $pdo->quote(date('Y-m')))->fetchColumn(),
];

$proximos = $pdo->query(
    'SELECT t.ID_turno, t.fecha, t.estado, t.vence_en, t.ID_cancha, p.Nombre, p.Apellido
     FROM turno t JOIN persona p ON p.ID_persona = t.ID_persona
     WHERE t.fecha > ' . $ahora . ' AND ' . sql_activo($pdo) . ' ORDER BY t.fecha LIMIT 5'
)->fetchAll();

$mantenimientos = $pdo->query(
    'SELECT m.fecha, m.estado, m.descripcion, mc.ID_cancha
     FROM mantenimiento m LEFT JOIN mantenimientoxcancha mc ON mc.ID_mantenimiento = m.ID_mantenimiento
     ORDER BY m.fecha DESC LIMIT 5'
)->fetchAll();

$titulo = 'Dashboard';
$activo = 'dashboard';
require __DIR__ . '/../views/admin/dashboard.view.php';
