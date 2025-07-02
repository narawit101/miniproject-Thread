<?php
include_once('dataheader.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
// ตรวจสอบว่าผู้ใช้เป็นแอดมินหรือไม่
if ($_SESSION['role'] !== 'admin') {
    // หากไม่ใช่แอดมิน ให้แสดงข้อความและหยุดการทำงาน
    header('Location: logout.php');
    exit();
}
// ดึงข้อมูลผู้ใช้ทั้งหมดจากฐานข้อมูล
$sql = "SELECT * FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();
?>

<?php include_once('top_layouts.php') ?>


    <link rel="stylesheet" href="styles/manage_userstyle.css">
    <div class="item layoutofcon3">
        <h1>จัดการผู้ใช้</h1>
        <div class="insidecon3">
            <h2>ผู้ใช้ Admin</h2>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ชื่อ</th>
                        <th>อีเมล</th>
                        <th>Role</th>
                        <th>การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['role'] == 'admin'): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td>
                                    <a href="edit_user.php?user_id=<?= $user['user_id'] ?>" class="btn btn-primary">แก้ไข</a>
                                    <a href="delete_user.php?user_id=<?= $user['user_id'] ?>"
                                        onclick="return confirm('คุณแน่ใจว่าต้องการลบผู้ใช้นี้หรือไม่?')"
                                        class="btn btn-danger">ลบ</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>ผู้ใช้ทั่วไป</h2>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ชื่อ</th>
                        <th>อีเมล</th>
                        <th>Role</th>
                        <th>การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['role'] != 'admin'): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td>
                                    <a href="edit_user.php?user_id=<?= $user['user_id'] ?>" class="btn btn-primary">แก้ไข</a>
                                    <a href="delete_user.php?user_id=<?= $user['user_id'] ?>"
                                        onclick="return confirm('คุณแน่ใจว่าต้องการลบผู้ใช้นี้หรือไม่?')"
                                        class="btn btn-danger">ลบ</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
   


<?php include_once('bottom_layouts.php') ?>