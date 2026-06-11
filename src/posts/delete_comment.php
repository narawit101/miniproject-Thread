<?php
require_once 'config/server.php';
require_once 'config/swal_helper.php';
include_once 'layouts/dataheader.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

$comment_id = $_GET['comment_id'] ?? null;
$post_id    = $_GET['post_id'] ?? null;

if (!$comment_id || !$post_id) {
    header('Location: index.php?page=homepage');
    exit();
}

$isAdmin = $_SESSION['role'] === 'admin';

// ตรวจสอบสิทธิ์ — เจ้าของความคิดเห็นหรือ admin เท่านั้น
$sql  = "SELECT * FROM comments WHERE comment_id = ?" . ($isAdmin ? "" : " AND user_id = ?");
$stmt = $conn->prepare($sql);
$params = $isAdmin ? [$comment_id] : [$comment_id, $_SESSION['user_id']];
$stmt->execute($params);
$comment = $stmt->fetch();

if (!$comment) {
    set_swal('error', 'ไม่พบความคิดเห็น', 'ความคิดเห็นนี้ไม่มีอยู่หรือคุณไม่มีสิทธิ์ลบ');
    header("Location: index.php?page=post&post_id=$post_id");
    exit();
}

// ลบความคิดเห็น
$stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
if ($stmt->execute([$comment_id])) {
    set_swal('success', 'ลบความคิดเห็นสำเร็จ!', 'ความคิดเห็นถูกลบออกจากระบบแล้ว');
} else {
    set_swal('error', 'เกิดข้อผิดพลาด!', 'ไม่สามารถลบความคิดเห็นได้ กรุณาลองใหม่');
}

header("Location: index.php?page=post&post_id=$post_id");
exit();
?>
