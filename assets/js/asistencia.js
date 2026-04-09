// Módulo Asistencia - DNS Pharmacy
document.addEventListener('DOMContentLoaded', function() {
    cargarReporte();
    cargarUsuarios();
    cargarHistorial();

    document.getElementById('fechaDesde').addEventListener('change', cargarHistorial);
    document.getElementById('fechaHasta').addEventListener('change', cargarHistorial);
    document.getElementById('filtroUsuario').addEventListener('change', cargarHistorial);
});

function cargarReporte() {
    fetch('../controllers/AsistenciaController.php?accion=reporte')
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                document.getElementById('statTotal').innerText = data.datos.total_usuarios;
                document.getElementById('statPresentes').innerText = data.datos.presentes_hoy;
                document.getElementById('statAusentes').innerText = data.datos.ausentes_hoy;
                document.getElementById('statPromedio').innerText = data.datos.promedio_horas_mes + ' h';
            }
        })
        .catch(err => console.error('Error cargando reporte:', err));
}

function cargarUsuarios() {
    fetch('../controllers/AsistenciaController.php?accion=listar_usuarios')
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                const selectFiltro = document.getElementById('filtroUsuario');
                selectFiltro.innerHTML = '<option value="">Todos los usuarios</option>';
                const selectGenerar = document.getElementById('selectUsuarioQR');
                selectGenerar.innerHTML = '<option value="">Seleccionar usuario</option>';

                data.datos.forEach(user => {
                    const optionFiltro = document.createElement('option');
                    optionFiltro.value = user.id_usuario;
                    optionFiltro.textContent = `${user.nombre} ${user.apellido} (${user.correo})`;
                    selectFiltro.appendChild(optionFiltro);

                    const optionGenerar = document.createElement('option');
                    optionGenerar.value = user.id_usuario;
                    optionGenerar.textContent = `${user.nombre} ${user.apellido} (${user.correo})`;
                    selectGenerar.appendChild(optionGenerar);
                });
            }
        })
        .catch(err => console.error('Error cargando usuarios:', err));
}

function cargarHistorial() {
    const desde = document.getElementById('fechaDesde').value;
    const hasta = document.getElementById('fechaHasta').value;
    const usuario = document.getElementById('filtroUsuario').value;

    let url = '../controllers/AsistenciaController.php?accion=historial';
    if (desde) url += `&desde=${desde}`;
    if (hasta) url += `&hasta=${hasta}`;
    if (usuario) url += `&usuario=${usuario}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('cuerpoTabla');
            if (data.ok && data.datos.length > 0) {
                tbody.innerHTML = '';
                data.datos.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                          <td>${row.fecha}</td>
                          <td>${row.nombre} ${row.apellido}</td>
                          <td><span class="asistencia-badge-entrada">${row.hora_entrada}</span></td>
                          <td>${row.hora_salida ? `<span class="asistencia-badge-salida">${row.hora_salida}</span>` : '—'}</td>
                          <td>${row.origen === 'qr' ? '<i class="bi bi-upc-scan"></i> QR' : '<i class="bi bi-pencil"></i> Manual'}</td>
                          <td><button class="btn-qr" onclick="generarQR(${row.id_usuario}, '${escapeHtml(row.nombre)} ${escapeHtml(row.apellido)}')"><i class="bi bi-qr-code"></i> QR</button></td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="tabla-vacia">No hay registros</td></tr>';
            }
        })
        .catch(err => console.error('Error cargando historial:', err));
}

function abrirModalGenerarQR() {
    const select = document.getElementById('selectUsuarioQR');
    if (select.options.length <= 1) {
        cargarUsuarios();
    }
    abrirModal('modalGenerarQR');
}

function generarQRDesdeSelect() {
    const select = document.getElementById('selectUsuarioQR');
    const userId = select.value;
    if (!userId) {
        alert('Por favor selecciona un usuario');
        return;
    }
    const selectedOption = select.options[select.selectedIndex];
    const nombre = selectedOption.textContent.split(' (')[0];
    generarQR(userId, nombre);
    cerrarModal('modalGenerarQR');
}

function generarQR(userId, nombre) {
    fetch(`../controllers/AsistenciaController.php?accion=generar_qr&id_usuario=${userId}`)
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                document.getElementById('qrUsuarioNombre').innerText = nombre;
                document.getElementById('qrImage').src = data.qr;
                document.getElementById('qrUrl').innerText = data.url;
                const downloadLink = document.getElementById('qrDownloadLink');
                downloadLink.href = data.qr;
                downloadLink.download = `qr_${userId}.png`;
                abrirModal('modalQR');
            } else {
                alert(data.mensaje);
            }
        })
        .catch(err => {
            console.error('Error generando QR:', err);
            alert('Error al generar el código QR.');
        });
}

function abrirModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});