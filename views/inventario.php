<!doctype html>
<html lang="es">
<head>
    <title>Inventario - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/inventario.css">
    
   <style>
        /* Animación  las Cards al pasar el mouse */
        .stat-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 14px 28px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.08);
        }
        /* Efecto sutil para la tabla */
        .tabla-card {
            transition: transform 0.3s ease;
        }
        .tabla-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}
include 'layouts/slider.php';
?>

<div class="main-content">

<<<<<<< HEAD
    <div class="stats-row animate__animated animate__fadeInDown">
=======
    <div class="stats-row">
>>>>>>> FrontEnd1
        <div class="stat-card">
            <div class="stat-num" id="statTotalCompras">0</div>
            <div class="stat-lbl">Compras registradas</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num" id="statProductos">0</div>
            <div class="stat-lbl">Productos en stock</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-num" id="statStockBajo">0</div>
            <div class="stat-lbl">Stock bajo mínimo</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-num" id="statInversion">$0</div>
            <div class="stat-lbl">Inversión total</div>
        </div>
    </div>

<<<<<<< HEAD
    <div class="inv-tabs animate__animated animate__fadeIn animate__delay-1s">
=======
    <div class="inv-tabs">
>>>>>>> FrontEnd1
        <button class="inv-tab active" onclick="cambiarTab(this,'tabStock')">
            <i class="bi bi-boxes"></i> Stock actual
        </button>
        <button class="inv-tab" onclick="cambiarTab(this,'tabHistorial')">
            <i class="bi bi-clock-history"></i> Historial de compras
        </button>
    </div>

<<<<<<< HEAD
    <div id="tabStock" class="tab-content active-tab animate__animated animate__fadeIn">
=======
    <!-- TAB: STOCK -->
    <div id="tabStock" class="tab-content active-tab">
>>>>>>> FrontEnd1
        <div class="tab-header">
            <div class="tab-filtros">
                <input type="text" id="buscadorStock" class="filtro-input" placeholder="Buscar producto..." oninput="filtrarStock()">
                <select id="filtroEstadoStock" class="filtro-select" onchange="filtrarStock()">
                    <option value="">Todos</option>
                    <option value="ok">Stock OK</option>
                    <option value="bajo">Stock bajo</option>
                    <option value="agotado">Agotado</option>
                </select>
            </div>
            <button class="btn-nueva-compra" onclick="abrirModalCompra()">
                <i class="bi bi-plus-circle"></i> Registrar Compra
            </button>
        </div>
        <div class="tabla-card">
            <div class="tabla-header-bar">
                <span>Stock actual — <strong id="contadorStock">0</strong> productos</span>
                <span>DNS Pharmacy · Inventario</span>
            </div>
            <table class="tabla-inv">
                <thead>
                    <tr>
                        <th>#</th><th>Producto</th><th>Categoría</th><th>Código</th>
                        <th>Stock actual</th><th>Stock mínimo</th><th>P. Compra</th><th>P. Venta</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody id="cuerpoStock">
                    <tr><td colspan="9" class="tabla-vacia">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<<<<<<< HEAD
    <div id="tabHistorial" class="tab-content animate__animated animate__fadeIn">
=======
    <!-- TAB: HISTORIAL -->
    <div id="tabHistorial" class="tab-content">
