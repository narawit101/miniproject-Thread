<?php
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
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
        set_swal('success', 'โพสต์สำเร็จ! 🎉', 'กระทู้ของคุณถูกสร้างแล้ว');
        header('Location: index.php?page=all_feed');
        exit();
    } else {
        set_swal('error', 'เกิดข้อผิดพลาด!', 'ไม่สามารถสร้างกระทู้ได้ กรุณาลองใหม่');
        header('Location: index.php?page=create_post');
        exit();
    }
}
?>

<?php include_once 'layouts/top_layouts.php'; ?>

<div class="bodyofcontent">

    <div class="item layoutofcon1">
        <?php include_once 'layouts/category_slide.php'; ?>
    </div>

    <div class="item layoutofcon3">

        <h1>เขียนกระทู้ของฉัน</h1>
        <div class="boxofpost">
            <!-- HTML Form -->
            <form id="postForm" method="POST" enctype="multipart/form-data" class="mt-3">
                <div class="mb-3">
                    <label for="title" class="form-label font-weight-bold">หัวข้อกระทู้:</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="หัวข้อกระทู้" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">เนื้อหากระทู้:</label>
                    <textarea id="content" name="content" class="form-control" rows="5" placeholder="เนื้อหากระทู้" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">อัปโหลดรูปภาพ (ถ้ามี):</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    <div class="mt-2">
                        <img id="preview" src="#" alt="ตัวอย่างรูปภาพ" class="img-thumbnail" style="display: none; max-width: 100%; height: auto;">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">เลือกหมวดหมู่:</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">เลือกหมวดหมู่</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>">
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">โพสต์กระทู้</button>
                    <button type="button" class="btn btn-secondary" onclick="deleteImage()">ลบรูปภาพ</button>
                    <button type="button" class="btn btn-outline-primary" onclick="window.location.href='index.php?page=homepage'">ย้อนกลับ</button>
                </div>
            </form>
        </div>
    </div>
    <div class="item layoutofcon4">
    <?php include_once 'layouts/con4.php'; ?>
    </div>
</div>

<?php include_once 'layouts/bottom_layouts.php';?>

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
        Swal.fire({
            title: 'ลบรูปภาพ?',
            text: 'คุณแน่ใจหรือว่าต้องการลบรูปภาพที่อัปโหลด?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#718096',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
        }).then(function(result) {
            if (!result.isConfirmed) return;
            const formData = new FormData(document.getElementById('postForm'));
            formData.append('delete_image', '1');
            fetch('index.php?page=create_post', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('preview').style.display = 'none';
                        document.querySelector('input[name="image"]').value = '';
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถลบรูปภาพได้', 'error');
                    }
                })
                .catch(() => Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถเชื่อมต่อได้', 'error'));
        });
    }

    function goBack() {
        window.history.back();
    }
</script>