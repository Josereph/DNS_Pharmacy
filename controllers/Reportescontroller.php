<?php
/**
 * ReportesController.php — DNS Pharmacy
 * Devuelve JSON para cada acción del módulo de reportes.
 *
 * URL: /DNS_Pharmacy/controllers/ReportesController.php?accion=XXX&periodo=YYY
 * Períodos válidos: hoy | semana | mes | mes_anterior | anio | custom (requiere desde + hasta)
 */

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$accion = $_GET['accion'] ?? '';
$conn   = conectar();

/* ── Calcular rango de fechas ─────────────────────────────── */
function getRango(mysqli $conn): array {
    $periodo = $_GET['periodo'] ?? 'mes';
    switch ($periodo) {
        case 'hoy':
            $desde = date('Y-m-d');
            $hasta = date('Y-m-d');
            break;
        case 'semana':
            $desde = date('Y-m-d', strtotime('monday this week'));
            $hasta = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'mes_anterior':
            $desde = date('Y-m-01', strtotime('first day of last month'));
            $hasta = date('Y-m-t',  strtotime('last day of last month'));
            break;
        case 'anio':
            $desde = date('Y-01-01');
            $hasta = date('Y-12-31');
            break;
        case 'custom':
            $desde = $_GET['desde'] ?? date('Y-m-01');
            $hasta = $_GET['hasta'] ?? date('Y-m-d');
            break;
        case 'mes':
        default:
            $desde = date('Y-m-01');
            $hasta = date('Y-m-d');
    }
    return [$desde, $hasta];
}

/* ── Rango período anterior (para deltas KPI) ─────────────── */
function getRangoAnterior(string $desde, string $hasta): array {
    $diff = (new DateTime($desde))->diff(new DateTime($hasta))->days + 1;
    $hastaAnt = date('Y-m-d', strtotime($desde . ' -1 day'));
    $desdeAnt = date('Y-m-d', strtotime($hastaAnt . ' -' . ($diff - 1) . ' days'));
    return [$desdeAnt, $hastaAnt];
}

function calcDelta(float $actual, float $anterior): ?float {
    if ($anterior == 0) return null;
    return round(($actual - $anterior) / $anterior * 100, 1);
}

[$desde, $hasta] = getRango($conn);

