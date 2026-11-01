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
                // Contraseña correcta: iniciamos sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['nombre'];
                
                // Redirigimos al dashboard (lo crearemos más adelante)
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
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-box-seam"></i> TecnoStock</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="correo" name="correo" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="clave" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="clave" name="clave" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Ingresar <i class="bi bi-box-arrow-in-right"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
