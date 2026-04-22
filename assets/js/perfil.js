/* ══════════════════════════════════════════
   CONSTANTES Y ESTADO
══════════════════════════════════════════ */
const API = '/DNS_Pharmacy/controllers/PerfilController.php';

const roles      = { 1: 'Administrador', 2: 'Cajero' };
const rolesClase = { 1: 'rol-admin',     2: 'rol-cajero' };

let usuarioSesion  = {};
let todasMisVentas = [];
let misVentas      = [];

/* ══════════════════════════════════════════
   INICIALIZACIÓN
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    obtenerPerfil();
    cargarMisVentas();

    document.getElementById('formEditar')  ?.addEventListener('submit', guardarEdicion);
    document.getElementById('formPassword')?.addEventListener('submit', cambiarPassword);

    aplicarRestriccionesEditar();

    /* Limpiar error al tipear */
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('input', () => {
            const err = input.closest('.form-group-custom')?.querySelector('.form-error');
            if (err) err.textContent = '';
        });
    });
});

/* ══════════════════════════════════════════
   RESTRICCIONES EN TIEMPO REAL
══════════════════════════════════════════ */
function aplicarRestriccionesEditar() {
    /* Solo letras en nombre y apellido */
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
   FETCH SEGURO — detecta HTML en vez de JSON
══════════════════════════════════════════ */
function fetchJSON(url, options = {}) {
    return fetch(url, options)
        .then(async res => {
            const text = await res.text();
            if (text.trimStart().startsWith('<')) {
                console.error('El servidor devolvió HTML:', text);
                throw new Error('Error interno del servidor. Revisa la consola.');
            }
            return JSON.parse(text);
        });
}

/* ══════════════════════════════════════════
   PERFIL — CARGAR Y PINTAR
══════════════════════════════════════════ */
function obtenerPerfil() {
    fetchJSON(${API}?action=perfil)
        .then(data => {
            if (data.error) { console.error(data.mensaje); return; }
            Object.assign(usuarioSesion, data.data);
            pintarPerfil();
        })
        .catch(e => console.error('Error perfil:', e));
}

function pintarPerfil() {
    const u   = usuarioSesion;
    const ini = ((u.nombre?.[0] || '') + (u.apellido?.[0] || '')).toUpperCase();

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };

    set('perfilNombreCompleto', ${u.nombre || ''} ${u.apellido || ''}.trim());
    set('perfilCorreo',         u.correo);
    set('perfilTelefono',       u.telefono);
    set('perfilUltimoAcceso',   formatearFecha(u.ultimo_acceso));

    const rolEl = document.getElementById('perfilRol');
    if (rolEl) {
        rolEl.textContent = roles[u.id_rol] || '—';
        rolEl.className   = badge-rol ${rolesClase[u.id_rol] || ''};
    }

    const fotoEl   = document.getElementById('perfilFoto');
    const avatarEl = document.getElementById('perfilAvatar');
    if (u.foto_perfil) {
        fotoEl.src           = /DNS_Pharmacy/uploads/perfiles/${u.foto_perfil};
        fotoEl.style.display = 'block';
        avatarEl.style.display = 'none';
    } else {
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
    if (file.size > 2 * 1024 * 1024) { alert('La imagen no debe superar 2 MB.'); input.value = ''; return; }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('perfilFoto').src = e.target.result;
        document.getElementById('perfilFoto').style.display   = 'block';
        document.getElementById('perfilAvatar').style.display = 'none';
    };
    reader.readAsDataURL(file);
    subirFoto(file);
}

function subirFoto(file) {
    const fd = new FormData();
    fd.append('action',      'subirFoto');
    fd.append('foto_perfil', file);
    fetchJSON(API, { method: 'POST', body: fd })
        .then(data => { if (data.error) alert(data.mensaje); })
        .catch(e => console.error('Error foto:', e));
}

/* ══════════════════════════════════════════
   MODAL EDITAR PERFIL
══════════════════════════════════════════ */
function abrirModalEditar() {
    const u = usuarioSesion;
    document.getElementById('edit_nombre').value   = u.nombre   || '';
    document.getElementById('edit_apellido').value = u.apellido || '';
    document.getElementById('edit_correo').value   = u.correo   || '';
    document.getElementById('edit_telefono').value = u.telefono || '';

    /* Correo: solo lectura visual */
    const correoInput = document.getElementById('edit_correo');
    if (correoInput) {
        correoInput.readOnly          = true;
        correoInput.style.background  = '#f5f5f5';
        correoInput.style.cursor      = 'not-allowed';
        correoInput.title             = 'El correo no se puede modificar';
    }

    limpiarErroresModal('modalEditar');
    abrirModal('modalEditar');
}

