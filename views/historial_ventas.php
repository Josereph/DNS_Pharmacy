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
    <title>Reportes de Ventas - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<<<<<<< HEAD

=======
>>>>>>> 2c75a12b16f68754376e3a415198b6ece8e71a6d
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/slider.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/historial_ventas.css">
    
    <style>
<<<<<<< HEAD
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
=======
        .nav-tabs .nav-link.active {
            background-color: #f8f9fa;
            font-weight: bold;
            border-bottom: 3px solid #841480; 
            color: #841480 !important;
        }
        .nav-link { color: #666; }
        .tab-content { padding-top: 20px; }
        .main-content { padding: 20px; background: #fdfaff; min-height: 100vh; }
>>>>>>> 2c75a12b16f68754376e3a415198b6ece8e71a6d
    </style>
</head>

<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

<<<<<<< HEAD
    <div class="page-header animate__animated animate__fadeInDown">
=======
    <div class="page-header">
>>>>>>> 2c75a12b16f68754376e3a415198b6ece8e71a6d
        <div>
            <h2 class="page-title">Centro de Reportes de Ventas</h2>
            <p class="page-subtitle">Gestión integral de ingresos y desempeño de empleados</p>
        </div>
    </div>

<<<<<<< HEAD
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
=======
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                <i class="bi bi-list-ul"></i> Historial General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="empleados-tab" data-toggle="tab" href="#empleados" role="tab">
                <i class="bi bi-people"></i> Ventas por Empleado
            </a>
        </li>
    </ul>
>>>>>>> 2c75a12b16f68754376e3a415198b6ece8e71a6d

    <div class="filtros-bar mt-3">
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

<<<<<<< HEAD
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
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody id="cuerpoTabla">
                <tr>
                    <td colspan="9" class="tabla-vacia">Cargando ventas...</td>
                </tr>
            </tbody>
        </table>
    </div>

=======
    <div class="tab-content" id="myTabContent">
        
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="stats-strip">
                <div class="stat-card">
                    <div class="stat-icon stat-purple"><i class="bi bi-ticket-perforated"></i></div>
                    <div>
                        <div class="stat-valor" id="statTicketsGeneral">0</div>
                        <div class="stat-label">Total tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-green"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="stat-valor" id="statTotalGeneral">$0.00</div>
                        <div class="stat-label">Total vendido</div>
                    </div>
                </div>
            </div>

            <div class="tabla-card">
                <table class="tabla-productos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>TICKET</th>
                            <th>EMPLEADO</th>
                            <th>FECHA</th>
                            <th>SUBTOTAL</th>
                            <th>IMPUESTO</th>
                            <th>TOTAL</th>
                            <th>MÉTODO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaGeneral">
                        <tr><td colspan="9" class="text-center">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="empleados" role="tabpanel">
            <div class="stats-strip">
                <div class="stat-card">
                    <div class="stat-icon stat-purple"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-valor" id="statEmpleadosActivos">0</div>
                        <div class="stat-label">Empleados activos</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-green"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="stat-valor" id="statTotalVentasEmp">$0.00</div>
                        <div class="stat-label">Monto total vendido</div>
                    </div>
                </div>
            </div>

            <div class="tabla-card">
                <table class="tabla-productos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>EMPLEADO</th>
                            <th>ROL</th>
                            <th class="text-center">N° TICKETS</th>
                            <th class="text-center">TOTAL VENDIDO</th>
                            <th>ÚLTIMO TICKET</th>
                            <th class="text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaEmpleados">
                        <tr><td colspan="7" class="text-center">No hay datos de empleados</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
>>>>>>> 2c75a12b16f68754376e3a415198b6ece8e71a6d
</div>

<?php include 'layouts/footer.php'; ?>

<<<<<<< HEAD
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

=======
>>>>>>> 2c75a12b16f68754376e3a415198b6ece8e71a6d
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/historial_ventas.js"></script>

</body>
</html>