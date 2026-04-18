<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

if ($_SESSION['usuario_rol'] !== 'Administrador') {
    header('Location: /DNS_Pharmacy/views/pos.php');
    exit;
}

$admin_nombre = $_SESSION['usuario_nombre'] ?? 'Admin';
?>
<!doctype html>
<html lang="es">
<head>
    <title>Inicio - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/slider.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<?php include 'views/layouts/slider.php'; ?>

<div class="main-content">

    <!-- Bienvenida -->
    <div class="dash-welcome">
        <div class="dash-welcome-left">
            <div class="dash-greeting">Bienvenido de vuelta,</div>
            <div class="dash-name"><?php echo htmlspecialchars($admin_nombre); ?> 👋</div>
            <div class="dash-sub">Panel de administración — DNS Pharmacy</div>
        </div>
        <div class="dash-welcome-right">
            <div class="dash-fecha-dia">Hoy es</div>
            <div class="dash-fecha-hora" id="dashHora">--:--</div>
            <div class="dash-fecha-completa" id="dashFecha">cargando...</div>
        </div>
    </div>

    <!-- Banner farmacéutico -->
    <div class="dash-banner">
        <div class="dash-banner-icon"><i class="bi bi-capsule"></i></div>
        <div class="dash-banner-text">
            <h4>DNS Pharmacy — Drug Network Supply</h4>
            <p>Sistema de gestión farmacéutica · San Salvador, El Salvador · contacto@dnspharmacy.com</p>
        </div>
        <div class="dash-banner-badge"><i class="bi bi-shield-check"></i> Sistema activo</div>
    </div>

    <!-- Stats -->
    <div class="dash-stats-grid">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-purple"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="dash-stat-val" id="statVentasHoy">—</div>
                <div class="dash-stat-label">Ventas hoy</div>
                <div class="dash-stat-sub" id="statVentasSub">$0.00 recaudado</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-green"><i class="bi bi-boxes"></i></div>
            <div>
                <div class="dash-stat-val" id="statProductos">—</div>
                <div class="dash-stat-label">Productos activos</div>
                <div class="dash-stat-sub" id="statProductosSub">en catálogo</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-amber"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="dash-stat-val" id="statStockBajo">—</div>
                <div class="dash-stat-label">Stock bajo</div>
                <div class="dash-stat-sub">requieren atención</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-blue"><i class="bi bi-people"></i></div>
            <div>
                <div class="dash-stat-val" id="statUsuarios">—</div>
                <div class="dash-stat-label">Usuarios activos</div>
                <div class="dash-stat-sub">en el sistema</div>
            </div>
        </div>
    </div>

    <!-- Ventas recientes + Stock bajo -->
    <div class="dash-row">

        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-clock-history"></i> Ventas recientes</div>
                <a href="views/historial_ventas.php" class="dash-card-link">Ver todas <i class="bi bi-arrow-right"></i></a>
            </div>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Empleado</th>
                        <th>Total</th>
                        <th>Método</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody id="dashVentasRecientes">
                    <tr><td colspan="5" class="dash-table-empty">Cargando...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-exclamation-circle"></i> Productos con stock bajo</div>
                <a href="views/inventario.php" class="dash-card-link">Ver inventario <i class="bi bi-arrow-right"></i></a>
            </div>
            <div id="dashStockBajo">
                <div class="dash-empty">Cargando...</div>
            </div>
        </div>

    </div>

    <!-- Compras recientes -->
    <div class="dash-row-full">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-cart3"></i> Compras recientes</div>
                <a href="views/compras.php" class="dash-card-link">Ver todas <i class="bi bi-arrow-right"></i></a>
            </div>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>N° Documento</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="dashComprasRecientes">
                    <tr><td colspan="5" class="dash-table-empty">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="assets/js/index.js"></script>
</body>
</html>