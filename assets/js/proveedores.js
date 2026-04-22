const CONTROLLER = '../controllers/Proveedorcontroller.php';

/* ══════════════════════════════════════════
   RESTRICCIONES EN TIEMPO REAL (al tipear)
══════════════════════════════════════════ */
function aplicarRestriccionesCampos() {

    /* Nombre empresa: letras, números, espacios y puntuación básica */
    document.getElementById('prov_nombre').addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s.,&()\-]/g, '');
        if (this.value.length > 150) this.value = this.value.slice(0, 150);
    });

    /* Nombre contacto: SOLO letras y espacios */
    document.getElementById('prov_contacto').addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
        if (this.value.length > 120) this.value = this.value.slice(0, 120);
    });

    /* Teléfono SV: auto-formato 0000-0000 (máx 9 chars con guión) */
    document.getElementById('prov_telefono').addEventListener('input', function () {
        let raw = this.value.replace(/[^0-9]/g, '').slice(0, 8);
        this.value = raw.length <= 4 ? raw : raw.slice(0, 4) + '-' + raw.slice(4);
    });

    /* Correo: validar al salir */
    document.getElementById('prov_correo').addEventListener('blur', function () {
        const val = this.value.trim();
        const err = document.getElementById('err_correo');
        if (!val) {
            err.textContent = 'El correo es obligatorio.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            err.textContent = 'Formato inválido. Ej: contacto@empresa.com';
        } else {
            err.textContent = '';
        }
        if (this.value.length > 120) this.value = this.value.slice(0, 120);
    });

    /* Dirección: texto libre, solo limitar longitud */
    document.getElementById('prov_direccion').addEventListener('input', function () {
        if (this.value.length > 255) this.value = this.value.slice(0, 255);
    });

    /* NIT SV: auto-formato 0000-000000-000-0 */
    document.getElementById('prov_nit').addEventListener('input', function () {
        let raw = this.value.replace(/[^0-9]/g, '').slice(0, 14);
        let fmt = '';
        if      (raw.length <= 4)  fmt = raw;
        else if (raw.length <= 10) fmt = raw.slice(0,4) + '-' + raw.slice(4);
        else if (raw.length <= 13) fmt = raw.slice(0,4) + '-' + raw.slice(4,10) + '-' + raw.slice(10);
        else                       fmt = raw.slice(0,4) + '-' + raw.slice(4,10) + '-' + raw.slice(10,13) + '-' + raw.slice(13);
        this.value = fmt;
    });

    document.getElementById('prov_nit').addEventListener('blur', function () {
        const val = this.value.trim();
        const err = document.getElementById('err_nit');
        if (!val) {
            err.textContent = 'El NIT es obligatorio.';
        } else if (!/^\d{4}-\d{6}-\d{3}-\d{1}$/.test(val)) {
            err.textContent = 'NIT incompleto. Formato: 0000-000000-000-0';
        } else {
            err.textContent = '';
        }
    });

    /* NRC SV: auto-formato 000000-0 (máx 8 chars con guión) */
    document.getElementById('prov_nrc').addEventListener('input', function () {
        let raw = this.value.replace(/[^0-9]/g, '').slice(0, 7);
        this.value = raw.length <= 6 ? raw : raw.slice(0, 6) + '-' + raw.slice(6);
    });

    document.getElementById('prov_nrc').addEventListener('blur', function () {
        const val = this.value.trim();
        const err = document.getElementById('err_nrc');
        if (!val) {
            err.textContent = 'El NRC es obligatorio.';
        } else if (!/^\d{1,6}-\d{1}$/.test(val)) {
            err.textContent = 'NRC incompleto. Formato: 123456-7';
        } else {
            err.textContent = '';
        }
    });
}

