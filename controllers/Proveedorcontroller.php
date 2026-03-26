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

    /* ── Listar proveedores ── */
    case 'listar':
        $conn = conectar();
        $r    = $conn->query("SELECT * FROM proveedores ORDER BY nombre ASC");
        $data = $r->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $data]);
        break;

    /* ── Guardar (crear o editar) ── */
    case 'guardar':
        $id             = intval(trim($_POST['id_proveedor']    ?? 0));
        $nombre         = trim($_POST['nombre']                 ?? '');
        $nombre_contacto = trim($_POST['nombre_contacto']       ?? '');
        $telefono       = trim($_POST['telefono']               ?? '');
        $correo         = trim($_POST['correo']                 ?? '');
        $direccion      = trim($_POST['direccion']              ?? '') ?: null;
        $nit            = trim($_POST['nit']                    ?? '') ?: null;
        $nrc            = trim($_POST['nrc']                    ?? '') ?: null;
        $estado         = intval($_POST['estado']               ?? 1);

        // Validaciones
        if (!$nombre || !$nombre_contacto || !$telefono || !$correo) {
            echo json_encode(['ok' => false, 'mensaje' => 'Faltan campos obligatorios.']);
            break;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'mensaje' => 'El correo no es válido.']);
            break;
        }

        $conn = conectar();

        // Verificar NIT duplicado si se proporcionó
        if ($nit) {
            $stmtCheck = $conn->prepare("SELECT id_proveedor FROM proveedores WHERE nit = ? AND id_proveedor != ?");
            $stmtCheck->bind_param('si', $nit, $id);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->num_rows > 0) {
                $stmtCheck->close(); $conn->close();
                echo json_encode(['ok' => false, 'mensaje' => 'Ya existe un proveedor con ese NIT.']);
                break;
            }
            $stmtCheck->close();
        }

        if ($id > 0) {
            // Editar
            $stmt = $conn->prepare("
                UPDATE proveedores
                SET nombre=?, nombre_contacto=?, telefono=?, correo=?,
                    direccion=?, nit=?, nrc=?, estado=?
                WHERE id_proveedor=?
            ");
            $stmt->bind_param('sssssssii',
                $nombre, $nombre_contacto, $telefono, $correo,
                $direccion, $nit, $nrc, $estado, $id
            );
            $stmt->execute();
            $ok = $stmt->affected_rows >= 0;
            $stmt->close();
            $conn->close();
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Proveedor actualizado.' : 'Error al actualizar.']);
        } else {
            // Crear
            $stmt = $conn->prepare("
                INSERT INTO proveedores
                    (nombre, nombre_contacto, telefono, correo, direccion, nit, nrc, estado)
                VALUES (?,?,?,?,?,?,?,?)
            ");
            $stmt->bind_param('sssssssi',
                $nombre, $nombre_contacto, $telefono, $correo,
                $direccion, $nit, $nrc, $estado
            );
            $stmt->execute();
            $nuevoId = $conn->insert_id;
            $stmt->close();
            $conn->close();
            echo json_encode(['ok' => $nuevoId > 0, 'mensaje' => 'Proveedor registrado.', 'id' => $nuevoId]);
        }
        break;

    /* ── Eliminar ── */
    case 'eliminar':
        $id   = intval($_POST['id_proveedor'] ?? 0);
        $conn = conectar();

        // Verificar si tiene compras asociadas
        $stmt = $conn->prepare("SELECT id_compra FROM compras WHERE id_proveedor = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close(); $conn->close();
            echo json_encode(['ok' => false, 'mensaje' => 'No se puede eliminar: tiene compras registradas.']);
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