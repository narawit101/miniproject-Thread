<?php
require_once 'config/server.php'; // รวมการเชื่อมต่อกับฐานข้อมูล

include_once 'layouts/dataheader.php'; // รวมส่วนหัวข้อมูล

// เก็บ URL ของหน้าก่อนหน้าในเซสชัน
if (!isset($_SESSION['previous_page'])) {
    $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];
}



$post_id = $_GET['post_id']; // รับ post_id จาก URL

// ดึงข้อมูลกระทู้จากฐานข้อมูล
$sql = "SELECT posts.*, users.first_name, users.last_name, users.user_img
FROM posts 
JOIN users ON posts.user_id = users.user_id 
WHERE post_id = ?"; // ใช้ JOIN เพื่อรวมข้อมูลจากตาราง posts และ users
$stmt = $conn->prepare($sql);
$stmt->execute([$post_id]);
$post = $stmt->fetch();

// ดึงข้อมูลคอมเมนต์ที่เกี่ยวข้องกับกระทู้นี้ พร้อมกับชื่อผู้ใช้และรูปภาพ
$sql = "SELECT comments.*, users.first_name, users.last_name, users.user_img
        FROM comments 
        JOIN users ON comments.user_id = users.user_id 
        WHERE post_id = ? 
        ORDER BY comments.created_at DESC"; // เรียงตามวันที่สร้างคอมเมนต์จากใหม่ไปเก่า
$stmt = $conn->prepare($sql);
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // ตรวจสอบว่าเป็นการส่งข้อมูลจากฟอร์มหรือไม่
    $content = $_POST['content']; // รับเนื้อหาคอมเมนต์
    $user_id = $_SESSION['user_id']; // รับ user_id จาก session

    // จัดการการอัปโหลดรูปภาพ
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        if (!validateUploadedFileSize($_FILES['image'], 5)) {
            set_swal('error', 'อัปโหลดไม่สำเร็จ!', 'ขนาดรูปภาพต้องไม่เกิน 5MB');
            header("Location: index.php?page=post&post_id=$post_id");
            exit();
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image = 'comment_' . uniqid() . '.' . $ext;
        $target = "uploads/" . $image;
        if (!uploadAndCompressImage($_FILES['image'], $target, 1000, 75)) {
            $image = null;
        }
    }

    // บันทึกคอมเมนต์ในฐานข้อมูล
    $sql = "INSERT INTO comments (post_id, user_id, content, image, created_at) VALUES (?, ?, ?, ?, NOW())"; // คำสั่ง SQL สำหรับบันทึกข้อมูล
    $stmt = $conn->prepare($sql); // เตรียมคำสั่ง SQL
    $stmt->execute([$post_id, $user_id, $content, $image]); // ทำการบันทึกข้อมูล

    header("Location: index.php?page=post&post_id=$post_id"); // เปลี่ยนเส้นทางกลับไปที่หน้ากระทู้
    exit();
}
include_once 'layouts/top_layouts.php';

?>

