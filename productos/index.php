<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

// Lógica para mostrar mensajes (éxito, error, advertencia)
$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_alerta = $_SESSION['tipo_alerta'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipo_alerta']);

// Capturamos el término de búsqueda (si existe)
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Preparamos la consulta base con JOIN
$sql = "SELECT p.id_producto, p.codigo, p.nombre, p.precio, p.stock_actual, p.stock_minimo, p.estado, c.nombre AS categoria 
        FROM producto p 
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria";

// Si hay búsqueda, agregamos el WHERE (usando 3 parámetros distintos)
if ($buscar !== '') {
    $sql .= " WHERE p.nombre LIKE :buscar1 OR p.codigo LIKE :buscar2 OR c.nombre LIKE :buscar3";
}

$sql .= " ORDER BY p.nombre ASC";

$stmt = $pdo->prepare($sql);

if ($buscar !== '') {
    $termino = "%$buscar%";
    $stmt->execute([
        ':buscar1' => $termino,
        ':buscar2' => $termino,
        ':buscar3' => $termino
    ]);
} else {
    $stmt->execute();
}

$productos = $stmt->fetchAll();
?>

<!-- Título y botón para crear -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-box-seam"></i> Gestión de Productos</h3>
    <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Producto</a>
</div>

<!-- Alerta de mensajes (éxito, error o advertencia) -->
<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show shadow-sm">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Barra de búsqueda -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="buscar" placeholder="Buscar por nombre, código o categoría..." value="<?= htmlspecialchars($buscar) ?>">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Buscar</button>
                <?php if ($buscar !== ''): ?>
                    <a href="index.php" class="btn btn-outline-danger w-100" title="Limpiar filtro"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos) > 0): ?>
                        <?php foreach ($productos as $prod): ?>
                            <?php 
                                // Evaluamos la alerta de stock bajo
                                $stock_bajo = ($prod['stock_actual'] < $prod['stock_minimo']);
                                $clase_fila = $stock_bajo ? 'table-danger' : '';
                            ?>
                            <tr class="<?= $clase_fila ?>">
                                <td class="fw-bold"><?= htmlspecialchars($prod['codigo']) ?></td>
                                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($prod['categoria']) ?></span></td>
                                <td>$<?= number_format($prod['precio'], 2, ',', '.') ?></td>
                                <td>
                                    <?php if ($stock_bajo): ?>
                                        <span class="badge bg-danger" title="¡Stock por debajo del mínimo!">
                                            <i class="bi bi-exclamation-triangle-fill"></i> 
                                            <?= $prod['stock_actual'] ?> / Mín: <?= $prod['stock_minimo'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <?= $prod['stock_actual'] ?> / Mín: <?= $prod['stock_minimo'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($prod['estado'] == 1): ?>
                                        <span class="badge bg-primary">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="editar.php?id=<?= $prod['id_producto'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <?php if ($prod['estado'] == 1): ?>
                                        <a href="desactivar.php?id=<?= $prod['id_producto'] ?>" class="btn btn-sm btn-danger" title="Desactivar" onclick="return confirm('¿Seguro que deseas desactivar este producto?')"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <?= $buscar !== '' ? 'No se encontraron productos para tu búsqueda.' : 'No hay productos registrados en el sistema.' ?>
                            </td>
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
