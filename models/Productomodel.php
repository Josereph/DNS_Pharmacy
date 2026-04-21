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
        $stmt = $conn->prepare("
            INSERT INTO productos
                (id_categoria, codigo_barras, nombre, descripcion,
                 presentacion, marca, laboratorio,
                 precio_compra, precio_venta,
                 stock_actual, stock_minimo,
                 unidad_medida, requiere_receta,
                 imagen_url, estado)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            'issssssddiisiisi',
            $datos['id_categoria'],
            $datos['codigo_barras'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['presentacion'],
            $datos['marca'],
            $datos['laboratorio'],
            $datos['precio_compra'],
            $datos['precio_venta'],
            $datos['stock_actual'],
            $datos['stock_minimo'],
            $datos['unidad_medida'],
            $datos['requiere_receta'],
            $datos['imagen_url'],
            $datos['estado']
        );
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return $id;
    }

    // Actualizar producto
    public function actualizar(array $datos): bool {
        $conn = conectar();
        $stmt = $conn->prepare("
            UPDATE productos SET
                id_categoria   = ?,
                codigo_barras  = ?,
                nombre         = ?,
                descripcion    = ?,
                presentacion   = ?,
                marca          = ?,
                laboratorio    = ?,
                precio_compra  = ?,
                precio_venta   = ?,
                stock_actual   = ?,
                stock_minimo   = ?,
                unidad_medida  = ?,
                requiere_receta = ?,
                imagen_url     = ?,
                estado         = ?
            WHERE id_producto  = ?
        ");
        $stmt->bind_param(
            'issssssddiisiisii',
            $datos['id_categoria'],
            $datos['codigo_barras'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['presentacion'],
            $datos['marca'],
            $datos['laboratorio'],
            $datos['precio_compra'],
            $datos['precio_venta'],
            $datos['stock_actual'],
            $datos['stock_minimo'],
            $datos['unidad_medida'],
            $datos['requiere_receta'],
            $datos['imagen_url'],
            $datos['estado'],
            $datos['id_producto']
        );
        $stmt->execute();
        $ok = $stmt->affected_rows >= 0;
        $stmt->close();
        $conn->close();
        return $ok;
    }

    // Eliminar producto (solo si no tiene ventas o compras asociadas)
    public function eliminar(int $id): array {
        $conn = conectar();

        // Verificar si tiene detalle_venta
        $stmt = $conn->prepare("SELECT id_detalle_venta FROM detalle_venta WHERE id_producto = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close(); $conn->close();
            return ['ok' => false, 'mensaje' => 'No se puede eliminar: el producto tiene ventas registradas.'];
        }
        $stmt->close();

        // Verificar si tiene detalle_compra
        $stmt2 = $conn->prepare("SELECT id_detalle_compra FROM detalle_compra WHERE id_producto = ? LIMIT 1");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        if ($stmt2->get_result()->num_rows > 0) {
            $stmt2->close(); $conn->close();
            return ['ok' => false, 'mensaje' => 'No se puede eliminar: el producto tiene compras registradas.'];
        }
        $stmt2->close();

        // Eliminar
        $stmt3 = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
        $stmt3->bind_param('i', $id);
        $stmt3->execute();
        $eliminado = $stmt3->affected_rows > 0;
        $stmt3->close();
        $conn->close();

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
        $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, estado) VALUES (?,?,?)");
        $stmt->bind_param('ssi', $datos['nombre'], $datos['descripcion'], $datos['estado']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return $id;
    }

    public function actualizarCategoria(array $datos): bool {
        $conn = conectar();
        $stmt = $conn->prepare("UPDATE categorias SET nombre=?, descripcion=?, estado=? WHERE id_categoria=?");
        $stmt->bind_param('ssii', $datos['nombre'], $datos['descripcion'], $datos['estado'], $datos['id_categoria']);
        $stmt->execute();
        $ok = $stmt->affected_rows >= 0;
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function eliminarCategoria(int $id): array {
        $conn = conectar();

        // Verificar si tiene productos asociados
        $stmt = $conn->prepare("SELECT id_producto FROM productos WHERE id_categoria = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close(); $conn->close();
            return ['ok' => false, 'mensaje' => 'No se puede eliminar: tiene productos asociados.'];
        }
        $stmt->close();

        $stmt2 = $conn->prepare("DELETE FROM categorias WHERE id_categoria = ?");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $eliminado = $stmt2->affected_rows > 0;
        $stmt2->close();
        $conn->close();

        return ['ok' => $eliminado, 'mensaje' => $eliminado ? 'Categoría eliminada.' : 'No encontrada.'];
    }
}