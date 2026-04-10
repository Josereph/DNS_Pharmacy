<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$rol_usuario = $_SESSION['rol'] ?? 'Administrador';
$base_url = '/DNS_Pharmacy';
?>
<!doctype html>
<html lang="es">
<head>
    <title>Dashboard - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/slider.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">

    <style>
        body {
            background: #f5f7fb;
        }

        .main-content {
            padding: 30px;
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #841480, #6a1066);
            color: #fff;
            border-radius: 18px;
            padding: 28px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(132, 20, 128, 0.15);
        }

        .dashboard-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .dashboard-header p {
            margin: 8px 0 0;
            opacity: 0.95;
        }

        .stat-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            transition: all 0.25s ease;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.10);
        }

        .stat-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px;
        }

        .stat-icon {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
        }

        .bg-soft-primary { background: #841480; }
        .bg-soft-success { background: #70ab32; }
        .bg-soft-warning { background: #f59e0b; }
        .bg-soft-danger  { background: #dc3545; }

        .stat-title {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #212529;
            margin: 0;
        }

        .section-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }

        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid #eef1f4;
            border-radius: 18px 18px 0 0 !important;
            font-weight: 700;
            padding: 18px 20px;
        }

        .section-card .card-body {
            padding: 20px;
        }

        .quick-action {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none !important;
            background: #fff;
            border: 1px solid #eef1f4;
            border-radius: 14px;
            padding: 14px 16px;
            color: #212529;
            transition: all 0.2s ease;
            margin-bottom: 12px;
        }

        .quick-action:hover {
            background: #f8f9fa;
            transform: translateX(4px);
        }

        .quick-action i {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9f2ff;
            color: #0d6efd;
            font-size: 1.1rem;
        }

        .alert-box {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            font-size: 0.95rem;
            border-left: 5px solid;
        }

        .alert-stock {
            background: #fff3cd;
            border-left-color: #f59e0b;
            color: #7a5a00;
        }

        .alert-expiry {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #842029;
        }

        .alert-sales {
            background: #d1e7dd;
            border-left-color: #198754;
            color: #0f5132;
        }

        .table thead th {
            border-top: none;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .badge-soft-success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 6px 10px;
            border-radius: 30px;
            font-weight: 600;
        }

        .badge-soft-warning {
            background: #fff3cd;
            color: #7a5a00;
            padding: 6px 10px;
            border-radius: 30px;
            font-weight: 600;
        }

        .badge-soft-danger {
            background: #f8d7da;
            color: #842029;
            padding: 6px 10px;
            border-radius: 30px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px 15px;
            }

            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>

<?php include 'views/layouts/slider.php'; ?>

<div class="main-content">

    <div class="dashboard-header">
        <h1><i class="fas fa-clinic-medical mr-2"></i> Panel principal</h1>
        <p>
            Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong>.
            Rol actual: <strong><?php echo htmlspecialchars($rol_usuario); ?></strong>.
            Aquí puedes visualizar el estado general del sistema POS de farmacia.
        </p>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div>
                        <div class="stat-title">Ventas del día</div>
                        <h3 class="stat-value">$1,248.75</h3>
                    </div>
                    <div class="stat-icon bg-soft-success">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div>
                        <div class="stat-title">Tickets generados</div>
                        <h3 class="stat-value">84</h3>
                    </div>
                    <div class="stat-icon bg-soft-primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div>
                        <div class="stat-title">Productos en stock bajo</div>
                        <h3 class="stat-value">12</h3>
                    </div>
                    <div class="stat-icon bg-soft-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div>
                        <div class="stat-title">Próximos a vencer</div>
                        <h3 class="stat-value">7</h3>
                    </div>
                    <div class="stat-icon bg-soft-danger">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card section-card">
                <div class="card-header">
                    <i class="fas fa-chart-line mr-2 text-primary"></i> Resumen operativo
                </div>
                <div class="card-body">
                    <div class="alert-box alert-sales">
                        <strong>Buen rendimiento:</strong> las ventas de hoy muestran un comportamiento estable respecto al promedio semanal.
                    </div>

                    <div class="alert-box alert-stock">
                        <strong>Atención:</strong> hay medicamentos y productos con existencias bajas que necesitan reposición.
                    </div>

                    <div class="alert-box alert-expiry">
                        <strong>Importante:</strong> algunos lotes están próximos a vencer y deben revisarse cuanto antes.
                    </div>

                    <hr>

                    <h6 class="font-weight-bold mb-3">Actividad reciente</h6>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Movimiento</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>08:15 AM</td>
                                    <td>Venta registrada #000245</td>
                                    <td>Cajero 1</td>
                                    <td><span class="badge-soft-success">Completado</span></td>
                                </tr>
                                <tr>
                                    <td>09:02 AM</td>
                                    <td>Ingreso de lote de ibuprofeno</td>
                                    <td>Administrador</td>
                                    <td><span class="badge-soft-success">Registrado</span></td>
                                </tr>
                                <tr>
                                    <td>09:40 AM</td>
                                    <td>Alerta de stock mínimo en vitaminas</td>
                                    <td>Sistema</td>
                                    <td><span class="badge-soft-warning">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td>10:05 AM</td>
                                    <td>Producto próximo a vencimiento detectado</td>
                                    <td>Sistema</td>
                                    <td><span class="badge-soft-danger">Revisar</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card section-card mb-4">
                <div class="card-header">
                    <i class="fas fa-bolt mr-2 text-warning"></i> Accesos rápidos
                </div>
                <div class="card-body">
                    <a href="<?php echo $base_url; ?>/views/ventas.php" class="quick-action">
                        <i class="fas fa-shopping-cart"></i>
                        <div>
                            <strong>Nueva venta</strong><br>
                            <small>Registrar una venta rápidamente</small>
                        </div>
                    </a>

                    <a href="<?php echo $base_url; ?>/views/productos.php" class="quick-action">
                        <i class="fas fa-capsules"></i>
                        <div>
                            <strong>Productos</strong><br>
                            <small>Gestionar inventario de farmacia</small>
                        </div>
                    </a>

                    <a href="<?php echo $base_url; ?>/views/compras.php" class="quick-action">
                        <i class="fas fa-truck-loading"></i>
                        <div>
                            <strong>Compras</strong><br>
                            <small>Registrar entradas de mercadería</small>
                        </div>
                    </a>

                    <a href="<?php echo $base_url; ?>/views/reportes.php" class="quick-action">
                        <i class="fas fa-file-medical-alt"></i>
                        <div>
                            <strong>Reportes</strong><br>
                            <small>Consultar métricas y movimientos</small>
                        </div>
                    </a>
                </div>
            </div>

            <div class="card section-card">
                <div class="card-header">
                    <i class="fas fa-notes-medical mr-2 text-danger"></i> Recordatorios
                </div>
                <div class="card-body">
                    <ul class="pl-3 mb-0">
                        <li class="mb-2">Verificar productos con fecha de vencimiento cercana.</li>
                        <li class="mb-2">Revisar el stock mínimo de antibióticos y analgésicos.</li>
                        <li class="mb-2">Confirmar cierre de caja al finalizar el turno.</li>
                        <li class="mb-0">Validar compras pendientes con proveedores.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>