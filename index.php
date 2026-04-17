<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}
// SOLO ADMIN puede entrar
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/slider.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<?php include 'views/layouts/slider.php'; ?>

<div class="main-content">

    <!-- Bienvenida estilo sistema -->
    <div class="dashboard-header">
        <h2 class="dashboard-title">
            Bienvenida, <?php echo $_SESSION['usuario_nombre']; ?> 👋
        </h2>
        <p class="dashboard-subtitle">
            Panel principal del sistema DNS Pharmacy
        </p>
    </div>

    <!-- Tarjetas estilo moderno -->
    <div class="dashboard-cards">

        <div class="dashboard-card">
            <div class="card-icon purple"><i class="bi bi-receipt"></i></div>
            <div>
                <h4>Ventas hoy</h4>
                <p class="card-value">$0.00</p>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-icon green"><i class="bi bi-cash"></i></div>
            <div>
                <h4>Ingresos</h4>
                <p class="card-value">$0.00</p>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-icon orange"><i class="bi bi-box"></i></div>
            <div>
                <h4>Productos</h4>
                <p class="card-value">--</p>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <h4>Stock bajo</h4>
                <p class="card-value">--</p>
            </div>
        </div>

    </div>

    <!-- Acciones rápidas (adaptadas por rol) -->
    <div class="dashboard-actions">

        <h3>Acciones rápidas</h3>

        <div class="actions-grid">

            <!-- TODOS -->
            <a href="views/pos.php" class="action-card">
                <i class="bi bi-display"></i>
                <span>Ir al POS</span>
            </a>

            <a href="views/perfil.php" class="action-card">
                <i class="bi bi-person"></i>
                <span>Mi perfil</span>
            </a>

            <!-- SOLO ADMIN -->
            <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>

            <a href="views/productos.php" class="action-card">
                <i class="bi bi-box-seam"></i>
                <span>Productos</span>
            </a>

            <a href="views/reportes.php" class="action-card">
                <i class="bi bi-bar-chart"></i>
                <span>Reportes</span>
            </a>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>

</body>
</html>