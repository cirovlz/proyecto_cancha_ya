<?php
require_once __DIR__ . '/../../includes/csrf.php';
$u = usuario_actual();
$activo = $activo ?? '';
$cls = function (string $clave) use ($activo): string { return $activo === $clave ? ' class="activo"' : ''; };
?>
<header class="barra">
    <a class="marca" href="<?= BASE_URL ?>/index.php"><img class="logo-img" src="<?= ASSETS_URL ?>/img/logo.png" alt="CanchaYa"></a>
    <button class="menu-btn" id="menu-btn" type="button" aria-expanded="false" aria-controls="menu">Menú</button>
    <nav class="menu" id="menu">
        <a href="<?= BASE_URL ?>/index.php"<?= $cls('inicio') ?>>Inicio</a>
        <?php if ($u === null): ?>
            <a href="<?= BASE_URL ?>/index.php#canchas">Canchas</a>
            <a href="<?= BASE_URL ?>/login.php">Iniciar sesión</a>
            <a class="btn btn-principal btn-auto" href="<?= BASE_URL ?>/registro.php">Crear cuenta</a>
        <?php elseif ($u['cargo'] === CARGO_CLIENTE): ?>
            <a href="<?= BASE_URL ?>/reservar.php"<?= $cls('reservar') ?>>Reservar</a>
            <a href="<?= BASE_URL ?>/mis_turnos.php"<?= $cls('turnos') ?>>Mis turnos</a>
            <a href="<?= BASE_URL ?>/panel.php"<?= $cls('panel') ?>>Mi panel</a>
            <a href="<?= BASE_URL ?>/perfil.php"<?= $cls('perfil') ?>>Perfil</a>
            <form method="POST" action="<?= BASE_URL ?>/logout.php"><?= csrf_campo() ?><button class="btn btn-secundario" type="submit">Cerrar sesión</button></form>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/panel.php"<?= $cls('panel') ?>>Mi panel</a>
            <form method="POST" action="<?= BASE_URL ?>/logout.php"><?= csrf_campo() ?><button class="btn btn-secundario" type="submit">Cerrar sesión</button></form>
        <?php endif; ?>
    </nav>
</header>
