<?php
include_once('dataheader.php');
// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
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

<?php include_once('top_layouts.php') ?>
<link rel="stylesheet" href="styles/button.css"> <!-- ลิงก์ไปยังไฟล์ CSS ของคุณ -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<div class="bodyofcontent">

    <div class="item layoutofcon1">
        <?php include_once('category_slide.php') ?>
    </div>


    <div class="item layoutofcon3">
        <h1>กระทู้ของฉัน</h1>
        <!-- Loop โพสต์ของผู้ใช้ที่ล็อกอิน -->
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="insidecon3">
                    <div class="user-profile" style="display: flex; align-items: center;margin-bottom: 30px;">
                        <?php
                        if (!empty($post['user_img']) && file_exists('uploads/' . $post['user_img'])) {
                            $user_img_path = 'uploads/' . htmlspecialchars($post['user_img']);
                        } else {
                            $user_img_path = 'icon/startprofile.png';
                        }
                        ?>
                        <img src="<?= $user_img_path ?>" alt="User Image"
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 100%; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); margin-right: 10px;">
                        <p style="margin: 0;">
                            <strong><?= htmlspecialchars($post['first_name']) ?>
                                <?= htmlspecialchars($post['last_name']) ?></strong>
                        </p>
                    </div>
                    <hr>
                    <div class="post-content" style="margin-bottom: 30px;">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p><?= htmlspecialchars($post['content']) ?></p>
                    </div>

                    <?php if (!empty($post['post_img'])): ?>
                        <img src="uploads/<?= htmlspecialchars($post['post_img']) ?>" alt="Post Image"
                            style="max-width: 80%; height: auto; margin-top: 5px; border-radius: 30px; display: block; margin-left: auto; margin-right: auto;">
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
                            <button type="submit" name="action" value="unlike">ยกเลิกไลค์</button>
                        <?php else: ?>
                            <button type="submit" name="action" value="like">ไลค์</button>
                        <?php endif; ?>
                    </form>
                    <!-- แสดงจำนวนไลค์ -->
                    <p class="like-count" style="margin-top: 15px;margin-bottom: 10px">จำนวนไลค์: <?= $like_count ?></p>

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
                                    <div class="comment-header" style="display: flex; align-items: center;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <?php
                                            // ดึงรูปผู้ใช้
                                            if (!empty($comment['user_img']) && file_exists('uploads/' . $comment['user_img'])):
                                                $comment_user_img_path = 'uploads/' . htmlspecialchars($comment['user_img']);
                                            else:
                                                $comment_user_img_path = 'images/de_icon.png';
                                            endif;
                                            ?>
                                            <img src="<?= $comment_user_img_path ?>" alt="รูปผู้ใช้"
                                                style="width: 30px; height: 30px; object-fit: cover; border-radius: 100%; margin-right: 0px;">

                                            <!-- ชื่อผู้แสดงความคิดเห็น และเวลาที่แสดงความคิดเห็น -->
                                            <div>
                                                <strong><?= htmlspecialchars($comment['first_name']) . ' ' . htmlspecialchars($comment['last_name']) ?></strong>
                                                <span class="comment-date" style="font-size: 0.9em; color: #666;">&bull;
                                                    <?= date('d M Y, H:i', strtotime($comment['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- เนื้อหาคอมเมนต์ -->
                                    <div class="comment-body">
                                        <?php if (!empty($comment['image'])): ?>
                                            <!-- รูปในคอมเมนต์ -->
                                            <img src="uploads/<?= htmlspecialchars($comment['image']) ?>" alt="รูปคอมเมนต์"
                                                style="max-width: 70%; height: auto; margin-top: 5px; border-radius: 10px;">
                                        <?php endif; ?>

                                        <!-- เนื้อหาคอมเมนต์ -->
                                        <p class="comment-content"><?= htmlspecialchars($comment['content']) ?></p>


                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="margin-top: 10px; margin-bottom: 10px;">ยังไม่มีคอมเมนต์</p>
                    <?php endif; ?>

                    <!-- ปุ่ม -->
                    <?php if ($post['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] == 'admin'): ?>
                        <!-- ปุ่มแก้ไขโพสต์ -->
                        <a href="edit_post.php?post_id=<?= $post['post_id'] ?>" class="button-link">แก้ไขโพสต์</a>
                        <!-- ปุ่มลบโพสต์ -->
                        <a href="delete_post.php?post_id=<?= $post['post_id'] ?>" class="button-link delete"
                            onclick="return confirm('ยืนยันการลบข้อมูล')">ลบโพสต์</a>
                    <?php endif; ?>
                    <!-- ปุ่มคอมเมนต์ -->
                    <a href="post.php?post_id=<?= $post['post_id'] ?>" class="button-link comment">คอมเมนต์</a>
                    <!-- ปุ่ม -->
                </div>
            
            <?php endforeach; ?>
        <?php else: ?>
            <p>ไม่พบผลลัพธ์ที่ตรงกับคำค้นหา</p>
        <?php endif; ?>
    </div>
    <div class="item layoutofcon4">
        <?php include_once('con4.php'); ?>
    </div>
</div>
<?php include_once('bottom_layouts.php') ?>
<script>
    document.querySelectorAll('.like-form').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const postId = this.dataset.postId;
            const action = this.querySelector('button').value;

            fetch('toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    post_id: postId,
                    action: action
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.action === 'unlike') {
                        this.querySelector('button').textContent = 'ยกเลิกไลค์';
                        this.querySelector('button').value = 'unlike';
                    } else {
                        this.querySelector('button').textContent = 'ไลค์';
                        this.querySelector('button').value = 'like';
                    }
                    this.nextElementSibling.textContent = 'จำนวนไลค์: ' + data.like_count;
                });
        });
    });
    let toggleButtons = document.querySelectorAll('.toggle-comments-btn');

    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            let postId = this.getAttribute('data-post-id'); // ดึง post_id จาก data attribute
            let commentList = document.querySelector(`.comment-list[data-post-id='${postId}']`);

            // เช็คการแสดงผลของคอมเมนต์
            if (commentList.style.display === 'none' || commentList.style.display === '') {
                commentList.style.display = 'block'; // แสดงคอมเมนต์
                this.textContent = 'ซ่อนคอมเมนต์'; // เปลี่ยนข้อความปุ่ม
            } else {
                commentList.style.display = 'none'; // ซ่อนคอมเมนต์
                this.textContent = `ดูคอมเมนต์ (${commentList.childElementCount})`; // เปลี่ยนข้อความปุ่มกลับไปพร้อมแสดงจำนวนคอมเมนต์
            }
        });
    });
</script>
</body>