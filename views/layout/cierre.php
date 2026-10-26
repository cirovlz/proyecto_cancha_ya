<script src="<?= ASSETS_URL ?>/js/menu.js"></script>
<?php foreach (($js ?? []) as $script): ?>
<script src="<?= ASSETS_URL ?>/js/<?= e($script) ?>.js"></script>
<?php endforeach; ?>
</body>
</html>
