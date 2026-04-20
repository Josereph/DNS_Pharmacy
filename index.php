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
?>
<!doctype html>
<html lang="es">
<head>
    <title>Dashboard - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/slider.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<?php include 'views/layouts/slider.php'; ?>

<div class="main-content">

    <div class="dash-header">
        <div>
            <h2 class="dash-title">
                Hola, <?php echo htmlspecialchars(explode(' ', $_SESSION['usuario_nombre'])[0]); ?> 👋
            </h2>
            <p class="dash-subtitle">Panel de administración · <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="dash-rol-badge">
            <i class="bi bi-shield-check"></i>
            <?php echo htmlspecialchars($_SESSION['usuario_rol']); ?>
        </div>
    </div>

    <div class="dash-stats">
        <div class="dash-stat-card purple">
            <div class="ds-icon"><i class="bi bi-receipt"></i></div>
            <div class="ds-info">
                <div class="ds-val" id="dsVentasHoy">$0.00</div>
                <div class="ds-lbl">Ventas hoy</div>
            </div>
        </div>
        <div class="dash-stat-card green">
            <div class="ds-icon"><i class="bi bi-box-seam"></i></div>
            <div class="ds-info">
                <div class="ds-val" id="dsProductos">0</div>
                <div class="ds-lbl">Productos activos</div>
            </div>
        </div>
        <div class="dash-stat-card orange">
            <div class="ds-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="ds-info">
                <div class="ds-val" id="dsStockBajo">0</div>
                <div class="ds-lbl">Stock bajo mínimo</div>
            </div>
        </div>
        <div class="dash-stat-card blue">
            <div class="ds-icon"><i class="bi bi-people"></i></div>
            <div class="ds-info">
                <div class="ds-val" id="dsUsuarios">0</div>
                <div class="ds-lbl">Usuarios activos</div>
            </div>
        </div>
    </div>

    <div class="dash-section">
        <h3 class="dash-section-title"><i class="bi bi-lightning-charge-fill"></i> Acciones rápidas</h3>
        <div class="dash-actions">
            <a href="views/pos.php" class="action-card purple">
                <i class="bi bi-display"></i>
                <span>Punto de Venta</span>
                <small>Abrir POS</small>
            </a>
            <a href="views/productos.php" class="action-card green">
                <i class="bi bi-box-seam"></i>
                <span>Productos</span>
                <small>Gestionar catálogo</small>
            </a>
            <a href="views/inventario.php" class="action-card orange">
                <i class="bi bi-clipboard2-pulse"></i>
                <span>Inventario</span>
                <small>Stock y compras</small>
            </a>
            <a href="views/historial_ventas.php" class="action-card blue">
                <i class="bi bi-clock-history"></i>
                <span>Historial</span>
                <small>Ver ventas</small>
            </a>
            <a href="views/usuarios.php" class="action-card indigo">
                <i class="bi bi-people"></i>
                <span>Usuarios</span>
                <small>Gestionar accesos</small>
            </a>
            <a href="views/proveedores.php" class="action-card teal">
                <i class="bi bi-building"></i>
                <span>Proveedores</span>
                <small>Ver proveedores</small>
            </a>
        </div>
    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/DNS_Pharmacy/controllers/ProductoController.php?accion=stats')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            document.getElementById('dsProductos').textContent = res.datos.activos;
            document.getElementById('dsStockBajo').textContent = res.datos.stock_bajo;
        });

    fetch('/DNS_Pharmacy/controllers/UsuarioController.php?accion=stats')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            document.getElementById('dsUsuarios').textContent = res.datos.activos;
        });

    var hoy = new Date().toISOString().split('T')[0];
    fetch('/DNS_Pharmacy/controllers/HistorialVentasController.php?accion=listar&desde=' + hoy + '&hasta=' + hoy)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            var total = res.datos.reduce(function(s, v) { return s + parseFloat(v.total); }, 0);
            document.getElementById('dsVentasHoy').textContent = '$' + total.toFixed(2);
        });
});
</script>
</body>
</html>