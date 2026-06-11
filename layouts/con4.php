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
    <h2>ประกาศ</h2>
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
                echo '<div class="news-card d-flex flex-column">';
                if (!empty($announce['image'])) {
                    echo '<img src="' . htmlspecialchars($announce['image']) . '" alt="News Image">';
                } else {
                    echo '<div class="news-no-image-header d-flex align-items-center justify-content-center">';
                    echo '<span class="material-symbols-outlined">campaign</span>';
                    echo '</div>';
                }
                echo '<div class="news-content d-flex flex-column flex-grow-1">';
                echo '<div class="news-title">';
                echo '<a href="index.php?page=declare_detail&post=' . $announce_id . '" target="_blank">' . htmlspecialchars($announce['title']) . '</a>';
                echo '</div>';
                echo '<div class="news-description flex-grow-1">';
                echo htmlspecialchars(mb_strimwidth($announce['description'], 0, 100, '...'));
                echo '</div>';
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    echo '<div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">';
                    echo '<button class="btn-edit-announce" onclick="window.location.href=\'index.php?page=feedbyadmin&edit=' . $announce_id . '\'">แก้ไข</button>';
                    echo '<button class="btn-delete-announce" onclick="deletePost(' . $announce_id . ')">ลบ</button>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center w-100 my-4 text-muted">ไม่มีข้อมูลโพสต์</p>';
        }
        ?>
    </div>
</div>