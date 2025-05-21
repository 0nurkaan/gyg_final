<?php
session_start();
require_once 'config.php';

// Yetki kontrolü
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Bu işlem için yetkiniz yok!']);
    exit();
}

// SQL Injection açığı: Kullanıcı girdisi doğrudan SQL sorgusuna ekleniyor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    
    $query = "DELETE FROM users WHERE id = '$id'";
    try {
        $db->query($query);
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?> 