<?php require __DIR__ . '/../layout/admin_abrir.php'; ?>
    <?php if ($form): ?>
    <form class="tarjeta form-admin" method="POST" action="productos.php" novalidate>
        <h2><?= $form['id'] ? 'Editar producto' : 'Nuevo producto' ?></h2>
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= e($form['id']) ?>">
        <div class="form-grid">
            <div class="campo"><label for="producto">Nombre</label><input id="producto" name="producto" type="text" maxlength="100" value="<?= e($form['producto']) ?>" required></div>
            <div class="campo"><label for="precio">Precio</label><input id="precio" name="precio" type="number" min="0" step="50" value="<?= e($form['precio']) ?>" required></div>
            <div class="campo">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria">
                    <option value="">Elegí una</option>
                    <?php foreach ($categorias as $idc => $nombre): ?>
                        <option value="<?= e($idc) ?>"<?= (int) $form['ID_categoria'] === (int) $idc ? ' selected' : '' ?>><?= e($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo"><label for="stock">Stock</label><input id="stock" name="stock" type="number" min="0" step="1" value="<?= e($form['stock']) ?>" required></div>
        </div>
        <div class="botones">
            <button class="btn btn-principal btn-auto" type="submit">Guardar</button>
            <a class="btn btn-secundario" href="productos.php">Cancelar</a>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!$productos): ?><p class="suave">Todavía no hay productos.</p><?php else: ?>
    <div class="tabla-scroll"><table class="tabla">
        <thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Reponer</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($productos as $p): ?>
            <tr class="<?= (int) $p['stock'] === 0 ? 'fila-inactiva' : '' ?>">
                <td><?= e($p['producto']) ?></td>
                <td><?= e($p['categoria']) ?></td>
                <td><?= e(dinero($p['precio'])) ?></td>
                <td><?= e($p['stock']) ?></td>
                <td>
                    <form class="mini" method="POST" action="productos.php">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="reponer"><input type="hidden" name="id" value="<?= e($p['ID_producto']) ?>">
                        <input type="number" name="cantidad" min="1" max="1000" value="10" aria-label="Cantidad a reponer">
                        <button class="btn btn-secundario btn-chico" type="submit">Sumar</button>
                    </form>
                </td>
                <td class="acciones">
                    <a class="btn btn-secundario btn-chico" href="productos.php?editar=<?= e($p['ID_producto']) ?>">Editar</a>
                    <form method="POST" action="productos.php" onsubmit="return confirm('¿Borrar este producto?');">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="borrar"><input type="hidden" name="id" value="<?= e($p['ID_producto']) ?>">
                        <button class="btn btn-secundario btn-chico" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
<?php require __DIR__ . '/../layout/admin_cerrar.php'; ?>
