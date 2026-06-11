<?php
require_once 'config/server.php';
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';

// ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=login');
    exit();
}

// ตรวจสอบว่ามีการส่ง user_id มาเพื่อแก้ไขหรือไม่
if (!isset($_GET['user_id'])) {
    header('Location: index.php?page=manage_users');
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

    set_swal('success', 'อัปเดตข้อมูลสำเร็จ!', 'ข้อมูลผู้ใช้ถูกบันทึกแล้ว');
    header('Location: index.php?page=manage_users');
    exit();
}
?>
<?php include_once 'layouts/top_layouts.php'; ?>

<div class="bodyofcontent">
    <div class="item layoutofcon3">
        <h1>แก้ไขข้อมูลผู้ใช้</h1>

        <div class="insidecon3">
            <form method="POST" class="user-edit-form mt-3">
                <div class="mb-3">
                    <label for="first_name" class="form-label font-weight-bold">ชื่อ:</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label font-weight-bold">นามสกุล:</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label font-weight-bold">อีเมล:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label font-weight-bold">สิทธิ์การใช้งาน (Role):</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User (สมาชิกทั่วไป)</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin (ผู้ดูแลระบบ)</option>
                    </select>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                    <button type="button" class="btn btn-outline-primary" onclick="window.location.href = 'index.php?page=manage_users'">ย้อนกลับ</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="item layoutofcon4">
        <?php include_once 'layouts/con4.php'; ?>
    </div>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>