<?php
session_start();
require_once __DIR__ . '/../models/PerfilModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Sesión no iniciada']);
    exit;
}

$model      = new PerfilModel();
$id_usuario = $_SESSION['usuario_id'];
$action     = $_GET['action'] ?? $_POST['action'] ?? '';

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
        $detalle  = $model->obtenerDetalle($id_venta);
        echo json_encode(['error' => false, 'data' => $detalle]);
        break;

    case 'actualizar':
        $nombre   = trim($_POST['nombre']   ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo   = trim($_POST['correo']   ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        /* ── Campos obligatorios ── */
        if (!$nombre || !$apellido || !$correo) {
            echo json_encode(['error' => true, 'mensaje' => 'Faltan campos obligatorios.']);
            break;
        }

        /* ── Nombre ── */
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{2,30}$/', $nombre)) {
            echo json_encode(['error' => true, 'campo' => 'nombre', 'mensaje' => 'Nombre inválido.']);
            break;
        }

        /* ── Apellido ── */
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{2,30}$/', $apellido)) {
            echo json_encode(['error' => true, 'campo' => 'apellido', 'mensaje' => 'Apellido inválido.']);
            break;
        }

        /* ── Correo ── */
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => true, 'campo' => 'correo', 'mensaje' => 'Correo inválido.']);
            break;
        }

        /* ── Teléfono: quitar guión para validar, guardar con formato ── */
        if ($telefono !== '') {
            $tel_limpio = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($tel_limpio) !== 8) {
                echo json_encode(['error' => true, 'campo' => 'telefono', 'mensaje' => 'El teléfono debe tener 8 dígitos. Ej: 7600-0000']);
                break;
            }
            /* Guardar siempre con formato 0000-0000 */
            $telefono = substr($tel_limpio, 0, 4) . '-' . substr($tel_limpio, 4);
        }

        $ok = $model->actualizarPerfil($id_usuario, $nombre, $apellido, $correo, $telefono);

        if ($ok) {
            $_SESSION['nombre']   = $nombre;
            $_SESSION['apellido'] = $apellido;
        }

        echo json_encode([
            'error'   => !$ok,
            'mensaje' => $ok ? 'Perfil actualizado correctamente.' : 'Error al actualizar.'
        ]);
        break;

    case 'cambiarPassword':
        $actual = trim($_POST['password_actual'] ?? '');
        $nueva  = trim($_POST['password_hash']   ?? '');

        if (!$actual || !$nueva) {
            echo json_encode(['error' => true, 'mensaje' => 'Completa todos los campos.']);
            break;
        }
        if (strlen($nueva) < 8) {
            echo json_encode(['error' => true, 'campo' => 'nueva', 'mensaje' => 'Mínimo 8 caracteres.']);
            break;
        }
        if (!preg_match('/[A-Z]/', $nueva)) {
            echo json_encode(['error' => true, 'campo' => 'nueva', 'mensaje' => 'Debe contener al menos una mayúscula.']);
            break;
        }
        if (!preg_match('/[0-9]/', $nueva)) {
            echo json_encode(['error' => true, 'campo' => 'nueva', 'mensaje' => 'Debe contener al menos un número.']);
            break;
        }

        $hash = $model->obtenerHash($id_usuario);
        if (!$hash || !password_verify($actual, $hash)) {
            echo json_encode(['error' => true, 'campo' => 'actual', 'mensaje' => 'Contraseña actual incorrecta.']);
            break;
        }

        $nuevoHash = password_hash($nueva, PASSWORD_BCRYPT);
        $ok        = $model->actualizarPassword($id_usuario, $nuevoHash);

        echo json_encode([
            'error'   => !$ok,
            'mensaje' => $ok ? 'Contraseña actualizada.' : 'Error al actualizar.'
        ]);
        break;

    case 'subirFoto':
        if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => true, 'mensaje' => 'No se recibió ninguna imagen.']);
            break;
        }

        $file      = $_FILES['foto_perfil'];
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['error' => true, 'mensaje' => 'Máximo 2 MB.']);
            break;
        }
        if (!in_array(mime_content_type($file['tmp_name']), $permitidos)) {
            echo json_encode(['error' => true, 'mensaje' => 'Solo JPG, PNG o WEBP.']);
            break;
        }

        $dir = __DIR__ . '/../uploads/perfiles/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext           = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'perfil_' . $id_usuario . '_' . time() . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . $nombreArchivo)) {
            echo json_encode(['error' => true, 'mensaje' => 'Error al guardar imagen.']);
            break;
        }

        $model->actualizarFoto($id_usuario, $nombreArchivo);
        echo json_encode(['error' => false, 'foto' => $nombreArchivo]);
        break;

    default:
        echo json_encode(['error' => true, 'mensaje' => 'Acción no válida: ' . htmlspecialchars($action)]);
        break;
}