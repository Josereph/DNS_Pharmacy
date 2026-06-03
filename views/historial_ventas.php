<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

$base_url = '/DNS_Pharmacy';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DNS_Pharmacy/config/database.php';
$conn = conectar();
if (!$conn) die("Error: No se pudo conectar a la base de datos");

// Obtener lista de empleados para el select del modal correo
$empleados = [];
if ($_SESSION['usuario_rol'] === 'Administrador') {
    $resEmp = $conn->query("SELECT id_usuario, CONCAT(nombre,' ',apellido) AS nombre_completo FROM usuarios WHERE estado=1 ORDER BY nombre");
    if ($resEmp) $empleados = $resEmp->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Ventas - DNS Pharmacy</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/slider.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/historial_ventas.css">
    <style>
        .stat-card { transition: all 0.3s cubic-bezier(0.25,.8,.25,1); cursor:pointer; }
        .stat-card:hover { transform:translateY(-8px) scale(1.02); box-shadow:0 14px 28px rgba(0,0,0,.10); }
        .tabla-card { transition: transform .3s ease, box-shadow .3s ease; }
        .tabla-card:hover { box-shadow:0 5px 15px rgba(0,0,0,.05); }
    </style>
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <!-- Header -->
    <div class="page-header animate__animated animate__fadeInDown">
        <div>
            <h2 class="page-title">Historial de Ventas</h2>
            <p class="page-subtitle">Consulta y seguimiento de todas las ventas registradas</p>
        </div>
    </div>

    <!-- Banner -->
    <div class="stats-strip slide-up mb-4">
        <div class="stat-card" style="--delay:0s">
            <div class="stat-icon stat-purple"><i class="bi bi-receipt"></i></div>
            <div>
                <h2 class="page-title mb-1">Centro de Reportes de Ventas</h2>
                <p class="page-subtitle mb-0">Gestión integral de ingresos y desempeño de empleados</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-strip animate__animated animate__zoomIn animate__delay-1s mb-4">
        <div class="stat-card" style="--delay:.08s">
            <div class="stat-icon stat-purple"><i class="bi bi-ticket-perforated"></i></div>
            <div><div class="stat-valor" id="statTickets">0</div><div class="stat-label">Total tickets</div></div>
        </div>
        <div class="stat-card" style="--delay:.16s">
            <div class="stat-icon stat-green"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-valor" id="statTotal">$0.00</div><div class="stat-label">Total vendido</div></div>
        </div>
        <div class="stat-card" style="--delay:.24s">
            <div class="stat-icon stat-amber"><i class="bi bi-percent"></i></div>
            <div><div class="stat-valor" id="statIva">$0.00</div><div class="stat-label">Total IVA</div></div>
        </div>
        <div class="stat-card" style="--delay:.32s">
            <div class="stat-icon stat-blue"><i class="bi bi-graph-up-arrow"></i></div>
            <div><div class="stat-valor" id="statPromedio">$0.00</div><div class="stat-label">Ticket promedio</div></div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-bar mt-3 animate__animated animate__fadeIn animate__delay-1s mb-4">
        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Desde</label>
            <input type="date" id="filtroDesde" class="filtro-input" onchange="filtrarDatos()">
        </div>
        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Hasta</label>
            <input type="date" id="filtroHasta" class="filtro-input" onchange="filtrarDatos()">
        </div>
        <div class="filtro-fecha-wrap">
            <label class="filtro-label">Método de pago</label>
            <select id="filtroMetodo" class="filtro-input" onchange="filtrarDatos()">
                <option value="">Todos</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Digital</option>
            </select>
        </div>
        <div class="filtros-accesos-rapidos">
            <button class="btn-periodo" onclick="setPeriodo('hoy',this)">Hoy</button>
            <button class="btn-periodo" onclick="setPeriodo('semana',this)">Esta semana</button>
            <button class="btn-periodo" onclick="setPeriodo('mes',this)">Este mes</button>
            <button class="btn-periodo activo" onclick="setPeriodo('todo',this)">Todo</button>
        </div>
        <div style="margin-left:auto; display:flex; gap:8px; align-items:center;">
            <button class="btn btn-danger d-flex align-items-center" onclick="generarPDFHistorial()"
                    style="font-size:13px;font-weight:600;border-radius:6px;padding:8px 16px;gap:6px;">
                <i class="bi bi-filetype-pdf" style="font-size:16px;"></i> Exportar PDF
            </button>
            <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>
            <button class="btn-enviar-correo" onclick="abrirModalCorreo()">
                <i class="bi bi-envelope-arrow-up"></i> Enviar por correo
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla -->
    <div class="tabla-card animate__animated animate__fadeInUp animate__delay-1s">
        <div class="tabla-header-bar">
            <span>Mostrando <strong id="contadorVisible">0</strong> de <strong id="contadorTotal">0</strong> ventas</span>
            <span>DNS Pharmacy · Historial</span>
        </div>
        <table class="tabla-ventas">
            <thead>
                <tr>
                    <th>#</th><th>Ticket</th><th>Empleado</th><th>Fecha</th>
                    <th>Subtotal</th><th>IVA</th><th>Total</th><th>Método</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr><td colspan="10" class="tabla-vacia">Cargando ventas...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<!-- ══ MODAL: DETALLE ══ -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo"><i class="bi bi-receipt"></i> Detalle de Venta</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalDetalle')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoDetalle"></div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalDetalle')">Cerrar</button>
            <button type="button" class="btn-imprimir" onclick="imprimirDetalle()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
</div>


<!-- ══ MODAL: ENVIAR CORREO ══ -->
<div class="modal-overlay" id="modalCorreo">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo"><i class="bi bi-envelope-arrow-up"></i> Enviar Historial por Correo</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalCorreo')">&times;</button>
        </div>
        <div class="modal-body">

            <div class="correo-intro">
                <i class="bi bi-info-circle"></i>
                Se generará un PDF con el historial seleccionado y se enviará al correo del administrador.
            </div>

            <!-- Tipo de reporte -->
            <div class="correo-campo">
                <label class="correo-label">Tipo de reporte</label>
                <div class="correo-tipo-tabs">
                    <button class="ctipo-tab active" data-tipo="dia" onclick="selTipo(this,'dia')">
                        <i class="bi bi-calendar-day"></i> Ventas del día
                    </button>
                    <button class="ctipo-tab" data-tipo="rango" onclick="selTipo(this,'rango')">
                        <i class="bi bi-calendar-range"></i> Rango de fechas
                    </button>
                    <button class="ctipo-tab" data-tipo="usuario" onclick="selTipo(this,'usuario')">
                        <i class="bi bi-person-lines-fill"></i> Por empleado
                    </button>
                </div>
            </div>

            <!-- Fecha del día -->
            <div class="correo-campo" id="campoFechaDia">
                <label class="correo-label">Fecha</label>
                <input type="date" id="correoDia" class="correo-input">
            </div>

            <!-- Rango fechas -->
            <div class="correo-campo" id="campoRango" style="display:none;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label class="correo-label">Desde</label>
                        <input type="date" id="correoDesde" class="correo-input">
                    </div>
                    <div>
                        <label class="correo-label">Hasta</label>
                        <input type="date" id="correoHasta" class="correo-input">
                    </div>
                </div>
            </div>

            <!-- Por usuario -->
            <div class="correo-campo" id="campoUsuario" style="display:none;">
                <label class="correo-label">Empleado</label>
                <select id="correoUsuario" class="correo-input">
                    <option value="">— Seleccionar empleado —</option>
                    <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id_usuario'] ?>"><?= htmlspecialchars($emp['nombre_completo']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:10px;">
                    <div>
                        <label class="correo-label">Desde (opcional)</label>
                        <input type="date" id="correoUsuarioDesde" class="correo-input">
                    </div>
                    <div>
                        <label class="correo-label">Hasta (opcional)</label>
                        <input type="date" id="correoUsuarioHasta" class="correo-input">
                    </div>
                </div>
            </div>

            <!-- Correo destino -->
            <div class="correo-campo">
                <label class="correo-label">Correo destino (administrador)</label>
                <input type="email" id="correoDestino" class="correo-input"
                       placeholder="admin@dnspharmacy.com">
            </div>

            <!-- Asunto -->
            <div class="correo-campo">
                <label class="correo-label">Asunto del correo</label>
                <input type="text" id="correoAsunto" class="correo-input"
                       value="Historial de Ventas - DNS Pharmacy">
            </div>

            <span class="correo-error" id="correoError"></span>

            <!-- Preview info -->
            <div class="correo-preview" id="correoPreview" style="display:none;">
                <i class="bi bi-check-circle-fill"></i>
                <span id="correoPreviewTxt"></span>
            </div>

        </div>
        <div class="modal-footer-custom">
            <button class="btn-cancelar" onclick="cerrarModal('modalCorreo')">Cancelar</button>
            <button class="btn-enviar-ok" id="btnEnviarOk" onclick="enviarCorreo()">
                <i class="bi bi-send"></i> Enviar correo
            </button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="<?= $base_url ?>/assets/js/historial_ventas.js"></script>
</body>
</html>