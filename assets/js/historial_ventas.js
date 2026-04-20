// assets/js/historial_ventas.js
let ventasData = [];

$(document).ready(function() {
    cargarVentas();
});

function cargarVentas() {
    const desde = $('#filtroDesde').val();
    const hasta = $('#filtroHasta').val();
    
    let url = '/DNS_Pharmacy/controllers/VentaController.php';
    let params = [];
    
    if (desde) params.push(`desde=${desde}`);
    if (hasta) params.push(`hasta=${hasta}`);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                ventasData = response.data;
                actualizarTabla(ventasData);
                actualizarEstadisticas(response.totales);
            } else {
                console.error('Error:', response.message);
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error de conexión con el servidor');
        }
    });
}

function actualizarTabla(ventas) {
    const tbody = $('#cuerpoTabla');
    tbody.empty();
    
    if (ventas.length === 0) {
        tbody.html('<tr><td colspan="10" class="text-center">No hay ventas registradas</td></tr>');
        return;
    }
    
    ventas.forEach((venta, index) => {
        const estadoClass = venta.estado === 'completada' ? 'estado-completada' : 
                           (venta.estado === 'anulada' ? 'estado-anulada' : 'estado-pendiente');
        
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${venta.numero_ticket || 'N/A'}</td>
                <td>${venta.empleado_nombre || 'Sistema'}</td>
                <td>${formatFecha(venta.fecha_venta)}</td>
                <td>$${formatNumber(venta.subtotal)}</td>
                <td>$${formatNumber(venta.impuesto)}</td>
                <td>$${formatNumber(venta.total)}</td>
                <td>${formatearMetodoPago(venta.metodo_pago)}</td>
                <td class="${estadoClass}">${formatearEstado(venta.estado)}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="verDetalle(${venta.id_venta})">
                        <i class="bi bi-eye"></i> Ver
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function actualizarEstadisticas(totales) {
    $('#statTickets').text(totales.tickets);
    $('#statTotal').text('$' + formatNumber(totales.total));
}

function formatFecha(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatNumber(numero) {
    return parseFloat(numero).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatearMetodoPago(metodo) {
    const metodos = {
        'efectivo': 'Efectivo',
        'tarjeta': 'Tarjeta',
        'transferencia': 'Transferencia'
    };
    return metodos[metodo] || metodo || 'Efectivo';
}

function formatearEstado(estado) {
    const estados = {
        'completada': 'Completada',
        'anulada': 'Anulada',
        'pendiente': 'Pendiente'
    };
    return estados[estado] || estado || 'Completada';
}

function filtrarTodo() {
    cargarVentas();
}

function setPeriodo(periodo) {
    const hoy = new Date();
    let desde = new Date();
    
    switch(periodo) {
        case 'hoy':
            desde = hoy;
            break;
        case 'semana':
            desde.setDate(hoy.getDate() - 7);
            break;
        case 'mes':
            desde.setMonth(hoy.getMonth() - 1);
            break;
        case 'todo':
            $('#filtroDesde').val('');
            $('#filtroHasta').val('');
            cargarVentas();
            return;
    }
    
    $('#filtroDesde').val(formatDateForInput(desde));
    $('#filtroHasta').val(formatDateForInput(hoy));
    cargarVentas();
}

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

function verDetalle(idVenta) {
    $.ajax({
        url: `/DNS_Pharmacy/controllers/VentaController.php?action=detalle&id=${idVenta}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarDetalleModal(response.data);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error al cargar el detalle');
        }
    });
}

function mostrarDetalleModal(venta) {
    let html = `
        <div class="row">
            <div class="col-md-6">
                <p><strong><i class="bi bi-upc-scan"></i> Ticket:</strong> ${venta.numero_ticket}</p>
                <p><strong><i class="bi bi-person"></i> Empleado:</strong> ${venta.empleado_nombre}</p>
                <p><strong><i class="bi bi-calendar"></i> Fecha:</strong> ${formatFecha(venta.fecha_venta)}</p>
            </div>
            <div class="col-md-6">
                <p><strong><i class="bi bi-credit-card"></i> Método de pago:</strong> ${formatearMetodoPago(venta.metodo_pago)}</p>
                <p><strong><i class="bi bi-cash"></i> Monto recibido:</strong> $${formatNumber(venta.monto_recibido)}</p>
                <p><strong><i class="bi bi-arrow-return-left"></i> Cambio:</strong> $${formatNumber(venta.cambio)}</p>
            </div>
        </div>
        <hr>
        <h6><i class="bi bi-box-seam"></i> Productos vendidos:</h6>
        <table class="table table-sm table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    if (venta.productos && venta.productos.length > 0) {
        venta.productos.forEach(producto => {
            html += `
                <tr>
                    <td>${producto.nombre_producto} ${producto.presentacion ? '(' + producto.presentacion + ')' : ''}</td>
                    <td class="text-center">${producto.cantidad}</td>
                    <td class="text-right">$${formatNumber(producto.precio_unitario)}</td>
                    <td class="text-right">$${formatNumber(producto.subtotal)}</td>
                </tr>
            `;
        });
    } else {
        html += '<tr><td colspan="4" class="text-center">No hay productos registrados</td></tr>';
    }
    
    html += `
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                    <td class="text-right"><strong>$${formatNumber(venta.subtotal)}</strong></td>
                </tr>
                <tr class="table-active">
                    <td colspan="3" class="text-right"><strong>Impuesto (IVA):</strong></td>
                    <td class="text-right"><strong>$${formatNumber(venta.impuesto)}</strong></td>
                </tr>
                <tr class="table-success">
                    <td colspan="3" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>$${formatNumber(venta.total)}</strong></td>
                </tr>
            </tfoot>
        </table>
    `;
    
    if (venta.observaciones) {
        html += `<div class="alert alert-info mt-3">
                    <i class="bi bi-chat"></i> <strong>Observaciones:</strong> ${venta.observaciones}
                </div>`;
    }
    
    $('#detalleVentaBody').html(html);
    $('#detalleVentaModal').modal('show');
}