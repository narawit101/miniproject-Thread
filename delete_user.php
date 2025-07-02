<?php
require 'server.php';
session_start();

if ($_SESSION['role'] != 'admin') {
    header('Location: logout.php');
    exit();
}

$user_id = $_GET['user_id'];

$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt->execute([$user_id])) {
    header('Location: admin.php');
    exit();
} else {
    echo "เกิดข้อผิดพลาด!";
}
?>