<!doctype html>
<html lang="es">
<head>
    <title>Estadísticas - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/reportes.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}
include 'layouts/slider.php';
?>

<div class="main-content">

    <div class="rep-header">
        <div class="rep-header-left">
            <span class="rep-badge">Dashboard analítico</span>
            <h2 class="rep-title">Estadísticas</h2>
            <p class="rep-subtitle">Indicadores, tendencias y comportamiento general del negocio</p>
        </div>

        <div class="rep-header-right">
            <div class="rep-filtro-fecha">
                <label>Período</label>
                <select id="filtroPeriodo" onchange="cambiarPeriodo(this.value)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Esta semana</option>
                    <option value="mes" selected>Este mes</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Este año</option>
                    <option value="custom">Personalizado</option>
                </select>
            </div>

            <div class="rep-filtro-custom" id="filtroCustom" style="display:none;">
                <input type="date" id="fechaDesde" class="fecha-input">
                <span>—</span>
                <input type="date" id="fechaHasta" class="fecha-input">
                <button class="btn-aplicar" onclick="aplicarFiltroCustom()">Aplicar</button>
            </div>

            <a href="ReportesPDF.php" class="btn-reportes-real">
                <i class="bi bi-folder2-open"></i> Ir a reportes
            </a>
        </div>
    </div>

    <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiVentas">—</div>
                <div class="kpi-lbl">Ventas realizadas</div>
                <div class="kpi-delta" id="kpiVentasDelta"></div>
            </div>
        </div>

        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiIngresos">—</div>
                <div class="kpi-lbl">Ingresos totales</div>
                <div class="kpi-delta" id="kpiIngresosDelta"></div>
            </div>
        </div>

       

        <div class="kpi-card kpi-purple">
            <div class="kpi-icon"><i class="bi bi-boxes"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiUnidades">—</div>
                <div class="kpi-lbl">Unidades vendidas</div>
                <div class="kpi-delta" id="kpiUnidadesDelta"></div>
            </div>
        </div>

        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiStockBajo">—</div>
                <div class="kpi-lbl">Productos bajo mínimo</div>
                <div class="kpi-delta kpi-delta-neutral" id="kpiStockBajoDelta">Ver detalle</div>
            </div>
        </div>

        <div class="kpi-card kpi-teal">
            <div class="kpi-icon"><i class="bi bi-cart-check"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiCompras">—</div>
                <div class="kpi-lbl">Compras registradas</div>
                <div class="kpi-delta" id="kpiComprasDelta"></div>
            </div>
        </div>
    </div>

    <div class="rep-tabs">
        <button class="rep-tab active" onclick="cambiarTab(this,'tabVentas')">
            <i class="bi bi-bar-chart-line"></i> Ventas
        </button>
        <button class="rep-tab" onclick="cambiarTab(this,'tabInventario')">
            <i class="bi bi-boxes"></i> Inventario
        </button>
        <button class="rep-tab" onclick="cambiarTab(this,'tabEmpleados')">
            <i class="bi bi-people"></i> Empleados
        </button>
        <button class="rep-tab" onclick="cambiarTab(this,'tabProveedores')">
            <i class="bi bi-building"></i> Proveedores
        </button>
        <button class="rep-tab" onclick="cambiarTab(this,'tabProductos')">
            <i class="bi bi-box-seam"></i> Productos
        </button>
    </div>

    <!-- TAB VENTAS -->
    <div id="tabVentas" class="rep-tab-content active-tab">
        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-bar-chart"></i> Ingresos en el período</span>
                    <div class="chart-toggle">
                        <button class="ct-btn active" onclick="toggleChartType('chartVentasTiempo','bar',this)">Barras</button>
                        <button class="ct-btn" onclick="toggleChartType('chartVentasTiempo','line',this)">Línea</button>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartVentasTiempo"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-pie-chart"></i> Método de pago</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartMetodoPago"></canvas>
                </div>
                <div class="chart-legend" id="legendMetodoPago"></div>
            </div>
        </div>

        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-clock"></i> Ventas por hora del día</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartHoras"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-tags"></i> Ingresos por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartCategorias"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card">
            <div class="rep-table-header">
                <span class="chart-title"><i class="bi bi-list-ol"></i> Detalle de ventas recientes</span>
                <input type="text" id="buscadorVentas" class="rep-search" placeholder="Buscar ticket..." oninput="filtrarTabla('tablaVentas', this.value)">
            </div>
            <table class="rep-table" id="tablaVentas">
                <thead>
                    <tr>
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
                <tbody id="cuerpoTablaVentas">
                    <tr><td colspan="9" class="tabla-vacia">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB INVENTARIO -->
    <div id="tabInventario" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-pie-chart"></i> Estado del stock</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartEstadoStock"></canvas>
                </div>
                <div class="chart-legend" id="legendEstadoStock"></div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-bar-chart-steps"></i> Unidades en stock por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartStockCategoria"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-arrow-left-right"></i> Movimientos de inventario</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartMovimientos"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-currency-dollar"></i> Inversión por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartInversionCategoria"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card rep-alerta-table">
            <div class="rep-table-header">
                <span class="chart-title"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Productos con stock bajo o agotado</span>
            </div>
            <table class="rep-table" id="tablaStockBajo">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock actual</th>
                        <th>Stock mínimo</th>
                        <th>P. Compra</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaStockBajo">
                    <tr><td colspan="7" class="tabla-vacia">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB EMPLEADOS -->
    <div id="tabEmpleados" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-person-lines-fill"></i> Ventas por empleado</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartVentasEmpleado"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-receipt"></i> Tickets emitidos por empleado</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartTicketsEmpleado"></canvas>
                </div>
                <div class="chart-legend" id="legendTicketsEmpleado"></div>
            </div>
        </div>

        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-clock-history"></i> Turnos trabajados</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTurnos"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-graph-up"></i> Ticket promedio por empleado</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTicketPromEmpleado"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card">
            <div class="rep-table-header">
                <span class="chart-title"><i class="bi bi-trophy"></i> Ranking de empleados</span>
            </div>
            <table class="rep-table" id="tablaEmpleados">
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
                <tbody id="cuerpoTablaEmpleados">
                    <tr><td colspan="8" class="tabla-vacia">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB PROVEEDORES -->
    <div id="tabProveedores" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-building"></i> Monto de compras por proveedor</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartComprasProveedor"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-pie-chart"></i> Participación por proveedor</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartParticipacionProveedor"></canvas>
                </div>
                <div class="chart-legend" id="legendParticipacionProveedor"></div>
            </div>
        </div>

        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-calendar3"></i> Evolución de compras</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartComprasTiempo"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-arrow-down-circle"></i> Top productos reabastecidos</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTopReabastecidos"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card">
            <div class="rep-table-header">
                <span class="chart-title"><i class="bi bi-table"></i> Historial de compras del período</span>
                <input type="text" id="buscadorCompras" class="rep-search" placeholder="Buscar proveedor..." oninput="filtrarTabla('tablaComprasRep', this.value)">
            </div>
            <table class="rep-table" id="tablaComprasRep">
                <thead>
                    <tr>
                        <th>N° Factura</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaComprasRep">
                    <tr><td colspan="8" class="tabla-vacia">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB PRODUCTOS -->
    <div id="tabProductos" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-trophy-fill"></i> Top 10 productos más vendidos</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTopVendidos"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-percent"></i> Margen por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartMargen"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-grid-2">
            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-slash-circle"></i> Productos sin ventas en el período</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartSinMovimiento"></canvas>
                </div>
                <div class="chart-legend" id="legendSinMovimiento"></div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-currency-dollar"></i> Ingresos vs costos por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartRentabilidad"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card">
            <div class="rep-table-header">
                <span class="chart-title"><i class="bi bi-list-stars"></i> Ranking de productos</span>
                <input type="text" id="buscadorProductos" class="rep-search" placeholder="Buscar producto..." oninput="filtrarTabla('tablaProductosRep', this.value)">
            </div>
            <table class="rep-table" id="tablaProductosRep">
                <thead>
                    <tr>
                        <th>Pos.</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Unidades vendidas</th>
                        <th>Ingresos</th>
                        <th>Costo total</th>
                        <th>Margen %</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaProductosRep">
                    <tr><td colspan="7" class="tabla-vacia">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="../assets/js/reportes.js"></script>
</body>
</html>