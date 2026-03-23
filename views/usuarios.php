<!doctype html>
<html lang="es">
<head>
    <title>Usuarios - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/usuarios.css">
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Usuarios</h2>
            <p class="page-subtitle">Gestión de usuarios del sistema</p>
        </div>
        <div class="header-actions">
            <button class="btn-nuevo" onclick="abrirModalUsuario()">
                <i class="bi bi-person-plus"></i> Nuevo Usuario
            </button>
        </div>
    </div>

    <div class="filtros-bar">
        <input type="text" id="buscador" class="filtro-input" placeholder="Buscar por nombre, correo o teléfono..." oninput="filtrarTabla()">
        <select id="filtroRol" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todos los roles</option>
            <option value="1">Administrador</option>
            <option value="2">Cajero</option>
        </select>
        <select id="filtroEstado" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>
    </div>

    <div class="tabla-card">
        <table class="tabla-productos" id="tablaUsuarios">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Último acceso</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr>
                    <td colspan="8" class="tabla-vacia">No hay usuarios registrados.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<<<<<<< HEAD
=======

>>>>>>> 30b5ac1 (se creo la vista de inventario, compras y venta por empleado)
<div class="modal-overlay" id="modalUsuario">
    <div class="modal-box modal-grande">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalUsuario">Nuevo Usuario</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalUsuario')">&times;</button>
        </div>
        <form id="formUsuario" novalidate>
            <input type="hidden" id="usr_id" name="id_usuario">
            <div class="modal-body">

                <div class="form-seccion">Información personal</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Nombre <span class="req">*</span></label>
                        <input type="text" id="usr_nombre" name="nombre" class="form-input" placeholder="Ej. Carlos">
                        <span class="form-error" id="err_nombre"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Apellido <span class="req">*</span></label>
                        <input type="text" id="usr_apellido" name="apellido" class="form-input" placeholder="Ej. Pérez">
                        <span class="form-error" id="err_apellido"></span>
                    </div>
                </div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Correo electrónico <span class="req">*</span></label>
                        <input type="email" id="usr_correo" name="correo" class="form-input" placeholder="Ej. usuario@dnspharmacy.com">
                        <span class="form-error" id="err_correo"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Teléfono</label>
                        <input type="text" id="usr_telefono" name="telefono" class="form-input" placeholder="Ej. +503 7600-0000">
                    </div>
                </div>

                <div class="form-seccion">Acceso al sistema</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Rol <span class="req">*</span></label>
                        <select id="usr_rol" name="id_rol" class="form-input">
                            <option value="">Seleccionar rol</option>
                            <option value="1">Administrador</option>
                            <option value="2">Cajero</option>
                        </select>
                        <span class="form-error" id="err_rol"></span>
                    </div>
                    <div class="form-group-custom">
                        <label id="labelPassword">Contraseña <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="usr_password" name="password_hash" class="form-input" placeholder="Mínimo 8 caracteres">
                            <button type="button" class="btn-toggle-pass" onclick="togglePassword('usr_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <span class="form-error" id="err_password"></span>
                        <span class="form-hint" id="hintPassword"></span>
                    </div>
                </div>

                <div class="form-seccion">Configuración</div>
                <div class="form-group-custom checks-group">
                    <label class="check-label">
                        <input type="checkbox" id="usr_estado" name="estado" value="1" checked>
                        <span>Usuario activo</span>
                    </label>
                </div>

            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalUsuario')">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar usuario</button>
            </div>
        </form>
    </div>
</div>



<div class="modal-overlay" id="modalVerUsuario">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo">Detalle del Usuario</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalVerUsuario')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoVerUsuario"></div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalVerUsuario')">Cerrar</button>
        </div>
    </div>
</div>



<div class="modal-overlay" id="modalEliminar">
    <div class="modal-box modal-chico">
        <div class="modal-header">
            <h5 class="modal-titulo">Eliminar usuario</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalEliminar')">&times;</button>
        </div>
        <div class="modal-body">
            <p class="eliminar-texto">¿Estás seguro que deseas eliminar a <strong id="nombreEliminar"></strong>?</p>
            <p class="eliminar-aviso">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEliminar')">Cancelar</button>
            <button type="button" class="btn-eliminar" id="btnConfirmarEliminar">Sí, eliminar</button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/usuarios.js"></script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 30b5ac1 (se creo la vista de inventario, compras y venta por empleado)
