<?php
// Configuración de la base de datos
$host     = 'localhost';
$dbname   = 'tecnostock';
$usuario  = 'root';
$clave    = ''; 

try {
    // DSN (Data Source Name): 
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // Opciones de PDO para seguridad y manejo de errores
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
        PDO::ATTR_EMULATE_PREPARES   => false,                  
    ];

    // Creamos la conexión
    $pdo = new PDO($dsn, $usuario, $clave, $opciones);

} catch (PDOException $e) {
    // Si hay un error, detenemos todo 
    die("Error crítico de conexión a la base de datos: " . $e->getMessage());
}
?>
