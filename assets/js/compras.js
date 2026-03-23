let compras = [];
let detalleCompra = [];
let compIdAnular = null;

const proveedores = {
    1: 'Distribuidora Médica S.A.',
    2: 'Pharma Supply Co.',
    3: 'MedTotal S.A. de C.V.'
};

const catalogoProductos = [
    { id_producto: 1, nombre: 'Ibuprofeno 400mg',     codigo_barras: '7891234', precio_compra: 3.20, stock_actual: 142 },
    { id_producto: 2, nombre: 'Amoxicilina 500mg',    codigo_barras: '6540987', precio_compra: 4.50, stock_actual: 14  },
    { id_producto: 3, nombre: 'Vitamina C 1000mg',    codigo_barras: '3320014', precio_compra: 1.80, stock_actual: 305 },
    { id_producto: 4, nombre: 'Hidrocortisona Crema', codigo_barras: '9981122', precio_compra: 2.10, stock_actual: 3   },
    { id_producto: 5, nombre: 'Metformina 850mg',     codigo_barras: '4456789', precio_compra: 5.60, stock_actual: 88  }
];

document.addEventListener('DOMContentLoaded', () => {
    cargarCompras();
    document.getElementById('formCompra').addEventListener('submit', guardarCompra);
    document.getElementById('btnConfirmarAnular').addEventListener('click', anularCompra);
    document.getElementById('comp_fecha_compra').value = hoy();
});

function cargarCompras() {
    compras = [
        {
            id_compra:        1,
            id_proveedor:     1,
            id_usuario:       1,
            numero_documento: 'TK-00001',
            tipo_documento:   'Ticket',
            fecha_compra:     '2026-03-15',
            subtotal:         150.00,
            impuesto:         19.50,
            total:            169.50,
            observaciones:    '',
            estado:           'completada',
            created_at:       '2026-03-15 10:00:00',
            updated_at:       '2026-03-15 10:00:00',
            detalle: [
                { id_detalle_compra: 1, id_compra: 1, id_producto: 1, nombre: 'Ibuprofeno 400mg',  cantidad: 20, costo_unitario: 3.20, subtotal: 64.00, created_at: '2026-03-15 10:00:00', updated_at: '2026-03-15 10:00:00' },
                { id_detalle_compra: 2, id_compra: 1, id_producto: 2, nombre: 'Amoxicilina 500mg', cantidad: 10, costo_unitario: 4.50, subtotal: 45.00, created_at: '2026-03-15 10:00:00', updated_at: '2026-03-15 10:00:00' },
                { id_detalle_compra: 3, id_compra: 1, id_producto: 3, nombre: 'Vitamina C 1000mg', cantidad: 25, costo_unitario: 1.64, subtotal: 41.00, created_at: '2026-03-15 10:00:00', updated_at: '2026-03-15 10:00:00' }
            ]
        },
        {
            id_compra:        2,
            id_proveedor:     2,
            id_usuario:       1,
            numero_documento: 'FAC-00001',
            tipo_documento:   'Factura',
            fecha_compra:     '2026-03-18',
            subtotal:         80.00,
            impuesto:         0,
            total:            80.00,
            observaciones:    'Entrega parcial',
            estado:           'pendiente',
            created_at:       '2026-03-18 09:00:00',
            updated_at:       '2026-03-18 09:00:00',
            detalle: [
                { id_detalle_compra: 4, id_compra: 2, id_producto: 5, nombre: 'Metformina 850mg',       cantidad: 10, costo_unitario: 5.60, subtotal: 56.00, created_at: '2026-03-18 09:00:00', updated_at: '2026-03-18 09:00:00' },
                { id_detalle_compra: 5, id_compra: 2, id_producto: 4, nombre: 'Hidrocortisona Crema 1%', cantidad: 10, costo_unitario: 2.40, subtotal: 24.00, created_at: '2026-03-18 09:00:00', updated_at: '2026-03-18 09:00:00' }
            ]
        }
    ];
    renderizarTabla(compras);
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="tabla-vacia">No hay compras registradas.</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map((c, i) => `
        <tr>
            <td>${i + 1}</td>
            <td><span class="td-ticket">${esc(c.numero_documento)}</span></td>
            <td><span class="badge-tipo">${esc(c.tipo_documento)}</span></td>
            <td>${esc(proveedores[c.id_proveedor] || '—')}</td>
            <td>${formatearFecha(c.fecha_compra)}</td>
            <td class="td-monto">$${c.subtotal.toFixed(2)}</td>
            <td class="td-monto">$${c.impuesto.toFixed(2)}</td>
            <td class="td-total">$${c.total.toFixed(2)}</td>
            <td>${badgeEstado(c.estado)}</td>
            <td>
                <button class="btn-accion btn-ver" title="Ver detalle" onclick="verCompra(${c.id_compra})"><i class="bi bi-eye"></i></button>
                ${c.estado !== 'anulada' ? `<button class="btn-accion btn-anular" title="Anular" onclick="confirmarAnular(${c.id_compra}, '${esc(c.numero_documento)}')"><i class="bi bi-x-circle"></i></button>` : ''}
            </td>
        </tr>
    `).join('');
}

