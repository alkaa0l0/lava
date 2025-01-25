<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin.php');
    exit();
}

require 'config.php';

// Подключение к базе данных
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Получение списка пользователей
$usersQuery = "SELECT id, telegram_username FROM users ORDER BY telegram_username ASC";
$usersResult = $conn->query($usersQuery);
$users = [];
if ($usersResult->num_rows > 0) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Обработка отправки сообщения
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'] ?? '';
    $message_text = $_POST['message_text'] ?? '';

    if (!empty($receiver_id) && !empty($message_text)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender, receiver_id, message) VALUES ('admin', ?, ?)");
        $stmt->bind_param("is", $receiver_id, $message_text);
        if ($stmt->execute()) {
            $message = 'Сообщение успешно отправлено!';
        } else {
            $message = 'Ошибка отправки сообщения.';
        }
        $stmt->close();
    } else {
        $message = 'Пожалуйста, заполните все поля.';
    }
}

// Получение сообщений для выбранного клиента
$selected_user_id = $_GET['user_id'] ?? null;
$chatMessages = [];
if ($selected_user_id) {
    $chatQuery = "
        SELECT m.sender, m.message, m.sent_at
        FROM messages m
        WHERE m.receiver_id = ?
        ORDER BY m.sent_at ASC
    ";
    $stmtChat = $conn->prepare($chatQuery);
    $stmtChat->bind_param("i", $selected_user_id);
    $stmtChat->execute();
    $chatResult = $stmtChat->get_result();
    while ($row = $chatResult->fetch_assoc()) {
        $chatMessages[] = $row;
    }
    $stmtChat->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мессенджер с клиентами</title>
    <style>
        body {
            background: #161622;
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
        }
        .users-list {
            margin-bottom: 20px;
        }
        .users-list a {
            display: block;
            padding: 10px;
            background: #27273A;
            color: white;
            text-decoration: none;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .users-list a:hover {
            background: #1E1E2D;
        }
        .chat {
            margin-top: 20px;
            background: #27273A;
            padding: 20px;
            border-radius: 8px;
        }
        .chat-messages {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .chat-messages .message {
            margin-bottom: 10px;
        }
        .chat-messages .admin {
            text-align: right;
            color: #00FF00;
        }
        .chat-messages .client {
            text-align: left;
            color: #0066FF;
        }
        .chat-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-form textarea {
            resize: none;
            padding: 10px;
            background: #1E1E2D;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .chat-form button {
            padding: 10px;
            background: #0066FF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Мессенджер с клиентами</h1>

<!-- Кнопка «Назад» -->
<a href="admin-panel.php" style="
      display: inline-flex;
      width: 42px; height: 42px;
      background: #1E1E2D;
      border-radius: 9999px;
      border: 1px solid #ffffff;
      text-decoration: none;
      position: relative;
      align-items: center;
      justify-content: center;
    ">
      <!-- Иконка back.png -->
      <img
        src="img/back.png"
        alt="Back"
        style="width: 20px; height: 20px;"
      />
    </a>

        <div class="users-list">
            <h2>Список клиентов</h2>
            <?php foreach ($users as $user): ?>
                <a href="?user_id=<?= htmlspecialchars($user['id']) ?>">
                    <?= htmlspecialchars($user['telegram_username']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($selected_user_id): ?>
            <div class="chat">
                <h2>Чат с клиентом</h2>
                <div class="chat-messages">
                    <?php foreach ($chatMessages as $msg): ?>
                        <div class="message <?= htmlspecialchars($msg['sender']) ?>">
                            <strong><?= $msg['sender'] === 'admin' ? 'Вы' : 'Клиент' ?>:</strong>
                            <p><?= htmlspecialchars($msg['message']) ?></p>
                            <small><?= htmlspecialchars($msg['sent_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="POST" class="chat-form">
                    <textarea name="message_text" rows="3" placeholder="Введите сообщение" required></textarea>
                    <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($selected_user_id) ?>">
                    <button type="submit">Отправить</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <p style="color: #00FF00;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>