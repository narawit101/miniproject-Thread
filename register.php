<?php
require 'server.php';
session_start(); // เริ่มต้นการใช้เซสชัน

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // ล้างข้อความข้อผิดพลาดหลังจากแสดงแล้ว
}
$errors = []; // ตัวแปรเก็บข้อความข้อผิดพลาด

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์มและตัดช่องว่าง
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_email = "admin@gmail.com";

    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง!';
    }

    // ตรวจสอบความยาวรหัสผ่าน (ตัวอย่าง: อย่างน้อย 6 ตัว)
    if (strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร!';
    }

    // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        $errors[] = 'รหัสผ่านไม่ตรงกัน!';
    }

    if (empty($errors)) {
        // ตรวจสอบว่าอีเมลซ้ำหรือไม่
        $sql_check_email = "SELECT * FROM users WHERE email = ?";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->execute([$email]);

        if ($stmt_check_email->rowCount() > 0) {
            $errors[] = 'อีเมลนี้ถูกใช้ไปแล้ว!,';
        } else {
            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = ($email === $admin_email) ? 'admin' : 'user';
            // บันทึกข้อมูลผู้ใช้ใหม่ลงในฐานข้อมูล

            $sql = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt->execute([$first_name, $last_name, $email, $hashed_password, $role])) {
                // หลังจากสมัครสำเร็จ เปลี่ยนเส้นทางไปยังหน้า login.php
                header('Location: login.php');
                exit(); // หยุดการประมวลผล PHP
            } else {
                $errors[] = 'เกิดข้อผิดพลาดในการสมัครสมาชิก!,';
            }
        }
    }

    // เก็บข้อความข้อผิดพลาดในเซสชัน (ถ้ามี)
    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
        // เก็บข้อมูลที่กรอกไว้ในเซสชันเพื่อแสดงในฟอร์ม
        $_SESSION['form_data'] = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        ];
        header('Location: register.php');
        exit();
    }
}

// ดึงข้อความข้อผิดพลาดจากเซสชัน (ถ้ามี)
if (isset($_SESSION['error_messages'])) {
    $errors = $_SESSION['error_messages'];
    unset($_SESSION['error_messages']); // ล้างข้อความข้อผิดพลาดหลังจากแสดงแล้ว
}

// ดึงข้อมูลที่กรอกไว้จากเซสชัน (ถ้ามี)
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']); // ล้างข้อมูลฟอร์มหลังจากดึงแล้ว
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/registerstyle.css"> <!-- ลิงก์ไปยังไฟล์ CSS ของคุณ -->
</head>

<body>
    <main class="form-signin w-100 m-auto">
        <form method="POST" action="register.php">
            <!-- แสดงข้อความข้อผิดพลาด (ถ้ามี) -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger ">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <?= htmlspecialchars($error) ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <h1 class="register">สมัครสมาชิก</h1>
            <div class="form-floating mb-3">
                <input type="text" name="first_name" class="form-control" placeholder="ชื่อ" required
                    value="<?= isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : '' ?>">
                <label for="first_name">ชื่อ</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" name="last_name" class="form-control" placeholder="นามสกุล" required
                    value="<?= isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : '' ?>">
                <label for="last_name">นามสกุล</label>
            </div>
            <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required
                    value="<?= isset($form_data['email']) ? htmlspecialchars($form_data['email']) : '' ?>">
                <label for="email">อีเมล</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                <label for="password">รหัสผ่าน</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน"
                    required>
                <label for="confirm_password">ยืนยันรหัสผ่าน</label>
            </div>
            <div class="form-check text-start mb-3">
                <p class="mt-5 mb-3 text-body-secondary">หรือคุณมีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
            </div>
            <button class="btn btn-primary w-100 py-2" type="submit">สมัครสมาชิก</button>
        </form>
    </main>
</body>

</html>