function filtrarTabla() {
    const texto  = document.getElementById('buscador').value.toLowerCase().trim();
    const tipo   = document.getElementById('filtroTipo').value;
    const estado = document.getElementById('filtroEstado').value;

    const filtrado = compras.filter(c => {
        const coincideTexto  = !texto  || c.numero_documento.toLowerCase().includes(texto) || (proveedores[c.id_proveedor] || '').toLowerCase().includes(texto);
        const coincideTipo   = !tipo   || c.tipo_documento === tipo;
        const coincideEstado = !estado || c.estado === estado;
        return coincideTexto && coincideTipo && coincideEstado;
    });

    renderizarTabla(filtrado);
}

function abrirModalCompra() {
    document.getElementById('tituloModalCompra').textContent = 'Nueva Compra';
    document.getElementById('formCompra').reset();
    document.getElementById('comp_id_compra').value   = '';
    document.getElementById('comp_fecha_compra').value = hoy();
    detalleCompra = [];
    renderizarDetalle();
    recalcularTotales();
    limpiarErrores();
    abrirModal('modalCompra');
}

function buscarProductoDetalle() {
    const texto      = document.getElementById('buscarProducto').value.toLowerCase().trim();
    const contenedor = document.getElementById('sugerenciasProducto');

    if (!texto) { contenedor.style.display = 'none'; return; }

    const resultados = catalogoProductos.filter(p =>
        p.nombre.toLowerCase().includes(texto) || p.codigo_barras.includes(texto)
    );

    if (!resultados.length) { contenedor.style.display = 'none'; return; }

    contenedor.innerHTML = resultados.map(p => `
        <div class="sugerencia-item" onclick="agregarProductoDetalle(${p.id_producto})">
            <span>${esc(p.nombre)} <small style="color:#aaa">${esc(p.codigo_barras)}</small></span>
            <span class="sugerencia-stock">Stock: ${p.stock_actual}</span>
        </div>
    `).join('');
    contenedor.style.display = 'block';
}

function agregarProductoDetalle(id) {
    const prod = catalogoProductos.find(p => p.id_producto === id);
    if (!prod) return;

    const existe = detalleCompra.find(d => d.id_producto === id);
    if (existe) {
        existe.cantidad++;
        existe.subtotal = existe.cantidad * existe.costo_unitario;
    } else {
        detalleCompra.push({
            id_detalle_compra: null,
            id_compra:         null,
            id_producto:       prod.id_producto,
            nombre:            prod.nombre,
            cantidad:          1,
            costo_unitario:    prod.precio_compra,
            subtotal:          prod.precio_compra,
            created_at:        null,
            updated_at:        null
        });
    }

    document.getElementById('buscarProducto').value = '';
    document.getElementById('sugerenciasProducto').style.display = 'none';
    renderizarDetalle();
    recalcularTotales();
}

