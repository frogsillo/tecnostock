<?php
// Este archivo no muestra HTML: solo procesa la acción y redirige.
// Por eso se incluye únicamente la protección de sesión, no el header visual.
require_once __DIR__ . '/../auth/proteger.php';
require_once '../config/conexion.php';

// Solo se acepta POST con un token CSRF válido (un enlace o una imagen ajena no puede activarlo)
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['csrf_token'], $_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['mensaje'] = "Solicitud no válida. Desactiva la categoría desde el listado.";
    $_SESSION['tipo_alerta'] = "danger";
    header("Location: index.php");
    exit;
}

// ID  válido
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

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
