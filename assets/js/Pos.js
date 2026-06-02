/* =====================
   POS.JS - DNS Pharmacy
   Con descuentos, IVA, escáner y reversión
   ===================== */
const POS_CONTROLLER  = '/DNS_Pharmacy/controllers/Poscontroller.php';
const PROD_CONTROLLER = '/DNS_Pharmacy/controllers/Productocontroller.php';

var carrito      = [];
var productos    = [];
var metodoActual = 'efectivo';

var descuento = {
    tipo:  'porcentaje',
    valor: 0,
    monto: 0
};

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    mostrarFecha();
    cargarProductos();
    actualizarCarritoUI();
    iniciarScanner();
});

function mostrarFecha() {
    var el = document.getElementById('posDate');
    if (el) el.textContent = new Date().toLocaleDateString('es-SV', {
        weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit'
    });
}

/* ══════════════════════════════════════════
   ESCÁNER
══════════════════════════════════════════ */
var _bufferScanner = '';
var _timerScanner  = null;

function iniciarScanner() {
    document.addEventListener('keydown', function(e) {
        var activo      = document.activeElement;
        var esBuscador  = activo && activo.id === 'buscadorPos';
        var esOtroInput = activo
            && (activo.tagName === 'INPUT' || activo.tagName === 'TEXTAREA' || activo.tagName === 'SELECT')
            && !esBuscador;
        if (esOtroInput) return;

        if (e.key === 'Enter') {
            var codigo = _bufferScanner.trim();
            _bufferScanner = '';
            clearTimeout(_timerScanner);
            if (codigo.length >= 3) {
                procesarCodigoEscaneado(codigo);
                var b = document.getElementById('buscadorPos');
                if (b) b.value = '';
            }
            return;
        }
        if (e.key.length === 1) {
            _bufferScanner += e.key;
            clearTimeout(_timerScanner);
            _timerScanner = setTimeout(function() { _bufferScanner = ''; }, 100);
        }
    });
}

function procesarCodigoEscaneado(codigo) {
    var prod = productos.find(function(p) {
        return String(p.codigo_barras).trim() === String(codigo).trim();
    });
    if (!prod) {
        mostrarToast('Código no encontrado: ' + codigo, 'error');
        var b = document.getElementById('buscadorPos');
        if (b) { b.value = codigo; buscarProducto(); }
        return;
    }
    if (parseInt(prod.stock_actual) <= 0) { mostrarToast(prod.nombre + ' — Sin stock.', 'error'); return; }
    agregarAlCarrito(prod.id_producto);
    var b = document.getElementById('buscadorPos');
    if (b) {
        b.value = '✓  ' + prod.nombre;
        b.style.background = '#e8f5e9'; b.style.color = '#2e7d32'; b.style.fontWeight = '600';
        setTimeout(function() { b.value = ''; b.style.background = ''; b.style.color = ''; b.style.fontWeight = ''; }, 1200);
    }
}

/* ══════════════════════════════════════════
   PRODUCTOS
══════════════════════════════════════════ */
function cargarProductos() {
    fetch(PROD_CONTROLLER + '?accion=listar')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            productos = res.datos;
            generarCategorias(productos);
            renderizarGrid(productos);
        });
}

function generarCategorias(prods) {
    var cats = {};
    prods.forEach(function(p) { if (p.nombre_categoria) cats[p.id_categoria] = p.nombre_categoria; });
    var container = document.getElementById('posCats');
    container.innerHTML = '<button class="cat-tab active" data-cat="" onclick="filtrarCategoria(this,\'\')">Todos</button>';
    Object.keys(cats).forEach(function(id) {
        var btn = document.createElement('button');
        btn.className = 'cat-tab'; btn.dataset.cat = id;
        btn.textContent = cats[id];
        btn.onclick = function() { filtrarCategoria(this, id); };
        container.appendChild(btn);
    });
}

function filtrarCategoria(btn, catId) {
    document.querySelectorAll('.cat-tab').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    renderizarGrid(catId ? productos.filter(function(p) { return p.id_categoria == catId; }) : productos);
}

