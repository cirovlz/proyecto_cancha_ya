<?php
declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';
function ahora(): string { return date('Y-m-d H:i:s'); }

function horas_disponibles(): array { 
    return range(HORA_INICIO, HORA_FIN - 1); 
}

function hora_valida(int $h): bool { return $h >= HORA_INICIO && $h < HORA_FIN; }

function fecha_valida(string $f): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $f);
    if (!$d || $d->format('Y-m-d') !== $f) {
        return false;
    }
    $hoy = new DateTime('today');
    $max = (clone $hoy)->modify('+' . DIAS_ADELANTE . ' days');
    return $d >= $hoy && $d <= $max;
}

function inicio_turno(string $fecha, int $hora): string { return sprintf('%s %02d:00:00', $fecha, $hora); }

function turno_en_futuro(string $inicio): bool { return strtotime($inicio) > time(); }

/** Un turno "ocupa" el horario si está señado/confirmado o pendiente y sin vencer. */
function sql_activo(PDO $pdo): string
{
    return "(estado IN ('senado','confirmado') OR (estado = 'pendiente' AND vence_en > " . $pdo->quote(ahora()) . "))";
}

function turno_ocupado(PDO $pdo, int $cancha, string $inicio, int $excluir = 0): bool
{
    $s = $pdo->prepare('SELECT 1 FROM turno WHERE ID_cancha = :c AND fecha = :f AND ID_turno <> :x AND ' . sql_activo($pdo) . ' LIMIT 1');
    $s->execute([':c' => $cancha, ':f' => $inicio, ':x' => $excluir]);
    return (bool) $s->fetchColumn();
}

/** Devuelve ["cancha|hora" => true] de los horarios tomados ese día. */
function ocupados_del_dia(PDO $pdo, string $fecha, int $excluir = 0): array
{
    $s = $pdo->prepare('SELECT ID_cancha, HOUR(fecha) AS h FROM turno WHERE DATE(fecha) = :d AND ID_turno <> :x AND ' . sql_activo($pdo));
    $s->execute([':d' => $fecha, ':x' => $excluir]);
    $mapa = [];
    foreach ($s->fetchAll() as $f) {
        $mapa[$f['ID_cancha'] . '|' . $f['h']] = true;
    }
    return $mapa;
}

function turnos_activos_usuario(PDO $pdo, int $idPersona): int
{
    $s = $pdo->prepare('SELECT COUNT(*) FROM turno WHERE ID_persona = :p AND fecha > ' . $pdo->quote(ahora()) . ' AND ' . sql_activo($pdo));
    $s->execute([':p' => $idPersona]);
    return (int) $s->fetchColumn();
}

/** Bloquea la fila de la cancha dentro de una transacción para evitar dobles reservas. */
function bloquear_cancha(PDO $pdo, int $id): ?array
{
    $s = $pdo->prepare('SELECT ID_cancha, precio_hora FROM cancha WHERE ID_cancha = :c AND activa = 1 FOR UPDATE');
    $s->execute([':c' => $id]);
    return $s->fetch() ?: null;
}

function turno_vencido(array $t): bool
{
    return $t['estado'] === 'pendiente' && $t['vence_en'] !== null && $t['vence_en'] < ahora();
}

/** El cliente puede modificar o cancelar si falta tiempo suficiente y el turno sigue vigente. */
function puede_cambiar(array $t): bool
{
    if ($t['estado'] === 'cancelado' || turno_vencido($t)) {
        return false;
    }
    return strtotime($t['fecha']) - time() >= (HORAS_MIN_CAMBIO * 3600);
}

/** [texto, clase css] */
function estado_texto(array $t): array
{
    if (turno_vencido($t)) {
        return ['Vencido', 'vencido'];
    }
    return match ($t['estado']) {
        'pendiente'  => ['Pendiente de seña', 'pendiente'],
        'senado'     => ['Seña pagada', 'senado'],
        'confirmado' => ['Confirmado', 'confirmado'],
        default      => ['Cancelado', 'cancelado'],
    };
}

function tomar_aviso(): string
{
    iniciar_sesion_segura();
    $a = $_SESSION['aviso'] ?? '';
    unset($_SESSION['aviso']);
    return (string) $a;
}

function redirigir_url(string $url): never
{
    header('Location: ' . $url);
    exit;
}
