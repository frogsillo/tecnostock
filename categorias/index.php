<?php
// Inclui el header 
require_once '../includes/header.php';
// Incluila conexión a la base de datos
require_once '../config/conexion.php';

// Lógica para mostrar mensajes 
$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_alerta = $_SESSION['tipo_alerta'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipo_alerta']);

// Consulta para traer todas las categorías
$sql = "SELECT id_categoria, nombre, descripcion, estado FROM categoria ORDER BY nombre ASC";
$stmt = $pdo->query($sql);
$categorias = $stmt->fetchAll();
?>

<!-- Título y botón para crear nueva categoría -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-tags"></i> Gestión de Categorías</h3>
    <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva Categoría</a>
</div>

<!-- Alerta de éxito o error -->
<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show shadow-sm">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabla de Categorías -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categorias) > 0): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td class="align-middle"><?= $cat['id_categoria'] ?></td>
                                <td class="align-middle fw-bold"><?= htmlspecialchars($cat['nombre']) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($cat['descripcion'] ?? '—') ?></td>
                                <td class="align-middle">
                                    <?php if ($cat['estado'] == 1): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end align-middle">
                                    <a href="editar.php?id=<?= $cat['id_categoria'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($cat['estado'] == 1): ?>
                                        <a href="desactivar.php?id=<?= $cat['id_categoria'] ?>" class="btn btn-sm btn-danger" title="Desactivar" onclick="return confirm('¿Seguro que deseas desactivar esta categoría?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No hay categorías registradas en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Incluimos el footer
require_once '../includes/footer.php';
?>
