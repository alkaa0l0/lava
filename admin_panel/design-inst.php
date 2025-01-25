<?php
session_start();
require_once 'db.php';

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin.php');
    exit();
}

// Проверка роли
$role = $_SESSION['role'];
if ($role !== 'Desinger' && $role !== 'Создатель') {
    echo 'У вас нет доступа к этой странице.';
    exit();
}

// Обработка формы добавления дизайна
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $avatarPath = null;
    $bannerPath = null;
    $creoPath = null;
    $docsPath = null;

    // Загрузка файлов
    $uploadDir = 'uploads/designs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Загрузка аватарки
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $avatarPath = $uploadDir . uniqid() . '_avatar_' . basename($_FILES['avatar']['name']);
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath);
    }

    // Загрузка баннера
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        $bannerPath = $uploadDir . uniqid() . '_banner_' . basename($_FILES['banner']['name']);
        move_uploaded_file($_FILES['banner']['tmp_name'], $bannerPath);
    }

    // Загрузка крео
    if (isset($_FILES['creo']) && $_FILES['creo']['error'] === UPLOAD_ERR_OK) {
        $creoPath = $uploadDir . uniqid() . '_creo_' . basename($_FILES['creo']['name']);
        move_uploaded_file($_FILES['creo']['tmp_name'], $creoPath);
    }

    // Загрузка отрисовки документов
    if (isset($_FILES['docs']) && $_FILES['docs']['error'] === UPLOAD_ERR_OK) {
        $docsPath = $uploadDir . uniqid() . '_docs_' . basename($_FILES['docs']['name']);
        move_uploaded_file($_FILES['docs']['tmp_name'], $docsPath);
    }

    // Сохранение в базе данных
    $stmt = $pdo->prepare("INSERT INTO designs (description, price, avatar, banner, creo, docs) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$description, $price, $avatarPath, $bannerPath, $creoPath, $docsPath]);

    header('Location: design-inst.php');
    exit();
}

// Получение списка дизайнов
$stmt = $pdo->query("SELECT * FROM designs");
$designs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Управление Дизайнами</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #161622;
      color: white;
      font-family: 'Poppins', sans-serif;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      padding: 20px;
      background: #27273A;
      border-radius: 10px;
    }
    h1 {
      text-align: center;
      color: white;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    input, textarea, button {
      padding: 10px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
    }
    input[type="file"] {
      background: #1E1E2D;
      color: white;
    }
    button {
      background: #0E5AE5;
      color: white;
      cursor: pointer;
    }
    .design-list {
      margin-top: 20px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 10px;
    }
    .design-item {
      background: #1E1E2D;
      padding: 10px;
      border-radius: 10px;
      text-align: center;
    }
    .design-item img {
      width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
    }
    .design-item .description, .design-item .price {
      margin: 10px 0;
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
    <h1>Управление Дизайнами</h1>
    <form method="POST" enctype="multipart/form-data">
      <label>Описание</label>
      <textarea name="description" rows="3" required></textarea>
      <label>Цена</label>
      <input type="text" name="price" required>
      <label>Аватарка</label>
      <input type="file" name="avatar" accept="image/*">
      <label>Баннер</label>
      <input type="file" name="banner" accept="image/*">
      <label>Крео</label>
      <input type="file" name="creo" accept="image/*">
      <label>Отрисовка документов</label>
      <input type="file" name="docs" accept="image/*">
      <button type="submit">Добавить Дизайн</button>
    </form>

    <div class="design-list">
      <?php foreach ($designs as $design): ?>
        <div class="design-item">
          <img src="<?= htmlspecialchars($design['avatar']) ?>" alt="Avatar">
          <div class="description"><?= htmlspecialchars($design['description']) ?></div>
          <div class="price">Цена: <?= htmlspecialchars($design['price']) ?> $</div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
