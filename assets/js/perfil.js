let misVentas = [];
let todasMisVentas = [];

const usuarioSesion = {};

const roles = { 1: 'Administrador', 2: 'Cajero' };
const rolesClase = { 1: 'rol-admin', 2: 'rol-cajero' };

document.addEventListener('DOMContentLoaded', () => {
    obtenerPerfil();
    cargarMisVentas();
    setPeriodo('mes');
});


function obtenerPerfil() {
    fetch('../controllers/PerfilController.php?action=perfil')
        .then(res => res.json())
        .then(data => {
            if (data.error) return console.error(data.mensaje);

            Object.assign(usuarioSesion, data.data);
            pintarPerfil();
        });
}

function pintarPerfil() {
    const u = usuarioSesion;
    const ini = (u.nombre?.[0] || '') + (u.apellido?.[0] || '');

    document.getElementById('perfilAvatar').textContent = ini.toUpperCase();
    document.getElementById('perfilNombreCompleto').textContent = `${u.nombre} ${u.apellido}`;
    document.getElementById('perfilRol').textContent = roles[u.id_rol] || '—';
    document.getElementById('perfilRol').className = `badge-rol ${rolesClase[u.id_rol] || ''}`;
    
    

    document.getElementById('perfilCorreo').textContent = u.correo || '—';
    document.getElementById('perfilTelefono').textContent = u.telefono || '—';
    document.getElementById('perfilUltimoAcceso').textContent = formatearFecha(u.ultimo_acceso);
}


function cargarMisVentas() {
    fetch('../controllers/PerfilController.php?action=ventas')
        .then(res => res.json())
        .then(data => {
            if (data.error) return console.error(data.mensaje);

            todasMisVentas = data.data;
            filtrarMisVentas();
        });
}

function filtrarMisVentas() {
    const desde = document.getElementById('filtroDesde').value;
    const hasta = document.getElementById('filtroHasta').value;
    const hoy = new Date().toISOString().split('T')[0];

    misVentas = todasMisVentas.filter(v => {
        const fecha = v.fecha_venta.split(' ')[0];
        return (!desde || fecha >= desde) && (!hasta || fecha <= hasta);
    });

    const ventasHoy = todasMisVentas.filter(v => v.fecha_venta.startsWith(hoy)).length;

    actualizarStats(ventasHoy);
    renderizarTabla(misVentas);
}

function actualizarStats(ventasHoy) {
    const total = misVentas.reduce((s, v) => s + parseFloat(v.total), 0);
    const tickets = misVentas.length;
    const promedio = tickets ? total / tickets : 0;

    document.getElementById('statMisTickets').textContent = tickets;
    document.getElementById('statMisVentas').textContent = `$${total.toFixed(2)}`;
    document.getElementById('statPromedio').textContent = `$${promedio.toFixed(2)}`;
    document.getElementById('statHoy').textContent = ventasHoy;
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoMisVentas');

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="9">No hay ventas</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map((v, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${v.numero_ticket}</td>
            <td>${formatearFecha(v.fecha_venta)}</td>
            <td>$${v.subtotal}</td>
            <td>$${v.impuesto}</td>
            <td>$${v.total}</td>
            <td>${v.metodo_pago}</td>
            <td>${v.estado}</td>
            <td><button onclick="verDetalleVenta(${v.id_venta})">Ver</button></td>
        </tr>
    `).join('');
}


function verDetalleVenta(id) {
    fetch(`../controllers/PerfilController.php?action=detalle&id_venta=${id}`)
        .then(res => res.json())
        .then(data => {

            let html = `<table>
                <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>`;

            data.data.forEach(d => {
                html += `
                    <tr>
                        <td>${d.nombre}</td>
                        <td>${d.cantidad}</td>
                        <td>$${d.precio_unitario}</td>
                        <td>$${d.subtotal}</td>
                    </tr>`;
            });

            html += `</table>`;

            document.getElementById('cuerpoDetalleVenta').innerHTML = html;
            abrirModal('modalDetalleVenta');
        });
}


function abrirModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}

function formatearFecha(f) {
    return new Date(f).toLocaleString('es-SV');
}