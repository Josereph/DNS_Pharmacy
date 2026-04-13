<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}
$base_url = '/DNS_Pharmacy';
?>
<!doctype html>
<html lang="es">
<head>
    <title>Historial de Ventas - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/slider.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/historial_ventas.css">
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <!-- Header -->
    <div class="page-header fade-in">
        <div>
            <h2 class="page-title">Historial de Ventas</h2>
            <p class="page-subtitle">Consulta y seguimiento de todas las ventas registradas</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-strip slide-up">
        <div class="stat-card" style="--delay: 0s">
            <div class="stat-icon stat-purple">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="stat-valor" id="statTickets">0</div>
                <div class="stat-label">Total tickets</div>
            </div>
        </div>
        <div class="stat-card" style="--delay: 0.08s">
            <div class="stat-icon stat-green">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-valor" id="statTotal">$0.00</div>
                <div class="stat-label">Total vendido</div>
            </div>
        </div>
        <div class="stat-card" style="--delay: 0.16s">
            <div class="stat-icon stat-amber">
                <i class="bi bi-percent"></i>
            </div>
            <div>
                <div class="stat-valor" id="statIva">$0.00</div>
                <div class="stat-label">Total IVA</div>
            </div>
        </div>
        <div class="stat-card" style="--delay: 0.24s">
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
    <div class="filtros-bar slide-up" style="--delay: 0.1s">
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
    <div class="tabla-card slide-up" style="--delay: 0.2s">
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
                <tr><td colspan="10" class="tabla-vacia">Cargando ventas...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<!-- MODAL: DETALLE VENTA -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo"><i class="bi bi-receipt"></i> Detalle de Venta</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalDetalle')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoDetalle">Cargando...</div>
        <div class="modal-footer-custom">
            <button class="btn-cancelar" onclick="cerrarModal('modalDetalle')">Cerrar</button>
            <button class="btn-imprimir" onclick="imprimirDetalle()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/historial_ventas.js"></script>
</body>
</html>