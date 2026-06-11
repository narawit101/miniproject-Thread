<?php
require_once 'config/server.php'; // รวมการเชื่อมต่อกับฐานข้อมูล
include_once 'layouts/dataheader.php';


// เก็บ URL ของหน้าปัจจุบันในเซสชัน
$_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); // หากผู้ใช้ยังไม่ล็อกอิน ให้เปลี่ยนเส้นทางไปที่หน้า login
    exit();
}

if (!isset($_GET['post_id']) || !isset($_GET['comment_id'])) { // ตรวจสอบว่ามี post_id และ comment_id หรือไม่
    header("Location: index.php?page=all_feed"); // ถ้าไม่มี redirect ไปหน้าหลัก
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
    header("Location: index.php?page=post&post_id=$post_id"); // ถ้าไม่พบ redirect กลับไปที่โพสต์
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

    header("Location: index.php?page=post&post_id=$post_id"); // เปลี่ยนเส้นทางกลับไปที่หน้ากระทู้
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

    header("Location: index.php?page=post&post_id=$post_id"); // เปลี่ยนเส้นทางกลับไปที่หน้ากระทู้
    exit();
}
?>

<!-- รวมส่วนหัวของ HTML -->
<?php include_once 'layouts/top_layouts.php'; ?>

<div class="bodyofcontent">
    <div class="item layoutofcon3">
        <h1>แก้ไขคอมเมนต์</h1>
        <div class="insidecon3">
            <form method="POST" action="" enctype="multipart/form-data" class="mt-3">
                <div class="mb-3">
                    <label for="content" class="form-label font-weight-bold">เนื้อหาคอมเมนต์:</label>
                    <textarea name="content" id="content" class="form-control" rows="5" required><?= htmlspecialchars($comment['content']) ?></textarea>
                </div>
                
                <?php if (!empty($comment['image'])): ?>
                    <div class="mb-3">
                        <p class="form-label font-weight-bold">รูปภาพเดิม:</p>
                        <div class="mb-2">
                            <img src="uploads/<?= htmlspecialchars($comment['image']) ?>" alt="Comment Image" class="img-thumbnail" style="max-width: 250px; height: auto;">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteImage()">ลบรูปภาพเดิม</button>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="image" class="form-label font-weight-bold">อัปโหลดรูปภาพใหม่ (ถ้ามี):</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    <div class="mt-2">
                        <img id="image-preview" class="img-thumbnail" style="display: none; max-width: 250px; height: auto;">
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="clearImage()">ยกเลิกการเลือกภาพ</button>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">อัปเดตคอมเมนต์</button>
                    <button type="button" class="btn btn-outline-primary" onclick="window.location.href='index.php?page=post&post_id=<?= $post_id ?>'">ย้อนกลับ</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="item layoutofcon4">
        <?php include_once 'layouts/con4.php'; ?>
    </div>
</div>

<!-- รวมส่วนท้ายของ HTML -->
<?php include_once 'layouts/bottom_layouts.php'; ?>

<script>
function confirmDeleteImage() {
    Swal.fire({
        title: 'ลบรูปภาพนี้?',
        text: 'คุณแน่ใจหรือว่าต้องการลบรูปภาพของคอมเมนต์นี้?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53e3e',
        cancelButtonColor: '#718096',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
    }).then(function(result) {
        if (result.isConfirmed) {
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
    });
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

    fileInput.value = '';
    imagePreview.style.display = 'none';
}
</script>