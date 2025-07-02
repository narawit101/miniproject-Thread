<?php
require 'server.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

$post_id = $_GET['post_id'];

// ตรวจสอบสิทธิ์ผู้ใช้
$isAdmin = $_SESSION['role'] === 'admin'; // ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่

// ลบโพสต์
$sql = "DELETE FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt->execute([$post_id])) {
    header('Location: all_feed.php');
    exit();
} else {
    echo "เกิดข้อผิดพลาดในการลบโพสต์!";
}
?>