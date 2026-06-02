<<<<<<< HEAD

=======
/* ═══════════════════════════════════════════════
   perfil.js — DNS Pharmacy
   COMPLETAMENTE FUNCIONAL
═══════════════════════════════════════════════ */
>>>>>>> origin/FrontEnd2

const API = '/DNS_Pharmacy/controllers/PerfilController.php';

let misVentas      = [];
let todasMisVentas = [];
let usuarioSesion = {};
const roles      = { 1: 'Administrador', 2: 'Cajero' };
const rolesClase = { 1: 'rol-admin', 2: 'rol-cajero' };

/* ══════════════════════════════════
   INIT
══════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    obtenerPerfil();
    cargarMisVentas();
    setPeriodo('mes');
    iniciarValidacionEnTiempoReal();
});

/* ══════════════════════════════════
   PERFIL
══════════════════════════════════ */
function obtenerPerfil() {
    fetch(`${API}?action=perfil`)
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error('Error perfil:', data.mensaje); return; }
            Object.assign(usuarioSesion, data.data);
            pintarPerfil();
        })
        .catch(e => console.error('Error al obtener perfil:', e));
}

function pintarPerfil() {
    const u   = usuarioSesion;
    const ini = ((u.nombre?.[0] || '') + (u.apellido?.[0] || '')).toUpperCase();

    document.getElementById('perfilNombreCompleto').textContent = `${u.nombre || ''} ${u.apellido || ''}`.trim();
    document.getElementById('perfilRol').textContent            = roles[u.id_rol] || '—';
    document.getElementById('perfilRol').className              = `badge-rol ${rolesClase[u.id_rol] || ''}`;
    document.getElementById('perfilCorreo').textContent         = u.correo   || '—';
    document.getElementById('perfilTelefono').textContent       = u.telefono || '—';
    document.getElementById('perfilUltimoAcceso').textContent   = formatearFecha(u.ultimo_acceso);

    const avatar = document.getElementById('perfilAvatar');
    const foto   = document.getElementById('perfilFoto');

    if (u.foto_perfil) {
        foto.src           = `../uploads/perfiles/${u.foto_perfil}`;
        foto.style.display  = 'block';
        avatar.style.display = 'none';
    } else {
        avatar.textContent   = ini || 'U';
        avatar.style.display = 'flex';
        foto.style.display   = 'none';
    }
}

/* ══════════════════════════════════
   FOTO
══════════════════════════════════ */
function previsualizarFoto(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        mostrarToast('La imagen no debe superar 2MB.', 'error');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('perfilFoto').src           = e.target.result;
        document.getElementById('perfilFoto').style.display  = 'block';
        document.getElementById('perfilAvatar').style.display = 'none';
    };
    reader.readAsDataURL(file);
    subirFoto(file);
}

function subirFoto(file) {
    const fd = new FormData();
    fd.append('action',      'subirFoto');
    fd.append('foto_perfil', file);

    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.error) mostrarToast('Error al subir foto: ' + data.mensaje, 'error');
            else            mostrarToast('Foto actualizada correctamente.', 'exito');
        })
        .catch(e => console.error('Error foto:', e));
}

/* ══════════════════════════════════
   MODAL EDITAR PERFIL
══════════════════════════════════ */
function abrirModalEditar() {
    const nombre   = document.getElementById('edit_nombre');
    const apellido = document.getElementById('edit_apellido');
    const telefono = document.getElementById('edit_telefono');
    const correo   = document.getElementById('edit_correo_display');

    nombre.value   = usuarioSesion.nombre   || '';
    apellido.value = usuarioSesion.apellido || '';
    telefono.value = usuarioSesion.telefono || '';
    if (correo) correo.value = usuarioSesion.correo || '';

    limpiarErrores(['err_edit_nombre', 'err_edit_apellido', 'err_edit_telefono']);
    abrirModal('modalEditar');
    setTimeout(() => nombre.focus(), 100);
}

