<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = $_GET['page'] ?? 'homepage';
?>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="styles/feedbyadmin.css?v=<?= time() ?>">
<script>
    function deletePost(index) {
        Swal.fire({
            title: 'คุณต้องการลบประกาศนี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#718096',
            confirmButtonText: 'ยืนยัน ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?page=<?= htmlspecialchars($current_page) ?>&delete=' + index;
            }
        });
    }
</script>

<div class="insidecon4">
    <div class="announce-widget-header d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined announce-header-icon">campaign</span>
        <h2>ประกาศจากระบบ</h2>
    </div>

    <div class="news-wrapper">
        <?php
        global $conn;
        if (!isset($conn)) {
            require_once __DIR__ . '/../config/server.php';
        }

        if (isset($_GET['delete'])) {
            // Security verification: Only admin can delete announcements
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                header('Location: index.php?page=login');
                exit();
            }

            $delete_id = $_GET['delete'];
            if (is_numeric($delete_id)) {
                // Fetch image first to delete it from disk
                $stmt = $conn->prepare("SELECT image FROM announcements WHERE announcement_id = ?");
                $stmt->execute([$delete_id]);
                $announce = $stmt->fetch();
                if ($announce && !empty($announce['image']) && file_exists($announce['image'])) {
                    unlink($announce['image']);
                }

                // Delete from DB
                $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = ?");
                $stmt->execute([$delete_id]);

                // Flash message
                require_once __DIR__ . '/../config/swal_helper.php';
                set_swal('success', 'ลบประกาศสำเร็จ!', 'ประกาศได้ถูกลบออกจากระบบเรียบร้อยแล้ว');

                echo '<script>window.location.href = "index.php?page=' . htmlspecialchars($current_page) . '";</script>';
                exit();
            } else {
                echo '<p>ไม่พบประกาศที่ต้องการลบ</p>';
            }
        }

        $stmt = $conn->prepare("SELECT * FROM announcements ORDER BY announcement_id DESC");
        $stmt->execute();
        $announcements = $stmt->fetchAll();

        if (is_array($announcements) && !empty($announcements)) {
            foreach ($announcements as $announce) {
                $announce_id = $announce['announcement_id'];
                $hasImage = !empty($announce['image']) && file_exists($announce['image']);
                $cardClass = $hasImage ? 'news-card has-image d-flex flex-column' : 'news-card d-flex flex-column';

                echo '<div class="' . $cardClass . '">';
                echo '<a href="index.php?page=declare_detail&post=' . $announce_id . '" target="_blank" class="news-card-link">';
                if ($hasImage) {
                    echo '<div class="news-card-img-wrap">';
                    echo '<img src="' . htmlspecialchars($announce['image']) . '" alt="News Image">';
                    echo '</div>';
                }
                echo '<div class="news-content d-flex flex-column flex-grow-1">';
                echo '<div class="news-meta d-flex align-items-center gap-1">';
                echo '<span class="material-symbols-outlined news-meta-icon">calendar_today</span>';
                echo '<span class="news-date">' . formatThaiDate($announce['created_at'], true) . '</span>';
                echo '</div>';
                echo '<div class="news-title">';
                echo htmlspecialchars($announce['title']);
                echo '</div>';
                echo '<div class="news-description flex-grow-1">';
                echo htmlspecialchars(mb_strimwidth($announce['description'], 0, 100, '...'));
                echo '</div>';
                echo '</div>'; // close news-content
                echo '</a>'; // close news-card-link
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    echo '<div class="news-admin-actions">';
                    echo '<button class="btn-edit-announce" onclick="window.location.href=\'index.php?page=feedbyadmin&edit=' . $announce_id . '\'">';
                    echo '<span class="material-symbols-outlined">edit</span> แก้ไข';
                    echo '</button>';
                    echo '<button class="btn-delete-announce" onclick="deletePost(' . $announce_id . ')">';
                    echo '<span class="material-symbols-outlined">delete</span> ลบ';
                    echo '</button>';
                    echo '</div>';
                }
                echo '</div>';
            }
        } else {
            echo '<p class="text-center w-100 my-4 text-muted">ไม่มีข้อมูลโพสต์</p>';
        }
        ?>
    </div>
</div>