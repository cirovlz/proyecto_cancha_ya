<?php /* Variables: $titulo, $css (opcional), $bodyClass (opcional) */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'CanchaYa') ?> | CanchaYa</title>
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/img/favicon.png">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/base.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/layout.css">
<?php foreach (($css ?? []) as $hoja): ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= e($hoja) ?>.css">
<?php endforeach; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
