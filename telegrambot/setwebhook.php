<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$bot_token = '7891982880:AAFTtHk2ZiEKCY4onm3vCHJSIfKYpTPfibk';
$webhook_url = 'https://autotrailerkz.pro/telegrambot/bot.php';

$api_url = "https://api.telegram.org/bot$bot_token/setWebhook?url=" . urlencode($webhook_url);

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Ошибка cURL: ' . curl_error($ch);
} else {
    $response_data = json_decode($response, true);
    if (isset($response_data['ok']) && $response_data['ok'] === true) {
        echo "Webhook установлен успешно!";
    } else {
        echo "Ошибка при установке webhook: " . ($response_data['description'] ?? 'Unknown');
    }
}
curl_close($ch);