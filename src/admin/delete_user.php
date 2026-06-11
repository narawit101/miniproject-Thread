<?php
require_once 'config/server.php';
require_once 'config/swal_helper.php';
session_start();

if ($_SESSION['role'] != 'admin') {
    header('Location: index.php?page=logout');
    exit();
}

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    header('Location: index.php?page=manage_users');
    exit();
}

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");

if ($stmt->execute([$user_id])) {
    set_swal('success', 'ลบผู้ใช้สำเร็จ!', 'บัญชีผู้ใช้ถูกลบออกจากระบบแล้ว');
} else {
    set_swal('error', 'เกิดข้อผิดพลาด!', 'ไม่สามารถลบผู้ใช้ได้ กรุณาลองใหม่');
}

header('Location: index.php?page=admin');
exit();
?>