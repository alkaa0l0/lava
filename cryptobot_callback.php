<?php
require 'config.php';

$input = file_get_contents("php://input");
$update = json_decode($input, true);

if (!$update || ($update['update_type'] ?? '') !== 'invoice_paid') {
    die("Некорректные данные.");
}

$signature = $_SERVER['HTTP_CRYPTO_PAY_API_SIGNATURE'] ?? '';
$secret = hash('sha256', CRYPTOBOT_API_KEY, true);
$calculated_signature = hash_hmac('sha256', $input, $secret);

if (!hash_equals($calculated_signature, $signature)) {
    die("Неверная подпись.");
}

$invoice = $update['payload'] ?? [];
$user_id = $invoice['payload'] ?? 0;
$amount = $invoice['amount'] ?? 0;

if (!$user_id || !$amount) {
    die("Некорректные данные пользователя или сумма.");
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("DB error: " . $conn->connect_error);
}

// Update user's balance
$stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
$stmt->bind_param("di", $amount, $user_id);
$stmt->execute();
$stmt->close();

$conn->close();
echo "OK";