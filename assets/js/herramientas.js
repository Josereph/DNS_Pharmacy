/* =====================
   HERRAMIENTAS.JS - DNS Pharmacy
   ===================== */

const CTRL = '/DNS_Pharmacy/controllers/HerramientasController.php';

/* ══════════════════════════════════════════
   TABS
══════════════════════════════════════════ */
function cambiarTab(btn, tabId) {
    document.querySelectorAll('.h-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.h-tab-content').forEach(function(t) { t.classList.remove('active-tab'); });
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active-tab');
}

/* ══════════════════════════════════════════
   CSV — MODAL
══════════════════════════════════════════ */
var tipoCSV    = '';
var archivoCSV = null;

function abrirUploadCSV(tipo) {
    tipoCSV    = tipo;
    archivoCSV = null;
    var titulos = { productos:'Importar Productos', proveedores:'Importar Proveedores', usuarios:'Importar Usuarios' };
    document.getElementById('tituloCSV').innerHTML = '<i class="bi bi-file-earmark-arrow-up"></i> ' + (titulos[tipo] || 'Importar CSV');
    document.getElementById('errorCSV').textContent  = '';
    document.getElementById('archCSVSel').style.display  = 'none';
    document.getElementById('dropAreaCSV').style.display = 'flex';
    document.getElementById('btnImportarCSV').disabled   = true;
    document.getElementById('inputCSV').value            = '';
    document.getElementById('modalCSV').classList.add('activo');
    document.body.style.overflow = 'hidden';
}

function cerrarModalCSV() {
    document.getElementById('modalCSV').classList.remove('activo');
    document.body.style.overflow = '';
    archivoCSV = null;
}

function handleDropCSV(e) {
    e.preventDefault();
    document.getElementById('dropAreaCSV').classList.remove('drag-over');
    if (e.dataTransfer.files[0]) procesarArchivoCSV(e.dataTransfer.files[0]);
}

function seleccionarCSV(input) {
    if (input.files && input.files[0]) procesarArchivoCSV(input.files[0]);
}

function procesarArchivoCSV(file) {
    document.getElementById('errorCSV').textContent = '';
    if (!file.name.toLowerCase().endsWith('.csv')) {
        document.getElementById('errorCSV').textContent = 'Solo archivos .csv'; return;
    }
    if (file.size > 10 * 1024 * 1024) {
        document.getElementById('errorCSV').textContent = 'Máximo 10MB.'; return;
    }
    archivoCSV = file;
    document.getElementById('nombreCSV').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    document.getElementById('archCSVSel').style.display  = 'flex';
    document.getElementById('dropAreaCSV').style.display = 'none';
    document.getElementById('btnImportarCSV').disabled   = false;
}

function quitarCSV() {
    archivoCSV = null;
    document.getElementById('archCSVSel').style.display  = 'none';
    document.getElementById('dropAreaCSV').style.display = 'flex';
    document.getElementById('btnImportarCSV').disabled   = true;
    document.getElementById('inputCSV').value            = '';
}

function ejecutarCSV() {
    if (!archivoCSV || !tipoCSV) return;
    var btn = document.getElementById('btnImportarCSV');
    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Importando...';
    btn.disabled  = true;

    var fd = new FormData();
    fd.append('accion',  'importar_csv');
    fd.append('tipo',    tipoCSV);
    fd.append('archivo', archivoCSV);

    fetch(CTRL, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.innerHTML = '<i class="bi bi-upload"></i> Importar';
            btn.disabled  = false;
            cerrarModalCSV();
            mostrarLogCSV(res);
        })
        .catch(function() {
            btn.innerHTML = '<i class="bi bi-upload"></i> Importar';
            btn.disabled  = false;
            mostrarToast('Error de conexión.', 'error');
        });
}

