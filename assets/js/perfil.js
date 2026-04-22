let misVentas = [];
let todasMisVentas = [];
const usuarioSesion = {};

const roles = { 1: 'Administrador', 2: 'Cajero' };
const rolesClase = { 1: 'rol-admin', 2: 'rol-cajero' };

// 🔥 IMPORTANTE: ruta correcta
const API = (window.BASE_URL || '') + '/controllers/PerfilController.php';

document.addEventListener('DOMContentLoaded', () => {
    obtenerPerfil();
    cargarMisVentas();
    // setPeriodo('mes') se llamará después de cargar los datos

    document.getElementById('formEditar')?.addEventListener('submit', guardarEdicion);
    document.getElementById('formPassword')?.addEventListener('submit', cambiarPassword);
});

/* ── FETCH SEGURO ── */
function fetchJSON(url, options = {}) {
    return fetch(url, options)
        .then(async res => {
            const text = await res.text();

            if (text.startsWith('<')) {
                console.error('❌ El servidor devolvió HTML:', text);
                throw new Error('Respuesta inválida del servidor');
            }

            return JSON.parse(text);
        });
}

/* ── Perfil ── */
function obtenerPerfil() {
    fetchJSON(`${API}?action=perfil`)
        .then(data => {
            if (data.error) return console.error(data.mensaje);
            Object.assign(usuarioSesion, data.data);
            pintarPerfil();
        })
        .catch(e => console.error('Error perfil:', e));
}

function pintarPerfil() {
    const u = usuarioSesion;
    const ini = ((u.nombre?.[0] || '') + (u.apellido?.[0] || '')).toUpperCase();

    document.getElementById('perfilNombreCompleto').textContent = `${u.nombre} ${u.apellido}`;
    document.getElementById('perfilRol').textContent = roles[u.id_rol] || '—';
    document.getElementById('perfilRol').className = `badge-rol ${rolesClase[u.id_rol] || ''}`;
    document.getElementById('perfilCorreo').textContent = u.correo || '—';
    document.getElementById('perfilTelefono').textContent = u.telefono || '—';
    document.getElementById('perfilUltimoAcceso').textContent = formatearFecha(u.ultimo_acceso);

    if (u.foto_perfil) {
        document.getElementById('perfilFoto').src = `${window.BASE_URL}/uploads/perfiles/${u.foto_perfil}`;
        document.getElementById('perfilFoto').style.display = 'block';
        document.getElementById('perfilAvatar').style.display = 'none';
    } else {
        document.getElementById('perfilAvatar').textContent = ini;
        document.getElementById('perfilAvatar').style.display = 'flex';
        document.getElementById('perfilFoto').style.display = 'none';
    }
}

/* ── Foto ── */
function previsualizarFoto(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        alert('La imagen no debe superar 2MB.');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('perfilFoto').src = e.target.result;
        document.getElementById('perfilFoto').style.display = 'block';
        document.getElementById('perfilAvatar').style.display = 'none';
    };
    reader.readAsDataURL(file);

    subirFoto(file);
}

function subirFoto(file) {
    const fd = new FormData();
    fd.append('action', 'subirFoto');
    fd.append('foto_perfil', file);

    fetchJSON(API, { method: 'POST', body: fd })
        .then(data => {
            if (data.error) alert(data.mensaje);
        })
        .catch(e => console.error('Error foto:', e));
}

function guardarEdicion(e) {
    e.preventDefault();

    const nombre = document.getElementById('edit_nombre');
    const apellido = document.getElementById('edit_apellido');
    const correo = document.getElementById('edit_correo');
    const telefono = document.getElementById('edit_telefono');

    let valido = true;

    // limpiar errores
    document.querySelectorAll('#modalEditar .form-error').forEach(e => e.textContent = '');

    // 🟣 NOMBRE
    if (!/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]{2,30}$/.test(nombre.value)) {
        document.getElementById('err_edit_nombre').textContent = 'Solo letras (2-30 caracteres)';
        valido = false;
    }

    // 🟣 APELLIDO
    if (!/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]{2,30}$/.test(apellido.value)) {
        document.getElementById('err_edit_apellido').textContent = 'Solo letras (2-30 caracteres)';
        valido = false;
    }

    // 🟣 CORREO (VALIDACIÓN REAL)
    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regexCorreo.test(correo.value)) {
        document.getElementById('err_edit_correo').textContent = 'Correo inválido';
        valido = false;
    }

    // 🟣 TELÉFONO (El Salvador ejemplo: 8 dígitos)
    if (telefono.value && !/^[0-9]{8}$/.test(telefono.value)) {
        document.getElementById('err_edit_telefono').textContent = 'Debe tener 8 dígitos';
        valido = false;
    }

    if (!valido) return;

    const fd = new FormData(e.target);
    fd.append('action', 'actualizar');

    fetchJSON(API, { method: 'POST', body: fd })
        .then(data => {
            if (data.error) return alert(data.mensaje);

            Object.assign(usuarioSesion, {
                nombre: fd.get('nombre'),
                apellido: fd.get('apellido'),
                correo: fd.get('correo'),
                telefono: fd.get('telefono')
            });

            pintarPerfil();
            cerrarModal('modalEditar');
        });
}

