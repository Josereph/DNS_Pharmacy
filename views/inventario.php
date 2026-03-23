<!doctype html>
<html lang="es">
<head>
    <title>Inventario - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/inventario.css">
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Inventario</h2>
            <p class="page-subtitle">Consulta de stock y movimientos de productos</p>
        </div>
        <div class="header-actions">
            <a href="compras.php" class="btn-nuevo">
                <i class="bi bi-cart-plus"></i> Registrar Compra
            </a>
        </div>
    </div>

    <!-- Tarjetas resumen -->
    <div class="stats-strip">
        <div class="stat-card">
            <div class="stat-icon stat-purple"><i class="bi bi-boxes"></i></div>
            <div>
                <div class="stat-valor" id="statTotal">0</div>
                <div class="stat-label">Total productos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-green"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="stat-valor" id="statOk">0</div>
                <div class="stat-label">Stock normal</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-amber"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-valor" id="statBajo">0</div>
                <div class="stat-label">Stock bajo</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-red"><i class="bi bi-x-circle"></i></div>
            <div>
                <div class="stat-valor" id="statAgotado">0</div>
                <div class="stat-label">Agotados</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
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
        <select id="filtroStock" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todo el stock</option>
            <option value="normal">Stock normal</option>
            <option value="bajo">Stock bajo</option>
            <option value="agotado">Agotado</option>
        </select>
    </div>

    <!-- Tabla — campos de tabla Productos -->
    <div class="tabla-card">
        <table class="tabla-productos" id="tablaInventario">
            <thead>
                <tr>
                    <th>#</th>
                    <th>codigo_barras</th>
                    <th>nombre</th>
                    <th>Categoría</th>
                    <th>stock_actual</th>
                    <th>stock_minimo</th>
                    <th>unidad_medida</th>
                    <th>precio_venta</th>
                    <th>Estado stock</th>
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


<!-- MODAL: VER MOVIMIENTOS — tabla Movimientos_Inventario -->
<div class="modal-overlay" id="modalMovimientos">
    <div class="modal-box modal-grande">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalMov">Movimientos del producto</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalMovimientos')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoMovimientos"></div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalMovimientos')">Cerrar</button>
        </div>
    </div>
</div>


<!-- MODAL: AJUSTE MANUAL — inserta en Movimientos_Inventario y actualiza stock_actual en Productos -->
<div class="modal-overlay" id="modalAjuste">
    <div class="modal-box modal-chico">
        <div class="modal-header">
            <h5 class="modal-titulo">Ajuste de stock</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalAjuste')">&times;</button>
        </div>
        <div class="modal-body">

            <!-- id_producto — FK hacia Productos -->
            <input type="hidden" id="ajuste_id_producto" name="id_producto">
            <!-- id_lote — FK hacia Lotes (NULL si no aplica) -->
            <input type="hidden" id="ajuste_id_lote" name="id_lote" value="">
            <!-- id_usuario — FK hacia Usuarios (viene de sesión) -->
            <input type="hidden" id="ajuste_id_usuario" name="id_usuario">

            <p class="ajuste-producto" id="ajusteNombreProducto"></p>

            <div class="form-group-custom">
                <label>Tipo de ajuste <span class="req">*</span></label>
                <!-- tipo_movimiento: 'entrada' | 'salida' | 'ajuste' -->
                <select id="ajuste_tipo" name="tipo_movimiento" class="form-input">
                    <option value="entrada">Entrada (sumar stock)</option>
                    <option value="salida">Salida (restar stock)</option>
                    <option value="ajuste">Ajuste directo</option>
                </select>
            </div>

            <div class="form-group-custom">
                <label>Referencia</label>
                <!-- referencia: número de ticket, venta, etc. -->
                <input type="text" id="ajuste_referencia" name="referencia" class="form-input" placeholder="Ej. TK-00001 (opcional)">
            </div>

            <div class="form-group-custom">
                <label>Cantidad <span class="req">*</span></label>
                <!-- cantidad -->
                <input type="number" id="ajuste_cantidad" name="cantidad" class="form-input" placeholder="0" min="1">
                <span class="form-error" id="err_ajuste_cantidad"></span>
            </div>

            <!-- stock_anterior y stock_nuevo los calcula el JS antes de enviar -->
            <input type="hidden" id="ajuste_stock_anterior" name="stock_anterior">
            <input type="hidden" id="ajuste_stock_nuevo"    name="stock_nuevo">

            <div class="form-group-custom">
                <label>Motivo</label>
                <!-- motivo -->
                <input type="text" id="ajuste_motivo" name="motivo" class="form-input" placeholder="Ej. Corrección de inventario">
            </div>

            <!-- fecha_movimiento la asigna el JS o el backend -->
            <input type="hidden" id="ajuste_fecha_movimiento" name="fecha_movimiento">

        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalAjuste')">Cancelar</button>
            <button type="button" class="btn-guardar" onclick="guardarAjuste()">Aplicar ajuste</button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/inventario.js"></script>
</body>
</html>