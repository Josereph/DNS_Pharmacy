<!doctype html>
<html lang="es">
<head>
    <title>Tomar Asistencia - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/asistencia_scan.css">
</head>
<body>
<div class="scan-container">
    <h4 class="text-center"><i class="bi bi-upc-scan"></i> Escanear código QR</h4>
    <p class="text-muted text-center">Coloca tu código QR frente a la cámara</p>
    <div class="video-container">
        <video id="preview" autoplay playsinline></video>
    </div>
    <div id="result" class="scan-result"></div>
    <div class="text-center mt-3">
        <a href="asistencia.php" class="btn-cerrar"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <!-- Alternativa manual si la cámara no funciona -->
    <div class="manual-input">
        <hr>
        <p class="text-center text-muted"><i class="bi bi-info-circle"></i> Si la cámara no funciona, usa el método manual:</p>
        <input type="text" id="manualToken" placeholder="Token del QR (código)">
        <input type="text" id="manualUid" placeholder="ID del usuario">
        <button class="btn-cerrar" style="background:#28a745;" onclick="registrarManual()">Registrar manual</button>
    </div>
</div>

<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script src="../assets/js/asistencia_scan.js"></script>
</body>
</html>