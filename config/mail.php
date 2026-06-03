<?php
/* =============================================
   CONFIGURACIÓN DE CORREO — DNS Pharmacy
   PHPMailer con Gmail SMTP
   =============================================

   IMPORTANTE — Para Gmail necesitas:
   1. Tener activada la verificación en 2 pasos en tu cuenta Google.
   2. Generar una "Contraseña de aplicación":
      → Mi cuenta Google → Seguridad → Contraseñas de aplicaciones
      → Selecciona "Correo" y "Otro" → copia los 16 caracteres generados
   3. Pegar esa contraseña en MAIL_PASS (NO tu contraseña normal de Gmail)
   ============================================= */

define('MAIL_USER', 'joseph.orellana@catolica.edu.sv');   // ← Cambia por tu Gmail
define('MAIL_PASS', 'qhbp erqg siix lxvt');  // ← Contraseña de aplicación (16 chars)
define('MAIL_FROM_NAME', 'DNS Pharmacy - Sistema POS');