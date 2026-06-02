<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}
if ($_SESSION['usuario_rol'] !== 'Administrador') {
    header('Location: /DNS_Pharmacy/views/Pos.php');
    exit;
}
$base_url = '/DNS_Pharmacy';
?>
<!doctype html>
<html lang="es">
<head>
    <title>Herramientas - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/slider.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/herramientas.css">
</head>
<body>

<?php include 'layouts/slider.php'; ?>

<div class="main-content">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h2 class="page-title">Herramientas del sistema</h2>
            <p class="page-subtitle">Carga masiva, importación SQL y respaldo de base de datos</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="h-tabs">
        <button class="h-tab active" onclick="cambiarTab(this,'tabCSV')">
            <i class="bi bi-file-earmark-spreadsheet"></i> Carga CSV
        </button>
        <button class="h-tab" onclick="cambiarTab(this,'tabSQL')">
            <i class="bi bi-database-up"></i> Importar SQL
        </button>
        <button class="h-tab" onclick="cambiarTab(this,'tabBackup')">
            <i class="bi bi-database-down"></i> Backup
        </button>
    </div>

    <!-- ══ TAB CSV ══ -->
    <div id="tabCSV" class="h-tab-content active-tab">
        <div class="tools-section">
            <div class="section-intro">
                <i class="bi bi-info-circle"></i>
                Descarga la plantilla de cada tipo, llénala en Excel y súbela. Los registros existentes se actualizan, los nuevos se insertan.
            </div>

            <div class="csv-cards">

                <!-- Productos -->
                <div class="csv-card">
                    <div class="csv-card-head">
                        <div class="csv-icon purple"><i class="bi bi-box-seam"></i></div>
                        <div>
                            <div class="csv-title">Productos</div>
                            <div class="csv-sub">código, nombre, categoría, precios, stock, marca...</div>
                        </div>
                    </div>
                    <div class="csv-campos">
                        <span>codigo_barras</span><span>nombre</span><span>categoria</span>
                        <span>precio_compra</span><span>precio_venta</span><span>stock_actual</span>
                        <span>stock_minimo</span><span>unidad_medida</span><span>marca</span>
                        <span>laboratorio</span><span>requiere_receta</span><span>estado</span>
                    </div>
                    <div class="csv-card-foot">
                        <a href="<?= $base_url ?>/controllers/HerramientasController.php?accion=plantilla&tipo=productos"
                           class="btn-tpl"><i class="bi bi-download"></i> Plantilla</a>
                        <button class="btn-imp" onclick="abrirUploadCSV('productos')">
                            <i class="bi bi-upload"></i> Importar
                        </button>
                    </div>
                </div>

                <!-- Proveedores -->
                <div class="csv-card">
                    <div class="csv-card-head">
                        <div class="csv-icon green"><i class="bi bi-building"></i></div>
                        <div>
                            <div class="csv-title">Proveedores</div>
                            <div class="csv-sub">nombre, contacto, teléfono, correo, NIT...</div>
                        </div>
                    </div>
                    <div class="csv-campos">
                        <span>nombre</span><span>nombre_contacto</span><span>telefono</span>
                        <span>correo</span><span>direccion</span><span>nit</span><span>estado</span>
                    </div>
                    <div class="csv-card-foot">
                        <a href="<?= $base_url ?>/controllers/HerramientasController.php?accion=plantilla&tipo=proveedores"
                           class="btn-tpl"><i class="bi bi-download"></i> Plantilla</a>
                        <button class="btn-imp" onclick="abrirUploadCSV('proveedores')">
                            <i class="bi bi-upload"></i> Importar
                        </button>
                    </div>
                </div>

                <!-- Usuarios -->
                <div class="csv-card">
                    <div class="csv-card-head">
                        <div class="csv-icon blue"><i class="bi bi-people"></i></div>
                        <div>
                            <div class="csv-title">Usuarios</div>
                            <div class="csv-sub">nombre, apellido, correo, rol, contraseña...</div>
                        </div>
                    </div>
                    <div class="csv-campos">
                        <span>nombre</span><span>apellido</span><span>correo</span>
                        <span>password</span><span>id_rol (1=Admin, 2=Empleado)</span>
                        <span>telefono</span><span>estado</span>
                    </div>
                    <div class="csv-card-foot">
                        <a href="<?= $base_url ?>/controllers/HerramientasController.php?accion=plantilla&tipo=usuarios"
                           class="btn-tpl"><i class="bi bi-download"></i> Plantilla</a>
                        <button class="btn-imp" onclick="abrirUploadCSV('usuarios')">
                            <i class="bi bi-upload"></i> Importar
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Log CSV -->
        <div class="tools-section" id="seccionLogCSV" style="display:none;">
            <div class="section-title-sm"><i class="bi bi-journal-text"></i> Resultado de importación</div>
            <div id="logCSV"></div>
        </div>
    </div>

    <!-- ══ TAB SQL ══ -->
    <div id="tabSQL" class="h-tab-content">
        <div class="tools-section">
            <div class="section-intro warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Importante:</strong> Importar un archivo SQL afecta directamente la base de datos.
                    Se recomienda generar un backup antes de continuar.
                </div>
            </div>

            <div class="sql-opciones">

                <!-- Opción A: Restaurar completo -->
                <div class="sql-opcion" id="opcionRestaurar">
                    <div class="sql-opcion-head">
                        <div class="csv-icon orange"><i class="bi bi-arrow-counterclockwise"></i></div>
                        <div>
                            <div class="csv-title">Restaurar base de datos completa</div>
                            <div class="csv-sub">Ejecuta el SQL tal como está. Ideal para restaurar un backup previo.</div>
                        </div>
                        <label class="sql-radio">
                            <input type="radio" name="modoSQL" value="restaurar" checked onchange="cambiarModoSQL(this)">
                        </label>
                    </div>
                    <div class="sql-opcion-desc">
                        <i class="bi bi-info-circle"></i>
                        Ejecuta cada sentencia del archivo SQL: <code>CREATE TABLE</code>, <code>INSERT</code>, <code>DROP TABLE</code>, etc.
                        Si el archivo tiene <code>DROP TABLE</code>, se borrarán y recrearán las tablas.
                    </div>
                </div>

                <!-- Opción B: Solo datos nuevos -->
                <div class="sql-opcion" id="opcionMerge">
                    <div class="sql-opcion-head">
                        <div class="csv-icon purple"><i class="bi bi-database-add"></i></div>
                        <div>
                            <div class="csv-title">Solo insertar datos nuevos</div>
                            <div class="csv-sub">Extrae solo los INSERT del SQL y los ejecuta sin borrar nada existente.</div>
                        </div>
                        <label class="sql-radio">
                            <input type="radio" name="modoSQL" value="merge" onchange="cambiarModoSQL(this)">
                        </label>
                    </div>
                    <div class="sql-opcion-desc">
                        <i class="bi bi-info-circle"></i>
                        Ideal para agregar datos de otro sistema sin perder los registros actuales.
                        Usa <code>INSERT IGNORE</code> para evitar duplicados.
                    </div>
                </div>

            </div>

            <!-- Drop area SQL -->
            <div class="upload-drop-area" id="dropAreaSQL"
                 ondragover="event.preventDefault(); this.classList.add('drag-over')"
                 ondragleave="this.classList.remove('drag-over')"
                 ondrop="handleDropSQL(event)"
                 onclick="document.getElementById('inputSQL').click()">
                <i class="bi bi-file-earmark-code"></i>
                <div class="drop-texto">Arrastra tu archivo .sql aquí</div>
                <div class="drop-sub">o haz clic para seleccionar</div>
                <input type="file" id="inputSQL" accept=".sql" style="display:none" onchange="seleccionarSQL(this)">
            </div>

            <div id="archivoSQLSel" style="display:none;" class="archivo-sel">
                <i class="bi bi-file-earmark-code"></i>
                <span id="nombreSQL"></span>
                <button onclick="quitarSQL()" class="btn-quitar-csv">&times;</button>
            </div>

            <span class="upload-error" id="errorSQL"></span>

            <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                <button class="btn-ejecutar-sql" id="btnEjecutarSQL" onclick="ejecutarSQL()" disabled>
                    <i class="bi bi-play-fill"></i> Ejecutar importación SQL
                </button>
            </div>
        </div>

        <!-- Log SQL -->
        <div class="tools-section" id="seccionLogSQL" style="display:none;">
            <div class="section-title-sm"><i class="bi bi-terminal"></i> Resultado SQL</div>
            <div id="logSQL"></div>
        </div>
    </div>

    <!-- ══ TAB BACKUP ══ -->
    <div id="tabBackup" class="h-tab-content">
        <div class="tools-section">

            <!-- Backup manual -->
            <div class="backup-manual-card">
                <div class="backup-manual-info">
                    <div class="csv-icon orange"><i class="bi bi-database-down"></i></div>
                    <div>
                        <div class="csv-title">Generar backup ahora</div>
                        <div class="csv-sub">
                            Exporta todas las tablas (estructura + datos) en un archivo .sql descargable.
                            También queda guardado en el servidor.
                        </div>
                    </div>
                </div>
                <button class="btn-backup-grande" id="btnBackupManual" onclick="generarBackup()">
                    <i class="bi bi-download"></i> Generar y descargar backup
                </button>
            </div>

            <!-- Historial -->
            <div class="backup-historial-header">
                <div class="section-title-sm"><i class="bi bi-clock-history"></i> Backups guardados en servidor</div>
                <button class="btn-tpl" onclick="cargarHistorialBackups()" id="btnRefreshBackups">
                    <i class="bi bi-arrow-repeat"></i> Actualizar
                </button>
            </div>

            <div id="historialBackups">
                <div class="backup-empty">
                    <i class="bi bi-database-x"></i>
                    <span>Haz clic en "Actualizar" para ver los backups guardados.</span>
                </div>
            </div>

        </div>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>


