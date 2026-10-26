<?php
$bodyClass = 'admin';
$css = ['admin'];
require __DIR__ . '/../layout/head.php';
$modalidades = ['mitad' => 'Mitad y mitad', 'completa' => 'Completa'];
?>
<div class="admin-wrap">
    <?php require __DIR__ . '/../layout/admin_sidebar.php'; ?>
    <main class="admin-main">
        <h1>Turnos</h1>
        <?php if ($aviso): ?><div class="alerta alerta-ok" role="status"><?= e($aviso) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alerta alerta-error" role="alert"><?= e($error) ?></div><?php endif; ?>

        <?php if (!$turnos): ?><p class="suave">No hay turnos desde hoy en adelante.</p><?php else: ?>
        <div class="tabla-scroll"><table class="tabla">
            <thead><tr><th>Fecha</th><th>Cancha</th><th>Cliente</th><th>Pago</th><th>Total</th><th>Seña</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($turnos as $t): [$txt, $clase] = estado_texto($t); ?>
                <tr>
                    <td><?= e(date('d/m H:i', strtotime($t['fecha']))) ?> hs</td>
                    <td><?= e($t['ID_cancha']) ?></td>
                    <td><?= e($t['Nombre'] . ' ' . $t['Apellido']) ?><br><span class="suave"><?= e($t['telefono'] ?? 'Sin teléfono') ?></span></td>
                    <td><?= e($modalidades[$t['modalidad_pago']] ?? '') ?></td>
                    <td><?= e(dinero($t['monto_total'])) ?></td>
                    <td><?= e(dinero($t['monto_sena'])) ?></td>
                    <td><span class="estado <?= e($clase) ?>"><?= e($txt) ?></span></td>
                    <td class="acciones">
                        <?php
                        $botones = [];
                        if ($t['estado'] === 'pendiente') { $botones['sena'] = 'Registrar seña'; }
                        if ($t['estado'] === 'senado') { $botones['confirmar'] = 'Confirmar'; }
                        if ($t['estado'] !== 'cancelado') { $botones['cancelar'] = 'Cancelar'; }
                        foreach ($botones as $accion => $texto): ?>
                            <form method="POST" action="turnos.php">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= e($t['ID_turno']) ?>">
                                <input type="hidden" name="accion" value="<?= e($accion) ?>">
                                <button class="btn btn-secundario btn-chico" type="submit"><?= e($texto) ?></button>
                            </form>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </main>
</div>
<?php require __DIR__ . '/../layout/cierre.php'; ?>
