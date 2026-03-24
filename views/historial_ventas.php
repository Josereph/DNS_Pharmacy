

<?php
$base_url = '/DNS_Pharmacy';
?><!doctype html>
<html lang="es">
<head>
    <title>Historial de Ventas - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <!-- ICONOS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

   <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/slider.css">
<link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
<link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/historial_ventas.css">
</head>

<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h2 class="page-title">Historial de Ventas</h2>
            <p class="page-subtitle">Consulta general con filtro por empleado</p>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-bar">

        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Desde</label>
            <input type="date" id="filtroDesde" class="filtro-input" onchange="filtrarDatos()">
        </div>

        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Hasta</label>
            <input type="date" id="filtroHasta" class="filtro-input" onchange="filtrarDatos()">
        </div>

        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Empleado</label>
            <select id="filtroEmpleado" class="filtro-input" onchange="filtrarDatos()">
                <option value="">Todos</option>
            </select>
        </div>

        <div class="filtros-accesos-rapidos">
            <button class="btn-periodo" onclick="setPeriodo('hoy')">Hoy</button>
            <button class="btn-periodo" onclick="setPeriodo('semana')">Esta semana</button>
            <button class="btn-periodo" onclick="setPeriodo('mes')">Este mes</button>
            <button class="btn-periodo" onclick="setPeriodo('todo')">Todo</button>
        </div>

    </div>

    <!-- STATS -->
    <div class="stats-strip">
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

    <!-- TABLA -->
    <div class="tabla-card">
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
                    <td colspan="9" class="tabla-vacia">No hay ventas</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>

<!-- JS -->
<script src="../assets/js/historial_ventas.js"></script>

</body>
</html>