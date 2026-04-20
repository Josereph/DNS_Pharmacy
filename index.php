<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/views/Login.php');
    exit;
}

<<<<<<< HEAD
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$rol_usuario = $_SESSION['rol'] ?? 'Administrador';
$base_url = '/DNS_Pharmacy';
=======
if ($_SESSION['usuario_rol'] !== 'Administrador') {
    header('Location: /DNS_Pharmacy/views/pos.php');
    exit;
}

$admin_nombre = $_SESSION['usuario_nombre'] ?? 'Admin';
>>>>>>> FrontEnd1
?>
<!doctype html>
<html lang="es">
<head>
    <title>Inicio - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<<<<<<< HEAD
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
=======
    <link rel="stylesheet" href="assets/css/slider.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
>>>>>>> FrontEnd1
</head>
<body>

<?php include 'views/layouts/slider.php'; ?>

<div class="main-content">

<<<<<<< HEAD
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
=======
    <!-- Bienvenida -->
    <div class="dash-welcome">
        <div class="dash-welcome-left">
            <div class="dash-greeting">Bienvenido de vuelta,</div>
            <div class="dash-name"><?php echo htmlspecialchars($admin_nombre); ?> 👋</div>
            <div class="dash-sub">Panel de administración — DNS Pharmacy</div>
        </div>
        <div class="dash-welcome-right">
            <div class="dash-fecha-dia">Hoy es</div>
            <div class="dash-fecha-hora" id="dashHora">--:--</div>
            <div class="dash-fecha-completa" id="dashFecha">cargando...</div>
        </div>
    </div>

    <!-- Banner farmacéutico -->
    <div class="dash-banner">
        <div class="dash-banner-icon"><i class="bi bi-capsule"></i></div>
        <div class="dash-banner-text">
            <h4>DNS Pharmacy — Drug Network Supply</h4>
            <p>Sistema de gestión farmacéutica · San Salvador, El Salvador · contacto@dnspharmacy.com</p>
        </div>
        <div class="dash-banner-badge"><i class="bi bi-shield-check"></i> Sistema activo</div>
    </div>

    <!-- Stats -->
    <div class="dash-stats-grid">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-purple"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="dash-stat-val" id="statVentasHoy">—</div>
                <div class="dash-stat-label">Ventas hoy</div>
                <div class="dash-stat-sub" id="statVentasSub">$0.00 recaudado</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-green"><i class="bi bi-boxes"></i></div>
            <div>
                <div class="dash-stat-val" id="statProductos">—</div>
                <div class="dash-stat-label">Productos activos</div>
                <div class="dash-stat-sub" id="statProductosSub">en catálogo</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-amber"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="dash-stat-val" id="statStockBajo">—</div>
                <div class="dash-stat-label">Stock bajo</div>
                <div class="dash-stat-sub">requieren atención</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-blue"><i class="bi bi-people"></i></div>
            <div>
                <div class="dash-stat-val" id="statUsuarios">—</div>
                <div class="dash-stat-label">Usuarios activos</div>
                <div class="dash-stat-sub">en el sistema</div>
>>>>>>> FrontEnd1
            </div>
        </div>
    </div>

<<<<<<< HEAD
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
=======
    <!-- Ventas recientes + Stock bajo -->
    <div class="dash-row">

        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-clock-history"></i> Ventas recientes</div>
                <a href="views/historial_ventas.php" class="dash-card-link">Ver todas <i class="bi bi-arrow-right"></i></a>
            </div>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Empleado</th>
                        <th>Total</th>
                        <th>Método</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody id="dashVentasRecientes">
                    <tr><td colspan="5" class="dash-table-empty">Cargando...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-exclamation-circle"></i> Productos con stock bajo</div>
                <a href="views/inventario.php" class="dash-card-link">Ver inventario <i class="bi bi-arrow-right"></i></a>
            </div>
            <div id="dashStockBajo">
                <div class="dash-empty">Cargando...</div>
            </div>
        </div>

    </div>

    <!-- Compras recientes -->
    <div class="dash-row-full">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-cart3"></i> Compras recientes</div>
                <a href="views/compras.php" class="dash-card-link">Ver todas <i class="bi bi-arrow-right"></i></a>
            </div>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>N° Documento</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="dashComprasRecientes">
                    <tr><td colspan="5" class="dash-table-empty">Cargando...</td></tr>
                </tbody>
            </table>
>>>>>>> FrontEnd1
        </div>
    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<<<<<<< HEAD
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

=======
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="assets/js/index.js"></script>
>>>>>>> FrontEnd1
</body>
</html>