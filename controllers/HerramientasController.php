<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}
if ($_SESSION['usuario_rol'] !== 'Administrador') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'mensaje' => 'Sin permiso.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

/* ══════════════════════════════════════════
   PLANTILLAS CSV
══════════════════════════════════════════ */
if ($accion === 'plantilla') {
    $tipo = $_GET['tipo'] ?? '';

    $plantillas = [
        'productos' => [
            'archivo'  => 'plantilla_productos.csv',
            'cabecera' => ['codigo_barras','nombre','categoria','descripcion','presentacion',
                           'marca','laboratorio','precio_compra','precio_venta',
                           'stock_actual','stock_minimo','unidad_medida','requiere_receta','estado'],
            'ejemplo'  => ['7501234567890','Paracetamol 500mg','Analgésicos','Para el dolor de cabeza',
                           'Tabletas','Bayer','Laboratorio MK','0.50','1.25','100','10','caja','0','1']
        ],
        'proveedores' => [
            'archivo'  => 'plantilla_proveedores.csv',
            'cabecera' => ['nombre','nombre_contacto','telefono','correo','direccion','nit','estado'],
            'ejemplo'  => ['Distribuidora Médica S.A.','Juan Pérez','+503 7600-0000',
                           'contacto@empresa.com','Col. Escalón, San Salvador','0614-010101-001-0','1']
        ],
        'usuarios' => [
            'archivo'  => 'plantilla_usuarios.csv',
            'cabecera' => ['nombre','apellido','correo','password','id_rol','telefono','estado'],
            'ejemplo'  => ['Carlos','Pérez','cajero@dns.com','Clave123!','2','+503 7500-0000','1']
        ],
    ];

    if (!isset($plantillas[$tipo])) { http_response_code(404); echo 'Tipo no válido.'; exit; }

    $p = $plantillas[$tipo];
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $p['archivo'] . '"');
    header('Cache-Control: no-cache');
    echo "\xEF\xBB\xBF"; // BOM UTF-8
    $out = fopen('php://output', 'w');
    fputcsv($out, $p['cabecera']);
    fputcsv($out, $p['ejemplo']);
    fclose($out);
    exit;
}

