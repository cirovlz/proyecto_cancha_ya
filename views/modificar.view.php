<?php
$css = ['reservar'];
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/header.php';
$horaActual = (int) date('G', strtotime($turno['fecha']));
$fechaActual = substr($turno['fecha'], 0, 10);
?>
<main class="seccion seccion-angosta">
    <h1>Modificar turno</h1>
    <p class="suave">Cancha <?= e($idCancha) ?>. Hoy está agendado para el <?= e(date('d/m/Y H:i', strtotime($turno['fecha']))) ?> hs.</p>

    <form class="filtros" method="GET" action="modificar.php">
        <input type="hidden" name="id" value="<?= e($idTurno) ?>">
        <div class="campo">
            <label for="fecha">Nuevo día</label>
            <input id="fecha" name="fecha" type="date" value="<?= e($fecha) ?>" min="<?= e(date('Y-m-d')) ?>" max="<?= e(date('Y-m-d', strtotime('+' . DIAS_ADELANTE . ' days'))) ?>">
        </div>
        <button class="btn btn-secundario" type="submit">Ver horarios</button>
    </form>

    <?php if ($errores): ?>
        <div class="alerta alerta-error" role="alert"><ul><?php foreach ($errores as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="modificar.php">
        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= e($idTurno) ?>">
        <input type="hidden" name="fecha" value="<?= e($fecha) ?>">
        <h2>Horarios para el <?= e(date('d/m/Y', strtotime($fecha))) ?></h2>
        <div class="chips">
            <?php foreach (horas_disponibles() as $h):
                $tomado = isset($ocupados[$idCancha . '|' . $h]);
                $paso = !turno_en_futuro(inicio_turno($fecha, $h));
                $mismo = ($fecha === $fechaActual && $h === $horaActual);
            ?>
                <span class="chip">
                    <input type="radio" name="hora" id="h-<?= e($h) ?>" value="<?= e($h) ?>"<?= ($tomado || $paso || $mismo) ? ' disabled' : '' ?>>
                    <label for="h-<?= e($h) ?>"><?= sprintf('%02d:00', $h) ?></label>
                </span>
            <?php endforeach; ?>
        </div>
        <p class="suave">El nuevo horario tiene que ser con al menos <?= e(HORAS_MIN_CAMBIO) ?> horas de anticipación.</p>
        <div class="botones">
            <button class="btn btn-principal btn-auto" type="submit">Guardar cambio</button>
            <a class="btn btn-secundario" href="mis_turnos.php">Volver</a>
        </div>
    </form>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
