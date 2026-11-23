<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

$error = '';
$id_producto = '';
$cantidad = 1;
$observacion = '';

// Traemos los productos ACTIVOS
$stmt_prod = $pdo->query("SELECT id_producto, codigo, nombre, stock_actual FROM producto WHERE estado = 1 ORDER BY nombre ASC");
$productos = $stmt_prod->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);
    $observacion = trim($_POST['observacion']);

    // Validaciones
    if ($id_producto <= 0 || empty($cantidad)) {
        $error = "Debe seleccionar un producto y una cantidad válida.";
    } elseif ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor a cero.";
    } else {
        try {
            // INICIO DE LA TRANSACCIÓN
            $pdo->beginTransaction();

            // Consultar el stock actual del producto
            $stmt_check = $pdo->prepare("SELECT stock_actual FROM producto WHERE id_producto = :id FOR UPDATE");
            $stmt_check->execute([':id' => $id_producto]);
            $prod_actual = $stmt_check->fetch();

            if (!$prod_actual) {
                throw new Exception("El producto seleccionado no existe.");
            }

            //  Aplicar la regla de negocio
            if ($cantidad > $prod_actual['stock_actual']) {
                throw new Exception("Stock insuficiente. Solo hay " . $prod_actual['stock_actual'] . " unidades disponibles.");
            }

            // Registrar el movimiento de salida
            $sql_mov = "INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario)
                        VALUES ('salida', :cantidad, :observacion, :id_producto, :id_usuario)";
            $stmt_mov = $pdo->prepare($sql_mov);
            $stmt_mov->execute([
                ':cantidad' => $cantidad,
                ':observacion' => empty($observacion) ? null : $observacion,
                ':id_producto' => $id_producto,
                ':id_usuario' => $_SESSION['id_usuario']
            ]);

            // Actualizar el stock
            $sql_stock = "UPDATE producto SET stock_actual = stock_actual - :cantidad WHERE id_producto = :id_producto";
            $stmt_stock = $pdo->prepare($sql_stock);
            $stmt_stock->execute([
                ':cantidad' => $cantidad,
                ':id_producto' => $id_producto
            ]);

            // 5. Confirmar transacción
            $pdo->commit();

            $_SESSION['mensaje'] = "Salida de stock registrada correctamente.";
            $_SESSION['tipo_alerta'] = "success";
            header("Location: index.php");
            exit;

        } catch (Exception $e) {
            // Si hay CUALQUIER error deshacemos los cambios
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="page-header">
            <div class="title-block">
                <span class="mov-icon is-out"><i class="bi bi-box-arrow-up"></i></span>
                <div>
                    <h1>Registrar salida</h1>
                    <p>Descuenta unidades del stock de un producto.</p>
                </div>
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

        <div class="row g-3">
            <!-- Formulario -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" action="" data-movimiento="salida">
                            <div class="mb-3">
                                <label for="id_producto" class="form-label">Producto <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_producto" name="id_producto" required>
                                    <option value="">Seleccione un producto...</option>
                                    <?php foreach ($productos as $prod): ?>
                                        <option value="<?= $prod['id_producto'] ?>"
                                                data-stock="<?= (int)$prod['stock_actual'] ?>"
                                                data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                                                <?= $id_producto == $prod['id_producto'] ? 'selected' : '' ?>>
                                            [<?= htmlspecialchars($prod['codigo']) ?>] <?= htmlspecialchars($prod['nombre']) ?> (Stock actual: <?= $prod['stock_actual'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="cantidad" class="form-label">Cantidad a retirar <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" value="<?= htmlspecialchars($cantidad) ?>" min="1" required>
                                <div class="form-text">No puedes retirar más unidades de las que hay disponibles.</div>
                            </div>

                            <div class="mb-4">
                                <label for="observacion" class="form-label">Observación <span class="text-muted fw-normal">(opcional)</span></label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="2" maxlength="255" placeholder="Ej: Venta mostrador, producto dañado..."><?= htmlspecialchars($observacion) ?></textarea>
                            </div>

                            <div class="form-actions mt-0 pt-3">
                                <a href="index.php" class="btn btn-light border">Cancelar</a>
                                <button type="submit" class="btn btn-danger"><i class="bi bi-check2 me-1"></i> Registrar salida</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Resumen en vivo -->
            <div class="col-lg-5">
                <div class="card summary-card" aria-live="polite">
                    <div class="card-body">
                        <h2 class="summary-title">Resumen del movimiento</h2>
                        <p class="summary-product" id="res-producto">Selecciona un producto para ver el resultado.</p>

                        <div class="summary-row"><span>Stock actual</span><span class="value" id="res-actual">&mdash;</span></div>
                        <div class="summary-row"><span>Salida</span><span class="value delta-out" id="res-delta">&mdash;</span></div>
                        <div class="summary-row is-total"><span>Stock resultante</span><span class="value" id="res-nuevo">&mdash;</span></div>

                        <div class="summary-warning" id="res-aviso" role="alert"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/tecnostock/assets/js/movimiento-preview.js"></script>

<?php
require_once '../includes/footer.php';
?>
