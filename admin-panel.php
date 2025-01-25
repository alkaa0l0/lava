<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin.php');
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role']; // Получение роли администратора
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Админ Панель</title>
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
      height: 100vh;
    }
    .panel {
      text-align: center;
    }
    .role {
      color: #7E848D;
      margin-top: 10px;
    }
    .menu {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .menu a {
      background: #27273A;
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 8px;
      display: block;
      text-align: center;
    }
    .menu a.disabled {
      background: #444;
      cursor: not-allowed;
    }
    .logout {
      margin-top: 20px;
      color: #FF5252;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="panel">
    <h1>Добро пожаловать, <?= htmlspecialchars($username) ?>!</h1>
    <div class="role">Ваша роль: <?= htmlspecialchars($role) ?></div>

    <div class="menu">
        <!-- Рассылки для роли "Создатель" -->
        <?php if ($role === 'Создатель' || $role === 'adm TAGGER'): ?>
        <a href="admin_panel/broadcast.php">Рассылки</a>
        <?php endif; ?>

        <!-- Доступ для роли "Shop Creator" -->
        <?php if ($role === 'Shop Creator' || $role === 'Создатель'): ?>
        <a href="admin_panel/shop-inst.php">Управление шопами</a>
        <?php endif; ?>

        <!-- Доступ для роли "Desinger" или "Создатель" -->
        <?php if ($role === 'Designer' || $role === 'Создатель'): ?>
        <a href="admin_panel/design-inst.php">Управление дизайнами</a>
        <?php endif; ?>

        <!-- Доступ к статистике только для ролей "Создатель" и "adm TAGGER" -->
        <?php if ($role === 'Создатель' || $role === 'adm TAGGER'): ?>
        <a href="admin_panel/bot-statistics.php">Статистика бота</a>
        <?php endif; ?>

        <!-- Доступ к статистике только для ролей "Создатель" и "adm TAGGER" -->
        <?php if ($role === 'Создатель' || $role === 'adm TAGGER'): ?>
        <a href="admin_panel/taken-phys-history.php">История взятых физов</a>
        <?php endif; ?>

             <!-- Доступ к статистике только для ролей "Создатель" и "adm TAGGER" -->
             <?php if ($role === 'Создатель' || $role === 'adm TAGGER'): ?>
        <a href="admin_panel/available-phys.php">Доступные физы</a>
        <?php endif; ?>

         <!-- Доступ к статистике только для ролей "Создатель" и "adm TAGGER" -->
         <?php if ($role === 'Создатель' || $role === 'adm TAGGER'): ?>
        <a href="admin_panel/client-messenger.php">Мессенджер с клиентами</a>
        <?php endif; ?>

         <!-- Доступ к статистике только для ролей "Создатель" и "adm TAGGER" -->
         <?php if ($role === 'Создатель' || $role === 'adm TAGGER'): ?>
        <a href="admin_panel/user-settings.php">Настройки пользователей</a>
        <?php endif; ?>
    </div>

    <a href="logout.php" class="logout">Выйти</a>
  </div>
</body>
</html>