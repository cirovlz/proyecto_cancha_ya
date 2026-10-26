<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/turnos.php';

$usuario = requerir_rol([CARGO_CLIENTE]);
$pdo = (new Conexion())->getConexion();
$idTurno = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$st = $pdo->prepare('SELECT ID_turno, ID_cancha, fecha, estado, vence_en FROM turno WHERE ID_turno = :id AND ID_persona = :p');
$st->execute([':id' => $idTurno, ':p' => $usuario['id']]);
$turno = $st->fetch();
if (!$turno || !puede_cambiar($turno)) {
    $_SESSION['aviso'] = 'Ese turno no se puede modificar.';
    redirigir('mis_turnos.php');
}

$errores = [];
$fecha = (string) ($_GET['fecha'] ?? $_POST['fecha'] ?? substr($turno['fecha'], 0, 10));
if (!fecha_valida($fecha)) {
    $fecha = date('Y-m-d');
}
$idCancha = (int) $turno['ID_cancha'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hora = (int) ($_POST['hora'] ?? -1);
    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } elseif (!hora_valida($hora)) {
        $errores[] = 'Elegí un horario.';
    } else {
        $inicio = inicio_turno($fecha, $hora);
        if (!turno_en_futuro($inicio) || strtotime($inicio) - time() < HORAS_MIN_CAMBIO * 3600) {
            $errores[] = 'El nuevo horario tiene que ser con al menos ' . HORAS_MIN_CAMBIO . ' horas de anticipación.';
        } else {
            try {
                $pdo->beginTransaction();
                if (!bloquear_cancha($pdo, $idCancha)) {
                    throw new RuntimeException('Esa cancha no está disponible.');
                }
                if (turno_ocupado($pdo, $idCancha, $inicio, $idTurno)) {
                    throw new RuntimeException('Ese horario ya está ocupado. Elegí otro.');
                }
                $up = $pdo->prepare('UPDATE turno SET fecha = :f WHERE ID_turno = :id AND ID_persona = :p');
                $up->execute([':f' => $inicio, ':id' => $idTurno, ':p' => $usuario['id']]);
                $pdo->commit();
                $_SESSION['aviso'] = 'Cambiaste la fecha y el horario del turno.';
                redirigir('mis_turnos.php');
            } catch (RuntimeException $ex) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $errores[] = $ex->getMessage();
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                error_log('Modificar: ' . $ex->getMessage());
                $errores[] = 'No pudimos guardar el cambio. Intentá de nuevo en unos minutos.';
            }
        }
    }
}

$ocupados = ocupados_del_dia($pdo, $fecha, $idTurno);
$titulo = 'Modificar turno';
$activo = 'turnos';
require __DIR__ . '/../views/modificar.view.php';
