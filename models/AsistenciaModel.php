<?php
require_once __DIR__ . '/../config/database.php';

class AsistenciaModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = conectar();
    }

    /**
     * Registrar entrada o salida para un usuario
     * @param int $userId
     * @param string $origen 'qr' o 'manual'
     * @return array ['ok' => bool, 'mensaje' => string, 'tipo' => 'entrada'|'salida']
     */
    public function registrar($userId, $origen = 'qr')
    {
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        // Verificar si ya tiene registro hoy
        $stmt = $this->conn->prepare("SELECT * FROM asistencia WHERE id_usuario = ? AND fecha = ?");
        $stmt->bind_param('is', $userId, $fecha);
        $stmt->execute();
        $asistencia = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$asistencia) {
            // No hay registro hoy → registrar entrada
            $stmt = $this->conn->prepare("INSERT INTO asistencia (id_usuario, fecha, hora_entrada, estado, origen) VALUES (?, ?, ?, 'entrada', ?)");
            $stmt->bind_param('isss', $userId, $fecha, $hora, $origen);
            $stmt->execute();
            $stmt->close();
            return ['ok' => true, 'mensaje' => "Entrada registrada a las $hora", 'tipo' => 'entrada'];
        } elseif ($asistencia['hora_salida'] === null) {
            // Tiene entrada pero no salida → registrar salida
            $stmt = $this->conn->prepare("UPDATE asistencia SET hora_salida = ?, estado = 'salida' WHERE id_asistencia = ?");
            $stmt->bind_param('si', $hora, $asistencia['id_asistencia']);
            $stmt->execute();
            $stmt->close();
            return ['ok' => true, 'mensaje' => "Salida registrada a las $hora", 'tipo' => 'salida'];
        } else {
            // Ya tiene entrada y salida
            return ['ok' => false, 'mensaje' => "Ya registraste entrada y salida hoy."];
        }
    }

    /**
     * Obtener historial de asistencias con filtros
     * @param string $fechaDesde
     * @param string $fechaHasta
     * @param int|null $usuarioId
     * @return array
     */
    public function obtenerHistorial($fechaDesde = null, $fechaHasta = null, $usuarioId = null)
    {
        $sql = "SELECT a.*, u.nombre, u.apellido, u.correo
                FROM asistencia a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($fechaDesde) {
            $sql .= " AND a.fecha >= ?";
            $params[] = $fechaDesde;
            $types .= "s";
        }
        if ($fechaHasta) {
            $sql .= " AND a.fecha <= ?";
            $params[] = $fechaHasta;
            $types .= "s";
        }
        if ($usuarioId) {
            $sql .= " AND a.id_usuario = ?";
            $params[] = $usuarioId;
            $types .= "i";
        }

        $sql .= " ORDER BY a.fecha DESC, a.hora_entrada DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    /**
     * Obtener reporte rápido (presentes hoy, ausentes, horas trabajadas promedio)
     * @return array
     */
    public function obtenerReporteRapido()
    {
        $hoy = date('Y-m-d');
        $mesActual = date('Y-m-01');
        $mesActualFin = date('Y-m-t');

        // Total usuarios activos (empleados y admin)
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE estado = 1");
        $stmt->execute();
        $totalUsuarios = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        // Presentes hoy (tienen entrada)
        $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT id_usuario) as presentes FROM asistencia WHERE fecha = ?");
        $stmt->bind_param('s', $hoy);
        $stmt->execute();
        $presentes = $stmt->get_result()->fetch_assoc()['presentes'];
        $stmt->close();

        // Ausentes hoy
        $ausentes = $totalUsuarios - $presentes;

        // Promedio horas trabajadas este mes (solo días con salida)
        $stmt = $this->conn->prepare("
            SELECT AVG(TIMESTAMPDIFF(HOUR, hora_entrada, hora_salida)) as promedio_horas
            FROM asistencia
            WHERE fecha BETWEEN ? AND ?
              AND hora_salida IS NOT NULL
        ");
        $stmt->bind_param('ss', $mesActual, $mesActualFin);
        $stmt->execute();
        $promedioHoras = $stmt->get_result()->fetch_assoc()['promedio_horas'];
        $stmt->close();

        return [
            'total_usuarios' => $totalUsuarios,
            'presentes_hoy' => $presentes,
            'ausentes_hoy' => $ausentes,
            'promedio_horas_mes' => round($promedioHoras ?: 0, 1)
        ];
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}