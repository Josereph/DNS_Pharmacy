let ventas = [];
let empleados = [];

document.addEventListener('DOMContentLoaded', () => {
    setPeriodo('mes');
    cargarDatos();
});

function cargarDatos() {

    empleados = [
        { id_usuario: 1, id_rol: 1, nombre: 'Carlos', apellido: 'Pérez' },
        { id_usuario: 2, id_rol: 2, nombre: 'María', apellido: 'López' },
        { id_usuario: 3, id_rol: 2, nombre: 'Juan', apellido: 'Martínez' }
    ];

    ventas = [
        {
            id_venta: 1, id_usuario: 2,
            numero_ticket: 'VTA-0001',
            fecha_venta: '2026-03-20 09:15:00',
            subtotal: 45.00, impuesto: 5.85, total: 50.85,
            metodo_pago: 'efectivo',
            estado: 'completada'
        },
        {
            id_venta: 2, id_usuario: 2,
            numero_ticket: 'VTA-0002',
            fecha_venta: '2026-03-20 11:30:00',
            subtotal: 30.00, impuesto: 0, total: 30.00,
            metodo_pago: 'tarjeta',
            estado: 'completada'
        },
        {
            id_venta: 3, id_usuario: 3,
            numero_ticket: 'VTA-0003',
            fecha_venta: '2026-03-21 14:00:00',
            subtotal: 80.00, impuesto: 10.40, total: 90.40,
            metodo_pago: 'efectivo',
            estado: 'completada'
        }
    ];

    cargarFiltroEmpleados();
    filtrarDatos();
}

function cargarFiltroEmpleados() {
    const select = document.getElementById('filtroEmpleado');

    select.innerHTML = `<option value="">Todos</option>` +
        empleados.map(e =>
            `<option value="${e.id_usuario}">${e.nombre} ${e.apellido}</option>`
        ).join('');
}

function filtrarDatos() {
    const desde = document.getElementById('filtroDesde').value;
    const hasta = document.getElementById('filtroHasta').value;
    const empleado = document.getElementById('filtroEmpleado').value;

    const filtradas = ventas.filter(v => {
        const fecha = v.fecha_venta.split(' ')[0];

        return (!desde || fecha >= desde) &&
               (!hasta || fecha <= hasta) &&
               (!empleado || v.id_usuario == empleado) &&
               v.estado === 'completada';
    });

    renderizarTabla(filtradas);
    actualizarStats(filtradas);
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="tabla-vacia">No hay ventas.</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map((v, i) => {
        const emp = empleados.find(e => e.id_usuario === v.id_usuario);

        return `
        <tr>
            <td>${i + 1}</td>
            <td><strong>${v.numero_ticket}</strong></td>
            <td>${emp ? emp.nombre + ' ' + emp.apellido : '—'}</td>
            <td>${formatearFecha(v.fecha_venta)}</td>
            <td>$${v.subtotal.toFixed(2)}</td>
            <td>$${v.impuesto.toFixed(2)}</td>
            <td class="td-total">$${v.total.toFixed(2)}</td>
            <td>${badgeMetodo(v.metodo_pago)}</td>
            <td>
                <button class="btn-accion btn-ver" onclick="verDetalleVenta(${v.id_venta})">
                    Ver
                </button>
            </td>
        </tr>`;
    }).join('');
}

function actualizarStats(lista) {
    const total = lista.reduce((s, v) => s + v.total, 0);

    document.getElementById('statTickets').textContent = lista.length;
    document.getElementById('statTotal').textContent = `$${total.toFixed(2)}`;
}

function verDetalleVenta(id) {
    alert("Aquí puedes mostrar el detalle igual que ya lo tenías");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-SV');
}

function badgeMetodo(m) {
    if (m === 'efectivo') return 'Efectivo';
    if (m === 'tarjeta') return 'Tarjeta';
    return m;
}