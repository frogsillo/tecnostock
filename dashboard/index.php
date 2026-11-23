<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

// 1. Contar Productos Activos
$stmt = $pdo->query("SELECT COUNT(*) FROM producto WHERE estado = 1");
$total_productos = $stmt->fetchColumn();

// 2. Contar Productos con Stock Bajo
$stmt = $pdo->query("SELECT COUNT(*) FROM producto WHERE stock_actual < stock_minimo AND estado = 1");
$total_stock_bajo = $stmt->fetchColumn();

// 3. Contar Categorías Activas
$stmt = $pdo->query("SELECT COUNT(*) FROM categoria WHERE estado = 1");
$total_categorias = $stmt->fetchColumn();

// 4. Traer los últimos 5 movimientos
$sql_movs = "SELECT m.fecha_hora, m.tipo, m.cantidad, p.nombre AS producto
             FROM movimiento m
             INNER JOIN producto p ON m.id_producto = p.id_producto
             ORDER BY m.fecha_hora DESC LIMIT 5";
$stmt = $pdo->query($sql_movs);
$ultimos_movimientos = $stmt->fetchAll();
?>

<!-- Bienvenida -->
<div class="page-header">
    <div>
        <h1>Panel de control</h1>
        <p>Resumen general del inventario de TecnoStock.</p>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row g-3 mb-4">
    <!-- Tarjeta 1: Productos Activos -->
    <div class="col-md-4">
        <a href="../productos/index.php" class="stat-link">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="stat-label">Productos activos</span>
                        <span class="stat-icon"><i class="bi bi-box-seam"></i></span>
                    </div>
                    <div class="stat-value"><?= $total_productos ?></div>
                    <p class="stat-note">Ver catálogo completo</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta 2: Stock Bajo (clickeable). Solo se tiñe de rojo si hay alertas -->
    <div class="col-md-4">
        <a href="../productos/index.php?alerta=1" class="stat-link">
            <div class="card stat-card <?= $total_stock_bajo > 0 ? 'is-danger' : '' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="stat-label">Stock bajo</span>
                        <span class="stat-icon"><i class="bi bi-exclamation-triangle"></i></span>
                    </div>
                    <div class="stat-value"><?= $total_stock_bajo ?></div>
                    <p class="stat-note">Productos bajo el stock mínimo</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta 3: Categorías -->
    <div class="col-md-4">
        <a href="../categorias/index.php" class="stat-link">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="stat-label">Categorías activas</span>
                        <span class="stat-icon"><i class="bi bi-tags"></i></span>
                    </div>
                    <div class="stat-value"><?= $total_categorias ?></div>
                    <p class="stat-note">Ver todas las categorías</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Tabla de Últimos Movimientos -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Últimos movimientos</h2>
    <a href="../movimientos/index.php" class="btn btn-sm btn-outline-secondary">Ver todos</a>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($ultimos_movimientos) > 0): ?>
                    <?php foreach ($ultimos_movimientos as $mov): ?>
                        <tr>
                            <td class="num"><?= date('d/m/Y H:i', strtotime($mov['fecha_hora'])) ?></td>
                            <td>
                                <?php if ($mov['tipo'] == 'entrada'): ?>
                                    <span class="badge bg-success">Entrada</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Salida</span>
                                <?php endif; ?>
                            </td>
                            <td class="cell-strong"><?= htmlspecialchars($mov['producto']) ?></td>
                            <td class="text-end">
                                <span class="qty <?= $mov['tipo'] == 'entrada' ? 'qty-in' : 'qty-out' ?>">
                                    <?= $mov['tipo'] == 'entrada' ? '+' : '−' ?><?= $mov['cantidad'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="bi bi-arrow-left-right"></i>
                            Aún no hay movimientos. Registra una entrada para empezar.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
