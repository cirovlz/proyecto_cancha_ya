<?php
declare(strict_types=1);
require_once '../config/Conexion.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/admin.php';

$usuario = requerir_rol([CARGO_ADMIN, CARGO_CANCHERO, CARGO_ATENCION]);
$pdo = (new Conexion())->getConexion();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (!csrf_validar()) {
        $error = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } else {
        $st = $pdo->prepare('SELECT ID_turno, fecha, estado, vence_en FROM turno WHERE ID_turno = :id');
        $st->execute([':id' => $id]);
        $t = $st->fetch();

        if (!$t) {
            $error = 'No encontramos ese turno.';
        } elseif ($accion === 'sena') {
            if ($t['estado'] !== 'pendiente' || turno_vencido($t)) {
                $error = 'Ese turno ya no está pendiente de seña (puede haber vencido).';
            } else {
                $pdo->prepare("UPDATE turno SET estado = 'senado', vence_en = NULL WHERE ID_turno = :id")->execute([':id' => $id]);
                ir_admin('turnos.php', 'Registramos la seña del turno.');
            }
        } elseif ($accion === 'confirmar') {
            if ($t['estado'] !== 'senado') {
                $error = 'Solo se pueden confirmar turnos con la seña pagada.';
            } else {
                $pdo->prepare("UPDATE turno SET estado = 'confirmado' WHERE ID_turno = :id")->execute([':id' => $id]);
                ir_admin('turnos.php', 'Confirmamos el turno.');
            }
        } elseif ($accion === 'cancelar') {
            if ($t['estado'] === 'cancelado') {
                $error = 'Ese turno ya estaba cancelado.';
            } else {
                $pdo->prepare("UPDATE turno SET estado = 'cancelado' WHERE ID_turno = :id")->execute([':id' => $id]);
                ir_admin('turnos.php', 'Cancelamos el turno.');
            }
        } else {
            $error = 'Acción no válida.';
        }
    }
}

// Turnos desde hoy en adelante
$turnos = $pdo->query(
    'SELECT t.ID_turno, t.fecha, t.estado, t.vence_en, t.ID_cancha, t.modalidad_pago, t.monto_total, t.monto_sena,
            p.Nombre, p.Apellido, p.telefono
     FROM turno t JOIN persona p ON p.ID_persona = t.ID_persona
     WHERE t.fecha >= CURDATE() ORDER BY t.fecha'
)->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Turnos';
$activo = 'turnos';
require '../views/admin/turnos.view.php';