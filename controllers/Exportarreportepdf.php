<?php
/**
 * ExportarReportePDF.php — DNS Pharmacy
 *
 * Genera un PDF completo del módulo de reportes usando mPDF.
 *
 * Instalar:
 *   composer require mpdf/mpdf
 *
 * URL:
 *   /DNS_Pharmacy/controllers/ExportarReportePDF.php?periodo=mes
 *   /DNS_Pharmacy/controllers/ExportarReportePDF.php?periodo=custom&desde=2025-01-01&hasta=2025-03-31
 */

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

// ════════════════════════════════════════════════════════════
// HELPERS GENERALES
// ════════════════════════════════════════════════════════════

function validarFecha(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

function mesNombreES(string $fecha): string
{
    $meses = [
        '01' => 'enero',
        '02' => 'febrero',
        '03' => 'marzo',
        '04' => 'abril',
        '05' => 'mayo',
        '06' => 'junio',
        '07' => 'julio',
        '08' => 'agosto',
        '09' => 'septiembre',
        '10' => 'octubre',
        '11' => 'noviembre',
        '12' => 'diciembre',
    ];

    $mes = date('m', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));

    return ucfirst($meses[$mes]) . ' ' . $anio;
}

function getRango(): array
{
    $periodo = $_GET['periodo'] ?? 'mes';

    switch ($periodo) {
        case 'hoy':
            return [date('Y-m-d'), date('Y-m-d')];

        case 'semana':
            return [
                date('Y-m-d', strtotime('monday this week')),
                date('Y-m-d', strtotime('sunday this week'))
            ];

        case 'mes_anterior':
            return [
                date('Y-m-01', strtotime('first day of last month')),
                date('Y-m-t', strtotime('last day of last month'))
            ];

        case 'anio':
            return [date('Y-01-01'), date('Y-12-31')];

        case 'custom':
            $desde = $_GET['desde'] ?? date('Y-m-01');
            $hasta = $_GET['hasta'] ?? date('Y-m-d');

            if (!validarFecha($desde)) {
                $desde = date('Y-m-01');
            }

            if (!validarFecha($hasta)) {
                $hasta = date('Y-m-d');
            }

            if ($desde > $hasta) {
                [$desde, $hasta] = [$hasta, $desde];
            }

            return [$desde, $hasta];

        case 'mes':
        default:
            return [date('Y-m-01'), date('Y-m-d')];
    }
}

function getLabelPeriodo(string $periodo, string $desde, string $hasta): string
{
    return match ($periodo) {
        'hoy'          => 'Hoy — ' . date('d/m/Y'),
        'semana'       => 'Esta semana',
        'mes'          => 'Este mes — ' . mesNombreES(date('Y-m-d')),
        'mes_anterior' => 'Mes anterior — ' . mesNombreES(date('Y-m-d', strtotime('first day of last month'))),
        'anio'         => 'Este año — ' . date('Y'),
        'custom'       => date('d/m/Y', strtotime($desde)) . ' al ' . date('d/m/Y', strtotime($hasta)),
        default        => 'Período personalizado',
    };
}

function fmtMoney(float $v): string
{
    return '$' . number_format($v, 2);
}

function badge(string $estado): string
{
    $map = [
        'completada' => 'completada',
        'anulada'    => 'anulada',
        'registrada' => 'registrada',
        'pendiente'  => 'pendiente',
        'agotado'    => 'agotado',
        'bajo'       => 'bajo',
        'normal'     => 'normal',
    ];

    $cls = $map[strtolower($estado)] ?? 'registrada';
    return "<span class='badge badge-{$cls}'>" . ucfirst($estado) . "</span>";
}

function posClass(int $i): string
{
    return match ($i) {
        0       => 'pos-1',
        1       => 'pos-2',
        2       => 'pos-3',
        default => '',
    };
}

function emptyRow(int $cols): string
{
    return "<tr><td colspan='{$cols}' class='empty-note'>Sin datos para el período seleccionado.</td></tr>";
}

function e(?string $txt): string
{
    return htmlspecialchars((string)$txt, ENT_QUOTES, 'UTF-8');
}

// ════════════════════════════════════════════════════════════
// PREPARACIÓN
// ════════════════════════════════════════════════════════════

[$desde, $hasta] = getRango();
$periodo = $_GET['periodo'] ?? 'mes';
$labelPeriodo = getLabelPeriodo($periodo, $desde, $hasta);

$tmpDir = __DIR__ . '/../tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

$conn = conectar();

// ════════════════════════════════════════════════════════════
// CONSULTAS
// ════════════════════════════════════════════════════════════

