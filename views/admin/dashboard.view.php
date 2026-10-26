<?php
$bodyClass = 'admin';
$css = ['admin'];
require __DIR__ . '/../layout/head.php';
?>
<div class="admin-wrap">
    <?php require __DIR__ . '/../layout/admin_sidebar.php'; ?>
    <main class="admin-main">
        <h1>Resumen general</h1>
        <div class="kpis">
            <div class="tarjeta"><span class="kpi"><?= e($resumen['usuarios']) ?></span><span class="suave">Usuarios registrados</span></div>
            <div class="tarjeta"><span class="kpi"><?= e($resumen['turnos']) ?></span><span class="suave">Turnos activos</span></div>
            <div class="tarjeta"><span class="kpi"><?= e($resumen['canchas']) ?></span><span class="suave">Canchas disponibles</span></div>
            <div class="tarjeta"><span class="kpi"><?= e(dinero($resumen['senas'])) ?></span><span class="suave">Señas del mes</span></div>
        </div>

        <section class="tarjeta bloque">
            <h2>Próximos turnos</h2>
            <?php if (!$proximos): ?><p class="suave">No hay turnos próximos.</p><?php else: ?>
            <div class="tabla-scroll"><table class="tabla">
                <thead><tr><th>Fecha</th><th>Cancha</th><th>Cliente</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($proximos as $t): [$txt, $clase] = estado_texto($t); ?>
                    <tr>
                        <td><?= e(date('d/m/Y H:i', strtotime($t['fecha']))) ?> hs</td>
                        <td><?= e($t['ID_cancha']) ?></td>
                        <td><?= e($t['Nombre'] . ' ' . $t['Apellido']) ?></td>
                        <td><span class="estado <?= e($clase) ?>"><?= e($txt) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <a href="<?= ADMIN_URL ?>/turnos.php">Gestionar turnos</a>
            <?php endif; ?>
        </section>

        <section class="tarjeta bloque">
            <h2>Mantenimiento</h2>
            <?php if (!$mantenimientos): ?><p class="suave">Sin tareas de mantenimiento cargadas.</p><?php else: ?>
            <ul class="lista-simple">
                <?php foreach ($mantenimientos as $m): ?>
                    <li>Cancha <?= e($m['ID_cancha'] ?? '-') ?>: <?= e($m['descripcion']) ?> <span class="suave">(<?= e($m['estado']) ?>)</span></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>
    </main>
</div>
<?php require __DIR__ . '/../layout/cierre.php'; ?>
