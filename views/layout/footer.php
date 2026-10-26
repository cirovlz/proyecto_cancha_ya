<footer class="pie" id="contacto">
    <div>
        <strong>CanchaYa</strong>
        <p>Reservá tu cancha de fútbol 8, elegí extras y armá tu equipo.</p>
    </div>
    <div>
        <strong>Contacto</strong>
        <p>Teléfono: <?= e(CONTACTO_TEL) ?><br>
        Correo: <a href="mailto:<?= e(CONTACTO_MAIL) ?>"><?= e(CONTACTO_MAIL) ?></a><br>
        Instagram: <a href="https://instagram.com/<?= e(CONTACTO_IG) ?>" target="_blank" rel="noopener">@<?= e(CONTACTO_IG) ?></a></p>
    </div>
</footer>
<?php require __DIR__ . '/cierre.php'; ?>
