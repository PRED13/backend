<?php
// config/db.php

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$port = getenv('DB_PORT') ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    // Opciones para habilitar SSL (Requerido por Aiven)
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt', // Ruta estándar en contenedores Debian
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

} catch(PDOException $e) {
    //error_log("Error de conexión: " . $e->getMessage());
    //die("Error de conexión a la base de datos.");
    die("Error de conexión detallado: " . $e->getMessage());
}
?>