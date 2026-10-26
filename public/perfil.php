<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/turnos.php';

$usuario = requerir_rol([CARGO_CLIENTE]);
$pdo = (new Conexion())->getConexion();
$errores = [];
$metodos = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'mercadopago' => 'Mercado Pago'];

$st = $pdo->prepare('SELECT Nombre, Apellido, Gmail, telefono, metodo_pago_pref FROM persona WHERE ID_persona = :id');
$st->execute([':id' => $usuario['id']]);
$p = $st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p['Nombre']   = trim((string) ($_POST['nombre'] ?? ''));
    $p['Apellido'] = trim((string) ($_POST['apellido'] ?? ''));
    $p['telefono'] = trim((string) ($_POST['telefono'] ?? ''));
    $metodo = (string) ($_POST['metodo'] ?? '');
    $p['metodo_pago_pref'] = array_key_exists($metodo, $metodos) ? $metodo : null;

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    }
    if ($p['Nombre'] === '' || mb_strlen($p['Nombre']) > 100) {
        $errores[] = 'Ingresá tu nombre (hasta 100 caracteres).';
    }
    if ($p['Apellido'] === '' || mb_strlen($p['Apellido']) > 100) {
        $errores[] = 'Ingresá tu apellido (hasta 100 caracteres).';
    }
    if ($p['telefono'] !== '' && !preg_match('/^[0-9+\s()-]{6,30}$/', $p['telefono'])) {
        $errores[] = 'El teléfono solo puede tener números, espacios, + y guiones.';
    }
    if (!$errores) {
        $up = $pdo->prepare('UPDATE persona SET Nombre = :n, Apellido = :a, telefono = :t, metodo_pago_pref = :m WHERE ID_persona = :id');
        $up->execute([
            ':n' => $p['Nombre'], ':a' => $p['Apellido'],
            ':t' => $p['telefono'] !== '' ? $p['telefono'] : null,
            ':m' => $p['metodo_pago_pref'], ':id' => $usuario['id'],
        ]);
        $_SESSION['usuario']['nombre'] = $p['Nombre'];
        $_SESSION['usuario']['apellido'] = $p['Apellido'];
        $_SESSION['aviso'] = 'Guardamos tus datos.';
        redirigir('perfil.php');
    }
}

$aviso = tomar_aviso();
$titulo = 'Mi perfil';
$activo = 'perfil';
require __DIR__ . '/../views/perfil.view.php';
