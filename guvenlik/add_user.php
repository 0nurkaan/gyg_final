<?php
session_start();
require_once 'config.php';

// Yetki kontrolü
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Bu işlem için yetkiniz yok!']);
    exit();
}

// SQL Injection açığı: Kullanıcı girdileri doğrudan SQL sorgusuna ekleniyor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    try {
        $db->query($query);
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?> 