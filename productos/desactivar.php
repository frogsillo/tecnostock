<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

// 1. Verificar que el ID sea válido
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['mensaje'] = "ID de producto inválido.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

// 2. Verificar si el producto existe
$stmt = $pdo->prepare("SELECT id_producto, estado FROM producto WHERE id_producto = :id");
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();

if (!$producto) {
    $_SESSION['mensaje'] = "El producto no existe.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

// 3. Si ya está inactivo, no hacemos nada (por seguridad)
if ($producto['estado'] == 0) {
    $_SESSION['mensaje'] = "El producto ya se encuentra inactivo.";
    $_SESSION['tipo_alerta'] = "warning";
    header("Location: index.php");
    exit;
}

// 4. Procedemos a desactivar (estado = 0) en lugar de hacer un DELETE
// Esto cumple con la RN05: conservar los movimientos históricos.
$stmt_update = $pdo->prepare("UPDATE producto SET estado = 0 WHERE id_producto = :id");
$stmt_update->execute([':id' => $id]);

$_SESSION['mensaje'] = "Producto desactivado correctamente. Sus movimientos se conservan en el historial.";
$_SESSION['tipo_alerta'] = "success";
header("Location: index.php");
exit;
?>
