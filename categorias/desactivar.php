<?php
require_once '../includes/header.php';
require_once '../config/conexion.php';

// ID  válido
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['mensaje'] = "ID de categoría inválido.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

// categoría existe ono
$stmt = $pdo->prepare("SELECT id_categoria, estado FROM categoria WHERE id_categoria = :id");
$stmt->execute([':id' => $id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    $_SESSION['mensaje'] = "La categoría no existe.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}
// cuántos productos pertenecen a esta categoría
$stmt_prod = $pdo->prepare("SELECT COUNT(*) FROM producto WHERE id_categoria = :id");
$stmt_prod->execute([':id' => $id]);
$cantidad_productos = $stmt_prod->fetchColumn();

if ($cantidad_productos > 0) {
    // Si tiene productos, bloqueamos la acción
    $_SESSION['mensaje'] = "No se puede desactivar la categoría porque tiene $cantidad_productos producto(s) asociado(s).";
    $_SESSION['tipo_alerta'] = "warning"; // Amarillo para advertencia
    header("Location: index.php");
    exit;
}

// 4. Si no tiene productos, procedemos a desactivar )
$stmt_update = $pdo->prepare("UPDATE categoria SET estado = 0 WHERE id_categoria = :id");
$stmt_update->execute([':id' => $id]);

$_SESSION['mensaje'] = "Categoría desactivada correctamente.";
$_SESSION['tipo_alerta'] = "success";
header("Location: index.php");
exit;
?>
