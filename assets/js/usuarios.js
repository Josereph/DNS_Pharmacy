let usuarios = [];
let usrIdEliminar = null;
const roles = { 1: 'Administrador', 2: 'Cajero' };
const rolesClase = { 1: 'rol-admin', 2: 'rol-cajero' };

document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
    document.getElementById('formUsuario').addEventListener('submit', guardarUsuario);
    document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarUsuario);
});

function cargarUsuarios() {
    usuarios = [
        {
            id_usuario: 1,
            id_rol: 1,
            nombre: 'Administrador',
            apellido: 'General',
            correo: 'admin@dnspharmacy.com',
            telefono: '0000-0000',
            estado: 1,
            ultimo_acceso: null,
            created_at: '2026-03-19 22:18:21'
        }
    ];
    renderizarTabla(usuarios);
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="tabla-vacia">No hay usuarios registrados.</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map((u, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>
                <div class="td-usuario">
                    <div class="usr-avatar">${iniciales(u.nombre, u.apellido)}</div>
                    <div class="usr-info">
                        <strong>${esc(u.nombre)} ${esc(u.apellido)}</strong>
                        <small>${esc(u.correo)}</small>
                    </div>
                </div>
            </td>
            <td>${esc(u.correo)}</td>
            <td>${(u.telefono && u.telefono !== '0000-0000') ? esc(u.telefono) : '<span style="color:#bbb">—</span>'}</td>
            <td><span class="badge-rol ${rolesClase[u.id_rol] || ''}">${roles[u.id_rol] || '—'}</span></td>
            <td class="td-acceso">${formatearFecha(u.ultimo_acceso)}</td>
            <td>${badgeEstado(u.estado)}</td>
            <td>
                <button class="btn-accion btn-ver" title="Ver detalle" onclick="verUsuario(${u.id_usuario})"><i class="bi bi-eye"></i></button>
                <button class="btn-accion btn-editar" title="Editar" onclick="editarUsuario(${u.id_usuario})"><i class="bi bi-pencil"></i></button>
                <button class="btn-accion btn-eliminar-sm" title="Eliminar" onclick="confirmarEliminar(${u.id_usuario}, '${esc(u.nombre)} ${esc(u.apellido)}')"><i class="bi bi-trash3"></i></button>
            </td>
        </tr>
    `).join('');
}

function filtrarTabla() {
    const texto  = document.getElementById('buscador').value.toLowerCase().trim();
    const rol    = document.getElementById('filtroRol').value;
    const estado = document.getElementById('filtroEstado').value;

    const filtrado = usuarios.filter(u => {
        const nombre = `${u.nombre} ${u.apellido}`.toLowerCase();
        const coincideTexto  = !texto  || nombre.includes(texto) || u.correo.toLowerCase().includes(texto) || (u.telefono && u.telefono.includes(texto));
        const coincideRol    = !rol    || u.id_rol == rol;
        const coincideEstado = !estado || (estado === 'activo' ? u.estado == 1 : u.estado == 0);
        return coincideTexto && coincideRol && coincideEstado;
    });

    renderizarTabla(filtrado);
}

function abrirModalUsuario() {
    document.getElementById('tituloModalUsuario').textContent = 'Nuevo Usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usr_id').value = '';
    document.getElementById('labelPassword').innerHTML = 'Contraseña <span class="req">*</span>';
    document.getElementById('hintPassword').textContent = '';
    limpiarErrores();
    abrirModal('modalUsuario');
}

function editarUsuario(id) {
    const u = usuarios.find(x => x.id_usuario === id);
    if (!u) return;

    document.getElementById('tituloModalUsuario').textContent = 'Editar Usuario';
    document.getElementById('usr_id').value       = u.id_usuario;
    document.getElementById('usr_nombre').value   = u.nombre;
    document.getElementById('usr_apellido').value = u.apellido;
    document.getElementById('usr_correo').value   = u.correo;
    document.getElementById('usr_telefono').value = (u.telefono === '0000-0000') ? '' : (u.telefono || '');
    document.getElementById('usr_rol').value      = u.id_rol;
    document.getElementById('usr_password').value = '';
    document.getElementById('usr_estado').checked = u.estado == 1;

    document.getElementById('labelPassword').textContent = 'Nueva contraseña';
    document.getElementById('hintPassword').textContent  = 'Dejar en blanco para mantener la actual.';

    limpiarErrores();
    abrirModal('modalUsuario');
}

function verUsuario(id) {
    const u = usuarios.find(x => x.id_usuario === id);
    if (!u) return;

    const tel = (u.telefono && u.telefono !== '0000-0000') ? esc(u.telefono) : '—';

    document.getElementById('cuerpoVerUsuario').innerHTML = `
        <div class="detalle-grid">
            <div class="detalle-header">
                <div class="detalle-avatar">${iniciales(u.nombre, u.apellido)}</div>
                <div>
                    <div class="detalle-nombre">${esc(u.nombre)} ${esc(u.apellido)}</div>
                    <span class="badge-rol ${rolesClase[u.id_rol] || ''}">${roles[u.id_rol] || '—'}</span>
                </div>
            </div>
            <div class="detalle-item">
                <label>Correo</label>
                <span>${esc(u.correo)}</span>
            </div>
            <div class="detalle-item">
                <label>Teléfono</label>
                <span>${tel}</span>
            </div>
            <hr class="detalle-divider">
            <div class="detalle-item">
                <label>Estado</label>
                <span>${badgeEstado(u.estado)}</span>
            </div>
            <div class="detalle-item">
                <label>Último acceso</label>
                <span>${formatearFecha(u.ultimo_acceso)}</span>
            </div>
            <div class="detalle-item">
                <label>Creado el</label>
                <span>${formatearFecha(u.created_at)}</span>
            </div>
        </div>
    `;
    abrirModal('modalVerUsuario');
}

function guardarUsuario(e) {
    e.preventDefault();
    if (!validarFormulario()) return;

    const tel = document.getElementById('usr_telefono').value.trim();
    const datos = {
        id_usuario: document.getElementById('usr_id').value,
        nombre:     document.getElementById('usr_nombre').value.trim(),
        apellido:   document.getElementById('usr_apellido').value.trim(),
        correo:     document.getElementById('usr_correo').value.trim(),
        telefono:   tel || '0000-0000',
        id_rol:     parseInt(document.getElementById('usr_rol').value),
        password:   document.getElementById('usr_password').value,
        estado:     document.getElementById('usr_estado').checked ? 1 : 0
    };

    if (datos.id_usuario) {
        const idx = usuarios.findIndex(x => x.id_usuario == datos.id_usuario);
        if (idx !== -1) usuarios[idx] = { ...usuarios[idx], ...datos };
    } else {
        datos.id_usuario    = Date.now();
        datos.ultimo_acceso = null;
        datos.created_at    = new Date().toISOString().replace('T', ' ').split('.')[0];
        usuarios.push(datos);
    }

    renderizarTabla(usuarios);
    cerrarModal('modalUsuario');
}

function confirmarEliminar(id, nombre) {
    usrIdEliminar = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    abrirModal('modalEliminar');
}

function eliminarUsuario() {
    if (!usrIdEliminar) return;
    usuarios = usuarios.filter(x => x.id_usuario !== usrIdEliminar);
    usrIdEliminar = null;
    renderizarTabla(usuarios);
    cerrarModal('modalEliminar');
}

function validarFormulario() {
    let ok = true;
    limpiarErrores();

    const campos = [
        { id: 'usr_nombre',   err: 'err_nombre',   msg: 'El nombre es obligatorio.' },
        { id: 'usr_apellido', err: 'err_apellido', msg: 'El apellido es obligatorio.' },
        { id: 'usr_correo',   err: 'err_correo',   msg: 'El correo es obligatorio.' },
        { id: 'usr_rol',      err: 'err_rol',      msg: 'Selecciona un rol.' },
    ];

    campos.forEach(c => {
        if (!document.getElementById(c.id).value.trim()) {
            document.getElementById(c.err).textContent = c.msg;
            ok = false;
        }
    });

    const correo = document.getElementById('usr_correo').value.trim();
    if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        document.getElementById('err_correo').textContent = 'Ingresa un correo válido.';
        ok = false;
    }

    const esNuevo = !document.getElementById('usr_id').value;
    const pass = document.getElementById('usr_password').value;
    if (esNuevo && !pass) {
        document.getElementById('err_password').textContent = 'La contraseña es obligatoria.';
        ok = false;
    } else if (pass && pass.length < 6) {
        document.getElementById('err_password').textContent = 'Mínimo 6 caracteres.';
        ok = false;
    }

    return ok;
}

function limpiarErrores() {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function abrirModal(id)  { document.getElementById(id).classList.add('activo'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

function iniciales(nombre, apellido) {
    return ((nombre || '').charAt(0) + (apellido || '').charAt(0)).toUpperCase();
}

function formatearFecha(fecha) {
    if (!fecha || fecha === 'NULL') return '—';
    const d = new Date(fecha);
    if (isNaN(d)) return fecha;
    return d.toLocaleDateString('es-SV', { day: '2-digit', month: 'short', year: 'numeric' });
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function badgeEstado(estado) {
    return (estado == 1)
        ? `<span class="badge-activo">Activo</span>`
        : `<span class="badge-inactivo">Inactivo</span>`;
}s