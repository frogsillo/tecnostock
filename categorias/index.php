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
<div class="page-header">
    <div>
        <h1>Categorías</h1>
        <p>Organiza los productos del catálogo.</p>
    </div>
    <div class="actions">
        <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva categoría</a>
    </div>
</div>

<!-- Alerta de éxito o error -->
<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabla de Categorías -->
<div class="table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($categorias) > 0): ?>
                    <?php foreach ($categorias as $cat): ?>
                        <tr class="<?= $cat['estado'] == 0 ? 'row-inactive' : '' ?>">
                            <td class="num"><?= $cat['id_categoria'] ?></td>
                            <td class="cell-strong"><?= htmlspecialchars($cat['nombre']) ?></td>
                            <td><?= htmlspecialchars($cat['descripcion'] ?? '—') ?></td>
                            <td>
                                <?php if ($cat['estado'] == 1): ?>
                                    <span class="badge bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="editar.php?id=<?= $cat['id_categoria'] ?>" class="btn btn-icon btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($cat['estado'] == 1): ?>
                                    <!-- Desactivar por POST con token CSRF; la confirmación la muestra el modal del footer -->
                                    <form method="POST" action="desactivar.php" class="inline-form"
                                          data-confirm="¿Desactivar la categoría «<?= htmlspecialchars($cat['nombre']) ?>»? Solo se puede desactivar si no tiene productos asociados."
                                          data-confirm-title="Desactivar categoría"
                                          data-confirm-ok="Desactivar">
                                        <input type="hidden" name="id" value="<?= $cat['id_categoria'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn btn-icon btn-outline-danger" title="Desactivar">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="bi bi-tags"></i>
                            Aún no hay categorías. <a href="crear.php">Crea la primera</a>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Incluimos el footer
require_once '../includes/footer.php';
?>
