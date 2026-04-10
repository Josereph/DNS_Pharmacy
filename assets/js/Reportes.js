/* ============================================================
   reportes.js — DNS Pharmacy
   Lógica de reportes: fetch a controllers, Chart.js, filtros
   ============================================================ */

'use strict';

/* ── Estado global ─────────────────────────────────────────── */
const STATE = {
    periodo: 'mes',
    fechaDesde: '',
    fechaHasta: '',
    charts: {},         // instancias Chart.js
    datos: {            // cache de datos por sección
        kpis: null,
        ventasTiempo: null,
        metodoPago: null,
        horas: null,
        categorias: null,
        ventas: null,
        stockEstado: null,
        stockCategoria: null,
        movimientos: null,
        inversionCategoria: null,
        stockBajo: null,
        ventasEmpleado: null,
        ticketsEmpleado: null,
        turnos: null,
        ticketPromEmpleado: null,
        empleados: null,
        comprasProveedor: null,
        participacionProveedor: null,
        comprasTiempo: null,
        topReabastecidos: null,
        compras: null,
        topVendidos: null,
        margen: null,
        sinMovimiento: null,
        rentabilidad: null,
        productosRep: null,
    }
};

/* ── Paleta de colores compartida ─────────────────────────── */
const PALETTE = {
    blue:   '#3b82f6',
    green:  '#22c55e',
    orange: '#f97316',
    purple: '#8b5cf6',
    red:    '#ef4444',
    teal:   '#14b8a6',
    amber:  '#f59e0b',
    pink:   '#ec4899',
    indigo: '#6366f1',
    cyan:   '#06b6d4',
};
const PALETTE_ARRAY = Object.values(PALETTE);

const CHART_DEFAULTS = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 11 },
            bodyFont:  { size: 11 },
            padding: 10,
            cornerRadius: 6,
        }
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 10 }, color: '#94a3b8' },
            border: { display: false }
        },
        y: {
            grid: { color: '#f1f5f9' },
            ticks: { font: { size: 10 }, color: '#94a3b8' },
            border: { display: false }
        }
    }
};

/* ════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    setDefaultDates();
    cargarTodo();
});

function setDefaultDates() {
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    document.getElementById('fechaHasta').value = formatDate(hoy);
    document.getElementById('fechaDesde').value = formatDate(primerDia);
}

function formatDate(d) {
    return d.toISOString().split('T')[0];
}

/* ── Cargar todo en paralelo ──────────────────────────────── */
async function cargarTodo() {
    const params = getPeriodoParams();
    await Promise.all([
        cargarKPIs(params),
        cargarTabVentas(params),
        cargarTabInventario(params),
        cargarTabEmpleados(params),
        cargarTabProveedores(params),
        cargarTabProductos(params),
    ]);
}

function getPeriodoParams() {
    const p = STATE.periodo;
    const params = new URLSearchParams({ periodo: p });
    if (p === 'custom') {
        params.set('desde', STATE.fechaDesde);
        params.set('hasta', STATE.fechaHasta);
    }
    return params;
}

/* ── Helpers de fetch ─────────────────────────────────────── */
async function fetchJSON(url) {
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.json();
    } catch (e) {
        console.error('Fetch error:', url, e);
        return null;
    }
}

const API = '/DNS_Pharmacy/controllers/ReportesController.php?accion=';

/* ════════════════════════════════════════════════════════════
   CAMBIO DE PERÍODO / TABS
════════════════════════════════════════════════════════════ */
function cambiarPeriodo(val) {
    STATE.periodo = val;
    const customEl = document.getElementById('filtroCustom');
    customEl.style.display = val === 'custom' ? 'flex' : 'none';
    if (val !== 'custom') cargarTodo();
}

function aplicarFiltroCustom() {
    STATE.fechaDesde = document.getElementById('fechaDesde').value;
    STATE.fechaHasta = document.getElementById('fechaHasta').value;
    if (!STATE.fechaDesde || !STATE.fechaHasta) {
        alert('Selecciona ambas fechas.');
        return;
    }
    cargarTodo();
}

