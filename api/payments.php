<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        if (!$user_id) {
            http_response_code(400);
            echo json_encode(["error" => "user_id es requerido"]);
            break;
        }
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $history = $stmt->fetchAll();
        echo json_encode(["data" => $history]);
        break;

    case 'POST':
        $user_id = $input['user_id'] ?? null;
        $transaction_id = $input['transaction_id'] ?? null;
        $amount = $input['amount'] ?? null;
        $payment_method = $input['payment_method'] ?? 'mercado_pago';
        $status = $input['status'] ?? null; 

        if (!$user_id || !$transaction_id || !$amount || !$status) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos requeridos"]);
            break;
        }

        // MODIFICACIÓN: Aceptamos 'approved' o 'COMPLETED' (para PayPal)
        $is_success = ($status === 'approved' || $status === 'COMPLETED');

        if (!$is_success) {
            http_response_code(400);
            echo json_encode(["error" => "El pago no ha sido aprobado. Estatus: " . $status]);
            break;
        }

        try {
            $pdo->beginTransaction();

            // 1. Verificar si la transacción ya fue registrada (evitar duplicidad)
            $check = $pdo->prepare("SELECT id FROM payments WHERE transaction_id = ?");
            $check->execute([$transaction_id]);
            if ($check->fetch()) {
                throw new Exception("La transacción ya fue procesada anteriormente.");
            }

            // 2. Insertar pago
            $stmtPayment = $pdo->prepare("INSERT INTO payments (user_id, transaction_id, amount, payment_method, status) VALUES (?, ?, ?, ?, 'success')");
            $stmtPayment->execute([$user_id, $transaction_id, $amount, $payment_method]);

            // 3. Actualizar usuario
            $stmtUser = $pdo->prepare("UPDATE users SET is_premium = TRUE WHERE id = ?");
            $stmtUser->execute([$user_id]);

            $pdo->commit();

            echo json_encode([
                "message" => "¡Pago procesado con éxito!",
                "transaction_id" => $transaction_id
            ]);

        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["error" => "Error: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>