function guardarEdicion() {
    limpiarErrores(['err_edit_nombre', 'err_edit_apellido', 'err_edit_telefono']);

    const nombre   = document.getElementById('edit_nombre').value.trim();
    const apellido = document.getElementById('edit_apellido').value.trim();
    const telefono = document.getElementById('edit_telefono').value.trim();

    let valido = true;

    if (!nombre) {
        setError('err_edit_nombre', 'El nombre es obligatorio.');
        valido = false;
    } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u.test(nombre)) {
        setError('err_edit_nombre', 'Solo se permiten letras.');
        valido = false;
    } else if (nombre.length < 2 || nombre.length > 50) {
        setError('err_edit_nombre', 'Debe tener entre 2 y 50 caracteres.');
        valido = false;
    }

    if (!apellido) {
        setError('err_edit_apellido', 'El apellido es obligatorio.');
        valido = false;
    } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u.test(apellido)) {
        setError('err_edit_apellido', 'Solo se permiten letras.');
        valido = false;
    } else if (apellido.length < 2 || apellido.length > 50) {
        setError('err_edit_apellido', 'Debe tener entre 2 y 50 caracteres.');
        valido = false;
    }

    if (telefono !== '') {
        const soloDigitos = telefono.replace(/\D/g, '');
        if (soloDigitos.length !== 8) {
            setError('err_edit_telefono', 'Debe tener exactamente 8 dígitos numéricos.');
            valido = false;
        }
    }

    if (!valido) return;

    const fd = new FormData();
    fd.append('action',   'actualizar');
    fd.append('nombre',   nombre);
    fd.append('apellido', apellido);
    fd.append('telefono', telefono);

    fetch(API, { method: 'POST', body: fd })
        .then(async r => {
            const texto = await r.text();
            try { return JSON.parse(texto); }
            catch { throw new Error('Respuesta inválida: ' + texto.slice(0, 200)); }
        })
        .then(data => {
            if (data.error) {
                if (data.campo === 'nombre')   setError('err_edit_nombre', data.mensaje);
                if (data.campo === 'apellido') setError('err_edit_apellido', data.mensaje);
                if (data.campo === 'telefono') setError('err_edit_telefono', data.mensaje);
                if (!data.campo) mostrarToast(data.mensaje, 'error');
                return;
            }
            Object.assign(usuarioSesion, { nombre, apellido, telefono });
            pintarPerfil();
            cerrarModal('modalEditar');
            mostrarToast('✓ Perfil actualizado correctamente.', 'exito');
        })
        .catch(e => {
            console.error('Error al guardar perfil:', e);
            mostrarToast('Error de conexión.', 'error');
        });
}

/* ══════════════════════════════════
   MODAL CONTRASEÑA
══════════════════════════════════ */
function abrirModalPassword() {
    document.getElementById('pass_actual').value    = '';
    document.getElementById('pass_nueva').value     = '';
    document.getElementById('pass_confirmar').value = '';
    limpiarErrores(['err_pass_actual', 'err_pass_nueva', 'err_pass_confirmar']);
    abrirModal('modalPassword');
    setTimeout(() => document.getElementById('pass_actual').focus(), 100);
}

function guardarPassword() {
    limpiarErrores(['err_pass_actual', 'err_pass_nueva', 'err_pass_confirmar']);

    const actual    = document.getElementById('pass_actual').value;
    const nueva     = document.getElementById('pass_nueva').value;
    const confirmar = document.getElementById('pass_confirmar').value;

    let valido = true;

    if (!actual) {
        setError('err_pass_actual', 'Ingresa tu contraseña actual.');
        valido = false;
    }
    if (!nueva) {
        setError('err_pass_nueva', 'Ingresa la nueva contraseña.');
        valido = false;
    } else if (nueva.length < 8) {
        setError('err_pass_nueva', 'Mínimo 8 caracteres.');
        valido = false;
    } else if (!/[A-Z]/.test(nueva)) {
        setError('err_pass_nueva', 'Debe contener al menos una letra mayúscula.');
        valido = false;
    } else if (!/[0-9]/.test(nueva)) {
        setError('err_pass_nueva', 'Debe contener al menos un número.');
        valido = false;
    }
    if (nueva && confirmar && nueva !== confirmar) {
        setError('err_pass_confirmar', 'Las contraseñas no coinciden.');
        valido = false;
    }

    if (!valido) return;

    const fd = new FormData();
    fd.append('action',          'cambiarPassword');
    fd.append('password_actual', actual);
    fd.append('password_nuevo',  nueva);

    fetch(API, { method: 'POST', body: fd })
        .then(async r => {
            const texto = await r.text();
            try { return JSON.parse(texto); }
            catch { throw new Error('Respuesta inválida: ' + texto.slice(0, 200)); }
        })
        .then(data => {
            if (data.error) {
                if (data.campo === 'actual') setError('err_pass_actual', data.mensaje);
                if (data.campo === 'nueva')  setError('err_pass_nueva', data.mensaje);
                if (!data.campo) mostrarToast(data.mensaje, 'error');
                return;
            }
            cerrarModal('modalPassword');
            mostrarToast('✓ Contraseña actualizada correctamente.', 'exito');
        })
        .catch(e => {
            console.error('Error contraseña:', e);
            mostrarToast('Error de conexión.', 'error');
        });
}