function mostrarLogCSV(res) {
    var sec = document.getElementById('seccionLogCSV');
    var log = document.getElementById('logCSV');
    sec.style.display = 'block';

    var html = '<div class="log-resumen ' + (res.ok ? 'log-ok' : 'log-error') + '">'
             + '<i class="bi bi-' + (res.ok ? 'check-circle-fill' : 'x-circle-fill') + '"></i> '
             + res.mensaje + '</div>';

    if (res.ok) {
        html += '<div class="log-stats">';
        if (res.insertar  > 0) html += '<div class="log-stat insertar"><i class="bi bi-plus-circle"></i> ' + res.insertar + ' nuevos</div>';
        if (res.actualizar > 0) html += '<div class="log-stat actualizar"><i class="bi bi-pencil-square"></i> ' + res.actualizar + ' actualizados</div>';
        html += '</div>';
    }
    if (res.errores && res.errores.length > 0) {
        html += '<div class="log-errores"><div class="log-errores-titulo"><i class="bi bi-exclamation-triangle"></i> Errores (' + res.errores.length + ')</div>'
              + res.errores.map(function(e) { return '<div class="log-error-item">' + e + '</div>'; }).join('') + '</div>';
    }
    log.innerHTML = html;
    sec.scrollIntoView({ behavior:'smooth' });
}

/* ══════════════════════════════════════════
   SQL — IMPORTAR
══════════════════════════════════════════ */
var archivoSQL = null;
var modoSQL    = 'restaurar';

function cambiarModoSQL(radio) {
    modoSQL = radio.value;
    document.getElementById('opcionRestaurar').classList.toggle('sql-opcion-active', modoSQL === 'restaurar');
    document.getElementById('opcionMerge').classList.toggle('sql-opcion-active',     modoSQL === 'merge');
}

function handleDropSQL(e) {
    e.preventDefault();
    document.getElementById('dropAreaSQL').classList.remove('drag-over');
    if (e.dataTransfer.files[0]) procesarArchivoSQL(e.dataTransfer.files[0]);
}

function seleccionarSQL(input) {
    if (input.files && input.files[0]) procesarArchivoSQL(input.files[0]);
}

function procesarArchivoSQL(file) {
    document.getElementById('errorSQL').textContent = '';
    if (!file.name.toLowerCase().endsWith('.sql')) {
        document.getElementById('errorSQL').textContent = 'Solo archivos .sql'; return;
    }
    if (file.size > 50 * 1024 * 1024) {
        document.getElementById('errorSQL').textContent = 'Máximo 50MB.'; return;
    }
    archivoSQL = file;
    document.getElementById('nombreSQL').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    document.getElementById('archivoSQLSel').style.display = 'flex';
    document.getElementById('dropAreaSQL').style.display   = 'none';
    document.getElementById('btnEjecutarSQL').disabled     = false;
}

function quitarSQL() {
    archivoSQL = null;
    document.getElementById('archivoSQLSel').style.display = 'none';
    document.getElementById('dropAreaSQL').style.display   = 'flex';
    document.getElementById('btnEjecutarSQL').disabled     = true;
    document.getElementById('inputSQL').value              = '';
}

function ejecutarSQL() {
    if (!archivoSQL) return;

    var advertencia = modoSQL === 'restaurar'
        ? '⚠️ ATENCIÓN: Esto ejecutará TODO el SQL incluyendo DROP TABLE.\n¿Confirmas que tienes un backup y quieres continuar?'
        : '¿Confirmas que quieres insertar los datos del archivo SQL sin borrar los existentes?';

    if (!confirm(advertencia)) return;

    var btn = document.getElementById('btnEjecutarSQL');
    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Ejecutando...';
    btn.disabled  = true;

    var fd = new FormData();
    fd.append('accion',  'importar_sql');
    fd.append('modo',    modoSQL);
    fd.append('archivo', archivoSQL);

    fetch(CTRL, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.innerHTML = '<i class="bi bi-play-fill"></i> Ejecutar importación SQL';
            btn.disabled  = false;
            mostrarLogSQL(res);
        })
        .catch(function() {
            btn.innerHTML = '<i class="bi bi-play-fill"></i> Ejecutar importación SQL';
            btn.disabled  = false;
            mostrarToast('Error de conexión.', 'error');
        });
}

