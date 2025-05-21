<?php
// Hata raporlamayı kapat
error_reporting(0);
ini_set('display_errors', 0);

// Güvenli oturum ayarları
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

$host = 'localhost';
$dbname = 'guvenlik_db';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
} catch(PDOException $e) {
    // Hata mesajını logla ama kullanıcıya gösterme
    error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
    die("Bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
}
?> 