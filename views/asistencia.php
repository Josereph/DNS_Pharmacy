<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

$base_url = '/DNS_Pharmacy';
$views = $base_url . '/views';
?>
<!doctype html>
<html lang="es">
<head>
    <title>Asistencia - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/asistencia.css">



    
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h2 class="page-title">Asistencia</h2>
            <p class="page-subtitle">Control de entradas y salidas por código QR</p>
        </div>
        <div class="header-actions">
            <button class="btn-nuevo" style="background: #6f42c1;" onclick="abrirModalGenerarQR()">
                <i class="bi bi-qr-code"></i> Generar QR de usuario
            </button>
            <a href="asistencia_scan.php" class="btn-nuevo" style="background: #28a745;">
                <i class="bi bi-camera"></i> Tomar asistencia
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num" id="statTotal">0</div>
            <div class="stat-lbl">Total usuarios activos</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num" id="statPresentes">0</div>
            <div class="stat-lbl">Presentes hoy</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-num" id="statAusentes">0</div>
            <div class="stat-lbl">Ausentes hoy</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-num" id="statPromedio">0</div>
            <div class="stat-lbl">Promedio horas/mes</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-bar">
        <input type="date" id="fechaDesde" class="filtro-input" placeholder="Fecha desde">
        <input type="date" id="fechaHasta" class="filtro-input" placeholder="Fecha hasta">
        <select id="filtroUsuario" class="filtro-select">
            <option value="">Todos los usuarios</option>
        </select>
    </div>

    <!-- Tabla historial -->
    <div class="tabla-card">
        <div class="tabla-header-bar">
            <span>Historial de asistencias</span>
            <span>DNS Pharmacy · Asistencia</span>
        </div>
        <table class="tabla-productos" id="tablaAsistencia">
            <thead>
                
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Origen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr><td colspan="6" class="tabla-vacia">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>

<!-- Modal QR existente (para mostrar QR desde tabla) -->
<div class="modal-overlay" id="modalQR" style="display:none;">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo">Código QR para <span id="qrUsuarioNombre"></span></h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalQR')">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center;">
            <img id="qrImage" src="" alt="QR" style="max-width: 100%;">
            <p id="qrUrl" style="font-size: 12px; word-break: break-all;"></p>
        </div>
        <div class="modal-footer-custom">
            <button class="btn-cancelar" onclick="cerrarModal('modalQR')">Cerrar</button>
            <a id="qrDownloadLink" download="qr.png" class="btn-guardar" style="display: inline-block;">Descargar QR</a>
        </div>
    </div>
</div>

<!-- Nuevo Modal: Generar QR seleccionando usuario -->
<div class="modal-overlay" id="modalGenerarQR" style="display:none;">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo">Generar QR para un usuario</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalGenerarQR')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group-custom">
                <label>Selecciona un usuario:</label>
                <select id="selectUsuarioQR" class="form-input">
                    <option value="">Cargando usuarios...</option>
                </select>
            </div>
            <div style="margin-top: 20px;">
                <button class="btn-guardar" onclick="generarQRDesdeSelect()">Generar QR</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="../assets/js/asistencia.js"></script>
<script>
    // Función para abrir modal de generación de QR y cargar usuarios
    function abrirModalGenerarQR() {
        // Cargar usuarios en el select si no están cargados
        const select = document.getElementById('selectUsuarioQR');
        if (select.options.length <= 1) {
            fetch('../controllers/AsistenciaController.php?accion=listar_usuarios')
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        select.innerHTML = '<option value="">Seleccione un usuario</option>';
                        data.datos.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id_usuario;
                            option.textContent = `${user.nombre} ${user.apellido} (${user.correo})`;
                            select.appendChild(option);
                        });
                    } else {
                        select.innerHTML = '<option value="">Error al cargar usuarios</option>';
                    }
                });
        }
        document.getElementById('modalGenerarQR').style.display = 'flex';
    }

    // Generar QR desde el select
    function generarQRDesdeSelect() {
        const select = document.getElementById('selectUsuarioQR');
        const userId = select.value;
        const selectedOption = select.options[select.selectedIndex];
        const nombre = selectedOption ? selectedOption.textContent.split(' (')[0] : '';
        if (!userId) {
            alert('Por favor selecciona un usuario');
            return;
        }
        generarQR(userId, nombre);
        cerrarModal('modalGenerarQR');
    }

    // Cerrar modal (reutiliza función global)
    window.cerrarModal = function(id) {
        document.getElementById(id).style.display = 'none';
    };
</script>
</body>
</html>