<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_alerta = $_SESSION['tipo_alerta'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipo_alerta']);

// Capturamos el término de búsqueda y el filtro de alerta
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$alerta = isset($_GET['alerta']) ? intval($_GET['alerta']) : 0;

$sql = "SELECT p.id_producto, p.codigo, p.nombre, p.precio, p.stock_actual, p.stock_minimo, p.estado, c.nombre AS categoria
        FROM producto p
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria";

$where_clauses = [];
$params = [];

if ($buscar !== '') {
    $where_clauses[] = "(p.nombre LIKE :buscar1 OR p.codigo LIKE :buscar2 OR c.nombre LIKE :buscar3)";
    $params[':buscar1'] = "%$buscar%";
    $params[':buscar2'] = "%$buscar%";
    $params[':buscar3'] = "%$buscar%";
}

if ($alerta == 1) {
    $where_clauses[] = "p.stock_actual < p.stock_minimo AND p.estado = 1";
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// OPTIMIZACIÓN: Agregamos LIMIT 100 para que no cargue miles de registros a la vez
$sql .= " ORDER BY p.nombre ASC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();
?>

<!-- Título y botón para crear -->
<div class="page-header">
    <div>
        <h1>Productos</h1>
        <p>Catálogo y control de stock.</p>
    </div>
    <div class="actions">
        <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nuevo producto</a>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Barra de búsqueda -->
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <?php if ($alerta == 1): ?>
                <input type="hidden" name="alerta" value="1">
            <?php endif; ?>
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="buscar" placeholder="Buscar por nombre, código o categoría..." value="<?= htmlspecialchars($buscar) ?>">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-secondary w-100">Buscar</button>
                <?php if ($buscar !== '' || $alerta == 1): ?>
                    <a href="index.php" class="btn btn-light border w-100" title="Ver todos los productos"><i class="bi bi-x-lg me-1"></i> Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Filtros rápidos -->
<div class="filter-chips">
    <a href="index.php<?= $buscar !== '' ? '?buscar=' . urlencode($buscar) : '' ?>" class="filter-chip <?= $alerta != 1 ? 'active' : '' ?>">Todos</a>
    <a href="index.php?alerta=1<?= $buscar !== '' ? '&buscar=' . urlencode($buscar) : '' ?>" class="filter-chip is-alert <?= $alerta == 1 ? 'active' : '' ?>">
        <i class="bi bi-exclamation-triangle"></i> Stock bajo
    </a>
</div>

<!-- Tabla de Productos -->
<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th class="text-end">Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($productos) > 0): ?>
                    <?php foreach ($productos as $prod): ?>
                        <?php
                            $stock_bajo = ($prod['stock_actual'] < $prod['stock_minimo']);
                            $clase_fila = $stock_bajo ? 'table-danger' : '';
                            $pct = ($prod['stock_minimo'] > 0)
                                ? min(100, round($prod['stock_actual'] / ($prod['stock_minimo'] * 2) * 100))
                                : 100;
                        ?>
                        <tr class="<?= $clase_fila ?> <?= $prod['estado'] == 0 ? 'row-inactive' : '' ?>">
                            <td class="cell-strong"><?= htmlspecialchars($prod['codigo']) ?></td>
                            <td class="cell-strong"><?= htmlspecialchars($prod['nombre']) ?></td>
                            <td><span class="badge bg-secondary badge-plain"><?= htmlspecialchars($prod['categoria']) ?></span></td>
                            <td class="text-end num">$<?= number_format($prod['precio'], 2, ',', '.') ?></td>
                            <td>
                                <div class="stock-cell" <?= $stock_bajo ? 'title="¡Stock por debajo del mínimo!"' : '' ?>>
                                    <div class="stock-text">
                                        <?php if ($stock_bajo): ?><i class="bi bi-exclamation-triangle-fill"></i><?php endif; ?>
                                        <strong><?= $prod['stock_actual'] ?></strong>
                                        <span class="min">/ mín. <?= $prod['stock_minimo'] ?></span>
                                    </div>
                                    <div class="stock-meter"><span class="<?= $stock_bajo ? 'is-low' : '' ?>" style="width: <?= $pct ?>%"></span></div>
                                </div>
                            </td>
                            <!-- data-col="estado": el JS localiza esta celda para actualizar la insignia -->
                            <td data-col="estado">
                                <?php if ($prod['estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="editar.php?id=<?= $prod['id_producto'] ?>" class="btn btn-icon btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <!-- data-ajax: se envía con fetch (estado-ajax.js); sin JS funciona como formulario normal.
                                     data-nombre y data-label-*: el JS los usa para construir el botón contrario. -->
                                <?php if ($prod['estado'] == 1): ?>
                                    <form method="POST" action="desactivar.php" class="inline-form"
                                          data-ajax="desactivar"
                                          data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                                          data-label-activo="Activo"
                                          data-label-inactivo="Inactivo"
                                          data-confirm="¿Desactivar «<?= htmlspecialchars($prod['nombre']) ?>»? No se borrará de la base de datos, solo cambiará a inactivo y sus movimientos se conservan."
                                          data-confirm-title="Desactivar producto"
                                          data-confirm-ok="Desactivar">
                                        <input type="hidden" name="id" value="<?= $prod['id_producto'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn btn-icon btn-outline-danger" title="Desactivar producto"><i class="bi bi-slash-circle"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="activar.php" class="inline-form"
                                          data-ajax="activar"
                                          data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                                          data-label-activo="Activo"
                                          data-label-inactivo="Inactivo"
                                          data-confirm="¿Activar «<?= htmlspecialchars($prod['nombre']) ?>»? Volverá a estar disponible para entradas y salidas."
                                          data-confirm-title="Activar producto"
                                          data-confirm-ok="Activar"
                                          data-confirm-tone="success">
                                        <input type="hidden" name="id" value="<?= $prod['id_producto'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn btn-icon btn-outline-success" title="Activar producto"><i class="bi bi-check-circle"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="bi <?= $alerta == 1 ? 'bi-check2-circle' : 'bi-search' ?>"></i>
                            <?= $alerta == 1 ? 'No hay productos con stock bajo actualmente. ¡Todo está en orden!' : 'No se encontraron productos.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($productos) > 0): ?>
        <div class="table-foot">
            <span><?= count($productos) ?> <?= count($productos) === 1 ? 'producto' : 'productos' ?></span>
            <span>
                <?= count($productos) === 100
                    ? 'Mostrando los primeros 100. Usa el buscador para acotar.'
                    : 'La marca vertical de la barra indica el stock mínimo.' ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<!-- Activar / desactivar sin recargar la página (fetch) -->
<script src="/tecnostock/assets/js/estado-ajax.js"></script>

<?php
require_once '../includes/footer.php';
?>
