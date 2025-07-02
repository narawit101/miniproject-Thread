<?php
require 'server.php';
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// **ดึงข้อมูลผู้ใช้ปัจจุบันจากฐานข้อมูล
$user_hader_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, user_img FROM users WHERE user_id = ?");
$stmt->execute([$user_hader_id]);
$user_hader = $stmt->fetch(); //** */

// ตรวจสอบว่ามีการส่งคำค้นหามาหรือไม่
$search_query = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// ปรับ SQL เพื่อใช้ในการค้นหาชื่อหรือเนื้อหาของโพสต์
$sql = "SELECT posts.*, users.first_name, users.last_name 
    FROM posts 
    JOIN users ON posts.user_id = users.user_id 
    WHERE posts.title LIKE ? OR posts.content LIKE ?
    ORDER BY posts.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$search_query, $search_query]);
$posts = $stmt->fetchAll();
