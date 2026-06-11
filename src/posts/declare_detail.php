<?php
include_once 'layouts/dataheader.php';

if (isset($_GET['post'])) {
    $post_id = $_GET['post'];
    if (is_numeric($post_id)) {
        $stmt = $conn->prepare("SELECT * FROM announcements WHERE announcement_id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            die('ไม่พบประกาศ');
        }
    } else {
        die('ไม่พบประกาศ');
    }
} else {
    die('ไม่พบประกาศ');
}
?>
<?php include_once 'layouts/top_layouts.php'; ?>

<div class="bodyofcontent">
    <div class="item layoutofcon5 announce-detail-card">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
        <?php if (!empty($post['image'])): ?>
            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="News Image">
        <?php endif; ?>
        <div class="announce-detail-desc mb-4">
            <?php echo nl2br(htmlspecialchars($post['description'])); ?>
        </div>
        <p class="text-muted mb-4"><small>โพสต์เมื่อ: <?php echo formatThaiDate($post['created_at']); ?></small></p>
        <button type="button" class="btn btn-primary w-100" onclick="window.location.href = 'index.php?page=homepage'">ย้อนกลับ</button>
    </div>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>
