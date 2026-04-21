




<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    die('mPDF no instalado. Ejecuta: composer require mpdf/mpdf');
}

require_once $autoload;
require_once __DIR__ . '/../config/database.php';

use Mpdf\Mpdf;

$tipo = $_GET['tipo'] ?? 'ventas';
$periodo = $_GET['periodo'] ?? 'mes';

function getRango(): array {
    $periodo = $_GET['periodo'] ?? 'mes';

    switch ($periodo) {
        case 'hoy':
            return [date('Y-m-d'), date('Y-m-d')];
        case 'semana':
            return [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))];
        case 'mes_anterior':
            return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))];
        case 'anio':
            return [date('Y-01-01'), date('Y-12-31')];
        case 'custom':
            $desde = $_GET['desde'] ?? date('Y-m-01');
            $hasta = $_GET['hasta'] ?? date('Y-m-d');
            return [$desde, $hasta];
        case 'mes':
        default:
            return [date('Y-m-01'), date('Y-m-d')];
    }
}

function getLabelPeriodo(string $desde, string $hasta): string {
    $periodo = $_GET['periodo'] ?? 'mes';

    return match($periodo) {
        'hoy' => 'Hoy — ' . date('d/m/Y'),
        'semana' => 'Esta semana',
        'mes' => 'Este mes — ' . date('F Y'),
        'mes_anterior' => 'Mes anterior',
        'anio' => 'Este año — ' . date('Y'),
        'custom' => date('d/m/Y', strtotime($desde)) . ' al ' . date('d/m/Y', strtotime($hasta)),
        default => 'Período seleccionado'
    };
}

function fmtMoney($v): string {
    return '$' . number_format((float)$v, 2);
}

function badge(string $estado): string {
    $e = strtolower($estado);
    $class = 'badge-secondary';

    if ($e === 'completada' || $e === 'ok') $class = 'badge-ok';
    elseif ($e === 'registrada') $class = 'badge-info';
    elseif ($e === 'anulada' || $e === 'agotado') $class = 'badge-danger';
    elseif ($e === 'pendiente' || $e === 'bajo') $class = 'badge-warning';

    return "<span class='badge {$class}'>" . htmlspecialchars(ucfirst($estado)) . "</span>";
}

[$desde, $hasta] = getRango();
$labelPeriodo = getLabelPeriodo($desde, $hasta);
$conn = conectar();