function cambiarPassword(e) {
    e.preventDefault();

    const actual = document.getElementById('pass_actual');
    const nueva = document.getElementById('pass_nueva');
    const confirmar = document.getElementById('pass_confirmar');

    let valido = true;

    document.querySelectorAll('#modalPassword .form-error').forEach(e => e.textContent = '');

    if (!actual.value) {
        document.getElementById('err_pass_actual').textContent = 'Ingresa tu contraseña actual';
        valido = false;
    }

    if (nueva.value.length < 8) {
        document.getElementById('err_pass_nueva').textContent = 'Mínimo 8 caracteres';
        valido = false;
    }

    if (nueva.value !== confirmar.value) {
        document.getElementById('err_pass_confirmar').textContent = 'No coinciden';
        valido = false;
    }

    if (!valido) return;

    const fd = new FormData();
    fd.append('action', 'cambiarPassword');
    fd.append('password_actual', actual.value);
    fd.append('password_hash', nueva.value);

    fetchJSON(API, { method: 'POST', body: fd })
        .then(data => {
            if (data.error) return alert(data.mensaje);

            cerrarModal('modalPassword');
            alert('Contraseña actualizada');
        });
}

/* ── Ventas ── */
function cargarMisVentas() {
    fetchJSON(`${API}?action=ventas`)
        .then(data => {
            if (data.error) return console.error(data.mensaje);
            todasMisVentas = data.data;
            setPeriodo('mes');
        })
        .catch(e => console.error('Error ventas:', e));
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
        tbody.innerHTML = `<tr><td colspan="9">No hay ventas.</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map((v, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${esc(v.numero_ticket)}</td>
            <td>${formatearFecha(v.fecha_venta)}</td>
            <td>$${parseFloat(v.subtotal).toFixed(2)}</td>
            <td>$${parseFloat(v.impuesto).toFixed(2)}</td>
            <td>$${parseFloat(v.total).toFixed(2)}</td>
            <td>${badgeMetodo(v.metodo_pago)}</td>
            <td>${badgeEstado(v.estado)}</td>
            <td>
                <button onclick="verDetalleVenta(${v.id_venta}, '${esc(v.numero_ticket)}')">Ver</button>
            </td>
        </tr>
    `).join('');
}

/* ── Helpers ── */
function formatearFecha(f) {
    if (!f) return '—';
    const d = new Date(f);
    return isNaN(d) ? f : d.toLocaleString('es-SV');
}

function esc(str) {
    return String(str || '').replace(/</g,'&lt;');
}

function badgeMetodo(m) {
    if (!m) return '—';
    return m.charAt(0).toUpperCase() + m.slice(1);
}

function badgeEstado(e) {
    if (!e) return '—';
    const color = e.toLowerCase() === 'anulada' ? '#dc3545' : (e.toLowerCase() === 'completada' ? '#198754' : '#ffc107');
    return `<span style="color: ${color}; font-weight: 600;">${e.toUpperCase()}</span>`;
}
function abrirModalEditar() {
    // Llenar el formulario con los datos actuales
    document.getElementById('edit_nombre').value = usuarioSesion.nombre || '';
    document.getElementById('edit_apellido').value = usuarioSesion.apellido || '';
    document.getElementById('edit_correo').value = usuarioSesion.correo || '';
    document.getElementById('edit_telefono').value = usuarioSesion.telefono || '';

    abrirModal('modalEditar');
}
function abrirModalPassword() {
    // limpiar campos
    document.getElementById('pass_actual').value = '';
    document.getElementById('pass_nueva').value = '';
    document.getElementById('pass_confirmar').value = '';

    abrirModal('modalPassword');
}
function togglePass(id, btn) {
    const input = document.getElementById(id);

    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}

document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('input', () => {
        const error = input.closest('.form-group-custom')?.querySelector('.form-error');
        if (error) error.textContent = '';
    });
});
document.getElementById('edit_telefono')?.addEventListener('input', function() {
    this.value = this.value.replace(/[^\d+ -]/g, '');
});

/* ── Modales ── */
function abrirModal(id) { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

/* ── Funcionalidad de Ventas y Periodos ── */
function setPeriodo(periodo, event) {
    if (event) {
        document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('activo'));
        event.target.classList.add('activo');
    }

    const hoy = new Date();
    let desde = '';
    let hasta = hoy.toISOString().split('T')[0];

    if (periodo === 'hoy') {
        desde = hasta;
    } else if (periodo === 'semana') {
        const d = new Date(hoy);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        const primerDia = new Date(d.setDate(diff));
        desde = primerDia.toISOString().split('T')[0];
    } else if (periodo === 'mes') {
        const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        desde = primerDia.toISOString().split('T')[0];
    } else if (periodo === 'todo') {
        desde = '';
        hasta = '';
    }

    document.getElementById('filtroDesde').value = desde;
    document.getElementById('filtroHasta').value = hasta;

    filtrarMisVentas();
}

function verDetalleVenta(id_venta, ticket) {
    document.getElementById('tituloDetalleVenta').textContent = `Detalle de venta #${ticket}`;
    document.getElementById('cuerpoDetalleVenta').innerHTML = `<p>Cargando...</p>`;
    abrirModal('modalDetalleVenta');

    fetchJSON(`${API}?action=detalle&id_venta=${id_venta}`)
        .then(data => {
            if (data.error) {
                document.getElementById('cuerpoDetalleVenta').innerHTML = `<p class="form-error">${data.mensaje}</p>`;
                return;
            }

            const d = data.data;
            if (!d.length) {
                document.getElementById('cuerpoDetalleVenta').innerHTML = `<p>No hay detalles disponibles para esta venta.</p>`;
                return;
            }

            const html = `
                <div class="tabla-card" style="margin:0; box-shadow:none;">
                    <table class="tabla-productos">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>P. Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${d.map(item => `
                                <tr>
                                    <td>${esc(item.nombre)}</td>
                                    <td>${item.cantidad}</td>
                                    <td>$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                                    <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            document.getElementById('cuerpoDetalleVenta').innerHTML = html;
        })
        .catch(e => {
            console.error('Error detalle:', e);
            document.getElementById('cuerpoDetalleVenta').innerHTML = `<p class="form-error">Error al cargar detalles de la venta.</p>`;
        });
}