function cambiarTab(btn, tabId) {
    document.querySelectorAll('.rep-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.rep-tab-content').forEach(t => t.classList.remove('active-tab'));
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active-tab');
    // Forzar resize de charts en el tab activo (por si estaban ocultos)
    setTimeout(() => {
        Object.values(STATE.charts).forEach(c => { try { c.resize(); } catch(e){} });
    }, 50);
}

function toggleChartType(chartId, tipo, btn) {
    document.querySelectorAll('.ct-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const chart = STATE.charts[chartId];
    if (!chart) return;
    chart.config.type = tipo;
    if (tipo === 'line') {
        chart.data.datasets.forEach(ds => {
            ds.fill = false;
            ds.tension = 0.4;
            ds.borderWidth = 2;
            ds.pointRadius = 3;
        });
    } else {
        chart.data.datasets.forEach(ds => {
            ds.fill = undefined;
            ds.tension = undefined;
            ds.borderWidth = undefined;
            ds.pointRadius = undefined;
        });
    }
    chart.update();
}

/* ════════════════════════════════════════════════════════════
   KPIs
════════════════════════════════════════════════════════════ */
async function cargarKPIs(params) {
    const data = await fetchJSON(`${API}kpis&${params}`);
    if (!data) return;

    setKPI('kpiVentas',      data.total_ventas,       data.delta_ventas,      '', false);
    setKPI('kpiIngresos',    data.total_ingresos,      data.delta_ingresos,    '$', true);
    setKPI('kpiTicket',      data.ticket_promedio,     data.delta_ticket,      '$', true);
    setKPI('kpiUnidades',    data.unidades_vendidas,   data.delta_unidades,    '', false);
    setKPI('kpiStockBajo',   data.productos_bajo_min,  null,                   '', false);
    setKPI('kpiCompras',     data.total_compras,       data.delta_compras,     '', false);
}

function setKPI(id, valor, delta, prefijo, decimales) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = valor != null
        ? prefijo + (decimales ? parseFloat(valor).toFixed(2) : parseInt(valor))
        : '—';

    const deltaEl = document.getElementById(id + 'Delta');
    if (deltaEl && delta != null) {
        const pct = parseFloat(delta);
        deltaEl.textContent = (pct >= 0 ? '▲ +' : '▼ ') + Math.abs(pct).toFixed(1) + '% vs período anterior';
        deltaEl.className = 'kpi-delta ' + (pct >= 0 ? 'up' : 'down');
    }
}

/* ════════════════════════════════════════════════════════════
   TAB VENTAS
════════════════════════════════════════════════════════════ */
async function cargarTabVentas(params) {
    await Promise.all([
        cargarVentasTiempo(params),
        cargarMetodoPago(params),
        cargarHoras(params),
        cargarCategoriasVenta(params),
        cargarTablaVentas(params),
    ]);
}

/* Ventas en el tiempo */
async function cargarVentasTiempo(params) {
    const data = await fetchJSON(`${API}ventas_tiempo&${params}`);
    const labels = data ? data.map(r => r.fecha) : mockLabelsTime();
    const valores = data ? data.map(r => parseFloat(r.total)) : mockValues(labels.length, 200, 1500);

    crearOActualizarChart('chartVentasTiempo', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Ingresos ($)',
                data: valores,
                backgroundColor: PALETTE.blue + '33',
                borderColor: PALETTE.blue,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: {
            ...CHART_DEFAULTS,
            plugins: {
                ...CHART_DEFAULTS.plugins,
                tooltip: { ...CHART_DEFAULTS.plugins.tooltip, callbacks: {
                    label: ctx => ' $' + ctx.parsed.y.toFixed(2)
                }}
            }
        }
    });
}

/* Método de pago */
async function cargarMetodoPago(params) {
    const data = await fetchJSON(`${API}metodo_pago&${params}`);
    const labels  = data ? data.map(r => r.metodo) : ['Efectivo', 'Tarjeta', 'Digital'];
    const valores = data ? data.map(r => parseInt(r.cantidad)) : [65, 25, 10];
    const colores = [PALETTE.green, PALETTE.blue, PALETTE.purple];

    crearOActualizarChart('chartMetodoPago', {
        type: 'doughnut',
        data: { labels, datasets: [{ data: valores, backgroundColor: colores, borderWidth: 0, hoverOffset: 6 }] },
        options: { ...CHART_DEFAULTS, scales: {}, plugins: { ...CHART_DEFAULTS.plugins, legend: { display: false } }, cutout: '65%' }
    });
    renderLegend('legendMetodoPago', labels, colores, valores);
}

