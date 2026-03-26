
let empleados = [
    { id_usuario: 1, id_rol: 1, nombre: 'Carlos', apellido: 'Pérez' },
    { id_usuario: 2, id_rol: 2, nombre: 'María', apellido: 'López' },
    { id_usuario: 3, id_rol: 2, nombre: 'Juan', apellido: 'Martínez' }
];

let ventas = [
    { id_venta: 1, id_usuario: 2, numero_ticket: 'VTA-0001', fecha_venta: '2026-03-20 09:15:00', subtotal: 45.00, impuesto: 5.85, total: 50.85, metodo_pago: 'efectivo' },
    { id_venta: 2, id_usuario: 2, numero_ticket: 'VTA-0002', fecha_venta: '2026-03-20 11:30:00', subtotal: 26.55, impuesto: 3.45, total: 30.00, metodo_pago: 'tarjeta' },
    { id_venta: 3, id_usuario: 3, numero_ticket: 'VTA-0003', fecha_venta: '2026-03-21 14:00:00', subtotal: 80.00, impuesto: 10.40, total: 90.40, metodo_pago: 'efectivo' },
    { id_venta: 4, id_usuario: 1, numero_ticket: 'VTA-0004', fecha_venta: '2026-03-22 10:00:00', subtotal: 106.20, impuesto: 13.80, total: 120.00, metodo_pago: 'transferencia' }
];

const roles = { 1: 'Administrador', 2: 'Cajero' };


document.addEventListener('DOMContentLoaded', () => {
    
    const fechaHoy = new Date().toISOString().split('T')[0];
    const inputHasta = document.getElementById('filtroHasta');
    if(inputHasta) inputHasta.value = fechaHoy;
    
  
    filtrarTodo();
});

function filtrarTodo() {
    const desde = document.getElementById('filtroDesde')?.value;
    const hasta = document.getElementById('filtroHasta')?.value;

    const filtradas = ventas.filter(v => {
        const fechaV = v.fecha_venta.split(' ')[0];
        return (!desde || fechaV >= desde) && (!hasta || fechaV <= hasta);
    });

    renderTablaGeneral(filtradas);
    renderTablaEmpleados(filtradas);
}

function renderTablaGeneral(lista) {
    const tbody = document.getElementById('cuerpoTablaGeneral');
    if (!tbody) return;

    let totalAcumulado = 0;

    if (lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">No se encontraron ventas.</td></tr>';
        document.getElementById('statTicketsGeneral').textContent = "0";
        document.getElementById('statTotalGeneral').textContent = "$0.00";
        return;
    }

    tbody.innerHTML = lista.map((v, i) => {
        totalAcumulado += v.total;
        const emp = empleados.find(e => e.id_usuario === v.id_usuario);
        const iniciales = emp ? `${emp.nombre[0]}${emp.apellido[0]}` : '??';
        
        return `
            <tr>
                <td>${i + 1}</td>
                <td><span class="td-ticket">${v.numero_ticket}</span></td>
                <td>
                    <div class="td-empleado">
                        <div class="emp-avatar">${iniciales}</div>
                        <strong>${emp ? emp.nombre + ' ' + emp.apellido : 'Usuario Eliminado'}</strong>
                    </div>
                </td>
                <td>${v.fecha_venta}</td>
                <td>$${v.subtotal.toFixed(2)}</td>
                <td>$${v.impuesto.toFixed(2)}</td>
                <td class="td-total">$${v.total.toFixed(2)}</td>
                <td><span class="badge-metodo metodo-${v.metodo_pago}">${v.metodo_pago.toUpperCase()}</span></td>
                <td><button class="btn btn-sm btn-outline-primary" onclick="verDetalleVenta(${v.id_venta})">Ver</button></td>
            </tr>`;
    }).join('');

 
    document.getElementById('statTicketsGeneral').textContent = lista.length;
    document.getElementById('statTotalGeneral').textContent = `$${totalAcumulado.toFixed(2)}`;
}


function renderTablaEmpleados(listaVentas) {
    const tbody = document.getElementById('cuerpoTablaEmpleados');
    if (!tbody) return;

    const resumen = {};
    let granTotal = 0;

    listaVentas.forEach(v => {
        granTotal += v.total;
        if (!resumen[v.id_usuario]) {
            const emp = empleados.find(e => e.id_usuario === v.id_usuario);
            resumen[v.id_usuario] = { 
                ...emp, 
                n_tickets: 0, 
                monto_total: 0, 
                ultimo_ticket: v.numero_ticket 
            };
        }
        resumen[v.id_usuario].n_tickets++;
        resumen[v.id_usuario].monto_total += v.total;
    });

    const dataFinal = Object.values(resumen);


    if(document.getElementById('statEmpleadosActivos')) document.getElementById('statEmpleadosActivos').textContent = dataFinal.length;
    if(document.getElementById('statTotalVentasEmp')) document.getElementById('statTotalVentasEmp').textContent = `$${granTotal.toFixed(2)}`;

    if (dataFinal.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay actividad de empleados.</td></tr>';
        return;
    }

    tbody.innerHTML = dataFinal.map((e, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>
                <div class="td-empleado">
                    <div class="emp-avatar">${e.nombre[0]}${e.apellido[0]}</div>
                    <strong>${e.nombre} ${e.apellido}</strong>
                </div>
            </td>
            <td><span class="badge badge-light" style="color:#841480; background:#f5e8f5">${roles[e.id_rol]}</span></td>
            <td class="text-center">${e.n_tickets}</td>
            <td class="text-center td-total">$${e.monto_total.toFixed(2)}</td>
            <td><span class="td-ticket">${e.ultimo_ticket}</span></td>
            <td class="text-center"><button class="btn btn-sm btn-success" onclick="verDetalleEmpleado(${e.id_usuario})">Detalle</button></td>
        </tr>`).join('');
}


function setPeriodo(periodo) {
    const hoy = new Date();
    const format = (d) => d.toISOString().split('T')[0];
    
    let fechaDesde = format(hoy);
    let fechaHasta = format(hoy);

    if (periodo === 'semana') {
        const sieteDias = new Date();
        sieteDias.setDate(hoy.getDate() - 7);
        fechaDesde = format(sieteDias);
    } else if (periodo === 'mes') {
        fechaDesde = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-01`;
    } else if (periodo === 'todo') {
        fechaDesde = '';
        fechaHasta = '';
    }

    document.getElementById('filtroDesde').value = fechaDesde;
    document.getElementById('filtroHasta').value = fechaHasta;
    
   
    document.querySelectorAll('.btn-periodo').forEach(btn => btn.classList.remove('activo'));
    if (event) event.target.classList.add('activo');

    filtrarTodo();
}


function verDetalleVenta(id) { alert("Consultando Ticket ID: " + id); }
function verDetalleEmpleado(id) { alert("Consultando Reporte Empleado ID: " + id); }