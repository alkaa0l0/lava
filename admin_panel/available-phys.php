<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin.php');
    exit();
}

$role = $_SESSION['role'];

// Подключение к базе данных
require 'config.php';
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Получение списка сервисов
$servicesQuery = "SELECT id, name FROM services";
$servicesResult = $conn->query($servicesQuery);
$services = [];
if ($servicesResult->num_rows > 0) {
    while ($row = $servicesResult->fetch_assoc()) {
        $services[] = $row;
    }
}

// Обработка добавления номера
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = $_POST['number'] ?? '';
    $service_id = $_POST['service_id'] ?? '';

    if (!empty($number) && !empty($service_id)) {
        $stmt = $conn->prepare("INSERT INTO numbers (number) VALUES (?)");
        $stmt->bind_param("s", $number);
        if ($stmt->execute()) {
            $number_id = $stmt->insert_id;

            $stmtService = $conn->prepare("INSERT INTO number_services (number_id, service_id) VALUES (?, ?)");
            $stmtService->bind_param("ii", $number_id, $service_id);
            if ($stmtService->execute()) {
                $message = 'Номер успешно добавлен!';
            } else {
                $message = 'Ошибка добавления сервиса для номера.';
            }
            $stmtService->close();
        } else {
            $message = 'Ошибка добавления номера.';
        }
        $stmt->close();
    } else {
        $message = 'Пожалуйста, заполните все поля.';
    }
}

// Получение списка доступных номеров
$numbersQuery = "
    SELECT n.id, n.number, s.name AS service_name
    FROM numbers n
    LEFT JOIN number_services ns ON n.id = ns.number_id
    LEFT JOIN services s ON ns.service_id = s.id
    WHERE n.user_id IS NULL
    ORDER BY n.date_taken DESC
";
$numbersResult = $conn->query($numbersQuery);
$availableNumbers = [];
if ($numbersResult->num_rows > 0) {
    while ($row = $numbersResult->fetch_assoc()) {
        $availableNumbers[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доступные физы</title>
    <style>
        body {
            background: #161622;
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .form-container {
            margin: 20px auto;
            padding: 20px;
            background: #27273A;
            border-radius: 8px;
            max-width: 600px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .form-container label {
            font-size: 14px;
        }
        .form-container input, .form-container select, .form-container button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #1E1E2D;
            color: white;
        }
        .form-container button {
            background: #0066FF;
            cursor: pointer;
        }
        .message {
            margin-top: 10px;
            color: #00FF00;
        }
        .numbers-table {
            margin: 20px auto;
            width: 90%;
            max-width: 800px;
            border-collapse: collapse;
        }
        .numbers-table th, .numbers-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        .numbers-table th {
            background: #27273A;
        }
        .numbers-table tr:nth-child(even) {
            background: #20212D;
        }
    </style>
</head>
<body>
  <h1>Доступные физы</h1>

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
        <h2>Добавить новый номер</h2>
        <form method="POST">
            <label for="number">Номер:</label>
            <input type="text" id="number" name="number" placeholder="Введите номер" required>
            
            <label for="service_id">Сервис:</label>
            <select id="service_id" name="service_id" required>
                <option value="" disabled selected>Выберите сервис</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= htmlspecialchars($service['id']) ?>"><?= htmlspecialchars($service['name']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit">Добавить</button>
        </form>
        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>

    <table class="numbers-table">
        <thead>
            <tr>
                <th>Номер</th>
                <th>Сервис</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($availableNumbers)): ?>
                <?php foreach ($availableNumbers as $number): ?>
                    <tr>
                        <td><?= htmlspecialchars($number['number']) ?></td>
                        <td><?= htmlspecialchars($number['service_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center;">Нет доступных номеров</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    

</body>
</html>