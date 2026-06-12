<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กระทู้ DPI </title>
    <link rel="icon" type="image/png" href="icon/logo.png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- App Styles -->
    <link rel="stylesheet" href="styles/layoutsstyle.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/bodyofconstyle.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/button.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/commentstyle.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/category_slidestyle.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/manage_userstyle.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/feedbyadmin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/add_categorystyle.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/editprofilestyle.css?v=<?= time() ?>">
</head>

<body>
    <?php $current_page = $_GET['page'] ?? 'homepage'; ?>
    <nav class="navbar navbar-expand-xl navbar-dark header py-2">
        <div class="container-fluid px-md-5 flex-column align-items-stretch">

            <!-- Top Row: Logo, Search, Actions & Profile -->
            <div class="header-top-row d-flex align-items-center justify-content-between w-100 mb-2">
                <!-- Logo -->
                <a class="navbar-brand me-4" href="index.php?page=homepage">
                    <img src="icon/logo.png" class="logo" alt="Logo">
                </a>

                <!-- Search Bar (Desktop only, >= 1200px) -->
                <div class="search-container d-none d-xl-block flex-grow-1 mx-5" style="max-width: 600px;">
                    <form action="index.php" method="GET" class="w-100">
                        <input type="hidden" name="page" value="search_results">
                        <div class="search w-100 m-0">
                            <span class="search-icon material-symbols-outlined">search</span>
                            <input class="search-input" type="search" name="search" placeholder="ค้นหา"
                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        </div>
                    </form>
                </div>

                <!-- Right Side Actions (Profile and Mobile Toggler) -->
                <div class="d-flex align-items-center gap-3">
                    <!-- User Profile Dropdown -->
                    <div class="position-relative">
                        <?php
                        // โหลดข้อมูลผู้ใช้เพิ่มเติมหากยังไม่มีการตั้งค่า
                        if (!isset($user_hader) || !$user_hader) {
                            if (session_status() === PHP_SESSION_NONE) {
                                session_start();
                            }
                            if (isset($_SESSION['user_id'])) {
                                require_once __DIR__ . '/../config/server.php';
                                $stmt = $conn->prepare("SELECT first_name, last_name, user_img FROM users WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $user_hader = $stmt->fetch();
                            }
                        }
                        if (!isset($user_hader) || !$user_hader) {
                            $user_hader = [
                                'first_name' => 'Guest',
                                'last_name' => '',
                                'user_img' => ''
                            ];
                        }
                        $profile_pic_url = !empty($user_hader['user_img']) && file_exists('uploads/' . $user_hader['user_img'])
                            ? 'uploads/' . htmlspecialchars($user_hader['user_img'])
                            : 'icon/startprofile.png';
                        ?>
                        <img src="<?= $profile_pic_url ?>" class="profile-pic" alt="Profile Picture"
                            onclick="toggleMenu()">

                        <!-- Dropdown Submenu -->
                        <div class="sub-menu-wrap" id="subMenu">
                            <div class="sub-menu">
                                <div class="user-info">
                                    <img src="<?= $profile_pic_url ?>" alt="Profile Picture">
                                    <h3><?php echo htmlspecialchars($user_hader['first_name'] . ' ' . $user_hader['last_name']); ?>
                                    </h3>
                                </div>
                                <hr>
                                <a href="index.php?page=profile" class="sub-menu-link">
                                    <img src="icon/editprofile.png">
                                    <p>โปรไฟล์</p>
                                    <span>&gt;</span>
                                </a>
                                <a href="#" data-confirm-logout="index.php?page=logout" class="sub-menu-link">
                                    <img src="icon/logout.png">
                                    <p>ออกจากระบบ</p>
                                    <span>&gt;</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger toggler button for mobile -->
                    <button class="navbar-toggler border-0 d-xl-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>

            <!-- Bottom Row: Collapsible Links and Search Bar for Mobile -->
            <div class="collapse navbar-collapse w-100" id="navbarContent">
                <!-- Search bar for mobile/tablet screens (< 1200px) -->
                <div class="d-xl-none w-100 my-2">
                    <form action="index.php" method="GET" class="w-100">
                        <input type="hidden" name="page" value="search_results">
                        <div class="search w-100 m-0">
                            <span class="search-icon material-symbols-outlined">search</span>
                            <input class="search-input" type="search" name="search" placeholder="ค้นหา"
                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        </div>
                    </form>
                </div>

                <!-- Menu Links -->
                <ul class="navbar-nav mx-xl-auto mb-2 mb-xl-0 gap-2 gap-xl-3 pt-2 pt-xl-0">
                    <li class="nav-item">
                        <a class="nav-link-custom <?= ($current_page == 'homepage') ? 'active' : '' ?>"
                            href="index.php?page=homepage">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom <?= ($current_page == 'all_feed') ? 'active' : '' ?>"
                            href="index.php?page=all_feed">กระทู้ทั้งหมด</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom <?= ($current_page == 'my_post') ? 'active' : '' ?>"
                            href="index.php?page=my_post">กระทู้ของฉัน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom <?= ($current_page == 'create_post') ? 'active' : '' ?>"
                            href="index.php?page=create_post">เขียนกระทู้</a>
                    </li>

                    <!-- Admin management links -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link-custom <?= ($current_page == 'add_category') ? 'active' : '' ?>"
                                href="index.php?page=add_category">จัดการหมวดหมู่</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-custom <?= ($current_page == 'manage_users') ? 'active' : '' ?>"
                                href="index.php?page=manage_users">จัดการผู้ใช้</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-custom <?= ($current_page == 'feedbyadmin') ? 'active' : '' ?>"
                                href="index.php?page=feedbyadmin">ประกาศ</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>