<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}

require_once __DIR__ . '/../models/ProductoModel.php';

header('Content-Type: application/json');

$model  = new ProductoModel();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {

    /* ── Listar productos ── */
    case 'listar':
        $productos = $model->obtenerTodos();
        echo json_encode(['ok' => true, 'datos' => $productos]);
        break;

    /* ── Stats ── */
    case 'stats':
        $stats = $model->obtenerStats();
        echo json_encode(['ok' => true, 'datos' => $stats]);
        break;

    /* ── Listar categorías ── */
    case 'listar_categorias':
        $cats = $model->obtenerCategorias();
        echo json_encode(['ok' => true, 'datos' => $cats]);
        break;

    /* ── Guardar producto (crear o editar) ── */
    case 'guardar':
        $id            = intval($_POST['id_producto'] ?? 0);
        $id_categoria  = intval($_POST['id_categoria'] ?? 0);
        $codigo        = trim($_POST['codigo_barras'] ?? '');
        $nombre        = trim($_POST['nombre'] ?? '');
        $descripcion   = trim($_POST['descripcion'] ?? '');
        $presentacion  = trim($_POST['presentacion'] ?? '');
        $marca         = trim($_POST['marca'] ?? '');
        $laboratorio   = trim($_POST['laboratorio'] ?? '');
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $precio_venta  = floatval($_POST['precio_venta'] ?? 0);
        $stock_actual  = intval($_POST['stock_actual'] ?? 0);
        $stock_minimo  = intval($_POST['stock_minimo'] ?? 0);
        $unidad        = trim($_POST['unidad_medida'] ?? '');
        $receta        = isset($_POST['requiere_receta']) ? 1 : 0;
        $estado        = isset($_POST['estado']) ? 1 : 0;
        $imagen_actual = trim($_POST['imagen_actual'] ?? '');

        // Validaciones básicas servidor
        if (!$id_categoria || !$codigo || !$nombre || !$unidad) {
            echo json_encode(['ok' => false, 'mensaje' => 'Faltan campos obligatorios.']);
            break;
        }

        if ($precio_venta < $precio_compra) {
            echo json_encode(['ok' => false, 'mensaje' => 'El precio de venta no puede ser menor al de compra.']);
            break;
        }

        // Verificar código de barras duplicado
        if ($model->codigoBarrasExiste($codigo, $id)) {
            echo json_encode(['ok' => false, 'mensaje' => 'El código de barras ya está registrado en otro producto.']);
            break;
        }

        // Manejo de imagen
        $imagen_url = $imagen_actual;

        if (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] === UPLOAD_ERR_OK) {
            $archivo    = $_FILES['imagen_url'];
            $extension  = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($extension, $permitidos)) {
                echo json_encode(['ok' => false, 'mensaje' => 'Tipo de imagen no permitido.']);
                break;
            }

            if ($archivo['size'] > 2 * 1024 * 1024) {
                echo json_encode(['ok' => false, 'mensaje' => 'La imagen supera los 2MB.']);
                break;
            }

            $carpeta = __DIR__ . '/../assets/img/productos/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

            $nombre_archivo = 'prod_' . time() . '_' . uniqid() . '.' . $extension;
            $ruta_destino   = $carpeta . $nombre_archivo;

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                // Eliminar imagen anterior si existe
                if ($imagen_actual && file_exists(__DIR__ . '/../' . $imagen_actual)) {
                    unlink(__DIR__ . '/../' . $imagen_actual);
                }
                $imagen_url = 'assets/img/productos/' . $nombre_archivo;
            } else {
                echo json_encode(['ok' => false, 'mensaje' => 'Error al subir la imagen.']);
                break;
            }
        }

        $datos = compact(
            'id_categoria', 'codigo', 'nombre', 'descripcion',
            'presentacion', 'marca', 'laboratorio',
            'precio_compra', 'precio_venta',
            'stock_actual', 'stock_minimo',
            'unidad', 'receta', 'imagen_url', 'estado'
        );

        // Renombrar claves para el modelo
        $datos['codigo_barras']   = $datos['codigo'];   unset($datos['codigo']);
        $datos['unidad_medida']   = $datos['unidad'];   unset($datos['unidad']);
        $datos['requiere_receta'] = $datos['receta'];   unset($datos['receta']);

        if ($id > 0) {
            $datos['id_producto'] = $id;
            $ok = $model->actualizar($datos);
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Producto actualizado.' : 'Error al actualizar.']);
        } else {
            $nuevoId = $model->insertar($datos);
            echo json_encode(['ok' => $nuevoId > 0, 'mensaje' => 'Producto guardado.', 'id' => $nuevoId]);
        }
        break;

    /* ── Eliminar producto ── */
    case 'eliminar':
        $id       = intval($_POST['id_producto'] ?? 0);
        $resultado = $model->eliminar($id);
        echo json_encode($resultado);
        break;

    /* ── Guardar categoría ── */
    case 'guardar_categoria':
        $id          = intval($_POST['id_categoria'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado      = isset($_POST['estado']) ? 1 : 0;

        if (!$nombre) {
            echo json_encode(['ok' => false, 'mensaje' => 'El nombre es obligatorio.']);
            break;
        }

        $datos = ['nombre' => $nombre, 'descripcion' => $descripcion, 'estado' => $estado];

        if ($id > 0) {
            $datos['id_categoria'] = $id;
            $ok = $model->actualizarCategoria($datos);
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Categoría actualizada.' : 'Error al actualizar.']);
        } else {
            $nuevoId = $model->insertarCategoria($datos);
            echo json_encode(['ok' => $nuevoId > 0, 'mensaje' => 'Categoría guardada.', 'id' => $nuevoId]);
        }
        break;

    /* ── Eliminar categoría ── */
    case 'eliminar_categoria':
        $id        = intval($_POST['id_categoria'] ?? 0);
        $resultado = $model->eliminarCategoria($id);
        echo json_encode($resultado);
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}