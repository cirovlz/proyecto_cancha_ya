<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/turnos.php';

$usuario = requerir_rol([CARGO_CLIENTE]);
$pdo = (new Conexion())->getConexion();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar()) {
        $error = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } else {
        $st = $pdo->prepare('SELECT ID_turno, fecha, estado, vence_en FROM turno WHERE ID_turno = :id AND ID_persona = :p');
        $st->execute([':id' => (int) ($_POST['id'] ?? 0), ':p' => $usuario['id']]);
        $t = $st->fetch();
        if (!$t || !puede_cambiar($t)) {
            $error = 'Ese turno ya no se puede cancelar (faltan menos de ' . HORAS_MIN_CAMBIO . ' horas o ya está cerrado).';
        } else {
            $up = $pdo->prepare("UPDATE turno SET estado = 'cancelado' WHERE ID_turno = :id AND ID_persona = :p");
            $up->execute([':id' => $t['ID_turno'], ':p' => $usuario['id']]);
            $_SESSION['aviso'] = 'Cancelaste el turno.';
            redirigir('mis_turnos.php');
        }
    }
}

$st = $pdo->prepare(
    'SELECT t.ID_turno, t.fecha, t.estado, t.vence_en, t.modalidad_pago, t.monto_total, t.monto_sena, c.ID_cancha, c.tipo_cesped
     FROM turno t JOIN cancha c ON c.ID_cancha = t.ID_cancha
     WHERE t.ID_persona = :p ORDER BY t.fecha DESC LIMIT 50'
);
$st->execute([':p' => $usuario['id']]);
$turnos = $st->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Mis turnos';
$activo = 'turnos';
require __DIR__ . '/../views/mis_turnos.view.php';
