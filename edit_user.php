<?php
require 'server.php';

// session_start();
include_once('dataheader.php');

// ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// ตรวจสอบว่ามีการส่ง user_id มาเพื่อแก้ไขหรือไม่
if (!isset($_GET['user_id'])) {
    header('Location: manage_users.php');
    exit();
}

$user_id = $_GET['user_id'];

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ตรวจสอบว่ามีการส่งข้อมูลเพื่อแก้ไขหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // อัปเดตข้อมูลผู้ใช้
    $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$first_name, $last_name, $email, $role, $user_id]);

    // กลับไปที่หน้าจัดการผู้ใช้
    header('Location: manage_users.php?msg=อัปเดตข้อมูลผู้ใช้สำเร็จ');
    exit();
}
?>
<?php include_once('top_layouts.php'); ?>

<!-- <div class="bodyofcontent" style="max-width: 800px; margin: 0 auto; padding: 20px;"> -->

    <div class="item layoutofcon3" >
        <h1>แก้ไขข้อมูลผู้ใช้</h1>

        <div class="insidecon3">
        <form method="POST" class="user-edit-form" style="display: flex; flex-direction: column;">
            <label for="first_name" style="margin-bottom: 5px; font-weight: bold;">ชื่อ:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required
                style="padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">

            <label for="last_name" style="margin-bottom: 5px; font-weight: bold;">นามสกุล:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required
                style="padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">

            <label for="email" style="margin-bottom: 5px; font-weight: bold;">อีเมล:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                style="padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">

            <label for="role" style="margin-bottom: 5px; font-weight: bold;">Role:</label>
            <select name="role" required
                style="padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>

            <div class="form-buttons" style="display: flex; justify-content: space-between;">
                <button type="submit" class="btn btn-save"
                    style="padding: 10px 20px; border: none; border-radius: 5px; background-color: #4CAF50; color: white; cursor: pointer;">บันทึก</button>
                <button type="button" class="btn btn-cancel" onclick="window.location.href='manage_users.php'"
                    style="padding: 10px 20px; border: none; border-radius: 5px; background-color: #f44336; color: white; cursor: pointer;">ย้อนกลับ</button>
            </div>
        </form>
    </div>
</div>

<?php include_once('bottom_layouts.php'); ?>