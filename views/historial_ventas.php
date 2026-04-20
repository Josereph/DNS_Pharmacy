<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

$base_url = '/DNS_Pharmacy';

// Incluir conexión a la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/DNS_Pharmacy/config/database.php';

// Verificar conexión
$conn = conectar();
if (!$conn) {
    die("Error: No se pudo conectar a la base de datos");
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Ventas - DNS Pharmacy</title>

    <!-- Bootstrap / Iconos -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/slider.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/historial_ventas.css">

    <style>
        .stat-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 14px 28px rgba(0,0,0,0.10), 0 10px 10px rgba(0,0,0,0.08);
        }

        .tabla-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tabla-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

    <?php include 'layouts/slider.php'; ?>

    <div class="main-content">

        <!-- Encabezado -->
        <div class="page-header animate__animated animate__fadeInDown">
            <div>
                <h2 class="page-title">Historial de Ventas</h2>
                <p class="page-subtitle">Consulta y seguimiento de todas las ventas registradas</p>
            </div>
        </div>

        <!-- Tarjeta principal -->
        <div class="stats-strip slide-up mb-4">
            <div class="stat-card" style="--delay: 0s">
                <div class="stat-icon stat-purple">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h2 class="page-title mb-1">Centro de Reportes de Ventas</h2>
                    <p class="page-subtitle mb-0">Gestión integral de ingresos y desempeño de empleados</p>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-strip animate__animated animate__zoomIn animate__delay-1s mb-4">
            <div class="stat-card" style="--delay: 0.08s">
                <div class="stat-icon stat-purple">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div>
                    <div class="stat-valor" id="statTickets">0</div>
                    <div class="stat-label">Total tickets</div>
                </div>
            </div>

            <div class="stat-card" style="--delay: 0.16s">
                <div class="stat-icon stat-green">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-valor" id="statTotal">$0.00</div>
                    <div class="stat-label">Total vendido</div>
                </div>
            </div>

            <div class="stat-card" style="--delay: 0.24s">
                <div class="stat-icon stat-amber">
                    <i class="bi bi-percent"></i>
                </div>
                <div>
                    <div class="stat-valor" id="statIva">$0.00</div>
                    <div class="stat-label">Total IVA</div>
                </div>
            </div>

            <div class="stat-card" style="--delay: 0.32s">
                <div class="stat-icon stat-blue">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="stat-valor" id="statPromedio">$0.00</div>
                    <div class="stat-label">Ticket promedio</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-bar mt-3 animate__animated animate__fadeIn animate__delay-1s mb-4">
            <div class="filtro-fecha-wrap">
                <label class="filtro-label">Desde</label>
                <input type="date" id="filtroDesde" class="filtro-input" onchange="filtrarDatos()">
            </div>

            <div class="filtro-fecha-wrap">
                <label class="filtro-label">Hasta</label>
                <input type="date" id="filtroHasta" class="filtro-input" onchange="filtrarDatos()">
            </div>

            <div class="filtro-fecha-wrap">
                <label class="filtro-label">Método de pago</label>
                <select id="filtroMetodo" class="filtro-input" onchange="filtrarDatos()">
                    <option value="">Todos</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Digital</option>
                </select>
            </div>

            <div class="filtros-accesos-rapidos">
                <button class="btn-periodo" onclick="setPeriodo('hoy')">Hoy</button>
                <button class="btn-periodo" onclick="setPeriodo('semana')">Esta semana</button>
                <button class="btn-periodo" onclick="setPeriodo('mes')">Este mes</button>
                <button class="btn-periodo activo" onclick="setPeriodo('todo')">Todo</button>
            </div>
        </div>

        <!-- Tabla -->
        <div class="tabla-card animate__animated animate__fadeInUp animate__delay-1s">
            <div class="tabla-header-bar">
                <span>Mostrando <strong id="contadorVisible">0</strong> de <strong id="contadorTotal">0</strong> ventas</span>
                <span>DNS Pharmacy · Historial</span>
            </div>

            <table class="tabla-ventas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ticket</th>
                        <th>Empleado</th>
                        <th>Fecha</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla">
                    <tr>
                        <td colspan="10" class="tabla-vacia">Cargando ventas...</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

    <!-- Modal detalle de venta -->
    <div class="modal fade" id="detalleVentaModal" tabindex="-1" role="dialog" aria-labelledby="detalleVentaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content animate__animated animate__zoomIn animate__faster">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleVentaModalLabel">
                        <i class="bi bi-receipt"></i> Detalle de Venta
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" id="detalleVentaBody">
                    <!-- Contenido dinámico -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/historial_ventas.js"></script>

</body>
</html>