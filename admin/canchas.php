<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin.php';

$usuario = requerir_rol([CARGO_ADMIN, CARGO_CANCHERO, CARGO_MANTENIMIENTO]);
$pdo = (new Conexion())->getConexion();
$errores = [];
$cespedes = ['Sintético', 'Natural'];
$form = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } elseif ($accion === 'estado') {
        $pdo->prepare('UPDATE cancha SET activa = 1 - activa WHERE ID_cancha = :id')->execute([':id' => $id]);
        ir_admin('canchas.php', 'Actualizamos el estado de la cancha.');
    } elseif ($accion === 'guardar') {
        $form = [
            'id' => $id,
            'tipo_cancha' => trim((string) ($_POST['tipo_cancha'] ?? '')),
            'tipo_cesped' => (string) ($_POST['tipo_cesped'] ?? ''),
            'precio_hora' => trim((string) ($_POST['precio_hora'] ?? '')),
            'activa' => isset($_POST['activa']) ? 1 : 0,
        ];
        if (!largo_ok($form['tipo_cancha'], 50)) { $errores[] = 'Ingresá el tipo de cancha (por ejemplo, Fútbol 8).'; }
        if (!in_array($form['tipo_cesped'], $cespedes, true)) { $errores[] = 'Elegí el tipo de césped.'; }
        if (!is_numeric($form['precio_hora']) || (float) $form['precio_hora'] < 0 || (float) $form['precio_hora'] > 9999999) { $errores[] = 'El precio por hora tiene que ser un número igual o mayor a 0.'; }

        if (!$errores) {
            $params = [':t' => $form['tipo_cancha'], ':c' => $form['tipo_cesped'], ':p' => (float) $form['precio_hora'], ':a' => $form['activa']];
            if ($id === 0) {
                $pdo->prepare('INSERT INTO cancha (tipo_cancha, tipo_cesped, precio_hora, activa) VALUES (:t, :c, :p, :a)')->execute($params);
                ir_admin('canchas.php', 'Creamos la cancha.');
            }
            $params[':id'] = $id;
            $pdo->prepare('UPDATE cancha SET tipo_cancha = :t, tipo_cesped = :c, precio_hora = :p, activa = :a WHERE ID_cancha = :id')->execute($params);
            ir_admin('canchas.php', 'Guardamos los cambios.');
        }
    }
} elseif (isset($_GET['nuevo'])) {
    $form = ['id' => 0, 'tipo_cancha' => 'Fútbol 8', 'tipo_cesped' => 'Sintético', 'precio_hora' => '', 'activa' => 1];
} elseif (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT ID_cancha AS id, tipo_cancha, tipo_cesped, precio_hora, activa FROM cancha WHERE ID_cancha = :id');
    $st->execute([':id' => (int) $_GET['editar']]);
    $form = $st->fetch() ?: null;
}

$canchas = $pdo->query('SELECT ID_cancha, tipo_cancha, tipo_cesped, precio_hora, activa FROM cancha ORDER BY ID_cancha')->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Canchas';
$activo = 'canchas';
$boton = ['Nueva cancha', 'canchas.php?nuevo=1'];
require __DIR__ . '/../views/admin/canchas.view.php';
