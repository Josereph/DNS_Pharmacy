<?php

require_once __DIR__ . '/../config/database.php';

class ProductoModel {

    /* ══════════════════════════════════════════
       PRODUCTOS
    ══════════════════════════════════════════ */

    // Obtener todos los productos con nombre de categoría
    public function obtenerTodos() {
        $conn = conectar();
        $sql  = "
            SELECT p.*, c.nombre AS nombre_categoria
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            ORDER BY p.created_at DESC
        ";
        $resultado = $conn->query($sql);
        $productos = $resultado->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        return $productos;
    }

    // Obtener un producto por ID
    public function obtenerPorId(int $id) {
        $conn = conectar();
        $stmt = $conn->prepare("
            SELECT p.*, c.nombre AS nombre_categoria
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.id_producto = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $producto;
    }

    // Verificar si el código de barras ya existe (excluyendo un ID en edición)
    public function codigoBarrasExiste(string $codigo, int $excluirId = 0): bool {
        $conn = conectar();
        $stmt = $conn->prepare("
            SELECT id_producto FROM productos
            WHERE codigo_barras = ? AND id_producto != ?
            LIMIT 1
        ");
        $stmt->bind_param('si', $codigo, $excluirId);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        $conn->close();
        return $existe;
    }

    // Insertar nuevo producto
    public function insertar(array $datos): int {
        $conn = conectar();
        
        // Escapar valores para evitar inyección SQL
        $id_categoria    = intval($datos['id_categoria']);
        $codigo_barras   = $conn->real_escape_string($datos['codigo_barras']);
        $nombre          = $conn->real_escape_string($datos['nombre']);
        $descripcion     = $conn->real_escape_string($datos['descripcion']);
        $presentacion    = $conn->real_escape_string($datos['presentacion']);
        $marca           = $conn->real_escape_string($datos['marca']);
        $laboratorio     = $conn->real_escape_string($datos['laboratorio']);
        $precio_compra   = floatval($datos['precio_compra']);
        $precio_venta    = floatval($datos['precio_venta']);
        $stock_actual    = intval($datos['stock_actual']);
        $stock_minimo    = intval($datos['stock_minimo']);
        $unidad_medida   = $conn->real_escape_string($datos['unidad_medida']);
        $requiere_receta = intval($datos['requiere_receta']);
        $imagen_url      = $conn->real_escape_string($datos['imagen_url']);
        $estado          = intval($datos['estado']);
        
        $sql = "
            INSERT INTO productos
                (id_categoria, codigo_barras, nombre, descripcion,
                 presentacion, marca, laboratorio,
                 precio_compra, precio_venta,
                 stock_actual, stock_minimo,
                 unidad_medida, requiere_receta,
                 imagen_url, estado)
            VALUES
                ($id_categoria, '$codigo_barras', '$nombre', '$descripcion',
                 '$presentacion', '$marca', '$laboratorio',
                 $precio_compra, $precio_venta,
                 $stock_actual, $stock_minimo,
                 '$unidad_medida', $requiere_receta,
                 '$imagen_url', $estado)
        ";
        
        $conn->query($sql);
        $id = $conn->insert_id;
        $conn->close();
        return $id;
    }

    // Actualizar producto - VERSIÓN SIMPLIFICADA QUE FUNCIONA
    public function actualizar(array $datos): bool {
        $conn = conectar();
        
        // Escapar valores para evitar inyección SQL
        $id_producto     = intval($datos['id_producto']);
        $id_categoria    = intval($datos['id_categoria']);
        $codigo_barras   = $conn->real_escape_string($datos['codigo_barras']);
        $nombre          = $conn->real_escape_string($datos['nombre']);
        $descripcion     = $conn->real_escape_string($datos['descripcion']);
        $presentacion    = $conn->real_escape_string($datos['presentacion']);
        $marca           = $conn->real_escape_string($datos['marca']);
        $laboratorio     = $conn->real_escape_string($datos['laboratorio']);
        $precio_compra   = floatval($datos['precio_compra']);
        $precio_venta    = floatval($datos['precio_venta']);
        $stock_actual    = intval($datos['stock_actual']);
        $stock_minimo    = intval($datos['stock_minimo']);
        $unidad_medida   = $conn->real_escape_string($datos['unidad_medida']);
        $requiere_receta = intval($datos['requiere_receta']);
        $imagen_url      = $conn->real_escape_string($datos['imagen_url']);
        $estado          = intval($datos['estado']);
        
        $sql = "
            UPDATE productos SET
                id_categoria    = $id_categoria,
                codigo_barras   = '$codigo_barras',
                nombre          = '$nombre',
                descripcion     = '$descripcion',
                presentacion    = '$presentacion',
                marca           = '$marca',
                laboratorio     = '$laboratorio',
                precio_compra   = $precio_compra,
                precio_venta    = $precio_venta,
                stock_actual    = $stock_actual,
                stock_minimo    = $stock_minimo,
                unidad_medida   = '$unidad_medida',
                requiere_receta = $requiere_receta,
                imagen_url      = '$imagen_url',
                estado          = $estado
            WHERE id_producto   = $id_producto
        ";
        
        $result = $conn->query($sql);
        $ok = $result !== false;
        $conn->close();
        return $ok;
    }

    // Eliminar producto (solo si no tiene ventas o compras asociadas)
    public function eliminar(int $id): array {
        $conn = conectar();

        // Verificar si tiene detalle_venta
        $result = $conn->query("SELECT id_detalle_venta FROM detalle_venta WHERE id_producto = $id LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $conn->close();
            return ['ok' => false, 'mensaje' => 'No se puede eliminar: el producto tiene ventas registradas.'];
        }

        // Verificar si tiene detalle_compra
        $result2 = $conn->query("SELECT id_detalle_compra FROM detalle_compra WHERE id_producto = $id LIMIT 1");
        if ($result2 && $result2->num_rows > 0) {
            $conn->close();
            return ['ok' => false, 'mensaje' => 'No se puede eliminar: el producto tiene compras registradas.'];
        }

        // Obtener la imagen para eliminarla después
        $resultImg = $conn->query("SELECT imagen_url FROM productos WHERE id_producto = $id");
        $producto = $resultImg->fetch_assoc();
        $imagen_url = $producto['imagen_url'] ?? '';

        // Eliminar
        $conn->query("DELETE FROM productos WHERE id_producto = $id");
        $eliminado = $conn->affected_rows > 0;
        $conn->close();

        // Eliminar archivo de imagen si existe
        if ($eliminado && !empty($imagen_url)) {
            $ruta_imagen = __DIR__ . '/../' . $imagen_url;
            if (file_exists($ruta_imagen)) {
                @unlink($ruta_imagen);
            }
        }

        return ['ok' => $eliminado, 'mensaje' => $eliminado ? 'Producto eliminado.' : 'No se encontró el producto.'];
    }

    // Stats para el dashboard
    public function obtenerStats(): array {
        $conn = conectar();
        $stats = [];

        $r = $conn->query("SELECT COUNT(*) AS total FROM productos");
        $stats['total'] = $r->fetch_assoc()['total'];

        $r = $conn->query("SELECT COUNT(*) AS activos FROM productos WHERE estado = 1");
        $stats['activos'] = $r->fetch_assoc()['activos'];

        $r = $conn->query("SELECT COUNT(*) AS inactivos FROM productos WHERE estado = 0");
        $stats['inactivos'] = $r->fetch_assoc()['inactivos'];

        $r = $conn->query("SELECT COUNT(*) AS bajo FROM productos WHERE stock_actual <= stock_minimo AND estado = 1");
        $stats['stock_bajo'] = $r->fetch_assoc()['bajo'];

        $conn->close();
        return $stats;
    }

    /* ══════════════════════════════════════════
       CATEGORÍAS
    ══════════════════════════════════════════ */

    public function obtenerCategorias(): array {
        $conn = conectar();
        $r    = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
        $cats = $r->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        return $cats;
    }

    public function insertarCategoria(array $datos): int {
        $conn = conectar();
        
        $nombre      = $conn->real_escape_string($datos['nombre']);
        $descripcion = $conn->real_escape_string($datos['descripcion']);
        $estado      = intval($datos['estado']);
        
        $sql = "INSERT INTO categorias (nombre, descripcion, estado) VALUES ('$nombre', '$descripcion', $estado)";
        $conn->query($sql);
        $id = $conn->insert_id;
        $conn->close();
        return $id;
    }

    public function actualizarCategoria(array $datos): bool {
        $conn = conectar();
        
        $id          = intval($datos['id_categoria']);
        $nombre      = $conn->real_escape_string($datos['nombre']);
        $descripcion = $conn->real_escape_string($datos['descripcion']);
        $estado      = intval($datos['estado']);
        
        $sql = "UPDATE categorias SET nombre='$nombre', descripcion='$descripcion', estado=$estado WHERE id_categoria=$id";
        $result = $conn->query($sql);
        $ok = $result !== false;
        $conn->close();
        return $ok;
    }

    public function eliminarCategoria(int $id): array {
        $conn = conectar();

        // Verificar si tiene productos asociados
        $result = $conn->query("SELECT id_producto FROM productos WHERE id_categoria = $id LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $conn->close();
            return ['ok' => false, 'mensaje' => 'No se puede eliminar: tiene productos asociados.'];
        }

        $conn->query("DELETE FROM categorias WHERE id_categoria = $id");
        $eliminado = $conn->affected_rows > 0;
        $conn->close();

        return ['ok' => $eliminado, 'mensaje' => $eliminado ? 'Categoría eliminada.' : 'No encontrada.'];
    }
}