<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Вы не авторизованы.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        die("Некорректная сумма.");
    }

    $user_id = $_SESSION['user_id'];

    // Создание чека через API CryptoBot
    $data = [
        "asset" => "USDT",
        "amount" => number_format($amount, 2, '.', ''),
        "description" => "Пополнение баланса",
        "payload" => $user_id,
        "callback_url" => CRYPTOBOT_CALLBACK_URL,
        "allow_comments" => false,
        "allow_anonymous" => false,
    ];

    $ch = curl_init("https://pay.crypt.bot/api/createInvoice");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Crypto-Pay-API-Token: " . CRYPTOBOT_API_KEY,
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if (!empty($response_data['ok']) && isset($response_data['result']['pay_url'])) {
        header("Location: " . $response_data['result']['pay_url']);
        exit();
    } else {
        die("Ошибка создания счёта: " . ($response_data['description'] ?? 'Неизвестная ошибка'));
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пополнение баланса</title>
</head>
<body>
<h1>Пополнить баланс</h1>
<form method="POST">
    <label for="amount">Сумма пополнения:</label>
    <input type="number" step="0.01" name="amount" id="amount" required>
    <button type="submit">Пополнить</button>
</form>
</body>
</html>