/* ════════════════════════════════════════════════════════════
   ROUTER
════════════════════════════════════════════════════════════ */
switch ($accion) {

    /* ── KPIs ─────────────────────────────────────────────── */
    case 'kpis':
        [$desdeAnt, $hastaAnt] = getRangoAnterior($desde, $hasta);

        // Ventas del período actual
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total_ventas,
                   COALESCE(SUM(total), 0) AS total_ingresos,
                   COALESCE(AVG(total), 0) AS ticket_promedio,
                   COALESCE(SUM(dv.unidades), 0) AS unidades_vendidas
            FROM ventas v
            LEFT JOIN (
                SELECT id_venta, SUM(cantidad) AS unidades FROM detalle_venta GROUP BY id_venta
            ) dv ON dv.id_venta = v.id_venta
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
              AND v.estado = 'completada'
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        $actual = $stmt->get_result()->fetch_assoc();

        // Ventas período anterior
        $stmt->bind_param('ss', $desdeAnt, $hastaAnt);
        $stmt->execute();
        $anterior = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Productos bajo mínimo
        $resStock = $conn->query("
            SELECT COUNT(*) AS cnt FROM productos
            WHERE stock_actual <= stock_minimo AND estado = 1
        ");
        $stockBajo = $resStock->fetch_assoc()['cnt'];

        // Compras período
        $stmtC = $conn->prepare("
            SELECT COUNT(*) AS total_compras FROM compras
            WHERE fecha_compra BETWEEN ? AND ? AND estado = 'registrada'
        ");
        $stmtC->bind_param('ss', $desde, $hasta);
        $stmtC->execute();
        $comprasActual = $stmtC->get_result()->fetch_assoc()['total_compras'];
        $stmtC->bind_param('ss', $desdeAnt, $hastaAnt);
        $stmtC->execute();
        $comprasAnt = $stmtC->get_result()->fetch_assoc()['total_compras'];
        $stmtC->close();

        echo json_encode([
            'total_ventas'      => $actual['total_ventas'],
            'total_ingresos'    => round($actual['total_ingresos'], 2),
            'ticket_promedio'   => round($actual['ticket_promedio'], 2),
            'unidades_vendidas' => $actual['unidades_vendidas'],
            'productos_bajo_min'=> $stockBajo,
            'total_compras'     => $comprasActual,
            'delta_ventas'      => calcDelta($actual['total_ventas'],   $anterior['total_ventas']),
            'delta_ingresos'    => calcDelta($actual['total_ingresos'],  $anterior['total_ingresos']),
            'delta_ticket'      => calcDelta($actual['ticket_promedio'], $anterior['ticket_promedio']),
            'delta_unidades'    => calcDelta($actual['unidades_vendidas'],$anterior['unidades_vendidas']),
            'delta_compras'     => calcDelta($comprasActual, $comprasAnt),
        ]);
        break;

    /* ── Ventas en el tiempo ─────────────────────────────── */
    case 'ventas_tiempo':
        $stmt = $conn->prepare("
            SELECT DATE(fecha_venta) AS fecha,
                   COUNT(*) AS num_ventas,
                   ROUND(SUM(total), 2) AS total
            FROM ventas
            WHERE DATE(fecha_venta) BETWEEN ? AND ?
              AND estado = 'completada'
            GROUP BY DATE(fecha_venta)
            ORDER BY fecha
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Método de pago ──────────────────────────────────── */
    case 'metodo_pago':
        $stmt = $conn->prepare("
            SELECT metodo_pago AS metodo, COUNT(*) AS cantidad, ROUND(SUM(total),2) AS monto
            FROM ventas
            WHERE DATE(fecha_venta) BETWEEN ? AND ? AND estado = 'completada'
            GROUP BY metodo_pago
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Ventas por hora ─────────────────────────────────── */
    case 'ventas_horas':
        $stmt = $conn->prepare("
            SELECT HOUR(fecha_venta) AS hora, COUNT(*) AS cantidad, ROUND(SUM(total),2) AS monto
            FROM ventas
            WHERE DATE(fecha_venta) BETWEEN ? AND ? AND estado = 'completada'
            GROUP BY HOUR(fecha_venta)
            ORDER BY hora
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Ventas por categoría ────────────────────────────── */
    case 'ventas_categorias':
        $stmt = $conn->prepare("
            SELECT c.nombre AS categoria,
                   SUM(dv.cantidad) AS unidades,
                   ROUND(SUM(dv.subtotal), 2) AS total
            FROM detalle_venta dv
            INNER JOIN ventas v        ON v.id_venta     = dv.id_venta
            INNER JOIN productos p     ON p.id_producto  = dv.id_producto
            INNER JOIN categorias c    ON c.id_categoria = p.id_categoria
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY c.id_categoria
            ORDER BY total DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Lista ventas ────────────────────────────────────── */
    case 'lista_ventas':
        $stmt = $conn->prepare("
            SELECT v.numero_ticket,
                   DATE_FORMAT(v.fecha_venta,'%d/%m/%Y %H:%i') AS fecha_venta,
                   CONCAT(u.nombre,' ',u.apellido) AS cajero,
                   COUNT(dv.id_detalle_venta) AS num_productos,
                   v.subtotal, v.impuesto, v.total,
                   v.metodo_pago, v.estado
            FROM ventas v
            INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
            LEFT JOIN detalle_venta dv ON dv.id_venta = v.id_venta
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
            GROUP BY v.id_venta
            ORDER BY v.fecha_venta DESC
            LIMIT 200
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Estado del stock ────────────────────────────────── */
    case 'estado_stock':
        $res = $conn->query("
            SELECT
                SUM(stock_actual > stock_minimo) AS ok,
                SUM(stock_actual > 0 AND stock_actual <= stock_minimo) AS bajo,
                SUM(stock_actual = 0) AS agotado
            FROM productos WHERE estado = 1
        ");
        echo json_encode($res->fetch_assoc());
        break;

    /* ── Stock por categoría ─────────────────────────────── */
    case 'stock_categoria':
        $res = $conn->query("
            SELECT c.nombre AS categoria, SUM(p.stock_actual) AS total_stock
            FROM productos p
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE p.estado = 1
            GROUP BY c.id_categoria
            ORDER BY total_stock DESC
        ");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    /* ── Movimientos de inventario ───────────────────────── */
    case 'movimientos':
        $stmt = $conn->prepare("
            SELECT DATE(fecha_movimiento) AS fecha,
                   SUM(CASE WHEN tipo_movimiento='entrada' THEN cantidad ELSE 0 END) AS entradas,
                   SUM(CASE WHEN tipo_movimiento='salida'  THEN cantidad ELSE 0 END) AS salidas
            FROM movimientos_inventario
            WHERE DATE(fecha_movimiento) BETWEEN ? AND ?
            GROUP BY DATE(fecha_movimiento)
            ORDER BY fecha
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Inversión por categoría ─────────────────────────── */
    case 'inversion_categoria':
        $res = $conn->query("
            SELECT c.nombre AS categoria,
                   ROUND(SUM(p.precio_compra * p.stock_actual), 2) AS inversion
            FROM productos p
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE p.estado = 1
            GROUP BY c.id_categoria
            ORDER BY inversion DESC
        ");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    /* ── Stock bajo mínimo ───────────────────────────────── */
    case 'stock_bajo':
        $res = $conn->query("
            SELECT p.nombre, c.nombre AS categoria,
                   p.stock_actual, p.stock_minimo, p.precio_compra
            FROM productos p
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE p.stock_actual <= p.stock_minimo AND p.estado = 1
            ORDER BY p.stock_actual ASC
        ");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        break;

    /* ── Ventas por empleado ─────────────────────────────── */
    case 'ventas_empleado':
        $stmt = $conn->prepare("
            SELECT CONCAT(u.nombre,' ',u.apellido) AS empleado,
                   COUNT(*) AS num_ventas,
                   ROUND(SUM(v.total), 2) AS total
            FROM ventas v
            INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY v.id_usuario
            ORDER BY total DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Tickets por empleado ────────────────────────────── */
    case 'tickets_empleado':
        $stmt = $conn->prepare("
            SELECT CONCAT(u.nombre,' ',u.apellido) AS empleado,
                   COUNT(v.id_venta) AS tickets
            FROM ventas v
            INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY v.id_usuario
            ORDER BY tickets DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Turnos por empleado ─────────────────────────────── */
    case 'turnos_empleado':
        $stmt = $conn->prepare("
            SELECT CONCAT(u.nombre,' ',u.apellido) AS empleado,
                   COUNT(t.id_turno) AS turnos
            FROM turnos t
            INNER JOIN usuarios u ON u.id_usuario = t.id_usuario
            WHERE t.fecha BETWEEN ? AND ?
            GROUP BY t.id_usuario
            ORDER BY turnos DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Ticket promedio por empleado ────────────────────── */
    case 'ticket_prom_empleado':
        $stmt = $conn->prepare("
            SELECT CONCAT(u.nombre,' ',u.apellido) AS empleado,
                   ROUND(AVG(v.total), 2) AS ticket_prom
            FROM ventas v
            INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY v.id_usuario
            ORDER BY ticket_prom DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Ranking empleados ───────────────────────────────── */
    case 'ranking_empleados':
        $stmt = $conn->prepare("
            SELECT CONCAT(u.nombre,' ',u.apellido) AS empleado,
                   r.nombre AS rol,
                   COUNT(DISTINCT v.id_venta) AS num_ventas,
                   COUNT(DISTINCT v.id_venta) AS num_tickets,
                   ROUND(COALESCE(SUM(v.total),0), 2) AS total_vendido,
                   ROUND(COALESCE(AVG(v.total),0), 2) AS ticket_promedio,
                   COUNT(DISTINCT t.id_turno) AS turnos
            FROM usuarios u
            INNER JOIN roles r ON r.id_rol = u.id_rol
            LEFT JOIN ventas v  ON v.id_usuario = u.id_usuario
                AND DATE(v.fecha_venta) BETWEEN ? AND ?
                AND v.estado = 'completada'
            LEFT JOIN turnos t ON t.id_usuario = u.id_usuario AND t.fecha BETWEEN ? AND ?
            WHERE u.estado = 1
            GROUP BY u.id_usuario
            ORDER BY total_vendido DESC
        ");
        $stmt->bind_param('ssss', $desde, $hasta, $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Compras por proveedor ───────────────────────────── */
    case 'compras_proveedor':
        $stmt = $conn->prepare("
            SELECT pr.nombre AS proveedor, COUNT(*) AS num_compras, ROUND(SUM(c.total),2) AS total
            FROM compras c
            INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
            WHERE c.fecha_compra BETWEEN ? AND ? AND c.estado = 'registrada'
            GROUP BY c.id_proveedor
            ORDER BY total DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Participación proveedor ─────────────────────────── */
    case 'participacion_proveedor':
        $stmt = $conn->prepare("
            SELECT pr.nombre AS proveedor, ROUND(SUM(c.total),2) AS total
            FROM compras c INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
            WHERE c.fecha_compra BETWEEN ? AND ? AND c.estado = 'registrada'
            GROUP BY c.id_proveedor ORDER BY total DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Compras en el tiempo ────────────────────────────── */
    case 'compras_tiempo':
        $stmt = $conn->prepare("
            SELECT fecha_compra AS fecha, ROUND(SUM(total),2) AS total
            FROM compras
            WHERE fecha_compra BETWEEN ? AND ? AND estado = 'registrada'
            GROUP BY fecha_compra ORDER BY fecha
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Top productos reabastecidos ─────────────────────── */
    case 'top_reabastecidos':
        $stmt = $conn->prepare("
            SELECT p.nombre AS producto, SUM(dc.cantidad) AS cantidad
            FROM detalle_compra dc
            INNER JOIN compras c  ON c.id_compra   = dc.id_compra
            INNER JOIN productos p ON p.id_producto = dc.id_producto
            WHERE c.fecha_compra BETWEEN ? AND ? AND c.estado = 'registrada'
            GROUP BY dc.id_producto
            ORDER BY cantidad DESC LIMIT 10
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Lista compras ───────────────────────────────────── */
    case 'lista_compras':
        $stmt = $conn->prepare("
            SELECT c.numero_factura,
                   pr.nombre AS proveedor,
                   DATE_FORMAT(c.fecha_compra,'%d/%m/%Y') AS fecha_compra,
                   COUNT(dc.id_detalle_compra) AS num_productos,
                   c.subtotal, c.impuesto, c.total, c.estado
            FROM compras c
            INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
            LEFT JOIN detalle_compra dc ON dc.id_compra = c.id_compra
            WHERE c.fecha_compra BETWEEN ? AND ?
            GROUP BY c.id_compra
            ORDER BY c.fecha_compra DESC LIMIT 200
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Top 10 más vendidos ─────────────────────────────── */
    case 'top_vendidos':
        $stmt = $conn->prepare("
            SELECT p.nombre AS producto, SUM(dv.cantidad) AS unidades,
                   ROUND(SUM(dv.subtotal), 2) AS ingresos
            FROM detalle_venta dv
            INNER JOIN ventas v   ON v.id_venta    = dv.id_venta
            INNER JOIN productos p ON p.id_producto = dv.id_producto
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY dv.id_producto
            ORDER BY unidades DESC LIMIT 10
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Margen por categoría ────────────────────────────── */
    case 'margen_categoria':
        $stmt = $conn->prepare("
            SELECT c.nombre AS categoria,
                   ROUND(SUM(dv.subtotal), 2) AS ingresos,
                   ROUND(SUM(p.precio_compra * dv.cantidad), 2) AS costos,
                   ROUND((SUM(dv.subtotal) - SUM(p.precio_compra * dv.cantidad))
                         / NULLIF(SUM(dv.subtotal),0) * 100, 1) AS margen_pct
            FROM detalle_venta dv
            INNER JOIN ventas v    ON v.id_venta     = dv.id_venta
            INNER JOIN productos p  ON p.id_producto  = dv.id_producto
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY c.id_categoria ORDER BY margen_pct DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Productos sin movimiento ────────────────────────── */
    case 'sin_movimiento':
        $stmt = $conn->prepare("
            SELECT
                COUNT(DISTINCT p.id_producto) AS total_productos,
                COUNT(DISTINCT dv.id_producto) AS con_ventas
            FROM productos p
            LEFT JOIN detalle_venta dv ON dv.id_producto = p.id_producto
            LEFT JOIN ventas v ON v.id_venta = dv.id_venta
                AND DATE(v.fecha_venta) BETWEEN ? AND ?
                AND v.estado = 'completada'
            WHERE p.estado = 1
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        echo json_encode([
            'con_ventas'  => (int)$r['con_ventas'],
            'sin_ventas'  => (int)$r['total_productos'] - (int)$r['con_ventas'],
        ]);
        $stmt->close();
        break;

    /* ── Rentabilidad por categoría ──────────────────────── */
    case 'rentabilidad_categoria':
        $stmt = $conn->prepare("
            SELECT c.nombre AS categoria,
                   ROUND(SUM(dv.subtotal), 2) AS ingresos,
                   ROUND(SUM(p.precio_compra * dv.cantidad), 2) AS costos
            FROM detalle_venta dv
            INNER JOIN ventas v    ON v.id_venta     = dv.id_venta
            INNER JOIN productos p  ON p.id_producto  = dv.id_producto
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY c.id_categoria ORDER BY ingresos DESC
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    /* ── Ranking productos ───────────────────────────────── */
    case 'ranking_productos':
        $stmt = $conn->prepare("
            SELECT p.nombre AS producto,
                   c.nombre AS categoria,
                   SUM(dv.cantidad) AS unidades_vendidas,
                   ROUND(SUM(dv.subtotal), 2) AS ingresos,
                   ROUND(SUM(p.precio_compra * dv.cantidad), 2) AS costo_total,
                   ROUND((SUM(dv.subtotal) - SUM(p.precio_compra * dv.cantidad))
                         / NULLIF(SUM(dv.subtotal),0) * 100, 1) AS margen_pct
            FROM detalle_venta dv
            INNER JOIN ventas v    ON v.id_venta     = dv.id_venta
            INNER JOIN productos p  ON p.id_producto  = dv.id_producto
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
            GROUP BY dv.id_producto
            ORDER BY unidades_vendidas DESC
            LIMIT 100
        ");
        $stmt->bind_param('ss', $desde, $hasta);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida: ' . htmlspecialchars($accion)]);
}

$conn->close();