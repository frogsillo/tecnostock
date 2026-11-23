<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

// Traemos todos los movimientos connn producto y usuario
$sql = "SELECT m.id_movimiento, m.fecha_hora, m.tipo, m.cantidad, m.observacion,
               p.codigo AS codigo_producto, p.nombre AS nombre_producto,
               u.nombre AS nombre_usuario
        FROM movimiento m
        INNER JOIN producto p ON m.id_producto = p.id_producto
        INNER JOIN usuario u ON m.id_usuario = u.id_usuario
        ORDER BY m.fecha_hora DESC";

$stmt = $pdo->query($sql);
$movimientos = $stmt->fetchAll();
?>

<!-- Título y botones de acción -->
<div class="page-header">
    <div>
        <h1>Movimientos</h1>
        <p>Historial de entradas y salidas de stock.</p>
    </div>
    <div class="actions">
        <a href="entrada.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-down me-1"></i> Registrar entrada</a>
        <a href="salida.php" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up me-1"></i> Registrar salida</a>
    </div>
</div>

<!-- Tabla de Movimientos -->
<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th>Usuario</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimientos) > 0): ?>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td class="num text-nowrap"><?= date('d/m/Y H:i', strtotime($mov['fecha_hora'])) ?></td>
                            <td>
                                <?php if ($mov['tipo'] == 'entrada'): ?>
                                    <span class="badge bg-success">Entrada</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Salida</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="cell-strong"><?= htmlspecialchars($mov['nombre_producto']) ?></span><br>
                                <small class="text-muted"><?= htmlspecialchars($mov['codigo_producto']) ?></small>
                            </td>
                            <td class="text-end">
                                <span class="qty <?= $mov['tipo'] == 'entrada' ? 'qty-in' : 'qty-out' ?>">
                                    <?= $mov['tipo'] == 'entrada' ? '+' : '−' ?><?= $mov['cantidad'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($mov['nombre_usuario']) ?></td>
                            <td><?= htmlspecialchars($mov['observacion'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="bi bi-arrow-left-right"></i>
                            Aún no hay movimientos. Registra una entrada para empezar.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($movimientos) > 0): ?>
        <div class="table-foot">
            <span><?= count($movimientos) ?> <?= count($movimientos) === 1 ? 'movimiento' : 'movimientos' ?></span>
            <span>Del más reciente al más antiguo.</span>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
?>
