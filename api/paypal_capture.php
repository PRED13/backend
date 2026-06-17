<?php

header('Content-Type: application/json');
require_once '../config/db.php';

$clientId = "ATuHDcR3OTiKtFh-AHWJywX3v0ag_QOqsj-67e_yv7pIN1ebegibXbJ6V_vnajrjZdWRGFCbjfCI5W5L";
$secret = "ECrsf2ssFdlRPpeggMBfn9xREUFKGFi0DaWeD0k57A1mX4BWihxVeM3CuBRWhfiYXVZGxTNqxUT5j0IY";

$data = json_decode(file_get_contents("php://input"), true);

$orderId = $data['order_id'] ?? null;
$userId = $data['user_id'] ?? null;

if (!$orderId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'order_id requerido'
    ]);
    exit;
}

/* Obtener token */

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://api-m.sandbox.paypal.com/v1/oauth2/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $clientId . ":" . $secret,
    CURLOPT_POSTFIELDS => "grant_type=client_credentials"
]);

$tokenResponse = json_decode(curl_exec($ch), true);
curl_close($ch);


$accessToken = $tokenResponse['access_token'];

/* Capturar orden */

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://api-m.sandbox.paypal.com/v2/checkout/orders/$orderId/capture",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ]
]);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (
    isset($response['status']) &&
    $response['status'] === 'COMPLETED' &&
    $userId
) {

    $stmt = $pdo->prepare("
        UPDATE users
        SET is_premium = 1
        WHERE id = ?
    ");

    $stmt->execute([$userId]);
}

echo json_encode($response);