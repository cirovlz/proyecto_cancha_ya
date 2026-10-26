<?php
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/header.php';
?>
<main class="seccion">
    <h1>Hola, <?= e($usuario['nombre']) ?></h1>
    <?php if ($aviso): ?><div class="alerta alerta-ok" role="status"><?= e($aviso) ?></div><?php endif; ?>

    <?php if (!$esCliente): ?>
        <div class="tarjeta"><p>Tu módulo de trabajo llega en la fase 3.</p></div>
    <?php else: ?>
        <div class="grid-panel">
            <section class="tarjeta">
                <h2>Próximo turno</h2>
                <?php if ($proximo): [$txt, $clase] = estado_texto($proximo); ?>
                    <p class="grande">Cancha <?= e($proximo['ID_cancha']) ?></p>
                    <p><?= e(date('d/m/Y H:i', strtotime($proximo['fecha']))) ?> hs</p>
                    <p><span class="estado <?= e($clase) ?>"><?= e($txt) ?></span></p>
                    <a class="btn btn-secundario btn-auto" href="mis_turnos.php">Ver detalles</a>
                <?php else: ?>
                    <p class="suave">No tenés turnos próximos.</p>
                <?php endif; ?>
            </section>

            <section class="tarjeta">
                <h2>Tus reservas</h2>
                <p class="grande"><?= e($activos) ?> de <?= e(MAX_TURNOS_ACTIVOS) ?></p>
                <p class="suave">turnos activos. Podés tener hasta <?= e(MAX_TURNOS_ACTIVOS) ?> a la vez.</p>
                <a class="btn btn-principal btn-auto" href="reservar.php">Reservar un nuevo turno</a>
            </section>

            <section class="tarjeta">
                <h2>Estadísticas y torneos</h2>
                <p class="suave">Tus partidos, goles, equipo y ligas aparecen acá cuando activemos esa parte del sistema.</p>
            </section>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
