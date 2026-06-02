<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/Login.php');
    exit;
}

$base_url = '';
$views = $base_url . '/views';
?>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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

        <!-- Tarjeta de perfil -->
        <div class="perfil-card">

            <!-- Foto de perfil -->
            <div class="perfil-avatar-wrap">
                <div class="perfil-foto-wrap">
                    <div class="perfil-avatar" id="perfilAvatar">AG</div>
                    <img id="perfilFoto" src="" alt="Foto de perfil" style="display:none;">
                    <button class="btn-cambiar-foto" onclick="document.getElementById('inputFoto').click()" title="Cambiar foto">
                        <i class="bi bi-camera"></i>
                    </button>
                    <input type="file" id="inputFoto" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previsualizarFoto(this)">
                </div>
                <div class="perfil-avatar-info">
                    <div class="perfil-nombre" id="perfilNombreCompleto">—</div>
                    <span class="badge-rol rol-admin" id="perfilRol">—</span>
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
                    <span class="form-error" id="err_edit_telefono"></span>
                </div>
                <div class="perfil-dato-item">
                    <span class="dato-label"><i class="bi bi-clock-history"></i> Último acceso</span>
                    <span class="dato-valor" id="perfilUltimoAcceso">—</span>
                </div>
            </div>

            <div class="perfil-divider"></div>

            <!-- Botones de acción -->
            <div class="perfil-acciones">
                <button class="btn-editar-perfil" onclick="abrirModalEditar()">
                    <i class="bi bi-pencil-square"></i> Editar información
                </button>
                <button class="btn-cambiar-pass" onclick="abrirModalPassword()">
                    <i class="bi bi-lock"></i> Cambiar contraseña
                </button>
            </div>

        </div>

        <!-- Dashboard de ventas -->
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
                            <th>N° Ticket</th>
                            <th>Fecha</th>
                            <th>Subtotal</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                            <th>Método pago</th>
                            <th>Estado</th>
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


<!-- MODAL: EDITAR PERFIL -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo">Editar información personal</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalEditar')">&times;</button>
        </div>
        <form id="formEditar" novalidate>
            <input type="hidden" id="edit_id_usuario" name="id_usuario">
            <div class="modal-body">

                <div class="form-seccion">Información personal</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Nombre <span class="req">*</span></label>
                        <input type="text" id="edit_nombre" name="nombre" class="form-input" placeholder="Ej. Carlos">
                        <span class="form-error" id="err_edit_nombre"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Apellido <span class="req">*</span></label>
                        <input type="text" id="edit_apellido" name="apellido" class="form-input" placeholder="Ej. Pérez">
                        <span class="form-error" id="err_edit_apellido"></span>
                    </div>
                </div>

                <div class="form-seccion">Contacto</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Correo electrónico <span class="req">*</span></label>
                        <input type="email" id="edit_correo" name="correo" class="form-input" placeholder="correo@dnspharmacy.com">
                        <span class="form-error" id="err_edit_correo"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Teléfono</label>
                        <input type="text" id="edit_telefono" name="telefono" class="form-input" placeholder="Ej. +503 7600-0000">
                    </div>
                </div>

            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL: CAMBIAR CONTRASEÑA -->
<div class="modal-overlay" id="modalPassword">
    <div class="modal-box modal-chico">
        <div class="modal-header">
            <h5 class="modal-titulo">Cambiar contraseña</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalPassword')">&times;</button>
        </div>
        <form id="formPassword" novalidate>
            <div class="modal-body">

                <div class="form-group-custom">
                    <label>Contraseña actual <span class="req">*</span></label>
                    <div class="input-password-wrap">
                        <input type="password" id="pass_actual" name="password_actual" class="form-input" placeholder="Tu contraseña actual">
                        <button type="button" class="btn-toggle-pass" onclick="togglePass('pass_actual', this)"><i class="bi bi-eye"></i></button>
                    </div>
                    <span class="form-error" id="err_pass_actual"></span>
                </div>

                <div class="form-group-custom">
                    <label>Nueva contraseña <span class="req">*</span></label>
                    <div class="input-password-wrap">
                        <input type="password" id="pass_nueva" name="password_hash" class="form-input" placeholder="Mínimo 8 caracteres">
                        <button type="button" class="btn-toggle-pass" onclick="togglePass('pass_nueva', this)"><i class="bi bi-eye"></i></button>
                    </div>
                    <span class="form-error" id="err_pass_nueva"></span>
                </div>

                <div class="form-group-custom">
                    <label>Confirmar nueva contraseña <span class="req">*</span></label>
                    <div class="input-password-wrap">
                        <input type="password" id="pass_confirmar" class="form-input" placeholder="Repite la nueva contraseña">
                        <button type="button" class="btn-toggle-pass" onclick="togglePass('pass_confirmar', this)"><i class="bi bi-eye"></i></button>
                    </div>
                    <span class="form-error" id="err_pass_confirmar"></span>
                </div>

            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalPassword')">Cancelar</button>
                <button type="submit" class="btn-guardar">Actualizar contraseña</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL: DETALLE VENTA -->
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