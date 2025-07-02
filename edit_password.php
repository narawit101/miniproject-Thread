<?php
include_once('dataheader.php');
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}


// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = []; // ตัวแปรเก็บข้อความข้อผิดพลาด

    // ตรวจสอบความยาวของรหัสผ่าน
    if (strlen($new_password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร!';
    }

    // ตรวจสอบความตรงกันของรหัสผ่าน
    if ($new_password !== $confirm_password) {
        $errors[] = 'รหัสผ่านไม่ตรงกัน!';
    }

    // แสดงข้อความแสดงข้อผิดพลาดถ้ามี
    if (!empty($errors)) {
        echo '<div class="error-box">' . implode('<br>', $errors) . '</div>';
    } else {
        // ถ้ารหัสผ่านถูกต้อง ให้แฮชและอัปเดตในฐานข้อมูล
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // อัปเดตรหัสผ่านในฐานข้อมูล
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed_password, $user_id]);

        // กลับไปยังหน้าโปรไฟล์
        header('Location: profile.php');
        exit();
    }
}



include_once('top_layouts.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
</head>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            /* display: flex; */
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button[type="submit"], button[type="button"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button[type="submit"]:hover, button[type="button"]:hover {
            background-color: #45a049;
        }
        .error-box {
            text-align: center;
    border: 1px solid red;
    background-color: #ffe6e6; /* Light red background */
    color: red; /* Text color */
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    /* font-weight: bold; */
}
    </style>
<body>
<div class="item layoutofcon3">

    <h1>เปลี่ยนรหัสผ่าน</h1>
    <div class="insidecon3">
    <form action="edit_password.php" method="POST">
        <div class="form-group">
            <label for="new_password">รหัสผ่านใหม่:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">ยืนยันรหัสผ่าน:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit">บันทึกการเปลี่ยนแปลง</button>
        <button type="button" onclick="window.history.back();">ย้อนกลับ</button>
    </form>
</body>
</div></div>
<?php include_once('bottom_layouts.php');
?>