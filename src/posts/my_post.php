<?php
include_once 'layouts/dataheader.php';
// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=logout');
    exit();
}

$user_id = $_SESSION['user_id'];
$search_query = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// ปรับ SQL เพื่อใช้ในการค้นหาโพสต์ของผู้ใช้ที่ล็อกอินและชื่อหรือเนื้อหาของโพสต์
$sql = "
    SELECT posts.*, users.first_name, users.last_name, users.user_img
    FROM posts 
    JOIN users ON posts.user_id = users.user_id 
    WHERE posts.user_id = ? AND (posts.title LIKE ? OR posts.content LIKE ?)
    ORDER BY posts.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id, $search_query, $search_query]);
$posts = $stmt->fetchAll();
?>

<?php include_once 'layouts/top_layouts.php';?>

<div class="bodyofcontent">

    <div class="item layoutofcon1">
        <?php include_once 'layouts/category_slide.php';?>
    </div>


    <div class="item layoutofcon3">
        <h1>กระทู้ของฉัน</h1>
        <!-- Loop โพสต์ของผู้ใช้ที่ล็อกอิน -->
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="insidecon3">
                    <div class="user-profile">
                        <?php
                        if (!empty($post['user_img']) && file_exists('uploads/' . $post['user_img'])) {
                            $user_img_path = 'uploads/' . htmlspecialchars($post['user_img']);
                        } else {
                            $user_img_path = 'icon/startprofile.png';
                        }
                        ?>
                        <img src="<?= $user_img_path ?>" alt="User Image">
                        <div>
                            <p class="user-name">
                                <strong><?= htmlspecialchars($post['first_name']) ?> <?= htmlspecialchars($post['last_name']) ?></strong>
                            </p>
                            <span class="post-time"><?= formatThaiDate($post['created_at']) ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="post-content">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p><?= htmlspecialchars($post['content']) ?></p>
                    </div>

                    <?php if (!empty($post['post_img'])): ?>
                        <?php
                        $post_img_path = 'uploads/' . $post['post_img'];
                        if (file_exists('uploads/posts/' . $post['post_img'])) {
                            $post_img_path = 'uploads/posts/' . $post['post_img'];
                        }
                        ?>
                        <img src="<?= htmlspecialchars($post_img_path) ?>" alt="Post Image" class="post-img">
                    <?php endif; ?>

                    <?php
                    // ไลค์ระบบ
                    $post_id = $post['post_id'];
                    $sql_like = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
                    $stmt_like = $conn->prepare($sql_like);
                    $stmt_like->execute([$user_hader_id, $post_id]);
                    $like = $stmt_like->fetch();

                    // ดึงจำนวนไลค์ทั้งหมด
                    $sql_like_count = "SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?";
                    $stmt_like_count = $conn->prepare($sql_like_count);
                    $stmt_like_count->execute([$post_id]);
                    $like_count = $stmt_like_count->fetch()['like_count'];
                    ?>
                    <form method="POST" class="like-form" data-post-id="<?= $post_id ?>">
                        <input type="hidden" name="post_id" value="<?= $post_id ?>">
                        <?php if ($like): ?>
                            <button type="submit" name="action" value="unlike" class="btn-like liked">♥ ยกเลิกไลค์</button>
                        <?php else: ?>
                            <button type="submit" name="action" value="like" class="btn-like">♡ ไลค์</button>
                        <?php endif; ?>
                    </form>
                    <!-- แสดงจำนวนไลค์ -->
                    <p class="like-count">จำนวนไลค์: <?= $like_count ?></p>

                    <?php
                    // นับจำนวนคอมเมนต์ทั้งหมดสำหรับโพสต์นั้นๆ
                    $sql_comment_count = "SELECT COUNT(*) as comment_count FROM comments WHERE post_id = ?";
                    $stmt_comment_count = $conn->prepare($sql_comment_count);
                    $stmt_comment_count->execute([$post_id]);
                    $comment_count = $stmt_comment_count->fetch()['comment_count'];

                    // ดึงคอมเมนต์ล่าสุด 3 คอมเมนต์
                    $sql_comments = "
                        SELECT comments.*, users.first_name, users.last_name,users.user_img
                        FROM comments 
                        JOIN users ON comments.user_id = users.user_id 
                        WHERE post_id = ? 
                        ORDER BY comments.created_at DESC 
                        LIMIT 10";
                    $stmt_comments = $conn->prepare($sql_comments);
                    $stmt_comments->execute([$post_id]);
                    $comments = $stmt_comments->fetchAll();
                    ?>

                    <?php if (count($comments) > 0): ?>
                        <!-- ปุ่มเพื่อแสดง/ซ่อนคอมเมนต์ และแสดงจำนวนคอมเมนต์ -->
                        <button class="toggle-comments-btn" data-post-id="<?= $post_id ?>">ดูคอมเมนต์
                            (<?= $comment_count ?>)</button>

                        <!-- คอมเมนต์ที่จะแสดง/ซ่อน -->
                        <ul class="comment-list" data-post-id="<?= $post_id ?>" style="display: none;">
                            <?php foreach ($comments as $comment): ?>
                                <li class="comment-item">
                                    <div class="comment-header">
                                        <div class="comment-user-info">
                                            <?php
                                            // ดึงรูปผู้ใช้
                                            if (!empty($comment['user_img']) && file_exists('uploads/' . $comment['user_img'])):
                                                $comment_user_img_path = 'uploads/' . htmlspecialchars($comment['user_img']);
                                            else:
                                                $comment_user_img_path = 'icon/startprofile.png';
                                            endif;
                                            ?>
                                            <img src="<?= $comment_user_img_path ?>" alt="รูปผู้ใช้" class="comment-avatar">

                                            <!-- ชื่อผู้แสดงความคิดเห็น และเวลาที่แสดงความคิดเห็น -->
                                            <div>
                                                <strong><?= htmlspecialchars($comment['first_name']) . ' ' . htmlspecialchars($comment['last_name']) ?></strong>
                                                <span class="comment-date">&bull;
                                                    <?= formatThaiDate($comment['created_at']) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- เนื้อหาคอมเมนต์ -->
                                    <div class="comment-body">
                                        <?php if (!empty($comment['image'])): ?>
                                            <!-- รูปในคอมเมนต์ -->
                                            <img src="uploads/<?= htmlspecialchars($comment['image']) ?>" alt="รูปคอมเมนต์" class="comment-img">
                                        <?php endif; ?>

                                        <!-- เนื้อหาคอมเมนต์ -->
                                        <p class="comment-content"><?= htmlspecialchars($comment['content']) ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="margin-top: 10px; margin-bottom: 10px;">ยังไม่มีคอมเมนต์</p>
                    <?php endif; ?>

                    <!-- ปุ่ม -->
                    <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] == 'admin'): ?>
                        <!-- ปุ่มแก้ไขโพสต์ -->
                        <a href="index.php?page=edit_post&post_id=<?= $post['post_id'] ?>" class="button-link">แก้ไขโพสต์</a>
                        <!-- ปุ่มลบโพสต์ -->
                        <a href="index.php?page=delete_post&post_id=<?= $post['post_id'] ?>" class="button-link delete"
                            data-confirm="ยืนยันการลบกระทู้นี้?">ลบโพสต์</a>
                    <?php endif; ?>
                    <!-- ปุ่มคอมเมนต์ -->
                    <a href="index.php?page=post&post_id=<?= $post['post_id'] ?>" class="button-link comment">คอมเมนต์</a>
                    <!-- ปุ่ม -->
                </div>
            
            <?php endforeach; ?>
        <?php else: ?>
            <div class="insidecon3 text-center py-4">
                <p class="text-muted mb-0" style="font-size: 16px; font-weight: 500;">ไม่พบผลลัพธ์ที่ตรงกับคำค้นหา</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="item layoutofcon4">
        <?php include_once 'layouts/con4.php'; ?>
    </div>
</div>
<?php include_once 'layouts/bottom_layouts.php';?>