<div class="bodyofcontent">
    <div class="item layoutofcon1">
        <?php include_once 'layouts/category_slide.php'; ?>
    </div>
    <div class="item layoutofcon3">
        <div class="insidecon3">
            <div class="user-profile">
                <?php
                // ตรวจสอบว่ามีรูปโปรไฟล์หรือไม่ และเส้นทางไฟล์ถูกต้องหรือไม่
                if (!empty($post['user_img']) && file_exists('uploads/' . $post['user_img'])):
                    $user_img_path = 'uploads/' . htmlspecialchars($post['user_img']);
                else:
                    // หากไม่มีรูปโปรไฟล์ ให้ใช้รูปผู้ใช้เริ่มต้น
                    $user_img_path = 'icon/startprofile.png';
                endif;
                ?>
                <img src="<?= $user_img_path ?>" alt="User Image">
                <div>
                    <p class="user-name">
                        <strong><?= htmlspecialchars($post['first_name']) ?>
                            <?= htmlspecialchars($post['last_name']) ?></strong>
                    </p>
                    <span class="post-time"><?= formatThaiDate($post['created_at']) ?></span>
                </div>
            </div>

            <!-- เพิ่มระยะห่างระหว่างโปรไฟล์กับโพสต์ -->
            <div class="post-content">
                <h2><?= htmlspecialchars($post['title']) ?></h2>
                <p><?= htmlspecialchars($post['content']) ?></p>
            </div>

            <!-- สิ้นสุดรูปกับโพส -->
            <?php if (!empty($post['post_img'])): ?>
            <?php
                $post_img_path = 'uploads/' . $post['post_img'];
                if (file_exists('uploads/posts/' . $post['post_img'])) {
                    $post_img_path = 'uploads/posts/' . $post['post_img'];
                }
                ?>
            <img src="<?= htmlspecialchars($post_img_path) ?>" alt="Post Image" class="post-img">
            <?php endif; ?>
            <!-- ฟอร์มสำหรับตอบคอมเมนต์ -->
            <form method="POST" action="" class="comment-form" enctype="multipart/form-data">
                <input type="hidden" name="post_id" value="<?= $post_id ?>"> <!-- ซ่อน post_id -->

                <textarea name="content" placeholder="เขียนคอมเมนต์..." required></textarea>
                <!-- ฟิลด์สำหรับคอมเมนต์ -->

                <label for="image" class="form-label mt-2">อัปโหลดรูปภาพ (ถ้ามี):</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*"
                    onchange="previewImage(event)">
                <!-- ฟิลด์สำหรับอัปโหลดรูปภาพ -->

                <!-- ส่วนพรีวิวรูปภาพ -->
                <div id="image-preview-container" class="img-preview-container">
                    <img id="image-preview" src="#" alt="Image Preview">
                    <div>
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="clearImage()">
                            ลบรูปภาพ
                        </button>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3 ">
                    <button type="submit" class="btn btn-primary">โพสต์คอมเมนต์</button>
                    <!-- ปุ่มย้อนกลับไปหน้าก่อนหน้า -->
                    <button type="button" class="btn btn-outline-primary" onclick="customBack()">ย้อนกลับ</button>
                </div>
            </form>

            <h3 class="mt-4 mb-3">คอมเมนต์ทั้งหมด</h3>
            <?php if (count($comments) > 0): ?>
            <ul class="comment-list">
                <?php foreach ($comments as $comment): ?>
                <li class="comment-item">
                    <div class="comment-header">
                        <!-- แสดงรูปโปรไฟล์ของผู้ที่คอมเมนต์ -->
                        <div class="comment-user-info">
                            <?php
                                    if (!empty($comment['user_img']) && file_exists('uploads/' . $comment['user_img'])):
                                        $comment_user_img_path = 'uploads/' . htmlspecialchars($comment['user_img']);
                                    else:
                                        $comment_user_img_path = 'icon/startprofile.png';
                                    endif;
                                    ?>
                            <img src="<?= $comment_user_img_path ?>" alt="User Image" class="comment-avatar"
                                style="width: 40px; height: 40px;">
                            <div>
                                <strong
                                    class="comment-author"><?= htmlspecialchars($comment['first_name']) . ' ' . htmlspecialchars($comment['last_name']) ?></strong>
                                <p class="comment-date">
                                    <?= formatThaiDate($comment['created_at']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="comment-body">
                        <!-- รูปภาพในคอมเมนต์ -->
                        <?php if (!empty($comment['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($comment['image']) ?>" alt="Comment Image"
                            class="comment-img">
                        <?php endif; ?>

                        <!-- เนื้อหาคอมเมนต์ -->
                        <div class="comment-content">
                            <p>
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            </p>
                        </div>
                    </div>
                    <div class="comment-footer">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] == 'admin'): ?>
                        <div class="comment-actions">
                            <a href="index.php?page=edit_comment&comment_id=<?= $comment['comment_id'] ?>&post_id=<?= $post_id ?>"
                                class="edit">แก้ไข</a>
                            <a href="index.php?page=delete_comment&comment_id=<?= $comment['comment_id'] ?>&post_id=<?= $post_id ?>"
                                class="delete" data-confirm="ยืนยันการลบความคิดเห็นนี้?">ลบ</a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="color: #777; font-style: italic;">ยังไม่มีคอมเมนต์</p> <!-- ถ้าไม่มีคอมเมนต์ให้แสดงข้อความนี้ -->
            <?php endif; ?>
        </div>


    </div>
    <div class="item layoutofcon4">
        <?php include_once 'layouts/con4.php'; ?>
    </div>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>
<script>
// ฟังก์ชันล้างข้อมูลรูปภาพ
function clearImage() {
    const imageInput = document.getElementById('image');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');

    imageInput.value = ''; // ล้างข้อมูลใน input
    imagePreview.src = '#'; // ล้างข้อมูลพรีวิวรูปภาพ
    imagePreviewContainer.style.display = 'none'; // ซ่อน container ของพรีวิวรูปภาพ
}

// ฟังก์ชันแสดงพรีวิวรูปภาพ
function previewImage(event) {
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const file = event.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result; // ตั้งค่า src ของพรีวิวรูปภาพ
            imagePreviewContainer.style.display = 'block'; // แสดง container ของพรีวิวรูปภาพ
        }
        reader.readAsDataURL(file);
    }
}

function customBack() {
    const previousURL = "<?php echo $_SESSION['previous_page']; ?>";
    if (previousURL.includes('edit_comment')) {
        window.location.href = 'index.php?page=all_feed'; // เปลี่ยนเส้นทางไปยัง all_feed.php
    } else {
        window.location.href = previousURL; // ย้อนกลับไปหน้าก่อนหน้า
    }
}
</script>