<!-- ══ MODAL CSV ══ -->
<div class="modal-overlay" id="modalCSV">
    <div class="modal-box modal-mediano">
        <div class="modal-header">
            <h5 class="modal-titulo" id="tituloCSV">
                <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV
            </h5>
            <button class="modal-cerrar" onclick="cerrarModalCSV()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="upload-instrucciones">
                <div class="upload-paso"><div class="paso-num">1</div><div>Descarga la plantilla con el botón <strong>Plantilla</strong>.</div></div>
                <div class="upload-paso"><div class="paso-num">2</div><div>Llena los datos. Guarda como <strong>.csv (UTF-8)</strong>.</div></div>
                <div class="upload-paso"><div class="paso-num">3</div><div>Registros con código/correo/NIT existente se <strong>actualizan</strong>. Los nuevos se insertan.</div></div>
            </div>

            <div class="upload-drop-area" id="dropAreaCSV"
                 ondragover="event.preventDefault(); this.classList.add('drag-over')"
                 ondragleave="this.classList.remove('drag-over')"
                 ondrop="handleDropCSV(event)"
                 onclick="document.getElementById('inputCSV').click()">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                <div class="drop-texto">Arrastra tu archivo .csv aquí</div>
                <div class="drop-sub">o haz clic para seleccionar</div>
                <input type="file" id="inputCSV" accept=".csv" style="display:none" onchange="seleccionarCSV(this)">
            </div>

            <div id="archCSVSel" style="display:none;" class="archivo-sel">
                <i class="bi bi-file-earmark-check"></i>
                <span id="nombreCSV"></span>
                <button onclick="quitarCSV()" class="btn-quitar-csv">&times;</button>
            </div>

            <span class="upload-error" id="errorCSV"></span>
        </div>
        <div class="modal-footer-custom">
            <button class="btn-cancelar" onclick="cerrarModalCSV()">Cancelar</button>
            <button class="btn-importar" id="btnImportarCSV" onclick="ejecutarCSV()" disabled>
                <i class="bi bi-upload"></i> Importar
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="<?= $base_url ?>/assets/js/herramientas.js"></script>
</body>
</html>