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
    <title>Productos - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/productos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="stats-row animate__animated animate__fadeInDown">
        <div class="stat-card">
            <div class="stat-num" id="statTotal">0</div>
            <div class="stat-lbl">Total productos</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num" id="statActivos">0</div>
            <div class="stat-lbl">Activos</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-num" id="statStockBajo">0</div>
            <div class="stat-lbl">Stock bajo mínimo</div>
        </div>
        <div class="stat-card gray">
            <div class="stat-num" id="statInactivos">0</div>
            <div class="stat-lbl">Inactivos</div>
        </div>
    </div>

    <div class="page-header animate__animated animate__fadeIn animate__delay-1s">
        <div>
            <h2 class="page-title">Productos</h2>
            <p class="page-subtitle">Gestión del catálogo de productos</p>
        </div>
        <div class="header-actions">
            <button class="btn-categorias" onclick="abrirModalCategorias()">Categorías</button>
            <button class="btn-nuevo" onclick="abrirModalProducto()">+ Nuevo Producto</button>
        </div>
    </div>

    <div class="filtros-bar animate__animated animate__fadeIn animate__delay-1s">
        <input type="text" id="buscador" class="filtro-input" placeholder="Buscar por nombre o código de barras..." oninput="filtrarTabla()">
        <select id="filtroCategoria" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todas las categorías</option>
            <option value="Analgésicos">Analgésicos</option>
            <option value="Antibióticos">Antibióticos</option>
            <option value="Vitaminas">Vitaminas</option>
            <option value="Jarabes">Jarabes</option>
            <option value="Higiene personal">Higiene personal</option>
        </select>
        <select id="filtroEstado" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>
    </div>

    <div class="tabla-card animate__animated animate__fadeInUp animate__delay-1s">
        <div class="tabla-header-bar">
            <span>Mostrando <strong id="contadorVisible">0</strong> de <strong id="contadorTotal">0</strong> productos</span>
            <span>DNS Pharmacy · Inventario</span>
        </div>
        <table class="tabla-productos" id="tablaProductos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Imagen</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>P. Compra</th>
                    <th>P. Venta</th>
                    <th>Stock</th>
                    <th>Receta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr>
                    <td colspan="11" class="tabla-vacia">No hay productos registrados.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>

