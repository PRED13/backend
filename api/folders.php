<?php
// backend/api/folders.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cambia la línea problemática por esta:
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$user_id = $input['user_id'] ?? $user_id;
$folder_id = $input['id'] ?? (isset($_GET['id']) ? intval($_GET['id']) : null);

// Función auxiliar para validar si es premium
function checkPremium($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT is_premium FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user || !$user['is_premium']) {
        http_response_code(403);
        echo json_encode(["error" => "Acceso denegado. Esta función es exclusiva para usuarios Premium."]);
        exit();
    }
}

switch ($method) {
    case 'GET':
        if (!$user_id) {
            http_response_code(400);
            echo json_encode(["error" => "user_id es requerido"]);
            break;
        }

        checkPremium($pdo, $user_id);
        $stmt = $pdo->prepare("SELECT * FROM folders WHERE user_id = ? ORDER BY name ASC");
        $stmt->execute([$user_id]);
        echo json_encode(["data" => $stmt->fetchAll()]);
        break;

    case 'POST':
        $user_id_post = $input['user_id'] ?? null;
        $name = $input['name'] ?? null;

        if (!$user_id_post || !$name) {
            http_response_code(400);
            echo json_encode(["error" => "user_id y name son requeridos"]);
            break;
        }

        checkPremium($pdo, $user_id_post);
        $stmt = $pdo->prepare("INSERT INTO folders (user_id, name) VALUES (?, ?)");
        if ($stmt->execute([$user_id_post, $name])) {
            http_response_code(201);
            echo json_encode(["message" => "Carpeta creada", "id" => $pdo->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al crear la carpeta"]);
        }
        break;

    case 'PUT':
        $name = $input['name'] ?? null;
        $user_id_put = $input['user_id'] ?? null;

        if (!$folder_id || !$name || !$user_id_put) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos requeridos (id, name, user_id)"]);
            break;
        }

        checkPremium($pdo, $user_id_put);
        $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$name, $folder_id, $user_id_put])) {
            echo json_encode(["message" => "Carpeta actualizada"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar la carpeta"]);
        }
        break;

    case 'DELETE':
        $user_id_delete = $input['user_id'] ?? $user_id;

        if (!$folder_id || !$user_id_delete) {
            http_response_code(400);
            echo json_encode(["error" => "id de carpeta y user_id son requeridos"]);
            break;
        }

        checkPremium($pdo, $user_id_delete);
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$folder_id, $user_id_delete])) {
            echo json_encode(["message" => "Carpeta eliminada"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al eliminar la carpeta"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>