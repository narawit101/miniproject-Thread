<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DPI</title>
    <link rel="stylesheet" href="styles/layoutsstyle.css">
    <link rel="stylesheet" href="styles/bodyofconstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>

<body>
    <div class="header">
        <nav>
            <a href="homepage.php">
                <img src="icon/logo.png" class="logo" alt="Logo">
            </a>

            <!-- เพิ่มฟอร์มค้นหาที่นี่ -->
            <form action="search_results.php" method="GET">
                <div class="search">
                    <span class="search-icon material-symbols-outlined">search</span>
                    <input class="search-input" type="search" name="search" placeholder="ค้นหา"
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
            </form>
            <ul>
                <li><a href="homepage.php">หน้าแรก</a></li>
                <li><a href="all_feed.php">กระทู้ทั้งหมด</a></li>
                <li><a href="my_post.php">กระทู้ของฉัน</a></li>
                <li><a href="create_post.php">เขียนกระทู้</a></li>

                <!-- เงื่อนไขสำหรับแสดงลิงก์จัดการผู้ใช้เฉพาะ admin -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <li><a href="add_category.php">จัดการหมวดหมู่</a></li>
                    <li><a href="manage_users.php">จัดการผู้ใช้</a></li>
                    <li><a href="feedbyadmin.php">ประกาศ</a></li>
                <?php endif; ?>
            </ul>

            <!-- แสดงรูปโปรไฟล์ของผู้ใช้และชื่อ -->
            <div class="user-info">
                <?php
                // ตรวจสอบว่ามีรูปโปรไฟล์หรือไม่ ถ้าไม่มีให้ใช้รูปไอคอนรูปคนแทน
                $profile_pic_url = !empty($user_hader['user_img']) && file_exists('uploads/' . $user_hader['user_img'])
                    ? 'uploads/' . htmlspecialchars($user_hader['user_img'])
                    : 'icon/startprofile.png'; // ไอคอนรูปคน
                ?>
                <img src="<?= $profile_pic_url ?>" class="profile-pic" alt="Profile Picture" onclick="toggleMenu()">
            </div>

            <div class="sub-menu-wrap" id="subMenu">
                <div class="sub-menu">
                    <div class="user-info">
                        <img src="<?= $profile_pic_url ?>" alt="Profile Picture">
                        <h3><?php echo htmlspecialchars($user_hader['first_name'] . ' ' . $user_hader['last_name']); ?>
                        </h3>
                    </div>
                    <hr>
                    <a href="profile.php" class="sub-menu-link">
                        <img src="icon/editprofile.png">
                        <p>โปรไฟล์</p>
                        <span>></span>
                    </a>
                    <!-- <a href="#" class="sub-menu-link">
                        <img src="icon/contact.png">
                        <p>ช่วยเหลือและสนับสนุน</p>
                        <span>></span>
                    </a> -->
                    <a href="logout.php" class="sub-menu-link">
                        <img src="icon/logout.png">
                        <p>ออกจากระบบ</p>
                        <span>></span>
                    </a>
                </div>
            </div>
        </nav>