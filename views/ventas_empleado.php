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
    <title>Ventas x Empleado - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/ventas_empleado.css">

    <style>
       /* Animación  las Cards al pasar el mouse */
        .stat-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 14px 28px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.08);
        }
        /* Efecto sutil para la tabla */
        .tabla-card {
            transition: transform 0.3s ease;
        }
        .tabla-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header animate__animated animate__fadeInDown">
        <div>
            <h2 class="page-title">Ventas x Empleado</h2>
            <p class="page-subtitle">Resumen de ventas agrupadas por empleado</p>
        </div>
    </div>

    <div class="filtros-bar animate__animated animate__fadeIn animate__delay-1s">
        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Desde</label>
            <input type="date" id="filtroDesde" class="filtro-input" onchange="filtrarDatos()">
        </div>
        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Hasta</label>
            <input type="date" id="filtroHasta" class="filtro-input" onchange="filtrarDatos()">
        </div>
        <div class="filtros-accesos-rapidos">
            <button class="btn-periodo" onclick="setPeriodo('hoy')">Hoy</button>
            <button class="btn-periodo" onclick="setPeriodo('semana')">Esta semana</button>
            <button class="btn-periodo" onclick="setPeriodo('mes')">Este mes</button>
            <button class="btn-periodo" onclick="setPeriodo('todo')">Todo</button>
        </div>
    </div>

    <div class="stats-strip animate__animated animate__zoomIn animate__delay-1s">
        <div class="stat-card">
            <div class="stat-icon stat-purple"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-valor" id="statEmpleados">0</div>
                <div class="stat-label">Empleados activos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-green"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-valor" id="statTickets">0</div>
                <div class="stat-label">Total tickets</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-amber"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-valor" id="statTotal">$0.00</div>
                <div class="stat-label">Monto total vendido</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-blue"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div class="stat-valor" id="statPromedio">$0.00</div>
                <div class="stat-label">Promedio por empleado</div>
            </div>
        </div>
    </div>

    <div class="tabla-card animate__animated animate__fadeInUp animate__delay-1s">
        <table class="tabla-productos" id="tablaEmpleados">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Empleado</th>
                    <th>Rol</th>
                    <th>N° Tickets</th>
                    <th>Subtotal</th>
                    <th>Impuesto</th>
                    <th>Total vendido</th>
                    <th>Último ticket</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr>
                    <td colspan="9" class="tabla-vacia">Cargando datos...</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<div class="modal-overlay" id="modalDetalleEmpleado">
    <div class="modal-box modal-grande animate__animated animate__zoomIn animate__faster">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalDetalle">Ventas del empleado</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalDetalleEmpleado')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoDetalleEmpleado"></div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalDetalleEmpleado')">Cerrar</button>
        </div>
    </div>
</div>


<div class="modal-overlay" id="modalDetalleVenta">
    <div class="modal-box modal-mediano animate__animated animate__zoomIn animate__faster">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalVenta">Detalle de venta</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalDetalleVenta')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoDetalleVenta"></div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalDetalleVenta')">Cerrar</button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/ventas_empleado.js"></script>
</body>
</html>