function renderizarGrid(prods) {
    var grid = document.getElementById('posGrid');
    if (!prods || prods.length === 0) {
        grid.innerHTML = '<div class="pos-no-result"><i class="bi bi-search" style="font-size:28px;display:block;margin-bottom:8px"></i>Sin resultados</div>';
        return;
    }
    grid.innerHTML = prods.map(function(p) {
        var sinStock   = parseInt(p.stock_actual) <= 0;
        var imgHtml    = p.imagen_url
            ? '<img src="/DNS_Pharmacy/' + p.imagen_url + '" class="prod-img" alt="' + p.nombre + '">'
            : '<div class="prod-placeholder"><i class="bi bi-capsule"></i></div>';
        var stockClass = parseInt(p.stock_actual) <= parseInt(p.stock_minimo) ? 'bajo' : '';
        return '<div class="prod-card' + (sinStock ? ' sin-stock' : '') + '"'
             + (sinStock ? '' : ' onclick="agregarAlCarrito(' + p.id_producto + ')"') + '>'
             + imgHtml
             + '<div class="prod-nombre">' + p.nombre + '</div>'
             + '<div class="prod-precio">$' + parseFloat(p.precio_venta).toFixed(2) + '</div>'
             + '<div class="prod-stock ' + stockClass + '">Stock: ' + p.stock_actual + '</div>'
             + (sinStock ? '' : '<button class="prod-add-btn" onclick="event.stopPropagation();agregarAlCarrito(' + p.id_producto + ')"><i class="bi bi-plus"></i></button>')
             + '</div>';
    }).join('');
}

