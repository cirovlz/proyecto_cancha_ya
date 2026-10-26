<?php
$css = ['reservar'];
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/header.php';
$horas = horas_disponibles();
$fechaTxt = date('d/m/Y', strtotime($fecha));
?>
<main class="seccion">
    <h1>Reservar turno</h1>

    <form class="filtros" method="GET" action="reservar.php">
        <div class="campo">
            <label for="fecha">Día</label>
            <input id="fecha" name="fecha" type="date" value="<?= e($fecha) ?>" min="<?= e(date('Y-m-d')) ?>" max="<?= e(date('Y-m-d', strtotime('+' . DIAS_ADELANTE . ' days'))) ?>">
        </div>
        <div class="campo">
            <label for="cesped">Tipo de césped</label>
            <select id="cesped" name="cesped">
                <option value="">Todos</option>
                <?php foreach ($cespedes as $ce): ?>
                    <option value="<?= e($ce) ?>"<?= $ce === $cesped ? ' selected' : '' ?>><?= e($ce) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-secundario" type="submit">Ver horarios</button>
    </form>

    <?php if ($errores): ?>
        <div class="alerta alerta-error" role="alert"><ul><?php foreach ($errores as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if (!$canchas): ?>
        <p class="suave">No hay canchas con ese tipo de césped.</p>
    <?php else: ?>
    <form id="form-reserva" method="POST" action="reservar.php" data-sena="<?= e(SENA_PORCENTAJE) ?>">
        <?= csrf_campo() ?>
        <input type="hidden" name="fecha" value="<?= e($fecha) ?>">

        <h2>1. Elegí cancha y horario para el <?= e($fechaTxt) ?></h2>
        <?php foreach ($canchas as $c): ?>
            <div class="fila-cancha">
                <div class="info-cancha">
                    <strong>Cancha <?= e($c['ID_cancha']) ?></strong>
                    <span class="suave"><?= e($c['tipo_cesped']) ?> - <?= e(dinero($c['precio_hora'])) ?> por hora</span>
                </div>
                <div class="chips">
                    <?php foreach ($horas as $h):
                        $tomado = isset($ocupados[$c['ID_cancha'] . '|' . $h]);
                        $paso = !turno_en_futuro(inicio_turno($fecha, $h));
                        $id = 'slot-' . $c['ID_cancha'] . '-' . $h;
                    ?>
                        <span class="chip">
                            <input type="radio" name="slot" id="<?= e($id) ?>" value="<?= e($c['ID_cancha'] . '|' . $h) ?>" data-precio="<?= e($c['precio_hora']) ?>"<?= ($tomado || $paso) ? ' disabled' : '' ?>>
                            <label for="<?= e($id) ?>"><?= sprintf('%02d:00', $h) ?></label>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <p class="suave">Los horarios en gris ya están ocupados o pasaron.</p>

        <h2>2. ¿Cómo pagan la cancha?</h2>
        <div class="opciones">
            <label class="opcion"><input type="radio" name="modalidad" value="mitad"> <span><strong>Mitad y mitad</strong><br><span class="suave">Cada equipo paga la mitad.</span></span></label>
            <label class="opcion"><input type="radio" name="modalidad" value="completa"> <span><strong>Cancha completa</strong><br><span class="suave">Un solo pago por el total.</span></span></label>
        </div>

        <?php if ($extras): ?>
        <h2>3. Extras (opcional)</h2>
        <div class="extras">
            <?php foreach ($extras as $x): ?>
                <div class="extra">
                    <label for="extra-<?= e($x['ID_producto']) ?>"><?= e($x['producto']) ?> <span class="suave">(<?= e(dinero($x['precio'])) ?> c/u)</span></label>
                    <input id="extra-<?= e($x['ID_producto']) ?>" type="number" name="extra[<?= e($x['ID_producto']) ?>]" min="0" max="10" value="0" data-extra data-precio="<?= e($x['precio']) ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="resumen tarjeta" aria-live="polite">
            <h2>Resumen</h2>
            <p>Total: <strong id="r-total">$0</strong></p>
            <p>Seña (<?= e(SENA_PORCENTAJE) ?>%): <strong id="r-sena">$0</strong></p>
            <p id="r-mitad" hidden>Cada equipo paga: <strong id="r-mitad-valor">$0</strong></p>
            <p class="suave">Tenés <?= e(MINUTOS_VENCE_PENDIENTE) ?> minutos para abonar la seña. Si no, el horario se libera.</p>
            <button class="btn btn-principal" type="submit">Reservar y dejar seña</button>
        </div>
    </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
