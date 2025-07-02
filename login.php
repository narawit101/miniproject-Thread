<?php
require 'server.php';
session_start();

$error_message = ''; // ตัวแปรเก็บข้อความข้อผิดพลาด

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $email_admin = "admin@gmail.com";

    if (!empty($email) && !empty($password)) {
        // ตรวจสอบอีเมลในฐานข้อมูล
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user && password_verify($password, $user['password'])) {
                if ($email == $email_admin) {
                    $user['role'] = 'admin';
                }
                // ตั้งค่า session เมื่อผู้ใช้ล็อกอินสำเร็จ
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = $user['role'];

                // เปลี่ยนเส้นทางไปยังหน้า homepage.php
                header('Location: homepage.php');
                exit();
            } else {
                // รหัสผ่านผิด
                $error_message = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            // อีเมลไม่มีอยู่ในระบบ
            $error_message = "อีเมลนี้ไม่มีอยู่ในระบบ";
        }
    } else {
        $error_message = "กรุณากรอกอีเมลและรหัสผ่าน";
    }
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="th">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/loginstyle.css"> <!-- ลิงก์ไปยังไฟล์ CSS ของคุณ -->
</head>

<body></body>
<!-- แสดงข้อความข้อผิดพลาด (ถ้ามี) -->
<main class="form-signin ">
    <h1 class="welcome text-center">ยินดีต้อนรับสู่กระทู้ DPI</h1>
    <h1 class="login text-center">กรุณาเข้าสู่ระบบ</h1>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST" class="form-floating mb-3">
        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" placeholder="" required>
            <label for="floatingInput">อีเมล</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" placeholder="" required>
            <label for="floatingPassword">รหัสผ่าน</label>
        </div>

        <div class="form-check text-start mb-3">
            <p class="mt-5 mb-3 text-body-secondary">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
        </div>
        <button class="btn btn-primary w-100 py-2" type="submit">เข้าสู่ระบบ</button>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // เพิ่มโค้ดนี้เพื่อป้องกันการใช้ปุ่มย้อนกลับ
    history.pushState(null, "", "login.php");
    window.onpopstate = function () {
        history.pushState(null, "", "login.php");
    };
</script>
</body>

</html>