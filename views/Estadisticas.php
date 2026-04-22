<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

$base_url = '/DNS_Pharmacy';
$views = $base_url . '/views';
include 'layouts/slider.php';
?>
<!doctype html>
<html lang="es">
<head>
    <title>Estadísticas - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/reportes.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        /* Animación para las KPI Cards al pasar el mouse */
        .kpi-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
        }
        .kpi-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 14px 28px rgba(0,0,0,0.1);
        }
        /* Efecto sutil para las tablas */
        .rep-table-card {
            transition: transform 0.3s ease;
        }
        .rep-table-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        /* Animación para los tabs */
        .rep-tab {
            transition: all 0.3s ease;
        }
        .rep-tab:hover {
            transform: translateY(-2px);
        }
        /* Animación para las charts */
        .chart-card {
            transition: all 0.3s ease;
        }
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="main-content">

    <div class="rep-header animate__animated animate__fadeInDown">
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
        <div class="kpi-card kpi-blue animate__animated animate__zoomIn animate__delay-1s">
            <div class="kpi-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiVentas">—</div>
                <div class="kpi-lbl">Ventas realizadas</div>
                <div class="kpi-delta" id="kpiVentasDelta"></div>
            </div>
        </div>

        <div class="kpi-card kpi-green animate__animated animate__zoomIn animate__delay-2s">
            <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiIngresos">—</div>
                <div class="kpi-lbl">Ingresos totales</div>
                <div class="kpi-delta" id="kpiIngresosDelta"></div>
            </div>
        </div>

        <div class="kpi-card kpi-purple animate__animated animate__zoomIn animate__delay-3s">
            <div class="kpi-icon"><i class="bi bi-boxes"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiUnidades">—</div>
                <div class="kpi-lbl">Unidades vendidas</div>
                <div class="kpi-delta" id="kpiUnidadesDelta"></div>
            </div>
        </div>

        <div class="kpi-card kpi-red animate__animated animate__zoomIn animate__delay-4s">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiStockBajo">—</div>
                <div class="kpi-lbl">Productos bajo mínimo</div>
                <div class="kpi-delta kpi-delta-neutral" id="kpiStockBajoDelta">Ver detalle</div>
            </div>
        </div>

        <div class="kpi-card kpi-teal animate__animated animate__zoomIn animate__delay-5s">
            <div class="kpi-icon"><i class="bi bi-cart-check"></i></div>
            <div class="kpi-body">
                <div class="kpi-val" id="kpiCompras">—</div>
                <div class="kpi-lbl">Compras registradas</div>
                <div class="kpi-delta" id="kpiComprasDelta"></div>
            </div>
        </div>
    </div>

    <div class="rep-tabs animate__animated animate__fadeIn animate__delay-1s">
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
        <button class="rep-tab" onclick="cambiarTab(this,'tabFinanciero')">
            <i class="bi bi-cash-coin"></i> Financiero
        </button>
        <button class="rep-tab" onclick="cambiarTab(this,'tabVencimientos')">
            <i class="bi bi-calendar-x"></i> Vencimientos
        </button>
    </div>

    <!-- TAB VENTAS -->
    <div id="tabVentas" class="rep-tab-content active-tab">
        <div class="charts-grid-2">
            <div class="chart-card animate__animated animate__fadeInUp animate__delay-1s">
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

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-2s">
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
            <div class="chart-card animate__animated animate__fadeInUp animate__delay-3s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-clock"></i> Ventas por hora del día</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartHoras"></canvas>
                </div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-4s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-tags"></i> Ingresos por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartCategorias"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card animate__animated animate__fadeInUp animate__delay-5s">
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
                    <tr><td colspan="9" class="tabla-vacia">Cargando...<\/td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB INVENTARIO -->
    <div id="tabInventario" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card animate__animated animate__fadeInUp">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-pie-chart"></i> Estado del stock</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartEstadoStock"></canvas>
                </div>
                <div class="chart-legend" id="legendEstadoStock"></div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-1s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-bar-chart-steps"></i> Unidades en stock por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartStockCategoria"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-grid-2">
            <div class="chart-card animate__animated animate__fadeInUp animate__delay-2s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-arrow-left-right"></i> Movimientos de inventario</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartMovimientos"></canvas>
                </div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-3s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-currency-dollar"></i> Inversión por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartInversionCategoria"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card rep-alerta-table animate__animated animate__fadeInUp animate__delay-4s">
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
                    <tr><td colspan="7" class="tabla-vacia">Cargando...<\/td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB EMPLEADOS -->
    <div id="tabEmpleados" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card animate__animated animate__fadeInUp">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-person-lines-fill"></i> Ventas por empleado</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartVentasEmpleado"></canvas>
                </div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-1s">
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
            <div class="chart-card animate__animated animate__fadeInUp animate__delay-2s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-clock-history"></i> Turnos trabajados</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTurnos"></canvas>
                </div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-3s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-graph-up"></i> Ticket promedio por empleado</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTicketPromEmpleado"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card animate__animated animate__fadeInUp animate__delay-4s">
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
                    <tr><td colspan="8" class="tabla-vacia">Cargando...<\/td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB PROVEEDORES -->
    <div id="tabProveedores" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card animate__animated animate__fadeInUp">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-building"></i> Monto de compras por proveedor</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartComprasProveedor"></canvas>
                </div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-1s">
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
            <div class="chart-card animate__animated animate__fadeInUp animate__delay-2s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-calendar3"></i> Evolución de compras</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartComprasTiempo"></canvas>
                </div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-3s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-arrow-down-circle"></i> Top productos reabastecidos</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTopReabastecidos"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card animate__animated animate__fadeInUp animate__delay-4s">
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
                    <tr><td colspan="8" class="tabla-vacia">Cargando...<\/td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB PRODUCTOS -->
    <div id="tabProductos" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card animate__animated animate__fadeInUp">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-trophy-fill"></i> Top 10 productos más vendidos</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartTopVendidos"></canvas>
                </div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-1s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-percent"></i> Margen por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartMargen"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-grid-2">
            <div class="chart-card animate__animated animate__fadeInUp animate__delay-2s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-slash-circle"></i> Productos sin ventas en el período</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartSinMovimiento"></canvas>
                </div>
                <div class="chart-legend" id="legendSinMovimiento"></div>
            </div>

            <div class="chart-card animate__animated animate__fadeInUp animate__delay-3s">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-currency-dollar"></i> Ingresos vs costos por categoría</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartRentabilidad"></canvas>
                </div>
            </div>
        </div>

        <div class="rep-table-card animate__animated animate__fadeInUp animate__delay-4s">
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
                    <tr><td colspan="7" class="tabla-vacia">Cargando...<\/td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB FINANCIERO -->
    <div id="tabFinanciero" class="rep-tab-content">
        <div class="charts-grid-2">
            <div class="chart-card chart-wide animate__animated animate__fadeInUp">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-activity"></i> Flujo de Caja (Ventas vs Compras)</span>
                    <div class="chart-toggle">
                        <button class="ct-btn active" onclick="toggleChartType('chartFlujoCaja','bar',this)">Barras</button>
                        <button class="ct-btn" onclick="toggleChartType('chartFlujoCaja','line',this)">Línea</button>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartFlujoCaja"></canvas>
                </div>
            </div>
        </div>
        
        <div class="kpi-grid">
            <div class="kpi-card kpi-purple animate__animated animate__zoomIn animate__delay-1s">
                <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="kpi-body">
                    <div class="kpi-val" id="kpiUtilidadIngresos">—</div>
                    <div class="kpi-lbl">Ingresos Totales (Ventas)</div>
                </div>
            </div>
            <div class="kpi-card kpi-orange animate__animated animate__zoomIn animate__delay-2s">
                <div class="kpi-icon"><i class="bi bi-graph-down-arrow"></i></div>
                <div class="kpi-body">
                    <div class="kpi-val" id="kpiUtilidadCostos">—</div>
                    <div class="kpi-lbl">Costo de Ventas (COGS)</div>
                </div>
            </div>
            <div class="kpi-card kpi-green animate__animated animate__zoomIn animate__delay-3s">
                <div class="kpi-icon"><i class="bi bi-piggy-bank"></i></div>
                <div class="kpi-body">
                    <div class="kpi-val" id="kpiUtilidadNeta">—</div>
                    <div class="kpi-lbl">Utilidad Bruta</div>
                </div>
            </div>
            <div class="kpi-card kpi-blue animate__animated animate__zoomIn animate__delay-4s">
                <div class="kpi-icon"><i class="bi bi-percent"></i></div>
                <div class="kpi-body">
                    <div class="kpi-val" id="kpiUtilidadMargen">—</div>
                    <div class="kpi-lbl">Margen Promedio</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB VENCIMIENTOS -->
    <div id="tabVencimientos" class="rep-tab-content">
        <div class="charts-grid-3">
            <div class="chart-card chart-wide-2 animate__animated animate__fadeInUp">
                <div class="chart-card-header">
                    <span class="chart-title"><i class="bi bi-shield-exclamation"></i> Estado Global de Lotes</span>
                </div>
                <div class="chart-wrap chart-wrap-donut">
                    <canvas id="chartEstadoLotes"></canvas>
                </div>
                <div class="chart-legend" id="legendEstadoLotes"></div>
            </div>

            <div class="rep-table-card rep-error-table animate__animated animate__fadeInUp animate__delay-1s" style="margin-bottom:0; height:100%;">
                <div class="rep-table-header">
                    <span class="chart-title"><i class="bi bi-exclamation-triangle"></i> Lotes Críticos (Vencidos o próximos)</span>
                    <input type="text" id="buscadorLotes" class="rep-search" placeholder="Buscar producto o lote..." oninput="filtrarTabla('tablaLotes', this.value)">
                </div>
                <div class="rep-table-wrapper" style="max-height: 350px; overflow-y: auto;">
                    <table class="rep-table" id="tablaLotes">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Producto</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Restantes</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaLotes">
                            <tr><td colspan="6" class="tabla-vacia">Cargando...<\/td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="../assets/js/reportes.js"></script>
</body>
</html>