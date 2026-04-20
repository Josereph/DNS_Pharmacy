/* =====================
   HISTORIAL_VENTAS.JS - DNS Pharmacy
   ===================== */

const HV_CONTROLLER = '/DNS_Pharmacy/controllers/HistorialventasController.php';

var ventasData = [];

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    setPeriodo('todo');
});

/* ══════════════════════════════════════════
   PERIODOS RÁPIDOS
══════════════════════════════════════════ */
function setPeriodo(periodo) {
    document.querySelectorAll('.btn-periodo').forEach(function(b) { b.classList.remove('activo'); });
    event.target.classList.add('activo');

    var hoy   = new Date();
    var desde = new Date();
    var hasta = new Date();

    if (periodo === 'hoy') {
        desde = hoy;
        hasta = hoy;
    } else if (periodo === 'semana') {
        var dia = hoy.getDay();
        desde.setDate(hoy.getDate() - (dia === 0 ? 6 : dia - 1));
        hasta = hoy;
    } else if (periodo === 'mes') {
        desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        hasta = hoy;
    } else {
        document.getElementById('filtroDesde').value = '';
        document.getElementById('filtroHasta').value = '';
        cargarVentas('', '');
        return;
    }

    var desdeStr = formatFecha(desde);
    var hastaStr = formatFecha(hasta);
    document.getElementById('filtroDesde').value = desdeStr;
    document.getElementById('filtroHasta').value = hastaStr;
    cargarVentas(desdeStr, hastaStr);
}

function filtrarDatos() {
    var desde = document.getElementById('filtroDesde').value;
    var hasta = document.getElementById('filtroHasta').value;
    cargarVentas(desde, hasta);
}

function formatFecha(d) {
    return d.getFullYear() + '-'
        + String(d.getMonth() + 1).padStart(2, '0') + '-'
        + String(d.getDate()).padStart(2, '0');
}

/* ══════════════════════════════════════════
   CARGAR VENTAS
══════════════════════════════════════════ */
function cargarVentas(desde, hasta) {
    var url = HV_CONTROLLER + '?accion=listar';
    if (desde) url += '&desde=' + desde;
    if (hasta) url += '&hasta=' + hasta;

    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            ventasData = res.datos;
            renderizarTabla(ventasData);
            calcularStats(ventasData);
        })
        .catch(function(e) { console.error('Error:', e); });
}

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
function calcularStats(ventas) {
    var tickets  = ventas.length;
    var total    = ventas.reduce(function(s, v) { return s + parseFloat(v.total); }, 0);
    var iva      = ventas.reduce(function(s, v) { return s + parseFloat(v.impuesto); }, 0);
    var promedio = tickets > 0 ? total / tickets : 0;

    document.getElementById('statTickets').textContent  = tickets;
    document.getElementById('statTotal').textContent    = '$' + total.toFixed(2);
    document.getElementById('statIva').textContent      = '$' + iva.toFixed(2);
    document.getElementById('statPromedio').textContent = '$' + promedio.toFixed(2);
}

/* ══════════════════════════════════════════
   RENDERIZAR TABLA
══════════════════════════════════════════ */
function renderizarTabla(ventas) {
    var tbody = document.getElementById('cuerpoTabla');

    actualizarContador(ventas.length, ventasData.length);

    if (!ventas || ventas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="tabla-vacia">No hay ventas en el período seleccionado.</td></tr>';
        return;
    }

    tbody.innerHTML = ventas.map(function(v, i) {
        var ini    = iniciales(v.nombre_empleado || '');
        var metodo = badgeMetodo(v.metodo_pago);
        var estado = v.estado === 'completada'
            ? '<span class="badge-completada">Completada</span>'
            : '<span class="badge-anulada">Anulada</span>';

        return '<tr data-metodo="' + v.metodo_pago + '" data-estado="' + v.estado + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td><span class="td-ticket">' + esc(v.numero_ticket) + '</span></td>'
             + '<td><div class="td-empleado">'
             + '<div class="emp-avatar">' + ini + '</div>'
             + '<div class="emp-info"><strong>' + esc(v.nombre_empleado || '—') + '</strong></div>'
             + '</div></td>'
             + '<td>' + formatearFechaLegible(v.fecha_venta) + '</td>'
             + '<td>$' + parseFloat(v.subtotal).toFixed(2) + '</td>'
             + '<td>' + (parseFloat(v.impuesto) > 0 ? '$' + parseFloat(v.impuesto).toFixed(2) : '<span style="color:#bbb">—</span>') + '</td>'
             + '<td class="td-total">$' + parseFloat(v.total).toFixed(2) + '</td>'
             + '<td>' + metodo + '</td>'
             + '<td>' + estado + '</td>'
             + '<td><button class="btn-accion btn-ver" onclick="verDetalle(' + v.id_venta + ')"><i class="bi bi-eye"></i> Ver</button></td>'
             + '</tr>';
    }).join('');

    // Aplicar filtro de método si hay uno seleccionado
    filtrarPorMetodo();
}

