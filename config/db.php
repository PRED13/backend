<?php
// config/db.php

// Obtenemos las variables desde Render
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$port = getenv('DB_PORT') ?: '3306';

try {
    // Definimos el DSN con el charset correcto
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    // Opciones necesarias para la conexión segura (SSL) requerida por Aiven
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Configuración SSL
        PDO::MYSQL_ATTR_SSL_CA       => '/etc/ssl/certs/ca-certificates.crt',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
    ];

    // Creamos la conexión PDO
    $pdo = new PDO($dsn, $username, $password, $options);

} catch(PDOException $e) {
    // Error detallado para depuración técnica
    die("Error de conexión detallado: " . $e->getMessage());
}
?>