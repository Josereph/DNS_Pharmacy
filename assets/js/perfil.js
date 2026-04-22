/* ══════════════════════════════════════════
   CONSTANTES Y ESTADO
══════════════════════════════════════════ */
const PERFIL_CTRL = '../controllers/PerfilController.php';

const roles = { 1: 'Administrador', 2: 'Cajero' };
const rolesClase = { 1: 'rol-admin', 2: 'rol-cajero' };

let usuarioSesion   = {};
let todasMisVentas  = [];
let periodoActivo   = 'mes';

/* ══════════════════════════════════════════
   INICIALIZACIÓN
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    obtenerPerfil();
    cargarMisVentas();

    document.getElementById('formEditar')  ?.addEventListener('submit', guardarEdicion);
    document.getElementById('formPassword')?.addEventListener('submit', cambiarPassword);

    /* Restricciones en tiempo real en el modal editar */
    aplicarRestriccionesEditar();
});

/* ══════════════════════════════════════════
   RESTRICCIONES EN TIEMPO REAL (modal editar)
══════════════════════════════════════════ */
function aplicarRestriccionesEditar() {
    /* Solo letras y espacios en nombre y apellido */
    ['edit_nombre', 'edit_apellido'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
        });
    });

    /* Teléfono: auto-formato 0000-0000 */
    document.getElementById('edit_telefono')?.addEventListener('input', function () {
        let raw = this.value.replace(/[^0-9]/g, '').slice(0, 8);
        this.value = raw.length <= 4 ? raw : raw.slice(0, 4) + '-' + raw.slice(4);
    });
}

/* ══════════════════════════════════════════
   PERFIL — CARGAR Y PINTAR
══════════════════════════════════════════ */
function obtenerPerfil() {
    fetch(PERFIL_CTRL + '?action=perfil')
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error(data.mensaje); return; }
            usuarioSesion = data.data;
            pintarPerfil();
        })
        .catch(e => console.error('Error al obtener perfil:', e));
}

function pintarPerfil() {
    const u = usuarioSesion;

    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val || '—';
    };

    /* Iniciales para el avatar */
    const ini = ((u.nombre?.[0] || '') + (u.apellido?.[0] || '')).toUpperCase();

    set('perfilNombreCompleto', `${u.nombre || ''} ${u.apellido || ''}`.trim());
    set('perfilCorreo',         u.correo);
    set('perfilTelefono',       u.telefono);
    set('perfilUltimoAcceso',   formatearFecha(u.ultimo_acceso));

    /* Badge de rol */
    const rolEl = document.getElementById('perfilRol');
    if (rolEl) {
        rolEl.textContent = roles[u.id_rol] || '—';
        rolEl.className   = `badge-rol ${rolesClase[u.id_rol] || ''}`;
    }

    /* Foto o avatar de iniciales */
    const fotoEl   = document.getElementById('perfilFoto');
    const avatarEl = document.getElementById('perfilAvatar');

    if (u.foto_perfil && fotoEl && avatarEl) {
        fotoEl.src           = `../uploads/perfiles/${u.foto_perfil}`;
        fotoEl.style.display = 'block';
        avatarEl.style.display = 'none';
    } else if (avatarEl && fotoEl) {
        avatarEl.textContent   = ini;
        avatarEl.style.display = 'flex';
        fotoEl.style.display   = 'none';
    }
}

/* ══════════════════════════════════════════
   FOTO DE PERFIL
══════════════════════════════════════════ */
function previsualizarFoto(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        alert('La imagen no debe superar 2 MB.');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        const fotoEl   = document.getElementById('perfilFoto');
        const avatarEl = document.getElementById('perfilAvatar');
        if (fotoEl)   { fotoEl.src = e.target.result; fotoEl.style.display = 'block'; }
        if (avatarEl) { avatarEl.style.display = 'none'; }
    };
    reader.readAsDataURL(file);
    subirFoto(file);
}

function subirFoto(file) {
    const fd = new FormData();
    fd.append('action',      'subirFoto');
    fd.append('foto_perfil', file);

    fetch(PERFIL_CTRL, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.error) alert('Error al subir la foto: ' + data.mensaje); })
        .catch(e => console.error('Error al subir foto:', e));
}

/* ══════════════════════════════════════════
   MODAL EDITAR PERFIL
══════════════════════════════════════════ */
function abrirModalEditar() {
    const u = usuarioSesion;

    document.getElementById('edit_nombre').value    = u.nombre   || '';
    document.getElementById('edit_apellido').value  = u.apellido || '';
    document.getElementById('edit_telefono').value  = u.telefono || '';

    const correoInput = document.getElementById('edit_correo');
    if (correoInput) {
        correoInput.value    = u.correo || '';
        correoInput.readOnly = true;
        correoInput.style.cssText = 'background:#f5f5f5;cursor:not-allowed;';
        correoInput.title    = 'El correo no se puede modificar';
    }

    limpiarErroresForm('formEditar');
    abrirModal('modalEditar');
}

