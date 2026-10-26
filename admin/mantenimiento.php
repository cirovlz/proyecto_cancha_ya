<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin.php';

$usuario = requerir_rol([CARGO_ADMIN, CARGO_MANTENIMIENTO]);
$pdo = (new Conexion())->getConexion();
$errores = [];
$estados = ['Pendiente', 'En curso', 'Finalizado'];
$canchas = $pdo->query('SELECT ID_cancha FROM cancha ORDER BY ID_cancha')->fetchAll(PDO::FETCH_COLUMN);
$form = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } elseif ($accion === 'borrar') {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM mantenimientoxcancha WHERE ID_mantenimiento = :id')->execute([':id' => $id]);
            $pdo->prepare('DELETE FROM mantenimiento WHERE ID_mantenimiento = :id')->execute([':id' => $id]);
            $pdo->commit();
            ir_admin('mantenimiento.php', 'Borramos la tarea.');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log('Mantenimiento: ' . $ex->getMessage());
            $errores[] = 'No pudimos borrar la tarea.';
        }
    } elseif ($accion === 'guardar') {
        $form = [
            'id' => $id,
            'ID_cancha' => (int) ($_POST['cancha'] ?? 0),
            'fecha' => trim((string) ($_POST['fecha'] ?? '')),
            'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
            'estado' => (string) ($_POST['estado'] ?? ''),
        ];
        $f = DateTime::createFromFormat('Y-m-d\TH:i', $form['fecha']);
        if (!in_array($form['ID_cancha'], array_map('intval', $canchas), true)) { $errores[] = 'Elegí una cancha.'; }
        if (!$f || $f->format('Y-m-d\TH:i') !== $form['fecha']) { $errores[] = 'Indicá la fecha y la hora de la tarea.'; }
        if (!largo_ok($form['descripcion'], 255)) { $errores[] = 'Describí la tarea (hasta 255 caracteres).'; }
        if (!in_array($form['estado'], $estados, true)) { $errores[] = 'Elegí el estado.'; }

        if (!$errores) {
            try {
                $pdo->beginTransaction();
                $fechaSql = $f->format('Y-m-d H:i:00');
                if ($id === 0) {
                    $pdo->prepare('INSERT INTO mantenimiento (fecha, estado, descripcion) VALUES (:f, :e, :d)')->execute([':f' => $fechaSql, ':e' => $form['estado'], ':d' => $form['descripcion']]);
                    $id = (int) $pdo->lastInsertId();
                    $msg = 'Creamos la tarea.';
                } else {
                    $pdo->prepare('UPDATE mantenimiento SET fecha = :f, estado = :e, descripcion = :d WHERE ID_mantenimiento = :id')->execute([':f' => $fechaSql, ':e' => $form['estado'], ':d' => $form['descripcion'], ':id' => $id]);
                    $pdo->prepare('DELETE FROM mantenimientoxcancha WHERE ID_mantenimiento = :id')->execute([':id' => $id]);
                    $msg = 'Guardamos los cambios.';
                }
                $pdo->prepare('INSERT INTO mantenimientoxcancha (ID_cancha, ID_mantenimiento) VALUES (:c, :m)')->execute([':c' => $form['ID_cancha'], ':m' => $id]);
                $pdo->commit();
                ir_admin('mantenimiento.php', $msg);
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                error_log('Mantenimiento: ' . $ex->getMessage());
                $errores[] = 'No pudimos guardar. Intentá de nuevo en unos minutos.';
            }
        }
    }
} elseif (isset($_GET['nuevo'])) {
    $form = ['id' => 0, 'ID_cancha' => 0, 'fecha' => date('Y-m-d\TH:i'), 'descripcion' => '', 'estado' => 'Pendiente'];
} elseif (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT m.ID_mantenimiento AS id, mc.ID_cancha, m.fecha, m.descripcion, m.estado
                         FROM mantenimiento m LEFT JOIN mantenimientoxcancha mc ON mc.ID_mantenimiento = m.ID_mantenimiento
                         WHERE m.ID_mantenimiento = :id');
    $st->execute([':id' => (int) $_GET['editar']]);
    $form = $st->fetch() ?: null;
    if ($form) { $form['fecha'] = date('Y-m-d\TH:i', strtotime($form['fecha'])); }
}

$tareas = $pdo->query(
    'SELECT m.ID_mantenimiento, m.fecha, m.estado, m.descripcion, mc.ID_cancha
     FROM mantenimiento m LEFT JOIN mantenimientoxcancha mc ON mc.ID_mantenimiento = m.ID_mantenimiento
     ORDER BY m.fecha DESC LIMIT 100'
)->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Mantenimiento';
$activo = 'mantenimiento';
$boton = ['Nueva tarea', 'mantenimiento.php?nuevo=1'];
require __DIR__ . '/../views/admin/mantenimiento.view.php';
