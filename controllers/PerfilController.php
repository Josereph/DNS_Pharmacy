<?php
session_start();

require_once __DIR__ . '/../models/PerfilModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Sesión no iniciada'
    ]);
    exit;
}

$model = new PerfilModel();
$id_usuario = $_SESSION['usuario_id'];

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'perfil':
        $perfil = $model->obtenerPerfil($id_usuario);
        echo json_encode(['error' => false, 'data' => $perfil]);
        break;

    case 'ventas':
        $ventas = $model->obtenerVentas($id_usuario);
        echo json_encode(['error' => false, 'data' => $ventas]);
        break;

    case 'detalle':
        $id_venta = intval($_GET['id_venta'] ?? 0);
        $detalle = $model->obtenerDetalle($id_venta);
        echo json_encode(['error' => false, 'data' => $detalle]);
        break;

    default:
        echo json_encode([
            'error' => true,
            'mensaje' => 'Acción no válida'
        ]);
        break;
}