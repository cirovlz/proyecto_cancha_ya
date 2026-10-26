<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

iniciar_sesion_segura();
if (usuario_actual() !== null) {
    redirigir('panel.php');
}

const MAX_INTENTOS = 5;
const BLOQUEO_SEGUNDOS = 300;

// Hash de relleno para verificar siempre una contraseña, exista o no el correo
const HASH_RELLENO = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234';

$error = '';
$gmail = '';
$aviso = $_SESSION['aviso'] ?? '';
unset($_SESSION['aviso']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = mb_strtolower(trim((string) ($_POST['gmail'] ?? '')));
    $clave = (string) ($_POST['clave'] ?? '');

    if (($_SESSION['bloqueo_hasta'] ?? 0) > time()) {
        $error = 'Demasiados intentos. Esperá unos minutos antes de volver a probar.';
    } elseif (!csrf_validar()) {
        $error = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } else {
        try {
            $pdo = (new Conexion())->getConexion();
            $stmt = $pdo->prepare(
                'SELECT ID_persona, Nombre, Apellido, contrasena, ID_Cargo
                 FROM persona WHERE Gmail = :gmail AND activo = 1 LIMIT 1'
            );
            $stmt->execute([':gmail' => $gmail]);
            $persona = $stmt->fetch();

            $hash = $persona ? $persona['contrasena'] : HASH_RELLENO;
            $ok = password_verify($clave, $hash) && $persona;

            if ($ok) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $upd = $pdo->prepare('UPDATE persona SET contrasena = :h WHERE ID_persona = :id');
                    $upd->execute([':h' => password_hash($clave, PASSWORD_DEFAULT), ':id' => $persona['ID_persona']]);
                }
                unset($_SESSION['intentos'], $_SESSION['bloqueo_hasta']);
                loguear_usuario($persona);
                redirigir('panel.php');
            }

            $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
            if ($_SESSION['intentos'] >= MAX_INTENTOS) {
                $_SESSION['bloqueo_hasta'] = time() + BLOQUEO_SEGUNDOS;
                $_SESSION['intentos'] = 0;
            }
            $error = 'El correo o la contraseña no coinciden.';
        } catch (PDOException $ex) {
            error_log('Login: ' . $ex->getMessage());
            $error = 'No pudimos iniciar sesión. Intentá de nuevo en unos minutos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | CanchaYa</title>
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/img/favicon.png">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/base.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
</head>
<body class="auth">
<main class="auth-layout">
    <section class="auth-marca">
        <a class="logo" href="<?= BASE_URL ?>/login.php"><img class="logo-img" src="<?= ASSETS_URL ?>/img/logo.png" alt="CanchaYa"></a>
        <h1>Reservá tu cancha de fútbol 8 en minutos</h1>
        <p>Turnos, extras, ligas y estadísticas de tu equipo en un solo lugar.</p>
    </section>

    <section class="auth-panel">
        <form class="tarjeta" method="POST" action="login.php" novalidate>
            <h2>Iniciar sesión</h2>

            <?php if ($aviso): ?><div class="alerta alerta-ok" role="status"><?= e($aviso) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alerta alerta-error" role="alert"><?= e($error) ?></div><?php endif; ?>

            <?= csrf_campo() ?>

            <div class="campo">
                <label for="gmail">Correo electrónico</label>
                <input id="gmail" name="gmail" type="email" required autocomplete="email" value="<?= e($gmail) ?>">
            </div>

            <div class="campo">
                <label for="clave">Contraseña</label>
                <div class="con-boton">
                    <input id="clave" name="clave" type="password" required autocomplete="current-password">
                    <button type="button" class="ver-clave" data-toggle-clave="clave">Mostrar</button>
                </div>
            </div>

            <button class="btn btn-principal" type="submit">Iniciar sesión</button>
            <p class="pie-form">¿Primera vez? <a href="registro.php">Creá tu cuenta</a></p>
        </form>
    </section>
</main>
<script src="<?= ASSETS_URL ?>/js/validaciones.js"></script>
</body>
</html>
