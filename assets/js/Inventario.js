/* =====================
   INVENTARIO.JS - DNS Pharmacy
   ===================== */

const INV_CONTROLLER  = '/DNS_Pharmacy/controllers/InventarioController.php';
const PROD_CONTROLLER = '/DNS_Pharmacy/controllers/ProductoController.php';

var productosLista   = [];
var filaContador     = 0;

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    cargarStats();
    cargarStock();
    cargarHistorial();
    cargarProductosLista();
    cargarProveedores();
    document.getElementById('comp_fecha').value = new Date().toISOString().split('T')[0];
});

/* ── Tabs ── */
function cambiarTab(btn, tabId) {
    document.querySelectorAll('.inv-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(t) { t.classList.remove('active-tab'); });
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active-tab');
}

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
function cargarStats() {
    fetch(INV_CONTROLLER + '?accion=stats')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            document.getElementById('statTotalCompras').textContent = res.datos.total_compras;
            document.getElementById('statProductos').textContent    = res.datos.total_productos;
            document.getElementById('statStockBajo').textContent    = res.datos.stock_bajo;
            document.getElementById('statInversion').textContent    = '$' + parseFloat(res.datos.inversion).toFixed(2);
        });
}

/* ══════════════════════════════════════════
   STOCK ACTUAL
══════════════════════════════════════════ */
function cargarStock() {
    fetch(PROD_CONTROLLER + '?accion=listar')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            renderizarStock(res.datos);
        });
}

function renderizarStock(prods) {
    var tbody = document.getElementById('cuerpoStock');
    document.getElementById('contadorStock').textContent = prods.length;

    if (!prods || prods.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">No hay productos.</td></tr>';
        return;
    }

    tbody.innerHTML = prods.map(function(p, i) {
        var stock  = parseInt(p.stock_actual);
        var minimo = parseInt(p.stock_minimo);
        var badge, cls, estadoKey;

        if (stock <= 0) {
            badge = '<span class="badge-agotado">Agotado</span>'; cls = 'stock-cero'; estadoKey = 'agotado';
        } else if (stock <= minimo) {
            badge = '<span class="badge-bajo">Stock bajo</span>'; cls = 'stock-bajo'; estadoKey = 'bajo';
        } else {
            badge = '<span class="badge-ok">OK</span>'; cls = 'stock-ok'; estadoKey = 'ok';
        }

        return '<tr data-nombre="' + p.nombre.toLowerCase() + '" data-estado-stock="' + estadoKey + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td style="font-weight:500">' + p.nombre + '</td>'
             + '<td>' + (p.nombre_categoria || '—') + '</td>'
             + '<td class="td-codigo">' + p.codigo_barras + '</td>'
             + '<td><span class="stock-num ' + cls + '">' + stock + '</span></td>'
             + '<td>' + minimo + '</td>'
             + '<td>$' + parseFloat(p.precio_compra).toFixed(2) + '</td>'
             + '<td class="td-precio-venta">$' + parseFloat(p.precio_venta).toFixed(2) + '</td>'
             + '<td>' + badge + '</td>'
             + '</tr>';
    }).join('');
}

function filtrarStock() {
    var q      = document.getElementById('buscadorStock').value.toLowerCase();
    var estado = document.getElementById('filtroEstadoStock').value;
    var filas  = document.querySelectorAll('#cuerpoStock tr[data-nombre]');
    var visible = 0;

    filas.forEach(function(fila) {
        var ok = (!q      || fila.dataset.nombre.includes(q))
              && (!estado || fila.dataset.estadoStock === estado);
        fila.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    document.getElementById('contadorStock').textContent = visible;
}

/* ══════════════════════════════════════════
   HISTORIAL COMPRAS
══════════════════════════════════════════ */
function cargarHistorial() {
    fetch(INV_CONTROLLER + '?accion=listar')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            renderizarHistorial(res.datos);
        });
}

