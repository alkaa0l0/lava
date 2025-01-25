<?php
session_start();
require 'config.php';

$user_id = $_SESSION['user_id'];
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("DB error: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT type, amount, date_time, description FROM transactions WHERE user_id = ? ORDER BY date_time DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История транзакций</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>История транзакций</h1>
    <table>
        <thead>
            <tr>
                <th>Тип</th>
                <th>Сумма</th>
                <th>Описание</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['type'] === 'credit' ? 'Пополнение' : 'Списание'; ?></td>
                    <td><?php echo number_format($row['amount'], 2); ?> $</td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo $row['date_time']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>