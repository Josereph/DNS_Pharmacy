let proveedores = [];
let provIdEliminar = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarProveedores();
    document.getElementById('formProveedor').addEventListener('submit', guardarProveedor);
    document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarProveedor);
});

function cargarProveedores() {
    proveedores = [
        {
            id_proveedor: 1,
            nombre: 'Distribuidora Médica S.A.',
            nombre_contacto: 'Juan Pérez',
            telefono: '+503 7600-0001',
            correo: 'contacto@distmedica.com',
            direccion: 'Col. Escalón, San Salvador',
            nit: '0614-010101-001-0',
            nrc: '123456-7',
            estado: 'activo'
        },
        {
            id_proveedor: 2,
            nombre: 'Pharma Supply Co.',
            nombre_contacto: 'Ana Gómez',
            telefono: '+503 7600-0002',
            correo: 'ventas@pharmasupply.com',
            direccion: 'Santa Ana, El Salvador',
            nit: '0106-020202-002-1',
            nrc: '234567-8',
            estado: 'activo'
        },
        {
            id_proveedor: 3,
            nombre: 'MedTotal S.A. de C.V.',
            nombre_contacto: 'Carlos Ramos',
            telefono: '+503 7600-0003',
            correo: 'info@medtotal.com.sv',
            direccion: 'San Miguel, El Salvador',
            nit: '',
            nrc: '',
            estado: 'inactivo'
        }
    ];
    renderizarTabla(proveedores);
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="tabla-vacia">No hay proveedores registrados.</td></tr>`;
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
            <td>${esc(p.nombre_contacto)}</td>
            <td>${esc(p.telefono)}</td>
            <td><a href="mailto:${esc(p.correo)}" style="color:#841480;text-decoration:none;">${esc(p.correo)}</a></td>
            <td>${p.nit ? `<span class="td-fiscal">${esc(p.nit)}</span>` : `<span class="td-fiscal vacio">—</span>`}</td>
            <td>${p.nrc ? `<span class="td-fiscal">${esc(p.nrc)}</span>` : `<span class="td-fiscal vacio">—</span>`}</td>
            <td>${badgeEstado(p.estado)}</td>
            <td>
                <button class="btn-accion btn-ver" title="Ver detalle" onclick="verProveedor(${p.id_proveedor})"><i class="bi bi-eye"></i></button>
                <button class="btn-accion btn-editar" title="Editar" onclick="editarProveedor(${p.id_proveedor})"><i class="bi bi-pencil"></i></button>
                <button class="btn-accion btn-eliminar-sm" title="Eliminar" onclick="confirmarEliminar(${p.id_proveedor}, '${esc(p.nombre)}')"><i class="bi bi-trash3"></i></button>
            </td>
        </tr>
    `).join('');
}

function filtrarTabla() {
    const texto  = document.getElementById('buscador').value.toLowerCase().trim();
    const estado = document.getElementById('filtroEstado').value;

    const filtrado = proveedores.filter(p => {
        const coincideTexto = !texto ||
            p.nombre.toLowerCase().includes(texto) ||
            p.correo.toLowerCase().includes(texto) ||
            p.nit.toLowerCase().includes(texto) ||
            p.nombre_contacto.toLowerCase().includes(texto);
        const coincideEstado = !estado || p.estado === estado;
        return coincideTexto && coincideEstado;
    });

    renderizarTabla(filtrado);
}

function abrirModalProveedor() {
    document.getElementById('tituloModalProveedor').textContent = 'Nuevo Proveedor';
    document.getElementById('formProveedor').reset();
    document.getElementById('prov_id').value = '';
    limpiarErrores();
    abrirModal('modalProveedor');
}

function editarProveedor(id) {
    const p = proveedores.find(x => x.id_proveedor === id);
    if (!p) return;

    document.getElementById('tituloModalProveedor').textContent = 'Editar Proveedor';
    document.getElementById('prov_id').value        = p.id_proveedor;
    document.getElementById('prov_nombre').value    = p.nombre;
    document.getElementById('prov_contacto').value  = p.nombre_contacto;
    document.getElementById('prov_telefono').value  = p.telefono;
    document.getElementById('prov_correo').value    = p.correo;
    document.getElementById('prov_direccion').value = p.direccion;
    document.getElementById('prov_nit').value       = p.nit;
    document.getElementById('prov_nrc').value       = p.nrc;
    document.getElementById('prov_estado').checked  = p.estado === 'activo';

    limpiarErrores();
    abrirModal('modalProveedor');
}

