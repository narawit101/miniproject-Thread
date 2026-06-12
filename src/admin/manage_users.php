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

// ดึงข้อมูลแอดมินทั้งหมด
$admin_stmt = $conn->prepare("SELECT * FROM users WHERE role = 'admin' ORDER BY user_id ASC");
$admin_stmt->execute();
$admin_users = $admin_stmt->fetchAll();

// ระบบแบ่งหน้าสำหรับผู้ใช้ทั่วไป
$limit = 5; // แสดงผลหน้าละ 5 คน
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
if ($page_num < 1) $page_num = 1;

// นับจำนวนผู้ใช้ทั่วไปทั้งหมด
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role != 'admin'");
$count_stmt->execute();
$total_general = $count_stmt->fetchColumn();

$total_pages = ceil($total_general / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page_num > $total_pages) $page_num = $total_pages;

$offset = ($page_num - 1) * $limit;

// ดึงข้อมูลผู้ใช้ทั่วไปแบบแบ่งหน้า
$general_stmt = $conn->prepare("SELECT * FROM users WHERE role != 'admin' ORDER BY user_id DESC LIMIT ? OFFSET ?");
$general_stmt->bindValue(1, $limit, PDO::PARAM_INT);
$general_stmt->bindValue(2, $offset, PDO::PARAM_INT);
$general_stmt->execute();
$general_users = $general_stmt->fetchAll();
?>

<?php include_once 'layouts/top_layouts.php'; ?>

<div class="single-card-layout wide">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="m-0" style="color: #123524; font-weight: 700;">จัดการผู้ใช้</h1>
    </div>

    <!-- ส่วนที่ 1: แอดมิน (Admin Users) -->
    <div class="mb-5">
        <h2 class="h5 mb-3 d-flex align-items-center gap-2" style="color: #123524; font-weight: 600;">
            <span class="material-symbols-outlined align-middle">admin_panel_settings</span>
            ผู้จัดการระบบ (Admin)
        </h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle border" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
                <thead class="table-dark">
                    <tr>
                        <th class="px-4 py-3" style="background-color: #123524; color: #F4FFC3; border: none;">ชื่อ-นามสกุล</th>
                        <th class="py-3" style="background-color: #123524; color: #F4FFC3; border: none;">อีเมล</th>
                        <th class="py-3" style="background-color: #123524; color: #F4FFC3; border: none;">สถานะ (Role)</th>
                        <th class="py-3 text-end px-4" style="background-color: #123524; color: #F4FFC3; border: none;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admin_users as $user): ?>
                        <tr>
                            <td class="px-4 py-3 fw-semibold" style="color: #123524;">
                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            </td>
                            <td class="py-3 text-muted"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="py-3">
                                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">Admin</span>
                            </td>
                            <td class="py-3 text-end px-4">
                                <a href="index.php?page=edit_user&user_id=<?= $user['user_id'] ?>"
                                    class="btn btn-sm btn-outline-primary me-2">แก้ไข</a>
                                <a href="index.php?page=delete_user&user_id=<?= $user['user_id'] ?>"
                                    data-confirm="ยืนยันการลบผู้ใช้ '<?= htmlspecialchars($user['first_name']) ?>'?"
                                    class="btn btn-sm btn-outline-danger">ลบ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ส่วนที่ 2: ผู้ใช้ทั่วไป (General Users) -->
    <div>
        <h2 class="h5 mb-3 d-flex align-items-center gap-2" style="color: #123524; font-weight: 600;">
            <span class="material-symbols-outlined align-middle">group</span>
            ผู้ใช้ทั่วไป (Users)
        </h2>
        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle border" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
                <thead class="table-dark">
                    <tr>
                        <th class="px-4 py-3" style="background-color: #123524; color: #F4FFC3; border: none;">ชื่อ-นามสกุล</th>
                        <th class="py-3" style="background-color: #123524; color: #F4FFC3; border: none;">อีเมล</th>
                        <th class="py-3" style="background-color: #123524; color: #F4FFC3; border: none;">สถานะ (Role)</th>
                        <th class="py-3 text-end px-4" style="background-color: #123524; color: #F4FFC3; border: none;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($general_users)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">ไม่มีข้อมูลผู้ใช้ทั่วไป</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($general_users as $user): ?>
                            <tr>
                                <td class="px-4 py-3 fw-semibold" style="color: #123524;">
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                </td>
                                <td class="py-3 text-muted"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="py-3">
                                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">User</span>
                                </td>
                                <td class="py-3 text-end px-4">
                                    <a href="index.php?page=edit_user&user_id=<?= $user['user_id'] ?>"
                                        class="btn btn-sm btn-outline-primary me-2">แก้ไข</a>
                                    <a href="index.php?page=delete_user&user_id=<?= $user['user_id'] ?>"
                                        data-confirm="ยืนยันการลบผู้ใช้ '<?= htmlspecialchars($user['first_name']) ?>'?"
                                        class="btn btn-sm btn-outline-danger">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination pagination-sm">
                    <!-- Previous button -->
                    <li class="page-item <?= ($page_num <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="index.php?page=manage_users&page_num=<?= $page_num - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page_num == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="index.php?page=manage_users&page_num=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next button -->
                    <li class="page-item <?= ($page_num >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="index.php?page=manage_users&page_num=<?= $page_num + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>