<!doctype html>
<html lang="es">
<head>
    <title>POS - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/pos.css">
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

<div class="main-content pos-layout">

    <!-- ══ PANEL IZQUIERDO ══ -->
    <div class="pos-left">

        <div class="pos-topbar">
            <div class="pos-topbar-left">
                <span class="pos-title">Punto de Venta</span>
                <span class="pos-date" id="posDate"></span>
            </div>
            <div class="pos-topbar-right">
                <div class="turno-badge">
                    <i class="bi bi-circle-fill"></i>
                    <span>Turno activo</span>
                </div>
            </div>
        </div>

        <div class="pos-search-row">
            <div class="pos-search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="buscadorPos" placeholder="Buscar producto por nombre o código de barras..." autocomplete="off" oninput="buscarProducto()">
                <button class="btn-scan" title="Escanear código">
                    <i class="bi bi-upc-scan"></i>
                </button>
            </div>
        </div>

        <div class="pos-cats" id="posCats">
            <button class="cat-tab active" data-cat="" onclick="filtrarCategoria(this, '')">Todos</button>
        </div>

        <div class="pos-grid" id="posGrid">
            <div class="pos-loading">
                <i class="bi bi-arrow-repeat"></i>
                Cargando productos...
            </div>
        </div>

    </div>

    <!-- ══ PANEL DERECHO — CARRITO ══ -->
    <div class="pos-right">

        <div class="cart-header">
            <div class="cart-title">
                <i class="bi bi-cart3"></i>
                Venta actual
                <span class="cart-count" id="cartCount">0</span>
            </div>
            <button class="btn-clear" onclick="limpiarCarrito()" title="Cancelar venta">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty">
                <i class="bi bi-cart-x"></i>
                <span>Carrito vacío</span>
            </div>
        </div>

        <!-- Totales -->
        <div class="cart-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span id="totalSubtotal">$0.00</span>
            </div>

            <!-- Descuento aplicado -->
            <div class="total-row descuento-row" id="descuentoRow" style="display:none;">
                <span class="descuento-label">
                    <i class="bi bi-tag-fill"></i>
                    Descuento <span id="descuentoDesc"></span>
                </span>
                <span id="totalDescuento" class="descuento-val">-$0.00</span>
            </div>

            <!-- Toggle IVA -->
            <div class="iva-toggle">
                <span class="iva-label">Aplicar IVA (13%)</span>
                <label class="switch">
                    <input type="checkbox" id="toggleIva" onchange="calcularTotales()">
                    <span class="slider-switch"></span>
                </label>
            </div>
            <div class="total-row iva-row" id="ivaRow">
                <span>IVA (13%)</span>
                <span id="totalIva">$0.00</span>
            </div>
            <div class="total-row total-final">
                <span>Total</span>
                <span id="totalFinal">$0.00</span>
            </div>
        </div>

        <!-- Botón descuento -->
        <div class="descuento-btn-wrap">
            <button class="btn-descuento" id="btnDescuento" onclick="abrirModalDescuento()" disabled>
                <i class="bi bi-tag"></i>
                Aplicar descuento
            </button>
        </div>

        <!-- Método de pago -->
        <div class="pay-section">
            <div class="pay-label">Método de pago</div>
            <div class="pay-btns">
                <button class="pay-btn active" data-metodo="efectivo" onclick="seleccionarMetodo(this)">
                    <i class="bi bi-cash-stack"></i>
                    Efectivo
                </button>
                <button class="pay-btn" data-metodo="tarjeta" onclick="seleccionarMetodo(this)">
                    <i class="bi bi-credit-card"></i>
                    Tarjeta
                </button>
                <button class="pay-btn" data-metodo="transferencia" onclick="seleccionarMetodo(this)">
                    <i class="bi bi-phone"></i>
                    Digital
                </button>
            </div>
        </div>

        <div class="pay-calc" id="payCalc">
            <div class="pay-calc-row">
                <label>Monto recibido</label>
                <input type="number" id="montoRecibido" class="monto-input" placeholder="$0.00" step="0.01" min="0" oninput="calcularCambio()">
            </div>
            <div class="pay-calc-row cambio-row">
                <span>Cambio</span>
                <span class="cambio-val" id="cambioVal">$0.00</span>
            </div>
        </div>

        <button class="btn-cobrar" id="btnCobrar" onclick="procesarVenta()" disabled>
            <i class="bi bi-receipt"></i>
            Confirmar y generar ticket
        </button>

    </div>
</div>


