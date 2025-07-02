<?php
require 'server.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO comments (post_id, content, user_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$post_id, $content, $user_id])) {
        header("Location: post.php?post_id=$post_id");
        exit();
    } else {
        echo "เกิดข้อผิดพลาด!";
    }
}
?>