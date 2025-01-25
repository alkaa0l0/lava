<?php
session_start();
require 'config.php';

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("DB error: ".$conn->connect_error);
}

$services = [];
$r = $conn->query("SELECT id, name, price FROM services");
while($row = $r->fetch_assoc()) {
    $services[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, maximum-scale=1.0, user-scalable=no" />
  <title>Доступные номера</title>
  <link rel="stylesheet" href="globals.css">
  <link rel="stylesheet" href="style.css">
</head>
<body style="margin:0; padding:0; background:#161622;">
<div class="phone-frame">
  <header style="
    height:60px; border-bottom:1px solid rgba(255,255,255,0.1);
    display:flex; align-items:center; padding:0 16px; box-sizing:border-box;
  ">
    <h1 style="color:#fff; font-family:Bebas Neue,sans-serif; font-size:26px; margin:0;">
      Физические номера
    </h1>
  </header>

  <main style="
    flex:1; padding:16px; box-sizing:border-box;
    padding-bottom:100px; overflow-y:auto;
  ">
    <div style="
      background:#27273a; border-radius:16px; padding:16px;
      margin-bottom:16px;
    ">
      <form action="assign_number.php" method="POST" style="display:flex; flex-direction:column; gap:8px;">
        <label for="service" style="color:#fff;">Сервис</label>
        <select name="service" id="service" style="padding:8px; border-radius:8px; border:none;" required>
          <option value="">Выберите сервис</option>
          <?php foreach($services as $svc): ?>
            <option value="<?php echo $svc['id']; ?>">
              <?php echo htmlspecialchars($svc['name'])." - $".htmlspecialchars($svc['price']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="telegram_username" style="color:#fff;">Telegram Username</label>
        <div style="display:flex; gap:4px; align-items:center;">
          <span style="color:#ccc;">@</span>
          <input type="text" name="telegram_username" id="telegram_username" placeholder="huggerwz" required style="
            flex:1; padding:8px; border-radius:8px; border:none;
          ">
        </div>

        <button type="submit" style="
          height:48px; background:#0066ff; border:none; border-radius:12px;
          color:#fff; font-family:Poppins,sans-serif; font-weight:600; font-size:16px;
          cursor:pointer; margin-top:8px;
        ">
          Получить номер
        </button>
      </form>
    </div>
  </main>

  <footer style="
    width:100%; height:83px; background:#27273A; position:relative;
    flex-shrink:0;
  ">
    <a href="index.html" style="
      position:absolute; left:58px; top:18px;
      width:64px; height:40px; text-align:center; text-decoration:none;
    ">
      <img src="img/first_icon.png" alt="" style="width:24px; height:24px; margin:0 auto;">
      <span style="
        display:block; margin-top:6px;
        font-size:11px; font-family:Inter,sans-serif; color:#949494;
      ">Главная</span>
    </a>
    <a href="news.html" style="
      position:absolute; left:190px; top:18px;
      width:64px; height:40px; text-align:center; text-decoration:none;
    ">
      <img src="img/news_icon.png" alt="" style="width:24px; height:24px; margin:0 auto;">
      <span style="
        display:block; margin-top:6px;
        font-size:11px; font-family:Inter,sans-serif; color:#949494;
      ">Новости</span>
    </a>
    <a href="profile.html" style="
      position:absolute; left:320px; top:18px;
      width:64px; height:40px; text-align:center; text-decoration:none;
    ">
      <img src="img/profile_icon.png" alt="" style="width:24px; height:24px; margin:0 auto;">
      <span style="
        display:block; margin-top:6px;
        font-size:11px; font-family:Inter,sans-serif; color:#949494;
      ">Профиль</span>
    </a>
  </footer>
</div>
</body>
</html>