$css = '
body{font-family: DejaVuSansCondensed, sans-serif; font-size:9pt; color:#334155;}
h1{font-size:18pt; margin:0 0 4px; color:#0f172a;}
h2{font-size:12pt; margin:18px 0 8px; color:#0f172a; border-bottom:2px solid #841480; padding-bottom:5px;}
p{font-size:8.5pt; margin:0 0 4px; color:#64748b;}
.small{font-size:7.5pt; color:#94a3b8;}
.kpi-box{padding:12px; border-radius:8px;}
.kpi-wrapper{width:100%; border-collapse:collapse; margin-bottom:15px;}
.kpi-wrapper td{vertical-align:top;}
.kpi-title{font-size:7.5pt; color:#64748b;}
.kpi-value{font-size:17pt; font-weight:bold; color:#0f172a;}
.bg-purple{background:#f3e8f3; border-left:4px solid #841480;}
.bg-green{background:#eef6e8; border-left:4px solid #70ab32;}
.bg-orange{background:#fff1e6; border-left:4px solid #f57c00;}
.bg-red{background:#fef2f2; border-left:4px solid #dc2626;}
.data-table{width:100%; border-collapse:collapse; margin-top:10px; font-size:8pt;}
.data-table th{background:#f8fafc; color:#64748b; text-transform:uppercase; font-size:7pt; padding:7px 9px; border-bottom:1px solid #e2e8f0; text-align:left;}
.data-table td{padding:7px 9px; border-bottom:1px solid #f1f5f9;}
.data-table tbody tr:nth-child(even) td{background:#fcfcfd;}
.badge{display:inline-block; padding:2px 7px; border-radius:10px; font-size:7pt; font-weight:bold;}
.badge-ok{background:#eef6e8; color:#3b6a12;}
.badge-warning{background:#fff1e6; color:#a14b00;}
.badge-danger{background:#fee2e2; color:#991b1b;}
.badge-info{background:#f3e8f3; color:#5b0d58;}
.badge-secondary{background:#e2e8f0; color:#334155;}
.empty{padding:16px; text-align:center; color:#94a3b8; font-style:italic;}
';

function getResumenVentas(mysqli $conn, string $desde, string $hasta): array {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total_ventas,
               COALESCE(SUM(total),0) AS total_ingresos,
               COALESCE(AVG(total),0) AS ticket_promedio
        FROM ventas
        WHERE DATE(fecha_venta) BETWEEN ? AND ?
          AND estado='completada'
    ");
    $stmt->bind_param('ss', $desde, $hasta);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $data;
}

function buildHeaderHtml(string $titulo, string $labelPeriodo): string {
    return "
        <table style='width:100%; margin-bottom:6px;'>
            <tr>
                <td>
                    <h1>DNS Pharmacy</h1>
                    <p>{$titulo}</p>
                    <p><strong>Período:</strong> {$labelPeriodo}</p>
                    <span class='small'>Generado el " . date('d/m/Y H:i') . "</span>
                </td>
                <td style='text-align:right;'>
                    <span class='small'>Drug Network Supply</span>
                </td>
            </tr>
        </table>
        <hr style='border:none;border-top:1px solid #e2e8f0; margin:12px 0;'>
    ";
}

function generarReporteVentas(mysqli $conn, string $desde, string $hasta, string $labelPeriodo): string {
    $resumen = getResumenVentas($conn, $desde, $hasta);

    $stmt = $conn->prepare("
        SELECT v.numero_ticket,
               DATE_FORMAT(v.fecha_venta,'%d/%m/%Y %H:%i') AS fecha_venta,
               CONCAT(u.nombre,' ',u.apellido) AS cajero,
               COUNT(dv.id_detalle_venta) AS num_productos,
               v.subtotal, v.impuesto, v.total, v.metodo_pago, v.estado
        FROM ventas v
        INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
        LEFT JOIN detalle_venta dv ON dv.id_venta = v.id_venta
        WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
        GROUP BY v.id_venta
        ORDER BY v.fecha_venta DESC
    ");
    $stmt->bind_param('ss', $desde, $hasta);
    $stmt->execute();
    $ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte de ventas', $labelPeriodo) ?>

    <table class="kpi-wrapper">
        <tr>
            <td width="32%" class="kpi-box bg-purple">
                <div class="kpi-title">Ventas realizadas</div>
                <div class="kpi-value"><?= (int)$resumen['total_ventas'] ?></div>
            </td>
            <td width="2%"></td>
            <td width="32%" class="kpi-box bg-green">
                <div class="kpi-title">Ingresos totales</div>
                <div class="kpi-value"><?= fmtMoney($resumen['total_ingresos']) ?></div>
            </td>
            <td width="2%"></td>
            <td width="32%" class="kpi-box bg-orange">
                <div class="kpi-title">Ticket promedio</div>
                <div class="kpi-value"><?= fmtMoney($resumen['ticket_promedio']) ?></div>
            </td>
        </tr>
    </table>

    <h2>Detalle de ventas</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Ticket</th>
                <th>Fecha</th>
                <th>Cajero</th>
                <th>Productos</th>
                <th>Subtotal</th>
                <th>IVA</th>
                <th>Total</th>
                <th>Método</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($ventas)): ?>
            <tr><td colspan="10" class="empty">Sin datos para el período seleccionado.</td></tr>
        <?php else: foreach ($ventas as $i => $v): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($v['numero_ticket']) ?></td>
                <td><?= $v['fecha_venta'] ?></td>
                <td><?= htmlspecialchars($v['cajero']) ?></td>
                <td><?= $v['num_productos'] ?></td>
                <td><?= fmtMoney($v['subtotal']) ?></td>
                <td><?= fmtMoney($v['impuesto']) ?></td>
                <td><strong><?= fmtMoney($v['total']) ?></strong></td>
                <td><?= htmlspecialchars(ucfirst($v['metodo_pago'])) ?></td>
                <td><?= badge($v['estado']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generarReporteCompras(mysqli $conn, string $desde, string $hasta, string $labelPeriodo): string {
    $stmtResumen = $conn->prepare("
        SELECT COUNT(*) AS total_compras,
               COALESCE(SUM(total),0) AS monto_total
        FROM compras
        WHERE fecha_compra BETWEEN ? AND ?
          AND estado='registrada'
    ");
    $stmtResumen->bind_param('ss', $desde, $hasta);
    $stmtResumen->execute();
    $resumen = $stmtResumen->get_result()->fetch_assoc();
    $stmtResumen->close();

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
        ORDER BY c.fecha_compra DESC
    ");
    $stmt->bind_param('ss', $desde, $hasta);
    $stmt->execute();
    $compras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte de compras', $labelPeriodo) ?>

    <table class="kpi-wrapper">
        <tr>
            <td width="49%" class="kpi-box bg-purple">
                <div class="kpi-title">Compras registradas</div>
                <div class="kpi-value"><?= (int)$resumen['total_compras'] ?></div>
            </td>
            <td width="2%"></td>
            <td width="49%" class="kpi-box bg-green">
                <div class="kpi-title">Monto total comprado</div>
                <div class="kpi-value"><?= fmtMoney($resumen['monto_total']) ?></div>
            </td>
        </tr>
    </table>

    <h2>Historial de compras</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Factura</th>
                <th>Proveedor</th>
                <th>Fecha</th>
                <th>Productos</th>
                <th>Subtotal</th>
                <th>IVA</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($compras)): ?>
            <tr><td colspan="9" class="empty">Sin compras para el período seleccionado.</td></tr>
        <?php else: foreach ($compras as $i => $c): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($c['numero_factura']) ?></td>
                <td><?= htmlspecialchars($c['proveedor']) ?></td>
                <td><?= $c['fecha_compra'] ?></td>
                <td><?= $c['num_productos'] ?></td>
                <td><?= fmtMoney($c['subtotal']) ?></td>
                <td><?= fmtMoney($c['impuesto']) ?></td>
                <td><strong><?= fmtMoney($c['total']) ?></strong></td>
                <td><?= badge($c['estado']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generarReporteInventario(mysqli $conn, string $desde, string $hasta, string $labelPeriodo): string {
    $res = $conn->query("
        SELECT
            SUM(stock_actual > stock_minimo) AS ok,
            SUM(stock_actual > 0 AND stock_actual <= stock_minimo) AS bajo,
            SUM(stock_actual = 0) AS agotado
        FROM productos
        WHERE estado = 1
    ");
    $resumen = $res->fetch_assoc();

    $resProd = $conn->query("
        SELECT p.nombre, c.nombre AS categoria, p.stock_actual, p.stock_minimo, p.precio_compra
        FROM productos p
        INNER JOIN categorias c ON c.id_categoria = p.id_categoria
        WHERE p.stock_actual <= p.stock_minimo AND p.estado = 1
        ORDER BY p.stock_actual ASC
    ");
    $productos = $resProd->fetch_all(MYSQLI_ASSOC);

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte de inventario', $labelPeriodo) ?>

    <table class="kpi-wrapper">
        <tr>
            <td width="32%" class="kpi-box bg-green">
                <div class="kpi-title">Stock suficiente</div>
                <div class="kpi-value"><?= (int)$resumen['ok'] ?></div>
            </td>
            <td width="2%"></td>
            <td width="32%" class="kpi-box bg-orange">
                <div class="kpi-title">Stock bajo</div>
                <div class="kpi-value"><?= (int)$resumen['bajo'] ?></div>
            </td>
            <td width="2%"></td>
            <td width="32%" class="kpi-box bg-red">
                <div class="kpi-title">Agotados</div>
                <div class="kpi-value"><?= (int)$resumen['agotado'] ?></div>
            </td>
        </tr>
    </table>

    <h2>Productos bajo mínimo o agotados</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Stock actual</th>
                <th>Stock mínimo</th>
                <th>Precio compra</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($productos)): ?>
            <tr><td colspan="7" class="empty">No hay productos críticos.</td></tr>
        <?php else: foreach ($productos as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['categoria']) ?></td>
                <td><?= $p['stock_actual'] ?></td>
                <td><?= $p['stock_minimo'] ?></td>
                <td><?= fmtMoney($p['precio_compra']) ?></td>
                <td><?= ((int)$p['stock_actual'] === 0) ? badge('agotado') : badge('bajo') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generarReporteEmpleados(mysqli $conn, string $desde, string $hasta, string $labelPeriodo): string {
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
        LEFT JOIN ventas v ON v.id_usuario = u.id_usuario
            AND DATE(v.fecha_venta) BETWEEN ? AND ?
            AND v.estado = 'completada'
        LEFT JOIN turnos t ON t.id_usuario = u.id_usuario
            AND t.fecha BETWEEN ? AND ?
        WHERE u.estado = 1
        GROUP BY u.id_usuario
        ORDER BY total_vendido DESC
    ");
    $stmt->bind_param('ssss', $desde, $hasta, $desde, $hasta);
    $stmt->execute();
    $empleados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte de empleados', $labelPeriodo) ?>

    <h2>Ranking de empleados</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Pos.</th>
                <th>Empleado</th>
                <th>Rol</th>
                <th>Ventas</th>
                <th>Tickets</th>
                <th>Total vendido</th>
                <th>Ticket promedio</th>
                <th>Turnos</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($empleados)): ?>
            <tr><td colspan="8" class="empty">Sin datos para el período seleccionado.</td></tr>
        <?php else: foreach ($empleados as $i => $e): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($e['empleado']) ?></td>
                <td><?= htmlspecialchars($e['rol']) ?></td>
                <td><?= $e['num_ventas'] ?></td>
                <td><?= $e['num_tickets'] ?></td>
                <td><?= fmtMoney($e['total_vendido']) ?></td>
                <td><?= fmtMoney($e['ticket_promedio']) ?></td>
                <td><?= $e['turnos'] ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generarReporteProveedores(mysqli $conn, string $desde, string $hasta, string $labelPeriodo): string {
    $stmt = $conn->prepare("
        SELECT pr.nombre AS proveedor, COUNT(*) AS num_compras, ROUND(SUM(c.total),2) AS total
        FROM compras c
        INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
        WHERE c.fecha_compra BETWEEN ? AND ?
          AND c.estado = 'registrada'
        GROUP BY c.id_proveedor
        ORDER BY total DESC
    ");
    $stmt->bind_param('ss', $desde, $hasta);
    $stmt->execute();
    $proveedores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte de proveedores', $labelPeriodo) ?>

    <h2>Compras por proveedor</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Proveedor</th>
                <th>Número de compras</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($proveedores)): ?>
            <tr><td colspan="4" class="empty">Sin datos para el período seleccionado.</td></tr>
        <?php else: foreach ($proveedores as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($p['proveedor']) ?></td>
                <td><?= $p['num_compras'] ?></td>
                <td><?= fmtMoney($p['total']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generarReporteProductos(mysqli $conn, string $desde, string $hasta, string $labelPeriodo): string {
    $stmt = $conn->prepare("
        SELECT p.nombre AS producto,
               c.nombre AS categoria,
               SUM(dv.cantidad) AS unidades_vendidas,
               ROUND(SUM(dv.subtotal), 2) AS ingresos,
               ROUND(SUM(p.precio_compra * dv.cantidad), 2) AS costo_total,
               ROUND((SUM(dv.subtotal) - SUM(p.precio_compra * dv.cantidad))
                     / NULLIF(SUM(dv.subtotal),0) * 100, 1) AS margen_pct
        FROM detalle_venta dv
        INNER JOIN ventas v ON v.id_venta = dv.id_venta
        INNER JOIN productos p ON p.id_producto = dv.id_producto
        INNER JOIN categorias c ON c.id_categoria = p.id_categoria
        WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
          AND v.estado = 'completada'
        GROUP BY dv.id_producto
        ORDER BY unidades_vendidas DESC
    ");
    $stmt->bind_param('ss', $desde, $hasta);
    $stmt->execute();
    $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte de productos', $labelPeriodo) ?>

    <h2>Ranking de productos</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Pos.</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Unidades</th>
                <th>Ingresos</th>
                <th>Costo total</th>
                <th>Margen %</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($productos)): ?>
            <tr><td colspan="7" class="empty">Sin datos para el período seleccionado.</td></tr>
        <?php else: foreach ($productos as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($p['producto']) ?></td>
                <td><?= htmlspecialchars($p['categoria']) ?></td>
                <td><?= $p['unidades_vendidas'] ?></td>
                <td><?= fmtMoney($p['ingresos']) ?></td>
                <td><?= fmtMoney($p['costo_total']) ?></td>
                <td><?= number_format((float)$p['margen_pct'], 1) ?>%</td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generarReporteFinanciero(mysqli $conn, string $desde, string $hasta, string $labelPeriodo): string {
    // 1. Ingresos y Gastos Totales
    $stmtUtil = $conn->prepare("
        SELECT 
            ROUND(SUM(dv.subtotal), 2) AS ingresos_ventas,
            ROUND(SUM(p.precio_compra * dv.cantidad), 2) AS costo_ventas,
            ROUND(SUM(dv.subtotal) - SUM(p.precio_compra * dv.cantidad), 2) AS utilidad_neta,
            ROUND((SUM(dv.subtotal) - SUM(p.precio_compra * dv.cantidad)) / NULLIF(SUM(dv.subtotal),0) * 100, 1) AS margen_pct
        FROM detalle_venta dv
        INNER JOIN ventas v ON v.id_venta = dv.id_venta
        INNER JOIN productos p ON p.id_producto = dv.id_producto
        WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado = 'completada'
    ");
    $stmtUtil->bind_param('ss', $desde, $hasta);
    $stmtUtil->execute();
    $utilidad = $stmtUtil->get_result()->fetch_assoc();
    $stmtUtil->close();

    // 2. Flujo de Caja
    $stmtFlujo = $conn->prepare("
        SELECT fecha, SUM(ingresos) AS ingresos, SUM(gastos) AS gastos
        FROM (
            SELECT DATE(fecha_venta) AS fecha, total AS ingresos, 0 AS gastos
            FROM ventas WHERE DATE(fecha_venta) BETWEEN ? AND ? AND estado = 'completada'
            UNION ALL
            SELECT fecha_compra AS fecha, 0 AS ingresos, total AS gastos
            FROM compras WHERE fecha_compra BETWEEN ? AND ? AND estado = 'registrada'
        ) flujos
        GROUP BY fecha ORDER BY fecha
    ");
    $stmtFlujo->bind_param('ssss', $desde, $hasta, $desde, $hasta);
    $stmtFlujo->execute();
    $flujos = $stmtFlujo->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtFlujo->close();

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte Financiero (Flujo de Caja)', $labelPeriodo) ?>

    <table class="kpi-wrapper">
        <tr>
            <td width="23.5%" class="kpi-box bg-green">
                <div class="kpi-title">Ingresos Totales</div>
                <div class="kpi-value"><?= fmtMoney($utilidad['ingresos_ventas'] ?? 0) ?></div>
            </td>
            <td width="2%"></td>
            <td width="23.5%" class="kpi-box bg-orange">
                <div class="kpi-title">Costo de Ventas</div>
                <div class="kpi-value"><?= fmtMoney($utilidad['costo_ventas'] ?? 0) ?></div>
            </td>
            <td width="2%"></td>
            <td width="23.5%" class="kpi-box bg-purple">
                <div class="kpi-title">Utilidad Bruta</div>
                <div class="kpi-value"><?= fmtMoney($utilidad['utilidad_neta'] ?? 0) ?></div>
            </td>
            <td width="2%"></td>
            <td width="23.5%" class="kpi-box bg-green" style="border-left-color: #0f172a;">
                <div class="kpi-title">Margen Promedio</div>
                <div class="kpi-value"><?= number_format($utilidad['margen_pct'] ?? 0, 1) ?>%</div>
            </td>
        </tr>
    </table>

    <h2>Detalle de Flujo de Caja</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ingresos (Ventas)</th>
                <th>Gastos (Compras)</th>
                <th>Balance Diario</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($flujos)): ?>
            <tr><td colspan="4" class="empty">Sin movimientos para el período seleccionado.</td></tr>
        <?php else: 
            $totalIngresos = 0; $totalGastos = 0;
            foreach ($flujos as $f): 
                $balance = (float)$f['ingresos'] - (float)$f['gastos'];
                $totalIngresos += (float)$f['ingresos'];
                $totalGastos += (float)$f['gastos'];
        ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                <td style="color:#16a34a; font-weight:bold;">+ <?= fmtMoney($f['ingresos']) ?></td>
                <td style="color:#ea580c; font-weight:bold;">- <?= fmtMoney($f['gastos']) ?></td>
                <td><strong style="color:<?= $balance >= 0 ? '#16a34a' : '#dc2626' ?>"><?= fmtMoney($balance) ?></strong></td>
            </tr>
        <?php endforeach; ?>
            <tr style="background:#f1f5f9;">
                <td><strong>TOTALES</strong></td>
                <td style="color:#16a34a; font-weight:bold;">+ <?= fmtMoney($totalIngresos) ?></td>
                <td style="color:#ea580c; font-weight:bold;">- <?= fmtMoney($totalGastos) ?></td>
                <td><strong style="color:<?= ($totalIngresos - $totalGastos) >= 0 ? '#16a34a' : '#dc2626' ?>"><?= fmtMoney($totalIngresos - $totalGastos) ?></strong></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generarReporteVencimientos(mysqli $conn, string $labelPeriodo): string {
    $res = $conn->query("
        SELECT 
            SUM(DATEDIFF(fecha_vencimiento, CURDATE()) > 90) AS vigentes,
            SUM(DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 0 AND 90) AS proximos,
            SUM(DATEDIFF(fecha_vencimiento, CURDATE()) < 0) AS vencidos
        FROM detalle_compra
        WHERE fecha_vencimiento IS NOT NULL
    ");
    $estado = $res->fetch_assoc();

    $resLotes = $conn->query("
        SELECT dc.numero_lote, p.nombre AS producto, DATE_FORMAT(dc.fecha_vencimiento,'%d/%m/%Y') AS fecha_vencimiento,
               DATEDIFF(dc.fecha_vencimiento, CURDATE()) AS dias_restantes,
               dc.cantidad
        FROM detalle_compra dc
        INNER JOIN productos p ON p.id_producto = dc.id_producto
        WHERE dc.fecha_vencimiento IS NOT NULL AND DATEDIFF(dc.fecha_vencimiento, CURDATE()) <= 90
        ORDER BY dias_restantes ASC
    ");
    $lotes = $resLotes->fetch_all(MYSQLI_ASSOC);

    ob_start(); ?>
    <?= buildHeaderHtml('Reporte de Vencimientos', 'Todo el inventario actual') ?>

    <table class="kpi-wrapper">
        <tr>
            <td width="32%" class="kpi-box bg-green">
                <div class="kpi-title">Lotes Vigentes (>90 días)</div>
                <div class="kpi-value"><?= (int)$estado['vigentes'] ?></div>
            </td>
            <td width="2%"></td>
            <td width="32%" class="kpi-box bg-orange">
                <div class="kpi-title">Próximos a Vencer</div>
                <div class="kpi-value"><?= (int)$estado['proximos'] ?></div>
            </td>
            <td width="2%"></td>
            <td width="32%" class="kpi-box bg-red">
                <div class="kpi-title">Lotes Vencidos</div>
                <div class="kpi-value"><?= (int)$estado['vencidos'] ?></div>
            </td>
        </tr>
    </table>

    <h2>Lotes Críticos (Vencidos o próximos)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Lote</th>
                <th>Producto</th>
                <th>Fecha Vto.</th>
                <th>Días Restantes</th>
                <th>Cant.</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($lotes)): ?>
            <tr><td colspan="6" class="empty">No hay lotes críticos registrados.</td></tr>
        <?php else: foreach ($lotes as $l): ?>
            <tr>
                <td><strong><?= htmlspecialchars($l['numero_lote'] ?: 'N/A') ?></strong></td>
                <td><?= htmlspecialchars($l['producto']) ?></td>
                <td><?= $l['fecha_vencimiento'] ?></td>
                <td><?= $l['dias_restantes'] < 0 ? 'Hace ' . abs($l['dias_restantes']) : $l['dias_restantes'] ?> días</td>
                <td><?= $l['cantidad'] ?></td>
                <td><?= $l['dias_restantes'] < 0 ? badge('agotado') : badge('bajo') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

switch ($tipo) {
    case 'ventas':
        $html = generarReporteVentas($conn, $desde, $hasta, $labelPeriodo);
        $tituloDoc = 'Reporte_Ventas';
        break;
    case 'compras':
        $html = generarReporteCompras($conn, $desde, $hasta, $labelPeriodo);
        $tituloDoc = 'Reporte_Compras';
        break;
    case 'inventario':
        $html = generarReporteInventario($conn, $desde, $hasta, $labelPeriodo);
        $tituloDoc = 'Reporte_Inventario';
        break;
    case 'empleados':
        $html = generarReporteEmpleados($conn, $desde, $hasta, $labelPeriodo);
        $tituloDoc = 'Reporte_Empleados';
        break;
    case 'proveedores':
        $html = generarReporteProveedores($conn, $desde, $hasta, $labelPeriodo);
        $tituloDoc = 'Reporte_Proveedores';
        break;
    case 'productos':
        $html = generarReporteProductos($conn, $desde, $hasta, $labelPeriodo);
        $tituloDoc = 'Reporte_Productos';
        break;
    case 'financiero':
        $html = generarReporteFinanciero($conn, $desde, $hasta, $labelPeriodo);
        $tituloDoc = 'Reporte_Financiero';
        break;
    case 'vencimientos':
        $html = generarReporteVencimientos($conn, $labelPeriodo);
        $tituloDoc = 'Reporte_Vencimientos';
        break;
    default:
        die('Tipo de reporte no válido.');
}

$conn->close();

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P',
    'margin_left' => 12,
    'margin_right' => 12,
    'margin_top' => 18,
    'margin_bottom' => 15,
    'tempDir' => __DIR__ . '/../tmp'
]);

$mpdf->SetHeader('
<table style="width:100%; font-size:8pt; color:#64748b; border-bottom:1px solid #e2e8f0;">
    <tr>
        <td><strong style="color:#0f172a;">DNS Pharmacy</strong> · ' . htmlspecialchars($tituloDoc) . '</td>
        <td style="text-align:right;">' . htmlspecialchars($labelPeriodo) . '</td>
    </tr>
</table>
');

$mpdf->SetFooter('
<table style="width:100%; font-size:7.5pt; color:#94a3b8; border-top:1px solid #e2e8f0;">
    <tr>
        <td>DNS Pharmacy · Drug Network Supply</td>
        <td style="text-align:center;">{DATE d/m/Y}</td>
        <td style="text-align:right;">Página {PAGENO} de {nbpg}</td>
    </tr>
</table>
');

$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$nombreArchivo = $tituloDoc . '_' . date('Y-m-d') . '.pdf';
$mpdf->Output($nombreArchivo, 'I');