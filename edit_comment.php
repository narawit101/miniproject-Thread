<?php
require 'server.php'; // รวมการเชื่อมต่อกับฐานข้อมูล
include_once('dataheader.php');


// เก็บ URL ของหน้าปัจจุบันในเซสชัน
$_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // หากผู้ใช้ยังไม่ล็อกอิน ให้เปลี่ยนเส้นทางไปที่หน้า login
    exit();
}

if (!isset($_GET['post_id']) || !isset($_GET['comment_id'])) { // ตรวจสอบว่ามี post_id และ comment_id หรือไม่
    header("Location: all_feed.php"); // ถ้าไม่มี redirect ไปหน้าหลัก
    exit();
}

$comment_id = $_GET['comment_id'];
$post_id = $_GET['post_id'];

// ดึงข้อมูลคอมเมนต์จากฐานข้อมูล
$sql = "SELECT * FROM comments WHERE comment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) { // ตรวจสอบว่าพบคอมเมนต์หรือไม่
    header("Location: post.php?post_id=$post_id"); // ถ้าไม่พบ redirect กลับไปที่โพสต์
    exit();
}

// ลบรูปภาพถ้ามีการกดปุ่มลบ
if (isset($_POST['delete_image'])) {
    if (!empty($comment['image'])) {
        unlink("uploads/" . $comment['image']); // ลบรูปภาพออกจากเซิร์ฟเวอร์
    }

    // อัปเดตคอมเมนต์ในฐานข้อมูลเพื่อเอารูปภาพออก
    $sql = "UPDATE comments SET image = NULL WHERE comment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$comment_id]);

    header("Location: post.php?post_id=$post_id"); // เปลี่ยนเส้นทางกลับไปที่หน้ากระทู้
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_image'])) { // ตรวจสอบว่ามีการส่งฟอร์มหรือไม่
    $content = $_POST['content']; // รับเนื้อหาคอมเมนต์

    // จัดการการอัปโหลดรูปภาพใหม่
    $image = $comment['image']; // ใช้รูปภาพเดิมเป็นค่าเริ่มต้น
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // หากมีการอัปโหลดรูปภาพใหม่
        if (!empty($comment['image'])) {
            // ลบรูปภาพเดิมออกจากเซิร์ฟเวอร์
            unlink("uploads/" . $comment['image']);
        }
        $image = $_FILES['image']['name'];
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    // อัปเดตคอมเมนต์ในฐานข้อมูล
    $sql = "UPDATE comments SET content = ?, image = ?, updated_at = NOW() WHERE comment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$content, $image, $comment_id]);

    header("Location: post.php?post_id=$post_id"); // เปลี่ยนเส้นทางกลับไปที่หน้ากระทู้
    exit();
}
?>

<!-- รวมส่วนหัวของ HTML -->
<?php include_once('top_layouts.php'); ?>

<div class="container">
    <h2>แก้ไขคอมเมนต์</h2>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="content">เนื้อหาคอมเมนต์:</label>
            <textarea name="content" id="content" required><?= htmlspecialchars($comment['content']) ?></textarea>
        </div>
        
        <div class="form-group">
            <?php if (!empty($comment['image'])): // แสดงรูปภาพเดิม ?>
                <p>รูปภาพเดิม:</p>
                <img src="uploads/<?= htmlspecialchars($comment['image']) ?>" alt="Comment Image" class="image-preview">
                <button type="button" onclick="confirmDeleteImage()">ลบรูปภาพ</button>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="image">อัปโหลดรูปภาพใหม่ (ถ้ามี):</label>
            <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)">
            <img id="image-preview" class="image-preview" style="display: none;">
            <button type="button" onclick="clearImage()">ยกเลิกการเลือกภาพ</button> <!-- ปุ่มยกเลิกการเลือกภาพ -->
        </div>

        <div class="form-group">
            <button type="submit">อัปเดตคอมเมนต์</button>
            <button type="button" onclick="window.location.href='post.php?post_id=<?= $post_id ?>'">ย้อนกลับ</button>
        </div>
    </form>
</div>

<!-- รวมส่วนท้ายของ HTML -->
<?php include_once('bottom_layouts.php'); ?>

<script>
function confirmDeleteImage() {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรูปภาพนี้?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_image';
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
}

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('image-preview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}

function clearImage() {
    const fileInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');

    // รีเซ็ตค่าไฟล์ใน input
    fileInput.value = '';
    // ซ่อนภาพพรีวิว
    imagePreview.style.display = 'none';
}
</script>

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
    margin-bottom: 15px;
}

textarea {
    width: 100%;
    height: 100px;
    border-radius: 4px;
    border: 1px solid #ccc;
    padding: 10px;
    font-size: 16px;
}

input[type="file"] {
    margin-top: 10px;
}

.image-preview {
    max-width: 60%;
    height: auto;
    border-radius: 10px;
    margin-top: 10px;
    display: block;
    margin: 0 auto;
}

button {
    background-color: #5f27cd; /* สีม่วง */
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
    display: block;
    width: 100%;
}

button:hover {
    background-color: #6f2cbb; /* สีม่วงเข้มเมื่อ hover */
}
</style>