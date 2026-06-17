<?php
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
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$input = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($method) {
    case 'GET':
        if (!$user_id) {
            http_response_code(400);
            echo json_encode(["error" => "user_id es requerido"]);
            break;
        }

        $folder_id = isset($_GET['folder_id']) ? $_GET['folder_id'] : null;
        if ($folder_id === 'null') {
            $sql = "SELECT * FROM notes WHERE user_id = ? AND folder_id IS NULL ORDER BY id DESC";
            $params = [$user_id];
        } elseif ($folder_id !== null && $folder_id !== '') {
            $sql = "SELECT * FROM notes WHERE user_id = ? AND folder_id = ? ORDER BY id DESC";
            $params = [$user_id, intval($folder_id)];
        } else {
            $sql = "SELECT * FROM notes WHERE user_id = ? ORDER BY id DESC";
            $params = [$user_id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(["data" => $stmt->fetchAll()]);
        break;

    case 'POST':
        $user_id_post = $input['user_id'] ?? null;
        $title = $input['title'] ?? 'Sin título';
        $content = $input['content'] ?? '';
        $priority = $input['priority'] ?? 'bajo';
        $folder_id = isset($input['folder_id']) ? ($input['folder_id'] === 'null' ? null : intval($input['folder_id'])) : null;

        if (!$user_id_post) {
            http_response_code(400);
            echo json_encode(["error" => "user_id es requerido"]);
            break;
        }

        $stmtUser = $pdo->prepare("SELECT is_premium FROM users WHERE id = ?");
        $stmtUser->execute([$user_id_post]);
        $user = $stmtUser->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(["error" => "Usuario no encontrado"]);
            break;
        }

        if (!$user['is_premium']) {
            $stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM notes WHERE user_id = ?");
            $stmtCount->execute([$user_id_post]);
            $totalNotes = $stmtCount->fetchColumn();

            if ($totalNotes >= 10) {
                http_response_code(403);
                echo json_encode(["error" => "Límite alcanzado. Los usuarios gratis pueden crear hasta 10 notas. Actualiza a Premium para notas ilimitadas."]);
                break;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO notes (user_id, folder_id, title, content, priority) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id_post, $folder_id, $title, $content, $priority])) {
            echo json_encode(["message" => "Nota creada", "id" => $pdo->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al crear la nota"]);
        }
        break;

    case 'PUT':
        $note_id = $input['id'] ?? null;
        $user_id_put = $input['user_id'] ?? null;
        $title = $input['title'] ?? 'Sin título';
        $content = $input['content'] ?? '';
        $priority = $input['priority'] ?? 'bajo';
        $folder_id = isset($input['folder_id']) ? ($input['folder_id'] === 'null' ? null : intval($input['folder_id'])) : null;

        if (!$note_id || !$user_id_put) {
            http_response_code(400);
            echo json_encode(["error" => "id y user_id son requeridos"]);
            break;
        }

        $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ?, priority = ?, folder_id = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$title, $content, $priority, $folder_id, $note_id, $user_id_put])) {
            echo json_encode(["message" => "Nota actualizada"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar la nota"]);
        }
        break;

    case 'DELETE':
        $note_id = $input['id'] ?? null;
        $user_id_delete = $input['user_id'] ?? null;

        if (!$note_id || !$user_id_delete) {
            http_response_code(400);
            echo json_encode(["error" => "id y user_id son requeridos"]);
            break;
        }

        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$note_id, $user_id_delete])) {
            echo json_encode(["message" => "Nota eliminada"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al eliminar la nota"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>