/* ══════════════════════════════════════════
   IMPORTAR CSV
══════════════════════════════════════════ */
if ($accion === 'importar_csv') {
    header('Content-Type: application/json');

    $tipo = $_POST['tipo'] ?? '';
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'mensaje' => 'No se recibió archivo.']); exit;
    }
    if (strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION)) !== 'csv') {
        echo json_encode(['ok' => false, 'mensaje' => 'Solo archivos .csv']); exit;
    }

    $handle = fopen($_FILES['archivo']['tmp_name'], 'r');
    // Quitar BOM
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $cabecera = array_map('trim', fgetcsv($handle));
    if (!$cabecera) { echo json_encode(['ok'=>false,'mensaje'=>'Archivo vacío.']); exit; }

    $conn       = conectar();
    $insertar   = 0; $actualizar = 0; $errores = []; $fila_num = 1;

    while (($fila = fgetcsv($handle)) !== false) {
        $fila_num++;
        if (count($fila) < count($cabecera)) { $errores[] = "Fila $fila_num: columnas insuficientes."; continue; }
        $d = array_combine($cabecera, array_map('trim', array_slice($fila, 0, count($cabecera))));

        switch ($tipo) {
            case 'productos':
                $codigo = $d['codigo_barras'] ?? '';
                $nombre = $d['nombre'] ?? '';
                $catNom = $d['categoria'] ?? '';
                if (!$codigo || !$nombre || !$catNom) { $errores[] = "Fila $fila_num: codigo, nombre y categoria obligatorios."; break; }

                // Categoría
                $stmtC = $conn->prepare("SELECT id_categoria FROM categorias WHERE nombre=? LIMIT 1");
                $stmtC->bind_param('s', $catNom); $stmtC->execute();
                $resC  = $stmtC->get_result()->fetch_assoc(); $stmtC->close();
                if ($resC) {
                    $id_cat = $resC['id_categoria'];
                } else {
                    $stmtIC = $conn->prepare("INSERT INTO categorias (nombre,estado) VALUES (?,1)");
                    $stmtIC->bind_param('s', $catNom); $stmtIC->execute();
                    $id_cat = $conn->insert_id; $stmtIC->close();
                }

                $pc=$d['precio_compra']??0; $pv=$d['precio_venta']??0;
                $sa=$d['stock_actual']??0;   $sm=$d['stock_minimo']??0;
                $um=$d['unidad_medida']??'unidad'; $rec=intval($d['requiere_receta']??0);
                $est=intval($d['estado']??1);
                $desc=$d['descripcion']??null; $pres=$d['presentacion']??null;
                $marc=$d['marca']??null; $lab=$d['laboratorio']??null;

                $stmtChk=$conn->prepare("SELECT id_producto FROM productos WHERE codigo_barras=? LIMIT 1");
                $stmtChk->bind_param('s',$codigo); $stmtChk->execute();
                $ex=$stmtChk->get_result()->fetch_assoc(); $stmtChk->close();

                if ($ex) {
                    $stmtU=$conn->prepare("UPDATE productos SET id_categoria=?,nombre=?,descripcion=?,presentacion=?,marca=?,laboratorio=?,precio_compra=?,precio_venta=?,stock_actual=?,stock_minimo=?,unidad_medida=?,requiere_receta=?,estado=? WHERE codigo_barras=?");
                    $stmtU->bind_param('isssssddiisis s',$id_cat,$nombre,$desc,$pres,$marc,$lab,$pc,$pv,$sa,$sm,$um,$rec,$est,$codigo);
                    $stmtU->execute(); $stmtU->close(); $actualizar++;
                } else {
                    $stmtI=$conn->prepare("INSERT INTO productos (id_categoria,codigo_barras,nombre,descripcion,presentacion,marca,laboratorio,precio_compra,precio_venta,stock_actual,stock_minimo,unidad_medida,requiere_receta,estado) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmtI->bind_param('issssssddiiisi',$id_cat,$codigo,$nombre,$desc,$pres,$marc,$lab,$pc,$pv,$sa,$sm,$um,$rec,$est);
                    $stmtI->execute(); $stmtI->close(); $insertar++;
                }
                break;

            case 'proveedores':
                $nombre=$d['nombre']??'';
                if (!$nombre) { $errores[]="Fila $fila_num: nombre obligatorio."; break; }
                $nit=($d['nit']??'')?:null;
                $stmtChk=$nit
                    ? $conn->prepare("SELECT id_proveedor FROM proveedores WHERE nit=? LIMIT 1")
                    : $conn->prepare("SELECT id_proveedor FROM proveedores WHERE nombre=? LIMIT 1");
                $buscarPor = $nit ?? $nombre;
                $stmtChk->bind_param('s', $buscarPor); $stmtChk->execute();
                $ex=$stmtChk->get_result()->fetch_assoc(); $stmtChk->close();
                $cont=$d['nombre_contacto']??null; $tel=$d['telefono']??null;
                $cor=$d['correo']??null; $dir=$d['direccion']??null; $est=intval($d['estado']??1);
                if ($ex) {
                    $stmtU=$conn->prepare("UPDATE proveedores SET nombre=?,nombre_contacto=?,telefono=?,correo=?,direccion=?,nit=?,estado=? WHERE id_proveedor=?");
                    $stmtU->bind_param('ssssssii',$nombre,$cont,$tel,$cor,$dir,$nit,$est,$ex['id_proveedor']);
                    $stmtU->execute(); $stmtU->close(); $actualizar++;
                } else {
                    $stmtI=$conn->prepare("INSERT INTO proveedores (nombre,nombre_contacto,telefono,correo,direccion,nit,estado) VALUES (?,?,?,?,?,?,?)");
                    $stmtI->bind_param('ssssssi',$nombre,$cont,$tel,$cor,$dir,$nit,$est);
                    $stmtI->execute(); $stmtI->close(); $insertar++;
                }
                break;

            case 'usuarios':
                $nom=$d['nombre']??''; $ape=$d['apellido']??'';
                $cor=$d['correo']??''; $pas=$d['password']??'';
                if (!$nom||!$ape||!$cor||!$pas) { $errores[]="Fila $fila_num: nombre, apellido, correo y password obligatorios."; break; }
                if (!filter_var($cor,FILTER_VALIDATE_EMAIL)) { $errores[]="Fila $fila_num: correo inválido."; break; }
                $rol=intval($d['id_rol']??2); $tel=$d['telefono']??null; $est=intval($d['estado']??1);
                $hash=password_hash($pas,PASSWORD_BCRYPT);
                $stmtChk=$conn->prepare("SELECT id_usuario FROM usuarios WHERE correo=? LIMIT 1");
                $stmtChk->bind_param('s',$cor); $stmtChk->execute();
                $ex=$stmtChk->get_result()->fetch_assoc(); $stmtChk->close();
                if ($ex) {
                    $stmtU=$conn->prepare("UPDATE usuarios SET nombre=?,apellido=?,id_rol=?,telefono=?,estado=? WHERE correo=?");
                    $stmtU->bind_param('ssisis',$nom,$ape,$rol,$tel,$est,$cor);
                    $stmtU->execute(); $stmtU->close(); $actualizar++;
                } else {
                    $stmtI=$conn->prepare("INSERT INTO usuarios (id_rol,nombre,apellido,correo,password_hash,telefono,estado) VALUES (?,?,?,?,?,?,?)");
                    $stmtI->bind_param('isssssi',$rol,$nom,$ape,$cor,$hash,$tel,$est);
                    $stmtI->execute(); $stmtI->close(); $insertar++;
                }
                break;

            default:
                $errores[]="Tipo '$tipo' no reconocido.";
        }
    }

    fclose($handle);
    $conn->close();
    echo json_encode(['ok'=>true,'insertar'=>$insertar,'actualizar'=>$actualizar,'errores'=>$errores,
        'mensaje'=>"Completado: $insertar nuevos, $actualizar actualizados".(count($errores)>0?', '.count($errores).' errores.':'.')]);
    exit;
}

