<?php
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=logout');
    exit();
}


// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = []; // ตัวแปรเก็บข้อความข้อผิดพลาด

    // ตรวจสอบความยาวของรหัสผ่าน
    if (strlen($new_password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร!';
    }

    // ตรวจสอบความตรงกันของรหัสผ่าน
    if ($new_password !== $confirm_password) {
        $errors[] = 'รหัสผ่านไม่ตรงกัน!';
    }

    if (!empty($errors)) {
        set_swal('error', 'ไม่สามารถเปลี่ยนรหัสผ่านได้', implode(' | ', $errors));
        header('Location: index.php?page=edit_password');
        exit();
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        set_swal('success', 'เปลี่ยนรหัสผ่านสำเร็จ!', 'รหัสผ่านใหม่ของคุณถูกบันทึกแล้ว');
        header('Location: index.php?page=profile');
        exit();
    }
}



include_once 'layouts/top_layouts.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
</head>
<link rel="stylesheet" href="styles/editprofilestyle.css">
<body>
<div class="item layoutofcon3">

    <h1>เปลี่ยนรหัสผ่าน</h1>
    <div class="insidecon3">
    <form action="index.php?page=edit_password" method="POST" class="mt-3">
        <div class="mb-3">
            <label for="new_password" class="form-label font-weight-bold">รหัสผ่านใหม่:</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label font-weight-bold">ยืนยันรหัสผ่านใหม่:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary w-100">บันทึกการเปลี่ยนแปลง</button>
            <button type="button" class="btn btn-secondary w-100" onclick="window.history.back();">ย้อนกลับ</button>
        </div>
    </form>
</body>
</div></div>
<?php include_once 'layouts/bottom_layouts.php';
?>