<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$base_url = '/DNS_Pharmacy';
?>

<div class="gym-sidebar">
    <div class="logo-area text-center py-4">
        <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" style="height:80px;">
    </div>

    <nav class="nav flex-column">

        <a class="nav-link <?php echo ($pagina_actual == 'index.php' || $pagina_actual == 'index.php') ? 'active' : ''; ?>" href="index.php">
            Inicio
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'usuarios.php') ? 'active' : ''; ?>" href="usuarios.php">
            Usuarios
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'proveedores.php') ? 'active' : ''; ?>" href="proveedores.php">
            Proveedores
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'productos.php') ? 'active' : ''; ?>" href="productos.php">
            Productos
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'compras.php') ? 'active' : ''; ?>" href="compras.php">
            Registrar Compras
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'historial_ventas.php') ? 'active' : ''; ?>" href="historial_ventas.php">
            Historial de Ventas
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'ventas_empleado.php') ? 'active' : ''; ?>" href="ventas_empleado.php">
            Ventas x Empleado
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'reportes.php') ? 'active' : ''; ?>" href="reportes.php">
            Reportes
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'asistencia.php') ? 'active' : ''; ?>" href="asistencia.php">
            Asistencia
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'perfil_admin.php') ? 'active' : ''; ?>" href="perfil_admin.php">
            Perfil Admin
        </a>

        <a class="nav-link back-to-home" href="<?php echo $base_url; ?>/login.php">
            Cerrar sesión
        </a>

    </nav>
</div>