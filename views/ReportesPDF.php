<!doctype html>
<html lang="es">
<head>
    <title>Reportes - DNS Pharmacy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../assets/css/slider.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/reportes-pdf.css">
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/Login.php');
    exit;
}
include 'layouts/slider.php';
?>

<div class="main-content">
    <div class="rp-header">
        <div>
            <span class="rp-badge">Módulo formal de reportes</span>
            <h2 class="rp-title">Reportes</h2>
            <p class="rp-subtitle">Seleccione el tipo de reporte, período y genere el PDF según la necesidad del negocio</p>
        </div>

        <div class="rp-actions">
            <a href="Estadisticas.php" class="btn-back-estadisticas">
                <i class="bi bi-bar-chart-line"></i> Volver a estadísticas
            </a>
        </div>
    </div>

    <div class="rp-grid">

        <div class="reporte-card" data-tipo="ventas">
            <div class="reporte-card-header">
                <div class="reporte-icon"><i class="bi bi-receipt"></i></div>
                <div>
                    <h4>Reporte de ventas</h4>
                    <p>Detalle de tickets, subtotales, impuestos, totales y métodos de pago.</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período</label>
                <select class="form-control periodo-select" onchange="toggleCustomDates(this)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Semanal</option>
                    <option value="mes" selected>Mensual</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="reporte-card" data-tipo="compras">
            <div class="reporte-card-header">
                <div class="reporte-icon"><i class="bi bi-cart-check"></i></div>
                <div>
                    <h4>Reporte de compras</h4>
                    <p>Historial de compras a proveedores con montos, fechas y estado.</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período</label>
                <select class="form-control periodo-select" onchange="toggleCustomDates(this)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Semanal</option>
                    <option value="mes" selected>Mensual</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="reporte-card" data-tipo="inventario">
            <div class="reporte-card-header">
                <div class="reporte-icon"><i class="bi bi-boxes"></i></div>
                <div>
                    <h4>Reporte de inventario</h4>
                    <p>Estado actual del inventario y productos con stock bajo o agotado.</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período</label>
                <select class="form-control periodo-select" onchange="toggleCustomDates(this)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Semanal</option>
                    <option value="mes" selected>Mensual</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="reporte-card" data-tipo="empleados">
            <div class="reporte-card-header">
                <div class="reporte-icon"><i class="bi bi-people"></i></div>
                <div>
                    <h4>Reporte de empleados</h4>
                    <p>Ranking de desempeño, tickets emitidos y ticket promedio por empleado.</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período</label>
                <select class="form-control periodo-select" onchange="toggleCustomDates(this)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Semanal</option>
                    <option value="mes" selected>Mensual</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="reporte-card" data-tipo="proveedores">
            <div class="reporte-card-header">
                <div class="reporte-icon"><i class="bi bi-building"></i></div>
                <div>
                    <h4>Reporte de proveedores</h4>
                    <p>Compras por proveedor, participación y comportamiento del abastecimiento.</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período</label>
                <select class="form-control periodo-select" onchange="toggleCustomDates(this)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Semanal</option>
                    <option value="mes" selected>Mensual</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="reporte-card" data-tipo="productos">
            <div class="reporte-card-header">
                <div class="reporte-icon"><i class="bi bi-box-seam"></i></div>
                <div>
                    <h4>Reporte de productos</h4>
                    <p>Productos más vendidos, ingresos, costo total y margen porcentual.</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período</label>
                <select class="form-control periodo-select" onchange="toggleCustomDates(this)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Semanal</option>
                    <option value="mes" selected>Mensual</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="reporte-card" data-tipo="financiero">
            <div class="reporte-card-header">
                <div class="reporte-icon" style="background:linear-gradient(135deg, #70ab32, #5c8e29);"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <h4>Reporte Financiero</h4>
                    <p>Análisis de ingresos, gastos, utilidad bruta y margen de ganancia.</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período</label>
                <select class="form-control periodo-select" onchange="toggleCustomDates(this)">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Semanal</option>
                    <option value="mes" selected>Mensual</option>
                    <option value="mes_anterior">Mes anterior</option>
                    <option value="anio">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)" style="background:linear-gradient(135deg, #70ab32, #5c8e29); box-shadow:0 10px 22px rgba(112, 171, 50, 0.3);">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="reporte-card" data-tipo="vencimientos">
            <div class="reporte-card-header">
                <div class="reporte-icon" style="background:linear-gradient(135deg, #f57c00, #e65100);"><i class="bi bi-calendar-x"></i></div>
                <div>
                    <h4>Reporte de Vencimientos</h4>
                    <p>Lotes críticos: vigentes, próximos a vencer y vencidos (sin rango de fecha).</p>
                </div>
            </div>

            <div class="reporte-form">
                <label>Período (No aplica para este reporte)</label>
                <select class="form-control periodo-select" disabled>
                    <option value="todo" selected>Todo el inventario actual</option>
                </select>

                <div class="custom-dates d-none">
                    <input type="date" class="form-control fecha-desde">
                    <input type="date" class="form-control fecha-hasta">
                </div>

                <button class="btn-generar-pdf" onclick="generarReporteDesdeCard(this)" style="background:linear-gradient(135deg, #f57c00, #e65100); box-shadow:0 10px 22px rgba(245, 124, 0, 0.3);">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

    </div>
</div>

<?php include 'layouts/footer.php'; ?>

<script src="../assets/js/reportes-pdf.js"></script>
</body>
</html>