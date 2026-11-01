<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

$error = '';

// 1. Verificar que venga un ID válido
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['mensaje'] = "ID de producto inválido.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

// 2. Buscar el producto en la base de datos
$stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = :id");
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();

if (!$producto) {
    $_SESSION['mensaje'] = "El producto no existe.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

// Traemos todas las categorías para el dropdown
$stmt_cat = $pdo->query("SELECT id_categoria, nombre, estado FROM categoria ORDER BY nombre ASC");
$categorias = $stmt_cat->fetchAll();

// 3. Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_categoria = $_POST['id_categoria'];

    // Validaciones (RN01 y RN02)
    if (empty($codigo) || empty($nombre) || empty($id_categoria)) {
        $error = "El código, nombre y categoría son obligatorios.";
    } elseif (!is_numeric($precio) || $precio < 0 || !is_numeric($stock_actual) || $stock_actual < 0 || !is_numeric($stock_minimo) || $stock_minimo < 0) {
        $error = "El precio y los stocks deben ser números mayores o iguales a cero.";
    } else {
        // Verificar que el código no esté duplicado en OTRO producto
        $stmt_check = $pdo->prepare("SELECT id_producto FROM producto WHERE codigo = :codigo AND id_producto != :id");
        $stmt_check->execute([':codigo' => $codigo, ':id' => $id]);
        
        if ($stmt_check->fetch()) {
            $error = "El código ingresado ya existe en otro producto.";
        } else {
            // Actualizar en la base de datos
            $sql = "UPDATE producto SET 
                        codigo = :codigo, 
                        nombre = :nombre, 
                        descripcion = :descripcion, 
                        precio = :precio, 
                        stock_actual = :stock_actual, 
                        stock_minimo = :stock_minimo, 
                        id_categoria = :id_categoria 
                    WHERE id_producto = :id";
            
            $stmt_update = $pdo->prepare($sql);
            
            try {
                $stmt_update->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':descripcion' => empty($descripcion) ? null : $descripcion,
                    ':precio' => $precio,
                    ':stock_actual' => $stock_actual,
                    ':stock_minimo' => $stock_minimo,
                    ':id_categoria' => $id_categoria,
                    ':id' => $id
                ]);
                
                $_SESSION['mensaje'] = "Producto actualizado correctamente.";
                $_SESSION['tipo_alerta'] = "success";
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $error = "Error al actualizar en la base de datos.";
            }
        }
    }
    
    // Si hubo error, actualizamos los datos para que no se pierda lo escrito
    $producto = array_merge($producto, $_POST);
}
?>

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-pencil-square"></i> Editar Producto: <?= htmlspecialchars($producto['codigo']) ?></h3>
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
                            <input type="text" class="form-control" id="codigo" name="codigo" value="<?= htmlspecialchars($producto['codigo']) ?>" required maxlength="50">
                        </div>
                        <div class="col-md-8">
                            <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required maxlength="150">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                            <option value="">Seleccione una categoría...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" <?= $producto['id_categoria'] == $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?> <?= $cat['estado'] == 0 ? '(Inactiva)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="255"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" value="<?= htmlspecialchars($producto['precio']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="stock_actual" class="form-label">Stock Actual <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" id="stock_actual" name="stock_actual" value="<?= htmlspecialchars($producto['stock_actual']) ?>" required>
                            <small class="text-muted">Modificar aquí no genera movimiento.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="stock_minimo" class="form-label">Stock Mínimo <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" id="stock_minimo" name="stock_minimo" value="<?= htmlspecialchars($producto['stock_minimo']) ?>" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
