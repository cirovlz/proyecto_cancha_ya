<?php
require_once __DIR__ . '/../../includes/csrf.php';
$esAdmin = $usuario['cargo'] === CARGO_ADMIN;
$cls = function (string $clave) use ($activo): string { return $activo === $clave ? ' class="activo"' : ''; };
?>
<aside class="lateral">
    <a class="marca" href="<?= ADMIN_URL ?>/<?= $esAdmin ? 'dashboard' : 'turnos' ?>.php"><img class="logo-img" src="<?= ASSETS_URL ?>/img/logo.png" alt="CanchaYa"></a>
    <nav>
        <?php if ($esAdmin): ?><a href="<?= ADMIN_URL ?>/dashboard.php"<?= $cls('dashboard') ?>>Dashboard</a><?php endif; ?>
        <a href="<?= ADMIN_URL ?>/turnos.php"<?= $cls('turnos') ?>>Turnos</a>
        <?php if ($esAdmin): ?>
            <span class="prox">Usuarios</span>
            <span class="prox">Canchas</span>
            <span class="prox">Productos</span>
            <span class="prox">Categorías</span>
            <span class="prox">Compras</span>
            <span class="prox">Mantenimiento</span>
        <?php endif; ?>
    </nav>
    <p class="nota-lateral">Los módulos en gris llegan en la fase 3.</p>
    <form method="POST" action="<?= BASE_URL ?>/logout.php"><?= csrf_campo() ?><button class="btn btn-secundario" type="submit">Cerrar sesión</button></form>
</aside>
