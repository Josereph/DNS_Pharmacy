<?php
// controllers/VentaController.php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/VentaModel.php';

class VentaController {
    private $model;
    
    public function __construct() {
        $this->model = new VentaModel();
    }
    
    public function getVentas() {
        $desde = isset($_GET['desde']) ? $_GET['desde'] : '';
        $hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
        
        try {
            $ventas = $this->model->getVentas($desde, $hasta);
            $totales = $this->model->getTotales($ventas);
            
            echo json_encode([
                'success' => true,
                'data' => $ventas,
                'totales' => $totales
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getDetalle() {
        $id_venta = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id_venta <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de venta no válido'
            ]);
            return;
        }
        
        try {
            $detalle = $this->model->getDetalleVenta($id_venta);
            
            if ($detalle) {
                echo json_encode([
                    'success' => true,
                    'data' => $detalle
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Venta no encontrada'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}

// Manejar la petición
$controller = new VentaController();

if (isset($_GET['action']) && $_GET['action'] == 'detalle') {
    $controller->getDetalle();
} else {
    $controller->getVentas();
}
?>