function renderizarHistorial(compras) {
    var tbody = document.getElementById('cuerpoCompras');
    document.getElementById('contadorCompras').textContent = compras.length;

    if (!compras || compras.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="tabla-vacia">No hay compras registradas.</td></tr>';
        return;
    }

    tbody.innerHTML = compras.map(function(c, i) {
        var badge = c.estado === 'registrada'
            ? '<span class="badge-reg">Registrada</span>'
            : '<span class="badge-anu">Anulada</span>';

        return '<tr data-factura="' + (c.numero_factura||'').toLowerCase() + '"'
             + ' data-proveedor="' + (c.nombre_proveedor||'').toLowerCase() + '"'
             + ' data-estado="' + c.estado + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td class="td-codigo">' + c.numero_factura + '</td>'
             + '<td>' + (c.nombre_proveedor || '—') + '</td>'
             + '<td>' + c.fecha_compra + '</td>'
             + '<td>' + (c.num_productos || 0) + ' productos</td>'
             + '<td>$' + parseFloat(c.subtotal).toFixed(2) + '</td>'
             + '<td>$' + parseFloat(c.impuesto).toFixed(2) + '</td>'
             + '<td style="font-weight:600;color:#841480">$' + parseFloat(c.total).toFixed(2) + '</td>'
             + '<td>' + badge + '</td>'
             + '<td>'
             + '<button class="btn-accion btn-ver" onclick="verDetalle(' + c.id_compra + ')">Ver</button>'
             + (c.estado === 'registrada' ? '<button class="btn-accion btn-anu" onclick="anularCompra(' + c.id_compra + ')">Anular</button>' : '')
             + '</td>'
             + '</tr>';
    }).join('');
}

function filtrarCompras() {
    var q      = document.getElementById('buscadorCompras').value.toLowerCase();
    var estado = document.getElementById('filtroEstadoCompra').value;
    var filas  = document.querySelectorAll('#cuerpoCompras tr[data-factura]');
    var visible = 0;

    filas.forEach(function(fila) {
        var ok = (!q      || fila.dataset.factura.includes(q) || fila.dataset.proveedor.includes(q))
              && (!estado || fila.dataset.estado === estado);
        fila.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    document.getElementById('contadorCompras').textContent = visible;
}

/* ══════════════════════════════════════════
   MODAL COMPRA
══════════════════════════════════════════ */
function cargarProductosLista() {
    fetch(PROD_CONTROLLER + '?accion=listar')
        .then(function(r) { return r.json(); })
        .then(function(res) { if (res.ok) productosLista = res.datos; });
}

function cargarProveedores() {
    fetch(INV_CONTROLLER + '?accion=listar_proveedores')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            var select = document.getElementById('comp_proveedor');
            select.innerHTML = '<option value="">Seleccionar proveedor</option>';
            res.datos.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.id_proveedor;
                opt.textContent = p.nombre;
                select.appendChild(opt);
            });
        });
}

function abrirModalCompra() {
    limpiarFormCompra();
    agregarFilaProducto();
    abrirModal('modalCompra');
}

function limpiarFormCompra() {
    document.getElementById('formCompra').reset();
    document.getElementById('detalleCompra').innerHTML = '';
    ['proveedor','factura','fecha','productos'].forEach(function(c) {
        var el = document.getElementById('err_' + c);
        if (el) el.textContent = '';
    });
    filaContador = 0;
    calcularTotalesCompra();
    document.getElementById('comp_fecha').value = new Date().toISOString().split('T')[0];
}

function agregarFilaProducto() {
    filaContador++;
    var id = 'fila_' + filaContador;

    var opciones = '<option value="">Seleccionar producto</option>'
        + productosLista.map(function(p) {
            return '<option value="' + p.id_producto + '" data-precio="' + p.precio_compra + '">' + p.nombre + '</option>';
          }).join('');

    var fila = document.createElement('tr');
    fila.id  = id;
    fila.innerHTML =
        '<td><select class="fila-select" onchange="autocompletarPrecio(\'' + id + '\');calcularSubtotalFila(\'' + id + '\')">' + opciones + '</select></td>'
      + '<td><input type="number" class="fila-input fila-cantidad" min="1" value="1" style="width:70px" oninput="calcularSubtotalFila(\'' + id + '\')"></td>'
      + '<td><input type="number" class="fila-input fila-costo"    min="0" step="0.01" style="width:90px" oninput="calcularSubtotalFila(\'' + id + '\')" placeholder="$0.00"></td>'
      + '<td><input type="text"   class="fila-input fila-lote"     style="width:90px"  placeholder="Opcional"></td>'
      + '<td><input type="date"   class="fila-input fila-venc"     style="width:120px"></td>'
      + '<td><span class="fila-subtotal">$0.00</span></td>'
      + '<td><button type="button" class="btn-del-fila" onclick="eliminarFila(\'' + id + '\')"><i class="bi bi-x-circle"></i></button></td>';

    document.getElementById('detalleCompra').appendChild(fila);
}

