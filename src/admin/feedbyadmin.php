<?php
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';

// Handle post editing
$post_to_edit = null;
$edit_id = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE announcement_id = ?");
    $stmt->execute([$edit_id]);
    $post_to_edit = $stmt->fetch();
}

// Handle form submission (create/edit post)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        if (!validateUploadedFileSize($_FILES['image'], 5)) {
            set_swal('error', 'อัปโหลดไม่สำเร็จ!', 'ขนาดรูปภาพต้องไม่เกิน 5MB');
            $redirect_url = $edit_id ? "index.php?page=feedbyadmin&edit=" . $edit_id : "index.php?page=feedbyadmin";
            header("Location: " . $redirect_url);
            exit();
        }
        $target_dir = "uploads/announcements/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $unique_name = $target_dir . 'announce_' . uniqid() . '.' . $ext;
        if (uploadAndCompressImage($_FILES['image'], $unique_name, 1200, 75)) {
            $image = $unique_name;
            // Delete old image if updating
            if ($post_to_edit && !empty($post_to_edit['image']) && file_exists($post_to_edit['image'])) {
                unlink($post_to_edit['image']);
            }
        } else {
            $image = $post_to_edit ? $post_to_edit['image'] : '';
        }
    } else {
        $image = $post_to_edit ? $post_to_edit['image'] : '';
    }

    // Update existing post or add a new one
    if (isset($_POST['edit_index']) && $_POST['edit_index'] !== '') {
        $edit_id = $_POST['edit_index'];
        $stmt = $conn->prepare("UPDATE announcements SET title = ?, description = ?, image = ? WHERE announcement_id = ?");
        $stmt->execute([$title, $description, $image, $edit_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO announcements (title, description, image) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $image]);
    }

    set_swal('success', 'บันทึกประกาศสำเร็จ!', 'ประกาศของคุณถูกบันทึกเรียบร้อยแล้ว');
    header("Location: index.php?page=homepage");
    exit();
}
?>

<?php include_once 'layouts/top_layouts.php'; ?>

<div class="single-card-layout">
    <h1>เขียนประกาศจากระบบ</h1>
    <div class="boxofpost" style="max-width: 600px; margin: 25px auto 0 auto;">
        <h2><?= isset($post_to_edit) ? 'แก้ไขประกาศจากระบบ' : 'โพสต์ประกาศจากระบบใหม่' ?></h2>

        <form method="POST" action="" enctype="multipart/form-data" class="mt-3">
            <input type="hidden" name="edit_index" value="<?= isset($edit_id) ? htmlspecialchars($edit_id) : '' ?>">

            <div class="mb-3">
                <label for="title" class="form-label font-weight-bold">หัวข้อ:</label>
                <input type="text" id="title" name="title" class="form-control"
                    value="<?= isset($post_to_edit) ? htmlspecialchars($post_to_edit['title']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label font-weight-bold">รายละเอียด:</label>
                <textarea id="description" name="description" class="form-control" rows="5"
                    required><?= isset($post_to_edit) ? htmlspecialchars($post_to_edit['description']) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label font-weight-bold">อัปโหลดรูปภาพ:</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*"
                    onchange="previewImage()">
                <div class="mt-2">
                    <img id="imagePreview"
                        src="<?= isset($post_to_edit) && $post_to_edit['image'] ? htmlspecialchars($post_to_edit['image']) : '' ?>"
                        alt="Image Preview" class="img-thumbnail admin-img-preview"
                        <?= isset($post_to_edit) && !empty($post_to_edit['image']) ? '' : 'style="display:none;"' ?>>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit"
                    class="btn btn-primary w-100"><?= isset($post_to_edit) ? 'บันทึกการแก้ไข' : 'โพสต์' ?></button>
                <button type="button" id="cancelButton" class="btn btn-outline-danger w-100" onclick="cancelImage()"
                    <?= isset($post_to_edit) && !empty($post_to_edit['image']) ? '' : 'style="display:none;"' ?>>ยกเลิกรูปภาพ</button>
                <button type="button" class="btn btn-outline-primary w-100" onclick="history.back()">ย้อนกลับ</button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>

<script>
    function previewImage() {
        const file = document.getElementById('image').files[0];
        const preview = document.getElementById('imagePreview');
        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
            preview.style.display = 'block';
            document.getElementById('cancelButton').style.display = 'inline-block';
        };

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
            preview.style.display = 'none';
            document.getElementById('cancelButton').style.display = 'none';
        }
    }

    function cancelImage() {
        const fileInput = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        fileInput.value = "";
        preview.src = "";
        preview.style.display = 'none';
        document.getElementById('cancelButton').style.display = 'none';
    }
</script>