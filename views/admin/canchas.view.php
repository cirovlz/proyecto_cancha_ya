<?php require __DIR__ . '/../layout/admin_abrir.php'; ?>
    <?php if ($form): ?>
    <form class="tarjeta form-admin" method="POST" action="canchas.php" novalidate>
        <h2><?= $form['id'] ? 'Editar cancha ' . e($form['id']) : 'Nueva cancha' ?></h2>
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= e($form['id']) ?>">
        <div class="form-grid">
            <div class="campo"><label for="tipo_cancha">Tipo de cancha</label><input id="tipo_cancha" name="tipo_cancha" type="text" maxlength="50" value="<?= e($form['tipo_cancha']) ?>" required></div>
            <div class="campo">
                <label for="tipo_cesped">Tipo de césped</label>
                <select id="tipo_cesped" name="tipo_cesped">
                    <?php foreach ($cespedes as $ce): ?><option value="<?= e($ce) ?>"<?= $form['tipo_cesped'] === $ce ? ' selected' : '' ?>><?= e($ce) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="campo"><label for="precio_hora">Precio por hora</label><input id="precio_hora" name="precio_hora" type="number" min="0" step="100" value="<?= e($form['precio_hora']) ?>" required></div>
        </div>
        <label class="check"><input type="checkbox" name="activa" value="1"<?= $form['activa'] ? ' checked' : '' ?>> Disponible para reservar</label>
        <div class="botones">
            <button class="btn btn-principal btn-auto" type="submit">Guardar</button>
            <a class="btn btn-secundario" href="canchas.php">Cancelar</a>
        </div>
    </form>
    <?php endif; ?>

    <div class="tabla-scroll"><table class="tabla">
        <thead><tr><th>N°</th><th>Tipo</th><th>Césped</th><th>Precio por hora</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($canchas as $c): ?>
            <tr class="<?= $c['activa'] ? '' : 'fila-inactiva' ?>">
                <td><?= e($c['ID_cancha']) ?></td>
                <td><?= e($c['tipo_cancha']) ?></td>
                <td><?= e($c['tipo_cesped']) ?></td>
                <td><?= e(dinero($c['precio_hora'])) ?></td>
                <td><?= $c['activa'] ? 'Disponible' : 'No disponible' ?></td>
                <td class="acciones">
                    <a class="btn btn-secundario btn-chico" href="canchas.php?editar=<?= e($c['ID_cancha']) ?>">Editar</a>
                    <form method="POST" action="canchas.php">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= e($c['ID_cancha']) ?>">
                        <button class="btn btn-secundario btn-chico" type="submit"><?= $c['activa'] ? 'Dar de baja' : 'Reactivar' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <p class="suave">Una cancha dada de baja no aparece para reservar, pero conserva sus turnos anteriores.</p>
<?php require __DIR__ . '/../layout/admin_cerrar.php'; ?>
