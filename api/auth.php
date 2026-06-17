<?php
// backend/api/auth.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se admite POST."]);
    exit();
}

$action = $input['action'] ?? null;
$username = isset($input['username']) ? trim($input['username']) : null;
$password = $input['password'] ?? null; // Texto plano

if (!$action || !$username || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos requeridos (action, username, password)"]);
    exit();
}

switch ($action) {
    case 'register':
        // 1. Validar si el usuario ya existe
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmtCheck->execute([$username]);
        if ($stmtCheck->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "El nombre de usuario ya está registrado."]);
            break;
        }

        // 2. Insertar directamente la contraseña en texto plano
        $stmtInsert = $pdo->prepare("INSERT INTO users (username, password, is_premium) VALUES (?, ?, FALSE)");
        if ($stmtInsert->execute([$username, $password])) {
            http_response_code(201);
            echo json_encode([
                "message" => "Usuario registrado exitosamente (Texto plano).",
                "user" => [
                    "id" => $pdo->lastInsertId(),
                    "username" => $username,
                    "is_premium" => false
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al registrar el usuario."]);
        }
        break;

    case 'login':
        // 1. Validar coincidencia directa de usuario y contraseña
        $stmtUser = $pdo->prepare("SELECT id, username, is_premium FROM users WHERE username = ? AND password = ?");
        $stmtUser->execute([$username, $password]);
        $user = $stmtUser->fetch();

        if ($user) {
            http_response_code(200);
            echo json_encode([
                "message" => "Inicio de sesión exitoso.",
                "user" => [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "is_premium" => (bool)$user['is_premium']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales incorrectas."]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Acción no válida. Use 'register' o 'login'."]);
        break;
}
?>