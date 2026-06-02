<?php
// config/database.php
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'dns_pharmacy');
define('DB_CHARSET', 'utf8mb4');

function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die(json_encode([
            'error'   => true,
            'mensaje' => 'Error de conexión: ' . $conn->connect_error
        ]));
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}

/**
 * Buscar productos por nombre, marca o laboratorio
 */
function buscarProductos($termino) {
    $conn = conectar();
    $productos = [];
    
    $termino = '%' . $termino . '%';
    
    $sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.presentacion, 
                   p.marca, p.laboratorio, p.precio_venta, p.stock_actual, 
                   p.requiere_receta, c.nombre as categoria
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE (p.nombre LIKE ? 
                   OR p.marca LIKE ? 
                   OR p.laboratorio LIKE ?
                   OR p.descripcion LIKE ?)
            AND p.estado = 1
            AND p.stock_actual > 0
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $productos;
}

/**
 * Obtener productos por categoría
 */
function getProductosPorCategoria($categoriaNombre) {
    $conn = conectar();
    $productos = [];
    
    $sql = "SELECT p.id_producto, p.nombre, p.presentacion, p.marca, 
                   p.precio_venta, p.stock_actual
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE c.nombre LIKE ? AND p.estado = 1 AND p.stock_actual > 0
            LIMIT 8";
    
    $stmt = $conn->prepare($sql);
    $categoriaLike = '%' . $categoriaNombre . '%';
    $stmt->bind_param("s", $categoriaLike);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $productos;
}

/**
 * Obtener todas las categorías disponibles
 */
function getCategorias() {
    $conn = conectar();
    $categorias = [];
    
    $sql = "SELECT id_categoria, nombre, descripcion FROM categorias WHERE estado = 1";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    
    $conn->close();
    return $categorias;
}

/**
 * Obtener información de la farmacia (para el contexto)
 */
function getInfoFarmacia() {
    return [
        'nombre' => 'DNS Pharmacy',
        'horario' => 'Lunes a Viernes 8:00 am - 8:00 pm, Sábados 8:00 am - 6:00 pm',
        'telefono' => '+503 7622-4659',
        'whatsapp' => '+503 7622-4659',
        'email' => 'contacto@dnspharmacy.com',
        'direccion' => 'Boulevard Los Próceres, San Salvador, El Salvador'
    ];
}

/**
 * Verificar si un medicamento requiere receta
 */
function requiereReceta($productoNombre) {
    $conn = conectar();
    
    $sql = "SELECT requiere_receta FROM productos WHERE nombre LIKE ? AND estado = 1 LIMIT 1";
    $stmt = $conn->prepare($sql);
    $nombreLike = '%' . $productoNombre . '%';
    $stmt->bind_param("s", $nombreLike);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        $conn->close();
        return $row['requiere_receta'] == 1;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}
?>