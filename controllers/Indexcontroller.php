<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => true, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'stats':
        $conn  = conectar();
        $hoy   = date('Y-m-d');
        $stats = [];

        $r = $conn->query("SELECT COUNT(*) AS t, COALESCE(SUM(total),0) AS s FROM ventas WHERE DATE(fecha_venta) = '$hoy' AND estado = 'completada'");
        $row = $r->fetch_assoc();
        $stats['ventas_hoy'] = $row['t'];
        $stats['total_hoy']  = $row['s'];

        $r = $conn->query("SELECT COUNT(*) AS t FROM productos WHERE estado = 1");
        $stats['productos_activos'] = $r->fetch_assoc()['t'];

        $r = $conn->query("SELECT COUNT(*) AS t FROM productos");
        $stats['total_productos'] = $r->fetch_assoc()['t'];

        $r = $conn->query("SELECT COUNT(*) AS t FROM productos WHERE stock_actual <= stock_minimo AND estado = 1");
        $stats['stock_bajo'] = $r->fetch_assoc()['t'];

        $r = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE estado = 1");
        $stats['usuarios_activos'] = $r->fetch_assoc()['t'];

        $conn->close();
        echo json_encode(['error' => false, 'data' => $stats]);
        break;

    case 'ventas_recientes':
        $conn = conectar();
        $hoy  = date('Y-m-d');
        $r    = $conn->query("
            SELECT v.numero_ticket, v.total, v.metodo_pago, v.fecha_venta,
                   CONCAT(u.nombre, ' ', u.apellido) AS empleado
            FROM ventas v
            INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE DATE(v.fecha_venta) = '$hoy'
            ORDER BY v.fecha_venta DESC
            LIMIT 8
        ");
        echo json_encode(['error' => false, 'data' => $r->fetch_all(MYSQLI_ASSOC)]);
        $conn->close();
        break;

    case 'stock_bajo':
        $conn = conectar();
        $r    = $conn->query("
            SELECT p.nombre, p.stock_actual, p.stock_minimo,
                   c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.stock_actual <= p.stock_minimo AND p.estado = 1
            ORDER BY p.stock_actual ASC
            LIMIT 6
        ");
        echo json_encode(['error' => false, 'data' => $r->fetch_all(MYSQLI_ASSOC)]);
        $conn->close();
        break;

    case 'compras_recientes':
        $conn = conectar();
        $r    = $conn->query("
            SELECT c.numero_documento, c.total, c.estado, c.fecha_compra,
                   p.nombre AS proveedor
            FROM compras c
            INNER JOIN proveedores p ON c.id_proveedor = p.id_proveedor
            ORDER BY c.created_at DESC
            LIMIT 6
        ");
        echo json_encode(['error' => false, 'data' => $r->fetch_all(MYSQLI_ASSOC)]);
        $conn->close();
        break;

    default:
        echo json_encode(['error' => true, 'mensaje' => 'Acción no reconocida.']);
        break;
}