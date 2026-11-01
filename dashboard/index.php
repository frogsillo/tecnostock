<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

// 1. Contar Productos Activos
$stmt = $pdo->query("SELECT COUNT(*) FROM producto WHERE estado = 1");
$total_productos = $stmt->fetchColumn();

// 2. Contar Productos con Stock Bajo (RN16)
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
<div class="mb-4">
    <h3><i class="bi bi-speedometer2"></i> Panel de Control</h3>
    <p class="text-muted">Resumen general del inventario de TecnoStock.</p>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Productos Activos</h6>
                    <h2 class="mb-0 fw-bold"><?= $total_productos ?></h2>
                </div>
                <i class="bi bi-box-seam fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
                <a href="../productos/index.php" class="text-white text-decoration-none small">Ver catálogo <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-danger text-white shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Stock Bajo</h6>
                    <h2 class="mb-0 fw-bold"><?= $total_stock_bajo ?></h2>
                </div>
                <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
                <span class="small opacity-75">Requieren reposición</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Categorías</h6>
                    <h2 class="mb-0 fw-bold"><?= $total_categorias ?></h2>
                </div>
                <i class="bi bi-tags fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
                <a href="../categorias/index.php" class="text-white text-decoration-none small">Ver categorías <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Últimos Movimientos -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Últimos Movimientos</h5>
        <a href="../movimientos/index.php" class="btn btn-sm btn-outline-secondary">Ver todos</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ultimos_movimientos) > 0): ?>
                        <?php foreach ($ultimos_movimientos as $mov): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($mov['fecha_hora'])) ?></td>
                                <td>
                                    <?php if ($mov['tipo'] == 'entrada'): ?>
                                        <span class="badge bg-success">Entrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Salida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($mov['producto']) ?></td>
                                <td class="fw-bold"><?= $mov['cantidad'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">No hay movimientos recientes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
