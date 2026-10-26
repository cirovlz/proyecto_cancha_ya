<?php require __DIR__ . '/../layout/admin_abrir.php'; ?>
    <div class="tabs">
        <a href="usuarios.php?ver=clientes"<?= $vista === 'clientes' ? ' class="activo"' : '' ?>>Clientes</a>
        <a href="usuarios.php?ver=empleados"<?= $vista === 'empleados' ? ' class="activo"' : '' ?>>Empleados</a>
    </div>

    <?php if ($form): ?>
    <form class="tarjeta form-admin" method="POST" action="usuarios.php" novalidate>
        <h2><?= $form['id'] ? 'Editar cuenta' : 'Nueva cuenta' ?></h2>
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= e($form['id']) ?>">
        <input type="hidden" name="ver" value="<?= e($vista) ?>">
        <div class="form-grid">
            <div class="campo"><label for="nombre">Nombre</label><input id="nombre" name="nombre" type="text" maxlength="100" value="<?= e($form['Nombre']) ?>" required></div>
            <div class="campo"><label for="apellido">Apellido</label><input id="apellido" name="apellido" type="text" maxlength="100" value="<?= e($form['Apellido']) ?>" required></div>
            <div class="campo"><label for="dni">DNI</label><input id="dni" name="dni" type="text" inputmode="numeric" value="<?= e($form['DNI']) ?>" required></div>
            <div class="campo"><label for="gmail">Correo electrónico</label><input id="gmail" name="gmail" type="email" value="<?= e($form['Gmail']) ?>" required></div>
            <div class="campo"><label for="telefono">Teléfono</label><input id="telefono" name="telefono" type="tel" value="<?= e($form['telefono']) ?>"></div>
            <div class="campo">
                <label for="cargo">Cargo</label>
                <select id="cargo" name="cargo">
                    <?php foreach ($cargos as $idc => $nombreCargo): ?>
                        <option value="<?= e($idc) ?>"<?= (int) $form['ID_Cargo'] === (int) $idc ? ' selected' : '' ?>><?= e($nombreCargo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="clave"><?= $form['id'] ? 'Contraseña nueva (dejá vacío para no cambiarla)' : 'Contraseña inicial (mínimo 8)' ?></label>
                <input id="clave" name="clave" type="password" autocomplete="new-password">
            </div>
        </div>
        <div class="botones">
            <button class="btn btn-principal btn-auto" type="submit">Guardar</button>
            <a class="btn btn-secundario" href="usuarios.php?ver=<?= e($vista) ?>">Cancelar</a>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!$personas): ?><p class="suave">No hay cuentas en esta lista.</p><?php else: ?>
    <div class="tabla-scroll"><table class="tabla">
        <thead><tr><th>Nombre</th><th>DNI</th><th>Correo</th><th>Teléfono</th><th>Cargo</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($personas as $p): ?>
            <tr class="<?= $p['activo'] ? '' : 'fila-inactiva' ?>">
                <td><?= e($p['Apellido'] . ', ' . $p['Nombre']) ?></td>
                <td><?= e($p['DNI']) ?></td>
                <td><?= e($p['Gmail']) ?></td>
                <td><?= e($p['telefono'] ?? '-') ?></td>
                <td><?= e($p['cargo']) ?></td>
                <td><?= $p['activo'] ? 'Activa' : 'Desactivada' ?></td>
                <td class="acciones">
                    <a class="btn btn-secundario btn-chico" href="usuarios.php?ver=<?= e($vista) ?>&editar=<?= e($p['ID_persona']) ?>">Editar</a>
                    <?php if ((int) $p['ID_persona'] !== $usuario['id']): ?>
                    <form method="POST" action="usuarios.php">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="accion" value="estado">
                        <input type="hidden" name="id" value="<?= e($p['ID_persona']) ?>">
                        <input type="hidden" name="ver" value="<?= e($vista) ?>">
                        <button class="btn btn-secundario btn-chico" type="submit"><?= $p['activo'] ? 'Dar de baja' : 'Reactivar' ?></button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <p class="suave">La baja desactiva la cuenta: no puede iniciar sesión, pero se conserva su historial de turnos y compras.</p>
    <?php endif; ?>
<?php require __DIR__ . '/../layout/admin_cerrar.php'; ?>
