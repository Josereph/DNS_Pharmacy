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

    /* ── Generar número de factura único ── */
    case 'generar_factura':
        $conn = conectar();
        do {
            $numero = 'FAC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $stmt   = $conn->prepare("SELECT id_compra FROM compras WHERE numero_factura = ? LIMIT 1");
            $stmt->bind_param('s', $numero);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        } while ($existe);
        $conn->close();
        echo json_encode(['ok' => true, 'numero' => $numero]);
        break;

    /* ── Stats ── */
    case 'stats':
        $conn  = conectar();
        $stats = [];

        $r = $conn->query("SELECT COUNT(*) AS total FROM compras WHERE estado = 'registrada'");
        $stats['total_compras'] = $r->fetch_assoc()['total'];

        $r = $conn->query("SELECT COUNT(*) AS total FROM productos WHERE estado = 1");
        $stats['total_productos'] = $r->fetch_assoc()['total'];

        $r = $conn->query("SELECT COUNT(*) AS total FROM productos WHERE stock_actual <= stock_minimo AND estado = 1");
        $stats['stock_bajo'] = $r->fetch_assoc()['total'];

        $r = $conn->query("SELECT COALESCE(SUM(total),0) AS inv FROM compras WHERE estado = 'registrada'");
        $stats['inversion'] = $r->fetch_assoc()['inv'];

        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $stats]);
        break;

    /* ── Listar proveedores ── */
    case 'listar_proveedores':
        $conn = conectar();
        $r    = $conn->query("SELECT id_proveedor, nombre FROM proveedores WHERE estado = 1 ORDER BY nombre ASC");
        $data = $r->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $data]);
        break;

    /* ── Listar compras ── */
    case 'listar':
        $conn = conectar();
        $r    = $conn->query("
            SELECT c.*,
                   p.nombre AS nombre_proveedor,
                   (SELECT COUNT(*) FROM detalle_compra dc WHERE dc.id_compra = c.id_compra) AS num_productos
            FROM compras c
            LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
            ORDER BY c.created_at DESC
        ");
        $data = $r->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $data]);
        break;

    /* ── Detalle compra ── */
    case 'detalle':
        $id   = intval($_GET['id'] ?? 0);
        $conn = conectar();

        $stmt = $conn->prepare("
            SELECT c.*,
                   p.nombre AS nombre_proveedor,
                   CONCAT(u.nombre,' ',u.apellido) AS nombre_usuario
            FROM compras c
            LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
            LEFT JOIN usuarios u    ON c.id_usuario   = u.id_usuario
            WHERE c.id_compra = ? LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $compra = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$compra) {
            echo json_encode(['ok' => false, 'mensaje' => 'Compra no encontrada.']);
            $conn->close(); break;
        }

        $stmt2 = $conn->prepare("
            SELECT dc.*, pr.nombre AS nombre_producto
            FROM detalle_compra dc
            LEFT JOIN productos pr ON dc.id_producto = pr.id_producto
            WHERE dc.id_compra = ?
        ");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $detalle = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
        $conn->close();

        echo json_encode(['ok' => true, 'datos' => ['compra' => $compra, 'detalle' => $detalle]]);
        break;

    /* ── Guardar compra ── */
    case 'guardar_compra':
        $id_usuario     = intval($_SESSION['usuario_id']);
        $id_proveedor   = intval($_POST['id_proveedor']   ?? 0);
        $numero_factura = trim($_POST['numero_factura']   ?? '');
        $fecha_compra   = trim($_POST['fecha_compra']     ?? '');
        $observaciones  = trim($_POST['observaciones']    ?? '');
        $subtotal       = floatval($_POST['subtotal']     ?? 0);
        $impuesto       = floatval($_POST['impuesto']     ?? 0);
        $total          = floatval($_POST['total']        ?? 0);
        $items          = json_decode($_POST['items']     ?? '[]', true);

        if (!$id_proveedor || !$numero_factura || !$fecha_compra || empty($items)) {
            echo json_encode(['ok' => false, 'mensaje' => 'Faltan datos obligatorios.']);
            break;
        }

        $conn = conectar();
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                INSERT INTO compras
                    (id_proveedor, id_usuario, numero_factura, fecha_compra,
                     subtotal, impuesto, total, observaciones, estado)
                VALUES (?,?,?,?,?,?,?,?,'registrada')
            ");
            $stmt->bind_param('iissddds',
                $id_proveedor, $id_usuario, $numero_factura, $fecha_compra,
                $subtotal, $impuesto, $total, $observaciones
            );
            $stmt->execute();
            $id_compra = $conn->insert_id;
            $stmt->close();

            foreach ($items as $item) {
                $id_producto  = intval($item['id_producto']);
                $cantidad     = intval($item['cantidad']);
                $costo_unit   = floatval($item['costo_unitario']);
                $subtotal_det = $cantidad * $costo_unit;
                $numero_lote  = !empty($item['numero_lote'])      ? $item['numero_lote']      : null;
                $fecha_venc   = !empty($item['fecha_vencimiento']) ? $item['fecha_vencimiento'] : null;

                $stmtDet = $conn->prepare("
                    INSERT INTO detalle_compra
                        (id_compra, id_producto, cantidad, costo_unitario, subtotal, numero_lote, fecha_vencimiento)
                    VALUES (?,?,?,?,?,?,?)
                ");
                $stmtDet->bind_param('iiiddss',
                    $id_compra, $id_producto, $cantidad,
                    $costo_unit, $subtotal_det, $numero_lote, $fecha_venc
                );
                $stmtDet->execute();
                $stmtDet->close();

                $stmtStock = $conn->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id_producto = ?");
                $stmtStock->bind_param('ii', $cantidad, $id_producto);
                $stmtStock->execute();
                $stmtStock->close();
            }

            $conn->commit();
            $conn->close();
            echo json_encode(['ok' => true, 'mensaje' => 'Compra registrada correctamente.', 'id' => $id_compra]);

        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            echo json_encode(['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
        break;

    /* ── Anular compra ── */
    case 'anular_compra':
        $id   = intval($_POST['id_compra'] ?? 0);
        $conn = conectar();
        $stmt = $conn->prepare("UPDATE compras SET estado = 'anulada' WHERE id_compra = ? AND estado = 'registrada'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();
        $conn->close();
        echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Compra anulada.' : 'No se pudo anular.']);
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}