function filtrarPorMetodo() {
    var metodo = document.getElementById('filtroMetodo').value;
    var filas  = document.querySelectorAll('#cuerpoTabla tr[data-metodo]');
    var visible = 0;
    filas.forEach(function(fila) {
        var ok = !metodo || fila.dataset.metodo === metodo;
        fila.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    actualizarContador(visible, filas.length);
}

function actualizarContador(visible, total) {
    var cv = document.getElementById('contadorVisible');
    var ct = document.getElementById('contadorTotal');
    if (cv) cv.textContent = visible;
    if (ct) ct.textContent = total;
}

/* ══════════════════════════════════════════
   VER DETALLE
══════════════════════════════════════════ */
function verDetalle(id) {
    fetch(HV_CONTROLLER + '?accion=detalle&id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) { mostrarToast(res.mensaje, 'error'); return; }
            renderizarDetalle(res.datos);
            abrirModal('modalDetalle');
        });
}

function renderizarDetalle(d) {
    var v        = d.venta;
    var tieneIva = parseFloat(v.impuesto) > 0;

    var html = '<div class="detalle-venta-header">'
             + '<div><div class="detalle-ticket">' + esc(v.numero_ticket) + '</div>'
             + '<div style="margin-top:4px">' + badgeMetodo(v.metodo_pago) + '</div></div>'
             + '<div class="detalle-meta">'
             + '<div><strong>Cajero:</strong> ' + esc(v.nombre_empleado || '—') + '</div>'
             + '<div><strong>Fecha:</strong> '  + formatearFechaLegible(v.fecha_venta) + '</div>'
             + '<div><strong>Estado:</strong> ' + (v.estado === 'completada' ? '<span class="badge-completada">Completada</span>' : '<span class="badge-anulada">Anulada</span>') + '</div>'
             + '</div></div>'
             + '<table class="detalle-tabla">'
             + '<thead><tr><th>Producto</th><th class="td-r">Cant.</th><th class="td-r">P. Unit.</th><th class="td-r">Subtotal</th></tr></thead>'
             + '<tbody>'
             + d.detalle.map(function(item) {
                 return '<tr>'
                      + '<td>' + esc(item.nombre_producto) + '</td>'
                      + '<td class="td-r">' + item.cantidad + '</td>'
                      + '<td class="td-r">$' + parseFloat(item.precio_unitario).toFixed(2) + '</td>'
                      + '<td class="td-r">$' + parseFloat(item.subtotal).toFixed(2) + '</td>'
                      + '</tr>';
               }).join('')
             + '</tbody></table>'
             + '<div class="detalle-totales">'
             + '<div>Subtotal: $' + parseFloat(v.subtotal).toFixed(2) + '</div>'
             + (tieneIva ? '<div>IVA (13%): $' + parseFloat(v.impuesto).toFixed(2) + '</div>' : '')
             + '<div class="detalle-total-final">TOTAL: $' + parseFloat(v.total).toFixed(2) + '</div>'
             + (v.metodo_pago === 'efectivo' ? '<div style="color:#888;font-size:12px">Recibido: $' + parseFloat(v.monto_recibido).toFixed(2) + ' | Cambio: $' + parseFloat(v.cambio).toFixed(2) + '</div>' : '')
             + '</div>';

    document.getElementById('cuerpoDetalle').innerHTML = html;
}

function imprimirDetalle() { window.print(); }

function generarPDFHistorial() {
    var desde = document.getElementById('filtroDesde').value;
    var hasta = document.getElementById('filtroHasta').value;
    
    var url = '/DNS_Pharmacy/controllers/GenerarReportePDF.php?tipo=ventas';
    if (desde) url += '&desde=' + desde;
    if (hasta) url += '&hasta=' + hasta;
    
    window.open(url, '_blank');
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

function badgeMetodo(metodo) {
    var mapa = {
        'efectivo':     '<span class="badge-efectivo">Efectivo</span>',
        'tarjeta':      '<span class="badge-tarjeta">Tarjeta</span>',
        'transferencia': '<span class="badge-transferencia">Digital</span>'
    };
    return mapa[metodo] || '<span>' + esc(metodo) + '</span>';
}

function iniciales(nombre) {
    var partes = nombre.trim().split(' ');
    return partes.length >= 2
        ? (partes[0].charAt(0) + partes[1].charAt(0)).toUpperCase()
        : nombre.charAt(0).toUpperCase();
}

function formatearFechaLegible(fecha) {
    if (!fecha) return '—';
    var d = new Date(fecha);
    if (isNaN(d)) return fecha;
    return d.toLocaleDateString('es-SV', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function mostrarToast(mensaje, tipo) {
    var toast = document.getElementById('toast');
    if (!toast) { toast = document.createElement('div'); toast.id = 'toast'; document.body.appendChild(toast); }
    toast.textContent = mensaje;
    toast.className   = 'toast toast-' + (tipo || 'ok');
    toast.classList.add('toast-visible');
    setTimeout(function() { toast.classList.remove('toast-visible'); }, 3500);
}