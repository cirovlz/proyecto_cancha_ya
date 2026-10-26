<?php
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/header.php';
?>
<main class="seccion seccion-angosta">
    <h1>Mi perfil</h1>
    <?php if ($aviso): ?><div class="alerta alerta-ok" role="status"><?= e($aviso) ?></div><?php endif; ?>
    <?php if ($errores): ?>
        <div class="alerta alerta-error" role="alert"><ul><?php foreach ($errores as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form class="tarjeta" method="POST" action="perfil.php" novalidate>
        <?= csrf_campo() ?>
        <div class="campo">
            <label for="gmail">Correo electrónico</label>
            <input id="gmail" type="email" value="<?= e($p['Gmail']) ?>" disabled>
        </div>
        <div class="fila-2">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" type="text" maxlength="100" value="<?= e($p['Nombre']) ?>" required>
            </div>
            <div class="campo">
                <label for="apellido">Apellido</label>
                <input id="apellido" name="apellido" type="text" maxlength="100" value="<?= e($p['Apellido']) ?>" required>
            </div>
        </div>
        <div class="campo">
            <label for="telefono">Teléfono</label>
            <input id="telefono" name="telefono" type="tel" value="<?= e($p['telefono']) ?>">
        </div>
        <div class="campo">
            <label for="metodo">Método de pago preferido</label>
            <select id="metodo" name="metodo">
                <option value="">Sin definir</option>
                <?php foreach ($metodos as $clave => $nombre): ?>
                    <option value="<?= e($clave) ?>"<?= $p['metodo_pago_pref'] === $clave ? ' selected' : '' ?>><?= e($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-principal" type="submit">Guardar cambios</button>
    </form>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
