/* =====================
   HISTORIAL_VENTAS.JS - DNS Pharmacy
   ===================== */

// ✅ Usando el nombre correcto del archivo
const HV_CONTROLLER = '/DNS_Pharmacy/controllers/HistorialventasController.php';

var ventasData = [];

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM cargado');
    console.log('📡 Conectando a:', HV_CONTROLLER);
    
    // Probar conexión
    testConexion();
    
    setPeriodoInicial();
});

function testConexion() {
    fetch(HV_CONTROLLER + '?accion=listar')
        .then(function(response) {
            console.log('📡 Respuesta HTTP:', response.status);
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            console.log('✅ Conexión exitosa:', data);
        })
        .catch(function(error) {
            console.error('❌ Error de conexión:', error);
            mostrarToast('Error: No se puede conectar al controlador', 'error');
        });
}

function setPeriodoInicial() {
    document.getElementById('filtroDesde').value = '';
    document.getElementById('filtroHasta').value = '';
    
    var botones = document.querySelectorAll('.btn-periodo');
    botones.forEach(function(b) {
        if (b.textContent.trim() === 'Todo' || b.textContent.trim() === 'todo') {
            b.classList.add('activo');
        } else {
            b.classList.remove('activo');
        }
    });
    
    cargarVentas('', '');
}

/* ══════════════════════════════════════════
   PERIODOS RÁPIDOS
══════════════════════════════════════════ */
function setPeriodo(periodo) {
    var botones = document.querySelectorAll('.btn-periodo');
    botones.forEach(function(b) { b.classList.remove('activo'); });
    
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('activo');
    }
    
    var hoy = new Date();
    var desde = new Date();
    var hasta = new Date();
    var desdeStr = '';
    var hastaStr = '';

    if (periodo === 'hoy') {
        desde = hoy;
        hasta = hoy;
        desdeStr = formatFecha(desde);
        hastaStr = formatFecha(hasta);
    } else if (periodo === 'semana') {
        var dia = hoy.getDay();
        var inicioSemana = hoy.getDate() - (dia === 0 ? 6 : dia - 1);
        desde.setDate(inicioSemana);
        hasta = hoy;
        desdeStr = formatFecha(desde);
        hastaStr = formatFecha(hasta);
    } else if (periodo === 'mes') {
        desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        hasta = hoy;
        desdeStr = formatFecha(desde);
        hastaStr = formatFecha(hasta);
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
    var desde = document.getElementById('filtroDesde').value;
    var hasta = document.getElementById('filtroHasta').value;
    cargarVentas(desde, hasta);
}

function formatFecha(d) {
    var year = d.getFullYear();
    var month = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
}

/* ══════════════════════════════════════════
   CARGAR VENTAS
══════════════════════════════════════════ */
function cargarVentas(desde, hasta) {
    var url = HV_CONTROLLER + '?accion=listar';
    if (desde) url += '&desde=' + encodeURIComponent(desde);
    if (hasta) url += '&hasta=' + encodeURIComponent(hasta);
    
    console.log('🔄 Cargando:', url);
    
    var tbody = document.getElementById('cuerpoTabla');
    tbody.innerHTML = '<tr><td colspan="10" class="tabla-vacia">⏳ Cargando ventas......</td></tr>';

    fetch(url)
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            return response.json();
        })
        .then(function(res) {
            console.log('📦 Datos recibidos:', res);
            if (!res.ok) {
                throw new Error(res.mensaje || 'Error del servidor');
            }
            ventasData = res.datos || [];
            renderizarTabla(ventasData);
            calcularStats(ventasData);
        })
        .catch(function(error) {
            console.error('❌ Error:', error);
            tbody.innerHTML = '<tr><td colspan="10" class="tabla-vacia">' +
                '❌ Error de conexión<br>' +
                '<small>' + error.message + '</small><br>' +
                '<small>Verifica que el archivo existe en:<br>' + HV_CONTROLLER + '</small>' +
                '</td></tr>';
            mostrarToast('Error: ' + error.message, 'error');
        });
}

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
function calcularStats(ventas) {
    var tickets = ventas.length;
    var total = 0;
    var iva = 0;
    
    for (var i = 0; i < ventas.length; i++) {
        total += parseFloat(ventas[i].total) || 0;
        iva += parseFloat(ventas[i].impuesto) || 0;
    }
    
    var promedio = tickets > 0 ? total / tickets : 0;

    document.getElementById('statTickets').textContent = tickets;
    document.getElementById('statTotal').textContent = '$' + total.toFixed(2);
    document.getElementById('statIva').textContent = '$' + iva.toFixed(2);
    document.getElementById('statPromedio').textContent = '$' + promedio.toFixed(2);
}

