<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = (new Conexion())->getConexion();
$canchas = $pdo->query('SELECT ID_cancha, tipo_cancha, tipo_cesped, precio_hora FROM cancha WHERE activa = 1 ORDER BY ID_cancha')->fetchAll();

$titulo = 'Reservá tu cancha';
$activo = 'inicio';
require __DIR__ . '/../views/inicio.view.php';