function guardarEdicion(e) {
    e.preventDefault();
    limpiarErroresForm('formEditar');

    const nombre   = document.getElementById('edit_nombre').value.trim();
    const apellido = document.getElementById('edit_apellido').value.trim();
    const telefono = document.getElementById('edit_telefono').value.trim();

    let valido = true;

    if (!nombre) {
        mostrarCampoError('err_edit_nombre', 'El nombre es obligatorio.');
        valido = false;
    } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{2,}$/.test(nombre)) {
        mostrarCampoError('err_edit_nombre', 'Solo letras, mínimo 2 caracteres.');
        valido = false;
    }

    if (!apellido) {
        mostrarCampoError('err_edit_apellido', 'El apellido es obligatorio.');
        valido = false;
    } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{2,}$/.test(apellido)) {
        mostrarCampoError('err_edit_apellido', 'Solo letras, mínimo 2 caracteres.');
        valido = false;
    }

    if (telefono && !/^\d{4}-\d{4}$/.test(telefono)) {
        mostrarCampoError('err_edit_telefono', 'Formato inválido. Ej: 7600-0000');
        valido = false;
    }

    if (!valido) return;

    /* El correo NO se edita, pero lo enviamos de sesión para que el
       controller no lo requiera — el backend lo ignora de todas formas */
    const fd = new FormData();
    fd.append('action',   'actualizar');
    fd.append('nombre',   nombre);
    fd.append('apellido', apellido);
    fd.append('telefono', telefono);

    const btnGuardar = e.target.querySelector('[type="submit"]');
    if (btnGuardar) { btnGuardar.disabled = true; btnGuardar.textContent = 'Guardando...'; }

    fetch(PERFIL_CTRL, { method: 'POST', body: fd })
        .then(r => {
            /* Si la respuesta no es JSON válido, mostramos el texto crudo para debug */
            const ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                return r.text().then(txt => { throw new Error('Respuesta inesperada del servidor: ' + txt); });
            }
            return r.json();
        })
        .then(data => {
            if (data.error) {
                if (data.campo === 'nombre')   mostrarCampoError('err_edit_nombre',   data.mensaje);
                if (data.campo === 'apellido') mostrarCampoError('err_edit_apellido', data.mensaje);
                if (data.campo === 'telefono') mostrarCampoError('err_edit_telefono', data.mensaje);
                if (!data.campo)               alert('Error: ' + data.mensaje);
                return;
            }
            /* Actualizar estado local y repintar tarjeta */
            Object.assign(usuarioSesion, { nombre, apellido, telefono });
            pintarPerfil();
            cerrarModal('modalEditar');
            mostrarToast('✓ Perfil actualizado correctamente.');
        })
        .catch(e => {
            console.error('Error al guardar:', e);
            alert(e.message || 'Error de conexión al guardar.');
        })
        .finally(() => {
            if (btnGuardar) { btnGuardar.disabled = false; btnGuardar.textContent = 'Guardar cambios'; }
        });
}

/* ══════════════════════════════════════════
   MODAL CAMBIAR CONTRASEÑA
══════════════════════════════════════════ */
function abrirModalPassword() {
    document.getElementById('formPassword').reset();
    limpiarErroresForm('formPassword');
    abrirModal('modalPassword');
}

