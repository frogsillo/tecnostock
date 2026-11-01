<?php
// Configuración de la base de datos
$host     = 'localhost';
$dbname   = 'tecnostock';
$usuario  = 'root';
$clave    = ''; // En XAMPP por defecto el root no tiene contraseña

try {
    // DSN (Data Source Name): indica el motor, el host, la BD y el charset
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // Opciones de PDO para seguridad y manejo de errores
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones si hay errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los datos como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa consultas preparadas reales (seguridad)
    ];

    // Creamos la conexión
    $pdo = new PDO($dsn, $usuario, $clave, $opciones);

} catch (PDOException $e) {
    // Si hay un error, detenemos todo y mostramos el mensaje
    die("Error crítico de conexión a la base de datos: " . $e->getMessage());
}
?>
