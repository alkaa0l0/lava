<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Вы не авторизованы.");
}

$user_id = $_SESSION['user_id'];
$service_id = intval($_POST['service']);
$telegram_username = trim($_POST['telegram_username']);

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных.");
}

// Проверка баланса
$stmt = $conn->prepare("
    SELECT SUM(s.price)
    FROM numbers n
    JOIN number_services ns ON n.id = ns.number_id
    JOIN services s ON ns.service_id = s.id
    WHERE n.user_id = ? AND n.is_paid = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_debt);
$stmt->fetch();
$stmt->close();

if ($total_debt > 0) {
    die("Вы не можете взять новый физ. У вас есть задолженность в размере $total_debt $.");
}

// Проверка существующего пользователя
$stmt = $conn->prepare("SELECT id FROM users WHERE telegram_username = ?");
$stmt->bind_param("s", $telegram_username);
$stmt->execute();
$stmt->bind_result($existing_user_id);
$stmt->fetch();
$stmt->close();

if (!$existing_user_id || $existing_user_id !== $user_id) {
    die("Пользователь не найден или не соответствует.");
}

// Добавление нового физа
$stmt = $conn->prepare("INSERT INTO numbers (number, user_id, is_paid) VALUES (?, ?, 0)");
$number = rand(1000, 9999); // Пример случайного номера
$stmt->bind_param("ii", $number, $user_id);
$stmt->execute();
$new_number_id = $stmt->insert_id;
$stmt->close();

$stmt = $conn->prepare("INSERT INTO number_services (number_id, service_id) VALUES (?, ?)");
$stmt->bind_param("ii", $new_number_id, $service_id);
$stmt->execute();
$stmt->close();

echo "Новый физ успешно добавлен. Ваш номер: $number.";
$conn->close();
?>