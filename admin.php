<?php
require 'server.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// ตรวจสอบว่าผู้ใช้เป็นแอดมินหรือไม่
if ($_SESSION['role'] !== 'admin') {
    // หากไม่ใช่แอดมิน ให้แสดงข้อความและหยุดการทำงาน
    header('Location: login.php');
    session_start();
    session_unset();  // ล้างข้อมูลทั้งหมดใน session
    session_destroy();  // ทำลาย session
    exit();
}

// ตรวจสอบว่ามีการส่ง user_id มาหรือไม่
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // สร้างคำสั่ง SQL เพื่อลบผู้ใช้
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$user_id])) {
        // หากลบสำเร็จ ให้ redirect กลับไปยังหน้าเดิม
        header('Location: manage_users.php?msg=ลบผู้ใช้เรียบร้อยแล้ว');
    } else {
        // หากเกิดข้อผิดพลาด
        header('Location: manage_users.php?msg=ไม่สามารถลบผู้ใช้ได้');
    }
    exit();
} else {
    // ถ้าไม่มี user_id ให้ redirect กลับไปยังหน้าเดิม
    header('Location: manage_users.php');
    exit();
}
