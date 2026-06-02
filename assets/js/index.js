document.addEventListener('DOMContentLoaded', () => {
    iniciarReloj();
    cargarStats();
    cargarVentasRecientes();
    cargarStockBajo();
    cargarComprasRecientes();
});

/* ── Reloj ── */
function iniciarReloj() {
    function actualizar() {
        const ahora = new Date();
        const hora  = ahora.toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit' });
        const fecha = ahora.toLocaleDateString('es-SV', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        document.getElementById('dashHora').textContent  = hora;
        document.getElementById('dashFecha').textContent = fecha;
    }
    actualizar();
    setInterval(actualizar, 1000);
}

/* ── Stats ── */
function cargarStats() {
    fetch(window.BASE_URL + '/controllers/IndexController.php?action=stats')
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            const d = data.data;
            document.getElementById('statVentasHoy').textContent  = d.ventas_hoy        ?? 0;
            document.getElementById('statVentasSub').textContent  = `$${parseFloat(d.total_hoy || 0).toFixed(2)} recaudado`;
            document.getElementById('statProductos').textContent  = d.productos_activos ?? 0;
            document.getElementById('statProductosSub').textContent = `${d.total_productos ?? 0} en catálogo`;
            document.getElementById('statStockBajo').textContent  = d.stock_bajo        ?? 0;
            document.getElementById('statUsuarios').textContent   = d.usuarios_activos  ?? 0;
        })
        .catch(e => console.error('Error stats:', e));
}

/* ── Ventas recientes ── */
function cargarVentasRecientes() {
    fetch(window.BASE_URL + '/controllers/IndexController.php?action=ventas_recientes')
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('dashVentasRecientes');
            if (data.error || !data.data.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="dash-table-empty">No hay ventas hoy.</td></tr>`;
                return;
            }
            tbody.innerHTML = data.data.map(v => `
                <tr>
                    <td><span class="dash-ticket">${esc(v.numero_ticket)}</span></td>
                    <td>${esc(v.empleado)}</td>
                    <td style="font-weight:600;color:#841480">$${parseFloat(v.total).toFixed(2)}</td>
                    <td>${badgeMetodo(v.metodo_pago)}</td>
                    <td style="font-size:12px;color:#888">${formatHora(v.fecha_venta)}</td>
                </tr>
            `).join('');
        })
        .catch(e => console.error('Error ventas:', e));
}

/* ── Stock bajo ── */
function cargarStockBajo() {
    fetch(window.BASE_URL + '/controllers/IndexController.php?action=stock_bajo')
        .then(r => r.json())
        .then(data => {
            const cont = document.getElementById('dashStockBajo');
            if (data.error || !data.data.length) {
                cont.innerHTML = `<div class="dash-empty"><i class="bi bi-check-circle" style="color:#70ab32"></i> Sin alertas de stock.</div>`;
                return;
            }
            cont.innerHTML = data.data.map(p => {
                const pct   = p.stock_minimo > 0 ? Math.min(100, Math.round((p.stock_actual / (p.stock_minimo * 2)) * 100)) : 0;
                const nivel = p.stock_actual === 0 ? 'agotado' : p.stock_actual <= p.stock_minimo ? 'critico' : 'bajo';
                return `
                <div class="stock-item">
                    <div>
                        <div class="stock-item-nombre">${esc(p.nombre)}</div>
                        <div class="stock-item-cat">${esc(p.categoria || '—')}</div>
                    </div>
                    <div class="stock-badge">
                        <div class="stock-bar-bg">
                            <div class="stock-bar-fill bar-${nivel}" style="width:${pct}%"></div>
                        </div>
                        <span class="stock-num ${nivel}">${p.stock_actual}</span>
                    </div>
                </div>`;
            }).join('');
        })
        .catch(e => console.error('Error stock:', e));
}

/* ── Compras recientes ── */
function cargarComprasRecientes() {
    fetch(window.BASE_URL + '/controllers/IndexController.php?action=compras_recientes')
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('dashComprasRecientes');
            if (data.error || !data.data.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="dash-table-empty">No hay compras recientes.</td></tr>`;
                return;
            }
            tbody.innerHTML = data.data.map(c => `
                <tr>
                    <td><span class="dash-ticket">${esc(c.numero_documento)}</span></td>
                    <td>${esc(c.proveedor)}</td>
                    <td style="font-size:12px;color:#888">${formatFecha(c.fecha_compra)}</td>
                    <td style="font-weight:600;color:#841480">$${parseFloat(c.total).toFixed(2)}</td>
                    <td>${badgeEstado(c.estado)}</td>
                </tr>
            `).join('');
        })
        .catch(e => console.error('Error compras:', e));
}

/* ── Helpers ── */
function formatHora(fecha) {
    if (!fecha) return '—';
    return new Date(fecha).toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit' });
}

function formatFecha(fecha) {
    if (!fecha) return '—';
    return new Date(fecha).toLocaleDateString('es-SV', { day: '2-digit', month: 'short', year: 'numeric' });
}

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function badgeMetodo(m) {
    const map = {
        efectivo:      `<span class="badge-metodo metodo-efectivo">Efectivo</span>`,
        tarjeta:       `<span class="badge-metodo metodo-tarjeta">Tarjeta</span>`,
        transferencia: `<span class="badge-metodo metodo-transferencia">Transferencia</span>`
    };
    return map[m] || `<span class="badge-metodo">${esc(m)}</span>`;
}

function badgeEstado(e) {
    const map = {
        completada: `<span class="badge-completada">Completada</span>`,
        pendiente:  `<span class="badge-pendiente">Pendiente</span>`,
        anulada:    `<span class="badge-anulada">Anulada</span>`
    };
    return map[e] || esc(e);
}