/* ══════════════════════════════════
   VENTAS
══════════════════════════════════ */
function cargarMisVentas() {
    fetch(`${API}?action=ventas`)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            todasMisVentas = data.data;
            filtrarMisVentas();
        })
        .catch(e => console.error('Error ventas:', e));
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
    const total    = misVentas.reduce((s, v) => s + parseFloat(v.total), 0);
    const tickets  = misVentas.length;
    const promedio = tickets ? total / tickets : 0;

    document.getElementById('statMisTickets').textContent = tickets;
    document.getElementById('statMisVentas').textContent  = `$${total.toFixed(2)}`;
    document.getElementById('statPromedio').textContent   = `$${promedio.toFixed(2)}`;
    document.getElementById('statHoy').textContent        = ventasHoy;
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoMisVentas');
    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="tabla-vacia">No hay ventas en el período seleccionado.懈</table>`;
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
                <button class="btn-accion btn-ver" onclick="verDetalleVenta(${v.id_venta})">
                    <i class="bi bi-receipt"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/* ══════════════════════════════════
   DETALLE DE VENTA - CORREGIDO
══════════════════════════════════ */
function verDetalleVenta(id) {
    console.log("Ver detalle venta ID:", id);
    
    if (!id) {
        mostrarToast('ID de venta inválido', 'error');
        return;
    }
    
    // Abrir modal y mostrar loading
    const cuerpo = document.getElementById('cuerpoDetalleVenta');
    const titulo = document.getElementById('tituloDetalleVenta');
    
    if (titulo) titulo.textContent = 'Cargando ticket...';
    if (cuerpo) cuerpo.innerHTML = '<div style="text-align:center;padding:40px;"><i class="bi bi-hourglass-split"></i> Cargando detalles...</div>';
    
    abrirModal('modalDetalleVenta');
    
    // Buscar el ticket número de la venta
    const venta = todasMisVentas.find(v => v.id_venta == id);
    const ticketNum = venta ? venta.numero_ticket : '#' + id;
    if (titulo) titulo.textContent = `Ticket ${ticketNum}`;
    
    // Hacer la petición al servidor
    const url = `${API}?action=detalle&id_venta=${id}`;
    console.log("URL llamada:", url);
    
    fetch(url)
        .then(response => {
            console.log("Respuesta status:", response.status);
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Datos recibidos:", data);
            
            if (data.error) {
                mostrarToast(data.mensaje || 'Error al cargar detalle', 'error');
                if (cuerpo) {
                    cuerpo.innerHTML = `<div style="text-align:center;padding:40px;color:#c62828;">
                        <i class="bi bi-exclamation-triangle"></i> ${data.mensaje || 'Error al cargar el detalle'}
                    </div>`;
                }
                return;
            }
            
            if (!data.data || data.data.length === 0) {
                if (cuerpo) {
                    cuerpo.innerHTML = '<div style="text-align:center;padding:40px;">No hay productos en esta venta</div>';
                }
                return;
            }
            
            // Construir HTML de productos
            let productosHtml = `
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.data.forEach(item => {
                productosHtml += `
                    <tr>
                        <td>${esc(item.nombre)}</td>
                        <td class="text-center">${item.cantidad}</td>
                        <td class="text-end">$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                        <td class="text-end"><strong>$${parseFloat(item.subtotal).toFixed(2)}</strong></td>
                    </tr>
                `;
            });
            
            productosHtml += `
                    </tbody>
                </table>
            `;
            
            // Agregar totales si tenemos la venta
            if (venta) {
                productosHtml += `
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span>$${parseFloat(venta.subtotal).toFixed(2)}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Impuesto:</span>
                                <span>$${parseFloat(venta.impuesto).toFixed(2)}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>TOTAL:</span>
                                <span>$${parseFloat(venta.total).toFixed(2)}</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-muted">
                                <span>Monto recibido:</span>
                                <span>$${parseFloat(venta.monto_recibido).toFixed(2)}</span>
                            </div>
                            <div class="d-flex justify-content-between text-success">
                                <span>Cambio:</span>
                                <span>$${parseFloat(venta.cambio).toFixed(2)}</span>
                            </div>
                            <div class="mt-2 small text-muted">
                                Método de pago: ${venta.metodo_pago || 'N/A'}
                            </div>
                        </div>
                    </div>
                `;
            }
            
            if (cuerpo) {
                cuerpo.innerHTML = productosHtml;
            }
        })
        .catch(error => {
            console.error('Error detalle:', error);
            mostrarToast('Error de conexión al cargar el detalle', 'error');
            if (cuerpo) {
                cuerpo.innerHTML = `<div style="text-align:center;padding:40px;color:#c62828;">
                    <i class="bi bi-wifi-off"></i> Error de conexión.<br>
                    <small>${error.message}</small>
                </div>`;
            }
        });
}

