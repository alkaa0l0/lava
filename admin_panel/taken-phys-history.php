<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin.php');
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role']; // Роль администратора

require 'config.php';

// Подключение к базе данных
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Получение истории взятых физов
$query = "SELECT t.id, t.username, t.phys_name, t.taken_date
          FROM taken_phys t
          ORDER BY t.taken_date DESC";
$result = $conn->query($query);

$physHistory = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $physHistory[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История взятых физов</title>
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
            min-height: 100vh;
        }
        h1 {
            margin-top: 20px;
        }
        .history-table {
            width: 90%;
            max-width: 800px;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .history-table th, .history-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        .history-table th {
            background: #27273A;
            color: white;
        }
        .history-table tr:nth-child(even) {
            background: #20212D;
        }
        .history-table tr:hover {
            background: #27273A;
        }
        .back-btn {
            margin-top: 20px;
            text-decoration: none;
            background: #FF5252;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
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
    
    <h1>История взятых физов</h1>
    <table class="history-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Пользователь</th>
                <th>Название физа</th>
                <th>Дата взятия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($physHistory)): ?>
                <?php foreach ($physHistory as $index => $phys): ?>
                    <tr>
                        <td><?= htmlspecialchars($index + 1) ?></td>
                        <td><?= htmlspecialchars($phys['username']) ?></td>
                        <td><?= htmlspecialchars($phys['phys_name']) ?></td>
                        <td><?= htmlspecialchars($phys['taken_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Нет записей</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>