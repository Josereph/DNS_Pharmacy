<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Tomar Asistencia - DNS Pharmacy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/asistencia_scan.css">
</head>
<body>

<div class="scan-card">
    <div class="scan-header">
        <h1><i class="bi bi-upc-scan"></i> Escanear QR</h1>
    </div>

    <div class="clock-panel">
        <div class="reloj" id="reloj">00:00:00</div>
        <div class="fecha" id="fecha"></div>
    </div>

    <div class="camera-area">
        <div class="camera-container">
            <canvas id="qr-canvas" style="width: 100%; height: 100%; object-fit: cover;"></canvas>
            <div id="camera-placeholder" class="camera-placeholder">
                <i class="bi bi-camera-video-off"></i>
                <span>Esperando cámara...</span>
            </div>
        </div>

        <div class="button-group">
            <button class="btn btn-primary" onclick="encenderCamara()"><i class="bi bi-camera-fill"></i> Activar cámara</button>
            <button class="btn btn-outline" onclick="cerrarCamara()"><i class="bi bi-power"></i> Apagar</button>
            <a href="asistencia.php" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    
</div>

<!-- Modal resultado -->
<div id="modal-overlay" class="modal-overlay">
    <div class="modal-box" id="modal-box">
        <div class="modal-icon" id="modalIcon">✅</div>
        <div class="modal-estado" id="modalEstado">Registrado</div>
        <div class="modal-mensaje" id="modalMensaje"></div>
        <div class="modal-codigo" id="modalCodigo"></div>
        <button class="modal-btn" onclick="cerrarModal()">Cerrar</button>
    </div>
</div>

<script src="../assets/js/jsqr.min.js"></script>
<script src="../assets/js/asistencia_scan.js"></script>
</body>
</html>