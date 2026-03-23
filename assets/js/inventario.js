let productos = [];
let productoAjusteId = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarInventario();
});

function cargarInventario() {
    productos = [
        { id_producto: 1, codigo_barras: '7891234', nombre: 'Ibuprofeno 400mg',     descripcion: 'Tabletas × 30',  categoria: 'Analgésicos',    stock_actual: 142, stock_minimo: 20, unidad_medida: 'caja',   precio_venta: 5.80  },
        { id_producto: 2, codigo_barras: '6540987', nombre: 'Amoxicilina 500mg',    descripcion: 'Cápsulas × 21',  categoria: 'Antibióticos',   stock_actual: 14,  stock_minimo: 20, unidad_medida: 'caja',   precio_venta: 9.25  },
        { id_producto: 3, codigo_barras: '3320014', nombre: 'Vitamina C 1000mg',    descripcion: 'Efervescentes',  categoria: 'Vitaminas',      stock_actual: 305, stock_minimo: 30, unidad_medida: 'caja',   precio_venta: 3.50  },
        { id_producto: 4, codigo_barras: '9981122', nombre: 'Hidrocortisona Crema', descripcion: 'Tubo 30g',       categoria: 'Dermatológicos', stock_actual: 0,   stock_minimo: 10, unidad_medida: 'unidad', precio_venta: 4.90  },
        { id_producto: 5, codigo_barras: '4456789', nombre: 'Metformina 850mg',     descripcion: 'Tabletas × 60',  categoria: 'Antidiabéticos', stock_actual: 88,  stock_minimo: 15, unidad_medida: 'caja',   precio_venta: 11.00 }
    ];

    actualizarStats();
    renderizarTabla(productos);
}

function actualizarStats() {
    const total    = productos.length;
    const agotados = productos.filter(p => p.stock_actual === 0).length;
    const bajos    = productos.filter(p => p.stock_actual > 0 && p.stock_actual <= p.stock_minimo).length;
    const ok       = total - agotados - bajos;

    document.getElementById('statTotal').textContent   = total;
    document.getElementById('statOk').textContent      = ok;
    document.getElementById('statBajo').textContent    = bajos;
    document.getElementById('statAgotado').textContent = agotados;
}

function renderizarTabla(lista) {
    const tbody = document.getElementById('cuerpoTabla');

    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="tabla-vacia">No hay productos registrados.</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map((p, i) => {
        const estadoStock = p.stock_actual === 0 ? 'agotado' : p.stock_actual <= p.stock_minimo ? 'bajo' : 'ok';
        const pct = p.stock_minimo > 0 ? Math.min(100, Math.round((p.stock_actual / (p.stock_minimo * 3)) * 100)) : 100;

        return `
        <tr>
            <td>${i + 1}</td>
            <td><span class="td-codigo">${esc(p.codigo_barras)}</span></td>
            <td>
                <div class="td-producto">
                    <strong>${esc(p.nombre)}</strong>
                    <small>${esc(p.descripcion)}</small>
                </div>
            </td>
            <td><span class="cat-badge">${esc(p.categoria)}</span></td>
            <td>
                <div class="stock-wrap">
                    <span class="stock-num stock-${estadoStock}">${p.stock_actual}</span>
                    <div class="stock-bar-bg">
                        <div class="stock-bar-fill bar-${estadoStock}" style="width:${pct}%"></div>
                    </div>
                </div>
            </td>
            <td>${p.stock_minimo}</td>
            <td>${esc(p.unidad_medida)}</td>
            <td>$${p.precio_venta.toFixed(2)}</td>
            <td>${badgeStock(estadoStock)}</td>
            <td>
                <button class="btn-accion btn-movimientos" title="Ver movimientos" onclick="verMovimientos(${p.id_producto})"><i class="bi bi-arrow-left-right"></i></button>
                <button class="btn-accion btn-ajuste" title="Ajuste de stock" onclick="abrirAjuste(${p.id_producto})"><i class="bi bi-pencil-square"></i></button>
            </td>
        </tr>`;
    }).join('');
}

function filtrarTabla() {
    const texto     = document.getElementById('buscador').value.toLowerCase().trim();
    const categoria = document.getElementById('filtroCategoria').value;
    const stock     = document.getElementById('filtroStock').value;

    const filtrado = productos.filter(p => {
        const estadoStock       = p.stock_actual === 0 ? 'agotado' : p.stock_actual <= p.stock_minimo ? 'bajo' : 'normal';
        const coincideTexto     = !texto     || p.nombre.toLowerCase().includes(texto) || p.codigo_barras.includes(texto);
        const coincideCategoria = !categoria || p.categoria === categoria;
        const coincideStock     = !stock     || estadoStock === stock;
        return coincideTexto && coincideCategoria && coincideStock;
    });

    renderizarTabla(filtrado);
}

