<?php
include_once('dataheader.php');


$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<?php include_once('top_layouts.php'); ?>
<style>
    /* Profile container styling */
    .profile-container {
        max-width: 600px;
        margin: 0 auto;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        font-family: Arial, sans-serif;
    }

    /* Heading style */
    .profile-container h1 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
    }

    /* Profile info section */
    .profile-info {
        margin-bottom: 20px;
    }

    .profile-info p {
        font-size: 16px;
        color: #555;
        margin: 8px 0;
    }

    /* Profile picture styling */
    .profile-picc {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 20px;
        border: 3px solid #ddd;
    }

    /* Buttons styling */
    .edit-button,
    .back-button {
        display: inline-block;
        margin: 10px;
        padding: 10px 20px;
        font-size: 14px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .edit-button {
        background-color: #007bff;
    }

    .back-button {
        background-color: #f44336;
    }

    .edit-button:hover,
    .back-button:hover {
        opacity: 0.9;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .profile-container {
            padding: 15px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
        }

        .edit-button,
        .back-button {
            padding: 8px 16px;
            font-size: 13px;
        }
    }
</style>

<body>
    <div class="profile-container">
        <h1>โปรไฟล์ของคุณ</h1>

        <div class="profile-info">
        <img src="uploads/<?= htmlspecialchars($user['user_img']) ?>" alt="รูปโปรไฟล์" class="profile-picc" onerror="this.src='icon/startprofile.png';">
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($user['first_name']) ?></p>
            <p><strong>นามสกุล:</strong> <?= htmlspecialchars($user['last_name']) ?></p>
            <p><strong>อีเมล:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <a href="edit_profile.php" class="edit-button">แก้ไขข้อมูล</a>

        <a href="homepage.php" class="back-button">กลับไปหน้าหลัก</a>
    </div>
</body>

</html>
<?php include_once('bottom_layouts.php'); ?>