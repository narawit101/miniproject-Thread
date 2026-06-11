<?php
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=logout');
    exit();
}
// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password =$_POST['password'];
    $email = $_POST['email'];

    // อัปเดตข้อมูลผู้ใช้
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
    $stmt->execute([$first_name, $last_name, $email, $user_id]);

    // ตรวจสอบการลบรูปภาพ
    if (isset($_POST['delete_profile_pic'])) {
        $old_img = $user['user_img'];
        if ($old_img && $old_img !== 'logo.png' && file_exists('uploads/' . $old_img)) {
            @unlink('uploads/' . $old_img);
        }
        $default_image ='logo.png';
        $stmt = $conn->prepare("UPDATE users SET user_img = ? WHERE user_id = ?");
        $stmt->execute([$default_image, $user_id]);
    }

    // ตรวจสอบการอัปโหลดรูปภาพ
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        if (!validateUploadedFileSize($_FILES['profile_pic'], 3)) {
            set_swal('error', 'อัปโหลดไม่สำเร็จ!', 'ขนาดรูปโปรไฟล์ต้องไม่เกิน 3MB');
            header('Location: index.php?page=edit_profile');
            exit();
        }
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['profile_pic']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($filetype, $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_dir = 'uploads/';
            $target = $upload_dir . $new_filename;

            if (uploadAndCompressImage($_FILES['profile_pic'], $target, 400, 75)) {
                // ลบรูปเก่าออกจากเซิร์ฟเวอร์
                $old_img = $user['user_img'];
                if ($old_img && $old_img !== 'logo.png' && file_exists('uploads/' . $old_img)) {
                    @unlink('uploads/' . $old_img);
                }
                // อัปเดตชื่อไฟล์รูปภาพในฐานข้อมูล
                $stmt = $conn->prepare("UPDATE users SET user_img = ? WHERE user_id = ?");
                $stmt->execute([$new_filename, $user_id]);
            }
        }
    }

    // กลับไปยังหน้าโปรไฟล์
    set_swal('success', 'บันทึกสำเร็จ! ✅', 'โปรไฟล์ของคุณถูกอัปเดตแล้ว');
    header('Location: index.php?page=profile');
    exit();
}
?>
<?php include_once 'layouts/top_layouts.php'; ?>
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('preview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

<div class="edit-profile-container">
    <h1>แก้ไขโปรไฟล์</h1>

    <form action="index.php?page=edit_profile" method="POST" enctype="multipart/form-data" class="mt-3">
        <!-- 1. รูปภาพโปรไฟล์ปัจจุบัน (แสดงเด่นชัดด้านบนสุด) -->
        <div class="mb-4 text-center">
            <?php if (!empty($user['user_img']) && file_exists('uploads/' . $user['user_img'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($user['user_img']); ?>" alt="Profile image" class="profile-picc" style="margin-bottom: 10px;">
                <div class="mt-2">
                    <button type="submit" name="delete_profile_pic" class="btn btn-outline-danger btn-sm">ลบรูปโปรไฟล์ปัจจุบัน</button>
                </div>
            <?php else: ?>
                <img src="icon/startprofile.png" alt="Default profile image" class="profile-picc" style="margin-bottom: 10px; border-color: #718096;">
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="first_name" class="form-label font-weight-bold">ชื่อ:</label>
            <input type="text" id="first_name" name="first_name" class="form-control"
                value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label font-weight-bold">นามสกุล:</label>
            <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label font-weight-bold">รหัสผ่านบัญชีผู้ใช้:</label>
            <div>
                <a href="index.php?page=edit_password" class="btn btn-outline-primary w-100">เปลี่ยนรหัสผ่าน</a>
            </div>
        </div>

        <div class="mb-3">
            <label for="profile_pic" class="form-label font-weight-bold">เปลี่ยนรูปโปรไฟล์:</label>
            <input type="file" id="profile_pic" name="profile_pic" class="form-control" accept="image/*" onchange="previewImage(event)">
            <div class="mt-3 text-center">
                <img id="preview" src="#" alt="ตัวอย่างรูปโปรไฟล์ใหม่" class="img-thumbnail rounded-circle" style="display: none; width: 120px; height: 120px; object-fit: cover; border: 3px solid #85A947; margin: 0 auto;">
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary w-100">บันทึกการเปลี่ยนแปลง</button>
            <a href="index.php?page=profile" class="btn btn-outline-primary w-100">ยกเลิก</a>
        </div>
    </form>
</div>
<script>
    document.getElementById('profile_pic').addEventListener('change', function (event) {
        const preview = document.getElementById('preview');
        preview.style.display = 'block';
    });
</script>
<?php include_once 'layouts/bottom_layouts.php'; ?>