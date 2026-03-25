const CONTROLLER = '../controllers/ProveedorController.php';


document.addEventListener('DOMContentLoaded', () => {
    cargarProveedores();
});

function cargarProveedores() {
    fetch(CONTROLLER + '?accion=listar')
        .then(r => r.json())
        .then(res => {
            if (res.ok) renderizarTablaProveedores(res.datos);
        })
        .catch(e => console.error("Error al cargar:", e));
}

function renderizarTablaProveedores(proveedores) {
    const tbody = document.getElementById('cuerpoTabla');
    if (!tbody) return;

    if (!proveedores || proveedores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">No hay proveedores registrados.</td></tr>';
        return;
    }

    tbody.innerHTML = proveedores.map((p, i) => {
        const estado = p.estado == 1 ? '<span class="badge-activo">Activo</span>' : '<span class="badge-inactivo">Inactivo</span>';
        const pString = JSON.stringify(p).replace(/'/g, "&#39;");

        return `<tr>
            <td>${i + 1}</td>
            <td>${p.nombre}</td>
            <td>${p.nombre_contacto || '—'}</td>
            <td>${p.telefono}</td>
            <td>${p.correo}</td>
            <td>${p.nit}</td>
            <td>${p.nrc || '—'}</td>
            <td>${estado}</td>
            <td><button class="btn-accion btn-editar" onclick='abrirModalProveedor(${pString})'>Editar</button></td>
        </tr>`;
    }).join('');
}

// Abrir Modal
function abrirModalProveedor(p) {
    const form = document.getElementById('formProveedor');
    if (form) form.reset();

    if (p) {
        document.getElementById('tituloModalProveedor').textContent = 'Editar Proveedor';
        document.getElementById('prov_id').value = p.id_proveedor;
        document.getElementById('prov_nombre').value = p.nombre;
        document.getElementById('prov_contacto').value = p.nombre_contacto;
        document.getElementById('prov_telefono').value = p.telefono;
        document.getElementById('prov_correo').value = p.correo;
        document.getElementById('prov_nit').value = p.nit;
        document.getElementById('prov_nrc').value = p.nrc;
        document.getElementById('prov_direccion').value = p.direccion;
        document.getElementById('prov_estado').checked = p.estado == 1;
    } else {
        document.getElementById('tituloModalProveedor').textContent = 'Nuevo Proveedor';
        document.getElementById('prov_id').value = '';
    }
    abrirModal('modalProveedor');
}


document.getElementById('formProveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('accion', 'guardar');
    formData.set('estado', document.getElementById('prov_estado').checked ? '1' : '0');

    fetch(CONTROLLER, { method: 'POST', body: formData })
        .then(async response => {
            if (!response.ok) {
                const text = await response.text();
                throw new Error("Error del servidor (500): " + text);
            }
            return response.json();
        })
        .then(res => {
            if (res.ok) {
                alert(res.mensaje);
                cerrarModal('modalProveedor');
                cargarProveedores();
            } else {
                alert("Error: " + res.mensaje);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error crítico. Revisa la consola para más detalles.");
        });
});


function abrirModal(id) { document.getElementById(id).classList.add('activo'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }