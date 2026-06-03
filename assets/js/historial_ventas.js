/* =====================
   HISTORIAL_VENTAS.JS - DNS Pharmacy
   Con envío de correo
   ===================== */

const HV_CONTROLLER = '/DNS_Pharmacy/controllers/HistorialventasController.php';

var ventasData = [];

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    setPeriodoInicial();

    // Cerrar modales al click en overlay
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('activo');
                document.body.style.overflow = '';
            }
        });
    });

    // Fecha por defecto en el modal correo = hoy
    var hoy = formatFecha(new Date());
    var el  = document.getElementById('correoDia');
    if (el) el.value = hoy;
});

function setPeriodoInicial() {
    document.getElementById('filtroDesde').value = '';
    document.getElementById('filtroHasta').value = '';
    document.querySelectorAll('.btn-periodo').forEach(function(b) {
        b.classList.toggle('activo', b.textContent.trim() === 'Todo');
    });
    cargarVentas('', '');
}

/* ══════════════════════════════════════════
   PERÍODOS RÁPIDOS
══════════════════════════════════════════ */
function setPeriodo(periodo, btn) {
    document.querySelectorAll('.btn-periodo').forEach(function(b) { b.classList.remove('activo'); });
    if (btn) btn.classList.add('activo');

    var hoy   = new Date();
    var desde = new Date();
    var hastaStr = formatFecha(hoy);
    var desdeStr = '';

    if (periodo === 'hoy') {
        desdeStr = formatFecha(hoy);
    } else if (periodo === 'semana') {
        var dia = hoy.getDay();
        desde.setDate(hoy.getDate() - (dia === 0 ? 6 : dia - 1));
        desdeStr = formatFecha(desde);
    } else if (periodo === 'mes') {
        desdeStr = formatFecha(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
    } else {
        document.getElementById('filtroDesde').value = '';
        document.getElementById('filtroHasta').value = '';
        cargarVentas('', '');
        return;
    }

    document.getElementById('filtroDesde').value = desdeStr;
    document.getElementById('filtroHasta').value = hastaStr;
    cargarVentas(desdeStr, hastaStr);
}

function filtrarDatos() {
    cargarVentas(
        document.getElementById('filtroDesde').value,
        document.getElementById('filtroHasta').value
    );
}

function formatFecha(d) {
    return d.getFullYear() + '-'
        + String(d.getMonth()+1).padStart(2,'0') + '-'
        + String(d.getDate()).padStart(2,'0');
}

/* ══════════════════════════════════════════
   CARGAR VENTAS
══════════════════════════════════════════ */
function cargarVentas(desde, hasta) {
    var url = HV_CONTROLLER + '?accion=listar';
    if (desde) url += '&desde=' + encodeURIComponent(desde);
    if (hasta) url += '&hasta=' + encodeURIComponent(hasta);

    document.getElementById('cuerpoTabla').innerHTML =
        '<tr><td colspan="10" class="tabla-vacia">⏳ Cargando ventas...</td></tr>';

    fetch(url)
        .then(function(r) { if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
        .then(function(res) {
            if (!res.ok) throw new Error(res.mensaje || 'Error del servidor');
            ventasData = res.datos || [];
            renderizarTabla(ventasData);
            calcularStats(ventasData);
        })
        .catch(function(e) {
            document.getElementById('cuerpoTabla').innerHTML =
                '<tr><td colspan="10" class="tabla-vacia">❌ ' + e.message + '</td></tr>';
            mostrarToast('Error: ' + e.message, 'error');
        });
}

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
function calcularStats(ventas) {
    var total = 0, iva = 0;
    ventas.forEach(function(v) { total += parseFloat(v.total)||0; iva += parseFloat(v.impuesto)||0; });
    var prom = ventas.length > 0 ? total / ventas.length : 0;
    document.getElementById('statTickets').textContent = ventas.length;
    document.getElementById('statTotal').textContent   = '$' + total.toFixed(2);
    document.getElementById('statIva').textContent     = '$' + iva.toFixed(2);
    document.getElementById('statPromedio').textContent= '$' + prom.toFixed(2);
}

/* ══════════════════════════════════════════
   RENDERIZAR TABLA
══════════════════════════════════════════ */
function renderizarTabla(ventas) {
    var tbody = document.getElementById('cuerpoTabla');
    if (!ventas || ventas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="tabla-vacia">📭 No hay ventas en este período.</td></tr>';
        actualizarContador(0, 0); return;
    }
    tbody.innerHTML = ventas.map(function(v, i) {
        var ini    = iniciales(v.nombre_empleado || '');
        var estado = v.estado === 'completada'
            ? '<span class="badge-completada">✅ Completada</span>'
            : '<span class="badge-anulada">❌ Anulada</span>';
        return '<tr data-metodo="' + esc(v.metodo_pago) + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td><span class="td-ticket">#' + esc(v.numero_ticket) + '</span></td>'
             + '<td><div class="td-empleado"><div class="emp-avatar">' + ini + '</div>'
             + '<div class="emp-info"><strong>' + esc(v.nombre_empleado||'—') + '</strong></div></div></td>'
             + '<td>' + formatearFechaLegible(v.fecha_venta) + '</td>'
             + '<td>$' + parseFloat(v.subtotal).toFixed(2) + '</td>'
             + '<td>' + (parseFloat(v.impuesto)>0?'$'+parseFloat(v.impuesto).toFixed(2):'—') + '</td>'
             + '<td class="td-total"><strong>$' + parseFloat(v.total).toFixed(2) + '</strong></td>'
             + '<td>' + badgeMetodo(v.metodo_pago) + '</td>'
             + '<td>' + estado + '</td>'
             + '<td><button class="btn-accion btn-ver" onclick="verDetalle(' + v.id_venta + ')"><i class="bi bi-eye"></i> Ver</button></td>'
             + '</tr>';
    }).join('');
    actualizarContador(ventas.length, ventas.length);
    filtrarPorMetodo();
}

function filtrarPorMetodo() {
    var metodo  = document.getElementById('filtroMetodo').value;
    var filas   = document.querySelectorAll('#cuerpoTabla tr[data-metodo]');
    var visible = 0;
    filas.forEach(function(f) {
        var show = !metodo || f.dataset.metodo === metodo;
        f.style.display = show ? '' : 'none';
        if (show) visible++;
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
            if (!res.ok) { mostrarToast(res.mensaje,'error'); return; }
            renderizarDetalle(res.datos);
            abrirModal('modalDetalle');
        })
        .catch(function() { mostrarToast('Error al cargar detalle','error'); });
}

function renderizarDetalle(d) {
    var v = d.venta;
    var tieneIva = parseFloat(v.impuesto) > 0;
    var filas = d.detalle.map(function(item) {
        return '<tr><td>'+esc(item.nombre_producto)+'</td>'
             + '<td class="td-r">'+item.cantidad+'</td>'
             + '<td class="td-r">$'+parseFloat(item.precio_unitario).toFixed(2)+'</td>'
             + '<td class="td-r">$'+parseFloat(item.subtotal).toFixed(2)+'</td></tr>';
    }).join('');

    document.getElementById('cuerpoDetalle').innerHTML =
        '<div class="detalle-venta-header">'
      + '<div><div class="detalle-ticket">🎫 ' + esc(v.numero_ticket) + '</div>'
      + '<div style="margin-top:8px">' + badgeMetodo(v.metodo_pago) + '</div></div>'
      + '<div class="detalle-meta">'
      + '<div><strong>👤 Cajero:</strong> ' + esc(v.nombre_empleado||'—') + '</div>'
      + '<div><strong>📅 Fecha:</strong> ' + formatearFechaLegible(v.fecha_venta) + '</div>'
      + '<div><strong>📊 Estado:</strong> ' + (v.estado==='completada'?'<span class="badge-completada">Completada</span>':'<span class="badge-anulada">Anulada</span>') + '</div>'
      + '</div></div>'
      + '<table class="detalle-tabla"><thead><tr><th>Producto</th><th class="td-r">Cant.</th><th class="td-r">P.Unit</th><th class="td-r">Subtotal</th></tr></thead>'
      + '<tbody>' + filas + '</tbody></table>'
      + '<div class="detalle-totales">'
      + '<div>💰 Subtotal: $' + parseFloat(v.subtotal).toFixed(2) + '</div>'
      + (tieneIva ? '<div>🧾 IVA (13%): $' + parseFloat(v.impuesto).toFixed(2) + '</div>' : '')
      + '<div class="detalle-total-final">💵 TOTAL: $' + parseFloat(v.total).toFixed(2) + '</div>'
      + (v.metodo_pago==='efectivo'?'<div style="color:#666;font-size:12px;margin-top:6px">Recibido: $'+parseFloat(v.monto_recibido).toFixed(2)+' | Cambio: $'+parseFloat(v.cambio).toFixed(2)+'</div>':'')
      + '</div>';
}

function imprimirDetalle() { window.print(); }

function generarPDFHistorial() {
    var url = '/DNS_Pharmacy/controllers/GenerarReportePDF.php?tipo=ventas';
    var d   = document.getElementById('filtroDesde').value;
    var h   = document.getElementById('filtroHasta').value;
    if (d) url += '&desde=' + d;
    if (h) url += '&hasta=' + h;
    window.open(url, '_blank');
}

/* ══════════════════════════════════════════
   MODAL CORREO
══════════════════════════════════════════ */
var tipoCorreo = 'dia';

function abrirModalCorreo() {
    // Precargar fechas actuales del filtro si hay
    var hoy = formatFecha(new Date());
    document.getElementById('correoDia').value    = document.getElementById('filtroDesde').value || hoy;
    document.getElementById('correoDesde').value  = document.getElementById('filtroDesde').value || '';
    document.getElementById('correoHasta').value  = document.getElementById('filtroHasta').value || hoy;
    document.getElementById('correoError').textContent = '';
    document.getElementById('correoPreview').style.display = 'none';
    document.getElementById('correoDestino').value = '';
    document.getElementById('correoAsunto').value  = 'Historial de Ventas - DNS Pharmacy';
    selTipo(document.querySelector('.ctipo-tab[data-tipo="dia"]'), 'dia');
    abrirModal('modalCorreo');
}

function selTipo(btn, tipo) {
    document.querySelectorAll('.ctipo-tab').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    tipoCorreo = tipo;

    document.getElementById('campoFechaDia').style.display = tipo === 'dia'     ? 'block' : 'none';
    document.getElementById('campoRango').style.display    = tipo === 'rango'   ? 'block' : 'none';
    document.getElementById('campoUsuario').style.display  = tipo === 'usuario' ? 'block' : 'none';
}

function enviarCorreo() {
    var destino = document.getElementById('correoDestino').value.trim();
    var asunto  = document.getElementById('correoAsunto').value.trim();
    var errEl   = document.getElementById('correoError');
    errEl.textContent = '';

    if (!destino) { errEl.textContent = 'Ingresa el correo destino.'; return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(destino)) { errEl.textContent = 'Correo inválido.'; return; }

    var fd = new FormData();
    fd.append('accion',  'enviar_correo');
    fd.append('tipo',    tipoCorreo);
    fd.append('destino', destino);
    fd.append('asunto',  asunto);

    if (tipoCorreo === 'dia') {
        fd.append('desde', document.getElementById('correoDia').value);
    } else if (tipoCorreo === 'rango') {
        fd.append('desde', document.getElementById('correoDesde').value);
        fd.append('hasta', document.getElementById('correoHasta').value);
    } else if (tipoCorreo === 'usuario') {
        fd.append('id_usuario', document.getElementById('correoUsuario').value);
        fd.append('desde',      document.getElementById('correoUsuarioDesde').value);
        fd.append('hasta',      document.getElementById('correoUsuarioHasta').value);
    }

    var btn = document.getElementById('btnEnviarOk');
    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Enviando...';
    btn.disabled  = true;

    fetch(HV_CONTROLLER, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.innerHTML = '<i class="bi bi-send"></i> Enviar correo';
            btn.disabled  = false;
            if (res.ok) {
                cerrarModal('modalCorreo');
                mostrarToast(res.mensaje, 'ok');
            } else {
                errEl.textContent = res.mensaje;
            }
        })
        .catch(function() {
            btn.innerHTML = '<i class="bi bi-send"></i> Enviar correo';
            btn.disabled  = false;
            errEl.textContent = 'Error de conexión.';
        });
}

/* ══════════════════════════════════════════
   UTILIDADES
══════════════════════════════════════════ */
function abrirModal(id) {
    var m = document.getElementById(id);
    if (m) { m.classList.add('activo'); document.body.style.overflow = 'hidden'; }
}
function cerrarModal(id) {
    var m = document.getElementById(id);
    if (m) { m.classList.remove('activo'); document.body.style.overflow = ''; }
}

function badgeMetodo(m) {
    var mapa = { 'efectivo':'<span class="badge-efectivo">💵 Efectivo</span>', 'tarjeta':'<span class="badge-tarjeta">💳 Tarjeta</span>', 'transferencia':'<span class="badge-transferencia">📱 Transferencia</span>' };
    return mapa[m] || '<span>'+esc(m)+'</span>';
}

function iniciales(nombre) {
    if (!nombre) return '?';
    var p = nombre.trim().split(' ');
    return p.length >= 2 ? (p[0][0]+p[1][0]).toUpperCase() : nombre[0].toUpperCase();
}

function formatearFechaLegible(fecha) {
    if (!fecha) return '—';
    var d = new Date(fecha);
    return isNaN(d) ? fecha : d.toLocaleDateString('es-ES',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
}

function esc(str) {
    return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function mostrarToast(mensaje, tipo) {
    var t = document.getElementById('toast-hv');
    if (!t) {
        t = document.createElement('div'); t.id = 'toast-hv';
        t.style.cssText = 'position:fixed;bottom:20px;right:20px;padding:12px 20px;border-radius:8px;color:white;z-index:9999;font-size:13px;font-weight:500;opacity:0;transform:translateY(8px);transition:all .3s;pointer-events:none;max-width:360px;';
        document.body.appendChild(t);
    }
    t.textContent = mensaje;
    t.style.background = tipo === 'error' ? '#c62828' : '#2e7d32';
    t.style.opacity = '1'; t.style.transform = 'translateY(0)';
    setTimeout(function() { t.style.opacity='0'; t.style.transform='translateY(8px)'; }, 4000);
}