<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin.php';

$usuario = requerir_rol([CARGO_ADMIN]);
$pdo = (new Conexion())->getConexion();
$errores = [];
$cargos = $pdo->query('SELECT ID_Cargo, nombre FROM cargos ORDER BY ID_Cargo')->fetchAll(PDO::FETCH_KEY_PAIR);
$vista = ($_GET['ver'] ?? $_POST['ver'] ?? 'clientes') === 'empleados' ? 'empleados' : 'clientes';
$form = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (!csrf_validar()) {
        $errores[] = 'La sesión del formulario venció. Recargá la página e intentá de nuevo.';
    } elseif ($accion === 'estado') {
        if ($id === $usuario['id']) {
            $errores[] = 'No podés desactivar tu propia cuenta.';
        } else {
            $pdo->prepare('UPDATE persona SET activo = 1 - activo WHERE ID_persona = :id')->execute([':id' => $id]);
            ir_admin('usuarios.php?ver=' . $vista, 'Actualizamos el estado de la cuenta.');
        }
    } elseif ($accion === 'guardar') {
        $d = [
            'id' => $id,
            'Nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'Apellido' => trim((string) ($_POST['apellido'] ?? '')),
            'DNI' => trim((string) ($_POST['dni'] ?? '')),
            'Gmail' => mb_strtolower(trim((string) ($_POST['gmail'] ?? ''))),
            'telefono' => trim((string) ($_POST['telefono'] ?? '')),
            'ID_Cargo' => (int) ($_POST['cargo'] ?? 0),
        ];
        $clave = (string) ($_POST['clave'] ?? '');
        $form = $d;

        if (!largo_ok($d['Nombre'], 100)) { $errores[] = 'Ingresá el nombre (hasta 100 caracteres).'; }
        if (!largo_ok($d['Apellido'], 100)) { $errores[] = 'Ingresá el apellido (hasta 100 caracteres).'; }
        if (!preg_match('/^\d{7,8}$/', $d['DNI'])) { $errores[] = 'El DNI debe tener 7 u 8 números.'; }
        if (!filter_var($d['Gmail'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['Gmail']) > 255) { $errores[] = 'Ingresá un correo válido.'; }
        if ($d['telefono'] !== '' && !preg_match('/^[0-9+\s()-]{6,30}$/', $d['telefono'])) { $errores[] = 'El teléfono solo puede tener números, espacios, + y guiones.'; }
        if (!isset($cargos[$d['ID_Cargo']])) { $errores[] = 'Elegí un cargo.'; }
        if ($id === 0 && strlen($clave) < 8) { $errores[] = 'La contraseña inicial debe tener al menos 8 caracteres.'; }
        if ($id > 0 && $clave !== '' && strlen($clave) < 8) { $errores[] = 'La contraseña nueva debe tener al menos 8 caracteres.'; }
        if ($id === $usuario['id'] && $d['ID_Cargo'] !== CARGO_ADMIN) { $errores[] = 'No podés quitarte el cargo de administrador a vos mismo.'; }

        if (!$errores) {
            try {
                $params = [':n' => $d['Nombre'], ':a' => $d['Apellido'], ':dni' => (int) $d['DNI'], ':g' => $d['Gmail'],
                           ':t' => $d['telefono'] !== '' ? $d['telefono'] : null, ':c' => $d['ID_Cargo']];
                if ($id === 0) {
                    $params[':p'] = password_hash($clave, PASSWORD_DEFAULT);
                    $pdo->prepare('INSERT INTO persona (Nombre, Apellido, DNI, Gmail, telefono, ID_Cargo, contrasena) VALUES (:n, :a, :dni, :g, :t, :c, :p)')->execute($params);
                    $msg = 'Creamos la cuenta.';
                } else {
                    $sql = 'UPDATE persona SET Nombre = :n, Apellido = :a, DNI = :dni, Gmail = :g, telefono = :t, ID_Cargo = :c';
                    if ($clave !== '') {
                        $sql .= ', contrasena = :p';
                        $params[':p'] = password_hash($clave, PASSWORD_DEFAULT);
                    }
                    $params[':id'] = $id;
                    $pdo->prepare($sql . ' WHERE ID_persona = :id')->execute($params);
                    $msg = 'Guardamos los cambios.';
                }
                ir_admin('usuarios.php?ver=' . ($d['ID_Cargo'] === CARGO_CLIENTE ? 'clientes' : 'empleados'), $msg);
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {
                    $errores[] = 'Ya existe otra cuenta con ese correo o ese DNI.';
                } else {
                    error_log('Usuarios: ' . $ex->getMessage());
                    $errores[] = 'No pudimos guardar. Intentá de nuevo en unos minutos.';
                }
            }
        }
    }
} elseif (isset($_GET['nuevo'])) {
    $form = ['id' => 0, 'Nombre' => '', 'Apellido' => '', 'DNI' => '', 'Gmail' => '', 'telefono' => '',
             'ID_Cargo' => $vista === 'clientes' ? CARGO_CLIENTE : CARGO_CANCHERO];
} elseif (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT ID_persona AS id, Nombre, Apellido, DNI, Gmail, telefono, ID_Cargo FROM persona WHERE ID_persona = :id');
    $st->execute([':id' => (int) $_GET['editar']]);
    $form = $st->fetch() ?: null;
}

$st = $pdo->query(
    'SELECT p.ID_persona, p.DNI, p.Nombre, p.Apellido, p.Gmail, p.telefono, p.activo, c.nombre AS cargo
     FROM persona p JOIN cargos c ON c.ID_Cargo = p.ID_Cargo
     WHERE p.ID_Cargo ' . ($vista === 'clientes' ? '= ' : '<> ') . CARGO_CLIENTE . ' ORDER BY p.Apellido, p.Nombre'
);
$personas = $st->fetchAll();

$aviso = tomar_aviso();
$titulo = 'Usuarios y empleados';
$activo = 'usuarios';
$boton = ['Nueva cuenta', 'usuarios.php?ver=' . $vista . '&nuevo=1'];
require __DIR__ . '/../views/admin/usuarios.view.php';
