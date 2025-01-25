<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lavina Scama - Профиль</title>

  <!-- Скрипт для Telegram WebApp (MiniApp) -->
  <script src="https://telegram.org/js/telegram-web-app.js?56"></script>

  <style>
    html, body {
      margin: 0; 
      padding: 0;
      width: 100%; 
      height: 100%;
      background: #161622;
      overflow: hidden; /* Нет скролла */
      font-family: 'Poppins', sans-serif;
    }

    .phone-frame {
      width: 100%;
      max-width: 430px;
      min-height: 100vh;
      margin: 0 auto;
      background: #161622;
      display: flex;
      flex-direction: column;
      position: relative;
      box-sizing: border-box;
      overflow: hidden;
    }

    header {
      position: relative;
      height: 60px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 16px;
      box-sizing: border-box;
      flex-shrink: 0;
    }

    header h1 {
      margin: 0;
      color: #fff;
      font-size: 20px;
    }

    .wallet-link {
      margin-left: auto;
      display: inline-flex;
      width: 130px;
      height: 31px;
      background: #27273a;
      border-radius: 15.5px;
      align-items: center;
      justify-content: space-around;
      text-decoration: none;
    }

    .wallet-text {
      color: #fff;
      font-size: 16px;
    }

    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding-bottom: 83px;
      box-sizing: border-box;
    }

    .profile-avatar {
      width: 100px;
      height: 100px;
      background: #D9D9D9;
      border-radius: 50%;
      border: 2px solid #0E5AE5;
      object-fit: cover;
    }

    .profile-name {
      margin-top: 20px;
      color: #fff;
      font-size: 20px;
      font-weight: 600;
      text-align: center;
    }

    .profile-username {
      margin-top: 10px;
      color: #7D7777;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      word-wrap: break-word;
    }

    .info-block {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      justify-content: center;
      margin-top: 30px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 120px;
      height: 60px;
      background: #27273A;
      border-radius: 9px;
      text-align: center;
    }

    .info-item .info-label {
      color: #8F8B8B;
      font-size: 12px;
    }

    .info-item .info-value {
      color: #fff;
      font-size: 14px;
      font-weight: 500;
    }

    .buttons {
      display: flex;
      flex-direction: row;
      justify-content: center;
      gap: 16px;
      margin-top: 20px;
    }

    .button {
      width: 176px;
      height: 53px;
      background: #27273A;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      color: #fff;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      font-weight: 300;
    }

    .support-btn {
      margin-top: 20px;
      width: 300px;
      height: 40px;
      border: 1px solid #fff;
      border-radius: 20px;
      text-align: center;
      line-height: 40px;
      color: #fff;
      font-size: 14px;
      cursor: pointer;
    }

    .settings-btn {
      margin-top: 20px;
      width: 300px;
      height: 40px;
      background: #27273A;
      border-radius: 20px;
      text-align: center;
      line-height: 40px;
      color: #fff;
      font-size: 14px;
      text-decoration: none;
    }

    footer {
      width: 100%;
      height: 83px;
      background: #27273A;
      display: flex;
      align-items: center;
      justify-content: space-evenly;
    }

    footer a {
      text-decoration: none;
      text-align: center;
      color: #949494;
      font-size: 11px;
    }

    footer a img {
      width: 24px;
      height: 24px;
      margin: 0 auto;
      display: block;
    }

    footer a span {
      display: block;
      margin-top: 4px;
    }

  </style>
</head>
<body>
  <div class="phone-frame">
    <header>
      <h1>LAVINA SCAMA</h1>
      <a href="wallet.php?username=" id="walletLink" class="wallet-link">
        <img src="img/wallet.png" alt="Wallet" style="width: 18px; height: 20px;" />
        <span class="wallet-text">0 $</span>
        <img src="img/plus.png" alt="Plus" style="width: 21px; height: 21px;" />
      </a>
    </header>

    <div class="main-content">
      <img id="avatar" class="profile-avatar" src="img/placeholder.png" alt="Avatar" />
      <div id="profileName" class="profile-name">Без имени</div>
      <div id="profileUsername" class="profile-username">@none</div>

      <div class="info-block">
        <div class="info-item">
          <div class="info-label">ID</div>
          <div class="info-value" id="userIDValue">---</div>
        </div>
        <div class="info-item">
          <div class="info-label">Ранг</div>
          <div class="info-value">Новичок</div>
        </div>
        <div class="info-item">
          <div class="info-label">Время</div>
          <div class="info-value">0ч 0м</div>
        </div>
        <div class="info-item">
          <div class="info-label">Физы</div>
          <div class="info-value">0</div>
        </div>
      </div>

      <div class="buttons">
        <a href="bonus.html" class="button">Бонусы</a>
        <a href="wallet.php?username=" class="button">Баланс</a>
      </div>

      <div class="support-btn" id="supportBtn">Связаться с поддержкой</div>
      <a href="settings.html?username=" id="settingsLink" class="settings-btn">Настройки</a>
    </div>
    
    <footer>
      <a href="profile.html" class="footer-home">
        <img src="img/first_icon.png" alt="Home" />
        <span>Главная</span>
      </a>
      <a href="news.html" class="footer-news">
        <img src="img/news_icon.png" alt="News" />
        <span>Новости</span>
      </a>
      <a href="index.php" class="footer-profile">
        <img src="img/profile_icon.png" alt="Profile" />
        <span>Профиль</span>
      </a>
    </footer>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tg = window.Telegram.WebApp;

      tg.ready();

      const user = tg.initDataUnsafe?.user;

      if (user) {
        if (user.photo_url) {
          document.getElementById('avatar').src = user.photo_url;
        }

        const fullName = [user.first_name, user.last_name].filter(Boolean).join(' ') || 'Без имени';
        document.getElementById('profileName').textContent = fullName;

        document.getElementById('profileUsername').textContent = user.username
          ? `@${user.username}`
          : 'Нет username';

        document.getElementById('userIDValue').textContent = user.id;
      }

      document.getElementById('supportBtn')?.addEventListener('click', () => tg.openTelegramLink('https://t.me/huggerwz'));
    });

    document.addEventListener('DOMContentLoaded', () => {
      const tg = window.Telegram.WebApp;
      tg.ready();

      const user = tg.initDataUnsafe?.user;

      if (user) {
        const username = user.username || 'unknown_user';
        const settingsLink = document.getElementById('settingsLink');
        settingsLink.href = `settings.html?username=${encodeURIComponent(username)}`;
      }
    });
  </script>

</body>
</html>