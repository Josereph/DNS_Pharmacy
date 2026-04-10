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
 *
 * Secciones generadas:
 *   1. Portada con KPIs
 *   2. Ventas (tabla + resumen por método de pago + por categoría)
 *   3. Inventario (stock actual, productos bajo mínimo)
 *   4. Empleados (ranking)
 *   5. Proveedores (historial compras)
 *   6. Productos (ranking con margen)
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
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

// ── Rango de fechas ──────────────────────────────────────────
function getRango(): array {
    $periodo = $_GET['periodo'] ?? 'mes';
    switch ($periodo) {
        case 'hoy':          return [date('Y-m-d'), date('Y-m-d')];
        case 'semana':       return [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))];
        case 'mes_anterior': return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))];
        case 'anio':         return [date('Y-01-01'), date('Y-12-31')];
        case 'custom':       return [$_GET['desde'] ?? date('Y-m-01'), $_GET['hasta'] ?? date('Y-m-d')];
        default:             return [date('Y-m-01'), date('Y-m-d')];
    }
}

[$desde, $hasta] = getRango();
$conn = conectar();

$labelPeriodo = match($_GET['periodo'] ?? 'mes') {
    'hoy'          => 'Hoy — ' . date('d/m/Y'),
    'semana'       => 'Esta semana',
    'mes'          => 'Este mes — ' . date('F Y'),
    'mes_anterior' => 'Mes anterior',
    'anio'         => 'Este año — ' . date('Y'),
    'custom'       => date('d/m/Y', strtotime($desde)) . ' al ' . date('d/m/Y', strtotime($hasta)),
    default        => 'Período personalizado',
};

// ════════════════════════════════════════════════════════════
// DATOS — mismas queries que ReportesController.php
// ════════════════════════════════════════════════════════════

