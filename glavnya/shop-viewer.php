<?php
require_once 'db.php';

// Получение списка всех шопов из базы данных
$stmt = $pdo->query("SELECT * FROM shops");
$shops = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($shops)) {
    echo 'Шопов пока нет.';
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Просмотр шопов</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #161622;
      color: white;
      font-family: 'Inter', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      min-height: 100vh;
    }
    .header {
      width: 100%;
      background: #27273A;
      padding: 10px 20px;
      box-sizing: border-box;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header .back-button {
      color: white;
      text-decoration: none;
      font-size: 16px;
      background: #444;
      padding: 8px 16px;
      border-radius: 8px;
    }
    .header h1 {
      font-size: 18px;
      margin: 0;
    }
    .shop-container {
      width: 100%;
      max-width: 800px;
      margin: 20px auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
      gap: 20px;
      padding: 20px;
      box-sizing: border-box;
    }
    .shop-card {
      background: rgba(217, 217, 217, 0.25);
      box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25) inset;
      border-radius: 40px;
      border: 3px solid black;
      cursor: pointer;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: center;
      overflow: hidden;
      position: relative;
      width: 100%;
      height: 220px;
    }
    .shop-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .shop-card .info {
      position: absolute;
      bottom: 0;
      width: 100%;
      background: rgba(0, 0, 0, 0.6);
      padding: 10px;
      color: white;
      text-align: center;
    }
    .shop-card .info .name {
      font-size: 16px;
      font-weight: bold;
    }
    .shop-card .info .price {
      font-size: 14px;
      color: #0E5AE5;
    }
    .order-shop {
      margin: 20px 0;
      background: #0E5AE5;
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 10px;
      font-size: 16px;
      display: inline-block;
      text-align: center;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    .order-shop:hover {
      background: #084B8A;
    }
    .menu {
      position: fixed;
      bottom: 0;
      width: 100%;
      height: 60px;
      background: #27273A;
      display: flex;
      justify-content: space-around;
      align-items: center;
    }
    .menu-item {
      text-align: center;
      color: #949494;
      font-size: 12px;
    }
    .menu-item img {
      width: 24px;
      height: 24px;
      margin-bottom: 5px;
    }
    .modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 90%;
      max-width: 400px;
      background: #27273A;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
      z-index: 1000;
      display: none;
    }
    .modal.active {
      display: block;
    }
    .modal h2 {
      margin: 0;
      color: white;
      font-size: 20px;
    }
    .modal p {
      margin: 10px 0;
      color: #CCC;
    }
    .modal .actions {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    .modal button {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }
    .modal button.write {
      background: #0E5AE5;
      color: white;
    }
    .modal button.back {
      background: #444;
      color: white;
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

    .bonus-image {
      width: 80%; /* Adjust the width as needed */
      height: auto;
      object-fit: cover;
      border-radius: 12px;
      display: block;
      margin: 0 auto; /* Center the image */
    }

  </style>
  <script>
    function openModal(shopId, shopName, shopDescription, shopPrice) {
      document.getElementById('modal-shop-name').innerText = shopName;
      document.getElementById('modal-shop-description').innerText = shopDescription;
      document.getElementById('modal-shop-price').innerText = `Цена: ${shopPrice}`;
      document.getElementById('write-button').onclick = function () {
        window.open(`https://t.me/delovarov666?text=Здравствуйте! Меня заинтересовал шоп под номером №${shopId}.`, '_blank');
      };
      document.getElementById('modal').classList.add('active');
      document.getElementById('overlay').classList.add('active');
    }

    function closeModal() {
      document.getElementById('modal').classList.remove('active');
      document.getElementById('overlay').classList.remove('active');
    }
  </script>
</head>
<body>
  <!-- Header -->
  <div class="header">
   
      <!-- Кнопка «Назад» -->
      <a href="index.php" style="
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

    <h1>Доступные шопы</h1>
  </div>

  <!-- Button to Order Shop -->
  <a href="https://t.me/delovarov666" class="order-shop">ЗАКАЗАТЬ СВОЙ ШОП</a>

  <!-- Shop List -->
  <div class="shop-container">
    <?php foreach ($shops as $shop): ?>
      <div class="shop-card" onclick="openModal(<?= htmlspecialchars($shop['id']) ?>, '<?= htmlspecialchars($shop['name']) ?>', '<?= htmlspecialchars($shop['description']) ?>', '<?= htmlspecialchars($shop['price']) ?>')">
        <?php if (!empty($shop['avatar'])): ?>
          <img src="<?= htmlspecialchars($shop['avatar']) ?>" alt="Shop Avatar">
        <?php endif; ?>
        <div class="info">
          <div class="name"><?= htmlspecialchars($shop['name']) ?></div>
          <div class="price">Цена: <?= htmlspecialchars($shop['price']) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Modal -->
  <div id="overlay" class="overlay" onclick="closeModal()"></div>
  <div id="modal" class="modal">
    <h2 id="modal-shop-name"></h2>
    <p id="modal-shop-description"></p>
    <p id="modal-shop-price"></p>
    <div class="actions">
      <button id="write-button" class="write">Написать продавцу</button>
      <button class="back" onclick="closeModal()">Назад</button>
    </div>
  </div>

  <!-- Bottom Menu -->
  <div class="menu">
  <a href="profile.html" class="menu-item">
    <img src="img/first_icon.png" alt="Home">
    <span>Главная</span>
  </a>
  <a href="news.php" class="menu-item">
    <img src="img/news_icon.png" alt="News">
    <span>Новости</span>
  </a>
  <a href="index.php" class="menu-item">
    <img src="img/profile_icon.png" alt="Profile">
    <span>Профиль</span>
  </a>
</div>
</body>
</html>