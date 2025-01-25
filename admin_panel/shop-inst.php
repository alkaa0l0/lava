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
if ($role !== 'Shop Creator' && $role !== 'Создатель') {
    echo 'У вас нет доступа к этой странице.';
    exit();
}

// Обработка формы сохранения данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopId = $_POST['shop_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $avatarPath = null;

    // Обработка загрузки аватарки
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/shop_avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
            $avatarPath = $uploadFile;
        }
    }

    // Обновление данных в базе
    $stmt = $pdo->prepare("UPDATE shops SET description = ?, price = ?, avatar = IFNULL(?, avatar) WHERE id = ?");
    $stmt->execute([$description, $price, $avatarPath, $shopId]);

    header("Location: shop-inst.php");
    exit();
}

// Получение списка шопов из базы данных
$stmt = $pdo->query("SELECT * FROM shops");
$shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ИНСТАШОПЫ</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #161622;
      color: white;
      font-family: 'Inter', sans-serif;
    }
    .profile {
      width: 100%;
      min-height: 100vh;
      position: relative;
      background: #161622;
      padding-bottom: 100px; /* Для нижнего меню */
    }
    .top-bar {
      width: 100%;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      box-sizing: border-box;
      background: #27273A;
    }
    .top-bar img {
      width: 24px;
      height: 24px;
    }
    .header-title {
      font-size: 24px;
      font-family: 'Banana Brick', sans-serif;
    }
    .menu {
      position: fixed;
      bottom: 0;
      width: 100%;
      height: 83px;
      background: #27273A;
      display: flex;
      justify-content: space-around;
      align-items: center;
    }
    .menu-item {
      text-align: center;
      color: #949494;
      font-size: 11px;
    }
    .menu-item img {
      width: 24px;
      height: 24px;
      display: block;
      margin: 0 auto;
    }
    .content {
      padding: 20px;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    .shop-card {
      width: 170px;
      height: 220px;
      background: rgba(217, 217, 217, 0.25);
      box-shadow: inset 0 4px 4px rgba(0, 0, 0, 0.25);
      border-radius: 40px;
      border-top: 3px solid white;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }
    .shop-card img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: 0;
    }
    .shop-card div {
      z-index: 1;
      background: rgba(0, 0, 0, 0.6);
      width: 100%;
      padding: 10px 0;
      font-size: 14px;
      font-weight: bold;
      color: white;
      text-align: center;
    }
    .modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 300px;
      background: #27273A;
      padding: 20px;
      border-radius: 8px;
      display: none;
      z-index: 1000;
    }
    .modal.active {
      display: block;
    }
    .modal h3 {
      margin: 0 0 10px;
      color: white;
    }
    .modal form {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .modal input, .modal textarea, .modal button {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      background: #1E1E2D;
      color: white;
    }
    .modal button {
      background: #0E5AE5;
      cursor: pointer;
    }
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 999;
      display: none;
    }
    .overlay.active {
      display: block;
    }
  </style>

<style>
    body {
      margin: 0;
      padding: 0;
      background: #161622;
      color: white;
      font-family: 'Inter', sans-serif;
    }
    .profile {
      width: 100%;
      min-height: 100vh;
      position: relative;
      background: #161622;
      padding-bottom: 100px; /* Для нижнего меню */
    }
    .top-bar {
      width: 100%;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      box-sizing: border-box;
      background: #27273A;
    }
    .top-bar img {
      width: 24px;
      height: 24px;
    }
    .header-title {
      font-size: 24px;
      font-family: 'Banana Brick', sans-serif;
    }
    .menu {
      position: fixed;
      bottom: 0;
      width: 100%;
      height: 83px;
      background: #27273A;
      display: flex;
      justify-content: space-around;
      align-items: center;
    }
    .menu-item {
      text-align: center;
      color: #949494;
      font-size: 11px;
    }
    .menu-item img {
      width: 24px;
      height: 24px;
      display: block;
      margin: 0 auto;
    }
    .content {
      padding: 20px;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    .shop-card {
      width: 170px;
      height: 220px;
      background: rgba(217, 217, 217, 0.25);
      box-shadow: inset 0 4px 4px rgba(0, 0, 0, 0.25);
      border-radius: 40px;
      border-top: 3px solid white;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }
    .shop-card img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: 0;
    }
    .shop-card div {
      z-index: 1;
      background: rgba(0, 0, 0, 0.6);
      width: 100%;
      padding: 10px 0;
      font-size: 14px;
      font-weight: bold;
      color: white;
      text-align: center;
    }
    .modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 300px;
      background: #27273A;
      padding: 20px;
      border-radius: 8px;
      display: none;
      z-index: 1000;
    }
    .modal.active {
      display: block;
    }
    .modal h3 {
      margin: 0 0 10px;
      color: white;
    }
    .modal form {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .modal input, .modal textarea, .modal button {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      background: #1E1E2D;
      color: white;
    }
    .modal button {
      background: #0E5AE5;
      cursor: pointer;
    }
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 999;
      display: none;
    }
    .overlay.active {
      display: block;
    }
  </style>
  
  <script>
    function openModal(shopId, description, price) {
      const modal = document.getElementById('modal');
      const overlay = document.getElementById('overlay');
      modal.classList.add('active');
      overlay.classList.add('active');
      document.getElementById('shopId').value = shopId;
      document.getElementById('description').value = description;
      document.getElementById('price').value = price;
    }
    function closeModal() {
      const modal = document.getElementById('modal');
      const overlay = document.getElementById('overlay');
      modal.classList.remove('active');
      overlay.classList.remove('active');
    }
  </script>
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
    
  <div class="profile">
    <!-- Верхняя панель -->
    <div class="top-bar">
      <img src="img/back.png" alt="Back">
      <div class="header-title">ИНСТАШОПЫ</div>
      <img src="img/menu.png" alt="Menu">
    </div>

    <!-- Контент -->
    <div class="content">
      <?php foreach ($shops as $shop): ?>
        <div class="shop-card" onclick="openModal(<?= $shop['id'] ?>, '<?= htmlspecialchars($shop['description']) ?>', '<?= htmlspecialchars($shop['price']) ?>')">
          <?php if (!empty($shop['avatar'])): ?>
            <img src="<?= htmlspecialchars($shop['avatar']) ?>" alt="Avatar">
          <?php endif; ?>
          <div><?= htmlspecialchars($shop['name']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Модальное окно -->
    <div id="overlay" class="overlay" onclick="closeModal()"></div>
    <div id="modal" class="modal">
      <h3>Редактировать шоп</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="shop_id" id="shopId">
        <label>Аватарка:</label>
        <input type="file" name="avatar">
        <label>Описание:</label>
        <textarea name="description" id="description" rows="3"></textarea>
        <label>Цена:</label>
        <input type="text" name="price" id="price">
        <button type="submit">Сохранить</button>
        <button type="button" onclick="closeModal()">Закрыть</button>
      </form>
    </div>

    <!-- Нижнее меню -->
    <div class="menu">
      <div class="menu-item">
        <img src="img/home_icon.png" alt="Home">
        <span>Главная</span>
      </div>
      <div class="menu-item">
        <img src="img/news_icon.png" alt="News">
        <span>Новости</span>
      </div>
      <div class="menu-item">
        <img src="img/profile_icon.png" alt="Profile">
        <span>Профиль</span>
      </div>
    </div>
  </div>
</body>
</html>