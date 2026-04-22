let periodoActual = 'mes';
let charts = {};

const CHART_COLORS = [
    '#841480', '#70ab32', '#f57c00', '#1565c0', '#5c35b5',
    '#00796b', '#d32f2f', '#fbc02d', '#8e24aa', '#c0ca33'
];

document.addEventListener('DOMContentLoaded', () => {
    cargarDashboard();
});

function cambiarPeriodo(valor) {
    periodoActual = valor;
    const filtroCustom = document.getElementById('filtroCustom');
    filtroCustom.style.display = valor === 'custom' ? 'flex' : 'none';

    if (valor !== 'custom') {
        cargarDashboard();
    }
}

function aplicarFiltroCustom() {
    const desde = document.getElementById('fechaDesde').value;
    const hasta = document.getElementById('fechaHasta').value;

    if (!desde || !hasta) {
        alert('Debe seleccionar fecha desde y fecha hasta.');
        return;
    }

    if (desde > hasta) {
        alert('La fecha desde no puede ser mayor que la fecha hasta.');
        return;
    }

    cargarDashboard();
}

function getParams() {
    let params = `periodo=${encodeURIComponent(periodoActual)}`;

    if (periodoActual === 'custom') {
        const desde = document.getElementById('fechaDesde').value;
        const hasta = document.getElementById('fechaHasta').value;
        params += `&desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`;
    }

    return params;
}

async function api(accion) {
    const url = `../controllers/Reportescontroller.php?accion=${accion}&${getParams()}`;
    const res = await fetch(url);
    if (!res.ok) throw new Error(`Error en ${accion}`);
    return await res.json();
}

async function cargarDashboard() {
    try {
        await Promise.all([
            cargarKPIs(),
            cargarVentas(),
            cargarInventario(),
            cargarEmpleados(),
            cargarProveedores(),
            cargarProductos(),
            cargarFinanciero(),
            cargarVencimientos()
        ]);
    } catch (e) {
        console.error(e);
    }
}

function formatMoney(val) {
    return `$${Number(val || 0).toFixed(2)}`;
}

function deltaHtml(delta) {
    if (delta === null || delta === undefined) {
        return `<span class="kpi-delta-neutral">Sin comparación previa</span>`;
    }
    if (Number(delta) > 0) {
        return `<span class="kpi-delta-positive"><i class="bi bi-arrow-up-right"></i> ${delta}% vs período anterior</span>`;
    }
    if (Number(delta) < 0) {
        return `<span class="kpi-delta-negative"><i class="bi bi-arrow-down-right"></i> ${Math.abs(delta)}% vs período anterior</span>`;
    }
    return `<span class="kpi-delta-neutral">Sin variación</span>`;
}

function badgeEstado(estado) {
    const e = String(estado || '').toLowerCase();

    if (e === 'completada' || e === 'ok') {
        return `<span class="badge-estado badge-ok">${estado}</span>`;
    }
    if (e === 'anulada' || e === 'agotado') {
        return `<span class="badge-estado badge-danger">${estado}</span>`;
    }
    if (e === 'registrada') {
        return `<span class="badge-estado badge-info">${estado}</span>`;
    }
    if (e === 'pendiente' || e === 'bajo') {
        return `<span class="badge-estado badge-warning">${estado}</span>`;
    }
    return `<span class="badge-estado badge-secondary">${estado}</span>`;
}

function createOrUpdateChart(id, config) {
    if (charts[id]) {
        charts[id].destroy();
    }
    const ctx = document.getElementById(id);
    if (!ctx) return;
    charts[id] = new Chart(ctx, config);
}

function renderLegend(targetId, labels, colors, values = []) {
    const container = document.getElementById(targetId);
    if (!container) return;

    container.innerHTML = labels.map((label, i) => `
        <div class="legend-item">
            <span class="legend-color" style="background:${colors[i % colors.length]}"></span>
            <span>${label}${values[i] !== undefined ? `: ${values[i]}` : ''}</span>
        </div>
    `).join('');
}

