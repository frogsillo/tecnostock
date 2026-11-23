<?php
session_start();

// Si el usuario ya está logueado, lo redirigimos al dashboard
if (isset($_SESSION['id_usuario'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

require_once '../config/conexion.php';

$error = '';

// Verificamos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $clave  = trim($_POST['clave']);

    if (empty($correo) || empty($clave)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        // Buscamos el usuario por correo (usando consulta preparada)
        $sql = "SELECT id_usuario, nombre, clave, estado FROM usuario WHERE correo = :correo LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            if ($usuario['estado'] == 0) {
                $error = "Tu usuario está desactivado. Contacta al administrador.";
            } elseif (password_verify($clave, $usuario['clave'])) {
                // ¡AUDITORÍA DE SEGURIDAD!
                // Regeneramos el ID de sesión para prevenir ataques de fijación de sesión (Session Fixation).
                session_regenerate_id(true);

                // Contraseña correcta: iniciamos sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['nombre'];

                // Redirigimos al dashboard
                header("Location: ../dashboard/index.php");
                exit;
            } else {
                $error = "Credenciales inválidas.";
            }
        } else {
            $error = "Credenciales inválidas.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TecnoStock</title>

    <!-- Tipografía -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="/tecnostock/assets/css/estilos.css">
</head>
<body>

    <div class="auth-split">

        <!-- Panel de marca (solo en pantallas grandes) -->
        <aside class="auth-aside d-none d-lg-flex">
            <span class="brand">
                <span class="brand-mark"><i class="bi bi-box-seam"></i></span>
                TecnoStock
            </span>

            <div>
                <h2>Tu inventario, claro y al día.</h2>
                <ul class="auth-points">
                    <li><i class="bi bi-box"></i><span>Catálogo de productos y categorías en un solo lugar.</span></li>
                    <li><i class="bi bi-arrow-left-right"></i><span>Entradas y salidas registradas con fecha y usuario.</span></li>
                    <li><i class="bi bi-exclamation-triangle"></i><span>Alertas visuales cuando el stock baja del mínimo.</span></li>
                </ul>
            </div>

            <small>Inventario de accesorios tecnológicos</small>
        </aside>

        <!-- Formulario -->
        <main class="auth-main">
            <div class="auth-card">

                <div class="auth-brand-mobile d-lg-none">
                    <span class="brand-mark"><i class="bi bi-box-seam"></i></span>
                    TecnoStock
                </div>

                <h1>Iniciar sesión</h1>
                <p class="auth-sub">Ingresa con tu correo y contraseña.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="correo" name="correo" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" autocomplete="username" autofocus required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="clave" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="clave" name="clave" autocomplete="current-password" required>
                            <button type="button" class="btn btn-outline-secondary" id="verClave" aria-label="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Ingresar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar / ocultar contraseña
        document.getElementById('verClave').addEventListener('click', function () {
            var campo = document.getElementById('clave');
            var oculto = campo.type === 'password';
            campo.type = oculto ? 'text' : 'password';
            this.querySelector('i').className = oculto ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>
</html>
