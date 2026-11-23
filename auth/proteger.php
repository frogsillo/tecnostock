<?php
// Iniciar la sesión 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /tecnostock/auth/login.php");
    exit;
}
?>