async function cargarKPIs() {
    const data = await api('kpis');

    document.getElementById('kpiVentas').textContent = data.total_ventas ?? 0;
    document.getElementById('kpiIngresos').textContent = formatMoney(data.total_ingresos);
    document.getElementById('kpiTicket').textContent = formatMoney(data.ticket_promedio);
    document.getElementById('kpiUnidades').textContent = data.unidades_vendidas ?? 0;
    document.getElementById('kpiStockBajo').textContent = data.productos_bajo_min ?? 0;
    document.getElementById('kpiCompras').textContent = data.total_compras ?? 0;

    document.getElementById('kpiVentasDelta').innerHTML = deltaHtml(data.delta_ventas);
    document.getElementById('kpiIngresosDelta').innerHTML = deltaHtml(data.delta_ingresos);
    document.getElementById('kpiTicketDelta').innerHTML = deltaHtml(data.delta_ticket);
    document.getElementById('kpiUnidadesDelta').innerHTML = deltaHtml(data.delta_unidades);
    document.getElementById('kpiComprasDelta').innerHTML = deltaHtml(data.delta_compras);
}

async function cargarVentas() {
    const [ventasTiempo, metodoPago, horas, categorias, listaVentas] = await Promise.all([
        api('ventas_tiempo'),
        api('metodo_pago'),
        api('ventas_horas'),
        api('ventas_categorias'),
        api('lista_ventas')
    ]);

    createOrUpdateChart('chartVentasTiempo', {
        type: 'bar',
        data: {
            labels: ventasTiempo.map(i => i.fecha),
            datasets: [{
                label: 'Ingresos',
                data: ventasTiempo.map(i => Number(i.total)),
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartMetodoPago', {
        type: 'doughnut',
        data: {
            labels: metodoPago.map(i => i.metodo),
            datasets: [{
                data: metodoPago.map(i => Number(i.monto)),
                backgroundColor: CHART_COLORS
            }]
        },
        options: doughnutOptions()
    });
    renderLegend('legendMetodoPago', metodoPago.map(i => i.metodo), CHART_COLORS, metodoPago.map(i => formatMoney(i.monto)));

    createOrUpdateChart('chartHoras', {
        type: 'bar',
        data: {
            labels: horas.map(i => `${String(i.hora).padStart(2, '0')}:00`),
            datasets: [{
                label: 'Ventas',
                data: horas.map(i => Number(i.monto)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartCategorias', {
        type: 'bar',
        data: {
            labels: categorias.map(i => i.categoria),
            datasets: [{
                label: 'Ingresos',
                data: categorias.map(i => Number(i.total)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    const tbody = document.getElementById('cuerpoTablaVentas');
    tbody.innerHTML = '';

    if (!listaVentas.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="tabla-vacia">Sin datos para el período seleccionado.</td></tr>`;
        return;
    }

    tbody.innerHTML = listaVentas.map(v => `
        <tr>
            <td>${v.numero_ticket}</td>
            <td>${v.fecha_venta}</td>
            <td>${v.cajero}</td>
            <td>${v.num_productos}</td>
            <td>${formatMoney(v.subtotal)}</td>
            <td>${formatMoney(v.impuesto)}</td>
            <td><strong>${formatMoney(v.total)}</strong></td>
            <td>${capitalizar(v.metodo_pago)}</td>
            <td>${badgeEstado(v.estado)}</td>
        </tr>
    `).join('');
}

async function cargarInventario() {
    const [estadoStock, stockCategoria, movimientos, inversionCategoria, stockBajo] = await Promise.all([
        api('estado_stock'),
        api('stock_categoria'),
        api('movimientos'),
        api('inversion_categoria'),
        api('stock_bajo')
    ]);

    const estadoLabels = ['Stock suficiente', 'Stock bajo', 'Agotado'];
    const estadoValues = [
        Number(estadoStock.ok || 0),
        Number(estadoStock.bajo || 0),
        Number(estadoStock.agotado || 0)
    ];

    createOrUpdateChart('chartEstadoStock', {
        type: 'doughnut',
        data: {
            labels: estadoLabels,
            datasets: [{
                data: estadoValues,
                backgroundColor: ['#16a34a', '#ea580c', '#dc2626']
            }]
        },
        options: doughnutOptions()
    });
    renderLegend('legendEstadoStock', estadoLabels, ['#16a34a', '#ea580c', '#dc2626'], estadoValues);

    createOrUpdateChart('chartStockCategoria', {
        type: 'bar',
        data: {
            labels: stockCategoria.map(i => i.categoria),
            datasets: [{
                label: 'Stock',
                data: stockCategoria.map(i => Number(i.total_stock)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartMovimientos', {
        type: 'line',
        data: {
            labels: movimientos.map(i => i.fecha),
            datasets: [
                { label: 'Entradas', data: movimientos.map(i => Number(i.entradas)), tension: .35 },
                { label: 'Salidas', data: movimientos.map(i => Number(i.salidas)), tension: .35 }
            ]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartInversionCategoria', {
        type: 'bar',
        data: {
            labels: inversionCategoria.map(i => i.categoria),
            datasets: [{
                label: 'Inversión',
                data: inversionCategoria.map(i => Number(i.inversion)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    const tbody = document.getElementById('cuerpoTablaStockBajo');
    tbody.innerHTML = '';

    if (!stockBajo.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="tabla-vacia">No hay productos bajo mínimo.</td></tr>`;
        return;
    }

    tbody.innerHTML = stockBajo.map((p, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${p.nombre}</td>
            <td>${p.categoria}</td>
            <td>${p.stock_actual}</td>
            <td>${p.stock_minimo}</td>
            <td>${formatMoney(p.precio_compra)}</td>
            <td>${Number(p.stock_actual) === 0 ? badgeEstado('agotado') : badgeEstado('bajo')}</td>
        </tr>
    `).join('');
}

async function cargarEmpleados() {
    const [ventasEmpleado, ticketsEmpleado, turnos, ticketProm, ranking] = await Promise.all([
        api('ventas_empleado'),
        api('tickets_empleado'),
        api('turnos_empleado'),
        api('ticket_prom_empleado'),
        api('ranking_empleados')
    ]);

    createOrUpdateChart('chartVentasEmpleado', {
        type: 'bar',
        data: {
            labels: ventasEmpleado.map(i => i.empleado),
            datasets: [{
                label: 'Total vendido',
                data: ventasEmpleado.map(i => Number(i.total)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartTicketsEmpleado', {
        type: 'doughnut',
        data: {
            labels: ticketsEmpleado.map(i => i.empleado),
            datasets: [{
                data: ticketsEmpleado.map(i => Number(i.tickets)),
                backgroundColor: CHART_COLORS
            }]
        },
        options: doughnutOptions()
    });
    renderLegend('legendTicketsEmpleado', ticketsEmpleado.map(i => i.empleado), CHART_COLORS, ticketsEmpleado.map(i => i.tickets));

    createOrUpdateChart('chartTurnos', {
        type: 'bar',
        data: {
            labels: turnos.map(i => i.empleado),
            datasets: [{
                label: 'Turnos',
                data: turnos.map(i => Number(i.turnos)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartTicketPromEmpleado', {
        type: 'bar',
        data: {
            labels: ticketProm.map(i => i.empleado),
            datasets: [{
                label: 'Ticket promedio',
                data: ticketProm.map(i => Number(i.ticket_prom)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    const tbody = document.getElementById('cuerpoTablaEmpleados');
    tbody.innerHTML = '';

    if (!ranking.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="tabla-vacia">Sin datos para el período seleccionado.</td></tr>`;
        return;
    }

    tbody.innerHTML = ranking.map((e, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${e.empleado}</td>
            <td>${e.rol}</td>
            <td>${e.num_ventas}</td>
            <td>${e.num_tickets}</td>
            <td>${formatMoney(e.total_vendido)}</td>
            <td>${formatMoney(e.ticket_promedio)}</td>
            <td>${e.turnos}</td>
        </tr>
    `).join('');
}

async function cargarProveedores() {
    const [comprasProveedor, participacionProveedor, comprasTiempo, topReabastecidos, listaCompras] = await Promise.all([
        api('compras_proveedor'),
        api('participacion_proveedor'),
        api('compras_tiempo'),
        api('top_reabastecidos'),
        api('lista_compras')
    ]);

    createOrUpdateChart('chartComprasProveedor', {
        type: 'bar',
        data: {
            labels: comprasProveedor.map(i => i.proveedor),
            datasets: [{
                label: 'Monto comprado',
                data: comprasProveedor.map(i => Number(i.total)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartParticipacionProveedor', {
        type: 'doughnut',
        data: {
            labels: participacionProveedor.map(i => i.proveedor),
            datasets: [{
                data: participacionProveedor.map(i => Number(i.total)),
                backgroundColor: CHART_COLORS
            }]
        },
        options: doughnutOptions()
    });
    renderLegend('legendParticipacionProveedor', participacionProveedor.map(i => i.proveedor), CHART_COLORS, participacionProveedor.map(i => formatMoney(i.total)));

    createOrUpdateChart('chartComprasTiempo', {
        type: 'line',
        data: {
            labels: comprasTiempo.map(i => i.fecha),
            datasets: [{
                label: 'Compras',
                data: comprasTiempo.map(i => Number(i.total)),
                tension: .35
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartTopReabastecidos', {
        type: 'bar',
        data: {
            labels: topReabastecidos.map(i => i.producto),
            datasets: [{
                label: 'Cantidad',
                data: topReabastecidos.map(i => Number(i.cantidad)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    const tbody = document.getElementById('cuerpoTablaComprasRep');
    tbody.innerHTML = '';

    if (!listaCompras.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="tabla-vacia">Sin compras en el período seleccionado.</td></tr>`;
        return;
    }

    tbody.innerHTML = listaCompras.map(c => `
        <tr>
            <td>${c.numero_factura}</td>
            <td>${c.proveedor}</td>
            <td>${c.fecha_compra}</td>
            <td>${c.num_productos}</td>
            <td>${formatMoney(c.subtotal)}</td>
            <td>${formatMoney(c.impuesto)}</td>
            <td><strong>${formatMoney(c.total)}</strong></td>
            <td>${badgeEstado(c.estado)}</td>
        </tr>
    `).join('');
}

async function cargarProductos() {
    const [topVendidos, margenCategoria, sinMovimiento, rentabilidad, rankingProductos] = await Promise.all([
        api('top_vendidos'),
        api('margen_categoria'),
        api('sin_movimiento'),
        api('rentabilidad_categoria'),
        api('ranking_productos')
    ]);

    createOrUpdateChart('chartTopVendidos', {
        type: 'bar',
        data: {
            labels: topVendidos.map(i => i.producto),
            datasets: [{
                label: 'Unidades',
                data: topVendidos.map(i => Number(i.unidades)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartMargen', {
        type: 'bar',
        data: {
            labels: margenCategoria.map(i => i.categoria),
            datasets: [{
                label: 'Margen %',
                data: margenCategoria.map(i => Number(i.margen_pct)),
                borderRadius: 10
            }]
        },
        options: responsiveOptions('y', true)
    });

    createOrUpdateChart('chartSinMovimiento', {
        type: 'doughnut',
        data: {
            labels: ['Con ventas', 'Sin ventas'],
            datasets: [{
                data: [Number(sinMovimiento.con_ventas || 0), Number(sinMovimiento.sin_ventas || 0)],
                backgroundColor: ['#16a34a', '#dc2626']
            }]
        },
        options: doughnutOptions()
    });
    renderLegend('legendSinMovimiento', ['Con ventas', 'Sin ventas'], ['#16a34a', '#dc2626'], [sinMovimiento.con_ventas || 0, sinMovimiento.sin_ventas || 0]);

    createOrUpdateChart('chartRentabilidad', {
        type: 'bar',
        data: {
            labels: rentabilidad.map(i => i.categoria),
            datasets: [
                { label: 'Ingresos', data: rentabilidad.map(i => Number(i.ingresos)), borderRadius: 10 },
                { label: 'Costos', data: rentabilidad.map(i => Number(i.costos)), borderRadius: 10 }
            ]
        },
        options: responsiveOptions('y', true)
    });

    const tbody = document.getElementById('cuerpoTablaProductosRep');
    tbody.innerHTML = '';

    if (!rankingProductos.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="tabla-vacia">Sin productos para mostrar.</td></tr>`;
        return;
    }

    tbody.innerHTML = rankingProductos.map((p, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${p.producto}</td>
            <td>${p.categoria}</td>
            <td>${p.unidades_vendidas}</td>
            <td>${formatMoney(p.ingresos)}</td>
            <td>${formatMoney(p.costo_total)}</td>
            <td>${Number(p.margen_pct).toFixed(1)}%</td>
        </tr>
    `).join('');
}

function responsiveOptions(axis = 'y', beginAtZero = true) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true }
        },
        scales: {
            [axis]: { beginAtZero }
        }
    };
}

function doughnutOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        }
    };
}

function capitalizar(txt) {
    txt = String(txt || '');
    return txt.charAt(0).toUpperCase() + txt.slice(1);
}

function cambiarTab(button, tabId) {
    document.querySelectorAll('.rep-tab').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.rep-tab-content').forEach(tab => tab.classList.remove('active-tab'));

    button.classList.add('active');
    document.getElementById(tabId).classList.add('active-tab');
}

function filtrarTabla(tablaId, valor) {
    const filtro = valor.toLowerCase();
    const tabla = document.getElementById(tablaId);
    if (!tabla) return;

    const filas = tabla.querySelectorAll('tbody tr');

    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
}

function toggleChartType(chartId, newType, button) {
    if (!charts[chartId]) return;

    const currentChart = charts[chartId];
    const data = currentChart.data;

    currentChart.destroy();

    charts[chartId] = new Chart(document.getElementById(chartId), {
        type: newType,
        data,
        options: responsiveOptions('y', true)
    });

    const parent = button.closest('.chart-toggle');
    if (parent) {
        parent.querySelectorAll('.ct-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
    }
}

async function cargarFinanciero() {
    const [flujo, utilidad] = await Promise.all([
        api('flujo_caja'),
        api('utilidad_neta')
    ]);

    createOrUpdateChart('chartFlujoCaja', {
        type: 'bar',
        data: {
            labels: flujo.map(i => i.fecha),
            datasets: [
                { label: 'Ingresos (Ventas)', data: flujo.map(i => Number(i.ingresos)), backgroundColor: '#70ab32', borderRadius: 6 },
                { label: 'Gastos (Compras)', data: flujo.map(i => Number(i.gastos)), backgroundColor: '#f57c00', borderRadius: 6 }
            ]
        },
        options: responsiveOptions('y', true)
    });

    document.getElementById('kpiUtilidadIngresos').textContent = formatMoney(utilidad?.ingresos_ventas);
    document.getElementById('kpiUtilidadCostos').textContent = formatMoney(utilidad?.costo_ventas);
    document.getElementById('kpiUtilidadNeta').textContent = formatMoney(utilidad?.utilidad_neta);
    document.getElementById('kpiUtilidadMargen').textContent = (utilidad?.margen_pct || 0) + '%';
}

async function cargarVencimientos() {
    const [estado, lotes] = await Promise.all([
        api('estado_lotes'),
        api('lotes_criticos')
    ]);

    const estadoLabels = ['Vigentes (>90 días)', 'Próximos a Vencer (<=90 días)', 'Vencidos'];
    const estadoValues = [
        Number(estado?.vigentes || 0),
        Number(estado?.proximos || 0),
        Number(estado?.vencidos || 0)
    ];

    createOrUpdateChart('chartEstadoLotes', {
        type: 'doughnut',
        data: {
            labels: estadoLabels,
            datasets: [{
                data: estadoValues,
                backgroundColor: ['#70ab32', '#f57c00', '#d32f2f']
            }]
        },
        options: doughnutOptions()
    });
    renderLegend('legendEstadoLotes', estadoLabels, ['#70ab32', '#f57c00', '#d32f2f'], estadoValues);

    const tbody = document.getElementById('cuerpoTablaLotes');
    tbody.innerHTML = '';

    if (!lotes || !lotes.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="tabla-vacia">No hay lotes críticos registrados.</td></tr>`;
        return;
    }

    tbody.innerHTML = lotes.map(l => {
        let estadoStr = l.dias_restantes < 0 ? '<span class="badge-estado badge-agotado">Vencido</span>' : '<span class="badge-estado badge-bajo">Próximo</span>';
        
        return `
        <tr>
            <td><strong>${l.numero_lote || 'N/A'}</strong></td>
            <td>${l.producto}</td>
            <td>${l.fecha_vencimiento}</td>
            <td>${l.dias_restantes < 0 ? 'Hace ' + Math.abs(l.dias_restantes) : l.dias_restantes} días</td>
            <td>${l.cantidad}</td>
            <td>${estadoStr}</td>
        </tr>
    `}).join('');
}