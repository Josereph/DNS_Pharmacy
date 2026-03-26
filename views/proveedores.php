<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}
$base_url = '/DNS_Pharmacy';
?>
<!doctype html>
<html lang="es">
<head>
    <title>Proveedores - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/proveedores.css">
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    
    <div class="page-header">
        <div>
            <h2 class="page-title">Proveedores</h2>
            <p class="page-subtitle">Gestión del catálogo de proveedores</p>
        </div>
        <div class="header-actions">
            <button class="btn-nuevo" onclick="abrirModalProveedor()">
                <i class="bi bi-plus-lg"></i> Nuevo Proveedor
            </button>
        </div>
    </div>

  
    <div class="filtros-bar">
        <div class="search-wrap">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="buscador" class="filtro-input" placeholder="Buscar por nombre, NIT o correo..." oninput="filtrarTabla()">
        </div>
        <select id="filtroEstado" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>
    </div>

   
    <div class="tabla-card">
        <table class="tabla-productos" id="tablaProveedores">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>NIT</th>
                    <th>NRC</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr>
                    <td colspan="9" class="tabla-vacia">No hay proveedores registrados.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>

<!-- MODAL PROVEEDOR - CORREGIDO -->
<div class="modal-overlay" id="modalProveedor">
    <div class="modal-box modal-grande">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalProveedor">Nuevo Proveedor</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalProveedor')">&times;</button>
        </div>
        <form id="formProveedor" novalidate class="modal-form">
            <input type="hidden" id="prov_id" name="id_proveedor">
            <div class="modal-body">

                <!-- Información de la empresa -->
                <div class="form-seccion">Información de la empresa</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Nombre de la empresa <span class="req">*</span></label>
                        <input type="text" id="prov_nombre" name="nombre" class="form-input"
                               placeholder="Ej. Distribuidora Médica S.A.">
                        <span class="form-error" id="err_nombre"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Nombre del contacto <span class="req">*</span></label>
                        <input type="text" id="prov_contacto" name="nombre_contacto" class="form-input"
                               placeholder="Ej. Juan Pérez">
                        <span class="form-error" id="err_contacto"></span>
                    </div>
                </div>

                <!-- Datos de contacto -->
                <div class="form-seccion">Datos de contacto</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Teléfono <span class="req">*</span></label>
                        <input type="text" id="prov_telefono" name="telefono" class="form-input"
                               placeholder="Ej. +503 7600-0000">
                        <span class="form-error" id="err_telefono"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Correo electrónico <span class="req">*</span></label>
                        <input type="email" id="prov_correo" name="correo" class="form-input"
                               placeholder="Ej. contacto@empresa.com">
                        <span class="form-error" id="err_correo"></span>
                    </div>
                </div>
                <div class="form-group-custom">
                    <label>Dirección</label>
                    <input type="text" id="prov_direccion" name="direccion" class="form-input"
                           placeholder="Ej. Col. Escalón, San Salvador">
                </div>

                <!-- Datos fiscales -->
                <div class="form-seccion">Datos fiscales</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>NIT</label>
                        <input type="text" id="prov_nit" name="nit" class="form-input"
                               placeholder="Ej. 0614-010101-001-0">
                    </div>
                    <div class="form-group-custom">
                        <label>NRC</label>
                        <input type="text" id="prov_nrc" name="nrc" class="form-input"
                               placeholder="Ej. 123456-7">
                    </div>
                </div>

                <!-- Configuración -->
                <div class="form-seccion">Configuración</div>
                <div class="form-group-custom checks-group">
                    <label class="check-label">
                        <input type="checkbox" id="prov_estado" name="estado" value="1" checked>
                        <span>Proveedor activo</span>
                    </label>
                </div>

            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalProveedor')">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar proveedor</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER PROVEEDOR -->
<div class="modal-overlay" id="modalVerProveedor">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo">Detalle del Proveedor</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalVerProveedor')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoVerProveedor">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalVerProveedor')">Cerrar</button>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-box modal-chico">
        <div class="modal-header">
            <h5 class="modal-titulo">Eliminar proveedor</h5>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script src="../assets/js/proveedores.js"></script>
</body>
</html>