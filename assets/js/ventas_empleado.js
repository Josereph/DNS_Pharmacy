let ventas = [];
let empleados = [];
let resumenEmpleados = [];

const roles = { 1: 'Administrador', 2: 'Cajero' };
const rolesClase = { 1: 'rol-admin', 2: 'rol-cajero' };

document.addEventListener('DOMContentLoaded', () => {
    setPeriodo('mes');
    cargarDatos();
});

function cargarDatos() {
    empleados = [
        { id_usuario: 1, id_rol: 1, nombre: 'Carlos',    apellido: 'Pérez',    correo: 'cperez@dnspharmacy.com' },
        { id_usuario: 2, id_rol: 2, nombre: 'María',     apellido: 'López',    correo: 'mlopez@dnspharmacy.com' },
        { id_usuario: 3, id_rol: 2, nombre: 'Juan',      apellido: 'Martínez', correo: 'jmartinez@dnspharmacy.com' }
    ];

    ventas = [
        {
            id_venta: 1, id_usuario: 2,
            numero_ticket: 'VTA-0001', fecha_venta: '2026-03-20 09:15:00',
            subtotal: 45.00, impuesto: 5.85, total: 50.85,
            monto_recibido: 60.00, cambio: 9.15,
            metodo_pago: 'efectivo', estado: 'completada', observaciones: '',
            created_at: '2026-03-20 09:15:00', updated_at: '2026-03-20 09:15:00',
            detalle: [
                { id_detalle_venta: 1, id_venta: 1, id_producto: 1, id_lote: null, nombre: 'Ibuprofeno 400mg',  cantidad: 3, precio_unitario: 5.80,  subtotal: 17.40, created_at: '2026-03-20 09:15:00', updated_at: '2026-03-20 09:15:00' },
                { id_detalle_venta: 2, id_venta: 1, id_producto: 3, id_lote: null, nombre: 'Vitamina C 1000mg', cantidad: 5, precio_unitario: 3.50,  subtotal: 17.50, created_at: '2026-03-20 09:15:00', updated_at: '2026-03-20 09:15:00' },
                { id_detalle_venta: 3, id_venta: 1, id_producto: 5, id_lote: null, nombre: 'Metformina 850mg',  cantidad: 1, precio_unitario: 11.00, subtotal: 11.00, created_at: '2026-03-20 09:15:00', updated_at: '2026-03-20 09:15:00' }
            ]
        },
        {
            id_venta: 2, id_usuario: 2,
            numero_ticket: 'VTA-0002', fecha_venta: '2026-03-20 11:30:00',
            subtotal: 30.00, impuesto: 0, total: 30.00,
            monto_recibido: 30.00, cambio: 0.00,
            metodo_pago: 'tarjeta', estado: 'completada', observaciones: '',
            created_at: '2026-03-20 11:30:00', updated_at: '2026-03-20 11:30:00',
            detalle: [
                { id_detalle_venta: 4, id_venta: 2, id_producto: 2, id_lote: null, nombre: 'Amoxicilina 500mg', cantidad: 2, precio_unitario: 9.25, subtotal: 18.50, created_at: '2026-03-20 11:30:00', updated_at: '2026-03-20 11:30:00' },
                { id_detalle_venta: 5, id_venta: 2, id_producto: 4, id_lote: null, nombre: 'Hidrocortisona',    cantidad: 1, precio_unitario: 4.90, subtotal: 4.90,  created_at: '2026-03-20 11:30:00', updated_at: '2026-03-20 11:30:00' }
            ]
        },
        {
            id_venta: 3, id_usuario: 3,
            numero_ticket: 'VTA-0003', fecha_venta: '2026-03-21 14:00:00',
            subtotal: 80.00, impuesto: 10.40, total: 90.40,
            monto_recibido: 100.00, cambio: 9.60,
            metodo_pago: 'efectivo', estado: 'completada', observaciones: 'Cliente frecuente',
            created_at: '2026-03-21 14:00:00', updated_at: '2026-03-21 14:00:00',
            detalle: [
                { id_detalle_venta: 6, id_venta: 3, id_producto: 5, id_lote: null, nombre: 'Metformina 850mg',  cantidad: 5, precio_unitario: 11.00, subtotal: 55.00, created_at: '2026-03-21 14:00:00', updated_at: '2026-03-21 14:00:00' },
                { id_detalle_venta: 7, id_venta: 3, id_producto: 1, id_lote: null, nombre: 'Ibuprofeno 400mg',  cantidad: 5, precio_unitario: 5.80,  subtotal: 29.00, created_at: '2026-03-21 14:00:00', updated_at: '2026-03-21 14:00:00' }
            ]
        }
    ];

    filtrarDatos();
}

