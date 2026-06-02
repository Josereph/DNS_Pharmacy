<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    /* ── Listar ventas con filtro de fechas ── */
    case 'listar':
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        $conn = conectar();

        $sql = "
            SELECT v.*,
                   CONCAT(u.nombre,' ',u.apellido) AS nombre_empleado
            FROM ventas v
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.estado != 'pendiente'
        ";

        $params = [];
        $types  = '';

        if ($desde) { $sql .= " AND DATE(v.fecha_venta) >= ?"; $params[] = $desde; $types .= 's'; }
        if ($hasta) { $sql .= " AND DATE(v.fecha_venta) <= ?"; $params[] = $hasta; $types .= 's'; }

        $sql .= " ORDER BY v.fecha_venta DESC";

        $stmt = $conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();

        echo json_encode(['ok' => true, 'datos' => $ventas]);
        break;

    /* ── Detalle de una venta ── */
    case 'detalle':
        $id   = intval($_GET['id'] ?? 0);
        $conn = conectar();

        $stmt = $conn->prepare("
            SELECT v.*,
                   CONCAT(u.nombre,' ',u.apellido) AS nombre_empleado
            FROM ventas v
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.id_venta = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$venta) {
            echo json_encode(['ok' => false, 'mensaje' => 'Venta no encontrada.']);
            $conn->close(); break;
        }

        $stmt2 = $conn->prepare("
            SELECT dv.*, p.nombre AS nombre_producto
            FROM detalle_venta dv
            LEFT JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?
        ");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $detalle = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
        $conn->close();

        echo json_encode(['ok' => true, 'datos' => ['venta' => $venta, 'detalle' => $detalle]]);
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}