/* ══════════════════════════════════════════
   VALIDACIÓN FINAL ANTES DE ENVIAR
══════════════════════════════════════════ */
function guardarProveedor(e) {
    e.preventDefault();
    limpiarErrores();

    let valido = true;

    /* ── Nombre empresa (obligatorio) ── */
    const nombre = document.getElementById('prov_nombre').value.trim();
    if (!nombre) {
        document.getElementById('err_nombre').textContent = 'El nombre de la empresa es obligatorio.';
        valido = false;
    } else if (nombre.length < 2) {
        document.getElementById('err_nombre').textContent = 'Mínimo 2 caracteres.';
        valido = false;
    }

    /* ── Nombre contacto (obligatorio) ── */
    const contacto = document.getElementById('prov_contacto').value.trim();
    if (!contacto) {
        document.getElementById('err_contacto').textContent = 'El nombre del contacto es obligatorio.';
        valido = false;
    } else if (contacto.length < 2) {
        document.getElementById('err_contacto').textContent = 'Mínimo 2 caracteres.';
        valido = false;
    }

    /* ── Teléfono SV (obligatorio, formato 0000-0000) ── */
    const telefono = document.getElementById('prov_telefono').value.trim();
    if (!telefono) {
        document.getElementById('err_telefono').textContent = 'El teléfono es obligatorio.';
        valido = false;
    } else if (!/^\d{4}-\d{4}$/.test(telefono)) {
        document.getElementById('err_telefono').textContent = 'Formato inválido. Ej: 7600-0000';
        valido = false;
    }

    /* ── Correo (obligatorio) ── */
    const correo = document.getElementById('prov_correo').value.trim();
    if (!correo) {
        document.getElementById('err_correo').textContent = 'El correo es obligatorio.';
        valido = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        document.getElementById('err_correo').textContent = 'Formato inválido. Ej: contacto@empresa.com';
        valido = false;
    }

    /* ── Dirección (obligatorio) ── */
    const direccion = document.getElementById('prov_direccion').value.trim();
    if (!direccion) {
        document.getElementById('err_direccion').textContent = 'La dirección es obligatoria.';
        valido = false;
    }

    /* ── NIT (obligatorio, formato 0000-000000-000-0) ── */
    const nit = document.getElementById('prov_nit').value.trim();
    if (!nit) {
        document.getElementById('err_nit').textContent = 'El NIT es obligatorio.';
        valido = false;
    } else if (!/^\d{4}-\d{6}-\d{3}-\d{1}$/.test(nit)) {
        document.getElementById('err_nit').textContent = 'Formato inválido. Ej: 0614-010101-001-0';
        valido = false;
    }

    /* ── NRC (obligatorio, formato 000000-0) ── */
    const nrc = document.getElementById('prov_nrc').value.trim();
    if (!nrc) {
        document.getElementById('err_nrc').textContent = 'El NRC es obligatorio.';
        valido = false;
    } else if (!/^\d{1,6}-\d{1}$/.test(nrc)) {
        document.getElementById('err_nrc').textContent = 'Formato inválido. Ej: 123456-7';
        valido = false;
    }

    if (!valido) return;

    /* ── Envío al servidor ── */
    const formData = new FormData();
    formData.append('accion',          'guardar');
    formData.append('id_proveedor',    document.getElementById('prov_id').value);
    formData.append('nombre',          nombre);
    formData.append('nombre_contacto', contacto);
    formData.append('telefono',        telefono);
    formData.append('correo',          correo);
    formData.append('direccion',       direccion);
    formData.append('nit',             nit);
    formData.append('nrc',             nrc);
    formData.append('estado',          document.getElementById('prov_estado').checked ? '1' : '0');

    fetch(CONTROLLER, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                cerrarModal('modalProveedor');
                cargarProveedores();
            } else {
                alert(res.mensaje);
            }
        })
        .catch(err => console.error('Error al guardar:', err));
}

