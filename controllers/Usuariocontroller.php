<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {

    /* ── Listar usuarios ── */
    case 'listar':
        $conn = conectar();
        $r    = $conn->query("
            SELECT u.id_usuario, u.nombre, u.apellido, u.correo,
                   u.telefono, u.estado, u.ultimo_acceso, u.created_at,
                   r.nombre AS rol, u.id_rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.created_at DESC
        ");
        $data = $r->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $data]);
        break;

    /* ── Stats ── */
    case 'stats':
        $conn  = conectar();
        $stats = [];
        $r = $conn->query("SELECT COUNT(*) AS t FROM usuarios"); $stats['total']     = $r->fetch_assoc()['t'];
        $r = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE estado = 1"); $stats['activos']   = $r->fetch_assoc()['t'];
        $r = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE estado = 0"); $stats['inactivos'] = $r->fetch_assoc()['t'];
        $r = $conn->query("SELECT COUNT(*) AS t FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol WHERE r.nombre = 'Administrador'");
        $stats['admins'] = $r->fetch_assoc()['t'];
        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $stats]);
        break;

    /* ── Guardar usuario (crear o editar) ── */
    case 'guardar':
        $id       = intval($_POST['id_usuario']  ?? 0);
        $nombre   = trim($_POST['nombre']        ?? '');
        $apellido = trim($_POST['apellido']      ?? '');
        $correo   = trim($_POST['correo']        ?? '');
        $telefono = trim($_POST['telefono']      ?? '') ?: null;
        $id_rol   = intval($_POST['id_rol']      ?? 0);
        $password = trim($_POST['password_hash'] ?? '');
        $estado   = isset($_POST['estado'])      ? 1 : 0;

        if (!$nombre || !$apellido || !$correo || !$id_rol) {
            echo json_encode(['ok' => false, 'mensaje' => 'Faltan campos obligatorios.']);
            break;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'mensaje' => 'Correo no válido.']);
            break;
        }

        $conn = conectar();

        // Verificar correo duplicado
        $stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?");
        $stmtCheck->bind_param('si', $correo, $id);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            $stmtCheck->close(); $conn->close();
            echo json_encode(['ok' => false, 'mensaje' => 'Ya existe un usuario con ese correo.']);
            break;
        }
        $stmtCheck->close();

        if ($id > 0) {
            // Editar
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE usuarios SET nombre=?,apellido=?,correo=?,telefono=?,id_rol=?,password_hash=?,estado=? WHERE id_usuario=?");
                $stmt->bind_param('ssssiisi', $nombre, $apellido, $correo, $telefono, $id_rol, $hash, $estado, $id);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nombre=?,apellido=?,correo=?,telefono=?,id_rol=?,estado=? WHERE id_usuario=?");
                $stmt->bind_param('ssssiis', $nombre, $apellido, $correo, $telefono, $id_rol, $estado, $id);
            }
            $stmt->execute();
            $ok = $stmt->affected_rows >= 0;
            $stmt->close();
            $conn->close();
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Usuario actualizado.' : 'Error al actualizar.']);
        } else {
            // Crear — contraseña obligatoria
            if (!$password || strlen($password) < 6) {
                $conn->close();
                echo json_encode(['ok' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
                break;
            }
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO usuarios (id_rol,nombre,apellido,correo,password_hash,telefono,estado) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param('isssssi', $id_rol, $nombre, $apellido, $correo, $hash, $telefono, $estado);
            $stmt->execute();
            $nuevoId = $conn->insert_id;
            $stmt->close();
            $conn->close();
            echo json_encode(['ok' => $nuevoId > 0, 'mensaje' => 'Usuario creado.', 'id' => $nuevoId]);
        }
        break;

    /* ── Eliminar usuario ── */
    case 'eliminar':
        $id         = intval($_POST['id_usuario']      ?? 0);
        $id_session = intval($_SESSION['usuario_id']);

        if ($id === $id_session) {
            echo json_encode(['ok' => false, 'mensaje' => 'No puedes eliminar tu propio usuario.']);
            break;
        }

        $conn = conectar();

        // Verificar si tiene ventas
        $stmt = $conn->prepare("SELECT id_venta FROM ventas WHERE id_usuario = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close(); $conn->close();
            echo json_encode(['ok' => false, 'mensaje' => 'No se puede eliminar: el usuario tiene ventas registradas.']);
            break;
        }
        $stmt->close();

        $stmt2 = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $ok = $stmt2->affected_rows > 0;
        $stmt2->close();
        $conn->close();
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Usuario eliminado.' : 'No se encontró el usuario.']);
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}