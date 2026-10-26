<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin.php';

$usuario = requerir_rol([CARGO_ADMIN, CARGO_ATENCION]);
$pdo = (new Conexion())->getConexion();
$errores = [];
$categorias = $pdo->query('SELECT ID_categoria, categoria FROM categoria ORDER BY categoria')->fetchAll(PDO::FETCH_KEY_PAIR);
$form = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } elseif ($accion === 'reponer') {
        $cant = (int) ($_POST['cantidad'] ?? 0);
        if ($cant < 1 || $cant > 1000) {
            $errores[] = 'La cantidad a reponer tiene que estar entre 1 y 1000.';
        } else {
            $pdo->prepare('UPDATE producto SET stock = stock + :q WHERE ID_producto = :id')->execute([':q' => $cant, ':id' => $id]);
            ir_admin('productos.php', 'Repusiste ' . $cant . ' unidades.');
        }
    } elseif ($accion === 'borrar') {
        try {
            $pdo->prepare('DELETE FROM producto WHERE ID_producto = :id')->execute([':id' => $id]);
            ir_admin('productos.php', 'Borramos el producto.');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ese producto tiene ventas o reservas asociadas y no se puede borrar. Dejá su stock en 0 para ocultarlo.';
            } else {
                error_log('Productos: ' . $ex->getMessage());
                $errores[] = 'No pudimos borrar el producto.';
            }
        }
    } elseif ($accion === 'guardar') {
        $form = [
            'id' => $id,
            'producto' => trim((string) ($_POST['producto'] ?? '')),
            'precio' => trim((string) ($_POST['precio'] ?? '')),
            'ID_categoria' => (int) ($_POST['categoria'] ?? 0),
            'stock' => trim((string) ($_POST['stock'] ?? '')),
        ];
        if (!largo_ok($form['producto'], 100)) { $errores[] = 'Ingresá el nombre del producto (hasta 100 caracteres).'; }
        if (!is_numeric($form['precio']) || (float) $form['precio'] < 0 || (float) $form['precio'] > 9999999) { $errores[] = 'El precio tiene que ser un número igual o mayor a 0.'; }
        if (!isset($categorias[$form['ID_categoria']])) { $errores[] = 'Elegí una categoría.'; }
        if (!ctype_digit($form['stock']) || (int) $form['stock'] > 100000) { $errores[] = 'El stock tiene que ser un número entero igual o mayor a 0.'; }

        if (!$errores) {
            $params = [':p' => $form['producto'], ':pr' => (float) $form['precio'], ':c' => $form['ID_categoria'], ':s' => (int) $form['stock']];
            if ($id === 0) {
                $params[':u'] = $usuario['id'];
                $pdo->prepare('INSERT INTO producto (producto, precio, ID_categoria, stock, ID_persona) VALUES (:p, :pr, :c, :s, :u)')->execute($params);
                ir_admin('productos.php', 'Creamos el producto.');
            }
            $params[':id'] = $id;
            $pdo->prepare('UPDATE producto SET producto = :p, precio = :pr, ID_categoria = :c, stock = :s WHERE ID_producto = :id')->execute($params);
            ir_admin('productos.php', 'Guardamos los cambios.');
        }
    }
} elseif (isset($_GET['nuevo'])) {
    $form = ['id' => 0, 'producto' => '', 'precio' => '', 'ID_categoria' => 0, 'stock' => '0'];
} elseif (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT ID_producto AS id, producto, precio, ID_categoria, stock FROM producto WHERE ID_producto = :id');
    $st->execute([':id' => (int) $_GET['editar']]);
    $form = $st->fetch() ?: null;
}

$productos = $pdo->query(
    'SELECT p.ID_producto, p.producto, p.precio, p.stock, c.categoria
     FROM producto p JOIN categoria c ON c.ID_categoria = p.ID_categoria ORDER BY c.categoria, p.producto'
)->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Productos';
$activo = 'productos';
$boton = ['Nuevo producto', 'productos.php?nuevo=1'];
require __DIR__ . '/../views/admin/productos.view.php';
