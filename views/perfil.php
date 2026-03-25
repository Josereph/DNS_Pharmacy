<!doctype html>
<html lang="es">
<head>
    <title>Mi Perfil - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Mi Perfil</h2>
            <p class="page-subtitle">Información personal y resumen de actividad</p>
        </div>
        
    </div>

    <div class="perfil-grid">

       
        <div class="perfil-card">
            <div class="perfil-avatar-wrap">
                <div class="perfil-avatar" id="perfilAvatar">AG</div>
                <div class="perfil-avatar-info">
                    <div class="perfil-nombre" id="perfilNombreCompleto">Administrador General</div>
                    <span class="badge-rol rol-admin" id="perfilRol">Administrador</span>
                </div>
            </div>

            <div class="perfil-divider"></div>

            <div class="perfil-datos">
    <div class="perfil-dato-item">
        <span class="dato-label"><i class="bi bi-envelope"></i> Correo</span>
        <span class="dato-valor" id="perfilCorreo">—</span>
    </div>
    <div class="perfil-dato-item">
        <span class="dato-label"><i class="bi bi-telephone"></i> Teléfono</span>
        <span class="dato-valor" id="perfilTelefono">—</span>
    </div>
    <div class="perfil-dato-item">
        <span class="dato-label"><i class="bi bi-clock-history"></i> Último acceso</span>
        <span class="dato-valor" id="perfilUltimoAcceso">—</span>
    </div>
</div>
        </div>

   
        <div class="perfil-stats-col">

            
            <div class="stats-strip">
                <div class="stat-card">
                    <div class="stat-icon stat-purple"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="stat-valor" id="statMisTickets">0</div>
                        <div class="stat-label">Mis tickets</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-green"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="stat-valor" id="statMisVentas">$0.00</div>
                        <div class="stat-label">Total vendido</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-amber"><i class="bi bi-graph-up"></i></div>
                    <div>
                        <div class="stat-valor" id="statPromedio">$0.00</div>
                        <div class="stat-label">Promedio/ticket</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-blue"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="stat-valor" id="statHoy">0</div>
                        <div class="stat-label">Ventas hoy</div>
                    </div>
                </div>
            </div>

            
            <div class="filtros-bar">
                <div class="filtro-fecha-wrap">
                    <label class="filtro-label">Desde</label>
                    <input type="date" id="filtroDesde" class="filtro-input" onchange="filtrarMisVentas()">
                </div>
                <div class="filtro-fecha-wrap">
                    <label class="filtro-label">Hasta</label>
                    <input type="date" id="filtroHasta" class="filtro-input" onchange="filtrarMisVentas()">
                </div>
                <div class="filtros-accesos-rapidos">
                    <button class="btn-periodo activo" onclick="setPeriodo('mes', event)">Este mes</button>
                    <button class="btn-periodo" onclick="setPeriodo('semana', event)">Esta semana</button>
                    <button class="btn-periodo" onclick="setPeriodo('hoy', event)">Hoy</button>
                    <button class="btn-periodo" onclick="setPeriodo('todo', event)">Todo</button>
                </div>
            </div>

            
            <div class="tabla-card">
                <table class="tabla-productos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>numero_ticket</th>
                            <th>fecha_venta</th>
                            <th>subtotal</th>
                            <th>impuesto</th>
                            <th>total</th>
                            <th>metodo_pago</th>
                            <th>estado</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoMisVentas">
                        <tr>
                            <td colspan="9" class="tabla-vacia">No hay ventas registradas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>





<div class="modal-overlay" id="modalDetalleVenta">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloDetalleVenta">Detalle de venta</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalDetalleVenta')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoDetalleVenta"></div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalDetalleVenta')">Cerrar</button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/perfil.js"></script>
</body>
</html>