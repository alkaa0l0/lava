<?php
require_once 'db.php';

// Получение списка всех дизайнов из базы данных
$stmt = $pdo->query("SELECT * FROM designs");
$designs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($designs)) {
    echo 'Дизайнов пока нет.';
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Просмотр Дизайнов</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #161622;
      color: white;
      font-family: 'Poppins', sans-serif;
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
    .design-container {
      width: 100%;
      max-width: 800px;
      margin: 20px auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      padding: 20px;
      box-sizing: border-box;
    }
    .design-card {
      background: #27273A;
      border: 2px solid black;
      border-radius: 10px;
      overflow: hidden;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      position: relative;
    }
    .design-card img {
      width: 100%;
      height: 150px;
      object-fit: cover;
    }
    .design-card .name {
      font-size: 16px;
      font-weight: bold;
      margin: 10px 0;
    }
    .design-card .price {
      font-size: 14px;
      color: #0E5AE5;
      font-weight: bold;
      margin-bottom: 10px;
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
    .modal img {
      width: 100%;
      height: auto;
      border-radius: 5px;
      margin-bottom: 15px;
    }
    .modal h2 {
      margin: 0;
      font-size: 20px;
      color: white;
    }
    .modal p {
      margin: 10px 0;
      color: #CCC;
    }
    .modal .price {
      color: #0E5AE5;
      font-weight: bold;
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
    .modal button.contact {
      background: #0E5AE5;
      color: white;
    }
    .modal button.close {
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
  </style>
  <script>
    function openModal(description, price, avatar, banner, creo, docs) {
      document.getElementById('modal-description').innerText = description;
      document.getElementById('modal-price').innerText = `Цена: ${price} $`;
      document.getElementById('modal-avatar').src = avatar || 'placeholder.png';
      document.getElementById('modal-banner').src = banner || 'placeholder.png';
      document.getElementById('modal-creo').src = creo || 'placeholder.png';
      document.getElementById('modal-docs').src = docs || 'placeholder.png';

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
    <a href="index.php" class="back-button">Назад</a>
    <h1>Доступные Дизайны</h1>
  </div>

  <!-- Design List -->
  <div class="design-container">
    <?php foreach ($designs as $design): ?>
      <div class="design-card" onclick="openModal(
        '<?= htmlspecialchars($design['description']) ?>', 
        '<?= htmlspecialchars($design['price']) ?>',
        '<?= htmlspecialchars($design['avatar']) ?>',
        '<?= htmlspecialchars($design['banner']) ?>',
        '<?= htmlspecialchars($design['creo']) ?>',
        '<?= htmlspecialchars($design['docs']) ?>'
      )">
        <img src="<?= htmlspecialchars($design['avatar']) ?>" alt="Avatar">
        <div class="name"><?= htmlspecialchars($design['description']) ?></div>
        <div class="price">Цена: <?= htmlspecialchars($design['price']) ?> $</div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Modal -->
  <div id="overlay" class="overlay" onclick="closeModal()"></div>
  <div id="modal" class="modal">
    <img id="modal-avatar" src="" alt="Avatar">
    <h2>Описание</h2>
    <p id="modal-description"></p>
    <p id="modal-price"></p>
    <h3>Примеры работ</h3>
    <img id="modal-banner" src="" alt="Banner">
    <img id="modal-creo" src="" alt="Creo">
    <img id="modal-docs" src="" alt="Docs">
    <div class="actions">
      <button class="contact" onclick="window.open('https://t.me/test', '_blank')">Написать продавцу</button>
      <button class="close" onclick="closeModal()">Закрыть</button>
    </div>
  </div>
</body>
</html>