/* ══════════════════════════════════════════
   IMPORTAR SQL
══════════════════════════════════════════ */
if ($accion === 'importar_sql') {
    header('Content-Type: application/json');

    $modo = $_POST['modo'] ?? 'restaurar'; // 'restaurar' | 'merge'

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'mensaje' => 'No se recibió archivo.']); exit;
    }
    if (strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION)) !== 'sql') {
        echo json_encode(['ok' => false, 'mensaje' => 'Solo archivos .sql']); exit;
    }
    if ($_FILES['archivo']['size'] > 50 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'mensaje' => 'El archivo no puede superar 50MB.']); exit;
    }

    $contenido = file_get_contents($_FILES['archivo']['tmp_name']);
    if (!$contenido) {
        echo json_encode(['ok' => false, 'mensaje' => 'No se pudo leer el archivo.']); exit;
    }

    $conn = conectar();
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    $conn->query("SET sql_mode=''");

    $ejecutados = 0;
    $errores    = [];

    if ($modo === 'merge') {
        // Solo INSERT — convertir a INSERT IGNORE
        preg_match_all('/INSERT INTO[^;]+;/is', $contenido, $matches);
        $sentencias = $matches[0];
        foreach ($sentencias as $sql) {
            // Convertir INSERT INTO → INSERT IGNORE INTO
            $sql = preg_replace('/INSERT\s+INTO\s+/i', 'INSERT IGNORE INTO ', $sql);
            if ($conn->query($sql) === true) {
                $ejecutados++;
            } else {
                $errores[] = substr($sql, 0, 80) . '... — ' . $conn->error;
            }
        }
    } else {
        // Restaurar completo — dividir por ; ignorando strings
        $sentencias = [];
        $buffer     = '';
        $inString   = false;
        $charStr    = '';

        for ($i = 0; $i < strlen($contenido); $i++) {
            $c = $contenido[$i];
            if (!$inString && ($c === '"' || $c === "'")) {
                $inString = true; $charStr = $c;
            } elseif ($inString && $c === $charStr && $contenido[$i-1] !== '\\') {
                $inString = false;
            }
            $buffer .= $c;
            if (!$inString && $c === ';') {
                $s = trim($buffer);
                if ($s && !preg_match('/^(--|\/\*|SET\s+SQL_MODE|SET\s+time_zone|\/\*!)/i', $s)) {
                    $sentencias[] = $s;
                }
                $buffer = '';
            }
        }

        foreach ($sentencias as $sql) {
            if (empty(trim($sql))) continue;
            if ($conn->query($sql) === true) {
                $ejecutados++;
            } else {
                if ($conn->errno !== 1065) { // ignorar queries vacías
                    $errores[] = substr($sql, 0, 100) . '... — ' . $conn->error;
                }
            }
        }
    }

    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    $conn->close();

    $ok = count($errores) === 0 || $ejecutados > 0;
    echo json_encode([
        'ok'         => $ok,
        'ejecutados' => $ejecutados,
        'errores'    => array_slice($errores, 0, 20), // máx 20 errores mostrados
        'mensaje'    => "$ejecutados sentencias ejecutadas" . (count($errores) > 0 ? ', ' . count($errores) . ' con error.' : ' sin errores.')
    ]);
    exit;
}

