<?php
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=logout');
    exit();
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = [];

    if (strlen($new_password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร!';
    }
    if ($new_password !== $confirm_password) {
        $errors[] = 'รหัสผ่านไม่ตรงกัน!';
    }

    if (!empty($errors)) {
        set_swal('error', 'ไม่สามารถเปลี่ยนรหัสผ่านได้', implode(' | ', $errors));
        header('Location: index.php?page=edit_password');
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed_password, $user_id]);
    set_swal('success', 'เปลี่ยนรหัสผ่านสำเร็จ!', 'รหัสผ่านใหม่ของคุณถูกบันทึกแล้ว');
    header('Location: index.php?page=profile');
    exit();
}

include_once 'layouts/top_layouts.php';
?>

<div class="bodyofcontent">

    <div class="item layoutofcon1">
        <?php include_once 'layouts/category_slide.php'; ?>
    </div>

    <div class="item layoutofcon3">
        <h1>เปลี่ยนรหัสผ่าน</h1>
        <div class="insidecon3">
            <form action="index.php?page=edit_password" method="POST" class="mt-3"
                  style="max-width: 420px;">

                <div class="mb-3">
                    <label for="new_password" class="form-label fw-semibold">รหัสผ่านใหม่:</label>
                    <input type="password" id="new_password" name="new_password"
                           class="form-control" required placeholder="อย่างน้อย 6 ตัวอักษร">
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label fw-semibold">ยืนยันรหัสผ่านใหม่:</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           class="form-control" required placeholder="พิมพ์รหัสผ่านอีกครั้ง">
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="window.history.back();">ย้อนกลับ</button>
                </div>
            </form>
        </div>
    </div>

    <div class="item layoutofcon4">
        <?php include_once 'layouts/con4.php'; ?>
    </div>

</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>