function guardarEdicion(e) {
    e.preventDefault();
    limpiarErroresModal('modalEditar');

    const nombre   = document.getElementById('edit_nombre').value.trim();
    const apellido = document.getElementById('edit_apellido').value.trim();
    const correo   = document.getElementById('edit_correo').value.trim();
    const telefono = document.getElementById('edit_telefono').value.trim();

    let valido = true;

    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{2,30}$/.test(nombre)) {
        document.getElementById('err_edit_nombre').textContent = 'Solo letras (2–30 caracteres)';
        valido = false;
    }
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{2,30}$/.test(apellido)) {
        document.getElementById('err_edit_apellido').textContent = 'Solo letras (2–30 caracteres)';
        valido = false;
    }
    if (telefono && !/^\d{4}-\d{4}$/.test(telefono)) {
        document.getElementById('err_edit_telefono').textContent = 'Formato inválido. Ej: 7600-0000';
        valido = false;
    }

    if (!valido) return;

    /* Usar FormData del form para incluir todos los campos (incluido correo readonly) */
    const fd = new FormData(e.target);
    fd.set('action', 'actualizar');

    const btn = e.target.querySelector('[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Guardando...'; }

    fetchJSON(API, { method: 'POST', body: fd })
        .then(data => {
            if (data.error) { alert('Error: ' + data.mensaje); return; }

            Object.assign(usuarioSesion, { nombre, apellido, correo, telefono });
            pintarPerfil();
            cerrarModal('modalEditar');
            mostrarToast('✓ Perfil actualizado correctamente.');
        })
        .catch(e => { console.error(e); alert(e.message); })
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Guardar cambios'; } });
}

/* ══════════════════════════════════════════
   MODAL CAMBIAR CONTRASEÑA
══════════════════════════════════════════ */
function abrirModalPassword() {
    document.getElementById('formPassword').reset();
    limpiarErroresModal('modalPassword');
    abrirModal('modalPassword');
}

function cambiarPassword(e) {
    e.preventDefault();
    limpiarErroresModal('modalPassword');

    const actual    = document.getElementById('pass_actual').value;
    const nueva     = document.getElementById('pass_nueva').value;
    const confirmar = document.getElementById('pass_confirmar').value;

    let valido = true;

    if (!actual) {
        document.getElementById('err_pass_actual').textContent = 'Ingresa tu contraseña actual.';
        valido = false;
    }
    if (!nueva || nueva.length < 8) {
        document.getElementById('err_pass_nueva').textContent = 'Mínimo 8 caracteres.';
        valido = false;
    } else if (!/[A-Z]/.test(nueva)) {
        document.getElementById('err_pass_nueva').textContent = 'Debe tener al menos una mayúscula.';
        valido = false;
    } else if (!/[0-9]/.test(nueva)) {
        document.getElementById('err_pass_nueva').textContent = 'Debe tener al menos un número.';
        valido = false;
    }
    if (nueva !== confirmar) {
        document.getElementById('err_pass_confirmar').textContent = 'Las contraseñas no coinciden.';
        valido = false;
    }

    if (!valido) return;

    const fd = new FormData();
    fd.append('action',          'cambiarPassword');
    fd.append('password_actual', actual);
    fd.append('password_hash',   nueva);

    const btn = e.target.querySelector('[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Actualizando...'; }

    fetchJSON(API, { method: 'POST', body: fd })
        .then(data => {
            if (data.error) {
                if (data.campo === 'actual') document.getElementById('err_pass_actual').textContent = data.mensaje;
                else alert('Error: ' + data.mensaje);
                return;
            }
            cerrarModal('modalPassword');
            mostrarToast('✓ Contraseña actualizada correctamente.');
        })
        .catch(e => { console.error(e); alert(e.message); })
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Actualizar contraseña'; } });
}

/* ══════════════════════════════════════════
   MOSTRAR / OCULTAR CONTRASEÑA
══════════════════════════════════════════ */
function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    if (input.type === 'password') {
        input.type      = 'text';
        btn.innerHTML   = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type      = 'password';
        btn.innerHTML   = '<i class="bi bi-eye"></i>';
    }
}

/* ══════════════════════════════════════════
   VENTAS
══════════════════════════════════════════ */
function cargarMisVentas() {
    fetchJSON(${API}?action=ventas)
        .then(data => {
            if (data.error) { console.error(data.mensaje); return; }
            todasMisVentas = data.data;
            setPeriodo('mes');
        })
        .catch(e => console.error('Error ventas:', e));
}

function setPeriodo(periodo, event) {
    /* Marcar botón activo */
    if (event) {
        document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('activo'));
        event.target.classList.add('activo');
    }

    const hoy = new Date();
    let desde = '', hasta = hoy.toISOString().split('T')[0];

    if (periodo === 'hoy') {
        desde = hasta;
    } else if (periodo === 'semana') {
        const d    = new Date(hoy);
        const day  = d.getDay() || 7;
        d.setDate(d.getDate() - day + 1);
        desde = d.toISOString().split('T')[0];
    } else if (periodo === 'mes') {
        desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
    } else if (periodo === 'todo') {
        desde = ''; hasta = '';
    }

    document.getElementById('filtroDesde').value = desde;
    document.getElementById('filtroHasta').value  = hasta;
    filtrarMisVentas();
}

