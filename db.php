<?php
$host = 'localhost';
$dbname = 'cl94870_databa2';
$username = 'cl94870_databa2'; // Укажите ваш логин для базы данных
$password = '131176eg'; // Укажите ваш пароль для базы данных

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>