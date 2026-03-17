
<?php

$pagina_actual = basename($_SERVER['PHP_SELF']);
$base_url = '/DNS_Pharmacy';
?>

<div class="gym-sidebar">
    <div class="logo-area text-center py-4">
       <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" style="height:80px;">
    </div>
    
    <nav class="nav flex-column">
       
        <a class="nav-link <?php echo ($pagina_actual == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'entrenadores.php') ? 'active' : ''; ?>" href="entrenadores.php">
            <i class="fas fa-user-tie"></i> Entrenadores
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'miembros.php') ? 'active' : ''; ?>" href="miembros.php">
            <i class="fas fa-users"></i> Miembros
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'clasess.php') ? 'active' : ''; ?>" href="clasess.php">
            <i class="fas fa-chalkboard-teacher"></i> Clases
        </a>
        <a class="nav-link <?php echo ($pagina_actual == 'horario_clases.php') ? 'active' : ''; ?>" href="horario_clases.php">
            <i class="fas fa-clock"></i> Horario Clases
        </a>


        <a class="nav-link <?php echo ($pagina_actual == 'registroproductos.php') ? 'active' : ''; ?>" href="registroproductos.php">
            <i class="fas fa-box"></i> Productos
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'planes.php') ? 'active' : ''; ?>" href="planes.php">
            <i class="fas fa-id-card"></i> Planes
        </a>

        <a class="nav-link <?php echo ($pagina_actual == 'reportes.php') ? 'active' : ''; ?>" href="reportes.php">
            <i class="fas fa-file-alt"></i> Reportes
        </a>

     

       
        <a class="nav-link back-to-home" href="<?php echo $base_url; ?>/index.php">
            <i class="fas fa-arrow-left"></i> Regresar al inicio
        </a>
       
         <div class="sidebar-divider"></div>
    </nav>
</div>