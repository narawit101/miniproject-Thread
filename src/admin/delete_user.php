<?php
require_once 'config/server.php';
session_start();

if ($_SESSION['role'] != 'admin') {
    header('Location: index.php?page=logout');
    exit();
}

$user_id = $_GET['user_id'];

$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt->execute([$user_id])) {
    header('Location: index.php?page=admin');
    exit();
} else {
    echo "เกิดข้อผิดพลาด!";
}
?>