function verMovimientos(id) {
    const p = productos.find(x => x.id_producto === id);
    if (!p) return;

    document.getElementById('tituloModalMov').textContent = `Movimientos — ${p.nombre}`;

    const movimientos = [
        { id_movimiento: 1, id_producto: id, id_lote: null, id_usuario: 1, tipo_movimiento: 'entrada', referencia: 'TK-00001', cantidad: 50,  stock_anterior: 92, stock_nuevo: 142, motivo: 'Compra TK-00001',         fecha_movimiento: '2026-03-15 10:00:00', created_at: '2026-03-15 10:00:00', updated_at: '2026-03-15 10:00:00' },
        { id_movimiento: 2, id_producto: id, id_lote: null, id_usuario: 1, tipo_movimiento: 'salida',  referencia: 'VTA-0042', cantidad: 5,   stock_anterior: 97, stock_nuevo: 92,  motivo: 'Venta #0042',             fecha_movimiento: '2026-03-14 14:30:00', created_at: '2026-03-14 14:30:00', updated_at: '2026-03-14 14:30:00' },
        { id_movimiento: 3, id_producto: id, id_lote: null, id_usuario: 1, tipo_movimiento: 'ajuste',  referencia: null,       cantidad: 2,   stock_anterior: 95, stock_nuevo: 97,  motivo: 'Corrección de inventario', fecha_movimiento: '2026-03-10 09:00:00', created_at: '2026-03-10 09:00:00', updated_at: '2026-03-10 09:00:00' }
    ];

    document.getElementById('cuerpoMovimientos').innerHTML = `
        <table class="tabla-movimientos">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Cantidad</th>
                    <th>Stock anterior</th>
                    <th>Stock nuevo</th>
                    <th>Motivo</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                ${movimientos.map(m => `
                    <tr>
                        <td>${badgeMovimiento(m.tipo_movimiento)}</td>
                        <td>${m.referencia ? esc(m.referencia) : '<span style="color:#bbb">—</span>'}</td>
                        <td><strong>${m.tipo_movimiento === 'salida' ? '-' : '+'}${m.cantidad}</strong></td>
                        <td>${m.stock_anterior}</td>
                        <td>${m.stock_nuevo}</td>
                        <td>${esc(m.motivo)}</td>
                        <td>${formatearFecha(m.fecha_movimiento)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    abrirModal('modalMovimientos');
}

function abrirAjuste(id) {
    const p = productos.find(x => x.id_producto === id);
    if (!p) return;
    productoAjusteId = id;

    document.getElementById('ajuste_id_producto').value  = p.id_producto;
    document.getElementById('ajuste_id_lote').value      = '';
    document.getElementById('ajuste_id_usuario').value   = 1;
    document.getElementById('ajuste_tipo').value         = 'entrada';
    document.getElementById('ajuste_referencia').value   = '';
    document.getElementById('ajuste_cantidad').value     = '';
    document.getElementById('ajuste_motivo').value       = '';
    document.getElementById('ajuste_stock_anterior').value = p.stock_actual;
    document.getElementById('ajuste_stock_nuevo').value    = p.stock_actual;
    document.getElementById('ajuste_fecha_movimiento').value = '';
    document.getElementById('err_ajuste_cantidad').textContent = '';
    document.getElementById('ajusteNombreProducto').textContent = `${p.nombre} — Stock actual: ${p.stock_actual}`;

    abrirModal('modalAjuste');
}

function guardarAjuste() {
    const cantidad = parseInt(document.getElementById('ajuste_cantidad').value);
    const tipo     = document.getElementById('ajuste_tipo').value;
    const motivo   = document.getElementById('ajuste_motivo').value.trim();

    if (!cantidad || cantidad < 1) {
        document.getElementById('err_ajuste_cantidad').textContent = 'Ingresa una cantidad válida.';
        return;
    }

    const idx = productos.findIndex(x => x.id_producto === productoAjusteId);
    if (idx === -1) return;

    const stockAnterior = productos[idx].stock_actual;
    let   stockNuevo    = stockAnterior;

    if (tipo === 'entrada') {
        stockNuevo = stockAnterior + cantidad;
    } else if (tipo === 'salida') {
        stockNuevo = Math.max(0, stockAnterior - cantidad);
    } else {
        stockNuevo = cantidad;
    }

    const ahora = new Date().toISOString().replace('T', ' ').split('.')[0];

    document.getElementById('ajuste_stock_anterior').value    = stockAnterior;
    document.getElementById('ajuste_stock_nuevo').value       = stockNuevo;
    document.getElementById('ajuste_fecha_movimiento').value  = ahora;

    const movimiento = {
        id_movimiento:    null,
        id_producto:      productoAjusteId,
        id_lote:          document.getElementById('ajuste_id_lote').value || null,
        id_usuario:       parseInt(document.getElementById('ajuste_id_usuario').value),
        tipo_movimiento:  tipo,
        referencia:       document.getElementById('ajuste_referencia').value.trim() || null,
        cantidad:         cantidad,
        stock_anterior:   stockAnterior,
        stock_nuevo:      stockNuevo,
        motivo:           motivo || 'Ajuste manual',
        fecha_movimiento: ahora,
        created_at:       ahora,
        updated_at:       ahora
    };

    console.log('Movimiento a enviar al backend:', movimiento);

    productos[idx].stock_actual = stockNuevo;
    actualizarStats();
    renderizarTabla(productos);
    cerrarModal('modalAjuste');
}

function abrirModal(id)  { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal(this.id);
    });
});

function badgeStock(estado) {
    const map = {
        ok:      `<span class="badge-stock-ok">Normal</span>`,
        bajo:    `<span class="badge-stock-bajo">Stock bajo</span>`,
        agotado: `<span class="badge-stock-agotado">Agotado</span>`
    };
    return map[estado] || estado;
}

function badgeMovimiento(tipo) {
    const map = {
        entrada: `<span class="badge-entrada">Entrada</span>`,
        salida:  `<span class="badge-salida">Salida</span>`,
        ajuste:  `<span class="badge-ajuste">Ajuste</span>`
    };
    return map[tipo] || tipo;
}

function formatearFecha(fecha) {
    if (!fecha) return '—';
    const d = new Date(fecha);
    if (isNaN(d)) return fecha;
    return d.toLocaleDateString('es-SV', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}