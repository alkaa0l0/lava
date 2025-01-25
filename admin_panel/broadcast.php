<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin.php');
    exit();
}

// Проверка роли
if ($_SESSION['role'] !== 'Создатель') {
    echo 'Доступ запрещен';
    exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $photoPath = null;

    // Проверка на загрузку изображения
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/broadcast_photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $photoPath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
            $error_message = "Ошибка загрузки изображения.";
        }
    }

    if (!empty($message) || $photoPath) {
        // Получение chat_id всех клиентов
        $stmt = $pdo->query("SELECT chat_id FROM users WHERE chat_id IS NOT NULL");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $bot_token = '7891982880:AAFTtHk2ZiEKCY4onm3vCHJSIfKYpTPfibk'; // Убедитесь, что токен правильный
        $url = "https://api.telegram.org/bot$bot_token/";

        foreach ($users as $user) {
            $chat_id = $user['chat_id'];

            // Если есть фото, отправляем его вместе с текстом
            if ($photoPath) {
                $urlToSend = $url . 'sendPhoto';
                $data = [
                    'chat_id' => $chat_id,
                    'caption' => $message,
                ];
                $cFile = curl_file_create(realpath($photoPath));
                $data['photo'] = $cFile;
            } else {
                // Если фото нет, отправляем только текст
                $urlToSend = $url . 'sendMessage';
                $data = [
                    'chat_id' => $chat_id,
                    'text' => $message,
                ];
            }

            $ch = curl_init($urlToSend);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            file_put_contents('broadcast.log', "Chat ID: $chat_id, Response: $response" . PHP_EOL, FILE_APPEND);
            curl_close($ch);

            // Задержка между отправками
            sleep(1);
        }

        $success_message = "Сообщение успешно отправлено всем клиентам.";
    } else {
        $error_message = "Введите текст сообщения или добавьте изображение.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рассылки</title>
    <style>
        body {
            background: #161622;
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            background: #27273A;
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        textarea, input[type="file"] {
            width: 100%;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background: #1E1E2D;
            color: white;
        }
        button {
            padding: 10px 20px;
            background: #0E5AE5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #084B8A;
        }
        .message {
            margin: 10px 0;
        }
        .success {
            color: #4CAF50;
        }
        .error {
            color: #FF5252;
        }
    </style>
</head>
<body>
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
    
<div class="container">
    <h1>Рассылка сообщений</h1>
    <?php if (!empty($success_message)): ?>
        <p class="message success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <p class="message error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <textarea name="message" placeholder="Введите текст сообщения..."></textarea>
        <input type="file" name="photo" accept="image/*">
        <button type="submit">Отправить</button>
    </form>
</div>
</body>
</html>
