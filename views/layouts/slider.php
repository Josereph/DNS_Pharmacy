<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$base_url = '/DNS_Pharmacy';
$views = $base_url . '/views';

$admin_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$admin_rol    = $_SESSION['usuario_rol']    ?? 'Sin rol';
$admin_ini    = strtoupper(substr($admin_nombre, 0, 2));

$rol = $_SESSION['usuario_rol'] ?? '';

// Detectar si estamos en POS (sin importar mayúsculas/minúsculas)
$es_pos = (strtolower($pagina_actual) === 'pos.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/slider.css">

<!-- Botón toggle siempre visible -->
<button class="sidebar-toggle-btn" id="sidebarToggle" title="Mostrar / ocultar menú">
    <i class="bi bi-list" id="toggleIcon"></i>
</button>

<div class="gym-sidebar" id="gymSidebar">

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

        <a class="nav-link nav-link-pos-inline <?php echo ($pagina_actual == 'Pos.php' || $pagina_actual == 'pos.php') ? 'active' : ''; ?>"
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
    var sidebar = document.getElementById('gymSidebar');
    var btn = document.getElementById('sidebarToggle');
    var icon = document.getElementById('toggleIcon');
    
    // Verificar si estamos en POS
    var esPos = <?php echo $es_pos ? 'true' : 'false'; ?>;
    
    // Función para ocultar sidebar
    function ocultarSidebar() {
        sidebar.style.transform = 'translateX(-250px)';
        document.body.classList.add('sidebar-collapsed');
        if (icon) icon.className = 'bi bi-layout-sidebar';
        // Guardar estado
        localStorage.setItem('sidebar_oculto', 'true');
    }
    
    // Función para mostrar sidebar
    function mostrarSidebar() {
        sidebar.style.transform = 'translateX(0)';
        document.body.classList.remove('sidebar-collapsed');
        if (icon) icon.className = 'bi bi-list';
        // Guardar estado
        localStorage.setItem('sidebar_oculto', 'false');
    }
    
    // Si estamos en POS, ocultar el sidebar automáticamente
    if (esPos) {
        ocultarSidebar();
    } else {
        // En otras páginas, restaurar el estado anterior o mostrar
        var estabaOculto = localStorage.getItem('sidebar_oculto');
        if (estabaOculto === 'true') {
            ocultarSidebar();
        } else {
            mostrarSidebar();
        }
    }
    
    // Evento del botón toggle
    if (btn) {
        btn.addEventListener('click', function() {
            var actualTransform = sidebar.style.transform;
            if (actualTransform === 'translateX(-250px)' || sidebar.classList.contains('sidebar-hidden')) {
                mostrarSidebar();
            } else {
                ocultarSidebar();
            }
        });
    }
})();
</script>