function buscarProducto() {
    var q = document.getElementById('buscadorPos').value.toLowerCase().trim();
    document.querySelectorAll('.cat-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelector('.cat-tab[data-cat=""]').classList.add('active');
    renderizarGrid(q ? productos.filter(function(p) {
        return p.nombre.toLowerCase().includes(q) || p.codigo_barras.toLowerCase().includes(q);
    }) : productos);
}

/* ══════════════════════════════════════════
   CARRITO
══════════════════════════════════════════ */
function agregarAlCarrito(id_producto) {
    var prod = productos.find(function(p) { return p.id_producto == id_producto; });
    if (!prod) return;
    if (parseInt(prod.stock_actual) <= 0) { mostrarToast('Sin stock disponible.', 'error'); return; }
    var item = carrito.find(function(i) { return i.id_producto == id_producto; });
    if (item) {
        if (item.cantidad >= parseInt(prod.stock_actual)) { mostrarToast('Stock máximo alcanzado.', 'error'); return; }
        item.cantidad++;
    } else {
        carrito.push({ id_producto: prod.id_producto, nombre: prod.nombre, precio: parseFloat(prod.precio_venta), cantidad: 1, stock_max: parseInt(prod.stock_actual) });
    }
    actualizarCarritoUI();
    mostrarToast(prod.nombre + ' agregado.', 'ok');
}

function cambiarCantidad(id_producto, delta) {
    var idx = carrito.findIndex(function(i) { return i.id_producto == id_producto; });
    if (idx === -1) return;
    carrito[idx].cantidad += delta;
    if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
    else if (carrito[idx].cantidad > carrito[idx].stock_max) { carrito[idx].cantidad = carrito[idx].stock_max; mostrarToast('Stock máximo.', 'error'); }
    actualizarCarritoUI();
}

function eliminarDelCarrito(id_producto) {
    carrito = carrito.filter(function(i) { return i.id_producto != id_producto; });
    actualizarCarritoUI();
}

function limpiarCarrito() {
    if (carrito.length === 0) return;
    if (!confirm('¿Cancelar la venta actual?')) return;
    carrito = []; resetDescuento(); actualizarCarritoUI();
}

function actualizarCarritoUI() {
    var container  = document.getElementById('cartItems');
    var count      = document.getElementById('cartCount');
    count.textContent = carrito.reduce(function(s, i) { return s + i.cantidad; }, 0);

    if (carrito.length === 0) {
        container.innerHTML = '<div class="cart-empty"><i class="bi bi-cart-x"></i><span>Carrito vacío</span></div>';
        resetDescuento();
    } else {
        container.innerHTML = carrito.map(function(item) {
            return '<div class="cart-item">'
                 + '<div style="flex:1"><div class="ci-nombre">' + item.nombre + '</div>'
                 + '<div class="ci-precio">$' + item.precio.toFixed(2) + ' c/u</div></div>'
                 + '<div class="ci-qty">'
                 + '<button class="qty-btn" onclick="cambiarCantidad(' + item.id_producto + ',-1)">−</button>'
                 + '<span class="qty-val">' + item.cantidad + '</span>'
                 + '<button class="qty-btn" onclick="cambiarCantidad(' + item.id_producto + ',1)">+</button>'
                 + '</div>'
                 + '<span class="ci-total">$' + (item.precio * item.cantidad).toFixed(2) + '</span>'
                 + '<button class="ci-del" onclick="eliminarDelCarrito(' + item.id_producto + ')"><i class="bi bi-x"></i></button>'
                 + '</div>';
        }).join('');
    }

    var btnDesc = document.getElementById('btnDescuento');
    if (btnDesc) btnDesc.disabled = carrito.length === 0;
    calcularTotales(); calcularCambio();
}

/* ══════════════════════════════════════════
   DESCUENTO
══════════════════════════════════════════ */
function abrirModalDescuento() {
    document.getElementById('inputDescuento').value = descuento.valor > 0 ? descuento.valor : '';
    document.getElementById('errDescuento').textContent = '';
    document.querySelectorAll('.tipo-tab').forEach(function(b) { b.classList.toggle('active', b.dataset.tipo === descuento.tipo); });
    actualizarPrefijo(); previsualizarDescuento();
    document.getElementById('modalDescuento').classList.add('activo');
    document.body.style.overflow = 'hidden';
    setTimeout(function() { document.getElementById('inputDescuento').focus(); }, 200);
}
function cerrarModalDescuento() { document.getElementById('modalDescuento').classList.remove('activo'); document.body.style.overflow = ''; }

function seleccionarTipoDescuento(btn, tipo) {
    document.querySelectorAll('.tipo-tab').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active'); descuento.tipo = tipo; actualizarPrefijo();
    var r = document.getElementById('rapidosPorcentaje');
    if (r) r.style.display = tipo === 'porcentaje' ? 'block' : 'none';
    document.getElementById('inputDescuento').value = ''; previsualizarDescuento();
}
function actualizarPrefijo() { var p = document.getElementById('descuentoPrefix'); if (p) p.textContent = descuento.tipo === 'porcentaje' ? '%' : '$'; }
function aplicarRapido(pct) { document.getElementById('inputDescuento').value = pct; previsualizarDescuento(); }

function previsualizarDescuento() {
    var subtotal = carrito.reduce(function(s, i) { return s + i.precio * i.cantidad; }, 0);
    var valor = parseFloat(document.getElementById('inputDescuento').value) || 0;
    var monto = 0;
    document.getElementById('errDescuento').textContent = '';
    if (descuento.tipo === 'porcentaje') {
        if (valor > 100) { document.getElementById('errDescuento').textContent = 'No puede superar 100%.'; valor = 100; }
        monto = subtotal * (valor / 100);
        document.getElementById('prevDescLabel').textContent = 'Descuento (' + valor + '%)';
    } else {
        if (valor > subtotal) { document.getElementById('errDescuento').textContent = 'No puede superar el subtotal.'; valor = subtotal; }
        monto = valor;
        document.getElementById('prevDescLabel').textContent = 'Descuento (monto fijo)';
    }
    document.getElementById('prevSubtotal').textContent  = '$' + subtotal.toFixed(2);
    document.getElementById('prevDescMonto').textContent = '-$' + monto.toFixed(2);
    document.getElementById('prevTotal').textContent     = '$' + Math.max(0, subtotal - monto).toFixed(2);
}

function confirmarDescuento() {
    var valor = parseFloat(document.getElementById('inputDescuento').value) || 0;
    var subtotal = carrito.reduce(function(s, i) { return s + i.precio * i.cantidad; }, 0);
    document.getElementById('errDescuento').textContent = '';
    if (valor <= 0) { document.getElementById('errDescuento').textContent = 'Ingresa un valor mayor a 0.'; return; }
    if (descuento.tipo === 'porcentaje' && valor > 100) { document.getElementById('errDescuento').textContent = 'No puede superar 100%.'; return; }
    if (descuento.tipo === 'monto' && valor > subtotal) { document.getElementById('errDescuento').textContent = 'No puede superar el subtotal.'; return; }
    descuento.valor = valor;
    descuento.monto = descuento.tipo === 'porcentaje' ? subtotal * (valor / 100) : valor;
    cerrarModalDescuento(); calcularTotales();
    mostrarToast('Descuento aplicado.', 'ok');
}

function quitarDescuento() { resetDescuento(); cerrarModalDescuento(); calcularTotales(); mostrarToast('Descuento eliminado.', 'ok'); }
function resetDescuento()   { descuento.tipo = 'porcentaje'; descuento.valor = 0; descuento.monto = 0; }

/* ══════════════════════════════════════════
   TOTALES
══════════════════════════════════════════ */
function calcularTotales() {
    var subtotalBruto = carrito.reduce(function(s, i) { return s + i.precio * i.cantidad; }, 0);
    descuento.monto = descuento.valor > 0
        ? (descuento.tipo === 'porcentaje' ? subtotalBruto * (descuento.valor / 100) : Math.min(descuento.valor, subtotalBruto))
        : 0;
    var subtotalConDesc = Math.max(0, subtotalBruto - descuento.monto);
    var aplicaIva = document.getElementById('toggleIva').checked;
    var iva   = aplicaIva ? subtotalConDesc * 0.13 : 0;
    var total = subtotalConDesc + iva;

    document.getElementById('totalSubtotal').textContent = '$' + subtotalBruto.toFixed(2);

    var descuentoRow = document.getElementById('descuentoRow');
    var btnDesc      = document.getElementById('btnDescuento');
    if (descuento.monto > 0) {
        descuentoRow.style.display = 'flex';
        document.getElementById('totalDescuento').textContent = '-$' + descuento.monto.toFixed(2);
        document.getElementById('descuentoDesc').textContent  = descuento.tipo === 'porcentaje' ? '(' + descuento.valor + '%)' : '(monto fijo)';
        if (btnDesc) { btnDesc.innerHTML = '<i class="bi bi-tag-fill"></i> Editar descuento'; btnDesc.classList.add('btn-descuento-activo'); }
    } else {
        descuentoRow.style.display = 'none';
        if (btnDesc) { btnDesc.innerHTML = '<i class="bi bi-tag"></i> Aplicar descuento'; btnDesc.classList.remove('btn-descuento-activo'); }
    }

    document.getElementById('totalIva').textContent = aplicaIva ? '$' + iva.toFixed(2) : 'No aplica';
    var ivaRow = document.getElementById('ivaRow');
    if (ivaRow) ivaRow.style.opacity = aplicaIva ? '1' : '0.4';
    document.getElementById('totalFinal').textContent = '$' + total.toFixed(2);
    document.getElementById('btnCobrar').disabled = carrito.length === 0;
    calcularCambio();
}

function calcularCambio() {
    var total    = parseFloat(document.getElementById('totalFinal').textContent.replace('$','')) || 0;
    var recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
    var el       = document.getElementById('cambioVal');
    el.textContent = '$' + Math.max(0, recibido - total).toFixed(2);
    el.classList.toggle('negativo', (recibido - total) < 0 && recibido > 0);
}

function seleccionarMetodo(btn) {
    document.querySelectorAll('.pay-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active'); metodoActual = btn.dataset.metodo;
    document.getElementById('payCalc').style.display = metodoActual === 'efectivo' ? 'block' : 'none';
}

/* ══════════════════════════════════════════
   PROCESAR VENTA
══════════════════════════════════════════ */
function procesarVenta() {
    if (carrito.length === 0) return;
    var subtotalBruto   = carrito.reduce(function(s, i) { return s + i.precio * i.cantidad; }, 0);
    var montoDescuento  = descuento.monto;
    var subtotalConDesc = Math.max(0, subtotalBruto - montoDescuento);
    var aplicaIva       = document.getElementById('toggleIva').checked;
    var iva             = aplicaIva ? subtotalConDesc * 0.13 : 0;
    var total           = subtotalConDesc + iva;
    var recibido        = parseFloat(document.getElementById('montoRecibido').value) || 0;

    if (metodoActual === 'efectivo' && recibido < total) {
        mostrarToast('El monto recibido es menor al total.', 'error');
        document.getElementById('montoRecibido').focus(); return;
    }

    var cambio = metodoActual === 'efectivo' ? Math.max(0, recibido - total) : 0;
    var btn    = document.getElementById('btnCobrar');
    btn.disabled = true; btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Procesando...';

    var fd = new FormData();
    fd.append('accion',          'procesar_venta');
    fd.append('subtotal',        subtotalConDesc.toFixed(2));
    fd.append('impuesto',        iva.toFixed(2));
    fd.append('total',           total.toFixed(2));
    fd.append('monto_recibido',  recibido.toFixed(2));
    fd.append('cambio',          cambio.toFixed(2));
    fd.append('metodo_pago',     metodoActual);
    fd.append('aplica_iva',      aplicaIva ? '1' : '0');
    fd.append('descuento_tipo',  descuento.tipo);
    fd.append('descuento_valor', descuento.valor);
    fd.append('descuento_monto', montoDescuento.toFixed(2));
    fd.append('items',           JSON.stringify(carrito));

    fetch(POS_CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-receipt"></i> Confirmar y generar ticket';
            if (res.ok) mostrarTicket(res, subtotalBruto, montoDescuento, subtotalConDesc, iva, total, recibido, cambio);
            else mostrarToast(res.mensaje || 'Error al procesar.', 'error');
        })
        .catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-receipt"></i> Confirmar y generar ticket';
            mostrarToast('Error de conexión.', 'error');
        });
}

