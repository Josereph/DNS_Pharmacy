/* =====================
   PROVEEDORES.JS - DNS Pharmacy
   Conectado a BD via fetch
   ===================== */

const CONTROLLER = '/DNS_Pharmacy/controllers/ProveedorController.php';

var proveedoresData = [];

/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    cargarProveedores();
    document.getElementById('formProveedor').addEventListener('submit', guardarProveedor);
    document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarProveedor);
});

/* ══════════════════════════════════════════
   CARGAR Y RENDERIZAR
══════════════════════════════════════════ */
function cargarProveedores() {
    fetch(CONTROLLER + '?accion=listar')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            proveedoresData = res.datos;
            renderizarTabla(proveedoresData);
        })
        .catch(function(e) { console.error('Error al cargar:', e); });
}

function renderizarTabla(lista) {
    var tbody = document.getElementById('cuerpoTabla');
    if (!tbody) return;

    if (!lista || lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">No hay proveedores registrados.</td></tr>';
        return;
    }

    tbody.innerHTML = lista.map(function(p, i) {
        var estado = p.estado == 1
            ? '<span class="badge-activo">Activo</span>'
            : '<span class="badge-inactivo">Inactivo</span>';

        var nit = p.nit
            ? '<span class="td-fiscal">' + esc(p.nit) + '</span>'
            : '<span class="td-fiscal vacio">—</span>';

        var nrc = p.nrc
            ? '<span class="td-fiscal">' + esc(p.nrc) + '</span>'
            : '<span class="td-fiscal vacio">—</span>';

        return '<tr'
             + ' data-nombre="'  + (p.nombre || '').toLowerCase() + '"'
             + ' data-nit="'     + (p.nit    || '').toLowerCase() + '"'
             + ' data-correo="'  + (p.correo || '').toLowerCase() + '"'
             + ' data-estado="'  + (p.estado == 1 ? 'activo' : 'inactivo') + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td><div class="td-empresa">' + esc(p.nombre)
             + (p.direccion ? '<small>' + esc(p.direccion) + '</small>' : '')
             + '</div></td>'
             + '<td>' + esc(p.nombre_contacto || '—') + '</td>'
             + '<td>' + esc(p.telefono || '—') + '</td>'
             + '<td>' + esc(p.correo   || '—') + '</td>'
             + '<td>' + nit + '</td>'
             + '<td>' + nrc + '</td>'
             + '<td>' + estado + '</td>'
             + '<td>'
             + '<button class="btn-accion btn-ver" onclick="verProveedor(' + p.id_proveedor + ')" title="Ver"><i class="bi bi-eye"></i></button>'
             + '<button class="btn-accion btn-editar" onclick="editarProveedor(' + p.id_proveedor + ')" title="Editar"><i class="bi bi-pencil"></i></button>'
             + '<button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminar(' + p.id_proveedor + ',\'' + esc(p.nombre) + '\')" title="Eliminar"><i class="bi bi-trash3"></i></button>'
             + '</td></tr>';
    }).join('');
}

/* ── Filtrar ── */
function filtrarTabla() {
    var q      = document.getElementById('buscador').value.toLowerCase().trim();
    var estado = document.getElementById('filtroEstado').value;
    var filas  = document.querySelectorAll('#cuerpoTabla tr[data-nombre]');

    filas.forEach(function(fila) {
        var ok = (!q      || fila.dataset.nombre.includes(q) || fila.dataset.nit.includes(q) || fila.dataset.correo.includes(q))
              && (!estado || fila.dataset.estado === estado);
        fila.style.display = ok ? '' : 'none';
    });
}

/* ══════════════════════════════════════════
   MODAL PROVEEDOR
══════════════════════════════════════════ */
function abrirModalProveedor(datos) {
    document.getElementById('formProveedor').reset();
    document.getElementById('prov_id').value = '';
    document.getElementById('prov_estado').checked = true;
    limpiarErrores();

    if (datos) {
        document.getElementById('tituloModalProveedor').textContent = 'Editar Proveedor';
        document.getElementById('prov_id').value        = datos.id_proveedor;
        document.getElementById('prov_nombre').value    = datos.nombre        || '';
        document.getElementById('prov_contacto').value  = datos.nombre_contacto || '';
        document.getElementById('prov_telefono').value  = datos.telefono      || '';
        document.getElementById('prov_correo').value    = datos.correo        || '';
        document.getElementById('prov_direccion').value = datos.direccion     || '';
        document.getElementById('prov_nit').value       = datos.nit           || '';
        document.getElementById('prov_nrc').value       = datos.nrc           || '';
        document.getElementById('prov_estado').checked  = datos.estado == 1;
    } else {
        document.getElementById('tituloModalProveedor').textContent = 'Nuevo Proveedor';
    }

    abrirModal('modalProveedor');
}

