<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/turnos.php';

$usuario = requerir_rol([CARGO_CLIENTE]);
$pdo = (new Conexion())->getConexion();
$errores = [];

$fecha = (string) ($_GET['fecha'] ?? $_POST['fecha'] ?? date('Y-m-d'));
if (!fecha_valida($fecha)) {
    $fecha = date('Y-m-d');
}

$cespedes = $pdo->query('SELECT DISTINCT tipo_cesped FROM cancha WHERE activa = 1 ORDER BY tipo_cesped')->fetchAll(PDO::FETCH_COLUMN);
$cesped = (string) ($_GET['cesped'] ?? '');
if (!in_array($cesped, $cespedes, true)) {
    $cesped = '';
}

$sql = 'SELECT ID_cancha, tipo_cancha, tipo_cesped, precio_hora FROM cancha WHERE activa = 1'
     . ($cesped !== '' ? ' AND tipo_cesped = :ce' : '') . ' ORDER BY ID_cancha';
$st = $pdo->prepare($sql);
$st->execute($cesped !== '' ? [':ce' => $cesped] : []);
$canchas = $st->fetchAll();

$extras = $pdo->query(
    "SELECT p.ID_producto, p.producto, p.precio FROM producto p
     JOIN categoria c ON c.ID_categoria = p.ID_categoria
     WHERE c.categoria = 'Extras' AND p.stock > 0 ORDER BY p.producto"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    }
    $partes = array_pad(array_map('intval', explode('|', (string) ($_POST['slot'] ?? ''))), 2, 0);
    [$idCancha, $hora] = $partes;
    $modalidad = in_array($_POST['modalidad'] ?? '', ['mitad', 'completa'], true) ? $_POST['modalidad'] : '';

    if ($idCancha <= 0 || !hora_valida($hora)) {
        $errores[] = 'Elegí una cancha y un horario.';
    }
    if ($modalidad === '') {
        $errores[] = 'Elegí cómo van a pagar la cancha (mitad y mitad o completa).';
    }
    $pedidos = [];
    foreach ((array) ($_POST['extra'] ?? []) as $idp => $q) {
        $q = (int) $q;
        if ($q > 0 && $q <= 10) {
            $pedidos[(int) $idp] = $q;
        }
    }
    $inicio = inicio_turno($fecha, $hora);
    if (!$errores && !turno_en_futuro($inicio)) {
        $errores[] = 'Ese horario ya pasó. Elegí otro.';
    }

    $creado = false;
    if (!$errores) {
        try {
            $pdo->beginTransaction();
            $c = bloquear_cancha($pdo, $idCancha);
            if (!$c) {
                throw new RuntimeException('Esa cancha no está disponible.');
            }
            if (turnos_activos_usuario($pdo, $usuario['id']) >= MAX_TURNOS_ACTIVOS) {
                throw new RuntimeException('Ya tenés ' . MAX_TURNOS_ACTIVOS . ' turnos activos. Cancelá uno o esperá a que se juegue.');
            }
            if (turno_ocupado($pdo, $idCancha, $inicio)) {
                throw new RuntimeException('Ese horario se acaba de ocupar. Elegí otro.');
            }

            $total = (float) $c['precio_hora'];
            $lineas = [];
            foreach ($extras as $x) {
                $q = $pedidos[(int) $x['ID_producto']] ?? 0;
                if ($q > 0) {
                    $total += $q * (float) $x['precio'];
                    $lineas[] = [(int) $x['ID_producto'], $q, $x['precio']];
                }
            }
            $sena = round($total * SENA_PORCENTAJE / 100, 2);
            $vence = date('Y-m-d H:i:s', time() + MINUTOS_VENCE_PENDIENTE * 60);

            $ins = $pdo->prepare(
                'INSERT INTO turno (ID_persona, fecha, tiempo, ID_cancha, modalidad_pago, monto_total, monto_sena, estado, vence_en)
                 VALUES (:p, :f, 60, :c, :m, :t, :s, \'pendiente\', :v)'
            );
            $ins->execute([':p' => $usuario['id'], ':f' => $inicio, ':c' => $idCancha, ':m' => $modalidad, ':t' => $total, ':s' => $sena, ':v' => $vence]);
            $idTurno = (int) $pdo->lastInsertId();

            $insX = $pdo->prepare('INSERT INTO turno_extra (ID_turno, ID_producto, cantidad, precio_unitario) VALUES (:t, :p, :q, :u)');
            foreach ($lineas as [$idp, $q, $precio]) {
                $insX->execute([':t' => $idTurno, ':p' => $idp, ':q' => $q, ':u' => $precio]);
            }
            $pdo->commit();
            $creado = true;
        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $errores[] = $ex->getMessage();
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log('Reservar: ' . $ex->getMessage());
            $errores[] = 'No pudimos guardar tu reserva. Intentá de nuevo en unos minutos.';
        }
    }
    if ($creado) {
        $_SESSION['aviso'] = 'Reservaste el turno. Tenés ' . MINUTOS_VENCE_PENDIENTE . ' minutos para abonar la seña de ' . dinero($sena) . ' antes de que venza.';
        redirigir('mis_turnos.php');
    }
}

$ocupados = ocupados_del_dia($pdo, $fecha);
$titulo = 'Reservar turno';
$activo = 'reservar';
$js = ['reservar'];
require __DIR__ . '/../views/reservar.view.php';