// ── KPIs generales ─────────────────────────────────────────
$stmtK = $conn->prepare("
    SELECT 
        COUNT(*) AS total_ventas,
        COALESCE(SUM(total),0) AS total_ingresos,
        COALESCE(AVG(total),0) AS ticket_promedio
    FROM ventas
    WHERE DATE(fecha_venta) BETWEEN ? AND ? 
      AND estado = 'completada'
");
$stmtK->bind_param('ss', $desde, $hasta);
$stmtK->execute();
$kpi = $stmtK->get_result()->fetch_assoc();
$stmtK->close();

// ── Compras registradas ────────────────────────────────────
$stmtCK = $conn->prepare("
    SELECT COUNT(*) AS cnt
    FROM compras
    WHERE fecha_compra BETWEEN ? AND ? 
      AND estado = 'registrada'
");
$stmtCK->bind_param('ss', $desde, $hasta);
$stmtCK->execute();
$totalCompras = (int)($stmtCK->get_result()->fetch_assoc()['cnt'] ?? 0);
$stmtCK->close();

// ── Resumen inventario ─────────────────────────────────────
$resInv = $conn->query("
    SELECT
        COUNT(*) AS total_activos,
        SUM(CASE WHEN stock_actual = 0 THEN 1 ELSE 0 END) AS agotados,
        SUM(CASE WHEN stock_actual > 0 AND stock_actual <= stock_minimo THEN 1 ELSE 0 END) AS bajo_minimo,
        SUM(CASE WHEN stock_actual > stock_minimo THEN 1 ELSE 0 END) AS normales
    FROM productos
    WHERE estado = 1
");
$resumenInv = $resInv->fetch_assoc();

$stockBajo = (int)($resumenInv['bajo_minimo'] ?? 0);
$productosAgotados = (int)($resumenInv['agotados'] ?? 0);
$totalProductosActivos = (int)($resumenInv['total_activos'] ?? 0);

// ── Ventas recientes ───────────────────────────────────────
$stmtV = $conn->prepare("
    SELECT 
        v.numero_ticket,
        DATE_FORMAT(v.fecha_venta,'%d/%m/%Y %H:%i') AS fecha_venta,
        CONCAT(u.nombre,' ',u.apellido) AS cajero,
        v.total,
        v.metodo_pago,
        v.estado
    FROM ventas v
    INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
    ORDER BY v.fecha_venta DESC
    LIMIT 100
");
$stmtV->bind_param('ss', $desde, $hasta);
$stmtV->execute();
$ventas = $stmtV->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtV->close();

// ── Ventas por método de pago ──────────────────────────────
$stmtMP = $conn->prepare("
    SELECT 
        metodo_pago AS metodo,
        COUNT(*) AS cantidad,
        ROUND(SUM(total), 2) AS monto
    FROM ventas
    WHERE DATE(fecha_venta) BETWEEN ? AND ?
      AND estado = 'completada'
    GROUP BY metodo_pago
    ORDER BY monto DESC
");
$stmtMP->bind_param('ss', $desde, $hasta);
$stmtMP->execute();
$metodoPago = $stmtMP->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtMP->close();

// ── Ventas por categoría ───────────────────────────────────
$stmtVC = $conn->prepare("
    SELECT 
        c.nombre AS categoria,
        SUM(dv.cantidad) AS unidades,
        ROUND(SUM(dv.subtotal), 2) AS total
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    INNER JOIN productos p ON p.id_producto = dv.id_producto
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
      AND v.estado = 'completada'
    GROUP BY c.id_categoria
    ORDER BY total DESC
");
$stmtVC->bind_param('ss', $desde, $hasta);
$stmtVC->execute();
$ventasCat = $stmtVC->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtVC->close();

// ── Ventas diarias ─────────────────────────────────────────
$stmtVD = $conn->prepare("
    SELECT 
        DATE(fecha_venta) AS fecha,
        COUNT(*) AS ventas,
        ROUND(SUM(total), 2) AS ingresos
    FROM ventas
    WHERE DATE(fecha_venta) BETWEEN ? AND ?
      AND estado = 'completada'
    GROUP BY DATE(fecha_venta)
    ORDER BY DATE(fecha_venta) ASC
");
$stmtVD->bind_param('ss', $desde, $hasta);
$stmtVD->execute();
$ventasDiarias = $stmtVD->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtVD->close();

// ── Stock bajo mínimo ──────────────────────────────────────
$resStockBajo = $conn->query("
    SELECT 
        p.nombre,
        c.nombre AS categoria,
        p.stock_actual,
        p.stock_minimo
    FROM productos p
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE p.stock_actual <= p.stock_minimo
      AND p.estado = 1
    ORDER BY p.stock_actual ASC
");
$productosStockBajo = $resStockBajo->fetch_all(MYSQLI_ASSOC);

// ── Ranking empleados ──────────────────────────────────────
$stmtE = $conn->prepare("
    SELECT 
        CONCAT(u.nombre,' ',u.apellido) AS empleado,
        r.nombre AS rol,
        COUNT(DISTINCT v.id_venta) AS num_ventas,
        ROUND(COALESCE(SUM(v.total),0),2) AS total_vendido,
        ROUND(COALESCE(AVG(v.total),0),2) AS ticket_promedio
    FROM usuarios u
    INNER JOIN roles r ON r.id_rol = u.id_rol
    LEFT JOIN ventas v 
        ON v.id_usuario = u.id_usuario
       AND DATE(v.fecha_venta) BETWEEN ? AND ?
       AND v.estado = 'completada'
    WHERE u.estado = 1
    GROUP BY u.id_usuario
    ORDER BY total_vendido DESC
");
$stmtE->bind_param('ss', $desde, $hasta);
$stmtE->execute();
$empleados = $stmtE->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtE->close();

// ── Historial compras ──────────────────────────────────────
$stmtC = $conn->prepare("
    SELECT 
        c.numero_factura,
        pr.nombre AS proveedor,
        DATE_FORMAT(c.fecha_compra,'%d/%m/%Y') AS fecha_compra,
        c.subtotal,
        c.impuesto,
        c.total,
        c.estado
    FROM compras c
    INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
    WHERE c.fecha_compra BETWEEN ? AND ?
    ORDER BY c.fecha_compra DESC
    LIMIT 100
");
$stmtC->bind_param('ss', $desde, $hasta);
$stmtC->execute();
$compras = $stmtC->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtC->close();

// ── Ranking proveedores ────────────────────────────────────
$stmtProv = $conn->prepare("
    SELECT 
        pr.nombre AS proveedor,
        COUNT(c.id_compra) AS compras,
        ROUND(SUM(c.total), 2) AS total_comprado
    FROM compras c
    INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
    WHERE c.fecha_compra BETWEEN ? AND ?
      AND c.estado = 'registrada'
    GROUP BY pr.id_proveedor
    ORDER BY total_comprado DESC
    LIMIT 10
");
$stmtProv->bind_param('ss', $desde, $hasta);
$stmtProv->execute();
$rankingProveedores = $stmtProv->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtProv->close();

// ── Ranking productos ──────────────────────────────────────
$stmtP = $conn->prepare("
    SELECT 
        p.nombre AS producto,
        c.nombre AS categoria,
        SUM(dv.cantidad) AS unidades_vendidas,
        ROUND(SUM(dv.subtotal), 2) AS ingresos,
        ROUND(SUM(p.precio_compra * dv.cantidad), 2) AS costo_total,
        ROUND(
            (SUM(dv.subtotal) - SUM(p.precio_compra * dv.cantidad)) / NULLIF(SUM(dv.subtotal), 0) * 100,
            1
        ) AS margen_pct
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    INNER JOIN productos p ON p.id_producto = dv.id_producto
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
      AND v.estado = 'completada'
    GROUP BY dv.id_producto
    ORDER BY unidades_vendidas DESC
    LIMIT 50
");
$stmtP->bind_param('ss', $desde, $hasta);
$stmtP->execute();
$productosRanking = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtP->close();

// ── Producto más vendido ───────────────────────────────────
$stmtTopProd = $conn->prepare("
    SELECT 
        p.nombre,
        SUM(dv.cantidad) AS unidades,
        ROUND(SUM(dv.subtotal), 2) AS total
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    INNER JOIN productos p ON p.id_producto = dv.id_producto
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
      AND v.estado = 'completada'
    GROUP BY p.id_producto
    ORDER BY unidades DESC
    LIMIT 1
");
$stmtTopProd->bind_param('ss', $desde, $hasta);
$stmtTopProd->execute();
$topProducto = $stmtTopProd->get_result()->fetch_assoc();
$stmtTopProd->close();

// ── Mejor empleado ─────────────────────────────────────────
$stmtTopEmp = $conn->prepare("
    SELECT 
        CONCAT(u.nombre,' ',u.apellido) AS empleado,
        ROUND(SUM(v.total),2) AS total_vendido,
        COUNT(*) AS ventas
    FROM ventas v
    INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
      AND v.estado = 'completada'
    GROUP BY u.id_usuario
    ORDER BY total_vendido DESC
    LIMIT 1
");
$stmtTopEmp->bind_param('ss', $desde, $hasta);
$stmtTopEmp->execute();
$topEmpleado = $stmtTopEmp->get_result()->fetch_assoc();
$stmtTopEmp->close();

// ── Categoría líder ────────────────────────────────────────
$stmtTopCat = $conn->prepare("
    SELECT 
        c.nombre AS categoria,
        ROUND(SUM(dv.subtotal),2) AS total
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    INNER JOIN productos p ON p.id_producto = dv.id_producto
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
      AND v.estado = 'completada'
    GROUP BY c.id_categoria
    ORDER BY total DESC
    LIMIT 1
");
$stmtTopCat->bind_param('ss', $desde, $hasta);
$stmtTopCat->execute();
$topCategoria = $stmtTopCat->get_result()->fetch_assoc();
$stmtTopCat->close();

// ── Ganancia estimada total ────────────────────────────────
$stmtGan = $conn->prepare("
    SELECT 
        ROUND(SUM(dv.subtotal),2) AS ingresos,
        ROUND(SUM(p.precio_compra * dv.cantidad),2) AS costos
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    INNER JOIN productos p ON p.id_producto = dv.id_producto
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
      AND v.estado = 'completada'
");
$stmtGan->bind_param('ss', $desde, $hasta);
$stmtGan->execute();
$resGan = $stmtGan->get_result()->fetch_assoc();
$stmtGan->close();

$ingresosEstimados = (float)($resGan['ingresos'] ?? 0);
$costosEstimados = (float)($resGan['costos'] ?? 0);
$gananciaTotal = $ingresosEstimados - $costosEstimados;

// ── Proveedor con más compras ──────────────────────────────
$topProveedor = $rankingProveedores[0] ?? null;

// ── Unidades totales vendidas ──────────────────────────────
$stmtUnid = $conn->prepare("
    SELECT COALESCE(SUM(dv.cantidad), 0) AS unidades
    FROM detalle_venta dv
    INNER JOIN ventas v ON v.id_venta = dv.id_venta
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
      AND v.estado = 'completada'
");
$stmtUnid->bind_param('ss', $desde, $hasta);
$stmtUnid->execute();
$totalUnidadesVendidas = (int)($stmtUnid->get_result()->fetch_assoc()['unidades'] ?? 0);
$stmtUnid->close();

$conn->close();

// ════════════════════════════════════════════════════════════
// ALERTAS Y RECOMENDACIONES
// ════════════════════════════════════════════════════════════

$alertas = [];

if ($stockBajo > 0) {
    $alertas[] = "Se detectaron {$stockBajo} productos con stock igual o inferior al mínimo establecido.";
}

if ($productosAgotados > 0) {
    $alertas[] = "Hay {$productosAgotados} productos agotados, lo que puede afectar la continuidad de ventas.";
}

if ((float)$kpi['ticket_promedio'] > 0 && (float)$kpi['ticket_promedio'] < 5) {
    $alertas[] = "El ticket promedio es relativamente bajo; conviene revisar estrategias de venta cruzada y promociones.";
}

if ($gananciaTotal < 0) {
    $alertas[] = "La ganancia estimada del período es negativa; se recomienda revisar costos y precios de venta.";
}

if (!empty($metodoPago)) {
    $metodoDominante = $metodoPago[0]['metodo'] ?? '';
    $montoDominante = (float)($metodoPago[0]['monto'] ?? 0);
    $alertas[] = "El método de pago con mayor volumen fue " . ucfirst($metodoDominante) . ", con " . fmtMoney($montoDominante) . ".";
}

$recomendaciones = [
    "Mantener monitoreo diario del inventario para reducir quiebres de stock y prevenir pérdidas de venta.",
    "Reforzar abastecimiento en categorías y productos con mejor desempeño para sostener la demanda.",
    "Revisar productos con bajo margen de ganancia para evaluar ajustes de precio o negociación con proveedores.",
    "Dar seguimiento al desempeño por empleado para identificar buenas prácticas de venta y replicarlas.",
    "Controlar el comportamiento de compras a proveedores para priorizar relaciones comerciales más rentables y estables.",
];

// ════════════════════════════════════════════════════════════
// CSS PARA mPDF
// ════════════════════════════════════════════════════════════

$css = '
body {
    font-family: DejaVuSansCondensed, sans-serif;
    font-size: 9pt;
    color: #334155;
    margin: 0;
}
h1 {
    font-size: 18pt;
    font-weight: bold;
    color: #0f172a;
    margin: 0 0 4px;
}
h2 {
    font-size: 12pt;
    font-weight: bold;
    color: #0f172a;
    margin: 18px 0 8px;
    border-bottom: 2px solid #3b82f6;
    padding-bottom: 5px;
}
h3 {
    font-size: 10pt;
    font-weight: bold;
    color: #475569;
    margin: 12px 0 6px;
}
p {
    font-size: 8.5pt;
    color: #64748b;
    margin: 0 0 4px;
}
small {
    font-size: 7.5pt;
    color: #94a3b8;
}
.kpi-grid {
    width: 100%;
    border-collapse: collapse;
    margin: 16px 0;
}
.kpi-cell {
    width: 33%;
    padding: 12px 14px;
    border-radius: 8px;
    vertical-align: top;
}
.kpi-val {
    font-size: 20pt;
    font-weight: bold;
    color: #0f172a;
    line-height: 1;
}
.kpi-lbl {
    font-size: 7.5pt;
    color: #94a3b8;
    margin-top: 3px;
}
.kpi-blue { background: #eff6ff; border-left: 4px solid #3b82f6; }
.kpi-green { background: #f0fdf4; border-left: 4px solid #22c55e; }
.kpi-orange { background: #fff7ed; border-left: 4px solid #f97316; }
.kpi-purple { background: #f5f3ff; border-left: 4px solid #8b5cf6; }
.kpi-red { background: #fef2f2; border-left: 4px solid #ef4444; }
.kpi-teal { background: #f0fdfa; border-left: 4px solid #14b8a6; }

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8pt;
    margin-bottom: 14px;
}
.data-table thead tr {
    background: #f1f5f9;
}
.data-table th {
    padding: 7px 10px;
    text-align: left;
    font-size: 7pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #94a3b8;
    border-bottom: 1px solid #e2e8f0;
}
.data-table td {
    padding: 7px 10px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
.data-table tbody tr:nth-child(even) td {
    background: #f8fafc;
}

.badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 7pt;
    font-weight: bold;
}
.badge-completada { background: #dcfce7; color: #166534; }
.badge-anulada { background: #fef2f2; color: #991b1b; }
.badge-registrada { background: #eff6ff; color: #1e40af; }
.badge-pendiente { background: #fefce8; color: #854d0e; }
.badge-agotado { background: #fef2f2; color: #991b1b; }
.badge-bajo { background: #fff7ed; color: #9a3412; }
.badge-normal { background: #ecfeff; color: #155e75; }

.pos-1 { font-weight: bold; color: #b45309; }
.pos-2 { font-weight: bold; color: #475569; }
.pos-3 { font-weight: bold; color: #9a3412; }

.pos { color: #166534; font-weight: bold; }
.neg { color: #991b1b; font-weight: bold; }

.section-divider {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 14px 0;
}
.empty-note {
    text-align: center;
    color: #94a3b8;
    font-style: italic;
    padding: 14px;
    font-size: 8pt;
}
.footer {
    font-size: 7pt;
    color: #94a3b8;
    text-align: center;
    border-top: 1px solid #e2e8f0;
    padding-top: 5px;
    margin-top: 8px;
}
.text-right { text-align: right; }
.page-break { page-break-before: always; }
.note-box {
    background: #f8fafc;
    border-left: 4px solid #3b82f6;
    padding: 10px 12px;
    margin: 8px 0 12px;
    font-size: 8.5pt;
    color: #334155;
}
';

// ════════════════════════════════════════════════════════════
// HTML DEL REPORTE
// ════════════════════════════════════════════════════════════

ob_start();
?>

<table style="width:100%; margin-bottom:6px;">
    <tr>
        <td style="vertical-align:middle;">
            <h1>DNS Pharmacy</h1>
            <p>Reporte estadístico integral del negocio</p>
            <p><strong>Período:</strong> <?= e($labelPeriodo) ?></p>
            <small>Generado el <?= date('d/m/Y \a \l\a\s H:i') ?></small>
        </td>
        <td style="text-align:right; vertical-align:middle; width:30%;">
            <p style="font-size:8pt; color:#94a3b8; font-style:italic;">Drug Network Supply</p>
        </td>
    </tr>
</table>

<hr class="section-divider">

<table class="kpi-grid">
    <tr>
        <td class="kpi-cell kpi-blue">
            <div class="kpi-val"><?= (int)$kpi['total_ventas'] ?></div>
            <div class="kpi-lbl">Ventas completadas</div>
        </td>
        <td style="width:2%;"></td>
        <td class="kpi-cell kpi-green">
            <div class="kpi-val"><?= fmtMoney((float)$kpi['total_ingresos']) ?></div>
            <div class="kpi-lbl">Ingresos totales</div>
        </td>
        <td style="width:2%;"></td>
        <td class="kpi-cell kpi-orange">
            <div class="kpi-val"><?= fmtMoney((float)$kpi['ticket_promedio']) ?></div>
            <div class="kpi-lbl">Ticket promedio</div>
        </td>
    </tr>
    <tr><td colspan="5" style="height:8px;"></td></tr>
    <tr>
        <td class="kpi-cell kpi-red">
            <div class="kpi-val"><?= $stockBajo ?></div>
            <div class="kpi-lbl">Productos bajo mínimo</div>
        </td>
        <td style="width:2%;"></td>
        <td class="kpi-cell kpi-teal">
            <div class="kpi-val"><?= $totalCompras ?></div>
            <div class="kpi-lbl">Compras registradas</div>
        </td>
        <td style="width:2%;"></td>
        <td class="kpi-cell kpi-purple">
            <div class="kpi-val"><?= fmtMoney($gananciaTotal) ?></div>
            <div class="kpi-lbl">Ganancia estimada</div>
        </td>
    </tr>
</table>

<h2>Resumen ejecutivo</h2>
<div class="note-box">
    Durante el período analizado se registraron <strong><?= (int)$kpi['total_ventas'] ?> ventas completadas</strong>,
    con ingresos por <strong><?= fmtMoney((float)$kpi['total_ingresos']) ?></strong>,
    ticket promedio de <strong><?= fmtMoney((float)$kpi['ticket_promedio']) ?></strong>
    y un total estimado de <strong><?= $totalUnidadesVendidas ?></strong> unidades vendidas.
</div>

<table class="data-table">
    <tbody>
        <tr>
            <td>
                El producto con mejor desempeño fue
                <strong><?= e($topProducto['nombre'] ?? 'N/D') ?></strong>,
                con <strong><?= (int)($topProducto['unidades'] ?? 0) ?> unidades</strong> vendidas
                y <strong><?= fmtMoney((float)($topProducto['total'] ?? 0)) ?></strong> en ingresos.
            </td>
        </tr>
        <tr>
            <td>
                La categoría líder fue
                <strong><?= e($topCategoria['categoria'] ?? 'N/D') ?></strong>,
                con un total de <strong><?= fmtMoney((float)($topCategoria['total'] ?? 0)) ?></strong>.
            </td>
        </tr>
        <tr>
            <td>
                El empleado con mejor desempeño fue
                <strong><?= e($topEmpleado['empleado'] ?? 'N/D') ?></strong>,
                con ventas por <strong><?= fmtMoney((float)($topEmpleado['total_vendido'] ?? 0)) ?></strong>.
            </td>
        </tr>
        <tr>
            <td>
                Se identificaron <strong><?= $stockBajo ?></strong> productos con stock bajo,
                <strong><?= $productosAgotados ?></strong> agotados y una ganancia estimada de
                <strong><?= fmtMoney($gananciaTotal) ?></strong> durante el período.
            </td>
        </tr>
        <tr>
            <td>
                El proveedor con mayor volumen de compras fue
                <strong><?= e($topProveedor['proveedor'] ?? 'N/D') ?></strong>,
                acumulando <strong><?= fmtMoney((float)($topProveedor['total_comprado'] ?? 0)) ?></strong>.
            </td>
        </tr>
    </tbody>
</table>

<h2>Alertas y observaciones</h2>
<table class="data-table">
    <tbody>
        <?php if (empty($alertas)): ?>
            <tr>
                <td class="empty-note">No se detectaron alertas relevantes en el período.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($alertas as $i => $alerta): ?>
                <tr>
                    <td><strong><?= $i + 1 ?>.</strong> <?= e($alerta) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="page-break"></div>

<h2>1. Ventas del período</h2>

<h3>Resumen de métodos de pago</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Método</th>
            <th>Cantidad</th>
            <th>Monto total</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($metodoPago)): ?>
            <?= emptyRow(3) ?>
        <?php else: ?>
            <?php foreach ($metodoPago as $m): ?>
                <tr>
                    <td><?= e(ucfirst($m['metodo'])) ?></td>
                    <td><?= (int)$m['cantidad'] ?></td>
                    <td><strong><?= fmtMoney((float)$m['monto']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<h3>Ventas por categoría</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Categoría</th>
            <th>Unidades vendidas</th>
            <th>Total ingresos</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($ventasCat)): ?>
            <?= emptyRow(3) ?>
        <?php else: ?>
            <?php foreach ($ventasCat as $vc): ?>
                <tr>
                    <td><?= e($vc['categoria']) ?></td>
                    <td><?= (int)$vc['unidades'] ?></td>
                    <td><strong><?= fmtMoney((float)$vc['total']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<h3>Comportamiento diario de ventas</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>N° de ventas</th>
            <th>Ingresos</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($ventasDiarias)): ?>
            <?= emptyRow(3) ?>
        <?php else: ?>
            <?php foreach ($ventasDiarias as $d): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                    <td><?= (int)$d['ventas'] ?></td>
                    <td><strong><?= fmtMoney((float)$d['ingresos']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<h3>Detalle de ventas registradas (últimas <?= min(count($ventas), 100) ?>)</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Ticket</th>
            <th>Fecha</th>
            <th>Cajero</th>
            <th>Total</th>
            <th>Método</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($ventas)): ?>
            <?= emptyRow(7) ?>
        <?php else: ?>
            <?php foreach ($ventas as $i => $v): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($v['numero_ticket']) ?></strong></td>
                    <td><?= e($v['fecha_venta']) ?></td>
                    <td><?= e($v['cajero']) ?></td>
                    <td><strong><?= fmtMoney((float)$v['total']) ?></strong></td>
                    <td><?= e(ucfirst($v['metodo_pago'])) ?></td>
                    <td><?= badge($v['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="page-break"></div>

<h2>2. Inventario</h2>

<table class="kpi-grid">
    <tr>
        <td class="kpi-cell kpi-blue">
            <div class="kpi-val"><?= $totalProductosActivos ?></div>
            <div class="kpi-lbl">Productos activos</div>
        </td>
        <td style="width:2%;"></td>
        <td class="kpi-cell kpi-red">
            <div class="kpi-val"><?= $productosAgotados ?></div>
            <div class="kpi-lbl">Productos agotados</div>
        </td>
        <td style="width:2%;"></td>
        <td class="kpi-cell kpi-orange">
            <div class="kpi-val"><?= $stockBajo ?></div>
            <div class="kpi-lbl">Stock bajo mínimo</div>
        </td>
    </tr>
</table>

<h3>Productos bajo mínimo</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Stock actual</th>
            <th>Stock mínimo</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($productosStockBajo)): ?>
            <tr>
                <td colspan="6" class="empty-note">✓ Todos los productos tienen stock suficiente.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($productosStockBajo as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($p['nombre']) ?></strong></td>
                    <td><?= e($p['categoria']) ?></td>
                    <td style="color:#ef4444; font-weight:bold;"><?= (int)$p['stock_actual'] ?></td>
                    <td><?= (int)$p['stock_minimo'] ?></td>
                    <td><?= ((int)$p['stock_actual'] === 0) ? badge('agotado') : badge('bajo') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="page-break"></div>

<h2>3. Ranking de empleados</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Pos.</th>
            <th>Empleado</th>
            <th>Rol</th>
            <th>Ventas</th>
            <th>Total vendido</th>
            <th>Ticket prom.</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($empleados)): ?>
            <?= emptyRow(6) ?>
        <?php else: ?>
            <?php foreach ($empleados as $i => $eItem): ?>
                <tr>
                    <td class="<?= posClass($i) ?>"><?= $i + 1 ?></td>
                    <td class="<?= posClass($i) ?>"><?= e($eItem['empleado']) ?></td>
                    <td><?= e($eItem['rol']) ?></td>
                    <td><?= (int)$eItem['num_ventas'] ?></td>
                    <td><strong><?= fmtMoney((float)$eItem['total_vendido']) ?></strong></td>
                    <td><?= fmtMoney((float)$eItem['ticket_promedio']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="page-break"></div>

<h2>4. Proveedores</h2>

<h3>Ranking de proveedores por monto comprado</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Pos.</th>
            <th>Proveedor</th>
            <th>N° Compras</th>
            <th>Total comprado</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rankingProveedores)): ?>
            <?= emptyRow(4) ?>
        <?php else: ?>
            <?php foreach ($rankingProveedores as $i => $prov): ?>
                <tr>
                    <td class="<?= posClass($i) ?>"><?= $i + 1 ?></td>
                    <td class="<?= posClass($i) ?>"><?= e($prov['proveedor']) ?></td>
                    <td><?= (int)$prov['compras'] ?></td>
                    <td><strong><?= fmtMoney((float)$prov['total_comprado']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<h3>Historial de compras a proveedores</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>N° Factura</th>
            <th>Proveedor</th>
            <th>Fecha</th>
            <th>Subtotal</th>
            <th>IVA</th>
            <th>Total</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($compras)): ?>
            <?= emptyRow(8) ?>
        <?php else: ?>
            <?php foreach ($compras as $i => $c): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($c['numero_factura']) ?></strong></td>
                    <td><?= e($c['proveedor']) ?></td>
                    <td><?= e($c['fecha_compra']) ?></td>
                    <td><?= fmtMoney((float)$c['subtotal']) ?></td>
                    <td><?= fmtMoney((float)$c['impuesto']) ?></td>
                    <td><strong><?= fmtMoney((float)$c['total']) ?></strong></td>
                    <td><?= badge($c['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="page-break"></div>

<h2>5. Ranking de productos</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Pos.</th>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Unidades</th>
            <th>Ingresos</th>
            <th>Costo</th>
            <th>Ganancia</th>
            <th>Margen%</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($productosRanking)): ?>
            <?= emptyRow(8) ?>
        <?php else: ?>
            <?php foreach ($productosRanking as $i => $p): ?>
                <?php
                    $ganancia = (float)$p['ingresos'] - (float)$p['costo_total'];
                    $gananciaClass = $ganancia >= 0 ? 'pos' : 'neg';
                    $margen = (float)$p['margen_pct'];
                ?>
                <tr>
                    <td class="<?= posClass($i) ?>"><?= $i + 1 ?></td>
                    <td class="<?= posClass($i) ?>"><?= e($p['producto']) ?></td>
                    <td><?= e($p['categoria']) ?></td>
                    <td><?= (int)$p['unidades_vendidas'] ?></td>
                    <td><?= fmtMoney((float)$p['ingresos']) ?></td>
                    <td><?= fmtMoney((float)$p['costo_total']) ?></td>
                    <td class="<?= $gananciaClass ?>"><?= fmtMoney($ganancia) ?></td>
                    <td class="<?= $gananciaClass ?>"><?= number_format($margen, 1) ?>%</td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<h2>6. Conclusiones y recomendaciones</h2>
<table class="data-table">
    <tbody>
        <?php foreach ($recomendaciones as $i => $rec): ?>
            <tr>
                <td><strong><?= $i + 1 ?>.</strong> <?= e($rec) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="footer">
    DNS Pharmacy · Drug Network Supply · Reporte generado el <?= date('d/m/Y H:i') ?> ·
    Sistema POS desarrollado por DevCore
</div>

<?php
$html = ob_get_clean();

// ════════════════════════════════════════════════════════════
// GENERAR PDF
// ════════════════════════════════════════════════════════════

$mpdf = new Mpdf([
    'mode'              => 'utf-8',
    'format'            => 'A4',
    'orientation'       => 'P',
    'margin_left'       => 14,
    'margin_right'      => 14,
    'margin_top'        => 18,
    'margin_bottom'     => 16,
    'margin_header'     => 8,
    'margin_footer'     => 8,
    'tempDir'           => $tmpDir,
    'autoScriptToLang'  => true,
    'autoLangToFont'    => true,
]);

$mpdf->SetTitle('Reporte Estadístico - DNS Pharmacy');
$mpdf->SetAuthor('DNS Pharmacy / DevCore');
$mpdf->SetCreator('Sistema POS DNS Pharmacy');
$mpdf->SetSubject('Reporte estadístico del negocio');
$mpdf->SetDisplayMode('fullpage');

$mpdf->SetHeader(
    '<table style="width:100%; font-size:8pt; color:#94a3b8; border-bottom:1px solid #e2e8f0; padding-bottom:4px;">
        <tr>
            <td><strong style="color:#0f172a;">DNS Pharmacy</strong> · Reporte estadístico</td>
            <td style="text-align:right;">' . e($labelPeriodo) . '</td>
        </tr>
    </table>'
);

$mpdf->SetFooter(
    '<table style="width:100%; font-size:7.5pt; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:4px;">
        <tr>
            <td>Drug Network Supply · DevCore</td>
            <td style="text-align:center;">{DATE d/m/Y}</td>
            <td style="text-align:right;">Página {PAGENO} de {nbpg}</td>
        </tr>
    </table>'
);

$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$nombreArchivo = 'DNS_Pharmacy_Reporte_' . date('Y-m-d_H-i-s') . '.pdf';
$mpdf->Output($nombreArchivo, 'D');
exit;