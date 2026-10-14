<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

// Solo por POST con token: evita que un enlace externo te cierre la sesión.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validar()) {
    cerrar_sesion();
}
redirigir('login.php');