function autocompletarPrecio(id) {
    var fila   = document.getElementById(id);
    var select = fila.querySelector('.fila-select');
    var costo  = fila.querySelector('.fila-costo');
    if (select.value) {
        var opt = select.options[select.selectedIndex];
        if (opt.dataset.precio && !costo.value) {
            costo.value = parseFloat(opt.dataset.precio).toFixed(2);
        }
    }
}

function eliminarFila(id) {
    var fila = document.getElementById(id);
    if (fila) fila.remove();
    calcularTotalesCompra();
}

function calcularSubtotalFila(id) {
    var fila     = document.getElementById(id);
    var cantidad = parseFloat(fila.querySelector('.fila-cantidad').value) || 0;
    var costo    = parseFloat(fila.querySelector('.fila-costo').value)    || 0;
    fila.querySelector('.fila-subtotal').textContent = '$' + (cantidad * costo).toFixed(2);
    calcularTotalesCompra();
}

function calcularTotalesCompra() {
    var subtotal = 0;
    document.querySelectorAll('.fila-subtotal').forEach(function(el) {
        subtotal += parseFloat(el.textContent.replace('$','')) || 0;
    });
    var iva   = subtotal * 0.13;
    var total = subtotal + iva;
    document.getElementById('compSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('compIva').textContent      = '$' + iva.toFixed(2);
    document.getElementById('compTotal').textContent    = '$' + total.toFixed(2);
}

document.getElementById('formCompra').addEventListener('submit', function(e) {
    e.preventDefault();

    ['proveedor','factura','fecha','productos'].forEach(function(c) {
        var el = document.getElementById('err_' + c);
        if (el) el.textContent = '';
    });

    var valido = true;
    if (!document.getElementById('comp_proveedor').value) { document.getElementById('err_proveedor').textContent = 'Selecciona un proveedor.'; valido = false; }
    if (!document.getElementById('comp_factura').value.trim()) { document.getElementById('err_factura').textContent = 'Ingresa el número de factura.'; valido = false; }
    if (!document.getElementById('comp_fecha').value) { document.getElementById('err_fecha').textContent = 'Selecciona la fecha.'; valido = false; }

    var items = [];
    document.querySelectorAll('#detalleCompra tr').forEach(function(fila) {
        var select = fila.querySelector('.fila-select');
        if (select && select.value) {
            items.push({
                id_producto:       select.value,
                cantidad:          parseInt(fila.querySelector('.fila-cantidad').value) || 0,
                costo_unitario:    parseFloat(fila.querySelector('.fila-costo').value)  || 0,
                numero_lote:       fila.querySelector('.fila-lote').value.trim(),
                fecha_vencimiento: fila.querySelector('.fila-venc').value || ''
            });
        }
    });

    if (items.length === 0) { document.getElementById('err_productos').textContent = 'Agrega al menos un producto.'; valido = false; }
    if (!valido) return;

    var subtotal = parseFloat(document.getElementById('compSubtotal').textContent.replace('$',''));
    var iva      = parseFloat(document.getElementById('compIva').textContent.replace('$',''));
    var total    = parseFloat(document.getElementById('compTotal').textContent.replace('$',''));

    var fd = new FormData();
    fd.append('accion',         'guardar_compra');
    fd.append('id_proveedor',   document.getElementById('comp_proveedor').value);
    fd.append('numero_factura', document.getElementById('comp_factura').value.trim());
    fd.append('fecha_compra',   document.getElementById('comp_fecha').value);
    fd.append('observaciones',  document.getElementById('comp_obs').value.trim());
    fd.append('subtotal',       subtotal.toFixed(2));
    fd.append('impuesto',       iva.toFixed(2));
    fd.append('total',          total.toFixed(2));
    fd.append('items',          JSON.stringify(items));

    var btn = document.querySelector('#formCompra .btn-guardar');
    btn.innerHTML = 'Guardando...'; btn.disabled = true;

    fetch(INV_CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.innerHTML = '<i class="bi bi-save"></i> Guardar compra'; btn.disabled = false;
            if (res.ok) {
                cerrarModal('modalCompra');
                mostrarToast('Compra registrada correctamente.', 'ok');
                cargarStats(); cargarStock(); cargarHistorial();
            } else {
                mostrarToast(res.mensaje || 'Error al guardar.', 'error');
            }
        })
        .catch(function() {
            btn.innerHTML = '<i class="bi bi-save"></i> Guardar compra'; btn.disabled = false;
            mostrarToast('Error de conexión.', 'error');
        });
});

