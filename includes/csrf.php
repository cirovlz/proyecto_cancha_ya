<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function csrf_token(): string
{
    iniciar_sesion_segura();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Imprime el input oculto dentro de cada formulario POST. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): bool
{
    iniciar_sesion_segura();
    $enviado = $_POST['csrf'] ?? '';
    return is_string($enviado) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $enviado);
}