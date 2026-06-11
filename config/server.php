<?php
// ดึงค่า credentials จาก Environment Variables (Docker)
// ถ้าไม่มีจะใช้ค่า default สำหรับ fallback
$host   = getenv('DB_HOST') ?: 'mysql';
$dbname = getenv('DB_NAME') ?: 'dpi_db';
$user   = getenv('DB_USER') ?: 'dpi_user';
$pass   = getenv('DB_PASS') ?: '';

// สร้างการเชื่อมต่อกับฐานข้อมูลโดยใช้ PDO
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ตั้งค่าการแสดงผลข้อผิดพลาด
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage());    // แสดงข้อผิดพลาดหากการเชื่อมต่อล้มเหลว
}
?>