<?php
// Detectar la URL base automáticamente
$base_url = '/DNS_Pharmacy';
?>

<footer>
  <div class="footer-container">
    <div class="footer-top">
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" alt="DNS Pharmacy">
          <div class="logo-text">
            <span class="logo-name">DNS Pharmacy</span>
            <span class="logo-sub">Drug Network Supply</span>
          </div>
        </div>
      </div>

      <nav class="footer-nav">
        <ul>
          <li><a href="<?php echo $base_url; ?>/index.php">Inicio</a></li>
          <li><a href="<?php echo $base_url; ?>/views/Estadisticas.php">Dashboard</a></li>
          <li><a href="<?php echo $base_url; ?>/views/productos.php">Productos</a></li>
          <li><a href="<?php echo $base_url; ?>/views/historial_ventas.php">Historial</a></li>
          <li><a href="<?php echo $base_url; ?>/views/Estadisticas.php">Reportes</a></li>
        </ul>
      </nav>

      <div class="footer-contact">
        <p>contacto@dnspharmacy.com</p>
        <p>+503 7622-4659</p>
        <p>San Salvador, El Salvador</p>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copy">© 2025 DNS Pharmacy. Todos los derechos reservados.</p>
      <div class="footer-devcore">
        Desarrollado por 
        <div class="devcore-wrapper">
          <span class="devcore-text">DevCore</span>
          <div class="devcore-modal">
            <p class="devcore-title">Equipo DevCore</p>
            <ul>
              <li>Lisseth Portillo</li>
              <li>Arturo</li>
              <li>Joseph</li>
              <li>Rene</li>
              <li>CHarly</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>