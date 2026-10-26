<?php require __DIR__ . '/../layout/admin_abrir.php'; ?>
    <?php if ($form): ?>
    <form class="tarjeta form-admin" method="POST" action="mantenimiento.php" novalidate>
        <h2><?= $form['id'] ? 'Editar tarea' : 'Nueva tarea' ?></h2>
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= e($form['id']) ?>">
        <div class="form-grid">
            <div class="campo">
                <label for="cancha">Cancha</label>
                <select id="cancha" name="cancha">
                    <option value="">Elegí una</option>
                    <?php foreach ($canchas as $c): ?><option value="<?= e($c) ?>"<?= (int) $form['ID_cancha'] === (int) $c ? ' selected' : '' ?>>Cancha <?= e($c) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="campo"><label for="fecha">Fecha y hora</label><input id="fecha" name="fecha" type="datetime-local" value="<?= e($form['fecha']) ?>" required></div>
            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <?php foreach ($estados as $es): ?><option value="<?= e($es) ?>"<?= $form['estado'] === $es ? ' selected' : '' ?>><?= e($es) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="campo"><label for="descripcion">Descripción</label><input id="descripcion" name="descripcion" type="text" maxlength="255" value="<?= e($form['descripcion']) ?>" required></div>
        <div class="botones">
            <button class="btn btn-principal btn-auto" type="submit">Guardar</button>
            <a class="btn btn-secundario" href="mantenimiento.php">Cancelar</a>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!$tareas): ?><p class="suave">No hay tareas de mantenimiento cargadas.</p><?php else: ?>
    <div class="tabla-scroll"><table class="tabla">
        <thead><tr><th>Fecha</th><th>Cancha</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($tareas as $t): ?>
            <tr class="<?= $t['estado'] === 'Finalizado' ? 'fila-inactiva' : '' ?>">
                <td><?= e(date('d/m/Y H:i', strtotime($t['fecha']))) ?></td>
                <td><?= e($t['ID_cancha'] ?? '-') ?></td>
                <td><?= e($t['descripcion']) ?></td>
                <td><?= e($t['estado']) ?></td>
                <td class="acciones">
                    <a class="btn btn-secundario btn-chico" href="mantenimiento.php?editar=<?= e($t['ID_mantenimiento']) ?>">Editar</a>
                    <form method="POST" action="mantenimiento.php" onsubmit="return confirm('¿Borrar esta tarea?');">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="borrar"><input type="hidden" name="id" value="<?= e($t['ID_mantenimiento']) ?>">
                        <button class="btn btn-secundario btn-chico" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
<?php require __DIR__ . '/../layout/admin_cerrar.php'; ?>
