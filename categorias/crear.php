<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

$error = '';
$nombre = '';
$descripcion = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio.";
    } else {
        // Verificar que el nombre no exista
        $stmt_check = $pdo->prepare("SELECT id_categoria FROM categoria WHERE nombre = :nombre");
        $stmt_check->execute([':nombre' => $nombre]);
        
        if ($stmt_check->fetch()) {
            $error = "Ya existe una categoría con ese nombre.";
        } else {
            // Insertar la nueva categoría 
            $sql = "INSERT INTO categoria (nombre, descripcion, estado) VALUES (:nombre, :descripcion, 1)";
            $stmt = $pdo->prepare($sql);
            
            try {
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':descripcion' => empty($descripcion) ? null : $descripcion
                ]);
                
                // Guardar mensaje de éxito en la sesión y redirigir
                $_SESSION['mensaje'] = "Categoría creada correctamente.";
                $_SESSION['tipo_alerta'] = "success";
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $error = "Error al guardar en la base de datos.";
            }
        }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-plus-circle"></i> Nueva Categoría</h3>
            <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required maxlength="100">
                    </div>
                    
                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="255"><?= htmlspecialchars($descripcion) ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Categoría</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