function cambiarPassword(e) {
    e.preventDefault();
    limpiarErroresForm('formPassword');

    const actual    = document.getElementById('pass_actual').value;
    const nueva     = document.getElementById('pass_nueva').value;
    const confirmar = document.getElementById('pass_confirmar').value;

    let valido = true;

    if (!actual) {
        mostrarCampoError('err_pass_actual', 'Ingresa tu contraseña actual.');
        valido = false;
    }

    if (!nueva) {
        mostrarCampoError('err_pass_nueva', 'Ingresa la nueva contraseña.');
        valido = false;
    } else if (nueva.length < 8) {
        mostrarCampoError('err_pass_nueva', 'Mínimo 8 caracteres.');
        valido = false;
    } else if (!/[A-Z]/.test(nueva)) {
        mostrarCampoError('err_pass_nueva', 'Debe contener al menos una mayúscula.');
        valido = false;
    } else if (!/[0-9]/.test(nueva)) {
        mostrarCampoError('err_pass_nueva', 'Debe contener al menos un número.');
        valido = false;
    }

    if (nueva && confirmar && nueva !== confirmar) {
        mostrarCampoError('err_pass_confirmar', 'Las contraseñas no coinciden.');
        valido = false;
    }

    if (!valido) return;

    const fd = new FormData();
    fd.append('action',          'cambiarPassword');
    fd.append('password_actual', actual);
    fd.append('password_hash',   nueva);

    const btnPass = e.target.querySelector('[type="submit"]');
    if (btnPass) { btnPass.disabled = true; btnPass.textContent = 'Actualizando...'; }

    fetch(PERFIL_CTRL, { method: 'POST', body: fd })
        .then(r => {
            const ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                return r.text().then(txt => { throw new Error('Respuesta inesperada: ' + txt); });
            }
            return r.json();
        })
        .then(data => {
            if (data.error) {
                if (data.campo === 'actual') mostrarCampoError('err_pass_actual', data.mensaje);
                if (data.campo === 'nueva')  mostrarCampoError('err_pass_nueva',  data.mensaje);
                if (!data.campo)             alert('Error: ' + data.mensaje);
                return;
            }
            cerrarModal('modalPassword');
            mostrarToast('✓ Contraseña actualizada correctamente.');
        })
        .catch(e => {
            console.error('Error:', e);
            alert(e.message || 'Error de conexión.');
        })
        .finally(() => {
            if (btnPass) { btnPass.disabled = false; btnPass.textContent = 'Actualizar contraseña'; }
        });
}

/* ══════════════════════════════════════════
   VENTAS
══════════════════════════════════════════ */
function cargarMisVentas() {
    fetch(PERFIL_CTRL + '?action=ventas')
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error(data.mensaje); return; }
            todasMisVentas = data.data;
            setPeriodo(periodoActivo);
        })
        .catch(e => console.error('Error al cargar ventas:', e));
}

function setPeriodo(periodo, event) {
    periodoActivo = periodo;

    /* Marcar botón activo */
    document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('activo'));
    if (event?.target) event.target.classList.add('activo');

    /* Calcular rango */
    const hoy   = new Date();
    let   desde = null;

    if (periodo === 'hoy') {
        desde = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
    } else if (periodo === 'semana') {
        const dia = hoy.getDay() || 7;
        desde = new Date(hoy);
        desde.setDate(hoy.getDate() - dia + 1);
        desde.setHours(0, 0, 0, 0);
    } else if (periodo === 'mes') {
        desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    }

    /* Limpiar filtros de fecha manuales */
    const desdeInput = document.getElementById('filtroDesde');
    const hastaInput = document.getElementById('filtroHasta');
    if (desdeInput) desdeInput.value = '';
    if (hastaInput) hastaInput.value = '';

    filtrarMisVentas(desde, periodo === 'todo' ? null : hoy);
}

function filtrarMisVentas(desdeParam, hastaParam) {
    /* Si se llama desde los inputs de fecha */
    if (desdeParam === undefined) {
        const desdeVal = document.getElementById('filtroDesde')?.value;
        const hastaVal = document.getElementById('filtroHasta')?.value;
        desdeParam = desdeVal ? new Date(desdeVal + 'T00:00:00') : null;
        hastaParam = hastaVal ? new Date(hastaVal + 'T23:59:59') : null;
    }

    const filtrado = todasMisVentas.filter(v => {
        const fecha = new Date(v.fecha_venta);
        if (desdeParam && fecha < desdeParam) return false;
        if (hastaParam && fecha > hastaParam) return false;
        return true;
    });

    renderizarTablaVentas(filtrado);
    actualizarStats(filtrado);
}

