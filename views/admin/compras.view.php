<?php require __DIR__ . '/../layout/admin_abrir.php'; ?>
    <?php if ($mostrar): ?>
    <form class="tarjeta form-admin" method="POST" action="compras.php" novalidate>
        <h2>Registrar venta</h2>
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="vender">
        <div class="form-grid">
            <div class="campo">
                <label for="cliente">Cliente</label>
                <select id="cliente" name="cliente">
                    <option value="0">Venta de mostrador (sin cliente)</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= e($c['ID_persona']) ?>"<?= (int) ($_POST['cliente'] ?? 0) === (int) $c['ID_persona'] ? ' selected' : '' ?>><?= e($c['Apellido'] . ', ' . $c['Nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="pago">Medio de pago</label>
                <select id="pago" name="pago">
                    <?php foreach ($pagos as $k => $v): ?><option value="<?= e($k) ?>"<?= ($_POST['pago'] ?? '') === $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php if (!$productos): ?>
            <p class="suave">No hay productos con stock para vender.</p>
        <?php else: ?>
        <div class="tabla-scroll"><table class="tabla">
            <thead><tr><th>Producto</th><th>Precio</th><th>Stock</th><th>Cantidad</th></tr></thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><label for="cant-<?= e($p['ID_producto']) ?>"><?= e($p['producto']) ?></label></td>
                    <td><?= e(dinero($p['precio'])) ?></td>
                    <td><?= e($p['stock']) ?></td>
                    <td><input id="cant-<?= e($p['ID_producto']) ?>" type="number" name="cant[<?= e($p['ID_producto']) ?>]" min="0" max="<?= e(min((int) $p['stock'], 100)) ?>" value="<?= e((int) ($_POST['cant'][$p['ID_producto']] ?? 0)) ?>" style="width:5rem"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
        <div class="botones">
            <button class="btn btn-principal btn-auto" type="submit">Registrar venta</button>
            <a class="btn btn-secundario" href="compras.php">Cancelar</a>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!$compras): ?><p class="suave">Todavía no hay compras registradas.</p><?php else: ?>
    <div class="tabla-scroll"><table class="tabla">
        <thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Pago</th><th>Total</th><?php if ($usuario['cargo'] === CARGO_ADMIN): ?><th>Acciones</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($compras as $c): ?>
            <tr>
                <td><?= e($c['ID_compra']) ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($c['fecha_compra']))) ?></td>
                <td><?= e($c['Nombre'] . ' ' . $c['Apellido']) ?></td>
                <td><?= e($c['items'] ?? '-') ?></td>
                <td><?= e($pagos[$c['tipo_de_pago']] ?? $c['tipo_de_pago']) ?></td>
                <td><?= e(dinero($c['monto_total'])) ?></td>
                <?php if ($usuario['cargo'] === CARGO_ADMIN): ?>
                <td>
                    <form method="POST" action="compras.php" onsubmit="return confirm('¿Anular esta compra? El stock vuelve al inventario.');">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="anular"><input type="hidden" name="id" value="<?= e($c['ID_compra']) ?>">
                        <button class="btn btn-secundario btn-chico" type="submit">Anular</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
<?php require __DIR__ . '/../layout/admin_cerrar.php'; ?>
