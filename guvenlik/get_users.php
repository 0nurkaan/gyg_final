<?php
session_start();
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Yetkisiz erişim');
}

// Yetki kontrolü
if ($_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Bu işlem için yetkiniz yok');
}

// CSRF token kontrolü
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    header('HTTP/1.1 403 Forbidden');
    exit('CSRF token doğrulaması başarısız');
}

// SQL Injection koruması için prepared statement
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT id, username, role FROM users WHERE username LIKE :search ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute(['search' => '%' . $search . '%']);
$users = $stmt->fetchAll();

echo '<table class="table">';
echo '<thead><tr><th>ID</th><th>Kullanıcı Adı</th><th>Rol</th><th>İşlemler</th></tr></thead>';
echo '<tbody>';

foreach ($users as $user) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($user['id']) . '</td>';
    echo '<td>' . htmlspecialchars($user['username']) . '</td>';
    echo '<td>' . htmlspecialchars($user['role']) . '</td>';
    echo '<td>';
    echo '<button class="btn btn-sm btn-primary edit-user" data-id="' . htmlspecialchars($user['id']) . '">Düzenle</button> ';
    echo '<button class="btn btn-sm btn-danger delete-user" data-id="' . htmlspecialchars($user['id']) . '">Sil</button>';
    echo '</td>';
    echo '</tr>';
}

echo '</tbody></table>';
?> 