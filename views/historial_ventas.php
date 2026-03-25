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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/slider.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/historial_ventas.css">
    
    <style>
        .nav-tabs .nav-link.active {
            background-color: #f8f9fa;
            font-weight: bold;
            border-bottom: 3px solid #841480; 
            color: #841480 !important;
        }
        .nav-link { color: #666; }
        .tab-content { padding-top: 20px; }
        .main-content { padding: 20px; background: #fdfaff; min-height: 100vh; }
    </style>
</head>

<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Centro de Reportes de Ventas</h2>
            <p class="page-subtitle">Gestión integral de ingresos y desempeño de empleados</p>
        </div>
    </div>

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
</div>

<?php include 'layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/historial_ventas.js"></script>

</body>
</html>