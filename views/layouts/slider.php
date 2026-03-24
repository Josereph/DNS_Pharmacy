<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$base_url = '/DNS_Pharmacy';
$views = $base_url . '/views';

$admin_nombre = $_SESSION['nombre'] ?? 'Dr. Reyes';
$admin_rol    = $_SESSION['rol']    ?? 'Admin';
$admin_ini    = strtoupper(substr($admin_nombre, 0, 2));
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="gym-sidebar">

    <div class="logo-area">
        <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" alt="DNS Pharmacy" style="height:72px;">
    </div>

    <nav class="nav flex-column">

        <a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>"
           href="<?php echo $base_url; ?>/index.php">
            <i class="bi bi-house-door"></i>
            Inicio
        </a>

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
            Productos y categorias
            <span class="badge-crud">CRUD</span>
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'inventario.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/inventario.php">
            <i class="bi bi-cart3"></i>
            Registrar Compras
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'inventario.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/inventario.php">
            <i class="bi bi-clipboard2-pulse"></i>
            Inventario
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'historial_ventas.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/historial_ventas.php">
            <i class="bi bi-clock-history"></i>
            Historial Ventas
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'ventas_empleado.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/ventas_empleado.php">
            <i class="bi bi-person-lines-fill"></i>
            Ventas x Empleado
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'reportes.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/reportes.php">
            <i class="bi bi-bar-chart-line"></i>
            Reportes
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'asistencia.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/asistencia.php">
            <i class="bi bi-calendar-check"></i>
            Asistencia
        </a>

<<<<<<< HEAD
        <a class="nav-link-pos" href="<?php echo $views; ?>/pos.php">
         <i class="bi bi-display"></i>
         Punto de Venta (POS)
        </a>

    
=======
>>>>>>> acfd96babe90344561970576404aecc9fbfad54a
        <a class="nav-link <?php echo ($pagina_actual == 'perfil_admin.php') ? 'active' : ''; ?>"
           href="<?php echo $views; ?>/perfil_admin.php">
            <i class="bi bi-gear"></i>
            Perfil Admin
        </a>

        <a class="nav-link back-to-home" href="/DNS_Pharmacy/logout.php">
            <i class="bi bi-box-arrow-left"></i>
            Cerrar sesión
        </a>

    </nav>

</div>