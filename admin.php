<?php
session_start();
require_once 'db.php';

// Получение username из settings.html
$usernameFromSettings = $_GET['username'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Проверка логина и роли администратора
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && hash('sha256', $password) === $admin['password']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $admin['role']; // Сохранение роли
        header('Location: admin-panel.php');
        exit();
    } else {
        $error = 'Неверный логин или пароль.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Авторизация</title>
  <style>
        body {
            margin: 0;
            padding: 0;
            background: #161622;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
        }
        .login-box {
            width: 300px;
            background: #27273A;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: white;
        }
        .login-box input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: #1E1E2D;
            color: white;
        }
        .login-box input[readonly] {
            background: #1E1E2D;
            color: #7E848D;
        }
        .login-box button {
            width: 90%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            background: #1E1E2D;
            color: white;
            cursor: pointer;
        }
        .login-box button:hover {
            background: #34344E;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
  <div class="login-box">
    <h1>Авторизация</h1>
    <form method="POST">
      <input type="text" name="username" value="<?= htmlspecialchars($usernameFromSettings) ?>" readonly>
      <input type="password" name="password" placeholder="Пароль" required>
      <button type="submit">Войти</button>
    </form>
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