/* ══════════════════════════════════════════
   RENDERIZAR TABLA
══════════════════════════════════════════ */
function renderizarTabla(ventas) {
    var tbody = document.getElementById('cuerpoTabla');

    if (!ventas || ventas.length === 0) {
        tbody.innerHTML = '</tr><td colspan="10" class="tabla-vacia">📭 No hay ventas en el período seleccionado.</td></tr>';
        actualizarContador(0, 0);
        return;
    }

    var html = '';
    for (var i = 0; i < ventas.length; i++) {
        var v = ventas[i];
        var ini = iniciales(v.nombre_empleado || '');
        var metodo = badgeMetodo(v.metodo_pago);
        var estado = v.estado === 'completada'
            ? '<span class="badge-completada">✅ Completada</span>'
            : '<span class="badge-anulada">❌ Anulada</span>';
        
        html += '<tr data-metodo="' + esc(v.metodo_pago) + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td><span class="td-ticket">#' + esc(v.numero_ticket) + '</span></td>'
             + '<td><div class="td-empleado">'
             + '<div class="emp-avatar">' + ini + '</div>'
             + '<div class="emp-info"><strong>' + esc(v.nombre_empleado || '—') + '</strong></div>'
             + '</div></td>'
             + '<td>' + formatearFechaLegible(v.fecha_venta) + '</td>'
             + '<td>$' + parseFloat(v.subtotal).toFixed(2) + '</td>'
             + '<td>' + (parseFloat(v.impuesto) > 0 ? '$' + parseFloat(v.impuesto).toFixed(2) : '—') + '</td>'
             + '<td class="td-total"><strong>$' + parseFloat(v.total).toFixed(2) + '</strong></td>'
             + '<td>' + metodo + '</td>'
             + '<td>' + estado + '</td>'
             + '<td><button class="btn-accion btn-ver" onclick="verDetalle(' + v.id_venta + ')"><i class="bi bi-eye"></i> Ver</button></td>'
             + '</tr>';
    }
    
    tbody.innerHTML = html;
    actualizarContador(ventas.length, ventas.length);
    filtrarPorMetodo();
}

function filtrarPorMetodo() {
    var metodo = document.getElementById('filtroMetodo').value;
    var filas = document.querySelectorAll('#cuerpoTabla tr[data-metodo]');
    var visible = 0;
    
    for (var i = 0; i < filas.length; i++) {
        var fila = filas[i];
        if (!metodo || fila.dataset.metodo === metodo) {
            fila.style.display = '';
            visible++;
        } else {
            fila.style.display = 'none';
        }
    }
    
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
    console.log('🔍 Ver detalle venta:', id);
    var url = HV_CONTROLLER + '?accion=detalle&id=' + id;
    
    fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(res) {
            if (!res.ok) {
                mostrarToast(res.mensaje, 'error');
                return;
            }
            renderizarDetalle(res.datos);
            abrirModal('modalDetalle');
        })
        .catch(function(error) {
            console.error('Error:', error);
            mostrarToast('Error al cargar detalle', 'error');
        });
}