/* ══════════════════════════════════════════
   TICKET
══════════════════════════════════════════ */
function mostrarTicket(res, subtotalBruto, montoDescuento, subtotalConDesc, iva, total, recibido, cambio) {
    var aplicaIva = document.getElementById('toggleIva').checked;
    document.getElementById('tktNumero').textContent = res.numero_ticket;
    document.getElementById('tktFecha').textContent  = new Date().toLocaleString('es-SV');
    document.getElementById('tktCajero').textContent = res.cajero || 'Cajero';
    document.getElementById('tktMetodo').textContent = metodoActual.charAt(0).toUpperCase() + metodoActual.slice(1);

    var itemsHtml = '<tr><th>Producto</th><th class="td-r">Cant.</th><th class="td-r">P.Unit</th><th class="td-r">Total</th></tr>';
    carrito.forEach(function(item) {
        itemsHtml += '<tr><td>' + item.nombre + '</td><td class="td-r">' + item.cantidad + '</td><td class="td-r">$' + item.precio.toFixed(2) + '</td><td class="td-r">$' + (item.precio * item.cantidad).toFixed(2) + '</td></tr>';
    });
    document.getElementById('tktItems').innerHTML = itemsHtml;

    var totalesHtml = '<div>Subtotal: $' + subtotalBruto.toFixed(2) + '</div>';
    if (montoDescuento > 0) totalesHtml += '<div>' + (descuento.tipo === 'porcentaje' ? 'Descuento (' + descuento.valor + '%)' : 'Descuento fijo') + ': -$' + montoDescuento.toFixed(2) + '</div>';
    if (aplicaIva) totalesHtml += '<div>IVA (13%): $' + iva.toFixed(2) + '</div>';
    totalesHtml += '<div class="ticket-total-final">TOTAL: $' + total.toFixed(2) + '</div>';
    if (metodoActual === 'efectivo') totalesHtml += '<div>Recibido: $' + recibido.toFixed(2) + '</div><div>Cambio: $' + cambio.toFixed(2) + '</div>';
    document.getElementById('tktTotales').innerHTML = totalesHtml;
    document.getElementById('modalTicket').classList.add('activo');
}

