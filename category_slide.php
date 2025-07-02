<?php
include_once('dataheader.php');
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY category_id");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/category_slidestyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
</head>

<body>
    <div class="item layoutofcon2 swiper">
        <h3>เลือกหมวดหมู่ที่คุณสนใจ</h3>
        <div class="slider-wrapper">
            <div class="categry-list swiper-wrapper">
                <?php foreach ($categories as $category): ?>
                    <div class="category-item swiper-slide">
                        <?php if (!empty($category['categorie_icon'])): ?>
                            <img src="<?= htmlspecialchars($category['categorie_icon']) ?>"
                                alt="<?= htmlspecialchars($category['category_name']) ?> icon" class="category-img">
                        <?php endif; ?>
                        <button type="button"
                            onclick="location.href='all_feed.php?category_id=<?= htmlspecialchars($category['category_id']) ?>'"
                            class="cate-button"><?= htmlspecialchars($category['category_name']) ?></button>
                    </div>
                <?php endforeach; ?>

            </div>

            <div class="swiper-pagination"></div>
            <div class="swiper-slide-button swiper-button-prev"></div>
            <div class="swiper-slide-button swiper-button-next"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>