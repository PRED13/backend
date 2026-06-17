<?php
// index.php - Router sencillo
header("Content-Type: application/json");

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];

// Enrutamiento básico
switch ($request) {
    case '/api/notes':
        require 'api/notes.php';
        break;
    case '/api/auth':
        require 'api/auth.php';
        break;
    case '/api/folders':
        require 'api/folders.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "Ruta no encontrada"]);
        break;
}
?>