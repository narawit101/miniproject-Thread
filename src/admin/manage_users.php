<?php
include_once 'layouts/dataheader.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=logout');
    exit();
}
// ตรวจสอบว่าผู้ใช้เป็นแอดมินหรือไม่
if ($_SESSION['role'] !== 'admin') {
    // หากไม่ใช่แอดมิน ให้แสดงข้อความและหยุดการทำงาน
    header('Location: index.php?page=logout');
    exit();
}
// ดึงข้อมูลผู้ใช้ทั้งหมดจากฐานข้อมูล
$sql = "SELECT * FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();
?>

<?php include_once 'layouts/top_layouts.php'; ?>

<div class="single-card-layout wide">
    <h1>จัดการผู้ใช้</h1>

    <h2>Admin</h2>
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
                            <a href="index.php?page=edit_user&user_id=<?= $user['user_id'] ?>"
                                class="btn btn-outline-primary btn-sm">แก้ไข</a>
                            <a href="index.php?page=delete_user&user_id=<?= $user['user_id'] ?>"
                                data-confirm="ยืนยันการลบผู้ใช้ '<?= htmlspecialchars($user['first_name']) ?>'?"
                                class="btn btn-outline-danger btn-sm">ลบ</a>
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
                            <a href="index.php?page=edit_user&user_id=<?= $user['user_id'] ?>"
                                class="btn btn-outline-primary btn-sm">แก้ไข</a>
                            <a href="index.php?page=delete_user&user_id=<?= $user['user_id'] ?>"
                                data-confirm="ยืนยันการลบผู้ใช้ '<?= htmlspecialchars($user['first_name']) ?>'?"
                                class="btn btn-outline-danger btn-sm">ลบ</a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>