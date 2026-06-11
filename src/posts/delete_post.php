<?php
require_once 'config/server.php';
require_once 'config/swal_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=logout');
    exit();
}

$post_id = $_GET['post_id'] ?? null;
if (!$post_id) {
    header('Location: index.php?page=homepage');
    exit();
}

$isAdmin = $_SESSION['role'] === 'admin';

// ดึง path รูปภาพก่อนลบ เพื่อลบไฟล์ออกจาก disk ด้วย
$img_stmt = $conn->prepare("SELECT post_img FROM posts WHERE post_id = ?");
$img_stmt->execute([$post_id]);
$post_img = $img_stmt->fetchColumn();

$sql  = "DELETE FROM posts WHERE post_id = ?" . ($isAdmin ? "" : " AND user_id = ?");
$stmt = $conn->prepare($sql);
$params = $isAdmin ? [$post_id] : [$post_id, $_SESSION['user_id']];

if ($stmt->execute($params)) {
    // ลบไฟล์รูปภาพออกจาก disk (ถ้ามี)
    if ($post_img) {
        $paths = [
            'uploads/posts/' . $post_img,
            'uploads/' . $post_img, // fallback สำหรับรูปเก่า
        ];
        foreach ($paths as $p) {
            if (file_exists($p)) {
                @unlink($p);
                break;
            }
        }
    }
    set_swal('success', 'ลบกระทู้สำเร็จ!', 'กระทู้ถูกลบออกจากระบบแล้ว');
    header('Location: index.php?page=all_feed');
    exit();
} else {
    set_swal('error', 'เกิดข้อผิดพลาด!', 'ไม่สามารถลบกระทู้ได้ กรุณาลองใหม่');
    header('Location: index.php?page=all_feed');
    exit();
}
?>