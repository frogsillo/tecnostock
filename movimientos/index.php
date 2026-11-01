<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

// Traemos todos los movimientos haciendo JOIN con producto y usuario
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
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-arrow-left-right"></i> Historial de Movimientos</h3>
    <div>
        <a href="entrada.php" class="btn btn-success me-2"><i class="bi bi-box-arrow-in-down"></i> Registrar Entrada</a>
        <a href="salida.php" class="btn btn-danger"><i class="bi bi-box-arrow-up"></i> Registrar Salida</a>
    </div>
</div>

<!-- Tabla de Movimientos -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($movimientos) > 0): ?>
                        <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($mov['fecha_hora'])) ?></td>
                                <td>
                                    <?php if ($mov['tipo'] == 'entrada'): ?>
                                        <span class="badge bg-success"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-arrow-up-circle"></i> Salida</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($mov['codigo_producto']) ?></span><br>
                                    <small class="text-muted"><?= htmlspecialchars($mov['nombre_producto']) ?></small>
                                </td>
                                <td class="fw-bold fs-5"><?= $mov['cantidad'] ?></td>
                                <td><?= htmlspecialchars($mov['nombre_usuario']) ?></td>
                                <td><?= htmlspecialchars($mov['observacion'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No hay movimientos registrados en el sistema.</td>
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
