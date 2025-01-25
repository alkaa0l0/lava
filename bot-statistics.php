<?php
session_start();

// Проверка роли (доступ только Создателю и adm TAGGER)
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin.php');
    exit();
}
$role = $_SESSION['role'];
if ($role !== 'Создатель' && $role !== 'adm TAGGER') {
    die("У вас нет доступа к этой странице.");
}

require 'config.php';

// Подключение к базе данных
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// 1. Получение количества активных пользователей за последние 24 часа
$activeUsersQuery = "SELECT COUNT(*) AS active_users FROM user_activity WHERE last_activity > NOW() - INTERVAL 1 DAY";
$activeUsersResult = $conn->query($activeUsersQuery);
$activeUsersCount = $activeUsersResult->fetch_assoc()['active_users'] ?? 0;

// 2. Получение данных активности для графика
$graphDataQuery = "
    SELECT HOUR(last_activity) AS activity_hour, COUNT(*) AS active_users
    FROM user_activity
    WHERE last_activity > NOW() - INTERVAL 24 HOUR
    GROUP BY activity_hour
    ORDER BY activity_hour ASC
";
$graphDataResult = $conn->query($graphDataQuery);

$activityLabels = [];
$activityCounts = [];

while ($row = $graphDataResult->fetch_assoc()) {
    $activityLabels[] = $row['activity_hour'] . ':00';
    $activityCounts[] = $row['active_users'];
}

// 3. Получение списка последних активных пользователей
$usersQuery = "SELECT username, last_activity FROM user_activity ORDER BY last_activity DESC LIMIT 10";
$usersResult = $conn->query($usersQuery);

$users = [];
if ($usersResult->num_rows > 0) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика бота</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin-top: 20px;
        }
        h1 {
            font-size: 24px;
        }
        .stats {
            margin-top: 20px;
        }
        .user-list {
            margin-top: 20px;
            border-top: 1px solid #444;
            padding-top: 10px;
        }
        .user-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
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
        <h1>Статистика бота</h1>
        <div class="stats">
            <p>Активных пользователей за последние 24 часа: <strong><?= $activeUsersCount ?></strong></p>
        </div>
        <canvas id="activityChart" width="400" height="200"></canvas>
        <div class="user-list">
            <h2>Последние активные пользователи</h2>
            <?php foreach ($users as $user): ?>
                <div class="user-item">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                    <span><?= htmlspecialchars($user['last_activity']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        // Данные для графика
        const activityData = {
            labels: <?= json_encode($activityLabels) ?>, // Часы активности
            datasets: [{
                label: 'Активные пользователи',
                data: <?= json_encode($activityCounts) ?>, // Количество активных пользователей
                backgroundColor: 'rgba(0, 102, 255, 0.5)',
                borderColor: '#0066FF',
                borderWidth: 1,
                tension: 0.4, // Плавность линий
                fill: true // Заполнение под линией
            }]
        };

        // Настройки графика
        const config = {
            type: 'line',
            data: activityData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#fff'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: '#444'
                        },
                        ticks: {
                            color: '#fff'
                        }
                    },
                    y: {
                        grid: {
                            color: '#444'
                        },
                        ticks: {
                            color: '#fff',
                            beginAtZero: true
                        }
                    }
                }
            }
        };

        // Инициализация графика
        const activityChart = new Chart(
            document.getElementById('activityChart'),
            config
        );
    </script>
</body>
</html>