<!-- MODAL PRODUCTO - MÁS ANCHO + CAMPOS EN 3-4 COLUMNAS (RESPETANDO CAMPOS ORIGINALES) -->
<div class="modal-overlay" id="modalProducto">
    <div class="modal-box modal-grande animate__animated animate__zoomIn animate__faster">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalProducto">Nuevo Producto</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalProducto')">&times;</button>
        </div>
        <form id="formProducto" novalidate enctype="multipart/form-data">
            <input type="hidden" id="prod_id" name="id_producto">
            <input type="hidden" id="prod_imagen_actual" name="imagen_actual">
            <div class="modal-body">
                <!-- SECCIÓN: Información general - 3 columnas -->
                <div class="form-seccion">Información general</div>
                <div class="form-row-custom-3">
                    <div class="form-group-custom">
                        <label>Nombre <span class="req">*</span></label>
                        <input type="text" id="prod_nombre" name="nombre" class="form-input" placeholder="Ej. Paracetamol 500mg">
                        <span class="form-error" id="err_nombre"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Código de barras <span class="req">*</span></label>
                        <input type="text" id="prod_codigo" name="codigo_barras" class="form-input" placeholder="Ej. 7501234567890">
                        <span class="form-error" id="err_codigo"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Categoría <span class="req">*</span></label>
                        <select id="prod_categoria" name="id_categoria" class="form-input">
                            <option value="">Seleccionar categoría</option>
                            <option value="1">Analgésicos</option>
                            <option value="2">Antibióticos</option>
                            <option value="3">Vitaminas</option>
                            <option value="4">Jarabes</option>
                            <option value="5">Higiene personal</option>
                        </select>
                        <span class="form-error" id="err_categoria"></span>
                    </div>
                </div>

                <div class="form-row-custom-3">
                    <div class="form-group-custom">
                        <label>Unidad de medida <span class="req">*</span></label>
                        <select id="prod_unidad" name="unidad_medida" class="form-input">
                            <option value="">Seleccionar</option>
                            <option value="unidad">Unidad</option>
                            <option value="caja">Caja</option>
                            <option value="blister">Blíster</option>
                            <option value="frasco">Frasco</option>
                            <option value="ampolla">Ampolla</option>
                            <option value="sobre">Sobre</option>
                            <option value="tubo">Tubo</option>
                        </select>
                        <span class="form-error" id="err_unidad"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Descripción</label>
                        <textarea id="prod_descripcion" name="descripcion" class="form-input" rows="2" placeholder="Descripción breve del producto"></textarea>
                    </div>
                    <div class="form-group-custom">
                        <label>Presentación</label>
                        <input type="text" id="prod_presentacion" name="presentacion" class="form-input" placeholder="Ej. Tabletas, Jarabe">
                    </div>
                </div>

                <div class="form-row-custom-3">
                    <div class="form-group-custom">
                        <label>Marca</label>
                        <input type="text" id="prod_marca" name="marca" class="form-input" placeholder="Ej. Bayer">
                    </div>
                    <div class="form-group-custom">
                        <label>Laboratorio</label>
                        <input type="text" id="prod_laboratorio" name="laboratorio" class="form-input" placeholder="Ej. Laboratorio MK">
                    </div>
                    <div class="form-group-custom">
                        <label>Concentración</label>
                        <input type="text" id="prod_concentracion" name="concentracion" class="form-input" placeholder="Ej. 500mg, 250ml">
                    </div>
                </div>

                <!-- SECCIÓN: Precios e inventario - 4 columnas -->
                <div class="form-seccion">Precios e inventario</div>
                <div class="form-row-custom-4">
                    <div class="form-group-custom">
                        <label>Precio de compra <span class="req">*</span></label>
                        <input type="number" id="prod_precio_compra" name="precio_compra" class="form-input" placeholder="0.00" step="0.01" min="0">
                        <span class="form-error" id="err_precio_compra"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Precio de venta <span class="req">*</span></label>
                        <input type="number" id="prod_precio_venta" name="precio_venta" class="form-input" placeholder="0.00" step="0.01" min="0">
                        <span class="form-error" id="err_precio_venta"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Stock actual <span class="req">*</span></label>
                        <input type="number" id="prod_stock_actual" name="stock_actual" class="form-input" placeholder="0" min="0">
                        <span class="form-error" id="err_stock_actual"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Stock mínimo <span class="req">*</span></label>
                        <input type="number" id="prod_stock_minimo" name="stock_minimo" class="form-input" placeholder="0" min="0">
                        <span class="form-error" id="err_stock_minimo"></span>
                    </div>
                </div>

                <!-- SECCIÓN: Imagen y configuración - 3 columnas -->
                <div class="form-seccion">Imagen y configuración</div>
                <div class="form-row-custom-3">
                    <div class="form-group-custom">
                        <label>Imagen del producto</label>
                        <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('prod_imagen').click()">
                            <div class="file-upload-placeholder" id="filePlaceholder">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Haz clic para seleccionar una imagen</span>
                                <small>JPG, PNG, WEBP — máx. 2MB</small>
                            </div>
                            <div class="img-preview" id="preview-imagen" style="display:none;">
                                <img id="img-preview-src" src="" alt="Vista previa">
                                <div class="img-preview-info">
                                    <span id="img-preview-nombre" class="img-nombre"></span>
                                    <button type="button" class="btn-quitar-img" onclick="event.stopPropagation(); quitarImagen()">Quitar</button>
                                </div>
                            </div>
                        </div>
                        <input type="file" id="prod_imagen" name="imagen_url" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;" onchange="previsualizarImagen(this)">
                        <span class="form-error" id="err_imagen"></span>
                    </div>
                    <div class="form-group-custom checks-group">
                        <label class="check-label">
                            <input type="checkbox" id="prod_receta" name="requiere_receta" value="1">
                            <span>Requiere receta</span>
                        </label>
                        <label class="check-label">
                            <input type="checkbox" id="prod_estado" name="estado" value="1" checked>
                            <span>Producto activo</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalProducto')">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar producto</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-box modal-chico animate__animated animate__headShake">
        <div class="modal-header">
            <h5 class="modal-titulo">Eliminar producto</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalEliminar')">&times;</button>
        </div>
        <div class="modal-body">
            <p class="eliminar-texto">¿Estás seguro que deseas eliminar <strong id="nombreEliminar"></strong>?</p>
            <p class="eliminar-aviso">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEliminar')">Cancelar</button>
            <button type="button" class="btn-eliminar" id="btnConfirmarEliminar">Sí, eliminar</button>
        </div>
    </div>
