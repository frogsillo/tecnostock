<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

$error = '';

// Verificar que venga un ID válido
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['mensaje'] = "ID de categoría inválido.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

//  Buscar en la base de datos
$stmt = $pdo->prepare("SELECT id_categoria, nombre, descripcion FROM categoria WHERE id_categoria = :id");
$stmt->execute([':id' => $id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    $_SESSION['mensaje'] = "La categoría no existe.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

//  Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio.";
    } else {
        // Verificar que el nombre no esté usado
        $stmt_check = $pdo->prepare("SELECT id_categoria FROM categoria WHERE nombre = :nombre AND id_categoria != :id");
        $stmt_check->execute([':nombre' => $nombre, ':id' => $id]);

        if ($stmt_check->fetch()) {
            $error = "Ya existe otra categoría con ese nombre.";
        } else {
            // Actualizar en la base de datos
            $sql = "UPDATE categoria SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id";
            $stmt_update = $pdo->prepare($sql);

            try {
                $stmt_update->execute([
                    ':nombre' => $nombre,
                    ':descripcion' => empty($descripcion) ? null : $descripcion,
                    ':id' => $id
                ]);

                $_SESSION['mensaje'] = "Categoría actualizada correctamente.";
                $_SESSION['tipo_alerta'] = "success";
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $error = "Error al actualizar en la base de datos.";
            }
        }
    }
    $categoria['nombre'] = $nombre;
    $categoria['descripcion'] = $descripcion;
}
?>

<!-- Contenido del formulario -->
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="page-header">
            <div>
                <h1>Editar categoría</h1>
                <p>Cambia el nombre o la descripción de la categoría.</p>
            </div>
            <div class="actions">
                <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la categoría <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required maxlength="100">
                        <div class="form-text">El nombre no puede repetirse.</div>
                    </div>

                    <div class="mb-2">
                        <label for="descripcion" class="form-label">Descripción <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="255"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
