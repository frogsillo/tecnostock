<?php
// Este archivo no muestra HTML: procesa la acción y responde.
//  - Con JavaScript (fetch): responde JSON y la página no se recarga.
//  - Sin JavaScript (formulario normal): guarda el mensaje en sesión y redirige al listado.
// IMPORTANTE: no debe haber espacios ni líneas en blanco antes de "<?php" ni después
// del último código; cualquier carácter extra se mezclaría con el JSON y lo rompería.
require_once __DIR__ . '/../auth/proteger.php';
require_once '../config/conexion.php';

// ¿La petición viene de fetch? Esta cabecera solo decide el FORMATO de la respuesta.
// La seguridad la da el token CSRF, no esta cabecera.
$es_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

/**
 * Termina la petición con el resultado, en el formato que corresponda.
 */
function responder(bool $ok, string $mensaje, string $tipo, int $codigo = 200, array $extra = []): void
{
    global $es_ajax;

    if ($es_ajax) {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(
            array_merge(['ok' => $ok, 'mensaje' => $mensaje, 'tipo' => $tipo], $extra),
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    // Sin JavaScript: comportamiento original (mensaje en sesión + redirección)
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_alerta'] = $tipo;
    header("Location: index.php");
    exit;
}

// 1) Solo se acepta POST con un token CSRF válido
$token_enviado = $_POST['csrf_token'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !is_string($token_enviado)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $token_enviado)) {
    responder(false, "Solicitud no válida. Recarga la página e inténtalo de nuevo.", "danger", 403);
}

// 2) Verificar que el ID sea un entero válido
$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    responder(false, "ID de producto inválido.", "danger", 400);
}

try {
    // 3) Verificar si el producto existe
    $stmt = $pdo->prepare("SELECT id_producto, estado FROM producto WHERE id_producto = :id");
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        responder(false, "El producto no existe.", "danger", 404);
    }

    // 4) Si ya está inactivo, no hacemos nada (el listado igual lo marcará como inactivo)
    if ($producto['estado'] == 0) {
        responder(false, "El producto ya se encuentra inactivo.", "warning", 409, ['id' => $id, 'estado' => 0]);
    }

    // 5) Desactivar en lugar de hacer un DELETE: conserva los movimientos históricos
    $stmt_update = $pdo->prepare("UPDATE producto SET estado = 0 WHERE id_producto = :id");
    $stmt_update->execute([':id' => $id]);

} catch (PDOException $e) {
    // El detalle técnico va al log del servidor; al usuario, un mensaje genérico
    error_log("TecnoStock [desactivar producto]: " . $e->getMessage());
    responder(false, "No se pudo desactivar el producto. Inténtalo de nuevo.", "danger", 500);
}

responder(true, "Producto desactivado correctamente. Sus movimientos se conservan en el historial.", "success", 200, ['id' => $id, 'estado' => 0]);