/* Ventas por hora */
async function cargarHoras(params) {
    const data = await fetchJSON(`${API}ventas_horas&${params}`);
    const horas   = data ? data.map(r => r.hora + ':00') : Array.from({length:12}, (_,i) => (8+i)+':00');
    const valores = data ? data.map(r => parseInt(r.cantidad)) : mockValues(12, 0, 30);

    crearOActualizarChart('chartHoras', {
        type: 'bar',
        data: {
            labels: horas,
            datasets: [{
                label: 'Ventas',
                data: valores,
                backgroundColor: PALETTE.teal + '55',
                borderColor: PALETTE.teal,
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: CHART_DEFAULTS
    });
}

/* Categorías venta */
async function cargarCategoriasVenta(params) {
    const data = await fetchJSON(`${API}ventas_categorias&${params}`);
    const labels  = data ? data.map(r => r.categoria) : ['Analgésicos','Antibióticos','Vitaminas','Jarabes','Higiene'];
    const valores = data ? data.map(r => parseFloat(r.total)) : mockValues(5, 100, 800);

    crearOActualizarChart('chartCategorias', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Ingresos ($)',
                data: valores,
                backgroundColor: PALETTE_ARRAY.slice(0,labels.length).map(c => c+'99'),
                borderColor:     PALETTE_ARRAY.slice(0,labels.length),
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: { ...CHART_DEFAULTS, indexAxis: 'y',
            scales: {
                x: { ...CHART_DEFAULTS.scales.x, grid: { color: '#f1f5f9' } },
                y: { ...CHART_DEFAULTS.scales.y, grid: { display: false } }
            }
        }
    });
}

/* Tabla ventas */
async function cargarTablaVentas(params) {
    const data = await fetchJSON(`${API}lista_ventas&${params}`);
    STATE.datos.ventas = data || [];
    renderTablaVentas(STATE.datos.ventas);
}

function renderTablaVentas(rows) {
    const tbody = document.getElementById('cuerpoTablaVentas');
    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">Sin datos para el período seleccionado.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => `
        <tr>
            <td><strong>${r.numero_ticket}</strong></td>
            <td>${r.fecha_venta}</td>
            <td>${r.cajero}</td>
            <td>${r.num_productos}</td>
            <td>$${parseFloat(r.subtotal).toFixed(2)}</td>
            <td>$${parseFloat(r.impuesto).toFixed(2)}</td>
            <td><strong>$${parseFloat(r.total).toFixed(2)}</strong></td>
            <td>${capitalize(r.metodo_pago)}</td>
            <td><span class="badge-${r.estado}">${capitalize(r.estado)}</span></td>
        </tr>
    `).join('');
}

function filtrarTablaVentas() {
    const q = document.getElementById('buscadorVentas').value.toLowerCase();
    const filtrados = (STATE.datos.ventas || []).filter(r =>
        r.numero_ticket.toLowerCase().includes(q) || r.cajero.toLowerCase().includes(q)
    );
    renderTablaVentas(filtrados);
}

/* ════════════════════════════════════════════════════════════
   TAB INVENTARIO
════════════════════════════════════════════════════════════ */
async function cargarTabInventario(params) {
    await Promise.all([
        cargarEstadoStock(),
        cargarStockCategoria(),
        cargarMovimientos(params),
        cargarInversionCategoria(),
        cargarStockBajo(),
    ]);
}

async function cargarEstadoStock() {
    const data = await fetchJSON(`${API}estado_stock`);
    const labels  = data ? ['Stock OK', 'Stock bajo', 'Agotados'] : ['Stock OK', 'Stock bajo', 'Agotados'];
    const valores = data ? [data.ok, data.bajo, data.agotado] : [45, 12, 5];
    const colores = [PALETTE.green, PALETTE.orange, PALETTE.red];

    crearOActualizarChart('chartEstadoStock', {
        type: 'doughnut',
        data: { labels, datasets: [{ data: valores, backgroundColor: colores, borderWidth: 0, hoverOffset: 6 }] },
        options: { ...CHART_DEFAULTS, scales: {}, cutout: '60%' }
    });
    renderLegend('legendEstadoStock', labels, colores, valores);
}

async function cargarStockCategoria() {
    const data = await fetchJSON(`${API}stock_categoria`);
    const labels  = data ? data.map(r => r.categoria) : ['Analgésicos','Antibióticos','Vitaminas','Jarabes','Higiene'];
    const valores = data ? data.map(r => parseInt(r.total_stock)) : mockValues(5, 30, 200);

    crearOActualizarChart('chartStockCategoria', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Unidades',
                data: valores,
                backgroundColor: PALETTE.indigo + '55',
                borderColor: PALETTE.indigo,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: CHART_DEFAULTS
    });
}

async function cargarMovimientos(params) {
    const data = await fetchJSON(`${API}movimientos&${params}`);
    const labels   = data ? data.map(r => r.fecha) : mockLabelsTime();
    const entradas = data ? data.map(r => parseInt(r.entradas)) : mockValues(labels.length, 0, 50);
    const salidas  = data ? data.map(r => parseInt(r.salidas))  : mockValues(labels.length, 0, 80);

    crearOActualizarChart('chartMovimientos', {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Entradas', data: entradas, borderColor: PALETTE.green, backgroundColor: PALETTE.green+'22', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 2 },
                { label: 'Salidas',  data: salidas,  borderColor: PALETTE.red,   backgroundColor: PALETTE.red+'22',   fill: true, tension: 0.4, borderWidth: 2, pointRadius: 2 },
            ]
        },
        options: { ...CHART_DEFAULTS, plugins: { ...CHART_DEFAULTS.plugins,
            legend: { display: true, position: 'top', labels: { font: { size: 10 }, boxWidth: 10 } }
        }}
    });
}

async function cargarInversionCategoria() {
    const data = await fetchJSON(`${API}inversion_categoria`);
    const labels  = data ? data.map(r => r.categoria) : ['Analgésicos','Antibióticos','Vitaminas','Jarabes','Higiene'];
    const valores = data ? data.map(r => parseFloat(r.inversion)) : mockValues(5, 500, 5000);

    crearOActualizarChart('chartInversionCategoria', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Inversión ($)',
                data: valores,
                backgroundColor: PALETTE.amber + '55',
                borderColor: PALETTE.amber,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: { ...CHART_DEFAULTS, indexAxis: 'y',
            scales: {
                x: { ...CHART_DEFAULTS.scales.x, grid: { color: '#f1f5f9' } },
                y: { ...CHART_DEFAULTS.scales.y, grid: { display: false } }
            }
        }
    });
}

async function cargarStockBajo() {
    const data = await fetchJSON(`${API}stock_bajo`);
    const rows = data || [];
    const tbody = document.getElementById('cuerpoTablaStockBajo');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="tabla-vacia">No hay productos con stock bajo en este momento. ✓</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((r, i) => {
        const estado = parseInt(r.stock_actual) === 0
            ? '<span class="badge-agotado">Agotado</span>'
            : '<span class="badge-bajo">Stock bajo</span>';
        return `
            <tr>
                <td>${i+1}</td>
                <td><strong>${r.nombre}</strong></td>
                <td>${r.categoria}</td>
                <td><strong style="color:#ef4444">${r.stock_actual}</strong></td>
                <td>${r.stock_minimo}</td>
                <td>$${parseFloat(r.precio_compra).toFixed(2)}</td>
                <td>${estado}</td>
            </tr>
        `;
    }).join('');
}

/* ════════════════════════════════════════════════════════════
   TAB EMPLEADOS
════════════════════════════════════════════════════════════ */
async function cargarTabEmpleados(params) {
    await Promise.all([
        cargarVentasEmpleado(params),
        cargarTicketsEmpleado(params),
        cargarTurnos(params),
        cargarTicketPromEmpleado(params),
        cargarTablaEmpleados(params),
    ]);
}

async function cargarVentasEmpleado(params) {
    const data = await fetchJSON(`${API}ventas_empleado&${params}`);
    const nombres = data ? data.map(r => r.empleado) : ['Ana L.','Carlos M.','Sofía R.','Luis P.'];
    const totales = data ? data.map(r => parseFloat(r.total)) : mockValues(4, 500, 5000);

    crearOActualizarChart('chartVentasEmpleado', {
        type: 'bar',
        data: {
            labels: nombres,
            datasets: [{
                label: 'Total vendido ($)',
                data: totales,
                backgroundColor: PALETTE_ARRAY.slice(0,nombres.length).map(c=>c+'88'),
                borderColor: PALETTE_ARRAY.slice(0,nombres.length),
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: CHART_DEFAULTS
    });
}

async function cargarTicketsEmpleado(params) {
    const data = await fetchJSON(`${API}tickets_empleado&${params}`);
    const labels  = data ? data.map(r => r.empleado) : ['Ana L.','Carlos M.','Sofía R.','Luis P.'];
    const valores = data ? data.map(r => parseInt(r.tickets)) : [120, 98, 75, 45];
    const colores = PALETTE_ARRAY.slice(0, labels.length);

    crearOActualizarChart('chartTicketsEmpleado', {
        type: 'doughnut',
        data: { labels, datasets: [{ data: valores, backgroundColor: colores, borderWidth: 0, hoverOffset: 6 }] },
        options: { ...CHART_DEFAULTS, scales: {}, cutout: '60%' }
    });
    renderLegend('legendTicketsEmpleado', labels, colores, valores);
}

async function cargarTurnos(params) {
    const data = await fetchJSON(`${API}turnos_empleado&${params}`);
    const labels  = data ? data.map(r => r.empleado) : ['Ana L.','Carlos M.','Sofía R.','Luis P.'];
    const valores = data ? data.map(r => parseInt(r.turnos)) : [22, 20, 18, 15];

    crearOActualizarChart('chartTurnos', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Turnos',
                data: valores,
                backgroundColor: PALETTE.cyan + '66',
                borderColor: PALETTE.cyan,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: CHART_DEFAULTS
    });
}

async function cargarTicketPromEmpleado(params) {
    const data = await fetchJSON(`${API}ticket_prom_empleado&${params}`);
    const labels  = data ? data.map(r => r.empleado) : ['Ana L.','Carlos M.','Sofía R.','Luis P.'];
    const valores = data ? data.map(r => parseFloat(r.ticket_prom)) : mockValues(4, 8, 25);

    crearOActualizarChart('chartTicketPromEmpleado', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Ticket promedio ($)',
                data: valores,
                backgroundColor: PALETTE.pink + '55',
                borderColor: PALETTE.pink,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: { ...CHART_DEFAULTS,
            plugins: { ...CHART_DEFAULTS.plugins,
                tooltip: { ...CHART_DEFAULTS.plugins.tooltip, callbacks: { label: ctx => ' $' + ctx.parsed.y.toFixed(2) }}
            }
        }
    });
}

async function cargarTablaEmpleados(params) {
    const data = await fetchJSON(`${API}ranking_empleados&${params}`);
    STATE.datos.empleados = data || [];
    const tbody = document.getElementById('cuerpoTablaEmpleados');
    const rows = STATE.datos.empleados;
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="tabla-vacia">Sin datos para el período seleccionado.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((r, i) => {
        const pos = i < 3
            ? `<span class="pos-num pos-${i+1}">${i+1}</span>`
            : `<span class="pos-num" style="background:#f1f5f9;color:#64748b">${i+1}</span>`;
        return `
            <tr>
                <td>${pos}</td>
                <td><strong>${r.empleado}</strong></td>
                <td>${r.rol}</td>
                <td>${r.num_ventas}</td>
                <td>${r.num_tickets}</td>
                <td><strong>$${parseFloat(r.total_vendido).toFixed(2)}</strong></td>
                <td>$${parseFloat(r.ticket_promedio).toFixed(2)}</td>
                <td>${r.turnos}</td>
            </tr>
        `;
    }).join('');
}

/* ════════════════════════════════════════════════════════════
   TAB PROVEEDORES
════════════════════════════════════════════════════════════ */
async function cargarTabProveedores(params) {
    await Promise.all([
        cargarComprasProveedor(params),
        cargarParticipacionProveedor(params),
        cargarComprasTiempo(params),
        cargarTopReabastecidos(params),
        cargarTablaComprasRep(params),
    ]);
}

async function cargarComprasProveedor(params) {
    const data = await fetchJSON(`${API}compras_proveedor&${params}`);
    const labels  = data ? data.map(r => r.proveedor) : ['Distrib. A','Distrib. B','Laborat. C','Farma D'];
    const valores = data ? data.map(r => parseFloat(r.total)) : mockValues(4, 1000, 8000);

    crearOActualizarChart('chartComprasProveedor', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Total compras ($)',
                data: valores,
                backgroundColor: PALETTE.teal + '55',
                borderColor: PALETTE.teal,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: CHART_DEFAULTS
    });
}

async function cargarParticipacionProveedor(params) {
    const data = await fetchJSON(`${API}participacion_proveedor&${params}`);
    const labels  = data ? data.map(r => r.proveedor) : ['Distrib. A','Distrib. B','Laborat. C','Farma D'];
    const valores = data ? data.map(r => parseFloat(r.total)) : [40,30,20,10];
    const colores = PALETTE_ARRAY.slice(0, labels.length);

    crearOActualizarChart('chartParticipacionProveedor', {
        type: 'doughnut',
        data: { labels, datasets: [{ data: valores, backgroundColor: colores, borderWidth: 0, hoverOffset: 6 }] },
        options: { ...CHART_DEFAULTS, scales: {}, cutout: '60%' }
    });
    renderLegend('legendParticipacionProveedor', labels, colores, valores);
}

async function cargarComprasTiempo(params) {
    const data = await fetchJSON(`${API}compras_tiempo&${params}`);
    const labels  = data ? data.map(r => r.fecha) : mockLabelsTime();
    const valores = data ? data.map(r => parseFloat(r.total)) : mockValues(labels.length, 0, 2000);

    crearOActualizarChart('chartComprasTiempo', {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Compras ($)',
                data: valores,
                borderColor: PALETTE.orange,
                backgroundColor: PALETTE.orange + '22',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 3,
            }]
        },
        options: CHART_DEFAULTS
    });
}

async function cargarTopReabastecidos(params) {
    const data = await fetchJSON(`${API}top_reabastecidos&${params}`);
    const labels  = data ? data.map(r => r.producto) : ['Paracetamol','Ibuprofeno','Amoxicilina','Omeprazol','Loratadina'];
    const valores = data ? data.map(r => parseInt(r.cantidad)) : mockValues(5, 20, 200);

    crearOActualizarChart('chartTopReabastecidos', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Unidades',
                data: valores,
                backgroundColor: PALETTE.purple + '55',
                borderColor: PALETTE.purple,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: { ...CHART_DEFAULTS, indexAxis: 'y',
            scales: {
                x: { ...CHART_DEFAULTS.scales.x, grid: { color: '#f1f5f9' } },
                y: { ...CHART_DEFAULTS.scales.y, grid: { display: false } }
            }
        }
    });
}

async function cargarTablaComprasRep(params) {
    const data = await fetchJSON(`${API}lista_compras&${params}`);
    STATE.datos.compras = data || [];
    renderTablaComprasRep(STATE.datos.compras);
}

function renderTablaComprasRep(rows) {
    const tbody = document.getElementById('cuerpoTablaComprasRep');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="tabla-vacia">Sin compras en este período.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => `
        <tr>
            <td><strong>${r.numero_factura}</strong></td>
            <td>${r.proveedor}</td>
            <td>${r.fecha_compra}</td>
            <td>${r.num_productos}</td>
            <td>$${parseFloat(r.subtotal).toFixed(2)}</td>
            <td>$${parseFloat(r.impuesto).toFixed(2)}</td>
            <td><strong>$${parseFloat(r.total).toFixed(2)}</strong></td>
            <td><span class="badge-${r.estado}">${capitalize(r.estado)}</span></td>
        </tr>
    `).join('');
}

function filtrarTablaCompras() {
    const q = document.getElementById('buscadorCompras').value.toLowerCase();
    renderTablaComprasRep((STATE.datos.compras || []).filter(r =>
        r.proveedor.toLowerCase().includes(q) || r.numero_factura.toLowerCase().includes(q)
    ));
}

/* ════════════════════════════════════════════════════════════
   TAB PRODUCTOS
════════════════════════════════════════════════════════════ */
async function cargarTabProductos(params) {
    await Promise.all([
        cargarTopVendidos(params),
        cargarMargen(params),
        cargarSinMovimiento(params),
        cargarRentabilidad(params),
        cargarTablaProductosRep(params),
    ]);
}

async function cargarTopVendidos(params) {
    const data = await fetchJSON(`${API}top_vendidos&${params}`);
    const labels  = data ? data.map(r => r.producto) : ['Paracetamol','Ibuprofeno','Amoxicilina','Omeprazol','Loratadina','Ranitidina','Metformina','Atorvastatina','Enalapril','Aspirina'];
    const valores = data ? data.map(r => parseInt(r.unidades)) : mockValues(10, 10, 200);

    crearOActualizarChart('chartTopVendidos', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Unidades vendidas',
                data: valores,
                backgroundColor: PALETTE_ARRAY.slice(0,labels.length).map(c=>c+'88'),
                borderColor: PALETTE_ARRAY.slice(0,labels.length),
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: CHART_DEFAULTS
    });
}

async function cargarMargen(params) {
    const data = await fetchJSON(`${API}margen_categoria&${params}`);
    const labels  = data ? data.map(r => r.categoria) : ['Analgésicos','Antibióticos','Vitaminas','Jarabes','Higiene'];
    const valores = data ? data.map(r => parseFloat(r.margen_pct)) : [32, 28, 45, 20, 38];

    crearOActualizarChart('chartMargen', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Margen (%)',
                data: valores,
                backgroundColor: PALETTE.green + '55',
                borderColor: PALETTE.green,
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: { ...CHART_DEFAULTS,
            plugins: { ...CHART_DEFAULTS.plugins,
                tooltip: { ...CHART_DEFAULTS.plugins.tooltip, callbacks: { label: ctx => ' ' + ctx.parsed.y.toFixed(1) + '%' }}
            }
        }
    });
}

async function cargarSinMovimiento(params) {
    const data = await fetchJSON(`${API}sin_movimiento&${params}`);
    const labels  = data ? ['Con ventas', 'Sin ventas'] : ['Con ventas', 'Sin ventas'];
    const valores = data ? [data.con_ventas, data.sin_ventas] : [80, 20];
    const colores = [PALETTE.blue, PALETTE.gray || '#e2e8f0'];

    crearOActualizarChart('chartSinMovimiento', {
        type: 'doughnut',
        data: { labels, datasets: [{ data: valores, backgroundColor: colores, borderWidth: 0, hoverOffset: 6 }] },
        options: { ...CHART_DEFAULTS, scales: {}, cutout: '60%' }
    });
    renderLegend('legendSinMovimiento', labels, colores, valores);
}

async function cargarRentabilidad(params) {
    const data = await fetchJSON(`${API}rentabilidad_categoria&${params}`);
    const labels   = data ? data.map(r => r.categoria) : ['Analgésicos','Antibióticos','Vitaminas','Jarabes','Higiene'];
    const ingresos = data ? data.map(r => parseFloat(r.ingresos)) : mockValues(5, 500, 3000);
    const costos   = data ? data.map(r => parseFloat(r.costos))   : ingresos.map(v => v * 0.65);

    crearOActualizarChart('chartRentabilidad', {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Ingresos ($)', data: ingresos, backgroundColor: PALETTE.blue+'88',   borderColor: PALETTE.blue,   borderWidth: 2, borderRadius: 4 },
                { label: 'Costo ($)',    data: costos,   backgroundColor: PALETTE.orange+'88', borderColor: PALETTE.orange, borderWidth: 2, borderRadius: 4 },
            ]
        },
        options: { ...CHART_DEFAULTS,
            plugins: { ...CHART_DEFAULTS.plugins,
                legend: { display: true, position: 'top', labels: { font: { size: 10 }, boxWidth: 10 } }
            }
        }
    });
}

async function cargarTablaProductosRep(params) {
    const data = await fetchJSON(`${API}ranking_productos&${params}`);
    STATE.datos.productosRep = data || [];
    renderTablaProductosRep(STATE.datos.productosRep);
}

function renderTablaProductosRep(rows) {
    const tbody = document.getElementById('cuerpoTablaProductosRep');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="tabla-vacia">Sin datos para el período seleccionado.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((r, i) => {
        const ganancia = parseFloat(r.ingresos) - parseFloat(r.costo_total);
        const margen   = parseFloat(r.margen_pct);
        const gananciaClass = ganancia >= 0 ? 'ganancia-pos' : 'ganancia-neg';
        const pos = i < 3
            ? `<span class="pos-num pos-${i+1}">${i+1}</span>`
            : `<span class="pos-num" style="background:#f1f5f9;color:#64748b">${i+1}</span>`;
        return `
            <tr>
                <td>${pos}</td>
                <td><strong>${r.producto}</strong></td>
                <td>${r.categoria}</td>
                <td>${r.unidades_vendidas}</td>
                <td>$${parseFloat(r.ingresos).toFixed(2)}</td>
                <td>$${parseFloat(r.costo_total).toFixed(2)}</td>
                <td class="${gananciaClass}">$${ganancia.toFixed(2)}</td>
                <td class="${gananciaClass}">${margen.toFixed(1)}%</td>
            </tr>
        `;
    }).join('');
}

function filtrarTablaProductos() {
    const q = document.getElementById('buscadorProductos').value.toLowerCase();
    renderTablaProductosRep((STATE.datos.productosRep || []).filter(r =>
        r.producto.toLowerCase().includes(q) || r.categoria.toLowerCase().includes(q)
    ));
}

/* ════════════════════════════════════════════════════════════
   UTILIDADES DE CHART.JS
════════════════════════════════════════════════════════════ */
function crearOActualizarChart(canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    if (STATE.charts[canvasId]) {
        STATE.charts[canvasId].destroy();
    }
    STATE.charts[canvasId] = new Chart(canvas.getContext('2d'), config);
}

function renderLegend(containerId, labels, colores, valores) {
    const el = document.getElementById(containerId);
    if (!el) return;
    const total = valores.reduce((a, b) => a + b, 0);
    el.innerHTML = labels.map((lbl, i) => {
        const pct = total ? Math.round(valores[i] / total * 100) : 0;
        return `
            <div class="chart-legend-item">
                <span class="legend-dot" style="background:${colores[i]}"></span>
                <span>${lbl}: <strong>${valores[i]}</strong> (${pct}%)</span>
            </div>
        `;
    }).join('');
}

/* ════════════════════════════════════════════════════════════
   EXPORTAR PDF
════════════════════════════════════════════════════════════ */
function exportarPDF() {
    const periodo = document.getElementById('filtroPeriodo').value;
    let url = '/DNS_Pharmacy/controllers/ExportarReportePDF.php?periodo=' + periodo;
 
    // Si es período personalizado, pasar las fechas
    if (periodo === 'custom') {
        const desde = document.getElementById('fechaDesde').value;
        const hasta = document.getElementById('fechaHasta').value;
        if (!desde || !hasta) {
            alert('Selecciona el rango de fechas personalizado antes de exportar.');
            return;
        }
        url += '&desde=' + desde + '&hasta=' + hasta;
    }
 
    // Abrir en nueva pestaña — mPDF enviará el PDF como descarga
    window.open(url, '_blank');
}

/* ════════════════════════════════════════════════════════════
   HELPERS
════════════════════════════════════════════════════════════ */
function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function mockValues(n, min, max) {
    return Array.from({length: n}, () => Math.floor(Math.random() * (max - min) + min));
}

function mockLabelsTime() {
    const labels = [];
    const hoy = new Date();
    for (let i = 29; i >= 0; i--) {
        const d = new Date(hoy); d.setDate(d.getDate() - i);
        labels.push(`${d.getDate()}/${d.getMonth()+1}`);
    }
    return labels;
}