/* ══════════════════════════════════
   PERÍODO
══════════════════════════════════ */
function setPeriodo(periodo, ev) {
    document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('activo'));
    if (ev) ev.target.classList.add('activo');

    const hoy = new Date();
    const fmt = d => d.toISOString().split('T')[0];

    if (periodo === 'hoy') {
        document.getElementById('filtroDesde').value = fmt(hoy);
        document.getElementById('filtroHasta').value = fmt(hoy);
    } else if (periodo === 'semana') {
        const lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - hoy.getDay() + 1);
        document.getElementById('filtroDesde').value = fmt(lunes);
        document.getElementById('filtroHasta').value = fmt(hoy);
    } else if (periodo === 'mes') {
        document.getElementById('filtroDesde').value =
            `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-01`;
        document.getElementById('filtroHasta').value = fmt(hoy);
    } else {
        document.getElementById('filtroDesde').value = '';
        document.getElementById('filtroHasta').value = '';
    }

    filtrarMisVentas();
}

/* ══════════════════════════════════
   VALIDACIÓN EN TIEMPO REAL
══════════════════════════════════ */
function iniciarValidacionEnTiempoReal() {
    document.getElementById('edit_nombre')?.addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '').slice(0, 50);
        setError('err_edit_nombre', '');
    });

    document.getElementById('edit_apellido')?.addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '').slice(0, 50);
        setError('err_edit_apellido', '');
    });

    document.getElementById('edit_telefono')?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
        if (this.value.length > 0 && this.value.length < 8) {
            setError('err_edit_telefono', `${this.value.length} / 8 dígitos`);
        } else {
            setError('err_edit_telefono', '');
        }
    });

    const passErrores = {
        pass_actual: 'err_pass_actual',
        pass_nueva: 'err_pass_nueva',
        pass_confirmar: 'err_pass_confirmar'
    };
    Object.entries(passErrores).forEach(([inputId, errId]) => {
        document.getElementById(inputId)?.addEventListener('input', () => setError(errId, ''));
    });
}

/* ══════════════════════════════════
   TOAST
══════════════════════════════════ */
function mostrarToast(mensaje, tipo = 'exito') {
    document.getElementById('perfilToast')?.remove();

    if (!document.getElementById('toastStyle')) {
        const s = document.createElement('style');
        s.id = 'toastStyle';
        s.textContent = `
            @keyframes toastIn { from { opacity:0; transform:translateY(-12px); } to { opacity:1; transform:translateY(0); } }
            @keyframes toastOut { from { opacity:1; transform:translateY(0); } to { opacity:0; transform:translateY(-12px); } }
        `;
        document.head.appendChild(s);
    }

    const colores = { exito: '#70ab32', error: '#c62828' };

    const toast = document.createElement('div');
    toast.id = 'perfilToast';
    toast.style.cssText = `
        position: fixed; top: 24px; right: 24px; z-index: 99999;
        background: ${colores[tipo] || colores.exito}; color: #ffffff;
        padding: 14px 22px; border-radius: 8px; font-size: 14px;
        font-weight: 500; box-shadow: 0 4px 20px rgba(0,0,0,0.22);
        animation: toastIn 0.3s ease forwards; max-width: 340px;
        line-height: 1.5; font-family: 'Segoe UI', sans-serif;
        pointer-events: none;
    `;
    toast.textContent = mensaje;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toastOut 0.35s ease forwards';
        setTimeout(() => toast.remove(), 350);
    }, 3500);
}

/* ══════════════════════════════════
   TOGGLE CONTRASEÑA
══════════════════════════════════ */
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        if (icon) icon.className = 'bi bi-eye';
    }
}

/* ══════════════════════════════════
   MODALES
══════════════════════════════════ */
function abrirModal(id) { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

/* ══════════════════════════════════
   HELPERS
══════════════════════════════════ */
function setError(id, msg) {
    const el = document.getElementById(id);
    if (el) el.textContent = msg;
}

function limpiarErrores(ids) {
    ids.forEach(id => setError(id, ''));
}

function formatearFecha(f) {
    if (!f) return '—';
    const d = new Date(f);
    return isNaN(d) ? f : d.toLocaleString('es-SV');
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function badgeMetodo(m) {
    const map = {
        efectivo: '<span class="badge-metodo metodo-efectivo">Efectivo</span>',
        tarjeta: '<span class="badge-metodo metodo-tarjeta">Tarjeta</span>',
        transferencia: '<span class="badge-metodo metodo-transferencia">Transferencia</span>'
    };
    return map[m] || `<span class="badge-metodo">${esc(m)}</span>`;
}

function badgeEstado(e) {
    const map = {
        completada: '<span class="badge-completada">Completada</span>',
        anulada: '<span class="badge-anulada">Anulada</span>',
        pendiente: '<span class="badge-pendiente">Pendiente</span>'
    };
    return map[e] || esc(e);
}