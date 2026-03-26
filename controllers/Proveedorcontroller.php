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

   
    case 'listar':
        $conn = conectar();
        $r    = $conn->query("
            SELECT id_proveedor, nombre, nombre_contacto,
                   telefono, correo, direccion, nit,
                   estado, created_at, updated_at
            FROM proveedores
            ORDER BY nombre ASC
        ");
        $data = $r->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $data]);
        break;

   
    case 'guardar':
        $id              = intval($_POST['id_proveedor']    ?? 0);
        $nombre          = trim($_POST['nombre']            ?? '');
        $nombre_contacto = trim($_POST['nombre_contacto']   ?? '') ?: null;
        $telefono        = trim($_POST['telefono']          ?? '') ?: null;
        $correo          = trim($_POST['correo']            ?? '') ?: null;
        $direccion       = trim($_POST['direccion']         ?? '') ?: null;
        $nit             = trim($_POST['nit']               ?? '') ?: null;
        $estado          = intval($_POST['estado']          ?? 1);

        if (!$nombre) {
            echo json_encode(['ok' => false, 'mensaje' => 'El nombre es obligatorio.']);
            break;
        }

        if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'mensaje' => 'El correo no tiene un formato válido.']);
            break;
        }

        $conn = conectar();

        
        if ($nit) {
            $stmtNit = $conn->prepare("SELECT id_proveedor FROM proveedores WHERE nit = ? AND id_proveedor != ?");
            $stmtNit->bind_param('si', $nit, $id);
            $stmtNit->execute();
            if ($stmtNit->get_result()->num_rows > 0) {
                $stmtNit->close(); $conn->close();
                echo json_encode(['ok' => false, 'mensaje' => 'Ya existe un proveedor con ese NIT.']);
                break;
            }
            $stmtNit->close();
        }

        if ($id > 0) {
            $stmt = $conn->prepare("
                UPDATE proveedores
                SET nombre=?, nombre_contacto=?, telefono=?, correo=?,
                    direccion=?, nit=?, estado=?
                WHERE id_proveedor=?
            ");
            $stmt->bind_param('ssssssii', $nombre, $nombre_contacto, $telefono, $correo, $direccion, $nit, $estado, $id);
            $stmt->execute();
            $ok = $stmt->affected_rows >= 0;
            $stmt->close();
            $conn->close();
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Proveedor actualizado.' : 'Error al actualizar.']);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO proveedores (nombre, nombre_contacto, telefono, correo, direccion, nit, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssssssi', $nombre, $nombre_contacto, $telefono, $correo, $direccion, $nit, $estado);
            $stmt->execute();
            $nuevoId = $conn->insert_id;
            $stmt->close();
            $conn->close();
            echo json_encode(['ok' => $nuevoId > 0, 'mensaje' => $nuevoId > 0 ? 'Proveedor creado.' : 'Error al crear.', 'id' => $nuevoId]);
        }
        break;

  
    case 'eliminar':
        $id = intval($_POST['id_proveedor'] ?? 0);

        if (!$id) {
            echo json_encode(['ok' => false, 'mensaje' => 'ID inválido.']);
            break;
        }

        $conn = conectar();

      
        $stmt = $conn->prepare("SELECT id_compra FROM compras WHERE id_proveedor = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close(); $conn->close();
            echo json_encode(['ok' => false, 'mensaje' => 'No se puede eliminar: el proveedor tiene compras registradas.']);
            break;
        }
        $stmt->close();

        $stmt2 = $conn->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $ok = $stmt2->affected_rows > 0;
        $stmt2->close();
        $conn->close();
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Proveedor eliminado.' : 'No se encontró el proveedor.']);
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}