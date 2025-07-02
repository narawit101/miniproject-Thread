<?php
include_once('dataheader.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลหมวดหมู่จากฐานข้อมูล
$sql_categories = "SELECT * FROM categories";
$stmt_categories = $conn->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id']; // รับค่า category_id
    $user_id = $_SESSION['user_id'];

    // จัดการการอัปโหลดรูปภาพ
    $post_img = $_FILES['image']['name'];
    $target = "uploads/" . basename($post_img);

    // ลบรูปภาพเก่าถ้ามี
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        $old_img_query = "SELECT post_img FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $old_img_stmt = $conn->prepare($old_img_query);
        $old_img_stmt->execute([$user_id]);
        $old_img = $old_img_stmt->fetchColumn();

        if ($old_img && file_exists("uploads/" . $old_img)) {
            unlink("uploads/" . $old_img);
        }
        echo json_encode(['status' => 'success']);
        exit();
    }
    if (!empty($post_img) && move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $sql = "INSERT INTO posts (title, content, user_id, post_img, category_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $params = [$title, $content, $user_id, $post_img, $category_id];
    } else {
        $sql = "INSERT INTO posts (title, content, user_id, category_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $params = [$title, $content, $user_id, $category_id];
    }

    if ($stmt->execute($params)) {
        header('Location: all_feed.php');
        exit();
    } else {
        echo "เกิดข้อผิดพลาด!";
    }
}
?>

<?php include_once('top_layouts.php'); ?>

<div class="bodyofcontent">

    <div class="item layoutofcon1">
        <?php include_once('category_slide.php'); ?>
    </div>

    <div class="item layoutofcon3">

        <h1>เขียนกระทู้ของฉัน</h1>
        <div class="boxofpost">
            <!-- HTML Form -->
            <form id="postForm" method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="หัวข้อกระทู้" required>
                <textarea name="content" placeholder="เนื้อหากระทู้" required></textarea>
                <input type="file" name="image" accept="image/*" onchange="previewImage(event)">
                <img id="preview" src="#" alt="ตัวอย่างรูปภาพ"
                    style="display: none; max-width: 100%; height: a    uto;">
                <label for="category">เลือกหมวดหมู่:</label>
                <select name="category_id" required>
                    <option value="">เลือกหมวดหมู่</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>">
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">โพสต์กระทู้</button>
                <button type="button" onclick="deleteImage()">ลบรูปภาพที่อัปโหลดก่อนหน้า</button>
                <button type="button"><a href="homepage.php">ย้อนกลับ</a></button>
            </form>
        </div>
    </div>
    <div class="item layoutofcon4">
    <?php include_once('con4.php'); ?>
    </div>
</div>

<?php include_once('bottom_layouts.php') ?>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('preview');
            output.src = reader.result;
            output.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    function deleteImage() {
        if (confirm('คุณแน่ใจหรือว่าต้องการลบรูปภาพที่อัปโหลดก่อนหน้า?')) {
            const formData = new FormData(document.getElementById('postForm'));
            formData.append('delete_image', '1');

            fetch('create_post.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('preview').style.display = 'none';
                        document.querySelector('input[name="image"]').value = '';
                    } else {
                        alert('เกิดข้อผิดพลาดในการลบรูปภาพ');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }

    function goBack() {
        window.history.back();
    }
</script>