function mostrarLogSQL(res) {
    var sec = document.getElementById('seccionLogSQL');
    var log = document.getElementById('logSQL');
    sec.style.display = 'block';

    var html = '<div class="log-resumen ' + (res.ok ? 'log-ok' : 'log-error') + '">'
             + '<i class="bi bi-' + (res.ok ? 'check-circle-fill' : 'x-circle-fill') + '"></i> '
             + res.mensaje + '</div>';

    if (res.ejecutados > 0) {
        html += '<div class="log-stats"><div class="log-stat insertar"><i class="bi bi-terminal"></i> ' + res.ejecutados + ' sentencias ejecutadas</div></div>';
    }
    if (res.errores && res.errores.length > 0) {
        html += '<div class="log-errores"><div class="log-errores-titulo"><i class="bi bi-exclamation-triangle"></i> Errores (' + res.errores.length + ')</div>'
              + res.errores.map(function(e) { return '<div class="log-error-item">' + e + '</div>'; }).join('') + '</div>';
    }
    log.innerHTML = html;
    sec.scrollIntoView({ behavior:'smooth' });
}

/* ══════════════════════════════════════════
   BACKUP
══════════════════════════════════════════ */
function generarBackup() {
    var btn = document.getElementById('btnBackupManual');
    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Generando...';
    btn.disabled  = true;

    var link = document.createElement('a');
    link.href = CTRL + '?accion=backup';
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(function() {
        btn.innerHTML = '<i class="bi bi-download"></i> Generar y descargar backup';
        btn.disabled  = false;
        mostrarToast('Backup descargado y guardado en servidor.', 'ok');
        cargarHistorialBackups();
    }, 3000);
}

function cargarHistorialBackups() {
    var cont = document.getElementById('historialBackups');
    cont.innerHTML = '<div class="rev-loading"><i class="bi bi-arrow-repeat"></i> Cargando...</div>';

    fetch(CTRL + '?accion=historial_backups')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok || res.datos.length === 0) {
                cont.innerHTML = '<div class="backup-empty"><i class="bi bi-database-x"></i><span>No hay backups guardados aún.</span></div>';
                return;
            }
            cont.innerHTML = '<div class="backup-lista">'
                + res.datos.map(function(b) {
                    return '<div class="backup-item">'
                         + '<div class="backup-item-left">'
                         + '<i class="bi bi-file-earmark-code"></i>'
                         + '<div><div class="backup-nombre">' + b.nombre + '</div>'
                         + '<div class="backup-meta">' + b.fecha + ' · ' + b.tamanio + '</div></div>'
                         + '</div>'
                         + '<div class="backup-item-right">'
                         + '<a href="' + b.url + '" class="btn-dl-backup" download><i class="bi bi-download"></i> Descargar</a>'
                         + '<button class="btn-del-backup" onclick="eliminarBackup(\'' + b.nombre + '\')"><i class="bi bi-trash3"></i></button>'
                         + '</div></div>';
                }).join('')
                + '</div>';
        })
        .catch(function() { cont.innerHTML = '<div class="backup-empty">Error al cargar.</div>'; });
}

function eliminarBackup(nombre) {
    if (!confirm('¿Eliminar el backup "' + nombre + '"?')) return;
    var fd = new FormData();
    fd.append('accion',  'eliminar_backup');
    fd.append('archivo', nombre);
    fetch(CTRL, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) { mostrarToast(res.mensaje, res.ok?'ok':'error'); if (res.ok) cargarHistorialBackups(); });
}

/* ══════════════════════════════════════════
   UTILS
══════════════════════════════════════════ */
document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) { overlay.classList.remove('activo'); document.body.style.overflow = ''; }
    });
});

function mostrarToast(msg, tipo) {
    var t = document.getElementById('toast-h');
    if (!t) {
        t = document.createElement('div'); t.id = 'toast-h';
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:12px 22px;border-radius:8px;font-size:13px;font-weight:500;color:#fff;z-index:9999;opacity:0;transform:translateY(8px);transition:opacity .3s,transform .3s;pointer-events:none;max-width:360px;';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#c62828' : '#2e7d32';
    t.style.opacity = '1'; t.style.transform = 'translateY(0)';
    setTimeout(function() { t.style.opacity='0'; t.style.transform='translateY(8px)'; }, 4000);
}

// Init
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('opcionRestaurar').classList.add('sql-opcion-active');
});