function cerrarModalTicket() { document.getElementById('modalTicket').classList.remove('activo'); }
function imprimirTicket()    { window.print(); }

function nuevaVenta() {
    cerrarModalTicket(); carrito = []; resetDescuento();
    document.getElementById('montoRecibido').value = '';
    document.getElementById('toggleIva').checked   = false;
    actualizarCarritoUI(); cargarProductos();
    mostrarToast('Nueva venta iniciada.', 'ok');
}

/* ══════════════════════════════════════════
   REVERSIÓN DE VENTA
══════════════════════════════════════════ */
function abrirModalReversion() {
    cargarVentasRecientes();
    document.getElementById('modalReversion').classList.add('activo');
    document.body.style.overflow = 'hidden';
}

function cerrarModalReversion() {
    document.getElementById('modalReversion').classList.remove('activo');
    document.body.style.overflow = '';
}

function cargarVentasRecientes() {
    var lista = document.getElementById('revLista');
    lista.innerHTML = '<div class="rev-loading"><i class="bi bi-arrow-repeat"></i> Cargando...</div>';

    fetch(POS_CONTROLLER + '?accion=listar_recientes')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok || res.datos.length === 0) {
                lista.innerHTML = '<div class="rev-empty"><i class="bi bi-receipt"></i><span>No hay ventas hoy</span></div>';
                document.getElementById('btnRevertirUltima').style.display = 'none';
                return;
            }

            var ventas = res.datos;

            // Botón revertir última
            var ultima = ventas[0];
            var btnUlt = document.getElementById('btnRevertirUltima');
            if (ultima.estado === 'completada') {
                btnUlt.style.display  = 'flex';
                btnUlt.onclick = function() { ejecutarReversion(ultima.id_venta, ultima.numero_ticket); };
                document.getElementById('ultimaTicket').textContent = ultima.numero_ticket;
                document.getElementById('ultimaTotal').textContent  = '$' + parseFloat(ultima.total).toFixed(2);
            } else {
                btnUlt.style.display = 'none';
            }

            // Lista de ventas recientes
            lista.innerHTML = ventas.map(function(v) {
                var esAnulada   = v.estado === 'anulada';
                var hora        = new Date(v.fecha_venta).toLocaleTimeString('es-SV', { hour:'2-digit', minute:'2-digit' });
                var metodoBadge = { efectivo:'badge-efectivo', tarjeta:'badge-tarjeta', transferencia:'badge-transferencia' }[v.metodo_pago] || '';

                return '<div class="rev-item' + (esAnulada ? ' rev-item-anulada' : '') + '">'
                     + '<div class="rev-item-left">'
                     + '<div class="rev-item-ticket">' + v.numero_ticket + '</div>'
                     + '<div class="rev-item-meta">'
                     + '<span>' + hora + '</span>'
                     + '<span class="' + metodoBadge + '" style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500">' + v.metodo_pago + '</span>'
                     + (esAnulada ? '<span class="badge-anulada" style="padding:2px 8px;border-radius:20px;font-size:10px">Anulada</span>' : '')
                     + '</div>'
                     + '</div>'
                     + '<div class="rev-item-right">'
                     + '<div class="rev-item-total">$' + parseFloat(v.total).toFixed(2) + '</div>'
                     + (!esAnulada
                         ? '<button class="btn-rev-item" onclick="ejecutarReversion(' + v.id_venta + ',\'' + v.numero_ticket + '\')">'
                           + '<i class="bi bi-arrow-counterclockwise"></i> Revertir'
                           + '</button>'
                         : '<span class="rev-ya-anulada">Ya anulada</span>')
                     + '</div>'
                     + '</div>';
            }).join('');
        })
        .catch(function() {
            lista.innerHTML = '<div class="rev-empty">Error al cargar.</div>';
        });
}