function renderizarDetalle() {
    const tbody = document.getElementById('cuerpoDetalle');

    if (!detalleCompra.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="tabla-vacia">Busca y agrega productos arriba.</td></tr>`;
        return;
    }

    tbody.innerHTML = detalleCompra.map((d, i) => `
        <tr>
            <td>${esc(d.nombre)}</td>
            <td>
                <input type="number" class="input-detalle" style="width:80px" min="1" value="${d.cantidad}"
                    onchange="actualizarDetalle(${i}, 'cantidad', this.value)">
            </td>
            <td>
                <input type="number" class="input-detalle" style="width:100px" min="0" step="0.01" value="${d.costo_unitario.toFixed(2)}"
                    onchange="actualizarDetalle(${i}, 'costo_unitario', this.value)">
            </td>
            <td><strong>$${d.subtotal.toFixed(2)}</strong></td>
            <td>
                <button type="button" class="btn-quitar-fila" onclick="quitarDetalle(${i})">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function actualizarDetalle(idx, campo, valor) {
    detalleCompra[idx][campo] = parseFloat(valor) || 0;
    detalleCompra[idx].subtotal = detalleCompra[idx].cantidad * detalleCompra[idx].costo_unitario;
    recalcularTotales();

    const filas = document.getElementById('cuerpoDetalle').querySelectorAll('tr');
    if (filas[idx]) {
        filas[idx].querySelectorAll('td')[3].innerHTML = `<strong>$${detalleCompra[idx].subtotal.toFixed(2)}</strong>`;
    }
}

function quitarDetalle(idx) {
    detalleCompra.splice(idx, 1);
    renderizarDetalle();
    recalcularTotales();
}

function recalcularTotales() {
    const subtotal  = detalleCompra.reduce((s, d) => s + d.subtotal, 0);
    const aplicaIva = document.getElementById('comp_aplica_impuesto').checked;
    const impuesto  = aplicaIva ? subtotal * 0.13 : 0;
    const total     = subtotal + impuesto;

    document.getElementById('resSubtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('resImpuesto').textContent = `$${impuesto.toFixed(2)}`;
    document.getElementById('resTotal').textContent    = `$${total.toFixed(2)}`;

    document.getElementById('comp_subtotal').value = subtotal.toFixed(2);
    document.getElementById('comp_impuesto').value = impuesto.toFixed(2);
    document.getElementById('comp_total').value    = total.toFixed(2);
}

function verCompra(id) {
    const c = compras.find(x => x.id_compra === id);
    if (!c) return;

    document.getElementById('cuerpoVerCompra').innerHTML = `
        <div class="ver-header">
            <div>
                <div class="ver-ticket">${esc(c.numero_documento)}</div>
                <div class="ver-fecha">${formatearFecha(c.fecha_compra)}</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <span class="badge-tipo">${esc(c.tipo_documento)}</span>
                ${badgeEstado(c.estado)}
            </div>
        </div>
        <div class="ver-info-grid">
            <div class="ver-info-item">
                <label>Proveedor</label>
                <span>${esc(proveedores[c.id_proveedor] || '—')}</span>
            </div>
            <div class="ver-info-item">
                <label>Estado</label>
                <span>${badgeEstado(c.estado)}</span>
            </div>
            ${c.observaciones ? `
            <div class="ver-info-item" style="grid-column:1/-1">
                <label>Observaciones</label>
                <span>${esc(c.observaciones)}</span>
            </div>` : ''}
            <div class="ver-info-item">
                <label>Creado</label>
                <span>${formatearFecha(c.created_at)}</span>
            </div>
            <div class="ver-info-item">
                <label>Actualizado</label>
                <span>${formatearFecha(c.updated_at)}</span>
            </div>
        </div>
        <div class="form-seccion">Detalle de productos</div>
        <table class="tabla-detalle">
            <thead>
                <tr><th>Producto</th><th>Cantidad</th><th>Costo unit.</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                ${c.detalle.map(d => `
                    <tr>
                        <td>${esc(d.nombre)}</td>
                        <td>${d.cantidad}</td>
                        <td>$${d.costo_unitario.toFixed(2)}</td>
                        <td><strong>$${d.subtotal.toFixed(2)}</strong></td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div class="totales-grid" style="margin-top:14px">
            <div class="total-row"><span>Subtotal</span><span>$${c.subtotal.toFixed(2)}</span></div>
            <div class="total-row"><span>Impuesto</span><span>$${c.impuesto.toFixed(2)}</span></div>
            <div class="total-row total-final"><span>Total</span><span>$${c.total.toFixed(2)}</span></div>
        </div>
    `;
    abrirModal('modalVerCompra');
}

function guardarCompra(e) {
    e.preventDefault();
    if (!validarFormulario()) return;

    const ahora = new Date().toISOString().replace('T', ' ').split('.')[0];
    const esNueva = !document.getElementById('comp_id_compra').value;

    document.getElementById('comp_id_usuario').value  = 1;
    document.getElementById('comp_updated_at').value  = ahora;
    if (esNueva) document.getElementById('comp_created_at').value = ahora;

    recalcularTotales();

    const datos = {
        id_compra:        document.getElementById('comp_id_compra').value || null,
        id_proveedor:     parseInt(document.getElementById('comp_id_proveedor').value),
        id_usuario:       1,
        numero_documento: document.getElementById('comp_numero_documento').value.trim(),
        tipo_documento:   document.getElementById('comp_tipo_documento').value,
        fecha_compra:     document.getElementById('comp_fecha_compra').value,
        subtotal:         parseFloat(document.getElementById('comp_subtotal').value),
        impuesto:         parseFloat(document.getElementById('comp_impuesto').value),
        total:            parseFloat(document.getElementById('comp_total').value),
        observaciones:    document.getElementById('comp_observaciones').value.trim(),
        estado:           document.getElementById('comp_estado').value,
        created_at:       esNueva ? ahora : '',
        updated_at:       ahora,
        detalle:          detalleCompra.map(d => ({
            id_detalle_compra: d.id_detalle_compra || null,
            id_compra:         d.id_compra || null,
            id_producto:       d.id_producto,
            cantidad:          d.cantidad,
            costo_unitario:    d.costo_unitario,
            subtotal:          d.subtotal,
            created_at:        ahora,
            updated_at:        ahora
        }))
    };

    if (datos.id_compra) {
        const idx = compras.findIndex(x => x.id_compra == datos.id_compra);
        if (idx !== -1) compras[idx] = { ...compras[idx], ...datos };
    } else {
        datos.id_compra = Date.now();
        compras.push(datos);
    }

    renderizarTabla(compras);
    cerrarModal('modalCompra');
}

function confirmarAnular(id, numero) {
    compIdAnular = id;
    document.getElementById('ticketAnular').textContent = numero;
    abrirModal('modalAnular');
}

function anularCompra() {
    if (!compIdAnular) return;
    const idx = compras.findIndex(x => x.id_compra === compIdAnular);
    if (idx !== -1) {
        compras[idx].estado     = 'anulada';
        compras[idx].updated_at = new Date().toISOString().replace('T', ' ').split('.')[0];
    }
    compIdAnular = null;
    renderizarTabla(compras);
    cerrarModal('modalAnular');
}

function validarFormulario() {
    let ok = true;
    limpiarErrores();

    if (!document.getElementById('comp_id_proveedor').value) {
        document.getElementById('err_proveedor').textContent = 'Selecciona un proveedor.';
        ok = false;
    }
    if (!document.getElementById('comp_numero_documento').value.trim()) {
        document.getElementById('err_numero').textContent = 'El número de documento es obligatorio.';
        ok = false;
    }
    if (!document.getElementById('comp_tipo_documento').value) {
        document.getElementById('err_tipo').textContent = 'Selecciona el tipo de documento.';
        ok = false;
    }
    if (!document.getElementById('comp_fecha_compra').value) {
        document.getElementById('err_fecha').textContent = 'La fecha es obligatoria.';
        ok = false;
    }
    if (!detalleCompra.length) {
        alert('Debes agregar al menos un producto.');
        ok = false;
    }

    return ok;
}

function limpiarErrores() {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

function abrirModal(id)  { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.detalle-buscador-wrap')) {
        document.getElementById('sugerenciasProducto').style.display = 'none';
    }
});

function hoy() { return new Date().toISOString().split('T')[0]; }

function formatearFecha(fecha) {
    if (!fecha) return '—';
    const d = new Date(fecha.includes('T') ? fecha : fecha + 'T00:00:00');
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
    const map = {
        completada: `<span class="badge-completada">Completada</span>`,
        pendiente:  `<span class="badge-pendiente">Pendiente</span>`,
        anulada:    `<span class="badge-anulada">Anulada</span>`
    };
    return map[estado] || estado;
}