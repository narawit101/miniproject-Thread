<?php
require 'server.php';
include_once('dataheader.php'); 

$comment_id = $_GET['comment_id']; // รับ comment_id จาก URL
$post_id = $_GET['post_id']; // รับ post_id จาก URL

// ตรวจสอบสิทธิ์ผู้ใช้ (เช่น admin)
$isAdmin = $_SESSION['role'] === 'admin'; // ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ลบความคิดเห็น
    $sql = "DELETE FROM comments WHERE comment_id = ?";
    
    // ถ้าเป็น admin ให้ไม่ตรวจสอบ user_id
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$comment_id])) {
        header("Location: post.php?post_id=$post_id"); // เปลี่ยนเส้นทางไปยังหน้ากระทู้ที่เกี่ยวข้อง
        exit();
    } else {
        echo "เกิดข้อผิดพลาดในการลบความคิดเห็น!";
    }
} else {
    // ดึงข้อมูลความคิดเห็นเพื่อตรวจสอบ
    $sql = "SELECT * FROM comments WHERE comment_id = ?";
    
    // ถ้าเป็น admin ให้ไม่ตรวจสอบ user_id
    $stmt = $conn->prepare($sql);
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    // ตรวจสอบว่าความคิดเห็นนี้มีอยู่จริง
    if (!$comment) {
        echo "ความคิดเห็นไม่พบ!";
        exit();
    }
}
?>

<?php include_once('top_layouts.php'); ?>


<div class="container">
    <h2>ยืนยันการลบความคิดเห็น</h2>
    <p>คุณแน่ใจหรือไม่ว่าต้องการลบความคิดเห็นนี้?</p>
    <p><strong>ความคิดเห็น:</strong> <?= htmlspecialchars($comment['content']) ?></p>
    
    <form method="POST">
        <div class="form-group">
            <button type="submit" class="btn-delete">ลบความคิดเห็น</button>
            <button type="button" onclick="window.location.href='post.php?post_id=<?= $post_id ?>'" class="btn-cancel">ยกเลิก</button>
        </div>
    </form>
</div>


<style>
/* สไตล์ CSS ที่ปรับปรุงใหม่ */
.container {
    background-color: #ffffff;
    border-radius: 8px;
    padding: 20px;
    margin: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

h2 {
    text-align: center;
    color: #333;
}

.form-group {
    margin-top: 20px;
    text-align: center;
}

.btn-delete {
    background-color: #e74c3c; /* สีแดงสำหรับปุ่มลบ */
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 16px;
    margin-right: 10px; /* เพิ่มระยะห่างระหว่างปุ่ม */
}

.btn-delete:hover {
    background-color: #c0392b; /* สีแดงเข้มเมื่อ hover */
}

.btn-cancel {
    background-color: #3498db; /* สีน้ำเงินสำหรับปุ่มยกเลิก */
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 16px;
}

.btn-cancel:hover {
    background-color: #2980b9; /* สีน้ำเงินเข้มเมื่อ hover */
}
</style>
