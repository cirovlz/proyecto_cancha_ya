<?php
/* Variables: $titulo, $usuario, $activo, $aviso (opc), $errores (opc), $boton [texto, url] (opc) */
$bodyClass = 'admin';
$css = ['admin'];
require __DIR__ . '/head.php';
?>
<div class="admin-wrap">
<?php require __DIR__ . '/admin_sidebar.php'; ?>
<main class="admin-main">
    <div class="cabecera-pagina">
        <h1><?= e($titulo) ?></h1>
        <?php if (!empty($boton)): ?><a class="btn btn-principal btn-auto" href="<?= e($boton[1]) ?>"><?= e($boton[0]) ?></a><?php endif; ?>
    </div>
    <?php if (!empty($aviso)): ?><div class="alerta alerta-ok" role="status"><?= e($aviso) ?></div><?php endif; ?>
    <?php if (!empty($errores)): ?>
        <div class="alerta alerta-error" role="alert"><ul><?php foreach ($errores as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
