<?php
session_start();
require 'config.php';

// Получение username из профиля
$usernameFromProfile = $_GET['username'] ?? '';
$error = '';

// Проверка, передан ли username
if (empty($usernameFromProfile)) {
    $error = "Ошибка: username не передан.";
} else {
    // Подключение к базе данных
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Проверка подключения
    if ($conn->connect_error) {
        die("Ошибка подключения к базе данных: " . $conn->connect_error);
    }

    // Получение информации о пользователе
    $stmt = $conn->prepare("SELECT id, balance FROM users WHERE username = ?");
    $stmt->bind_param("s", $usernameFromProfile);
    $stmt->execute();
    $stmt->bind_result($userId, $balance);
    $stmt->fetch();

    if (!$userId) {
        $error = "Ошибка: Пользователь с username '$usernameFromProfile' не найден.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavina Scama - Кошелёк</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #161622;
            font-family: Arial, sans-serif;
            color: white;
        }
        .phone-frame {
            width: 100%;
            max-width: 430px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #161622;
            min-height: 100vh;
        }
        header {
            height: 60px;
            width: 100%;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            box-sizing: border-box;
        }
        header h1 {
            font-size: 20px;
            color: white;
        }
        .card {
            width: 90%;
            height: 200px;
            margin-top: 20px;
            background: url('img/worldmap.png') no-repeat center;
            background-size: cover;
            border-radius: 16px;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }
        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 16px;
        }
        .card-content {
            position: relative;
            z-index: 1;
        }
        .balance {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-around;
            width: 90%;
        }
        .button {
            width: 45%;
            height: 50px;
            background: #0066FF;
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            line-height: 50px;
            cursor: pointer;
        }
        footer {
            margin-top: auto;
            height: 83px;
            background: #27273A;
            width: 100%;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        footer a {
            text-decoration: none;
            text-align: center;
            color: #949494;
            font-size: 12px;
        }
        footer a img {
            width: 24px;
            height: 24px;
            margin-bottom: 4px;
        }
        .error {
            background: #FF5252;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="phone-frame">
    <header>
        <a href="index.php">
            <img src="img/back.png" alt="Back" style="width: 28px; height: 28px;">
        </a>
        <h1>Мой Кошелёк</h1>
    </header>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php else: ?>
        <div class="card">
            <div class="card-overlay"></div>
            <div class="card-content">
                <div>
                    <span>Пользователь: <?php echo htmlspecialchars($usernameFromProfile, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="balance">Баланс: <?php echo number_format($balance, 2); ?> $</div>
                <div>
                    <span style="font-size: 12px;">4562 1122 4595 7852</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer>
        <a href="profile.html">
            <img src="img/first_icon.png" alt="Home">
            <span>Главная</span>
        </a>
        <a href="news.html">
            <img src="img/news_icon.png" alt="News">
            <span>Новости</span>
        </a>
        <a href="index.html" class="footer-profile">
            <img src="img/profile_icon.png" alt="Profile">
            <span>Профиль</span>
        </a>
    </footer>
</div>
</body>
</html>