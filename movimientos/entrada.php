<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

$error = '';
$id_producto = '';
$cantidad = 1;
$observacion = '';

// Traemos los productos ACTIVOS para el dropdown
$stmt_prod = $pdo->query("SELECT id_producto, codigo, nombre, stock_actual FROM producto WHERE estado = 1 ORDER BY nombre ASC");
$productos = $stmt_prod->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);
    $observacion = trim($_POST['observacion']);

    // Validaciones (RN02 y campos obligatorios)
    if ($id_producto <= 0 || empty($cantidad)) {
        $error = "Debe seleccionar un producto y una cantidad válida.";
    } elseif ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor a cero.";
    } else {
        try {
            // INICIO DE LA TRANSACCIÓN (RN06)
            $pdo->beginTransaction();

            // 1. Registrar el movimiento
            $sql_mov = "INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario) 
                        VALUES ('entrada', :cantidad, :observacion, :id_producto, :id_usuario)";
            $stmt_mov = $pdo->prepare($sql_mov);
            $stmt_mov->execute([
                ':cantidad' => $cantidad,
                ':observacion' => empty($observacion) ? null : $observacion,
                ':id_producto' => $id_producto,
                ':id_usuario' => $_SESSION['id_usuario'] // RN03: Usuario responsable
            ]);

            // 2. Actualizar el stock del producto
            $sql_stock = "UPDATE producto SET stock_actual = stock_actual + :cantidad WHERE id_producto = :id_producto";
            $stmt_stock = $pdo->prepare($sql_stock);
            $stmt_stock->execute([
                ':cantidad' => $cantidad,
                ':id_producto' => $id_producto
            ]);

            // 3. Confirmar la transacción (si llegamos aquí, todo salió bien)
            $pdo->commit();

            $_SESSION['mensaje'] = "Entrada de stock registrada correctamente.";
            $_SESSION['tipo_alerta'] = "success";
            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            // Si algo falla, deshacemos todo para no dejar datos inconsistentes
            $pdo->rollBack();
            $error = "Error crítico al registrar la entrada: " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-success"><i class="bi bi-box-arrow-in-down"></i> Registrar Entrada</h3>
            <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 border-top border-success border-4">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="id_producto" class="form-label">Producto <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_producto" name="id_producto" required>
                            <option value="">Seleccione un producto...</option>
                            <?php foreach ($productos as $prod): ?>
                                <option value="<?= $prod['id_producto'] ?>" <?= $id_producto == $prod['id_producto'] ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($prod['codigo']) ?>] <?= htmlspecialchars($prod['nombre']) ?> (Stock actual: <?= $prod['stock_actual'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad a ingresar <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" value="<?= htmlspecialchars($cantidad) ?>" min="1" required>
                    </div>

                    <div class="mb-4">
                        <label for="observacion" class="form-label">Observación (Opcional)</label>
                        <textarea class="form-control" id="observacion" name="observacion" rows="2" maxlength="255" placeholder="Ej: Compra a proveedor, reposición de stock..."><?= htmlspecialchars($observacion) ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Registrar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
