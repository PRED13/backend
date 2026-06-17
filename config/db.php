<?php
// config/db.php

// Obtenemos los valores de las variables de entorno configuradas en Render
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    // Es recomendable añadir el puerto si el proveedor te lo indica (por defecto es 3306)
    $port = getenv('DB_PORT') ?: '3306'; 
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    // En producción, es buena práctica no exponer el mensaje detallado del error
    // para evitar fugas de información sobre la estructura de la base de datos.
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos.");
}
?>