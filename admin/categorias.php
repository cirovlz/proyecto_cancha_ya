<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin.php';

$usuario = requerir_rol([CARGO_ADMIN, CARGO_ATENCION]);
$pdo = (new Conexion())->getConexion();
$errores = [];
$form = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    $st = $pdo->prepare('SELECT categoria FROM categoria WHERE ID_categoria = :id');
    $st->execute([':id' => $id]);
    $actual = $st->fetchColumn();

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } elseif ($accion === 'borrar') {
        $cant = $pdo->prepare('SELECT COUNT(*) FROM producto WHERE ID_categoria = :id');
        $cant->execute([':id' => $id]);
        if ($actual === 'Extras') {
            $errores[] = 'La categoría Extras la usa el formulario de reservas y no se puede borrar.';
        } elseif ((int) $cant->fetchColumn() > 0) {
            $errores[] = 'Esa categoría tiene productos. Movelos a otra categoría antes de borrarla.';
        } else {
            $pdo->prepare('DELETE FROM categoria WHERE ID_categoria = :id')->execute([':id' => $id]);
            ir_admin('categorias.php', 'Borramos la categoría.');
        }
    } elseif ($accion === 'guardar') {
        $nombre = trim((string) ($_POST['categoria'] ?? ''));
        $form = ['id' => $id, 'categoria' => $nombre];
        $dup = $pdo->prepare('SELECT COUNT(*) FROM categoria WHERE categoria = :c AND ID_categoria <> :id');
        $dup->execute([':c' => $nombre, ':id' => $id]);

        if (!largo_ok($nombre, 255)) {
            $errores[] = 'Ingresá el nombre de la categoría.';
        } elseif ((int) $dup->fetchColumn() > 0) {
            $errores[] = 'Ya existe una categoría con ese nombre.';
        } elseif ($actual === 'Extras' && $nombre !== 'Extras') {
            $errores[] = 'La categoría Extras no se puede renombrar porque la usa el formulario de reservas.';
        } else {
            if ($id === 0) {
                $pdo->prepare('INSERT INTO categoria (categoria) VALUES (:c)')->execute([':c' => $nombre]);
                ir_admin('categorias.php', 'Creamos la categoría.');
            }
            $pdo->prepare('UPDATE categoria SET categoria = :c WHERE ID_categoria = :id')->execute([':c' => $nombre, ':id' => $id]);
            ir_admin('categorias.php', 'Guardamos los cambios.');
        }
    }
} elseif (isset($_GET['nuevo'])) {
    $form = ['id' => 0, 'categoria' => ''];
} elseif (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT ID_categoria AS id, categoria FROM categoria WHERE ID_categoria = :id');
    $st->execute([':id' => (int) $_GET['editar']]);
    $form = $st->fetch() ?: null;
}

$categorias = $pdo->query(
    'SELECT c.ID_categoria, c.categoria, COUNT(p.ID_producto) AS productos
     FROM categoria c LEFT JOIN producto p ON p.ID_categoria = c.ID_categoria
     GROUP BY c.ID_categoria, c.categoria ORDER BY c.categoria'
)->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Categorías';
$activo = 'categorias';
$boton = ['Nueva categoría', 'categorias.php?nuevo=1'];
require __DIR__ . '/../views/admin/categorias.view.php';
