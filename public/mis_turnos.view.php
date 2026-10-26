<?php
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/header.php';
$ahoraTs = time();
$proximos = array_filter($turnos, fn($t) => strtotime($t['fecha']) > $ahoraTs);
$pasados = array_filter($turnos, fn($t) => strtotime($t['fecha']) <= $ahoraTs);
$modalidades = ['mitad' => 'Mitad y mitad', 'completa' => 'Cancha completa'];

$tabla = function (array $lista, bool $acciones) use ($modalidades): void { ?>
    <div class="tabla-scroll">
    <table class="tabla">
        <thead><tr><th>Fecha</th><th>Cancha</th><th>Pago</th><th>Total</th><th>Seña</th><th>Estado</th><?php if ($acciones): ?><th>Acciones</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($lista as $t): [$txt, $clase] = estado_texto($t); ?>
            <tr>
                <td><?= e(date('d/m/Y H:i', strtotime($t['fecha']))) ?> hs</td>
                <td><?= e($t['ID_cancha']) ?> (<?= e($t['tipo_cesped']) ?>)</td>
                <td><?= e($modalidades[$t['modalidad_pago']] ?? '') ?></td>
                <td><?= e(dinero($t['monto_total'])) ?></td>
                <td><?= e(dinero($t['monto_sena'])) ?></td>
                <td><span class="estado <?= e($clase) ?>"><?= e($txt) ?></span></td>
                <?php if ($acciones): ?>
                <td class="acciones">
                    <?php if (puede_cambiar($t)): ?>
                        <a class="btn btn-secundario btn-chico" href="modificar.php?id=<?= e($t['ID_turno']) ?>">Modificar</a>
                        <form method="POST" action="mis_turnos.php" onsubmit="return confirm('¿Cancelar este turno?');">
                            <?= csrf_campo() ?><input type="hidden" name="id" value="<?= e($t['ID_turno']) ?>">
                            <button class="btn btn-secundario btn-chico" type="submit">Cancelar</button>
                        </form>
                    <?php else: ?><span class="suave">-</span><?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php };
?>
<main class="seccion">
    <h1>Mis turnos</h1>
    <?php if ($aviso): ?><div class="alerta alerta-ok" role="status"><?= e($aviso) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alerta alerta-error" role="alert"><?= e($error) ?></div><?php endif; ?>

    <h2>Próximos</h2>
    <?php if ($proximos): $tabla($proximos, true); else: ?>
        <p class="suave">No tenés turnos próximos. <a href="reservar.php">Reservá uno</a>.</p>
    <?php endif; ?>

    <?php if ($pasados): ?>
        <h2>Historial</h2>
        <?php $tabla($pasados, false); ?>
    <?php endif; ?>
    <p class="suave">Podés modificar o cancelar hasta <?= e(HORAS_MIN_CAMBIO) ?> horas antes del turno.</p>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
