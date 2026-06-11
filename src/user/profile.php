<?php
include_once 'layouts/dataheader.php';


$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<?php include_once 'layouts/top_layouts.php'; ?>
    <link rel="stylesheet" href="styles/editprofilestyle.css">

<body>
    <div class="profile-container">
        <h1>โปรไฟล์ของคุณ</h1>

        <div class="profile-info">
        <img src="uploads/<?= htmlspecialchars($user['user_img']) ?>" alt="รูปโปรไฟล์" class="profile-picc" onerror="this.src='icon/startprofile.png';">
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($user['first_name']) ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($user['last_name']) ?></p>
            <p><strong>อีเมล:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <a href="index.php?page=edit_profile" class="edit-button">แก้ไขข้อมูล</a>

        <a href="index.php?page=homepage" class="back-button">กลับไปหน้าหลัก</a>
    </div>
</body>

</html>
<?php include_once 'layouts/bottom_layouts.php'; ?>