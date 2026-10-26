<?php require __DIR__ . '/../layout/admin_abrir.php'; ?>
    <?php if ($form): ?>
    <form class="tarjeta form-admin" method="POST" action="categorias.php" novalidate>
        <h2><?= $form['id'] ? 'Editar categoría' : 'Nueva categoría' ?></h2>
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= e($form['id']) ?>">
        <div class="campo"><label for="categoria">Nombre</label><input id="categoria" name="categoria" type="text" maxlength="255" value="<?= e($form['categoria']) ?>" required></div>
        <div class="botones">
            <button class="btn btn-principal btn-auto" type="submit">Guardar</button>
            <a class="btn btn-secundario" href="categorias.php">Cancelar</a>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!$categorias): ?><p class="suave">Todavía no hay categorías.</p><?php else: ?>
    <div class="tabla-scroll"><table class="tabla">
        <thead><tr><th>Categoría</th><th>Productos</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($categorias as $c): ?>
            <tr>
                <td><?= e($c['categoria']) ?></td>
                <td><?= e($c['productos']) ?></td>
                <td class="acciones">
                    <a class="btn btn-secundario btn-chico" href="categorias.php?editar=<?= e($c['ID_categoria']) ?>">Editar</a>
                    <form method="POST" action="categorias.php" onsubmit="return confirm('¿Borrar esta categoría?');">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="borrar"><input type="hidden" name="id" value="<?= e($c['ID_categoria']) ?>">
                        <button class="btn btn-secundario btn-chico" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <p class="suave">Los productos de la categoría Extras aparecen como opcionales al reservar una cancha.</p>
    <?php endif; ?>
<?php require __DIR__ . '/../layout/admin_cerrar.php'; ?>
