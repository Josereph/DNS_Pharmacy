<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$base_url = '/DNS_Pharmacy';
$views = $base_url . '/views';

$admin_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$admin_rol    = $_SESSION['usuario_rol']    ?? 'Sin rol';
$admin_ini    = strtoupper(substr($admin_nombre, 0, 2));

$rol = $_SESSION['usuario_rol'] ?? '';

// En el POS el sidebar arranca oculto
$es_pos = ($pagina_actual === 'pos.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Botón toggle siempre visible -->
<button class="sidebar-toggle-btn" id="sidebarToggle" title="Mostrar / ocultar menú">
    <i class="bi bi-list" id="toggleIcon"></i>
</button>

<div class="gym-sidebar <?php echo $es_pos ? 'sidebar-hidden' : ''; ?>" id="gymSidebar">

    <div class="logo-area">
        <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" alt="DNS Pharmacy" style="height:105px;">
    </div>

    <nav class="nav flex-column">

        <?php if ($rol === 'Administrador'): ?>
<a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>"
   href="<?php echo $base_url; ?>/index.php">
    <i class="bi bi-house-door"></i>
    Inicio
</a>
<?php endif; ?>

        <?php if ($rol === 'Administrador'): ?>
        <a class="nav-link <?php echo ($pagina_actual == 'usuarios.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/usuarios.php">
            <i class="bi bi-people"></i>
            Usuarios
            <span class="badge-crud">CRUD</span>
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'proveedores.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/proveedores.php">
            <i class="bi bi-building"></i>
            Proveedores
            <span class="badge-crud">CRUD</span>
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'productos.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/productos.php">
            <i class="bi bi-box-seam"></i>
            Productos y categorías
            <span class="badge-crud">CRUD</span>
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'inventario.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/inventario.php">
            <i class="bi bi-clipboard2-pulse"></i>
            Inventario
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'reportes.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/Estadisticas.php">
            <i class="bi bi-bar-chart-line"></i>
            Reportes
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'asistencia.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/asistencia.php">
            <i class="bi bi-calendar-check"></i>
            Asistencia
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'historial_ventas.php') ? 'active' : ''; ?>"
   href="<?php echo $views; ?>/historial_ventas.php">
    <i class="bi bi-clock-history"></i>
    Historial Ventas
</a>
        <?php endif; ?>

       

        <a class="nav-link nav-link-pos-inline <?php echo ($pagina_actual == 'Pos.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/Pos.php">
            <i class="bi bi-display"></i>
            Punto de Venta (POS)
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'perfil.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/perfil.php">
            <i class="bi bi-gear"></i>
            Perfil Admin
        </a>

        <a class="nav-link back-to-home" href="<?php echo $base_url; ?>/logout.php">
            <i class="bi bi-box-arrow-left"></i>
            Cerrar sesión
        </a>

    </nav>

    <div class="sidebar-profile">
        <div class="profile-avatar"><?php echo htmlspecialchars($admin_ini); ?></div>
        <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($admin_nombre); ?></div>
            <div class="profile-role"><?php echo htmlspecialchars($admin_rol); ?></div>
        </div>
    </div>

</div>

<script>
(function() {
    var sidebar   = document.getElementById('gymSidebar');
    var btn       = document.getElementById('sidebarToggle');
    var icon      = document.getElementById('toggleIcon');
    var esPos     = <?php echo $es_pos ? 'true' : 'false'; ?>;

    // Estado inicial
    var collapsed = esPos;
    if (collapsed) {
        document.body.classList.add('sidebar-collapsed');
        icon.className = 'bi bi-layout-sidebar';
    }

    btn.addEventListener('click', function() {
        collapsed = !collapsed;

        if (collapsed) {
            sidebar.classList.add('sidebar-hidden');
            document.body.classList.add('sidebar-collapsed');
            icon.className = 'bi bi-layout-sidebar';
        } else {
            sidebar.classList.remove('sidebar-hidden');
            document.body.classList.remove('sidebar-collapsed');
            icon.className = 'bi bi-list';
        }
    });
})();
</script>