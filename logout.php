<?php
// 1. Iniciar la sesión para poder destruirla
session_start();

// 2. Vaciar todas las variables de sesión
$_SESSION = array();

// 3. Destruir la sesión
session_destroy();

// 4. Redirigir al usuario a la página de login
header("Location: auth/login.php");
exit;
?>
