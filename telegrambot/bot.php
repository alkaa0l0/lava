<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$update = file_get_contents('php://input');
$updateData = json_decode($update, true);

file_put_contents('bot.log', date('Y-m-d H:i:s') . " - Получено обновление: " . $update . PHP_EOL, FILE_APPEND);

if (isset($updateData['my_chat_member'])) {
    exit();
}

if (isset($updateData['message'])) {
    $message = $updateData['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $telegram_username = $message['from']['username'] ?? '';

    $bot_token = '7891982880:AAFTtHk2ZiEKCY4onm3vCHJSIfKYpTPfibk';
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";

    $servername = "localhost";
    $db_username = "cl94870_databa2";
    $db_password = "131176eg";
    $dbname = "cl94870_databa2";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        file_put_contents('bot.log', date('Y-m-d H:i:s') . " - Ошибка подключения к БД: " . $conn->connect_error . PHP_EOL, FILE_APPEND);
        exit();
    }

    $user_id = null;
    if (!empty($telegram_username)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE telegram_username = ?");
        $stmt->bind_param("s", $telegram_username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            $stmt->close();

            $stmt_upd = $conn->prepare("UPDATE users SET chat_id = ? WHERE id = ?");
            $stmt_upd->bind_param("ii", $chat_id, $user_id);
            if ($stmt_upd->execute()) {
                file_put_contents('bot.log', date('Y-m-d H:i:s') . " - Обновлен chat_id=$chat_id для пользователя @$telegram_username (user_id=$user_id)" . PHP_EOL, FILE_APPEND);
            }
            $stmt_upd->close();
        } else {
            $stmt->close();
            $stmt_ins = $conn->prepare("INSERT INTO users (telegram_username, chat_id) VALUES (?, ?)");
            $stmt_ins->bind_param("si", $telegram_username, $chat_id);
            if ($stmt_ins->execute()) {
                $user_id = $stmt_ins->insert_id;
                file_put_contents('bot.log', date('Y-m-d H:i:s') . " - Добавлен новый пользователь @$telegram_username (id=$user_id), chat_id=$chat_id" . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents('bot.log', date('Y-m-d H:i:s') . " - Ошибка вставки пользователя: " . $stmt_ins->error . PHP_EOL, FILE_APPEND);
            }
            $stmt_ins->close();
        }
    }
   
    // **Добавляем обновление активности**
    if ($user_id) {
        $stmt_activity = $conn->prepare("
            INSERT INTO user_activity (user_id, username, last_activity)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_activity = NOW()
        ");
        $stmt_activity->bind_param("is", $user_id, $telegram_username);
        $stmt_activity->execute();
        $stmt_activity->close();
    }


    if (stripos($text, '/start') === 0) {
        $start_text = "Привет, " . ($telegram_username ? "@$telegram_username" : "друг") . "! Твой chat_id: $chat_id";
        $data_start = [
            'chat_id' => $chat_id,
            'text' => $start_text
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_start));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    if (preg_match('/^Я забрал номер\s+(\d+)$/ui', $text, $m)) {
        $selected_number = $m[1];
        $date_time = date('Y-m-d H:i:s');

        if (!$user_id) {
            $reply_data = [
                'chat_id' => $chat_id,
                'text' => "У вас не установлен username. Пожалуйста, задайте username в Telegram."
            ];
            $ch_reply = curl_init($url);
            curl_setopt($ch_reply, CURLOPT_POST, true);
            curl_setopt($ch_reply, CURLOPT_POSTFIELDS, json_encode($reply_data));
            curl_setopt($ch_reply, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch_reply, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch_reply);
            curl_close($ch_reply);
            $conn->close();
            exit();
        }

        $stmt_num = $conn->prepare("SELECT id, service_id, user_id FROM numbers WHERE number = ?");
        $stmt_num->bind_param("s", $selected_number);
        $stmt_num->execute();
        $stmt_num->store_result();
        if ($stmt_num->num_rows > 0) {
            $stmt_num->bind_result($num_id, $srv_id, $existing_user_id);
            $stmt_num->fetch();
            $stmt_num->close();
            if ($existing_user_id) {
                $already_data = [
                    'chat_id' => $chat_id,
                    'text' => "Извините, номер $selected_number уже занят."
                ];
                $ch_already = curl_init($url);
                curl_setopt($ch_already, CURLOPT_POST, true);
                curl_setopt($ch_already, CURLOPT_POSTFIELDS, json_encode($already_data));
                curl_setopt($ch_already, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch_already, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch_already);
                curl_close($ch_already);
            } else {
                $stmt_upd = $conn->prepare("UPDATE numbers SET user_id = ?, date_taken = ? WHERE id = ?");
                $stmt_upd->bind_param("isi", $user_id, $date_time, $num_id);
                if ($stmt_upd->execute()) {
                    $stmt_srv = $conn->prepare("SELECT name FROM services WHERE id = ?");
                    $stmt_srv->bind_param("i", $srv_id);
                    $stmt_srv->execute();
                    $stmt_srv->bind_result($srv_name);
                    $stmt_srv->fetch();
                    $stmt_srv->close();

                    $conf_data = [
                        'chat_id' => $chat_id,
                        'text' => "Спасибо, @$telegram_username! Номер $selected_number для сервиса «$srv_name» закреплён за вами."
                    ];
                    $ch_conf = curl_init($url);
                    curl_setopt($ch_conf, CURLOPT_POST, true);
                    curl_setopt($ch_conf, CURLOPT_POSTFIELDS, json_encode($conf_data));
                    curl_setopt($ch_conf, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                    curl_setopt($ch_conf, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch_conf);
                    curl_close($ch_conf);

                    $notify_chat_ids = ['7251189242', '7539805346'];
                    foreach ($notify_chat_ids as $adm_chat_id) {
                        $notify_text = "Клиент @$telegram_username взял номер: $selected_number для сервиса «$srv_name».\nДата и время: $date_time";
                        $data_notify = [
                            'chat_id' => $adm_chat_id,
                            'text' => $notify_text
                        ];
                        $ch_notify = curl_init($url);
                        curl_setopt($ch_notify, CURLOPT_POST, true);
                        curl_setopt($ch_notify, CURLOPT_POSTFIELDS, json_encode($data_notify));
                        curl_setopt($ch_notify, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                        curl_setopt($ch_notify, CURLOPT_RETURNTRANSFER, true);
                        $resp_notify = curl_exec($ch_notify);
                        curl_close($ch_notify);
                    }
                }
                $stmt_upd->close();
            }
        } else {
            $stmt_num->close();
            $default_service = "Техобслуживание";
            $stmt_srv = $conn->prepare("SELECT id FROM services WHERE name = ?");
            $stmt_srv->bind_param("s", $default_service);
            $stmt_srv->execute();
            $stmt_srv->bind_result($new_srv_id);
            $stmt_srv->fetch();
            $stmt_srv->close();

            if (!$new_srv_id) {
                $stmt_srv_ins = $conn->prepare("INSERT INTO services (name, price) VALUES (?, ?)");
                $price = 100.0;
                $stmt_srv_ins->bind_param("sd", $default_service, $price);
                $stmt_srv_ins->execute();
                $new_srv_id = $stmt_srv_ins->insert_id;
                $stmt_srv_ins->close();
            }

            $stmt_ins_num = $conn->prepare("INSERT INTO numbers (number, service_id, user_id, date_taken) VALUES (?, ?, ?, ?)");
            $stmt_ins_num->bind_param("siis", $selected_number, $new_srv_id, $user_id, $date_time);
            if ($stmt_ins_num->execute()) {
                $conf2_data = [
                    'chat_id' => $chat_id,
                    'text' => "Спасибо, @$telegram_username! Новый номер $selected_number (сервис «$default_service») сохранён за вами."
                ];
                $ch_conf2 = curl_init($url);
                curl_setopt($ch_conf2, CURLOPT_POST, true);
                curl_setopt($ch_conf2, CURLOPT_POSTFIELDS, json_encode($conf2_data));
                curl_setopt($ch_conf2, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch_conf2, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch_conf2);
                curl_close($ch_conf2);

                $notify_chat_ids = ['7251189242', '7539805346'];
                foreach ($notify_chat_ids as $adm_chat_id) {
                    $notify_text = "Клиент @$telegram_username взял новый номер: $selected_number для сервиса «$default_service».\nДата и время: $date_time";
                    $data_notify = [
                        'chat_id' => $adm_chat_id,
                        'text' => $notify_text
                    ];
                    $ch_notify = curl_init($url);
                    curl_setopt($ch_notify, CURLOPT_POST, true);
                    curl_setopt($ch_notify, CURLOPT_POSTFIELDS, json_encode($data_notify));
                    curl_setopt($ch_notify, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                    curl_setopt($ch_notify, CURLOPT_RETURNTRANSFER, true);
                    $resp_notify = curl_exec($ch_notify);
                    curl_close($ch_notify);
                }
            }
            $stmt_ins_num->close();
        }
    }

    $conn->close();
}
?>