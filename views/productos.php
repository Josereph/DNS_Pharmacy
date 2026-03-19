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
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Productos</h2>
            <p class="page-subtitle">Gestión del catálogo de productos</p>
        </div>
        <div class="header-actions">
            <button class="btn-categorias" onclick="abrirModalCategorias()">Categorías</button>
            <button class="btn-nuevo" onclick="abrirModalProducto()">+ Nuevo Producto</button>
        </div>
    </div>

    <div class="filtros-bar">
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

    <div class="tabla-card">
        <table class="tabla-productos" id="tablaProductos">
            <thead>
                <tr>
                    <th>#</th>
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
                    <td colspan="10" class="tabla-vacia">No hay productos registrados.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<!-- MODAL: PRODUCTO -->
<div class="modal-overlay" id="modalProducto">
    <div class="modal-box modal-grande">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalProducto">Nuevo Producto</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalProducto')">&times;</button>
        </div>
        <form id="formProducto" novalidate>
            <input type="hidden" id="prod_id" name="id_producto">
            <div class="modal-body">

                <div class="form-seccion">Información general</div>
                <div class="form-row-custom">
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
                </div>
                <div class="form-row-custom">
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
                </div>
                <div class="form-group-custom">
                    <label>Descripción</label>
                    <textarea id="prod_descripcion" name="descripcion" class="form-input" rows="2" placeholder="Descripción breve del producto"></textarea>
                </div>

                <div class="form-seccion">Detalles del producto</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Presentación</label>
                        <input type="text" id="prod_presentacion" name="presentacion" class="form-input" placeholder="Ej. Tabletas, Jarabe">
                    </div>
                    <div class="form-group-custom">
                        <label>Marca</label>
                        <input type="text" id="prod_marca" name="marca" class="form-input" placeholder="Ej. Bayer">
                    </div>
                </div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Laboratorio</label>
                        <input type="text" id="prod_laboratorio" name="laboratorio" class="form-input" placeholder="Ej. Laboratorio MK">
                    </div>
                    <div class="form-group-custom">
                        <label>Concentración</label>
                        <input type="text" id="prod_concentracion" name="concentracion" class="form-input" placeholder="Ej. 500mg, 250ml">
                    </div>
                </div>

                <div class="form-seccion">Precios e inventario</div>
                <div class="form-row-custom">
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
                </div>
                <div class="form-row-custom">
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

                <div class="form-seccion">Configuración</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>URL de imagen</label>
                        <input type="text" id="prod_imagen" name="imagen_url" class="form-input" placeholder="assets/img/productos/imagen.jpg">
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


<!-- MODAL: CONFIRMAR ELIMINAR PRODUCTO -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-box modal-chico">
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


<!-- MODAL: CATEGORÍAS -->
<div class="modal-overlay" id="modalCategorias">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo">Gestión de Categorías</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalCategorias')">&times;</button>
        </div>
        <div class="modal-body">

            <form id="formCategoria" novalidate>
                <input type="hidden" id="cat_id" name="id_categoria">
                <div class="form-row-custom">
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
                    <tr>
                        <td>1</td><td>Analgésicos</td><td>Medicamentos para aliviar dolor</td>
                        <td><span class="badge-activo">Activo</span></td>
                        <td>
                            <button class="btn-accion btn-editar" onclick="editarCategoria(1,'Analgésicos','Medicamentos para aliviar dolor',1)">Editar</button>
                            <button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminarCategoria(1,'Analgésicos')">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td><td>Antibióticos</td><td>Medicamentos para infecciones bacterianas</td>
                        <td><span class="badge-activo">Activo</span></td>
                        <td>
                            <button class="btn-accion btn-editar" onclick="editarCategoria(2,'Antibióticos','Medicamentos para infecciones bacterianas',1)">Editar</button>
                            <button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminarCategoria(2,'Antibióticos')">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td><td>Vitaminas</td><td>Suplementos vitamínicos</td>
                        <td><span class="badge-activo">Activo</span></td>
                        <td>
                            <button class="btn-accion btn-editar" onclick="editarCategoria(3,'Vitaminas','Suplementos vitamínicos',1)">Editar</button>
                            <button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminarCategoria(3,'Vitaminas')">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td><td>Jarabes</td><td>Medicamentos líquidos orales</td>
                        <td><span class="badge-activo">Activo</span></td>
                        <td>
                            <button class="btn-accion btn-editar" onclick="editarCategoria(4,'Jarabes','Medicamentos líquidos orales',1)">Editar</button>
                            <button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminarCategoria(4,'Jarabes')">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>5</td><td>Higiene personal</td><td>Productos de aseo e higiene</td>
                        <td><span class="badge-activo">Activo</span></td>
                        <td>
                            <button class="btn-accion btn-editar" onclick="editarCategoria(5,'Higiene personal','Productos de aseo e higiene',1)">Editar</button>
                            <button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminarCategoria(5,'Higiene personal')">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- MODAL: CONFIRMAR ELIMINAR CATEGORÍA -->
<div class="modal-overlay" id="modalEliminarCategoria">
    <div class="modal-box modal-chico">
        <div class="modal-header">
            <h5 class="modal-titulo">Eliminar categoría</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalEliminarCategoria')">&times;</button>
        </div>
        <div class="modal-body">
            <p class="eliminar-texto">¿Estás seguro que deseas eliminar la categoría <strong id="nombreEliminarCat"></strong>?</p>
            <p class="eliminar-aviso">Los productos asociados quedarán sin categoría.</p>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEliminarCategoria')">Cancelar</button>
            <button type="button" class="btn-eliminar" id="btnConfirmarEliminarCat">Sí, eliminar</button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/productos.js"></script>
</body>
</html>