function renderizarDetalle(d) {
    var v = d.venta;
    var tieneIva = parseFloat(v.impuesto) > 0;
    
    var htmlDetalle = '';
    for (var i = 0; i < d.detalle.length; i++) {
        var item = d.detalle[i];
        htmlDetalle += '<tr>'
             + '<td>' + esc(item.nombre_producto) + '</td>'
             + '<td class="td-r">' + item.cantidad + '</td>'
             + '<td class="td-r">$' + parseFloat(item.precio_unitario).toFixed(2) + '</td>'
             + '<td class="td-r">$' + parseFloat(item.subtotal).toFixed(2) + '</td>'
             + '</tr>';
    }

    var html = '<div class="detalle-venta-header">'
             + '<div><div class="detalle-ticket">🎫 Ticket: ' + esc(v.numero_ticket) + '</div>'
             + '<div style="margin-top:8px">' + badgeMetodo(v.metodo_pago) + '</div></div>'
             + '<div class="detalle-meta">'
             + '<div><strong>👤 Cajero:</strong> ' + esc(v.nombre_empleado || '—') + '</div>'
             + '<div><strong>📅 Fecha:</strong> ' + formatearFechaLegible(v.fecha_venta) + '</div>'
             + '<div><strong>📊 Estado:</strong> ' + (v.estado === 'completada' ? '<span class="badge-completada">Completada</span>' : '<span class="badge-anulada">Anulada</span>') + '</div>'
             + '</div></div>'
             + '<table class="detalle-tabla">'
             + '<thead><tr><th>Producto</th><th class="td-r">Cantidad</th><th class="td-r">Precio Unit.</th><th class="td-r">Subtotal</th></tr></thead>'
             + '<tbody>' + htmlDetalle + '</tbody>'
             + '</table>'
             + '<div class="detalle-totales">'
             + '<div>💰 Subtotal: $' + parseFloat(v.subtotal).toFixed(2) + '</div>'
             + (tieneIva ? '<div>🧾 IVA (13%): $' + parseFloat(v.impuesto).toFixed(2) + '</div>' : '')
             + '<div class="detalle-total-final">💵 TOTAL: $' + parseFloat(v.total).toFixed(2) + '</div>'
             + (v.metodo_pago === 'efectivo' ? '<div style="color:#666;font-size:12px;margin-top:8px">💵 Recibido: $' + parseFloat(v.monto_recibido).toFixed(2) + ' | 🪙 Cambio: $' + parseFloat(v.cambio).toFixed(2) + '</div>' : '')
             + '</div>';

    document.getElementById('cuerpoDetalle').innerHTML = html;
}

function imprimirDetalle() { 
    window.print(); 
}

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
    var modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModal(id) {
    var modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('activo');
        document.body.style.overflow = '';
    }
}

// Cerrar modal al hacer clic en overlay
document.addEventListener('DOMContentLoaded', function() {
    var overlays = document.querySelectorAll('.modal-overlay');
    for (var i = 0; i < overlays.length; i++) {
        overlays[i].addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('activo');
                document.body.style.overflow = '';
            }
        });
    }
});

function badgeMetodo(metodo) {
    var mapa = {
        'efectivo': '<span class="badge-efectivo">💵 Efectivo</span>',
        'tarjeta': '<span class="badge-tarjeta">💳 Tarjeta</span>',
        'transferencia': '<span class="badge-transferencia">📱 Transferencia</span>'
    };
    return mapa[metodo] || '<span>' + esc(metodo) + '</span>';
}

function iniciales(nombre) {
    if (!nombre || nombre === '') return '?';
    var partes = nombre.trim().split(' ');
    if (partes.length >= 2) {
        return (partes[0].charAt(0) + partes[1].charAt(0)).toUpperCase();
    }
    return nombre.charAt(0).toUpperCase();
}

function formatearFechaLegible(fecha) {
    if (!fecha) return '—';
    var d = new Date(fecha);
    if (isNaN(d.getTime())) return fecha;
    return d.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function mostrarToast(mensaje, tipo) {
    // Solo mostrar errores, no mensajes de éxito
    if (tipo === 'error') {
        var toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            document.body.appendChild(toast);
            
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';
            toast.style.padding = '12px 20px';
            toast.style.borderRadius = '8px';
            toast.style.color = 'white';
            toast.style.zIndex = '9999';
            toast.style.fontSize = '14px';
            toast.style.transition = 'all 0.3s ease';
            toast.style.opacity = '0';
        }
        
        toast.textContent = mensaje;
        toast.style.backgroundColor = '#dc3545';
        toast.style.opacity = '1';
        
        setTimeout(function() {
            toast.style.opacity = '0';
        }, 3000);
    }
}