<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$base_url = '/DNS_Pharmacy';
$views = $base_url . '/views';
?>

<div class="gym-sidebar">
    <div class="logo-area text-center py-4">
        <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" style="height:80px;">
    </div>

    <nav class="nav flex-column">

        <a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>/index.php">
            Inicio
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'usuarios.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/usuarios.php">
            Usuarios
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'proveedores.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/proveedores.php">
            Proveedores
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'productos.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/productos.php">
            Productos
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'compras.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/compras.php">
            Registrar Compras
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'historial_ventas.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/historial_ventas.php">
            Historial de Ventas
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'ventas_empleado.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/ventas_empleado.php">
            Ventas x Empleado
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'reportes.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/reportes.php">
            Reportes
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'asistencia.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/asistencia.php">
            Asistencia
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'perfil_admin.php') ? 'active' : ''; ?>" href="<?php echo $views; ?>/perfil_admin.php">
            Perfil Admin
        </a>

        <a class="nav-link back-to-home" href="<?php echo $base_url; ?>/login.php">
            Cerrar sesión
        </a>

    </nav>
</div>