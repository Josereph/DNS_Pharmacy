<?php
session_start();
require_once __DIR__ . '/../models/PerfilModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Sesión no iniciada']);
    exit;
}

$model = new PerfilModel();
$id_usuario = $_SESSION['usuario_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

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

    case 'actualizar':

        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        // 🔒 VALIDACIONES
        if (!$nombre || !$apellido || !$correo) {
            echo json_encode(['error' => true, 'mensaje' => 'Faltan campos obligatorios']);
            break;
        }

        if (!preg_match('/^[a-zA-ZáéíóúñÑ\s]{2,30}$/', $nombre)) {
            echo json_encode(['error' => true, 'mensaje' => 'Nombre inválido']);
            break;
        }

        if (!preg_match('/^[a-zA-ZáéíóúñÑ\s]{2,30}$/', $apellido)) {
            echo json_encode(['error' => true, 'mensaje' => 'Apellido inválido']);
            break;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => true, 'mensaje' => 'Correo inválido']);
            break;
        }

        if ($telefono && !preg_match('/^[0-9]{8}$/', $telefono)) {
            echo json_encode(['error' => true, 'mensaje' => 'Teléfono inválido (8 dígitos)']);
            break;
        }

        $ok = $model->actualizarPerfil($id_usuario, $nombre, $apellido, $correo, $telefono);

        if ($ok) {
            $_SESSION['nombre']   = $nombre;
            $_SESSION['apellido'] = $apellido;
        }

        echo json_encode([
            'error' => !$ok,
            'mensaje' => $ok ? 'Perfil actualizado correctamente' : 'Error al actualizar'
        ]);
        break;

    case 'cambiarPassword':

        $actual = trim($_POST['password_actual'] ?? '');
        $nueva  = trim($_POST['password_hash'] ?? '');

        if (!$actual || !$nueva) {
            echo json_encode(['error' => true, 'mensaje' => 'Completa todos los campos']);
            break;
        }

        if (strlen($nueva) < 8) {
            echo json_encode(['error' => true, 'mensaje' => 'Mínimo 8 caracteres']);
            break;
        }

        $hash = $model->obtenerHash($id_usuario);

        if (!$hash || !password_verify($actual, $hash)) {
            echo json_encode(['error' => true, 'mensaje' => 'Contraseña actual incorrecta']);
            break;
        }

        $nuevoHash = password_hash($nueva, PASSWORD_BCRYPT);
        $ok = $model->actualizarPassword($id_usuario, $nuevoHash);

        echo json_encode([
            'error' => !$ok,
            'mensaje' => $ok ? 'Contraseña actualizada' : 'Error al actualizar'
        ]);
        break;

    case 'subirFoto':

        if (!isset($_FILES['foto_perfil'])) {
            echo json_encode(['error' => true, 'mensaje' => 'No hay archivo']);
            break;
        }

        $file = $_FILES['foto_perfil'];

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['error' => true, 'mensaje' => 'Máximo 2MB']);
            break;
        }

        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array(mime_content_type($file['tmp_name']), $permitidos)) {
            echo json_encode(['error' => true, 'mensaje' => 'Formato inválido']);
            break;
        }

        $dir = __DIR__ . '/../uploads/perfiles/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'perfil_' . $id_usuario . '_' . time() . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . $nombreArchivo)) {
            echo json_encode(['error' => true, 'mensaje' => 'Error al subir imagen']);
            break;
        }

        $model->actualizarFoto($id_usuario, $nombreArchivo);

        echo json_encode([
            'error' => false,
            'foto' => $nombreArchivo
        ]);
        break;

    default:
        echo json_encode(['error' => true, 'mensaje' => 'Acción no válida']);
        break;
}