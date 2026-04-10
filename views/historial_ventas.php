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
    <title>Reportes de Ventas - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

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
            box-shadow: 0 14px 28px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.08);
        }
        .tabla-card {
            transition: transform 0.3s ease;
        }
        .tabla-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .estado-completada {
            color: #28a745;
            font-weight: bold;
        }
        .estado-anulada {
            color: #dc3545;
            font-weight: bold;
        }
        .estado-pendiente {
            color: #ffc107;
            font-weight: bold;
        }
    </style>
</head>

<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header animate__animated animate__fadeInDown">
        <div>
            <h2 class="page-title">Centro de Reportes de Ventas</h2>
            <p class="page-subtitle">Gestión integral de ingresos y desempeño de empleados</p>
        </div>
    </div>

    <div class="stats-strip animate__animated animate__zoomIn animate__delay-1s">
        <div class="stat-card">
            <div>
                <div class="stat-valor" id="statTickets">0</div>
                <div class="stat-label">Total tickets</div>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <div class="stat-valor" id="statTotal">$0.00</div>
                <div class="stat-label">Total vendido</div>
            </div>
        </div>
    </div>

    <div class="filtros-bar animate__animated animate__fadeIn animate__delay-1s">
        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Desde</label>
            <input type="date" id="filtroDesde" class="filtro-input" onchange="filtrarTodo()">
        </div>
        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Hasta</label>
            <input type="date" id="filtroHasta" class="filtro-input" onchange="filtrarTodo()">
        </div>
        <div class="filtros-accesos-rapidos">
            <button class="btn-periodo" onclick="setPeriodo('hoy')">Hoy</button>
            <button class="btn-periodo" onclick="setPeriodo('semana')">Semana</button>
            <button class="btn-periodo" onclick="setPeriodo('mes')">Mes</button>
            <button class="btn-periodo" onclick="setPeriodo('todo')">Todo</button>
        </div>
    </div>

    <div class="tabla-card animate__animated animate__fadeInUp animate__delay-1s">
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ticket</th>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Subtotal</th>
                    <th>Impuesto</th>
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

<div class="modal fade" id="detalleVentaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content animate__animated animate__zoomIn animate__faster">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Detalle de Venta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalleVentaBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/historial_ventas.js"></script>

</body>
</html>