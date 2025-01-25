<?php
session_start();

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

// Обработка ограничений
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $restriction_type = $_POST['restriction_type'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!empty($user_id) && !empty($restriction_type) && !empty($action)) {
        if ($action === 'restrict') {
            $stmt = $conn->prepare("INSERT INTO restricted_users (user_id, restriction_type) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $restriction_type);
            if ($stmt->execute()) {
                $message = 'Ограничение успешно добавлено.';
            } else {
                $message = 'Ошибка добавления ограничения.';
            }
            $stmt->close();
        } elseif ($action === 'unrestrict') {
            $stmt = $conn->prepare("DELETE FROM restricted_users WHERE user_id = ? AND restriction_type = ?");
            $stmt->bind_param("is", $user_id, $restriction_type);
            if ($stmt->execute()) {
                $message = 'Ограничение успешно снято.';
            } else {
                $message = 'Ошибка снятия ограничения.';
            }
            $stmt->close();
        }
    } else {
        $message = 'Пожалуйста, выберите пользователя и действие.';
    }
}

// Получение списка пользователей с ограничениями
$restrictedUsersQuery = "
    SELECT u.telegram_username, r.restriction_type, r.created_at
    FROM restricted_users r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
";
$restrictedUsersResult = $conn->query($restrictedUsersQuery);
$restrictedUsers = [];
if ($restrictedUsersResult->num_rows > 0) {
    while ($row = $restrictedUsersResult->fetch_assoc()) {
        $restrictedUsers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки пользователей</title>
    <style>
        body {
            background: #161622;
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
        }
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #27273A;
            border-radius: 8px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .form-container label, .form-container select, .form-container button {
            color: white;
            background: #1E1E2D;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }
        .form-container button {
            background: #0066FF;
            cursor: pointer;
        }
        .message {
            color: #00FF00;
            margin-top: 10px;
        }
        .restricted-users-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .restricted-users-table th, .restricted-users-table td {
            padding: 10px;
            border-bottom: 1px solid #444;
        }
        .restricted-users-table th {
            background: #27273A;
        }
        .restricted-users-table tr:nth-child(even) {
            background: #20212D;
        }
    </style>
</head>
<body>
    <h1>Настройки пользователей</h1>

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

    <div class="form-container">
        <h2>Управление доступом</h2>
        <form method="POST">
            <label for="user_id">Пользователь:</label>
            <select id="user_id" name="user_id" required>
                <option value="" disabled selected>Выберите пользователя</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['telegram_username']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <label for="restriction_type">Тип ограничения:</label>
            <select id="restriction_type" name="restriction_type" required>
                <option value="" disabled selected>Выберите тип ограничения</option>
                <option value="bot_access">Доступ к боту</option>
                <option value="phys_access">Доступ к физам</option>
            </select>
            
            <label>Действие:</label>
            <div>
                <input type="radio" id="restrict" name="action" value="restrict" required>
                <label for="restrict">Ограничить</label>
                <input type="radio" id="unrestrict" name="action" value="unrestrict" required>
                <label for="unrestrict">Снять ограничение</label>
            </div>
            
            <button type="submit">Применить</button>
        </form>
        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>

    <h2>Список ограничений</h2>
    <table class="restricted-users-table">
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Тип ограничения</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($restrictedUsers)): ?>
                <?php foreach ($restrictedUsers as $restrictedUser): ?>
                    <tr>
                        <td><?= htmlspecialchars($restrictedUser['telegram_username']) ?></td>
                        <td><?= htmlspecialchars($restrictedUser['restriction_type'] === 'bot_access' ? 'Доступ к боту' : 'Доступ к физам') ?></td>
                        <td><?= htmlspecialchars($restrictedUser['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center;">Нет ограничений</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>