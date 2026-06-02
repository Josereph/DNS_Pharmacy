<?php
require_once __DIR__ . '/../config/database.php';

class PerfilModel {

    private $conn;

    public function __construct() {
        $this->conn = conectar();
    }

    public function obtenerPerfil($id_usuario) {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario, id_rol, nombre, apellido, correo,
                    telefono, estado, ultimo_acceso, created_at, foto_perfil
             FROM usuarios WHERE id_usuario = ?"
        );
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerVentas($id_usuario) {
        $stmt = $this->conn->prepare(
            "SELECT id_venta, id_usuario, numero_ticket, fecha_venta,
                    subtotal, impuesto, total, monto_recibido, cambio,
                    metodo_pago, estado, observaciones, created_at, updated_at
             FROM ventas
             WHERE id_usuario = ?
             ORDER BY fecha_venta DESC"
        );
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

public function obtenerDetalle($id_venta) {
    // Verificar que el ID sea válido
    if ($id_venta <= 0) {
        return [];
    }
    
    // Consulta para obtener los detalles
    $sql = "SELECT dv.id_detalle_venta, dv.id_venta, dv.id_producto,
                   dv.cantidad, dv.precio_unitario, dv.subtotal,
                   p.nombre as nombre_producto
            FROM detalle_venta dv
            LEFT JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?";
    
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param('i', $id_venta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $detalles = [];
    while ($row = $resultado->fetch_assoc()) {
        $detalles[] = [
            'nombre' => $row['nombre_producto'] ?? 'Producto',
            'cantidad' => $row['cantidad'],
            'precio_unitario' => $row['precio_unitario'],
            'subtotal' => $row['subtotal']
        ];
    }
    
    return $detalles;
}

    /* Solo actualiza nombre, apellido, telefono — el correo NUNCA se toca */
    public function actualizarPerfil($id_usuario, $nombre, $apellido, $telefono) {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios
             SET nombre = ?, apellido = ?, telefono = ?
             WHERE id_usuario = ?"
        );
        $stmt->bind_param('sssi', $nombre, $apellido, $telefono, $id_usuario);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }

    public function obtenerHash($id_usuario) {
        $stmt = $this->conn->prepare(
            "SELECT password_hash FROM usuarios WHERE id_usuario = ?"
        );
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['password_hash'] ?? null;
    }

    public function actualizarPassword($id_usuario, $nuevo_hash) {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?"
        );
        $stmt->bind_param('si', $nuevo_hash, $id_usuario);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function actualizarFoto($id_usuario, $filename) {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?"
        );
        $stmt->bind_param('si', $filename, $id_usuario);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
}