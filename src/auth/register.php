<?php
require_once 'config/server.php';
require_once 'config/swal_helper.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$swal_data = null;
if (isset($_SESSION['swal'])) {
    $swal_data = $_SESSION['swal'];
    unset($_SESSION['swal']);
}

$errors    = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name      = trim($_POST['first_name']);
    $last_name       = trim($_POST['last_name']);
    $email           = trim($_POST['email']);
    $password        = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    }
    if (strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'รหัสผ่านไม่ตรงกัน';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $errors[] = 'อีเมลนี้ถูกใช้งานแล้ว';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role   = ($email === 'admin@gmail.com') ? 'admin' : 'user';
            $stmt   = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");

            if ($stmt->execute([$first_name, $last_name, $email, $hashed, $role])) {
                set_swal('success', 'สมัครสมาชิกสำเร็จ! 🎉', 'ยินดีต้อนรับ ' . $first_name . ' เข้าสู่กระทู้ DPI');
                header('Location: index.php?page=login');
                exit();
            } else {
                $errors[] = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
            }
        }
    }

    // เก็บข้อมูลฟอร์มไว้แสดงซ้ำ
    $form_data = ['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก — DPI Thread</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="styles/loginstyle.css?v=<?= time() ?>">
    <link rel="stylesheet" href="styles/registerstyle.css?v=<?= time() ?>">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-7 col-xl-5">
            <main class="card auth-card p-4 p-sm-5">
                <h1 class="register text-center mb-4">สมัครสมาชิก</h1>

                <form method="POST" action="index.php?page=register">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="first_name" class="form-control" id="first_name" placeholder="ชื่อ" required
                                    value="<?= isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : '' ?>">
                                <label for="first_name">ชื่อ</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="last_name" class="form-control" id="last_name" placeholder="นามสกุล" required
                                    value="<?= isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : '' ?>">
                                <label for="last_name">นามสกุล</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required
                            value="<?= isset($form_data['email']) ? htmlspecialchars($form_data['email']) : '' ?>">
                        <label for="email">อีเมล</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" id="password" placeholder="รหัสผ่าน" required>
                        <label for="password">รหัสผ่าน</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
                        <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                    </div>

                    <div class="auth-link-container text-center my-4">
                        <span>หรือคุณมีบัญชีอยู่แล้ว? </span><a href="index.php?page=login">เข้าสู่ระบบ</a>
                    </div>

                    <button class="btn-auth-primary" type="submit">สมัครสมาชิก</button>
                </form>
            </main>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($swal_data): ?>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon:  <?= json_encode($swal_data['icon']) ?>,
                title: <?= json_encode($swal_data['title']) ?>,
                text:  <?= json_encode($swal_data['text']) ?>,
                confirmButtonColor: '#123524',
            });
        });
        <?php elseif (!empty($errors)): ?>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'สมัครสมาชิกไม่สำเร็จ',
                html: <?= json_encode('<ul style="text-align:left;margin:0;padding-left:1.2rem">' . implode('', array_map(fn($e) => '<li>' . htmlspecialchars($e) . '</li>', $errors)) . '</ul>') ?>,
                confirmButtonColor: '#123524',
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>