</div>

<!-- MODAL CATEGORÍAS -->
<div class="modal-overlay" id="modalCategorias">
    <div class="modal-box modal-mediano animate__animated animate__fadeInDown animate__faster">
        <div class="modal-header">
            <h5 class="modal-titulo">Gestión de Categorías</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalCategorias')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formCategoria" novalidate>
                <input type="hidden" id="cat_id" name="id_categoria">
                <div class="form-row-custom-2">
                    <div class="form-group-custom">
                        <label>Nombre <span class="req">*</span></label>
                        <input type="text" id="cat_nombre" name="nombre" class="form-input" placeholder="Ej. Antibióticos">
                        <span class="form-error" id="err_cat_nombre"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Descripción</label>
                        <input type="text" id="cat_descripcion" name="descripcion" class="form-input" placeholder="Descripción breve">
                    </div>
                </div>
                <div class="cat-form-footer">
                    <label class="check-label">
                        <input type="checkbox" id="cat_estado" name="estado" value="1" checked>
                        <span>Categoría activa</span>
                    </label>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="btn-cancelar" id="btnCancelarCategoria" onclick="limpiarFormCategoria()" style="display:none;">Cancelar</button>
                        <button type="submit" class="btn-guardar" id="btnGuardarCategoria">Agregar</button>
                    </div>
                </div>
            </form>
            <hr class="separador-cat">
            <table class="tabla-categorias" id="tablaCategorias">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaCategoria">
                    <!-- Las categorías se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/productos.js"></script>

<script>
    // Funciones auxiliares para la vista previa de imagen
    function previsualizarImagen(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img-preview-src').src = e.target.result;
                document.getElementById('preview-imagen').style.display = 'flex';
                document.getElementById('filePlaceholder').style.display = 'none';
                document.getElementById('img-preview-nombre').textContent = input.files[0].name;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function quitarImagen() {
        document.getElementById('prod_imagen').value = '';
        document.getElementById('preview-imagen').style.display = 'none';
        document.getElementById('filePlaceholder').style.display = 'flex';
        document.getElementById('img-preview-src').src = '';
    }

    // Funciones para abrir/cerrar modales
    function abrirModalProducto() {
        document.getElementById('modalProducto').classList.add('activo');
        document.getElementById('tituloModalProducto').textContent = 'Nuevo Producto';
        document.getElementById('formProducto').reset();
        document.getElementById('prod_id').value = '';
        document.getElementById('prod_estado').checked = true;
        quitarImagen();
    }

    function abrirModalCategorias() {
        document.getElementById('modalCategorias').classList.add('activo');
        cargarCategorias();
    }

    function cerrarModal(modalId) {
        document.getElementById(modalId).classList.remove('activo');
    }

    function limpiarFormCategoria() {
        document.getElementById('formCategoria').reset();
        document.getElementById('cat_id').value = '';
        document.getElementById('btnGuardarCategoria').textContent = 'Agregar';
        document.getElementById('btnCancelarCategoria').style.display = 'none';
        document.getElementById('cat_estado').checked = true;
    }

    function cargarCategorias() {
        // Aquí iría la lógica para cargar categorías desde el backend
        console.log('Cargando categorías...');
    }

    function filtrarTabla() {
        // Implementar lógica de filtrado
        console.log('Filtrando tabla...');
    }

    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('activo');
        }
    }
</script>

</body>
</html>