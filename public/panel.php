<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

$usuario = requerir_login();
$cargos = [1 => 'Administrador', 2 => 'Canchero', 3 => 'Atención al cliente', 4 => 'Mantenimiento', 5 => 'Cliente'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi panel | CanchaYa</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/base.css">
</head>
<body>
<main class="contenedor">
    <div class="tarjeta">
        <h1>Hola, <?= e($usuario['nombre']) ?></h1>
        <p>Iniciaste sesión como <?= e($cargos[$usuario['cargo']] ?? 'Usuario') ?>.</p>
        <p>Acá va a estar tu panel. Las reservas llegan en la fase 2.</p>
        <form method="POST" action="logout.php">
            <?= csrf_campo() ?>
            <button class="btn btn-secundario" type="submit">Cerrar sesión</button>
        </form>
    </div>
</main>
</body>
</html>