function filtrarDatos() {
    const desde = document.getElementById('filtroDesde').value;
    const hasta = document.getElementById('filtroHasta').value;

    const filtradas = ventas.filter(v => {
        const fecha = v.fecha_venta.split(' ')[0];
        const desdeOk = !desde || fecha >= desde;
        const hastaOk = !hasta || fecha <= hasta;
        return desdeOk && hastaOk && v.estado === 'completada';
    });

    calcularResumen(filtradas);
}

function calcularResumen(ventasFiltradas) {
    const mapa = {};

    ventasFiltradas.forEach(v => {
        if (!mapa[v.id_usuario]) {
            const emp = empleados.find(e => e.id_usuario === v.id_usuario);
            mapa[v.id_usuario] = {
                id_usuario:     v.id_usuario,
                nombre:         emp ? emp.nombre : '—',
                apellido:       emp ? emp.apellido : '',
                id_rol:         emp ? emp.id_rol : null,
                tickets:        0,
                subtotal:       0,
                impuesto:       0,
                total:          0,
                ultimo_ticket:  null,
                ventas:         []
            };
        }
        mapa[v.id_usuario].tickets++;
        mapa[v.id_usuario].subtotal  += v.subtotal;
        mapa[v.id_usuario].impuesto  += v.impuesto;
        mapa[v.id_usuario].total     += v.total;
        mapa[v.id_usuario].ultimo_ticket = v.fecha_venta;
        mapa[v.id_usuario].ventas.push(v);
    });

    resumenEmpleados = Object.values(mapa).sort((a, b) => b.total - a.total);

    actualizarStats(ventasFiltradas);
    renderizarTabla(resumenEmpleados);
}

