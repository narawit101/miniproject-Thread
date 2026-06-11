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
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) { // ตรวจสอบว่ามีการอัปโหลดรูปภาพหรือไม่
        $image = $_FILES['image']['name']; // รับชื่อไฟล์รูปภาพ
        $target = "uploads/" . basename($image); // ตั้งค่าเส้นทางที่จะเก็บไฟล์
        move_uploaded_file($_FILES['image']['tmp_name'], $target); // ย้ายไฟล์ไปยังตำแหน่งที่กำหนด
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
    <!-- <div class="item layoutofcon1">
        <img src="images/illustration.png" class="wellcome-pic">
    </div> -->
    <div class="item layoutofcon3">
        <div class="insidecon3">
            <div class="user-profile">
                <?php
                // ตรวจสอบว่ามีรูปโปรไฟล์หรือไม่ และเส้นทางไฟล์ถูกต้องหรือไม่
                if (!empty($post['user_img']) && file_exists('uploads/' . $post['user_img'])):
                    $user_img_path = 'uploads/' . htmlspecialchars($post['user_img']);
                else:
                    // หากไม่มีรูปโปรไฟล์ ให้ใช้รูปผู้ใช้เริ่มต้น
                    $user_img_path = 'images/de_icon.png';
                endif;
                ?>

                <!-- แสดงโปรไฟล์ -->
                <div class="user-profile"
                    style="display: flex; align-items: center;margin-bottom: 10px;margin-top: 10px;margin-left: 10px">
                    <!-- แสดงรูปโปรไฟล์ -->
                    <img src="<?= $user_img_path ?>" alt="User Image"
                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 100%; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); margin-right: 10px;">

                    <!-- แสดงชื่อผู้ใช้ -->
                    <p style="margin: 0;">
                        <strong><?= htmlspecialchars($post['first_name']) ?>
                            <?= htmlspecialchars($post['last_name']) ?></strong>
                    </p>
                </div>

            </div>

            <!-- เพิ่มระยะห่างระหว่างโปรไฟล์กับโพสต์ -->
            <div class="post-content" style="margin-bottom: 10px;margin-left: 20px">
                <h2><?= htmlspecialchars($post['title']) ?></h2>
                <p><?= htmlspecialchars($post['content']) ?></p>
            </div>

            <!-- สิ้นสุดรูปกับโพส -->
            <?php if (!empty($post['post_img']) && file_exists('uploads/' . $post['post_img'])): ?>
            <img src="uploads/<?= htmlspecialchars($post['post_img']) ?>" alt="Post Image"
                style="max-width: 80%; height: auto; margin-top: 5px; border-radius: 30px; display: block; margin-left: auto; margin-right: auto;">
            <?php endif; ?>
            <!-- ฟอร์มสำหรับตอบคอมเมนต์ -->
            <form method="POST" action="" class="comment-form" enctype="multipart/form-data">
                <link rel="stylesheet" href="styles/commentstyle.css"> <!-- ลิงก์ไปยังไฟล์ CSS ของคุณ -->
                <input type="hidden" name="post_id" value="<?= $post_id ?>"> <!-- ซ่อน post_id -->

                <textarea name="content" placeholder="เขียนคอมเมนต์..." required></textarea>
                <!-- ฟิลด์สำหรับคอมเมนต์ -->

                <label for="image">อัปโหลดรูปภาพ (ถ้ามี):</label>
                <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)">
                <!-- ฟิลด์สำหรับอัปโหลดรูปภาพ -->

                <!-- เพิ่ม class และ id ให้กับส่วนพรีวิวเพื่อให้ตกแต่งง่ายขึ้น -->
                <div id="image-preview-container" style="display: none; text-align: center; margin-top: 10px;">
                    <img id="image-preview" src="#" alt="Image Preview"
                        style="max-width: 70%; height: auto; border-radius: 15px; box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);">
                    <button type="button" onclick="clearImage()"
                        style="margin-top: 10px; padding: 8px 16px; background-color: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        ลบรูปภาพ
                    </button>
                </div>

                <button type="submit" style="margin-top: 10px margin-bottom 5px; ">โพสต์คอมเมนต์</button>
                <!-- ปุ่มส่งคอมเมนต์ -->
                <!-- ปุ่มย้อนกลับไปหน้าก่อนหน้า -->
                <button type="button" onclick="customBack()">ย้อนกลับ</button>
            </form>

            <h3 style="margin-top: 10px; margin-bottom: 10px;">คอมเมนต์ทั้งหมด</h3>
            <?php if (count($comments) > 0): ?>
            <ul class="comment-list" style="list-style: none; padding: 0;">
                <?php foreach ($comments as $comment): ?>
                <li class="comment-item"
                    style="background-color: #fff; margin-bottom: 20px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);">
                    <div class="comment-header" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <!-- แสดงรูปโปรไฟล์ของผู้ที่คอมเมนต์ -->
                        <?php
                                if (!empty($comment['user_img']) && file_exists('uploads/' . $comment['user_img'])):
                                    $comment_user_img_path = 'uploads/' . htmlspecialchars($comment['user_img']);
                                else:
                                    $comment_user_img_path = 'images/de_icon.png';
                                endif;
                                ?>
                        <img src="<?= $comment_user_img_path ?>" alt="User Image"
                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; margin-right: 15px; border: 2px solid #eee;">
                        <div>
                            <strong
                                style="font-size: 1.1em; color: #333;"><?= htmlspecialchars($comment['first_name']) . ' ' . htmlspecialchars($comment['last_name']) ?></strong>
                            <p class="comment-date" style="font-size: 0.9em; color: #aaa; margin: 5px 0 0;">
                                <?= date('d M Y, H:i', strtotime($comment['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <div class="comment-body" style="margin-left: 55px;">
                        <!-- รูปภาพในคอมเมนต์ -->
                        <?php if (!empty($comment['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($comment['image']) ?>" alt="Comment Image"
                            style="max-width: 80%; height: auto; margin-top: 10px; border-radius: 10px;">
                        <?php endif; ?>

                        <!-- เนื้อหาคอมเมนต์ -->
                        <div class="comment-content" style="margin-top: 10px;">
                            <p style="font-size: 1.1em; line-height: 1.4; color: #444;">
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            </p>
                        </div>
                    </div>
                    <div class="comment-footer" style="display: flex; justify-content: flex-end; margin-top: 10px;">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] == 'admin'): ?>
                        <div class="comment-actions" style="font-size: 0.9em;">
                            <a href="index.php?page=edit_comment&comment_id=<?= $comment['comment_id'] ?>&post_id=<?= $post_id ?>"
                                style="color: #007bff; text-decoration: none; margin-right: 10px;">แก้ไข</a>
                            <a href="index.php?page=delete_comment&comment_id=<?= $comment['comment_id'] ?>&post_id=<?= $post_id ?>"
                                style="color: #dc3545; text-decoration: none;"
                                data-confirm="ยืนยันการลบความคิดเห็นนี้?">ลบ</a>
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
    <!-- <div class="item layoutofcon4"></div> -->
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