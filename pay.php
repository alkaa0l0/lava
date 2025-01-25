<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Пользователь не определён.");
}
$user_id = $_SESSION['user_id'];

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("DB error: ".$conn->connect_error);
}

$sql_sum = "
  SELECT SUM(s.price)
  FROM numbers n
  JOIN number_services ns ON (n.id=ns.number_id)
  JOIN services s ON (ns.service_id=s.id)
  WHERE n.user_id=? AND n.is_paid=0
";
$stmt = $conn->prepare($sql_sum);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_price);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$total_price || $total_price<=0) {
    echo "Нет неоплаченных номеров! <a href='index.html'>На главную</a>";
    exit();
}

$apiToken = CRYPTOBOT_API_KEY;
$invoiceUrl = "https://pay.crypt.bot/api/createInvoice";

$invoice_data = [
    "asset" => "USDT",
    "amount" => (string)$total_price,
    "description" => "Оплата номеров LAVINA SCAMA",
    "hidden_message" => "Спасибо за оплату!",
    "callback_url" => CRYPTOBOT_CALLBACK_URL . "?user_id=$user_id",
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
if (curl_errno($ch)) {
    die("Ошибка cURL: " . curl_error($ch));
}
curl_close($ch);

$resp_data = json_decode($response, true);
if (empty($resp_data['ok'])) {
    die("Ошибка CryptoBot: " . print_r($resp_data, true));
}

$pay_url = $resp_data['result']['bot_invoice_url'] ?? '';
if (!$pay_url) {
    die("Не удалось получить ссылку на оплату.");
}

header("Location: $pay_url");
exit();