>>>>>>> FrontEnd1
        <div class="tab-header">
            <div class="tab-filtros">
                <input type="text" id="buscadorCompras" class="filtro-input" placeholder="Buscar por N° factura o proveedor..." oninput="filtrarCompras()">
                <select id="filtroEstadoCompra" class="filtro-select" onchange="filtrarCompras()">
                    <option value="">Todos los estados</option>
                    <option value="registrada">Registrada</option>
                    <option value="anulada">Anulada</option>
                </select>
            </div>
            <button class="btn-nueva-compra" onclick="abrirModalCompra()">
                <i class="bi bi-plus-circle"></i> Registrar Compra
            </button>
        </div>
        <div class="tabla-card">
            <div class="tabla-header-bar">
                <span>Historial — <strong id="contadorCompras">0</strong> compras</span>
                <span>DNS Pharmacy · Inventario</span>
            </div>
            <table class="tabla-inv">
                <thead>
                    <tr>
                        <th>#</th><th>N° Factura</th><th>Proveedor</th><th>Fecha</th>
                        <th>Productos</th><th>Subtotal</th><th>IVA</th><th>Total</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoCompras">
                    <tr><td colspan="10" class="tabla-vacia">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<div class="modal-overlay" id="modalCompra">
    <div class="modal-box modal-xl animate__animated animate__slideInUp animate__faster">
        <div class="modal-header">
            <h5 class="modal-titulo"><i class="bi bi-cart-plus"></i> Registrar Compra</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalCompra')">&times;</button>
        </div>
        <form id="formCompra" novalidate style="display:flex;flex-direction:column;flex:1;overflow:hidden;min-height:0;">
            <div class="modal-body">
                <div class="form-seccion">Datos de la compra</div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Proveedor <span class="req">*</span></label>
                        <select id="comp_proveedor" name="id_proveedor" class="form-input">
                            <option value="">Seleccionar proveedor</option>
                        </select>
                        <span class="form-error" id="err_proveedor"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>N° Factura <span class="req">*</span></label>
                        <input type="text" id="comp_factura" name="numero_factura" class="form-input" placeholder="Ej. FAC-2025-001">
                        <span class="form-error" id="err_factura"></span>
                    </div>
                </div>
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label>Fecha de compra <span class="req">*</span></label>
                        <input type="date" id="comp_fecha" name="fecha_compra" class="form-input">
                        <span class="form-error" id="err_fecha"></span>
                    </div>
                    <div class="form-group-custom">
                        <label>Observaciones</label>
                        <input type="text" id="comp_obs" name="observaciones" class="form-input" placeholder="Notas adicionales">
                    </div>
                </div>

                <div class="form-seccion">
                    Productos
                    <button type="button" class="btn-add-prod" onclick="agregarFilaProducto()">
                        <i class="bi bi-plus"></i> Agregar producto
                    </button>
                </div>

                <div class="tabla-compra-wrap">
                    <table class="tabla-compra-detalle">
                        <thead>
                            <tr>
                                <th>Producto</th><th>Cantidad</th><th>Costo unitario</th>
                                <th>N° Lote</th><th>Fecha venc.</th><th>Subtotal</th><th></th>
                            </tr>
                        </thead>
                        <tbody id="detalleCompra"></tbody>
                    </table>
                </div>

                <span class="form-error" id="err_productos"></span>

                <!-- Toggle IVA -->
                <div class="iva-toggle" style="margin-top:12px;">
                    <span class="iva-label">Aplicar IVA (13%)</span>
                    <label class="switch">
                        <input type="checkbox" id="toggleIvaCompra" onchange="calcularTotalesCompra()">
                        <span class="slider-switch"></span>
                    </label>
                </div>

                <div class="compra-totales">
                    <div class="compra-total-row"><span>Subtotal</span><span id="compSubtotal">$0.00</span></div>
                    <div class="compra-total-row iva-row" id="ivaRowCompra"><span>IVA (13%)</span><span id="compIva">$0.00</span></div>
                    <div class="compra-total-row compra-total-final"><span>Total</span><span id="compTotal">$0.00</span></div>
                </div>
            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCompra')">Cancelar</button>
                <button type="submit" class="btn-guardar">
                    <i class="bi bi-save"></i> Guardar compra
                </button>
            </div>
        </form>
    </div>
</div>


<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box modal-mediano animate__animated animate__zoomIn animate__faster">
        <div class="modal-header">
            <h5 class="modal-titulo"><i class="bi bi-file-earmark-text"></i> Detalle de compra</h5>
            <button class="modal-cerrar" onclick="cerrarModal('modalDetalle')">&times;</button>
        </div>
        <div class="modal-body" id="cuerpoDetalle">Cargando...</div>
        <div class="modal-footer-custom">
            <button class="btn-cancelar" onclick="cerrarModal('modalDetalle')">Cerrar</button>
            <button class="btn-imprimir" onclick="imprimirDetalle()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="../assets/js/inventario.js"></script>
</body>
</html>