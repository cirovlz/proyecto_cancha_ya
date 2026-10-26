<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

iniciar_sesion_segura();
if (usuario_actual() !== null) {
    redirigir('panel.php');
}

$errores = [];
$v = ['nombre' => '', 'apellido' => '', 'dni' => '', 'gmail' => '', 'telefono' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($v) as $campo) {
        $v[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }
    $clave  = (string) ($_POST['clave'] ?? '');
    $clave2 = (string) ($_POST['clave2'] ?? '');

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    }
    if ($v['nombre'] === '' || mb_strlen($v['nombre']) > 100) {
        $errores[] = 'Ingresá tu nombre (hasta 100 caracteres).';
    }
    if ($v['apellido'] === '' || mb_strlen($v['apellido']) > 100) {
        $errores[] = 'Ingresá tu apellido (hasta 100 caracteres).';
    }
    if (!preg_match('/^\d{7,8}$/', $v['dni'])) {
        $errores[] = 'El DNI debe tener 7 u 8 números, sin puntos.';
    }
    if (!filter_var($v['gmail'], FILTER_VALIDATE_EMAIL) || mb_strlen($v['gmail']) > 255) {
        $errores[] = 'Ingresá un correo electrónico válido.';
    }
    if ($v['telefono'] !== '' && !preg_match('/^[0-9+\s()-]{6,30}$/', $v['telefono'])) {
        $errores[] = 'El teléfono solo puede tener números, espacios, + y guiones.';
    }
    if (strlen($clave) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($clave !== $clave2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        try {
            $pdo = (new Conexion())->getConexion();
            $stmt = $pdo->prepare(
                'INSERT INTO persona (DNI, Nombre, Apellido, Gmail, telefono, contrasena, ID_Cargo)
                 VALUES (:dni, :nombre, :apellido, :gmail, :telefono, :clave, :cargo)'
            );
            $stmt->execute([
                ':dni'      => (int) $v['dni'],
                ':nombre'   => $v['nombre'],
                ':apellido' => $v['apellido'],
                ':gmail'    => mb_strtolower($v['gmail']),
                ':telefono' => $v['telefono'] !== '' ? $v['telefono'] : null,
                ':clave'    => password_hash($clave, PASSWORD_DEFAULT),
                ':cargo'    => CARGO_CLIENTE, // el registro público SIEMPRE crea clientes
            ]);
            $_SESSION['aviso'] = 'Cuenta creada. Ya podés iniciar sesión.';
            redirigir('login.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ya existe una cuenta con ese correo o ese DNI.';
            } else {
                error_log('Registro: ' . $ex->getMessage());
                $errores[] = 'No pudimos crear tu cuenta. Intentá de nuevo en unos minutos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | CanchaYa</title>
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/img/favicon.png">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/base.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
</head>
<body class="auth">
<main class="auth-layout">
    <section class="auth-marca">
        <a class="logo" href="<?= BASE_URL ?>/login.php"><img class="logo-img" src="<?= ASSETS_URL ?>/img/logo.png" alt="CanchaYa"></a>
        <h1>Armá tu cuenta y reservá tu turno</h1>
        <p>Elegí cancha, horario y césped. Pagás la seña y el resto se arregla en la cancha.</p>
    </section>

    <section class="auth-panel">
        <form class="tarjeta" method="POST" action="registro.php" novalidate>
            <h2>Crear cuenta</h2>

            <?php if ($errores): ?>
                <div class="alerta alerta-error" role="alert">
                    <ul>
                        <?php foreach ($errores as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?= csrf_campo() ?>

            <div class="fila">
                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" maxlength="100" required autocomplete="given-name" value="<?= e($v['nombre']) ?>">
                </div>
                <div class="campo">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" name="apellido" type="text" maxlength="100" required autocomplete="family-name" value="<?= e($v['apellido']) ?>">
                </div>
            </div>

            <div class="fila">
                <div class="campo">
                    <label for="dni">DNI</label>
                    <input id="dni" name="dni" type="text" inputmode="numeric" pattern="\d{7,8}" required value="<?= e($v['dni']) ?>">
                </div>
                <div class="campo">
                    <label for="telefono">Teléfono (opcional)</label>
                    <input id="telefono" name="telefono" type="tel" autocomplete="tel" value="<?= e($v['telefono']) ?>">
                </div>
            </div>

            <div class="campo">
                <label for="gmail">Correo electrónico</label>
                <input id="gmail" name="gmail" type="email" maxlength="255" required autocomplete="email" value="<?= e($v['gmail']) ?>">
            </div>

            <div class="campo">
                <label for="clave">Contraseña (mínimo 8 caracteres)</label>
                <input id="clave" name="clave" type="password" minlength="8" required autocomplete="new-password">
            </div>

            <div class="campo">
                <label for="clave2">Repetí la contraseña</label>
                <input id="clave2" name="clave2" type="password" minlength="8" required autocomplete="new-password">
            </div>

            <button class="btn btn-principal" type="submit">Crear cuenta</button>
            <p class="pie-form">¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a></p>
        </form>
    </section>
</main>
<script src="<?= ASSETS_URL ?>/js/validaciones.js"></script>
</body>
</html>