function editarProveedor(id) {
    var p = proveedoresData.find(function(x) { return x.id_proveedor == id; });
    if (p) abrirModalProveedor(p);
}

function verProveedor(id) {
    var p = proveedoresData.find(function(x) { return x.id_proveedor == id; });
    if (!p) return;

    document.getElementById('cuerpoVerProveedor').innerHTML =
        '<div class="detalle-grid">'
      + '<div class="detalle-item" style="grid-column:1/-1">'
      + '<label>Empresa</label>'
      + '<span style="font-size:16px;font-weight:600;color:#841480">' + esc(p.nombre) + '</span>'
      + '</div>'
      + '<div class="detalle-item"><label>Contacto</label><span>' + esc(p.nombre_contacto || '—') + '</span></div>'
      + '<div class="detalle-item"><label>Teléfono</label><span>' + esc(p.telefono || '—') + '</span></div>'
      + '<div class="detalle-item"><label>Correo</label><span>' + esc(p.correo || '—') + '</span></div>'
      + '<div class="detalle-item"><label>Dirección</label><span>' + esc(p.direccion || '—') + '</span></div>'
      + '<hr class="detalle-divider">'
      + '<div class="detalle-item"><label>NIT</label><span>' + (p.nit ? '<span class="td-fiscal">' + esc(p.nit) + '</span>' : '—') + '</span></div>'
      + '<div class="detalle-item"><label>NRC</label><span>' + (p.nrc ? '<span class="td-fiscal">' + esc(p.nrc) + '</span>' : '—') + '</span></div>'
      + '<hr class="detalle-divider">'
      + '<div class="detalle-item"><label>Estado</label><span>' + (p.estado == 1 ? '<span class="badge-activo">Activo</span>' : '<span class="badge-inactivo">Inactivo</span>') + '</span></div>'
      + '<div class="detalle-item"><label>Registrado el</label><span>' + formatearFecha(p.created_at) + '</span></div>'
      + '</div>';

    abrirModal('modalVerProveedor');
}

/* ── Guardar ── */
function guardarProveedor(e) {
    e.preventDefault();
    if (!validarFormulario()) return;

    var fd = new FormData(document.getElementById('formProveedor'));
    fd.append('accion', 'guardar');
    fd.set('estado', document.getElementById('prov_estado').checked ? '1' : '0');

    var btn = document.querySelector('#formProveedor .btn-guardar');
    btn.textContent = 'Guardando...';
    btn.disabled    = true;

    fetch(CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.textContent = 'Guardar proveedor';
            btn.disabled    = false;
            if (res.ok) {
                cerrarModal('modalProveedor');
                mostrarToast(res.mensaje, 'ok');
                cargarProveedores();
            } else {
                mostrarToast(res.mensaje, 'error');
            }
        })
        .catch(function() {
            btn.textContent = 'Guardar proveedor';
            btn.disabled    = false;
            mostrarToast('Error de conexión.', 'error');
        });
}

/* ── Eliminar ── */
var provIdEliminar = null;

function confirmarEliminar(id, nombre) {
    provIdEliminar = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    abrirModal('modalEliminar');
}

function eliminarProveedor() {
    if (!provIdEliminar) return;

    var fd = new FormData();
    fd.append('accion',        'eliminar');
    fd.append('id_proveedor',  provIdEliminar);

    fetch(CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            cerrarModal('modalEliminar');
            mostrarToast(res.mensaje, res.ok ? 'ok' : 'error');
            if (res.ok) cargarProveedores();
            provIdEliminar = null;
        });
}

/* ══════════════════════════════════════════
   VALIDACIONES
══════════════════════════════════════════ */
function validarFormulario() {
    limpiarErrores();
    var ok = true;

    if (!document.getElementById('prov_nombre').value.trim()) {
        document.getElementById('err_nombre').textContent = 'El nombre es obligatorio.';
        ok = false;
    }
    if (!document.getElementById('prov_contacto').value.trim()) {
        document.getElementById('err_contacto').textContent = 'El contacto es obligatorio.';
        ok = false;
    }
    if (!document.getElementById('prov_telefono').value.trim()) {
        document.getElementById('err_telefono').textContent = 'El teléfono es obligatorio.';
        ok = false;
    }

    var correo = document.getElementById('prov_correo').value.trim();
    if (!correo) {
        document.getElementById('err_correo').textContent = 'El correo es obligatorio.';
        ok = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        document.getElementById('err_correo').textContent = 'Ingresa un correo válido.';
        ok = false;
    }

    return ok;
}

function limpiarErrores() {
    document.querySelectorAll('.form-error').forEach(function(el) { el.textContent = ''; });
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