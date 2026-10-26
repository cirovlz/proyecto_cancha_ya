<?php
require __DIR__ . '/layout/head.php';
require __DIR__ . '/layout/header.php';
?>
<main>
    <section class="hero">
        <div class="hero-texto">
            <h1>Reservá tu cancha de fútbol 8 en minutos</h1>
            <p>Elegí cancha, horario y césped. Sumá árbitro, pecheras y pelotas, y dejá la seña online.</p>
            <a class="btn btn-principal btn-auto" href="<?= BASE_URL ?>/reservar.php">Reservar ahora</a>
        </div>
    </section>

    <section class="seccion" id="canchas">
        <h2>Nuestras canchas</h2>
        <?php if (!$canchas): ?>
            <p class="suave">Todavía no hay canchas cargadas.</p>
        <?php else: ?>
            <div class="grid-canchas">
                <?php foreach ($canchas as $c): ?>
                    <article class="tarjeta">
                        <h3>Cancha <?= e($c['ID_cancha']) ?></h3>
                        <p class="suave"><?= e($c['tipo_cancha']) ?> con césped <?= e(mb_strtolower($c['tipo_cesped'])) ?></p>
                        <p class="precio"><?= e(dinero($c['precio_hora'])) ?> por hora</p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="seccion">
        <h2>Cómo reservar</h2>
        <ol class="pasos">
            <li>Creá tu cuenta o iniciá sesión.</li>
            <li>Elegí el día, el césped y un horario libre.</li>
            <li>Decidí si pagan mitad y mitad o la cancha completa, y sumá los extras.</li>
            <li>Abonás la seña dentro de los <?= e(MINUTOS_VENCE_PENDIENTE) ?> minutos y el turno queda tuyo.</li>
        </ol>
    </section>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
