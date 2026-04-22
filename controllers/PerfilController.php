<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => true, 'mensaje' => 'Sesión no iniciada']);
    exit;
}

require_once __DIR__ . '/../models/PerfilModel.php';

header('Content-Type: application/json');

$model      = new PerfilModel();
$id_usuario = (int) $_SESSION['usuario_id'];
$action     = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    /* ── Obtener perfil ── */
    case 'perfil':
        $perfil = $model->obtenerPerfil($id_usuario);
        echo json_encode(['error' => false, 'data' => $perfil]);
        break;

    /* ── Obtener ventas ── */
    case 'ventas':
        $ventas = $model->obtenerVentas($id_usuario);
        echo json_encode(['error' => false, 'data' => $ventas]);
        break;

    /* ── Detalle de venta ── */
    case 'detalle':
        $id_venta = (int) ($_GET['id_venta'] ?? 0);
        $detalle  = $model->obtenerDetalle($id_venta);
        echo json_encode(['error' => false, 'data' => $detalle]);
        break;

    /* ── Actualizar nombre, apellido, telefono ── */
    case 'actualizar':
        $nombre   = trim($_POST['nombre']   ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($nombre === '') {
            echo json_encode(['error' => true, 'campo' => 'nombre', 'mensaje' => 'El nombre es obligatorio.']);
            break;
        }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u', $nombre)) {
            echo json_encode(['error' => true, 'campo' => 'nombre', 'mensaje' => 'Solo se permiten letras, sin números ni símbolos.']);
            break;
        }
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 50) {
            echo json_encode(['error' => true, 'campo' => 'nombre', 'mensaje' => 'Entre 2 y 50 caracteres.']);
            break;
        }

        if ($apellido === '') {
            echo json_encode(['error' => true, 'campo' => 'apellido', 'mensaje' => 'El apellido es obligatorio.']);
            break;
        }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u', $apellido)) {
            echo json_encode(['error' => true, 'campo' => 'apellido', 'mensaje' => 'Solo se permiten letras, sin números ni símbolos.']);
            break;
        }
        if (mb_strlen($apellido) < 2 || mb_strlen($apellido) > 50) {
            echo json_encode(['error' => true, 'campo' => 'apellido', 'mensaje' => 'Entre 2 y 50 caracteres.']);
            break;
        }

        $telefonoFinal = null;
        if ($telefono !== '') {
            $soloDigitos = preg_replace('/\D/', '', $telefono);
            if (strlen($soloDigitos) !== 8) {
                echo json_encode(['error' => true, 'campo' => 'telefono', 'mensaje' => 'El teléfono debe tener exactamente 8 dígitos.']);
                break;
            }
            $telefonoFinal = $soloDigitos;
        }

        $ok = $model->actualizarPerfil($id_usuario, $nombre, $apellido, $telefonoFinal);
        echo json_encode([
            'error'   => !$ok,
            'mensaje' => $ok ? 'Perfil actualizado correctamente.' : 'Error al actualizar el perfil.'
        ]);
        break;

    /* ── Cambiar contraseña ── */
    case 'cambiarPassword':
        $password_actual = trim($_POST['password_actual'] ?? '');
        $password_nuevo  = trim($_POST['password_nuevo']  ?? '');

        if ($password_actual === '') {
            echo json_encode(['error' => true, 'campo' => 'actual', 'mensaje' => 'Ingresa tu contraseña actual.']);
            break;
        }
        if ($password_nuevo === '') {
            echo json_encode(['error' => true, 'campo' => 'nueva', 'mensaje' => 'Ingresa la nueva contraseña.']);
            break;
        }
        if (strlen($password_nuevo) < 8) {
            echo json_encode(['error' => true, 'campo' => 'nueva', 'mensaje' => 'Mínimo 8 caracteres.']);
            break;
        }
        if (!preg_match('/[A-Z]/', $password_nuevo)) {
            echo json_encode(['error' => true, 'campo' => 'nueva', 'mensaje' => 'Debe contener al menos una letra mayúscula.']);
            break;
        }
        if (!preg_match('/[0-9]/', $password_nuevo)) {
            echo json_encode(['error' => true, 'campo' => 'nueva', 'mensaje' => 'Debe contener al menos un número.']);
            break;
        }

        $hash_actual = $model->obtenerHash($id_usuario);
        if (!$hash_actual || !password_verify($password_actual, $hash_actual)) {
            echo json_encode(['error' => true, 'campo' => 'actual', 'mensaje' => 'La contraseña actual es incorrecta.']);
            break;
        }

        $nuevo_hash = password_hash($password_nuevo, PASSWORD_BCRYPT);
        $ok = $model->actualizarPassword($id_usuario, $nuevo_hash);
        echo json_encode([
            'error'   => !$ok,
            'mensaje' => $ok ? 'Contraseña actualizada correctamente.' : 'Error al actualizar la contraseña.'
        ]);
        break;

    /* ── Subir foto ── */
    case 'subirFoto':
        if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => true, 'mensaje' => 'No se recibió ninguna imagen.']);
            break;
        }
        $file    = $_FILES['foto_perfil'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['error' => true, 'mensaje' => 'La imagen no debe superar 2MB.']);
            break;
        }
        if (!in_array(mime_content_type($file['tmp_name']), $allowed)) {
            echo json_encode(['error' => true, 'mensaje' => 'Solo se permiten imágenes JPG, PNG o WEBP.']);
            break;
        }

        $dir = __DIR__ . '/../uploads/perfiles/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'perfil_' . $id_usuario . '_' . time() . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            echo json_encode(['error' => true, 'mensaje' => 'Error al guardar la imagen.']);
            break;
        }

        $ok = $model->actualizarFoto($id_usuario, $filename);
        echo json_encode([
            'error'   => !$ok,
            'mensaje' => $ok ? 'Foto actualizada.' : 'Error al guardar la foto.',
            'foto'    => $filename
        ]);
        break;

    default:
        echo json_encode(['error' => true, 'mensaje' => 'Acción no válida.']);
        break;
}