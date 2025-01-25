<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit();
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Ошибка подключения: ".$conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_number'], $_POST['service_id']) && !isset($_POST['code'])) {
    $phone_number = trim($_POST['phone_number']);
    $service_id = intval($_POST['service_id']);
    if ($phone_number !== '' && $service_id>0) {
        $stmt = $conn->prepare("INSERT INTO numbers (number, owner_id) VALUES (?, ?)");
        // Можно owner_id = админ, получаем из таблицы users, ...
        $owner_id = 0; 
        if (!empty($_SESSION['admin_logged_in'])) {
            // При желании вы можете найти user_id админа
        }
        $stmt->bind_param("si", $phone_number, $owner_id);
        if ($stmt->execute()) {
            $new_num_id = $stmt->insert_id;
            $stmt->close();
            $stmt_ns = $conn->prepare("INSERT INTO number_services (number_id, service_id) VALUES (?, ?)");
            $stmt_ns->bind_param("ii", $new_num_id, $service_id);
            $stmt_ns->execute();
            $stmt_ns->close();
            $success_message_number = "Номер успешно добавлен!";
        } else {
            $error_message_number = "Ошибка добавления номера: ".$stmt->error;
        }
    } else {
        $error_message_number = "Заполните все поля.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'], $_POST['user_id'])) {
    $code = trim($_POST['code']);
    $u_id = intval($_POST['user_id']);
    if (!preg_match('/^\d{4,6}$/', $code)) {
        $error_message = "Код должен быть 4-6 цифр.";
    } else {
        $stmt_u = $conn->prepare("SELECT telegram_username, chat_id FROM users WHERE id=?");
        $stmt_u->bind_param("i", $u_id);
        $stmt_u->execute();
        $stmt_u->bind_result($tn, $cid);
        $stmt_u->fetch();
        $stmt_u->close();
        if ($cid) {
            $bot_api_url = "https://api.telegram.org/bot".BOT_TOKEN."/sendMessage";
            $msg_data = [
                "chat_id" => $cid,
                "text" => "Ваш код: $code"
            ];
            $ch = curl_init($bot_api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resp = curl_exec($ch);
            curl_close($ch);
            $success_message = "Код успешно отправлен @$tn";
        } else {
            $error_message = "У пользователя нет chat_id.";
        }
    }
}

$sql_users = "
SELECT u.id as user_id, u.telegram_username, u.chat_id,
       s.name as service_name,
       n.number,
       n.is_paid
FROM users u
LEFT JOIN numbers n ON (u.id=n.user_id)
LEFT JOIN number_services ns ON (n.id=ns.number_id)
LEFT JOIN services s ON (ns.service_id=s.id)
ORDER BY u.telegram_username ASC, n.id ASC
";
$res_users = $conn->query($sql_users);

$serv_res = $conn->query("SELECT id, name, price FROM services");
$sql_all_users = "SELECT id, telegram_username, chat_id FROM users ORDER BY telegram_username ASC";
$res_all_users = $conn->query($sql_all_users);

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админ - LAVINA SCAMA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<header class="bg-primary text-white text-center py-4 shadow">
  <h1>LAVINA SCAMA - Админ</h1>
</header>
<main class="container my-4">
  <h2>Админ панель</h2>

  <?php if(!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
  <?php endif; ?>
  <?php if(!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
  <?php endif; ?>
  <?php if(!empty($success_message_number)): ?>
    <div class="alert alert-success"><?php echo $success_message_number; ?></div>
  <?php endif; ?>
  <?php if(!empty($error_message_number)): ?>
    <div class="alert alert-danger"><?php echo $error_message_number; ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Отправить код</h5>
      <form method="POST">
        <div class="mb-3">
          <label for="code" class="form-label">Код (4-6 цифр)</label>
          <input type="text" id="code" name="code" class="form-control" required pattern="\d{4,6}">
        </div>
        <div class="mb-3">
          <label for="user_id" class="form-label">Пользователь</label>
          <select name="user_id" id="user_id" class="form-select" required>
            <option value="">---</option>
            <?php if($res_all_users && $res_all_users->num_rows>0): ?>
              <?php while($u = $res_all_users->fetch_assoc()): ?>
                <?php if($u['chat_id']): ?>
                  <option value="<?php echo $u['id'];?>">@<?php echo $u['telegram_username'];?></option>
                <?php endif; ?>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Отправить</button>
      </form>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Добавить номер</h5>
      <form method="POST">
        <div class="mb-3">
          <label for="phone_number" class="form-label">Номер</label>
          <input type="text" id="phone_number" name="phone_number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="service_id" class="form-label">Сервис</label>
          <select id="service_id" name="service_id" class="form-select" required>
            <option value="">---</option>
            <?php if($serv_res && $serv_res->num_rows>0): ?>
              <?php while($srv=$serv_res->fetch_assoc()): ?>
                <option value="<?php echo $srv['id'];?>">
                  <?php echo $srv['name']." - $".$srv['price'];?>
                </option>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Добавить</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Список пользователей и номеров</h5>
      <?php if($res_users && $res_users->num_rows>0): ?>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead class="table-primary">
              <tr>
                <th>Username</th><th>ChatID</th><th>Сервис</th><th>Номер</th><th>Оплачено</th>
              </tr>
            </thead>
            <tbody>
              <?php while($row=$res_users->fetch_assoc()): ?>
                <tr>
                  <td>@<?php echo $row['telegram_username'] ?? '---';?></td>
                  <td><?php echo $row['chat_id'] ?? '';?></td>
                  <td><?php echo $row['service_name'] ?? '---';?></td>
                  <td><?php echo $row['number'] ?? '---';?></td>
                  <td>
                    <?php
                    if(!empty($row['number'])) {
                      echo $row['is_paid'] ? 
                           '<span class="badge bg-success">Да</span>' :
                           '<span class="badge bg-danger">Нет</span>';
                    } else {
                      echo '—';
                    }
                    ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p>Нет данных.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="mt-3 d-flex justify-content-between">
    <a href="index.html" class="btn btn-secondary">На главную</a>
    <a href="logout.php" class="btn btn-danger">Выйти</a>
  </div>
</main>
</body>
</html>