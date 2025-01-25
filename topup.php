<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Вы не авторизованы.");
}

$user_id = $_SESSION['user_id'];
$amount = floatval($_POST['amount']);

if ($amount <= 0) {
    die("Некорректная сумма.");
}

// Create CryptoBot Invoice
$apiToken = CRYPTOBOT_API_KEY;
$invoiceUrl = "https://pay.crypt.bot/api/createInvoice";

$invoice_data = [
    "asset" => "USDT",
    "amount" => number_format($amount, 2, '.', ''),
    "description" => "Пополнение баланса",
    "payload" => $user_id,
    "callback_url" => CRYPTOBOT_CALLBACK_URL,
    "allow_comments" => false,
    "allow_anonymous" => false
];

$ch = curl_init($invoiceUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invoice_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Crypto-Pay-API-Token: $apiToken"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$resp_data = json_decode($response, true);

if (empty($resp_data['ok'])) {
    die("Ошибка: не удалось создать счёт.");
}

$pay_url = $resp_data['result']['pay_url'] ?? '';
header("Location: $pay_url");
exit;