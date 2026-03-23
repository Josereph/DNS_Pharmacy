<!doctype html>
<html lang="es">
<head>
    <title>Registrar Compras - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/compras.css">
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Registrar Compras</h2>
            <p class="page-subtitle">Registro de compras a proveedores</p>
        </div>
        <div class="header-actions">
            <button class="btn-nuevo" onclick="abrirModalCompra()">
                <i class="bi bi-plus-lg"></i> Nueva Compra
            </button>
        </div>
    </div>

    <div class="filtros-bar">
        <input type="text" id="buscador" class="filtro-input"
               placeholder="Buscar por N° documento o proveedor..." oninput="filtrarTabla()">
        <select id="filtroTipo" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todos los tipos</option>
            <option value="Ticket">Ticket</option>
            <option value="Factura">Factura</option>
            <option value="Comprobante de crédito fiscal">Crédito fiscal</option>
            <option value="Recibo">Recibo</option>
        </select>
        <select id="filtroEstado" class="filtro-select" onchange="filtrarTabla()">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="completada">Completada</option>
            <option value="anulada">Anulada</option>
        </select>
    </div>

    <div class="tabla-card">
        <table class="tabla-productos" id="tablaCompras">
            <thead>
                <tr>
                    <th>#</th>
                    <th>N° Documento</th>
                    <th>Tipo</th>
                    <th>Proveedor</th>
                    <th>Fecha compra</th>
                    <th>Subtotal</th>
                    <th>Impuesto</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr>
                    <td colspan="10" class="tabla-vacia">No hay compras registradas.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<!-- ══════════════════════════════════════
     MODAL: NUEVA / EDITAR COMPRA
     Campos: id_compra | id_proveedor | id_usuario | numero_documento |
             tipo_documento | fecha_compra | subtotal | impuesto |
             total | observaciones | estado | created_at | updated_at
══════════════════════════════════════ -->
<div class="modal-overlay" id="modalCompra">
    <div class="modal-box modal-grande">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloModalCompra">Nueva Compra</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalCompra')">&times;</button>
        </div>
        <form id="formCompra" novalidate>

            <!-- Campos ocultos calculados por JS antes de enviar -->
            <input type="hidden" id="comp_id_compra"   name="id_compra">
            <input type="hidden" id="comp_id_usuario"  name="id_usuario">
            <input type="hidden" id="comp_subtotal"    name="subtotal">
            <input type="hidden" id="comp_impuesto"    name="impuesto">
            <input type="hidden" id="comp_total"       name="total">
            <input type="hidden" id="comp_created_at"  name="created_at">
            <input type="hidden" id="comp_updated_at"  name="updated_at">

            <div class="modal-body">

                <div class="form-seccion">Información del documento</div>
                <div class="form-row-custom">

                    <!-- id_proveedor -->
                    <div class="form-group-custom">
                        <label>Proveedor <span class="req">*</span></label>
                        <select id="comp_id_proveedor" name="id_proveedor" class="form-input">
                            <option value="">Seleccionar proveedor</option>
                            <option value="1">Distribuidora Médica S.A.</option>
                            <option value="2">Pharma Supply Co.</option>
                            <option value="3">MedTotal S.A. de C.V.</option>
                        </select>
                        <span class="form-error" id="err_proveedor"></span>
                    </div>

                    <!-- numero_documento -->
                    <div class="form-group-custom">
                        <label>N° Documento <span class="req">*</span></label>
                        <input type="text" id="comp_numero_documento" name="numero_documento"
                               class="form-input" placeholder="Ej. TK-00001">
                        <span class="form-error" id="err_numero"></span>
                    </div>

                </div>
                <div class="form-row-custom">

                    <!-- tipo_documento -->
                    <div class="form-group-custom">
                        <label>Tipo de documento <span class="req">*</span></label>
                        <select id="comp_tipo_documento" name="tipo_documento" class="form-input">
                            <option value="">Seleccionar tipo</option>
                            <option value="Ticket">Ticket</option>
                            <option value="Factura">Factura</option>
                            <option value="Comprobante de crédito fiscal">Comprobante de crédito fiscal</option>
                            <option value="Recibo">Recibo</option>
                        </select>
                        <span class="form-error" id="err_tipo"></span>
                    </div>

                    <!-- fecha_compra -->
                    <div class="form-group-custom">
                        <label>Fecha de compra <span class="req">*</span></label>
                        <input type="date" id="comp_fecha_compra" name="fecha_compra" class="form-input">
                        <span class="form-error" id="err_fecha"></span>
                    </div>

                </div>
                <div class="form-row-custom">

                    <!-- estado -->
                    <div class="form-group-custom">
                        <label>Estado</label>
                        <select id="comp_estado" name="estado" class="form-input">
                            <option value="pendiente">Pendiente</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>

                    <!-- observaciones -->
                    <div class="form-group-custom">
                        <label>Observaciones</label>
                        <input type="text" id="comp_observaciones" name="observaciones"
                               class="form-input" placeholder="Notas opcionales">
                    </div>

                </div>

                <div class="form-seccion">Detalle de productos</div>

                <div class="detalle-buscador-wrap">
                    <input type="text" id="buscarProducto" class="filtro-input"
                           placeholder="Buscar producto por nombre o código de barras..."
                           oninput="buscarProductoDetalle()">
                    <div class="detalle-sugerencias" id="sugerenciasProducto"></div>
                </div>

                <!-- Detalle_Compra: id_detalle_compra | id_compra | id_producto | cantidad | costo_unitario | subtotal -->
                <table class="tabla-detalle" id="tablaDetalle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo unitario</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoDetalle">
                        <tr>
                            <td colspan="5" class="tabla-vacia">Busca y agrega productos arriba.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-seccion">Totales</div>
                <div class="totales-grid">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span id="resSubtotal">$0.00</span>
                    </div>
                    <div class="total-row">
                        <span>Impuesto (13%)</span>
                        <div class="impuesto-wrap">
                            <label class="check-label">
                                <input type="checkbox" id="comp_aplica_impuesto" onchange="recalcularTotales()">
                                <span>Aplicar IVA</span>
                            </label>
                            <span id="resImpuesto">$0.00</span>
                        </div>
                    </div>
                    <div class="total-row total-final">
                        <span>Total</span>
                        <span id="resTotal">$0.00</span>
                    </div>
                </div>

            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCompra')">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar compra</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL: VER DETALLE COMPRA -->
<div class="modal-overlay" id="modalVerCompra">
    <div class="modal-box modal-grande">
        <div class="modal-header">
            <h5 class="modal-titulo">Detalle de Compra</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalVerCompra')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoVerCompra"></div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalVerCompra')">Cerrar</button>
        </div>
    </div>
</div>


<!-- MODAL: CONFIRMAR ANULAR -->
<div class="modal-overlay" id="modalAnular">
    <div class="modal-box modal-chico">
        <div class="modal-header">
            <h5 class="modal-titulo">Anular compra</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalAnular')">&times;</button>
        </div>
        <div class="modal-body">
            <p class="eliminar-texto">¿Estás seguro que deseas anular la compra <strong id="ticketAnular"></strong>?</p>
            <p class="eliminar-aviso">Esta acción revertirá el stock de los productos ingresados.</p>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalAnular')">Cancelar</button>
            <button type="button" class="btn-eliminar" id="btnConfirmarAnular">Sí, anular</button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="../assets/js/compras.js"></script>
</body>
</html>