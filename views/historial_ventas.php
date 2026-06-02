<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/Login.php');
    exit;
}

$base_url      = '';
$usuario_rol   = $_SESSION['usuario_rol'] ?? '';

// Incluir conexión a la base de datos
require_once __DIR__ . '/../config/database.php';

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

            <div style="margin-left: auto;">
                <button class="btn btn-danger d-flex align-items-center" onclick="generarPDFHistorial()" style="font-size: 13px; font-weight: 600; border-radius: 6px; padding: 8px 16px; gap: 6px; transition: all 0.2s;">
                    <i class="bi bi-filetype-pdf" style="font-size: 16px;"></i> Exportar PDF
                </button>
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
    <div class="modal-overlay" id="modalDetalle">
        <div class="modal-box modal-mediano">
            <div class="modal-header">
                <h5 class="modal-titulo">
                    <i class="bi bi-receipt"></i> Detalle de Venta
                </h5>
                <button class="modal-cerrar" onclick="cerrarModal('modalDetalle')">&times;</button>
            </div>
            <div class="modal-body" id="cuerpoDetalle">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalDetalle')">Cerrar</button>
                <button type="button" class="btn-imprimir" onclick="imprimirDetalle()"><i class="bi bi-printer"></i> Imprimir</button>
                <button type="button" class="btn-anular" id="btnAnularVenta" onclick="solicitarAnulacion()" style="display:none">
                    <i class="bi bi-x-circle"></i> Anular Venta
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        window.USUARIO_ROL = '<?php echo htmlspecialchars($usuario_rol); ?>';
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/historial_ventas.js"></script>

    <!-- Modal confirmación de anulación -->
    <div class="modal-overlay" id="modalAnular">
        <div class="modal-box" style="max-width:460px">
            <div class="modal-header" style="background:linear-gradient(135deg,#dc3545,#c82333)">
                <h5 class="modal-titulo"><i class="bi bi-exclamation-triangle-fill"></i> Confirmar Anulación</h5>
                <button class="modal-cerrar" onclick="cerrarModal('modalAnular')">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align:center;padding:12px 0 8px">
                    <i class="bi bi-x-octagon" style="font-size:48px;color:#dc3545"></i>
                    <p style="margin:16px 0 6px;font-size:15px;font-weight:600;color:#333">
                        ¿Anular el ticket <strong id="txtTicketAnular"></strong>?
                    </p>
                    <p style="font-size:13px;color:#666;margin-bottom:16px">
                        Esta acción devolverá los productos al inventario y marcará la venta como <strong>Anulada</strong>. No se puede deshacer.
                    </p>
                    <div style="margin-bottom:12px;text-align:left">
                        <label style="font-size:13px;font-weight:600;color:#555;display:block;margin-bottom:6px">Motivo (opcional)</label>
                        <input type="text" id="motivoAnulacion" maxlength="255"
                               placeholder="Ej: Error en productos ingresados"
                               style="width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
                    </div>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalAnular')">Cancelar</button>
                <button type="button" id="btnConfirmarAnulacion" class="btn-anular" onclick="ejecutarAnulacion()">
                    <i class="bi bi-x-circle"></i> Sí, anular venta
                </button>
            </div>
        </div>
    </div>

</body>
</html>