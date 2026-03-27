<?php
require_once __DIR__ . '/../config/database.php';

class PerfilModel {

    private $conn;

    public function __construct() {
        $this->conn = conectar();
    }

    public function obtenerPerfil($id_usuario) {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function obtenerVentas($id_usuario) {
        $sql = "SELECT * FROM ventas WHERE id_usuario = ? ORDER BY fecha_venta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerDetalle($id_venta) {
        $sql = "SELECT nombre, cantidad, precio_unitario, subtotal 
                FROM detalle_venta 
                WHERE id_venta = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_venta);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}