function actualizarStats(ventasFiltradas) {
    const totalMonto   = ventasFiltradas.reduce((s, v) => s + v.total, 0);
    const totalTickets = ventasFiltradas.length;
    const empleadosActivos = resumenEmpleados.length;
    const promedio = empleadosActivos > 0 ? totalMonto / empleadosActivos : 0;

    document.getElementById('statEmpleados').textContent = empleadosActivos;
    document.getElementById('statTickets').textContent   = totalTickets;
    document.getElementById('statTotal').textContent     = `$${totalMonto.toFixed(2)}`;
    document.getElementById('statPromedio').textContent  = `$${promedio.toFixed(2)}`;
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="tabla-vacia">No hay ventas en el período seleccionado.</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map((e, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>
                <div class="td-empleado">
                    <div class="emp-avatar">${iniciales(e.nombre, e.apellido)}</div>
                    <div class="emp-info">
                        <strong>${esc(e.nombre)} ${esc(e.apellido)}</strong>
                        <small>ID: ${e.id_usuario}</small>
                    </div>
                </div>
            </td>
            <td><span class="badge-rol ${rolesClase[e.id_rol] || ''}">${roles[e.id_rol] || '—'}</span></td>
            <td><strong>${e.tickets}</strong></td>
            <td class="td-monto">$${e.subtotal.toFixed(2)}</td>
            <td class="td-monto">$${e.impuesto.toFixed(2)}</td>
            <td class="td-total">$${e.total.toFixed(2)}</td>
            <td class="td-fecha">${formatearFecha(e.ultimo_ticket)}</td>
            <td>
                <button class="btn-accion btn-ver" title="Ver ventas" onclick="verDetalleEmpleado(${e.id_usuario})">
                    <i class="bi bi-eye"></i> Ver ventas
                </button>
            </td>
        </tr>
    `).join('');
}

function verDetalleEmpleado(id_usuario) {
    const e = resumenEmpleados.find(x => x.id_usuario === id_usuario);
    if (!e) return;

    document.getElementById('tituloModalDetalle').textContent = `Ventas — ${e.nombre} ${e.apellido}`;

    document.getElementById('cuerpoDetalleEmpleado').innerHTML = `
        <div class="emp-detalle-header">
            <div class="emp-detalle-avatar">${iniciales(e.nombre, e.apellido)}</div>
            <div>
                <div class="emp-detalle-nombre">${esc(e.nombre)} ${esc(e.apellido)}</div>
                <span class="badge-rol ${rolesClase[e.id_rol] || ''}">${roles[e.id_rol] || '—'}</span>
            </div>
        </div>

        <div class="emp-detalle-stats">
            <div class="emp-stat-mini">
                <span class="valor">${e.tickets}</span>
                <div class="lbl">Tickets</div>
            </div>
            <div class="emp-stat-mini">
                <span class="valor">$${e.subtotal.toFixed(2)}</span>
                <div class="lbl">Subtotal</div>
            </div>
            <div class="emp-stat-mini">
                <span class="valor">$${e.total.toFixed(2)}</span>
                <div class="lbl">Total vendido</div>
            </div>
        </div>

        <div class="form-seccion">Tickets registrados</div>
        <table class="tabla-ventas-modal">
            <thead>
                <tr>
                    <th>N° Ticket</th>
                    <th>Fecha</th>
                    <th>Subtotal</th>
                    <th>Impuesto</th>
                    <th>Total</th>
                    <th>Método pago</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                ${e.ventas.map(v => `
                    <tr>
                        <td><span class="td-ticket">${esc(v.numero_ticket)}</span></td>
                        <td class="td-fecha">${formatearFecha(v.fecha_venta)}</td>
                        <td class="td-monto">$${v.subtotal.toFixed(2)}</td>
                        <td class="td-monto">$${v.impuesto.toFixed(2)}</td>
                        <td class="td-total">$${v.total.toFixed(2)}</td>
                        <td>${badgeMetodo(v.metodo_pago)}</td>
                        <td>
                            <button class="btn-accion btn-ver" onclick="verDetalleVenta(${v.id_venta})">
                                <i class="bi bi-receipt"></i>
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    abrirModal('modalDetalleEmpleado');
}

function verDetalleVenta(id_venta) {
    const v = ventas.find(x => x.id_venta === id_venta);
    if (!v) return;

    document.getElementById('tituloModalVenta').textContent = `Ticket ${v.numero_ticket}`;

    document.getElementById('cuerpoDetalleVenta').innerHTML = `
        <div class="venta-detalle-header">
            <div>
                <div class="venta-ticket">${esc(v.numero_ticket)}</div>
                <div class="venta-fecha">${formatearFecha(v.fecha_venta)}</div>
            </div>
            ${badgeMetodo(v.metodo_pago)}
        </div>

        <div class="form-seccion">Productos vendidos</div>
        <table class="tabla-detalle-venta">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                ${v.detalle.map(d => `
                    <tr>
                        <td>${esc(d.nombre)}</td>
                        <td>${d.cantidad}</td>
                        <td>$${d.precio_unitario.toFixed(2)}</td>
                        <td><strong>$${d.subtotal.toFixed(2)}</strong></td>
                    </tr>
                `).join('')}
            </tbody>
        </table>

        <div class="totales-grid">
            <div class="total-row"><span>Subtotal</span><span>$${v.subtotal.toFixed(2)}</span></div>
            <div class="total-row"><span>Impuesto</span><span>$${v.impuesto.toFixed(2)}</span></div>
            <div class="total-row total-final"><span>Total</span><span>$${v.total.toFixed(2)}</span></div>
            <div class="total-row"><span>Monto recibido</span><span>$${v.monto_recibido.toFixed(2)}</span></div>
            <div class="total-row total-cambio"><span>Cambio</span><span>$${v.cambio.toFixed(2)}</span></div>
        </div>

        ${v.observaciones ? `<p style="font-size:12px;color:#888;margin-top:10px"><strong>Obs:</strong> ${esc(v.observaciones)}</p>` : ''}
    `;

    abrirModal('modalDetalleVenta');
}

function setPeriodo(periodo) {
    document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('activo'));
    event.target.classList.add('activo');

    const hoy   = new Date();
    const fecha = d => d.toISOString().split('T')[0];

    if (periodo === 'hoy') {
        document.getElementById('filtroDesde').value = fecha(hoy);
        document.getElementById('filtroHasta').value = fecha(hoy);
    } else if (periodo === 'semana') {
        const lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - hoy.getDay() + 1);
        document.getElementById('filtroDesde').value = fecha(lunes);
        document.getElementById('filtroHasta').value = fecha(hoy);
    } else if (periodo === 'mes') {
        document.getElementById('filtroDesde').value = `${hoy.getFullYear()}-${String(hoy.getMonth()+1).padStart(2,'0')}-01`;
        document.getElementById('filtroHasta').value = fecha(hoy);
    } else {
        document.getElementById('filtroDesde').value = '';
        document.getElementById('filtroHasta').value = '';
    }

    filtrarDatos();
}

function abrirModal(id)  { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

function iniciales(nombre, apellido) {
    return ((nombre||'').charAt(0) + (apellido||'').charAt(0)).toUpperCase();
}

function formatearFecha(fecha) {
    if (!fecha) return '—';
    const d = new Date(fecha);
    if (isNaN(d)) return fecha;
    return d.toLocaleDateString('es-SV', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function badgeMetodo(metodo) {
    const map = {
        efectivo:      `<span class="badge-metodo metodo-efectivo">Efectivo</span>`,
        tarjeta:       `<span class="badge-metodo metodo-tarjeta">Tarjeta</span>`,
        transferencia: `<span class="badge-metodo metodo-transferencia">Transferencia</span>`
    };
    return map[metodo] || `<span class="badge-metodo">${esc(metodo)}</span>`;
}