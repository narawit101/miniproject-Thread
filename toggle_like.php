<?php
include_once('dataheader.php');

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];
$action = $_POST['action'];

if ($action == 'like') {
    // เพิ่มไลค์
    $sql = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $post_id]);
} elseif ($action == 'unlike') {
    // ยกเลิกไลค์
    $sql = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $post_id]);
}

// ดึงจำนวนไลค์ทั้งหมด
$sql_like_count = "SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?";
$stmt_like_count = $conn->prepare($sql_like_count);
$stmt_like_count->execute([$post_id]);
$like_count = $stmt_like_count->fetch()['like_count'];

// ส่งข้อมูลกลับไปยัง client
$response = [
    'action' => $action == 'like' ? 'unlike' : 'like',
    'like_count' => $like_count
];

echo json_encode($response);
?>