/* ══════════════════════════════════════════
   DETALLE COMPRA
══════════════════════════════════════════ */
function verDetalle(id) {
    fetch(INV_CONTROLLER + '?accion=detalle&id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) { mostrarToast(res.mensaje, 'error'); return; }
            renderizarDetalle(res.datos);
            abrirModal('modalDetalle');
        });
}

function renderizarDetalle(d) {
    var html = '<div class="detalle-print" id="detallePrintArea">'
        + '<h3><i class="bi bi-receipt"></i> Compra #' + d.compra.id_compra + '</h3>'
        + '<div class="det-meta">'
        + '<div><strong>N° Factura:</strong> ' + d.compra.numero_factura + '</div>'
        + '<div><strong>Proveedor:</strong> '  + (d.compra.nombre_proveedor || '—') + '</div>'
        + '<div><strong>Fecha:</strong> '       + d.compra.fecha_compra + '</div>'
        + '<div><strong>Registrado por:</strong> ' + (d.compra.nombre_usuario || '—') + '</div>'
        + (d.compra.observaciones ? '<div><strong>Obs:</strong> ' + d.compra.observaciones + '</div>' : '')
        + '</div>'
        + '<table><thead><tr><th>Producto</th><th>Cant.</th><th>Costo</th><th>Subtotal</th><th>Lote</th><th>Vencimiento</th></tr></thead><tbody>'
        + d.detalle.map(function(item) {
            return '<tr><td>' + item.nombre_producto + '</td>'
                 + '<td>' + item.cantidad + '</td>'
                 + '<td>$' + parseFloat(item.costo_unitario).toFixed(2) + '</td>'
                 + '<td>$' + parseFloat(item.subtotal).toFixed(2) + '</td>'
                 + '<td>' + (item.numero_lote || '—') + '</td>'
                 + '<td>' + (item.fecha_vencimiento || '—') + '</td></tr>';
          }).join('')
        + '</tbody></table>'
        + '<div class="det-totales">'
        + '<div>Subtotal: $' + parseFloat(d.compra.subtotal).toFixed(2) + '</div>'
        + '<div>IVA (13%): $' + parseFloat(d.compra.impuesto).toFixed(2) + '</div>'
        + '<div class="det-total-final">TOTAL: $' + parseFloat(d.compra.total).toFixed(2) + '</div>'
        + '</div></div>';

    document.getElementById('cuerpoDetalle').innerHTML = html;
}

function imprimirDetalle() { window.print(); }

function anularCompra(id) {
    if (!confirm('¿Anular esta compra? El stock no se revertirá automáticamente.')) return;
    var fd = new FormData();
    fd.append('accion', 'anular_compra');
    fd.append('id_compra', id);
    fetch(INV_CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            mostrarToast(res.mensaje, res.ok ? 'ok' : 'error');
            if (res.ok) { cargarHistorial(); cargarStats(); }
        });
}

/* ══════════════════════════════════════════
   UTILIDADES
══════════════════════════════════════════ */
function abrirModal(id) {
    document.getElementById(id).classList.add('activo');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('activo');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) { overlay.classList.remove('activo'); document.body.style.overflow = ''; }
    });
});

function mostrarToast(mensaje, tipo) {
    var toast = document.getElementById('toast');
    if (!toast) { toast = document.createElement('div'); toast.id = 'toast'; document.body.appendChild(toast); }
    toast.textContent = mensaje;
    toast.className   = 'toast toast-' + (tipo || 'ok');
    toast.classList.add('toast-visible');
    setTimeout(function() { toast.classList.remove('toast-visible'); }, 3500);
}