<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

$error = '';
$codigo = $nombre = $descripcion = '';
$precio = $stock_actual = $stock_minimo = 0;
$id_categoria = '';

// Traemos las categorías activas para el dropdown
$stmt_cat = $pdo->query("SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre ASC");
$categorias = $stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpiamos los datos
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_categoria = $_POST['id_categoria'];

    // Validaciones
    if (empty($codigo) || empty($nombre) || empty($id_categoria)) {
        $error = "El código, nombre y categoría son obligatorios.";
    } elseif (!is_numeric($precio) || $precio < 0 || !is_numeric($stock_actual) || $stock_actual < 0 || !is_numeric($stock_minimo) || $stock_minimo < 0) {
        $error = "El precio y los stocks deben ser números mayores o iguales a cero."; // RN02
    } else {
        // Validación RN01: Código Único
        $stmt_check = $pdo->prepare("SELECT id_producto FROM producto WHERE codigo = :codigo");
        $stmt_check->execute([':codigo' => $codigo]);
        
        if ($stmt_check->fetch()) {
            $error = "El código ingresado ya existe en otro producto.";
        } else {
            // Insertar producto
            $sql = "INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) 
                    VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, 1, :id_categoria)";
            $stmt = $pdo->prepare($sql);
            
            try {
                $stmt->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':descripcion' => empty($descripcion) ? null : $descripcion,
                    ':precio' => $precio,
                    ':stock_actual' => $stock_actual,
                    ':stock_minimo' => $stock_minimo,
                    ':id_categoria' => $id_categoria
                ]);
                
                $_SESSION['mensaje'] = "Producto creado correctamente.";
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
    <div class="col-md-10 col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-plus-circle"></i> Nuevo Producto</h3>
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
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="codigo" name="codigo" value="<?= htmlspecialchars($codigo) ?>" required maxlength="50">
                        </div>
                        <div class="col-md-8">
                            <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required maxlength="150">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                            <option value="">Seleccione una categoría...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" <?= $id_categoria == $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="255"><?= htmlspecialchars($descripcion) ?></textarea>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" value="<?= htmlspecialchars($precio) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="stock_actual" class="form-label">Stock Actual <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" id="stock_actual" name="stock_actual" value="<?= htmlspecialchars($stock_actual) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="stock_minimo" class="form-label">Stock Mínimo <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" id="stock_minimo" name="stock_minimo" value="<?= htmlspecialchars($stock_minimo) ?>" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
