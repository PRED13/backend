<?php
// backend/api/paypal_auth.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$client_id = "TU_SANDBOX_CLIENT_ID";
$secret = "TU_SANDBOX_CLIENT_SECRET";

$ch = curl_init("https://api-m.sandbox.paypal.com/v1/oauth2/token");
curl_setopt($ch, CURLOPT_USERPWD, "$client_id:$secret");
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);

echo $res; // Devuelve el JSON con el access_token
?>