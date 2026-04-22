<?php

require_once __DIR__ . '/../config/database.php';

class PerfilModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = conectar();
    }

    public function obtenerPerfil($id_usuario)
    {
        $sql  = "SELECT id_usuario, id_rol, nombre, apellido, correo, telefono,
                        estado, ultimo_acceso, created_at, foto_perfil
                 FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerVentas($id_usuario)
    {
        $sql  = "SELECT id_venta, id_usuario, numero_ticket, fecha_venta,
                        subtotal, impuesto, total, monto_recibido, cambio,
                        metodo_pago, estado, observaciones, created_at, updated_at
                 FROM ventas WHERE id_usuario = ? ORDER BY fecha_venta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerDetalle($id_venta)
    {
        $sql  = "SELECT dv.id_detalle_venta, dv.id_venta, dv.id_producto, dv.id_lote,
                        dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre
                 FROM detalle_venta dv
                 INNER JOIN productos p ON dv.id_producto = p.id_producto
                 WHERE dv.id_venta = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id_venta);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function actualizarPerfil($id_usuario, $nombre, $apellido, $telefono)
    {
        /* null no es válido para bind_param 's' — convertir a string vacío */
        $telefono = (string)($telefono ?? '');

        $sql  = "UPDATE usuarios SET nombre = ?, apellido = ?, telefono = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log('prepare() falló: ' . $this->conn->error);
            return false;
        }

        $stmt->bind_param('sssi', $nombre, $apellido, $telefono, $id_usuario);

        if (!$stmt->execute()) {
            error_log('execute() falló: ' . $stmt->error);
            return false;
        }

        /* affected_rows = 0 cuando los datos son idénticos — sigue siendo éxito */
        return $stmt->affected_rows >= 0;
    }

    public function obtenerHash($id_usuario)
    {
        $sql  = "SELECT password_hash FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        return $row['password_hash'] ?? null;
    }

    public function actualizarPassword($id_usuario, $nuevo_hash)
    {
        $sql  = "UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $nuevo_hash, $id_usuario);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }

    public function actualizarFoto($id_usuario, $filename)
    {
        $sql  = "UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $filename, $id_usuario);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
}