/* ══════════════════════════════════════════
   INICIALIZACIÓN
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    cargarProveedores();
    document.getElementById('formProveedor').addEventListener('submit', guardarProveedor);
    document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarProveedor);
    aplicarRestriccionesCampos();
});

let provIdEliminar = null;
let _todosProveedores = [];

function cargarProveedores() {
    fetch(CONTROLLER + '?accion=listar')
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                _todosProveedores = res.datos;
                renderizarTabla(_todosProveedores);
            } else console.error(res.mensaje);
        })
        .catch(e => console.error('Error al cargar proveedores:', e));
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');
    if (!lista || !lista.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="tabla-vacia">No hay proveedores registrados.</td></tr>';
        return;
    }
    tbody.innerHTML = lista.map((p, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>
                <div class="td-empresa">
                    ${esc(p.nombre)}
                    ${p.direccion ? `<small><i class="bi bi-geo-alt"></i> ${esc(p.direccion)}</small>` : ''}
                </div>
            </td>
            <td>${esc(p.nombre_contacto || '—')}</td>
            <td>${esc(p.telefono || '—')}</td>
            <td><a href="mailto:${esc(p.correo)}" style="color:#841480;text-decoration:none;">${esc(p.correo || '—')}</a></td>
            <td>${p.nit ? `<span class="td-fiscal">${esc(p.nit)}</span>` : '<span class="td-fiscal vacio">—</span>'}</td>
            <td>${badgeEstado(p.estado)}</td>
            <td>
                <button class="btn-accion btn-ver"         onclick="verProveedor(${p.id_proveedor})"                           title="Ver"><i class="bi bi-eye"></i></button>
                <button class="btn-accion btn-editar"      onclick="abrirModalProveedor(${p.id_proveedor})"                   title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminar(${p.id_proveedor}, '${esc(p.nombre)}')" title="Eliminar"><i class="bi bi-trash3"></i></button>
            </td>
        </tr>
    `).join('');
}

function filtrarTabla() {
    const texto  = document.getElementById('buscador').value.toLowerCase().trim();
    const estado = document.getElementById('filtroEstado').value;
    const filtrado = _todosProveedores.filter(p => {
        const coincideTexto = !texto ||
            (p.nombre          || '').toLowerCase().includes(texto) ||
            (p.correo          || '').toLowerCase().includes(texto) ||
            (p.nit             || '').toLowerCase().includes(texto) ||
            (p.nombre_contacto || '').toLowerCase().includes(texto);
        const coincideEstado = !estado ||
            (estado === 'activo'   && p.estado == 1) ||
            (estado === 'inactivo' && p.estado == 0);
        return coincideTexto && coincideEstado;
    });
    renderizarTabla(filtrado);
}

function nuevaProveedor() {
    document.getElementById('tituloModalProveedor').textContent = 'Nuevo Proveedor';
    document.getElementById('formProveedor').reset();
    document.getElementById('prov_id').value = '';
    limpiarErrores();
    abrirModal('modalProveedor');
}

function abrirModalProveedor(id) {
    const p = _todosProveedores.find(x => x.id_proveedor == id);
    if (!p) return;
    document.getElementById('tituloModalProveedor').textContent = 'Editar Proveedor';
    document.getElementById('prov_id').value        = p.id_proveedor;
    document.getElementById('prov_nombre').value    = p.nombre           || '';
    document.getElementById('prov_contacto').value  = p.nombre_contacto  || '';
    document.getElementById('prov_telefono').value  = p.telefono         || '';
    document.getElementById('prov_correo').value    = p.correo           || '';
    document.getElementById('prov_direccion').value = p.direccion        || '';
    document.getElementById('prov_nit').value       = p.nit              || '';
    document.getElementById('prov_nrc').value       = p.nrc              || '';
    document.getElementById('prov_estado').checked  = p.estado == 1;
    limpiarErrores();
    abrirModal('modalProveedor');
}

function verProveedor(id) {
    const p = _todosProveedores.find(x => x.id_proveedor == id);
    if (!p) return;
    document.getElementById('cuerpoVerProveedor').innerHTML = `
        <div class="detalle-grid">
            <div class="detalle-item"><label>Empresa</label><span>${esc(p.nombre)}</span></div>
            <div class="detalle-item"><label>Contacto</label><span>${esc(p.nombre_contacto || '—')}</span></div>
            <hr class="detalle-divider">
            <div class="detalle-item"><label>Teléfono</label><span>${esc(p.telefono || '—')}</span></div>
            <div class="detalle-item"><label>Correo</label><span>${esc(p.correo || '—')}</span></div>
            <div class="detalle-item" style="grid-column:1/-1"><label>Dirección</label><span>${esc(p.direccion || '—')}</span></div>
            <hr class="detalle-divider">
            <div class="detalle-item"><label>NIT</label><span>${esc(p.nit || '—')}</span></div>
            <div class="detalle-item"><label>NRC</label><span>${esc(p.nrc || '—')}</span></div>
            <hr class="detalle-divider">
            <div class="detalle-item"><label>Estado</label><span>${badgeEstado(p.estado)}</span></div>
            <div class="detalle-item"><label>Registrado</label><span>${formatearFecha(p.created_at)}</span></div>
        </div>
    `;
    abrirModal('modalVerProveedor');
}

function confirmarEliminar(id, nombre) {
    provIdEliminar = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    abrirModal('modalEliminar');
}

function eliminarProveedor() {
    if (!provIdEliminar) return;
    const formData = new FormData();
    formData.append('accion',       'eliminar');
    formData.append('id_proveedor', provIdEliminar);
    fetch(CONTROLLER, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                provIdEliminar = null;
                cerrarModal('modalEliminar');
                cargarProveedores();
            } else {
                alert(res.mensaje);
            }
        })
        .catch(e => console.error('Error al eliminar:', e));
}

function abrirModal(id)  { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

function limpiarErrores() {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

function formatearFecha(fecha) {
    if (!fecha) return '—';
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
    return estado == 1
        ? '<span class="badge-activo">Activo</span>'
        : '<span class="badge-inactivo">Inactivo</span>';
}