function renderizarTablaVentas(lista) {
    const tbody = document.getElementById('cuerpoMisVentas');
    if (!lista.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">Sin ventas en este período.</td></tr>';
        return;
    }

    tbody.innerHTML = lista.map((v, i) => `
        <tr>
            <td>${i + 1}</td>
            <td><span class="td-ticket">${esc(v.numero_ticket)}</span></td>
            <td class="td-fecha">${formatearFecha(v.fecha_venta)}</td>
            <td class="td-monto">$${parseFloat(v.subtotal).toFixed(2)}</td>
            <td class="td-monto">$${parseFloat(v.impuesto).toFixed(2)}</td>
            <td class="td-total">$${parseFloat(v.total).toFixed(2)}</td>
            <td>${badgeMetodo(v.metodo_pago)}</td>
            <td>${badgeEstadoVenta(v.estado)}</td>
            <td>
                <button class="btn-accion btn-ver" onclick="verDetalle(${v.id_venta})">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function actualizarStats(lista) {
    const completadas = lista.filter(v => v.estado === 'completada');
    const totalVendido = completadas.reduce((s, v) => s + parseFloat(v.total), 0);
    const promedio     = completadas.length ? totalVendido / completadas.length : 0;

    const hoy = new Date().toDateString();
    const ventasHoy = completadas.filter(v => new Date(v.fecha_venta).toDateString() === hoy).length;

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

    set('statMisTickets', completadas.length);
    set('statMisVentas',  '$' + totalVendido.toFixed(2));
    set('statPromedio',   '$' + promedio.toFixed(2));
    set('statHoy',        ventasHoy);
}

function verDetalle(id_venta) {
    fetch(`${PERFIL_CTRL}?action=detalle&id_venta=${id_venta}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.mensaje); return; }

            const venta = todasMisVentas.find(v => v.id_venta == id_venta);
            document.getElementById('tituloDetalleVenta').textContent =
                'Detalle — ' + (venta?.numero_ticket || '');

            const items = data.data;
            const subtotalVenta = venta ? parseFloat(venta.subtotal).toFixed(2) : '0.00';
            const impuesto      = venta ? parseFloat(venta.impuesto).toFixed(2)  : '0.00';
            const total         = venta ? parseFloat(venta.total).toFixed(2)     : '0.00';
            const cambio        = venta ? parseFloat(venta.cambio  || 0).toFixed(2) : '0.00';

            document.getElementById('cuerpoDetalleVenta').innerHTML = `
                <table class="tabla-detalle-venta">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Precio unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(it => `
                            <tr>
                                <td>${esc(it.nombre)}</td>
                                <td>${it.cantidad}</td>
                                <td>$${parseFloat(it.precio_unitario).toFixed(2)}</td>
                                <td>$${parseFloat(it.subtotal).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="totales-grid">
                    <div class="total-row"><span>Subtotal</span><span>$${subtotalVenta}</span></div>
                    <div class="total-row"><span>IVA (13%)</span><span>$${impuesto}</span></div>
                    <div class="total-row total-final"><span>Total</span><span>$${total}</span></div>
                    <div class="total-row total-cambio"><span>Cambio</span><span>$${cambio}</span></div>
                </div>
            `;

            abrirModal('modalDetalleVenta');
        })
        .catch(e => console.error('Error al cargar detalle:', e));
}

/* ══════════════════════════════════════════
   HELPERS
══════════════════════════════════════════ */
function abrirModal(id)  { const el = document.getElementById(id); if (el) el.style.display = 'flex'; }
function cerrarModal(id) { const el = document.getElementById(id); if (el) el.style.display = 'none'; }

function mostrarCampoError(id, msg) {
    const el = document.getElementById(id);
    if (el) el.textContent = msg;
}

function limpiarErroresForm(formId) {
    document.getElementById(formId)
        ?.querySelectorAll('.form-error')
        .forEach(el => el.textContent = '');
}

function formatearFecha(f) {
    if (!f) return '—';
    const d = new Date(f);
    if (isNaN(d)) return f;
    return d.toLocaleString('es-SV', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function badgeMetodo(metodo) {
    const map = {
        efectivo:      'metodo-efectivo',
        tarjeta:       'metodo-tarjeta',
        transferencia: 'metodo-transferencia'
    };
    const cls = map[(metodo || '').toLowerCase()] || 'metodo-efectivo';
    return `<span class="badge-metodo ${cls}">${esc(metodo)}</span>`;
}

function badgeEstadoVenta(estado) {
    const map = {
        completada: 'badge-completada',
        anulada:    'badge-anulada',
        pendiente:  'badge-pendiente'
    };
    const cls = map[(estado || '').toLowerCase()] || '';
    return `<span class="${cls}">${esc(estado)}</span>`;
}

/* Cerrar modal al hacer clic en el overlay */
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function (e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

/* ══════════════════════════════════════════
   TOAST DE ÉXITO
══════════════════════════════════════════ */
function mostrarToast(mensaje, color) {
    let toast = document.getElementById('toastPerfil');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toastPerfil';
        toast.style.cssText = [
            'position:fixed', 'bottom:28px', 'right:28px',
            'padding:13px 22px', 'border-radius:8px',
            'font-size:14px', 'font-weight:500', 'color:#fff',
            'box-shadow:0 4px 16px rgba(0,0,0,0.18)',
            'z-index:9999', 'opacity:0', 'transition:opacity 0.3s',
            "font-family:'Segoe UI',sans-serif"
        ].join(';');
        document.body.appendChild(toast);
    }
    toast.style.background = color || '#2e7d32';
    toast.textContent      = mensaje;
    toast.style.opacity    = '1';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 3200);
}