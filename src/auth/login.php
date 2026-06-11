<?php
require_once 'config/server.php';
require_once 'config/swal_helper.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$swal_inline = null; // สำหรับ error ที่แสดง inline ไม่ต้อง redirect
$swal_data = null;
if (isset($_SESSION['swal'])) {
    $swal_data = $_SESSION['swal'];
    unset($_SESSION['swal']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($email === 'admin@gmail.com') {
                    $user['role'] = 'admin';
                }
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role']    = $user['role'];

                header('Location: index.php?page=homepage');
                exit();
            } else {
                $swal_inline = ['icon' => 'error', 'title' => 'รหัสผ่านไม่ถูกต้อง', 'text' => 'กรุณาตรวจสอบรหัสผ่านของคุณอีกครั้ง'];
            }
        } else {
            $swal_inline = ['icon' => 'error', 'title' => 'ไม่พบบัญชีนี้', 'text' => 'อีเมลนี้ไม่มีอยู่ในระบบ'];
        }
    } else {
        $swal_inline = ['icon' => 'warning', 'title' => 'กรุณากรอกข้อมูล', 'text' => 'กรุณากรอกอีเมลและรหัสผ่าน'];
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DPI Thread</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="styles/loginstyle.css?v=<?= time() ?>">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <main class="card auth-card p-4 p-sm-5">
                <h1 class="welcome text-center mb-1">ยินดีต้อนรับสู่กระทู้ DPI</h1>
                <p class="login-subtitle text-center mb-4">กรุณาเข้าสู่ระบบ</p>

                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        <label for="email">อีเมล</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                        <label for="password">รหัสผ่าน</label>
                    </div>

                    <div class="auth-link-container text-center my-4">
                        <span>ยังไม่มีบัญชี? </span><a href="index.php?page=register">สมัครสมาชิก</a>
                    </div>
                    
                    <button class="btn-auth-primary" type="submit">เข้าสู่ระบบ</button>
                </form>
            </main>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ป้องกันการใช้ปุ่มย้อนกลับ
    history.pushState(null, "", "index.php?page=login");
    window.onpopstate = function () {
        history.pushState(null, "", "index.php?page=login");
    };

    <?php if ($swal_data): ?>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon:  <?= json_encode($swal_data['icon']) ?>,
            title: <?= json_encode($swal_data['title']) ?>,
            text:  <?= json_encode($swal_data['text']) ?>,
            confirmButtonColor: '#123524',
        });
    });
    <?php elseif ($swal_inline): ?>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon:  <?= json_encode($swal_inline['icon']) ?>,
            title: <?= json_encode($swal_inline['title']) ?>,
            text:  <?= json_encode($swal_inline['text']) ?>,
            confirmButtonColor: '#123524',
        });
    });
    <?php endif; ?>
</script>
</body>
</html>