function ejecutarReversion(id_venta, numero_ticket) {
    if (!confirm('¿Revertir la venta ' + numero_ticket + '?\n\nEl stock se restaurará y la venta quedará anulada.')) return;

    var fd = new FormData();
    fd.append('accion',   'revertir_venta');
    fd.append('id_venta', id_venta);

    fetch(POS_CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.ok) {
                cerrarModalReversion();
                mostrarToast(res.mensaje, 'ok');
                cargarProductos();
            } else {
                mostrarToast(res.mensaje, 'error');
            }
        })
        .catch(function() { mostrarToast('Error de conexión.', 'error'); });
}

/* ══════════════════════════════════════════
   TOAST
══════════════════════════════════════════ */
function mostrarToast(mensaje, tipo) {
    var toast = document.getElementById('toast-pos');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast-pos';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:11px 20px;border-radius:8px;font-size:13px;font-weight:500;color:white;z-index:9999;opacity:0;transform:translateY(8px);transition:opacity .3s,transform .3s;pointer-events:none;';
        document.body.appendChild(toast);
    }
    toast.textContent      = mensaje;
    toast.style.background = tipo === 'error' ? '#c62828' : '#2e7d32';
    toast.style.opacity    = '1'; toast.style.transform = 'translateY(0)';
    setTimeout(function() { toast.style.opacity = '0'; toast.style.transform = 'translateY(8px)'; }, 3000);
}