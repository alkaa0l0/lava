<?php
require 'config.php';

$input = file_get_contents("php://input");
$update = json_decode($input, true);

if (!$update || !isset($update['update_id'])) {
    die("Некорректные данные.");
}

$signature = $_SERVER['HTTP_CRYPTO_PAY_API_SIGNATURE'] ?? '';
$calculated_signature = hash_hmac('sha256', $input, hash('sha256', CRYPTOBOT_API_KEY, true));

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
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обновляем баланс пользователя
$stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
$stmt->bind_param("di", $amount, $user_id);
$stmt->execute();
$stmt->close();

// Логируем транзакцию
$stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, 'Пополнение баланса')");
$stmt->bind_param("id", $user_id, $amount);
$stmt->execute();
$stmt->close();

$conn->close();
echo "OK";
?>