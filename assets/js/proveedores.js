const CONTROLLER = '../controllers/ProveedorController.php';

document.addEventListener('DOMContentLoaded', () => {
    cargarProveedores();
    document.getElementById('formProveedor').addEventListener('submit', guardarProveedor);
    document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarProveedor);
});

let provIdEliminar = null;


function cargarProveedores() {
    fetch(CONTROLLER + '?accion=listar')
        .then(r => r.json())
        .then(res => {
            if (res.ok) renderizarTabla(res.datos);
            else console.error(res.mensaje);
        })
        .catch(e => console.error('Error al cargar proveedores:', e));
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');

    if (!lista || !lista.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">No hay proveedores registrados.</td></tr>';
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
                <button class="btn-accion btn-ver"         onclick="verProveedor(${p.id_proveedor})"            title="Ver"><i class="bi bi-eye"></i></button>
                <button class="btn-accion btn-editar"      onclick="abrirModalProveedor(${p.id_proveedor})"    title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminar(${p.id_proveedor}, '${esc(p.nombre)}')" title="Eliminar"><i class="bi bi-trash3"></i></button>
            </td>
        </tr>
    `).join('');
}


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
    document.getElementById('prov_estado').checked  = p.estado == 1;

    limpiarErrores();
    abrirModal('modalProveedor');
}

/* ── Ver detalle ── */
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
            <hr class="detalle-divider">
            <div class="detalle-item"><label>Estado</label><span>${badgeEstado(p.estado)}</span></div>
            <div class="detalle-item"><label>Registrado</label><span>${formatearFecha(p.created_at)}</span></div>
        </div>
    `;
    abrirModal('modalVerProveedor');
}


function guardarProveedor(e) {
    e.preventDefault();
    limpiarErrores();

    const nombre = document.getElementById('prov_nombre').value.trim();
    if (!nombre) {
        document.getElementById('err_nombre').textContent = 'El nombre es obligatorio.';
        return;
    }

    const correo = document.getElementById('prov_correo').value.trim();
    if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        document.getElementById('err_correo').textContent = 'Ingresa un correo válido.';
        return;
    }

    const formData = new FormData();
    formData.append('accion',          'guardar');
    formData.append('id_proveedor',    document.getElementById('prov_id').value);
    formData.append('nombre',          nombre);
    formData.append('nombre_contacto', document.getElementById('prov_contacto').value.trim());
    formData.append('telefono',        document.getElementById('prov_telefono').value.trim());
    formData.append('correo',          correo);
    formData.append('direccion',       document.getElementById('prov_direccion').value.trim());
    formData.append('nit',             document.getElementById('prov_nit').value.trim());
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
        .catch(e => console.error('Error al guardar:', e));
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
