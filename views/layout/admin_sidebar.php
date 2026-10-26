<?php
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/admin.php';
$cls = function (string $clave) use ($activo): string { return $activo === $clave ? ' class="activo"' : ''; };
?>
<aside class="lateral">
    <a class="marca" href="<?= ADMIN_URL ?>/<?= e(primera_ruta($usuario['cargo'])) ?>"><img class="logo-img" src="<?= ASSETS_URL ?>/img/logo.png" alt="CanchaYa"></a>
    <nav>
        <?php foreach (menu_admin() as [$clave, $texto, $ruta, $roles]):
            if (!in_array($usuario['cargo'], $roles, true)) { continue; } ?>
            <a href="<?= ADMIN_URL ?>/<?= e($ruta) ?>"<?= $cls($clave) ?>><?= e($texto) ?></a>
        <?php endforeach; ?>
    </nav>
    <p class="nota-lateral">Sesión de <?= e($usuario['nombre']) ?></p>
    <form method="POST" action="<?= BASE_URL ?>/logout.php"><?= csrf_campo() ?><button class="btn btn-secundario" type="submit">Cerrar sesión</button></form>
</aside>
