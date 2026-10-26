<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin.php';

$usuario = requerir_rol([CARGO_ADMIN, CARGO_ATENCION]);
$pdo = (new Conexion())->getConexion();
$errores = [];
$pagos = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'mercadopago' => 'Mercado Pago'];
$mostrar = isset($_GET['nueva']) || $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'vender';

$clientes = $pdo->query('SELECT ID_persona, Nombre, Apellido FROM persona WHERE ID_Cargo = ' . CARGO_CLIENTE . ' AND activo = 1 ORDER BY Apellido, Nombre')->fetchAll();
$productos = $pdo->query('SELECT ID_producto, producto, precio, stock FROM producto WHERE stock > 0 ORDER BY producto')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } elseif ($accion === 'anular' && $usuario['cargo'] === CARGO_ADMIN) {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $pdo->beginTransaction();
            $det = $pdo->prepare('SELECT ID_producto, cantidad FROM detalle_compra WHERE ID_compra = :id');
            $det->execute([':id' => $id]);
            foreach ($det->fetchAll() as $d) {
                $pdo->prepare('UPDATE producto SET stock = stock + :q WHERE ID_producto = :p')->execute([':q' => $d['cantidad'], ':p' => $d['ID_producto']]);
            }
            $pdo->prepare('DELETE FROM detalle_compra WHERE ID_compra = :id')->execute([':id' => $id]);
            $pdo->prepare('DELETE FROM compra WHERE ID_compra = :id')->execute([':id' => $id]);
            $pdo->commit();
            ir_admin('compras.php', 'Anulaste la compra y devolviste el stock.');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log('Anular compra: ' . $ex->getMessage());
            $errores[] = 'No pudimos anular la compra.';
        }
    } elseif ($accion === 'vender') {
        $idCliente = (int) ($_POST['cliente'] ?? 0);
        $pago = (string) ($_POST['pago'] ?? '');
        $pedidos = [];
        foreach ((array) ($_POST['cant'] ?? []) as $idp => $q) {
            $q = (int) $q;
            if ($q > 0 && $q <= 100) { $pedidos[(int) $idp] = $q; }
        }
        $comprador = $usuario['id'];
        if ($idCliente > 0) {
            if (!in_array($idCliente, array_column($clientes, 'ID_persona'), false)) { $errores[] = 'Elegí un cliente de la lista.'; }
            else { $comprador = $idCliente; }
        }
        if (!isset($pagos[$pago])) { $errores[] = 'Elegí el medio de pago.'; }
        if (!$pedidos) { $errores[] = 'Indicá la cantidad de al menos un producto.'; }

        if (!$errores) {
            try {
                $pdo->beginTransaction();
                $total = 0.0;
                $lineas = [];
                foreach ($pedidos as $idp => $q) {
                    $s = $pdo->prepare('SELECT precio, stock, producto FROM producto WHERE ID_producto = :id FOR UPDATE');
                    $s->execute([':id' => $idp]);
                    $p = $s->fetch();
                    if (!$p) { throw new RuntimeException('Un producto ya no existe.'); }
                    if ((int) $p['stock'] < $q) { throw new RuntimeException('No alcanza el stock de ' . $p['producto'] . ' (quedan ' . $p['stock'] . ').'); }
                    $total += $q * (float) $p['precio'];
                    $lineas[] = [$idp, $q, $p['precio']];
                }
                $pdo->prepare('INSERT INTO compra (monto_total, tipo_de_pago, ID_persona) VALUES (:t, :p, :c)')->execute([':t' => $total, ':p' => $pago, ':c' => $comprador]);
                $idCompra = (int) $pdo->lastInsertId();
                foreach ($lineas as [$idp, $q, $precio]) {
                    $pdo->prepare('INSERT INTO detalle_compra (ID_compra, ID_producto, cantidad, precio_unitario) VALUES (:c, :p, :q, :u)')->execute([':c' => $idCompra, ':p' => $idp, ':q' => $q, ':u' => $precio]);
                    $pdo->prepare('UPDATE producto SET stock = stock - :q WHERE ID_producto = :p')->execute([':q' => $q, ':p' => $idp]);
                }
                $pdo->commit();
                ir_admin('compras.php', 'Registraste la venta por ' . dinero($total) . '.');
            } catch (RuntimeException $ex) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $errores[] = $ex->getMessage();
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                error_log('Venta: ' . $ex->getMessage());
                $errores[] = 'No pudimos registrar la venta. Intentá de nuevo en unos minutos.';
            }
        }
    }
}

$compras = $pdo->query(
    "SELECT c.ID_compra, c.fecha_compra, c.monto_total, c.tipo_de_pago, p.Nombre, p.Apellido,
            GROUP_CONCAT(CONCAT(d.cantidad, ' x ', pr.producto) SEPARATOR ', ') AS items
     FROM compra c JOIN persona p ON p.ID_persona = c.ID_persona
     LEFT JOIN detalle_compra d ON d.ID_compra = c.ID_compra
     LEFT JOIN producto pr ON pr.ID_producto = d.ID_producto
     GROUP BY c.ID_compra, c.fecha_compra, c.monto_total, c.tipo_de_pago, p.Nombre, p.Apellido
     ORDER BY c.ID_compra DESC LIMIT 30"
)->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Compras';
$activo = 'compras';
$boton = ['Registrar venta', 'compras.php?nueva=1'];
require __DIR__ . '/../views/admin/compras.view.php';
