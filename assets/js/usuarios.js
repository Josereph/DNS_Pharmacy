

const USR_CONTROLLER = '/DNS_Pharmacy/controllers/Usuariocontroller.php';

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();
    cargarStats();
    document.getElementById('formUsuario').addEventListener('submit', guardarUsuario);
    document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarUsuario);
});

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
function cargarStats() {
    fetch(USR_CONTROLLER + '?accion=stats')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            document.getElementById('statTotal').textContent     = res.datos.total;
            document.getElementById('statActivos').textContent   = res.datos.activos;
            document.getElementById('statAdmins').textContent    = res.datos.admins;
            document.getElementById('statInactivos').textContent = res.datos.inactivos;
        });
}

/* ══════════════════════════════════════════
   TABLA
══════════════════════════════════════════ */
var usuariosData = [];

function cargarUsuarios() {
    fetch(USR_CONTROLLER + '?accion=listar')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            usuariosData = res.datos;
            renderizarTabla(usuariosData);
        });
}

function renderizarTabla(lista) {
    var tbody = document.getElementById('cuerpoTabla');

    if (!lista || lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="tabla-vacia">No hay usuarios registrados.</td></tr>';
        actualizarContador(0, 0);
        return;
    }

    tbody.innerHTML = lista.map(function(u, i) {
        var rolClase = u.rol === 'Administrador' ? 'rol-admin' : 'rol-empleado';
        var tel = (u.telefono && u.telefono !== '0000-0000') ? esc(u.telefono) : '<span style="color:#bbb">—</span>';

        return '<tr'
             + ' data-nombre="'  + (u.nombre + ' ' + u.apellido).toLowerCase() + '"'
             + ' data-correo="'  + u.correo.toLowerCase() + '"'
             + ' data-rol="'     + u.rol + '"'
             + ' data-estado="'  + (u.estado == 1 ? 'activo' : 'inactivo') + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td><div class="td-usuario">'
             + '<div class="usr-avatar">' + iniciales(u.nombre, u.apellido) + '</div>'
             + '<div class="usr-info"><strong>' + esc(u.nombre) + ' ' + esc(u.apellido) + '</strong><small>' + esc(u.correo) + '</small></div>'
             + '</div></td>'
             + '<td>' + esc(u.correo) + '</td>'
             + '<td>' + tel + '</td>'
             + '<td><span class="badge-rol ' + rolClase + '">' + esc(u.rol) + '</span></td>'
             + '<td class="td-acceso">' + formatearFecha(u.ultimo_acceso) + '</td>'
             + '<td>' + badgeEstado(u.estado) + '</td>'
             + '<td>'
             + '<button class="btn-accion btn-ver" onclick="verUsuario(' + u.id_usuario + ')" title="Ver"><i class="bi bi-eye"></i></button>'
             + '<button class="btn-accion btn-editar" onclick="editarUsuario(' + u.id_usuario + ')" title="Editar"><i class="bi bi-pencil"></i></button>'
             + '<button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminar(' + u.id_usuario + ',\'' + esc(u.nombre) + ' ' + esc(u.apellido) + '\')" title="Eliminar"><i class="bi bi-trash3"></i></button>'
             + '</td></tr>';
    }).join('');

    actualizarContador(lista.length, usuariosData.length);
}

function actualizarContador(visible, total) {
    var cv = document.getElementById('contadorVisible');
    var ct = document.getElementById('contadorTotal');
    if (cv) cv.textContent = visible;
    if (ct) ct.textContent = total;
}

function filtrarTabla() {
    var texto  = document.getElementById('buscador').value.toLowerCase().trim();
    var rol    = document.getElementById('filtroRol').value;
    var estado = document.getElementById('filtroEstado').value;
    var filas  = document.querySelectorAll('#cuerpoTabla tr[data-nombre]');
    var visible = 0;

    filas.forEach(function(fila) {
        var ok = (!texto  || fila.dataset.nombre.includes(texto) || fila.dataset.correo.includes(texto))
              && (!rol    || fila.dataset.rol === rol)
              && (!estado || fila.dataset.estado === estado);
        fila.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });

    actualizarContador(visible, filas.length);
}

/* ══════════════════════════════════════════
   MODAL USUARIO
══════════════════════════════════════════ */
function abrirModalUsuario() {
    document.getElementById('tituloModalUsuario').textContent = 'Nuevo Usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usr_id').value = '';
    document.getElementById('usr_estado').checked = true;
    document.getElementById('labelPassword').innerHTML = 'Contraseña <span class="req">*</span>';
    document.getElementById('hintPassword').textContent = '';
    limpiarErrores();
    abrirModal('modalUsuario');
}

function editarUsuario(id) {
    var u = usuariosData.find(function(x) { return x.id_usuario == id; });
    if (!u) return;

    document.getElementById('tituloModalUsuario').textContent = 'Editar Usuario';
    document.getElementById('usr_id').value        = u.id_usuario;
    document.getElementById('usr_nombre').value    = u.nombre;
    document.getElementById('usr_apellido').value  = u.apellido;
    document.getElementById('usr_correo').value    = u.correo;
    document.getElementById('usr_telefono').value  = (u.telefono === '0000-0000' || !u.telefono) ? '' : u.telefono;
    document.getElementById('usr_rol').value       = u.id_rol;
    document.getElementById('usr_password').value  = '';
    document.getElementById('usr_estado').checked  = u.estado == 1;
    document.getElementById('labelPassword').textContent = 'Nueva contraseña';
    document.getElementById('hintPassword').textContent  = 'Dejar en blanco para mantener la actual.';

    limpiarErrores();
    abrirModal('modalUsuario');
}