<!-- ══ MODAL: DESCUENTO ══ -->
<div class="modal-overlay" id="modalDescuento">
    <div class="modal-box modal-descuento">
        <div class="modal-header">
            <h5 class="modal-titulo"><i class="bi bi-tag-fill"></i> Aplicar Descuento</h5>
            <button class="modal-cerrar" onclick="cerrarModalDescuento()">&times;</button>
        </div>
        <div class="modal-body">

            <!-- Tipo de descuento -->
            <div class="descuento-tipo-tabs">
                <button class="tipo-tab active" data-tipo="porcentaje" onclick="seleccionarTipoDescuento(this, 'porcentaje')">
                    <i class="bi bi-percent"></i>
                    Porcentaje
                </button>
                <button class="tipo-tab" data-tipo="monto" onclick="seleccionarTipoDescuento(this, 'monto')">
                    <i class="bi bi-currency-dollar"></i>
                    Monto fijo
                </button>
            </div>

            <!-- Input descuento -->
            <div class="descuento-input-wrap">
                <div class="descuento-prefix" id="descuentoPrefix">%</div>
                <input type="number" id="inputDescuento" class="descuento-input"
                       placeholder="0" min="0" step="0.01"
                       oninput="previsualizarDescuento()">
            </div>

            <span class="form-error" id="errDescuento"></span>

            <!-- Preview cálculo -->
            <div class="descuento-preview" id="descuentoPreview">
                <div class="preview-row">
                    <span>Subtotal original</span>
                    <span id="prevSubtotal">$0.00</span>
                </div>
                <div class="preview-row descuento-preview-val">
                    <span id="prevDescLabel">Descuento (0%)</span>
                    <span id="prevDescMonto" class="text-descuento">-$0.00</span>
                </div>
                <div class="preview-divider"></div>
                <div class="preview-row preview-total">
                    <span>Subtotal con descuento</span>
                    <span id="prevTotal">$0.00</span>
                </div>
            </div>

            <!-- Accesos rápidos porcentaje -->
            <div class="descuento-rapidos" id="rapidosPorcentaje">
                <span class="rapidos-label">Accesos rápidos</span>
                <div class="rapidos-btns">
                    <button onclick="aplicarRapido(5)">5%</button>
                    <button onclick="aplicarRapido(10)">10%</button>
                    <button onclick="aplicarRapido(15)">15%</button>
                    <button onclick="aplicarRapido(20)">20%</button>
                    <button onclick="aplicarRapido(25)">25%</button>
                    <button onclick="aplicarRapido(50)">50%</button>
                </div>
            </div>

        </div>
        <div class="modal-footer-custom">
            <button class="btn-cancelar" onclick="quitarDescuento()">
                <i class="bi bi-x"></i> Quitar descuento
            </button>
            <button class="btn-aplicar-desc" onclick="confirmarDescuento()">
                <i class="bi bi-check-lg"></i> Aplicar
            </button>
        </div>
    </div>
</div>


<!-- ══ MODAL: TICKET ══ -->
<div class="modal-overlay" id="modalTicket">
    <div class="modal-box modal-ticket">
        <div class="modal-header">
            <h5 class="modal-titulo"><i class="bi bi-receipt"></i> Ticket de venta</h5>
            <button class="modal-cerrar" onclick="cerrarModalTicket()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="ticket-paper" id="ticketPaper">
                <div class="ticket-logo">
                    <img src="../assets/img/logo-dns.png" alt="DNS Pharmacy" style="height:50px;">
                </div>
                <div class="ticket-empresa">DNS Pharmacy</div>
                <div class="ticket-sub">Drug Network Supply</div>
                <div class="ticket-divider">--------------------------------</div>
                <div class="ticket-meta">
                    <div>Ticket: <strong id="tktNumero">#0000</strong></div>
                    <div>Fecha: <strong id="tktFecha"></strong></div>
                    <div>Cajero: <strong id="tktCajero"></strong></div>
                    <div>Método: <strong id="tktMetodo"></strong></div>
                </div>
                <div class="ticket-divider">--------------------------------</div>
                <table class="ticket-items" id="tktItems"></table>
                <div class="ticket-divider">--------------------------------</div>
                <div class="ticket-totales" id="tktTotales"></div>
                <div class="ticket-divider">--------------------------------</div>
                <div class="ticket-footer">¡Gracias por su compra!</div>
                <div class="ticket-footer">DNS Pharmacy · San Salvador, El Salvador</div>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button class="btn-cancelar" onclick="cerrarModalTicket()">Cerrar</button>
            <button class="btn-imprimir" onclick="imprimirTicket()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <button class="btn-guardar" onclick="nuevaVenta()">
                <i class="bi bi-plus-circle"></i> Nueva venta
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="../assets/js/pos.js"></script>
</body>
</html>