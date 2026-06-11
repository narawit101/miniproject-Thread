<?php
// โหลด categories จาก DB (ถ้ายังไม่ได้โหลด)
if (!isset($conn)) require_once __DIR__ . '/../config/server.php';
$stmt_cat = $conn->prepare("SELECT * FROM categories ORDER BY category_id");
$stmt_cat->execute();
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="styles/category_slidestyle.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<div class="layoutofcon2 swiper">
    <h3>เลือกหมวดหมู่ที่คุณสนใจ</h3>
    <div class="slider-wrapper">
        <div class="categry-list swiper-wrapper">
            <?php foreach ($categories as $category): ?>
                <div class="category-item swiper-slide">
                    <?php if (!empty($category['categorie_icon']) && file_exists($category['categorie_icon'])): ?>
                        <img src="<?= htmlspecialchars($category['categorie_icon']) ?>"
                             alt="<?= htmlspecialchars($category['category_name']) ?>"
                             class="category-img">
                    <?php else: ?>
                        <div class="category-img-placeholder">🗂️</div>
                    <?php endif; ?>
                    <button type="button"
                            onclick="location.href='index.php?page=all_feed&category_id=<?= htmlspecialchars($category['category_id']) ?>'"
                            class="cate-button">
                        <?= htmlspecialchars($category['category_name']) ?>
                    </button>
                </div>
            <?php endforeach; ?>

            <?php if (empty($categories)): ?>
                <div class="swiper-slide" style="text-align:center;padding:20px;color:#666;">
                    ยังไม่มีหมวดหมู่
                </div>
            <?php endif; ?>
        </div>

        <div class="swiper-pagination"></div>
        <div class="swiper-slide-button swiper-button-prev"></div>
        <div class="swiper-slide-button swiper-button-next"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="assets/js/script.js"></script>