<?php
$host = 'localhost';    // ชื่อโฮสต์ของฐานข้อมูล
$dbname = 'dpi_db';     // ชื่อฐานข้อมูล
$user = 'root';         // ชื่อผู้ใช้ฐานข้อมูล
$pass = '';             // รหัสผ่านของฐานข้อมูล

// สร้างการเชื่อมต่อกับฐานข้อมูลโดยใช้ PDO
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ตั้งค่าการแสดงผลข้อผิดพลาด
} catch (PDOException $e) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage());    // แสดงข้อผิดพลาดหากการเชื่อมต่อล้มเหลว
}
?>