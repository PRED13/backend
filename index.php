<?php
// index.php - Router profesional con soporte CORS y manejo de parámetros

// 1. Configuración de cabeceras CORS (restringido a tu dominio de Vercel)
// NOTA: Es vital que coincida exactamente con tu URL, sin la barra inclinada al final.
header("Access-Control-Allow-Origin: https://frontend-one-xi-878hgptzpo.vercel.app");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// 2. Manejo de la petición pre-vuelo (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Si usas credenciales (cookies/auth), podrías necesitar:
    // header("Access-Control-Allow-Credentials: true");
    http_response_code(200);
    exit;
}

// 3. Limpieza de la ruta
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 4. Enrutamiento
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
    case '/api/payments':
        require 'api/payments.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada", "ruta_solicitada" => $request]);
        break;
}
?>