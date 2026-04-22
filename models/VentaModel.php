<?php
// models/VentaModel.php
require_once __DIR__ . '/../config/database.php';

class VentaModel {
    private $conn;
    
    public function __construct() {
        $this->conn = conectar();
    }
    
    public function getVentas($desde = '', $hasta = '') {
        $sql = "SELECT 
                    v.id_venta,
                    v.numero_ticket,
                    CONCAT(u.nombre, ' ', u.apellido) as empleado_nombre,
                    v.fecha_venta,
                    v.subtotal,
                    v.impuesto,
                    v.total,
                    v.metodo_pago,
                    v.estado
                FROM ventas v
                LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                WHERE v.estado != 'anulada'";
        
        $params = [];
        $types = "";
        
        if (!empty($desde)) {
            $sql .= " AND DATE(v.fecha_venta) >= ?";
            $params[] = $desde;
            $types .= "s";
        }
        
        if (!empty($hasta)) {
            $sql .= " AND DATE(v.fecha_venta) <= ?";
            $params[] = $hasta;
            $types .= "s";
        }
        
        $sql .= " ORDER BY v.fecha_venta DESC";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ventas = [];
        while ($row = $result->fetch_assoc()) {
            $ventas[] = $row;
        }
        
        $stmt->close();
        return $ventas;
    }
    
    public function getDetalleVenta($id_venta) {
        // Datos de la venta
        $sql = "SELECT 
                    v.*,
                    CONCAT(u.nombre, ' ', u.apellido) as empleado_nombre
                FROM ventas v 
                LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario 
                WHERE v.id_venta = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_venta);
        $stmt->execute();
        $result = $stmt->get_result();
        $venta = $result->fetch_assoc();
        
        if (!$venta) {
            return null;
        }
        
        // Productos de la venta
        $sql2 = "SELECT 
                    dv.*,
                    p.nombre as nombre_producto,
                    p.codigo_barras,
                    p.presentacion
                 FROM detalle_venta dv 
                 LEFT JOIN productos p ON dv.id_producto = p.id_producto 
                 WHERE dv.id_venta = ?";
        
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("i", $id_venta);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        $productos = [];
        while ($row = $result2->fetch_assoc()) {
            $productos[] = $row;
        }
        
        $venta['productos'] = $productos;
        
        $stmt->close();
        $stmt2->close();
        
        return $venta;
    }
    
    public function getTotales($ventas) {
        $totalTickets = count($ventas);
        $totalVendido = array_sum(array_column($ventas, 'total'));
        
        return [
            'tickets' => $totalTickets,
            'total' => $totalVendido
        ];
    }
}
?>