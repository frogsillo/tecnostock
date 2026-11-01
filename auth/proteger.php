<?php
// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    // Si no está logueado, redirigir al login
    // Usamos ruta absoluta desde la raíz del servidor web para evitar problemas de rutas
    header("Location: /tecnostock/auth/login.php");
    exit;
}
?>