function verUsuario(id) {
    var u = usuariosData.find(function(x) { return x.id_usuario == id; });
    if (!u) return;

    var tel      = (u.telefono && u.telefono !== '0000-0000') ? esc(u.telefono) : '—';
    var rolClase = u.rol === 'Administrador' ? 'rol-admin' : 'rol-empleado';

    document.getElementById('cuerpoVerUsuario').innerHTML =
        '<div class="detalle-grid">'
      + '<div class="detalle-header">'
      + '<div class="detalle-avatar">' + iniciales(u.nombre, u.apellido) + '</div>'
      + '<div><div class="detalle-nombre">' + esc(u.nombre) + ' ' + esc(u.apellido) + '</div>'
      + '<span class="badge-rol ' + rolClase + '">' + esc(u.rol) + '</span></div>'
      + '</div>'
      + '<div class="detalle-item"><label>Correo</label><span>' + esc(u.correo) + '</span></div>'
      + '<div class="detalle-item"><label>Teléfono</label><span>' + tel + '</span></div>'
      + '<hr class="detalle-divider">'
      + '<div class="detalle-item"><label>Estado</label><span>' + badgeEstado(u.estado) + '</span></div>'
      + '<div class="detalle-item"><label>Último acceso</label><span>' + formatearFecha(u.ultimo_acceso) + '</span></div>'
      + '<div class="detalle-item"><label>Creado el</label><span>' + formatearFecha(u.created_at) + '</span></div>'
      + '</div>';

    abrirModal('modalVerUsuario');
}

function guardarUsuario(e) {
    e.preventDefault();
    if (!validarFormulario()) return;

    var fd = new FormData(document.getElementById('formUsuario'));
    fd.append('accion', 'guardar');
    if (document.getElementById('usr_estado').checked) {
        fd.set('estado', '1');
    } else {
        fd.delete('estado');
    }

    var btn = document.querySelector('#formUsuario .btn-guardar');
    btn.textContent = 'Guardando...';
    btn.disabled    = true;

    fetch(USR_CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.textContent = 'Guardar usuario';
            btn.disabled    = false;
            if (res.ok) {
                cerrarModal('modalUsuario');
                mostrarToast(res.mensaje, 'ok');
                cargarUsuarios();
                cargarStats();
            } else {
                mostrarToast(res.mensaje, 'error');
            }
        })
        .catch(function() {
            btn.textContent = 'Guardar usuario';
            btn.disabled    = false;
            mostrarToast('Error de conexión.', 'error');
        });
}

/* ── Eliminar ── */
var usrIdEliminar = null;

function confirmarEliminar(id, nombre) {
    usrIdEliminar = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    abrirModal('modalEliminar');
}

function eliminarUsuario() {
    if (!usrIdEliminar) return;

    var fd = new FormData();
    fd.append('accion',      'eliminar');
    fd.append('id_usuario',  usrIdEliminar);

    fetch(USR_CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            cerrarModal('modalEliminar');
            mostrarToast(res.mensaje, res.ok ? 'ok' : 'error');
            if (res.ok) { cargarUsuarios(); cargarStats(); }
            usrIdEliminar = null;
        });
}

/* ══════════════════════════════════════════
   VALIDACIONES
══════════════════════════════════════════ */
function validarFormulario() {
    limpiarErrores();
    var ok = true;

    [['usr_nombre','err_nombre','El nombre es obligatorio.'],
     ['usr_apellido','err_apellido','El apellido es obligatorio.'],
     ['usr_correo','err_correo','El correo es obligatorio.'],
     ['usr_rol','err_rol','Selecciona un rol.']
    ].forEach(function(c) {
        if (!document.getElementById(c[0]).value.trim()) {
            document.getElementById(c[1]).textContent = c[2];
            ok = false;
        }
    });

    var correo = document.getElementById('usr_correo').value.trim();
    if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        document.getElementById('err_correo').textContent = 'Ingresa un correo válido.';
        ok = false;
    }

    var esNuevo = !document.getElementById('usr_id').value;
    var pass    = document.getElementById('usr_password').value;
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
    document.querySelectorAll('.form-error').forEach(function(el) { el.textContent = ''; });
}

function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

/* ══════════════════════════════════════════
   UTILIDADES
══════════════════════════════════════════ */
function abrirModal(id) {
    document.getElementById(id).classList.add('activo');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('activo');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('activo');
            document.body.style.overflow = '';
        }
    });
});

function mostrarToast(mensaje, tipo) {
    var toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = mensaje;
    toast.className   = 'toast toast-' + (tipo || 'ok');
    toast.classList.add('toast-visible');
    setTimeout(function() { toast.classList.remove('toast-visible'); }, 3500);
}

function iniciales(nombre, apellido) {
    return ((nombre || '').charAt(0) + (apellido || '').charAt(0)).toUpperCase();
}

function formatearFecha(fecha) {
    if (!fecha || fecha === 'NULL' || fecha === null) return '—';
    var d = new Date(fecha);
    if (isNaN(d)) return fecha;
    return d.toLocaleDateString('es-SV', { day:'2-digit', month:'short', year:'numeric' });
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#39;');
}

function badgeEstado(estado) {
    return estado == 1
        ? '<span class="badge-activo">Activo</span>'
        : '<span class="badge-inactivo">Inactivo</span>';
}