function verProveedor(id) {
    const p = proveedores.find(x => x.id_proveedor === id);
    if (!p) return;

    document.getElementById('cuerpoVerProveedor').innerHTML = `
        <div class="detalle-grid">
            <div class="detalle-item">
                <label>Empresa</label>
                <span>${esc(p.nombre)}</span>
            </div>
            <div class="detalle-item">
                <label>Contacto</label>
                <span>${esc(p.nombre_contacto)}</span>
            </div>
            <hr class="detalle-divider">
            <div class="detalle-item">
                <label>Teléfono</label>
                <span>${esc(p.telefono)}</span>
            </div>
            <div class="detalle-item">
                <label>Correo</label>
                <span>${esc(p.correo)}</span>
            </div>
            <div class="detalle-item" style="grid-column:1/-1">
                <label>Dirección</label>
                <span>${p.direccion ? esc(p.direccion) : '—'}</span>
            </div>
            <hr class="detalle-divider">
            <div class="detalle-item">
                <label>NIT</label>
                <span>${p.nit ? esc(p.nit) : '—'}</span>
            </div>
            <div class="detalle-item">
                <label>NRC</label>
                <span>${p.nrc ? esc(p.nrc) : '—'}</span>
            </div>
            <hr class="detalle-divider">
            <div class="detalle-item">
                <label>Estado</label>
                <span>${badgeEstado(p.estado)}</span>
            </div>
        </div>
    `;
    abrirModal('modalVerProveedor');
}

function guardarProveedor(e) {
    e.preventDefault();
    if (!validarFormulario()) return;

    const datos = {
        id_proveedor:    document.getElementById('prov_id').value,
        nombre:          document.getElementById('prov_nombre').value.trim(),
        nombre_contacto: document.getElementById('prov_contacto').value.trim(),
        telefono:        document.getElementById('prov_telefono').value.trim(),
        correo:          document.getElementById('prov_correo').value.trim(),
        direccion:       document.getElementById('prov_direccion').value.trim(),
        nit:             document.getElementById('prov_nit').value.trim(),
        nrc:             document.getElementById('prov_nrc').value.trim(),
        estado:          document.getElementById('prov_estado').checked ? 'activo' : 'inactivo'
    };

    if (datos.id_proveedor) {
        const idx = proveedores.findIndex(x => x.id_proveedor == datos.id_proveedor);
        if (idx !== -1) proveedores[idx] = { ...proveedores[idx], ...datos };
    } else {
        datos.id_proveedor = Date.now();
        proveedores.push(datos);
    }

    renderizarTabla(proveedores);
    cerrarModal('modalProveedor');
}

function confirmarEliminar(id, nombre) {
    provIdEliminar = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    abrirModal('modalEliminar');
}

function eliminarProveedor() {
    if (!provIdEliminar) return;
    proveedores = proveedores.filter(x => x.id_proveedor !== provIdEliminar);
    provIdEliminar = null;
    renderizarTabla(proveedores);
    cerrarModal('modalEliminar');
}

function validarFormulario() {
    let ok = true;
    limpiarErrores();

    const campos = [
        { id: 'prov_nombre',   err: 'err_nombre',   msg: 'El nombre es obligatorio.' },
        { id: 'prov_contacto', err: 'err_contacto', msg: 'El nombre del contacto es obligatorio.' },
        { id: 'prov_telefono', err: 'err_telefono', msg: 'El teléfono es obligatorio.' },
        { id: 'prov_correo',   err: 'err_correo',   msg: 'El correo es obligatorio.' },
    ];

    campos.forEach(c => {
        const val = document.getElementById(c.id).value.trim();
        if (!val) {
            document.getElementById(c.err).textContent = c.msg;
            ok = false;
        }
    });

    const correo = document.getElementById('prov_correo').value.trim();
    if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        document.getElementById('err_correo').textContent = 'Ingresa un correo válido.';
        ok = false;
    }

    return ok;
}

function limpiarErrores() {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

function abrirModal(id)  { document.getElementById(id).classList.add('activo'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function badgeEstado(estado) {
    return estado === 'activo'
        ? `<span class="badge-activo">Activo</span>`
        : `<span class="badge-inactivo">Inactivo</span>`;
}