// ── KPIs ──────────────────────────────────────────────────
$stmtK = $conn->prepare("
    SELECT COUNT(*) AS total_ventas,
           COALESCE(SUM(total),0) AS total_ingresos,
           COALESCE(AVG(total),0) AS ticket_promedio
    FROM ventas
    WHERE DATE(fecha_venta) BETWEEN ? AND ? AND estado='completada'
");
$stmtK->bind_param('ss', $desde, $hasta);
$stmtK->execute();
$kpi = $stmtK->get_result()->fetch_assoc();
$stmtK->close();

$resStk = $conn->query("SELECT COUNT(*) AS cnt FROM productos WHERE stock_actual <= stock_minimo AND estado=1");
$stockBajo = (int)$resStk->fetch_assoc()['cnt'];

$stmtCK = $conn->prepare("SELECT COUNT(*) AS cnt FROM compras WHERE fecha_compra BETWEEN ? AND ? AND estado='registrada'");
$stmtCK->bind_param('ss', $desde, $hasta);
$stmtCK->execute();
$totalCompras = (int)$stmtCK->get_result()->fetch_assoc()['cnt'];
$stmtCK->close();

// ── Ventas recientes ──────────────────────────────────────
$stmtV = $conn->prepare("
    SELECT v.numero_ticket,
           DATE_FORMAT(v.fecha_venta,'%d/%m/%Y %H:%i') AS fecha_venta,
           CONCAT(u.nombre,' ',u.apellido) AS cajero,
           v.total, v.metodo_pago, v.estado
    FROM ventas v
    INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
    ORDER BY v.fecha_venta DESC LIMIT 100
");
$stmtV->bind_param('ss', $desde, $hasta);
$stmtV->execute();
$ventas = $stmtV->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtV->close();

// ── Método de pago ────────────────────────────────────────
$stmtMP = $conn->prepare("
    SELECT metodo_pago AS metodo, COUNT(*) AS cantidad, ROUND(SUM(total),2) AS monto
    FROM ventas WHERE DATE(fecha_venta) BETWEEN ? AND ? AND estado='completada'
    GROUP BY metodo_pago
");
$stmtMP->bind_param('ss', $desde, $hasta);
$stmtMP->execute();
$metodoPago = $stmtMP->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtMP->close();

// ── Ventas por categoría ──────────────────────────────────
$stmtVC = $conn->prepare("
    SELECT c.nombre AS categoria, SUM(dv.cantidad) AS unidades, ROUND(SUM(dv.subtotal),2) AS total
    FROM detalle_venta dv
    INNER JOIN ventas v    ON v.id_venta     = dv.id_venta
    INNER JOIN productos p  ON p.id_producto  = dv.id_producto
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado='completada'
    GROUP BY c.id_categoria ORDER BY total DESC
");
$stmtVC->bind_param('ss', $desde, $hasta);
$stmtVC->execute();
$ventasCat = $stmtVC->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtVC->close();

// ── Stock bajo mínimo ─────────────────────────────────────
$resStockBajo = $conn->query("
    SELECT p.nombre, c.nombre AS categoria, p.stock_actual, p.stock_minimo
    FROM productos p
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE p.stock_actual <= p.stock_minimo AND p.estado=1
    ORDER BY p.stock_actual ASC
");
$productosStockBajo = $resStockBajo->fetch_all(MYSQLI_ASSOC);

// ── Ranking empleados ─────────────────────────────────────
$stmtE = $conn->prepare("
    SELECT CONCAT(u.nombre,' ',u.apellido) AS empleado,
           r.nombre AS rol,
           COUNT(DISTINCT v.id_venta) AS num_ventas,
           ROUND(COALESCE(SUM(v.total),0),2) AS total_vendido,
           ROUND(COALESCE(AVG(v.total),0),2) AS ticket_promedio
    FROM usuarios u
    INNER JOIN roles r ON r.id_rol = u.id_rol
    LEFT JOIN ventas v ON v.id_usuario = u.id_usuario
        AND DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado='completada'
    WHERE u.estado=1
    GROUP BY u.id_usuario ORDER BY total_vendido DESC
");
$stmtE->bind_param('ss', $desde, $hasta);
$stmtE->execute();
$empleados = $stmtE->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtE->close();

// ── Historial compras ─────────────────────────────────────
$stmtC = $conn->prepare("
    SELECT c.numero_factura, pr.nombre AS proveedor,
           DATE_FORMAT(c.fecha_compra,'%d/%m/%Y') AS fecha_compra,
           c.subtotal, c.impuesto, c.total, c.estado
    FROM compras c
    INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
    WHERE c.fecha_compra BETWEEN ? AND ?
    ORDER BY c.fecha_compra DESC LIMIT 100
");
$stmtC->bind_param('ss', $desde, $hasta);
$stmtC->execute();
$compras = $stmtC->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtC->close();

// ── Ranking productos ─────────────────────────────────────
$stmtP = $conn->prepare("
    SELECT p.nombre AS producto, c.nombre AS categoria,
           SUM(dv.cantidad) AS unidades_vendidas,
           ROUND(SUM(dv.subtotal),2) AS ingresos,
           ROUND(SUM(p.precio_compra * dv.cantidad),2) AS costo_total,
           ROUND((SUM(dv.subtotal)-SUM(p.precio_compra*dv.cantidad))/NULLIF(SUM(dv.subtotal),0)*100,1) AS margen_pct
    FROM detalle_venta dv
    INNER JOIN ventas v    ON v.id_venta     = dv.id_venta
    INNER JOIN productos p  ON p.id_producto  = dv.id_producto
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado='completada'
    GROUP BY dv.id_producto ORDER BY unidades_vendidas DESC LIMIT 50
");
$stmtP->bind_param('ss', $desde, $hasta);
$stmtP->execute();
$productosRanking = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtP->close();

$conn->close();

// ════════════════════════════════════════════════════════════
// CSS INLINE PARA mPDF
// ════════════════════════════════════════════════════════════
$css = '
body        { font-family: DejaVuSansCondensed, sans-serif; font-size: 9pt; color: #334155; margin: 0; }
h1          { font-size: 18pt; font-weight: bold; color: #0f172a; margin: 0 0 4px; }
h2          { font-size: 12pt; font-weight: bold; color: #0f172a; margin: 18px 0 8px; border-bottom: 2px solid #3b82f6; padding-bottom: 5px; }
h3          { font-size: 10pt; font-weight: bold; color: #475569; margin: 12px 0 6px; }
p           { font-size: 8.5pt; color: #64748b; margin: 0 0 4px; }
small       { font-size: 7.5pt; color: #94a3b8; }

/* ── Portada KPI grid ── */
.kpi-grid   { width: 100%; border-collapse: collapse; margin: 16px 0; }
.kpi-cell   { width: 33%; padding: 12px 14px; border-radius: 8px; vertical-align: top; }
.kpi-val    { font-size: 20pt; font-weight: bold; color: #0f172a; line-height: 1; }
.kpi-lbl    { font-size: 7.5pt; color: #94a3b8; margin-top: 3px; }
.kpi-blue   { background: #eff6ff; border-left: 4px solid #3b82f6; }
.kpi-green  { background: #f0fdf4; border-left: 4px solid #22c55e; }
.kpi-orange { background: #fff7ed; border-left: 4px solid #f97316; }
.kpi-purple { background: #f5f3ff; border-left: 4px solid #8b5cf6; }
.kpi-red    { background: #fef2f2; border-left: 4px solid #ef4444; }
.kpi-teal   { background: #f0fdfa; border-left: 4px solid #14b8a6; }

/* ── Tablas de datos ── */
.data-table          { width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 14px; }
.data-table thead tr { background: #f1f5f9; }
.data-table th       { padding: 7px 10px; text-align: left; font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; border-bottom: 1px solid #e2e8f0; }
.data-table td       { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.data-table tbody tr:nth-child(even) td { background: #f8fafc; }

/* ── Badges ── */
.badge             { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 7pt; font-weight: bold; }
.badge-entrada     { background: #dcfce7; color: #166534; }
.badge-completada  { background: #dcfce7; color: #166534; }
.badge-anulada     { background: #fef2f2; color: #991b1b; }
.badge-registrada  { background: #eff6ff; color: #1e40af; }
.badge-pendiente   { background: #fefce8; color: #854d0e; }
.badge-agotado     { background: #fef2f2; color: #991b1b; }
.badge-bajo        { background: #fff7ed; color: #9a3412; }

/* ── Rankings / posiciones ── */
.pos-1 { font-weight: bold; color: #b45309; }
.pos-2 { font-weight: bold; color: #475569; }
.pos-3 { font-weight: bold; color: #9a3412; }

/* ── Colores de ganancia ── */
.pos { color: #166534; font-weight: bold; }
.neg { color: #991b1b; font-weight: bold; }

/* ── Separador sección ── */
.section-divider { border: none; border-top: 1px solid #e2e8f0; margin: 14px 0; }

/* ── Nota vacía ── */
.empty-note { text-align: center; color: #94a3b8; font-style: italic; padding: 14px; font-size: 8pt; }

/* ── Footer PDF ── */
.footer { font-size: 7pt; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 5px; margin-top: 8px; }
';

// ════════════════════════════════════════════════════════════
// HELPERS HTML
// ════════════════════════════════════════════════════════════

function badge(string $estado): string {
    $map = [
        'completada' => 'completada','anulada' => 'anulada','registrada' => 'registrada',
        'pendiente'  => 'pendiente', 'agotado'  => 'agotado','bajo'       => 'bajo',
    ];
    $cls = $map[strtolower($estado)] ?? 'registrada';
    return "<span class='badge badge-{$cls}'>" . ucfirst($estado) . "</span>";
}

function fmtMoney(float $v): string { return '$' . number_format($v, 2); }

function posClass(int $i): string {
    return match($i) { 0 => 'pos-1', 1 => 'pos-2', 2 => 'pos-3', default => '' };
}

function emptyRow(int $cols): string {
    return "<tr><td colspan='{$cols}' class='empty-note'>Sin datos para el período seleccionado.</td></tr>";
}

// ════════════════════════════════════════════════════════════
// HTML DEL REPORTE
// ════════════════════════════════════════════════════════════
ob_start();
?>

<!-- ══ PORTADA ══════════════════════════════════════════════ -->
<table style="width:100%; margin-bottom:6px;">
    <tr>
        <td style="vertical-align:middle;">
            <h1>DNS Pharmacy</h1>
            <p>Reporte estadístico del negocio</p>
            <p><strong>Período:</strong> <?= htmlspecialchars($labelPeriodo) ?></p>
            <small>Generado el <?= date('d/m/Y \a \l\a\s H:i') ?></small>
        </td>
        <td style="text-align:right; vertical-align:middle; width:30%;">
            <p style="font-size:8pt; color:#94a3b8; font-style:italic;">Drug Network Supply</p>
        </td>
    </tr>
</table>

<hr class="section-divider">

<!-- KPIs de portada -->
<table class="kpi-grid">
    <tr>
        <td class="kpi-cell kpi-blue">
            <div class="kpi-val"><?= (int)$kpi['total_ventas'] ?></div>
            <div class="kpi-lbl">Ventas realizadas</div>
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
            <div class="kpi-val"><?= count($productosRanking) ?></div>
            <div class="kpi-lbl">Productos con ventas</div>
        </td>
    </tr>
</table>

<!-- ══ SECCIÓN 1: VENTAS ══════════════════════════════════════ -->
<h2>1. Ventas del período</h2>

<?php if (!empty($metodoPago)): ?>
<h3>Ventas por método de pago</h3>
<table class="data-table">
    <thead><tr><th>Método</th><th>Cantidad</th><th>Monto total</th></tr></thead>
    <tbody>
    <?php foreach ($metodoPago as $m): ?>
        <tr>
            <td><?= ucfirst($m['metodo']) ?></td>
            <td><?= $m['cantidad'] ?></td>
            <td><strong><?= fmtMoney((float)$m['monto']) ?></strong></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($ventasCat)): ?>
<h3>Ventas por categoría</h3>
<table class="data-table">
    <thead><tr><th>Categoría</th><th>Unidades vendidas</th><th>Total ingresos</th></tr></thead>
    <tbody>
    <?php foreach ($ventasCat as $vc): ?>
        <tr>
            <td><?= htmlspecialchars($vc['categoria']) ?></td>
            <td><?= $vc['unidades'] ?></td>
            <td><strong><?= fmtMoney((float)$vc['total']) ?></strong></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<h3>Detalle de ventas (últimas <?= min(count($ventas), 100) ?>)</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th><th>Ticket</th><th>Fecha</th><th>Cajero</th>
            <th>Total</th><th>Método</th><th>Estado</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($ventas)): echo emptyRow(7);
    else: foreach ($ventas as $i => $v): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($v['numero_ticket']) ?></strong></td>
            <td><?= $v['fecha_venta'] ?></td>
            <td><?= htmlspecialchars($v['cajero']) ?></td>
            <td><strong><?= fmtMoney((float)$v['total']) ?></strong></td>
            <td><?= ucfirst($v['metodo_pago']) ?></td>
            <td><?= badge($v['estado']) ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ══ SECCIÓN 2: INVENTARIO ════════════════════════════════ -->
<h2>2. Inventario — Productos bajo mínimo</h2>
<table class="data-table">
    <thead>
        <tr><th>#</th><th>Producto</th><th>Categoría</th><th>Stock actual</th><th>Stock mínimo</th><th>Estado</th></tr>
    </thead>
    <tbody>
    <?php if (empty($productosStockBajo)): ?>
        <tr><td colspan="6" class="empty-note">✓ Todos los productos tienen stock suficiente.</td></tr>
    <?php else: foreach ($productosStockBajo as $i => $p): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
            <td><?= htmlspecialchars($p['categoria']) ?></td>
            <td style="color:#ef4444; font-weight:bold;"><?= $p['stock_actual'] ?></td>
            <td><?= $p['stock_minimo'] ?></td>
            <td><?= $p['stock_actual'] == 0 ? badge('agotado') : badge('bajo') ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ══ SECCIÓN 3: EMPLEADOS ═════════════════════════════════ -->
<h2>3. Ranking de empleados</h2>
<table class="data-table">
    <thead>
        <tr><th>Pos.</th><th>Empleado</th><th>Rol</th><th>Ventas</th><th>Total vendido</th><th>Ticket prom.</th></tr>
    </thead>
    <tbody>
    <?php if (empty($empleados)): echo emptyRow(6);
    else: foreach ($empleados as $i => $e): ?>
        <tr>
            <td class="<?= posClass($i) ?>"><?= $i+1 ?></td>
            <td class="<?= posClass($i) ?>"><?= htmlspecialchars($e['empleado']) ?></td>
            <td><?= htmlspecialchars($e['rol']) ?></td>
            <td><?= $e['num_ventas'] ?></td>
            <td><strong><?= fmtMoney((float)$e['total_vendido']) ?></strong></td>
            <td><?= fmtMoney((float)$e['ticket_promedio']) ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ══ SECCIÓN 4: PROVEEDORES ════════════════════════════════ -->
<h2>4. Historial de compras a proveedores</h2>
<table class="data-table">
    <thead>
        <tr><th>#</th><th>N° Factura</th><th>Proveedor</th><th>Fecha</th><th>Subtotal</th><th>IVA</th><th>Total</th><th>Estado</th></tr>
    </thead>
    <tbody>
    <?php if (empty($compras)): echo emptyRow(8);
    else: foreach ($compras as $i => $c): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($c['numero_factura']) ?></strong></td>
            <td><?= htmlspecialchars($c['proveedor']) ?></td>
            <td><?= $c['fecha_compra'] ?></td>
            <td><?= fmtMoney((float)$c['subtotal']) ?></td>
            <td><?= fmtMoney((float)$c['impuesto']) ?></td>
            <td><strong><?= fmtMoney((float)$c['total']) ?></strong></td>
            <td><?= badge($c['estado']) ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ══ SECCIÓN 5: PRODUCTOS ══════════════════════════════════ -->
<h2>5. Ranking de productos</h2>
<table class="data-table">
    <thead>
        <tr><th>Pos.</th><th>Producto</th><th>Categoría</th><th>Unidades</th><th>Ingresos</th><th>Costo</th><th>Ganancia</th><th>Margen%</th></tr>
    </thead>
    <tbody>
    <?php if (empty($productosRanking)): echo emptyRow(8);
    else: foreach ($productosRanking as $i => $p):
        $ganancia  = (float)$p['ingresos'] - (float)$p['costo_total'];
        $gananciaClass = $ganancia >= 0 ? 'pos' : 'neg';
        $margen    = (float)$p['margen_pct'];
    ?>
        <tr>
            <td class="<?= posClass($i) ?>"><?= $i+1 ?></td>
            <td class="<?= posClass($i) ?>"><?= htmlspecialchars($p['producto']) ?></td>
            <td><?= htmlspecialchars($p['categoria']) ?></td>
            <td><?= $p['unidades_vendidas'] ?></td>
            <td><?= fmtMoney((float)$p['ingresos']) ?></td>
            <td><?= fmtMoney((float)$p['costo_total']) ?></td>
            <td class="<?= $gananciaClass ?>"><?= fmtMoney($ganancia) ?></td>
            <td class="<?= $gananciaClass ?>"><?= number_format($margen, 1) ?>%</td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ══ FOOTER ════════════════════════════════════════════════ -->
<div class="footer">
    DNS Pharmacy · Drug Network Supply · Reporte generado el <?= date('d/m/Y H:i') ?> ·
    Sistema POS desarrollado por DevCore
</div>

<?php
$html = ob_get_clean();

// ════════════════════════════════════════════════════════════
// CONFIGURAR Y GENERAR PDF CON mPDF
// ════════════════════════════════════════════════════════════
$mpdf = new Mpdf([
    'mode'           => 'utf-8',
    'format'         => 'A4',
    'orientation'    => 'P',
    'margin_left'    => 14,
    'margin_right'   => 14,
    'margin_top'     => 18,
    'margin_bottom'  => 16,
    'margin_header'  => 8,
    'margin_footer'  => 8,
    'tempDir'        => __DIR__ . '/../tmp',   // crea la carpeta tmp en tu proyecto
    'autoScriptToLang'  => true,
    'autoLangToFont'    => true,
]);

// ── Encabezado de página ──────────────────────────────────
$mpdf->SetHeader(
    '<table style="width:100%; font-size:8pt; color:#94a3b8; border-bottom:1px solid #e2e8f0; padding-bottom:4px;">
        <tr>
            <td><strong style="color:#0f172a;">DNS Pharmacy</strong> · Reporte estadístico</td>
            <td style="text-align:right;">' . htmlspecialchars($labelPeriodo) . '</td>
        </tr>
    </table>'
);

// ── Pie de página ─────────────────────────────────────────
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

// Nombre del archivo
$nombreArchivo = 'DNS_Pharmacy_Reporte_' . date('Y-m-d') . '.pdf';

$mpdf->Output($nombreArchivo, 'D');   // 'D' = fuerza descarga, 'I' = abre en navegador