/* ══════════════════════════════════════════
   GENERAR BACKUP
══════════════════════════════════════════ */
if ($accion === 'backup') {
    $tablas = ['roles','categorias','proveedores','usuarios','productos','turnos',
               'compras','detalle_compra','ventas','detalle_venta','asistencia','movimientos_inventario'];

    $conn  = conectar();
    $fecha = date('Y-m-d_H-i-s');
    $arch  = 'backup_dns_' . $fecha . '.sql';
    $dir   = __DIR__ . '/../backups/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $sql  = "-- DNS Pharmacy - Backup " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tablas as $tabla) {
        $res = $conn->query("SHOW CREATE TABLE `$tabla`");
        if (!$res) continue;
        $row  = $res->fetch_assoc();
        $sql .= "-- Tabla: $tabla\nDROP TABLE IF EXISTS `$tabla`;\n";
        $sql .= $row['Create Table'] . ";\n\n";

        $data = $conn->query("SELECT * FROM `$tabla`");
        if ($data && $data->num_rows > 0) {
            while ($r = $data->fetch_assoc()) {
                $vals = array_map(fn($v) => $v === null ? 'NULL' : "'" . $conn->real_escape_string($v) . "'", array_values($r));
                $sql .= "INSERT INTO `$tabla` VALUES (" . implode(',', $vals) . ");\n";
            }
            $sql .= "\n";
        }
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $conn->close();

    file_put_contents($dir . $arch, $sql);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $arch . '"');
    header('Content-Length: ' . strlen($sql));
    echo $sql;
    exit;
}

/* ══════════════════════════════════════════
   HISTORIAL BACKUPS
══════════════════════════════════════════ */
if ($accion === 'historial_backups') {
    header('Content-Type: application/json');
    $dir   = __DIR__ . '/../backups/';
    $lista = [];
    if (is_dir($dir)) {
        foreach (glob($dir . 'backup_dns_*.sql') as $f) {
            $lista[] = ['nombre'=>basename($f),'tamanio'=>round(filesize($f)/1024,1).' KB',
                        'fecha'=>date('d/m/Y H:i',filemtime($f)),
                        'url'=>'/DNS_Pharmacy/controllers/HerramientasController.php?accion=descargar_backup&archivo='.basename($f)];
        }
        usort($lista, fn($a,$b) => strcmp($b['nombre'],$a['nombre']));
    }
    echo json_encode(['ok'=>true,'datos'=>$lista]);
    exit;
}

/* ══════════════════════════════════════════
   DESCARGAR / ELIMINAR BACKUP
══════════════════════════════════════════ */
if ($accion === 'descargar_backup') {
    $arch  = basename($_GET['archivo'] ?? '');
    $ruta  = __DIR__ . '/../backups/' . $arch;
    if (!$arch || !file_exists($ruta) || pathinfo($arch,PATHINFO_EXTENSION)!=='sql') { http_response_code(404); echo 'No encontrado.'; exit; }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$arch.'"');
    header('Content-Length: '.filesize($ruta));
    readfile($ruta); exit;
}

if ($accion === 'eliminar_backup') {
    header('Content-Type: application/json');
    $arch = basename($_POST['archivo'] ?? '');
    $ruta = __DIR__ . '/../backups/' . $arch;
    if (!$arch || !file_exists($ruta)) { echo json_encode(['ok'=>false,'mensaje'=>'No encontrado.']); exit; }
    unlink($ruta);
    echo json_encode(['ok'=>true,'mensaje'=>'Backup eliminado.']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['ok'=>false,'mensaje'=>'Acción no reconocida.']);