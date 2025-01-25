<?php
require 'config.php';

// Получаем данные из запроса
$input = file_get_contents("php://input");
$update = json_decode($input, true);

// Проверяем данные
if (!$update || ($update['update_type'] ?? '') !== 'invoice_paid') {
    die("Некорректные данные.");
}

// Проверяем подпись
$signature = $_SERVER['HTTP_CRYPTO_PAY_API_SIGNATURE'] ?? '';
$secret = hash('sha256', CRYPTOBOT_API_KEY, true);
$calculated_signature = hash_hmac('sha256', $input, $secret);

if (!hash_equals($calculated_signature, $signature)) {
    die("Неверная подпись.");
}

// Обработка успешной оплаты
$invoice = $update['payload'] ?? [];
$user_id = $invoice['payload'] ?? 0;
$amount = $invoice['amount'] ?? 0;

if (!$user_id || !$amount) {
    die("Некорректные данные пользователя или сумма.");
}

// Обновляем баланс пользователя
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

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