function filtrarMisVentas() {
    const desde = document.getElementById('filtroDesde').value;
    const hasta = document.getElementById('filtroHasta').value;
    const hoy   = new Date().toISOString().split('T')[0];

    misVentas = todasMisVentas.filter(v => {
        const fecha = v.fecha_venta.split(' ')[0];
        return (!desde || fecha >= desde) && (!hasta || fecha <= hasta);
    });

    const ventasHoy = todasMisVentas.filter(v => v.fecha_venta.startsWith(hoy)).length;
    actualizarStats(ventasHoy);
    renderizarTabla(misVentas);
}

function actualizarStats(ventasHoy) {
    const completadas    = misVentas.filter(v => v.estado === 'completada');
    const totalVendido   = completadas.reduce((s, v) => s + parseFloat(v.total), 0);
    const promedio       = completadas.length ? totalVendido / completadas.length : 0;

    document.getElementById('statMisTickets').textContent = completadas.length;
    document.getElementById('statMisVentas').textContent  = $${totalVendido.toFixed(2)};
    document.getElementById('statPromedio').textContent   = $${promedio.toFixed(2)};
    document.getElementById('statHoy').textContent        = ventasHoy;
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoMisVentas');
    if (!lista.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">No hay ventas en este período.</td></tr>';
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
            <td>${badgeEstado(v.estado)}</td>
            <td>
                <button class="btn-accion btn-ver"
                    onclick="verDetalleVenta(${v.id_venta}, '${esc(v.numero_ticket)}')">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function verDetalleVenta(id_venta, ticket) {
    document.getElementById('tituloDetalleVenta').textContent = Detalle — ${ticket};
    document.getElementById('cuerpoDetalleVenta').innerHTML   = '<p style="padding:16px;color:#888;">Cargando...</p>';
    abrirModal('modalDetalleVenta');

    fetchJSON(${API}?action=detalle&id_venta=${id_venta})
        .then(data => {
            if (data.error) {
                document.getElementById('cuerpoDetalleVenta').innerHTML = <p class="form-error" style="padding:16px">${data.mensaje}</p>;
                return;
            }
            const items = data.data;
            const venta = todasMisVentas.find(v => v.id_venta == id_venta);

            if (!items.length) {
                document.getElementById('cuerpoDetalleVenta').innerHTML = '<p style="padding:16px;color:#888;">Sin detalles disponibles.</p>';
                return;
            }

            document.getElementById('cuerpoDetalleVenta').innerHTML = `
                <table class="tabla-detalle-venta">
                    <thead>
                        <tr>
                            <th>Producto</th><th>Cant.</th><th>P. Unit.</th><th>Subtotal</th>
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
                    <div class="total-row"><span>Subtotal</span><span>$${parseFloat(venta?.subtotal||0).toFixed(2)}</span></div>
                    <div class="total-row"><span>IVA (13%)</span><span>$${parseFloat(venta?.impuesto||0).toFixed(2)}</span></div>
                    <div class="total-row total-final"><span>Total</span><span>$${parseFloat(venta?.total||0).toFixed(2)}</span></div>
                    <div class="total-row total-cambio"><span>Cambio</span><span>$${parseFloat(venta?.cambio||0).toFixed(2)}</span></div>
                </div>
            `;
        })
        .catch(e => {
            document.getElementById('cuerpoDetalleVenta').innerHTML = <p class="form-error" style="padding:16px">Error al cargar detalles.</p>;
            console.error(e);
        });
}

/* ══════════════════════════════════════════
   HELPERS
══════════════════════════════════════════ */
function abrirModal(id)  { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

function limpiarErroresModal(modalId) {
    document.getElementById(modalId)?.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

function formatearFecha(f) {
    if (!f) return '—';
    const d = new Date(f);
    return isNaN(d) ? f : d.toLocaleString('es-SV');
}

function esc(str) {
    return String(str || '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function badgeMetodo(m) {
    if (!m) return '—';
    const map = { efectivo: 'metodo-efectivo', tarjeta: 'metodo-tarjeta', transferencia: 'metodo-transferencia' };
    const cls = map[m.toLowerCase()] || '';
    return <span class="badge-metodo ${cls}">${esc(m)}</span>;
}

function badgeEstado(e) {
    if (!e) return '—';
    const map = { completada: 'badge-completada', anulada: 'badge-anulada', pendiente: 'badge-pendiente' };
    const cls = map[e.toLowerCase()] || '';
    return <span class="${cls}">${esc(e)}</span>;
}

/* Cerrar modal al clicar el overlay */
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function (e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

/* ══════════════════════════════════════════
   TOAST DE ÉXITO
══════════════════════════════════════════ */
function mostrarToast(mensaje) {
    let toast = document.getElementById('toastPerfil');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toastPerfil';
        toast.style.cssText = [
            'position:fixed','bottom:28px','right:28px',
            'background:#2e7d32','color:#fff',
            'padding:13px 22px','border-radius:8px',
            'font-size:14px','font-weight:500',
            'box-shadow:0 4px 16px rgba(0,0,0,0.18)',
            "font-family:'Segoe UI',sans-serif",
            'z-index:9999','opacity:0','transition:opacity 0.3s'
        ].join(';');
        document.body.appendChild(toast);
    }
    toast.textContent   = mensaje;
    toast.style.opacity = '1';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 3200);
}