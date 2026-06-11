<?php
include_once 'layouts/dataheader.php'; // รวมการตั้งค่าข้อมูลและเชื่อมต่อฐานข้อมูล
require_once 'config/swal_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// รับค่า post_id ที่จะทำการแก้ไข
$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    echo "ไม่พบโพสต์ที่จะทำการแก้ไข!";
    exit();
}

// ตรวจสอบสิทธิ์ผู้ใช้ (ผู้สร้างโพสต์หรือ admin)
$isAdmin = $_SESSION['role'] === 'admin';

// ดึงข้อมูลโพสต์จากฐานข้อมูล
$sql = "SELECT * FROM posts WHERE post_id = ?";
$params = [$post_id];

if (!$isAdmin) {
    $sql .= " AND user_id = ?";
    $params[] = $_SESSION['user_id'];
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$post = $stmt->fetch();

if (!$post) {
    echo "ไม่พบโพสต์ที่ต้องการแก้ไข!";
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

    // จัดการการอัปโหลดรูปภาพใหม่
    $post_img = $_FILES['image']['name'];
    $target = "uploads/" . basename($post_img);

    // ลบรูปภาพเก่าถ้ามีการลบ
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if ($post['post_img'] && file_exists("uploads/" . $post['post_img'])) {
            unlink("uploads/" . $post['post_img']);
        }
        $post['post_img'] = null; // อัปเดตข้อมูลในตัวแปร $post
    }

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่
    if (!empty($post_img) && move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // ลบรูปภาพเก่าถ้ามีการอัปโหลดรูปใหม่
        if ($post['post_img'] && file_exists("uploads/" . $post['post_img'])) {
            unlink("uploads/" . $post['post_img']);
        }
        $post['post_img'] = $post_img;
    }

    // อัปเดตข้อมูลโพสต์ในฐานข้อมูล
    if ($post['post_img'] !== null) {
        $sql = "UPDATE posts SET title = ?, content = ?, category_id = ?, post_img = ? WHERE post_id = ?";
        $params = [$title, $content, $category_id, $post['post_img'], $post_id];
    } else {
        $sql = "UPDATE posts SET title = ?, content = ?, category_id = ?, post_img = NULL WHERE post_id = ?";
        $params = [$title, $content, $category_id, $post_id];
    }

    // ถ้าไม่ใช่ admin ให้ตรวจสอบ user_id
    if (!$isAdmin) {
        $sql .= " AND user_id = ?";
        $params[] = $user_id;
    }

    $stmt = $conn->prepare($sql);

    if ($stmt->execute($params)) {
        set_swal('success', 'แก้ไขสำเร็จ! ✅', 'กระทู้ของคุณถูกอัปเดตแล้ว');
        header("Location: index.php?page=post&post_id=$post_id");
        exit();
    } else {
        set_swal('error', 'เกิดข้อผิดพลาด!', 'ไม่สามารถแก้ไขกระทู้ได้ กรุณาลองใหม่');
        header("Location: index.php?page=edit_post&post_id=$post_id");
        exit();
    }
}
?>

<?php include_once 'layouts/top_layouts.php'; ?>

<div class="bodyofcontent">

    <div class="item layoutofcon3">
        <div class="insidecreatepost">
            <div class="boxofpost">
                <h1>แก้ไขกระทู้</h1>
                <!-- HTML Form -->
                <form id="postForm" method="POST" enctype="multipart/form-data" class="mt-3">
                    <div class="mb-3">
                        <label for="title" class="form-label font-weight-bold">หัวข้อกระทู้:</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" placeholder="หัวข้อกระทู้" required>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">เนื้อหากระทู้:</label>
                        <textarea id="content" name="content" class="form-control" rows="5" placeholder="เนื้อหากระทู้" required><?= htmlspecialchars($post['content']) ?></textarea>
                    </div>

                    <!-- แสดงรูปภาพปัจจุบันและให้ตัวเลือกในการลบ -->
                    <div class="mb-3">
                        <?php if (!empty($post['post_img'])): ?>
                            <div class="mb-2">
                                <img id="preview" src="uploads/<?= htmlspecialchars($post['post_img']) ?>" alt="รูปภาพที่อัปโหลด" class="img-thumbnail" style="max-width: 100%; height: auto;">
                                <input type="hidden" name="delete_image" id="delete_image" value="0">
                                <button type="button" class="btn btn-sm btn-danger mt-2 d-block" onclick="deleteImage()">ลบรูปภาพนี้</button>
                            </div>
                        <?php else: ?>
                            <div class="mb-2">
                                <img id="preview" src="#" alt="ตัวอย่างรูปภาพ" class="img-thumbnail" style="display: none; max-width: 100%; height: auto;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ช่องเพิ่มรูปภาพใหม่ -->
                    <div class="mb-3">
                        <label for="image" class="form-label">อัปโหลดรูปใหม่ (ถ้ามี):</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">เลือกหมวดหมู่:</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">เลือกหมวดหมู่</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id'] ?>" <?= ($category['category_id'] == $post['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                        <button type="button" class="btn btn-outline-primary" onclick="history.back()">ย้อนกลับ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="item layoutofcon4">ยังไม่รู้จะใส่อะไร</div>

    <div class="item layoutofcon5">footer</div>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>

<script>
    // ฟังก์ชันพรีวิวรูปภาพ
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('preview');
            output.src = reader.result;
            output.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    // ฟังก์ชันลบรูปภาพ
    function deleteImage() {
        Swal.fire({
            title: 'ลบรูปภาพนี้?',
            text: 'คุณแน่ใจหรือว่าต้องการลบรูปภาพนี้?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#718096',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('delete_image').value = '1';
                document.getElementById('preview').style.display = 'none';
            }
        });
    }

    // ฟังก์ชันย้อนกลับ
    function goBack() {
        window.history.back();
    }
</script>