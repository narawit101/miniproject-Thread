<?php
include_once('dataheader.php');
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password =$_POST['password'];
    $email = $_POST['email'];

    // อัปเดตข้อมูลผู้ใช้
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
    $stmt->execute([$first_name, $last_name, $email, $user_id]);

    // ตรวจสอบการลบรูปภาพ
    if (isset($_POST['delete_profile_pic'])) {
        $default_image ='logo.png';
        $stmt = $conn->prepare("UPDATE users SET user_img = ? WHERE user_id = ?");
        $stmt->execute([$default_image, $user_id]);
    }

    // ตรวจสอบการอัปโหลดรูปภาพ
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_dir = 'uploads/';

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $new_filename)) {
                // อัปเดตชื่อไฟล์รูปภาพในฐานข้อมูล
                $stmt = $conn->prepare("UPDATE users SET user_img = ? WHERE user_id = ?");
                $stmt->execute([$new_filename, $user_id]);
            }
        }
    }

    // กลับไปยังหน้าโปรไฟล์
    header('Location: profile.php');
    exit();
}
?>
<?php include_once('top_layouts.php'); ?>
<style>
    /* Container styling */
    .edit-profile-container {
        max-width: 600px;
        margin: 0 auto;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: left;
        font-family: Arial, sans-serif;
    }

    /* Heading styling */
    .edit-profile-container h1 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
        text-align: center;
    }

    /* Form styling */
    form {
        display: flex;
        flex-direction: column;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        font-size: 16px;
        margin-bottom: 5px;
        display: block;
        color: #555;
    }

    input[type="text"],
    input[type="email"],
    input[type="file"] {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    input[type="file"] {
        padding: 8px;
    }

    /* Profile picture preview */
    #preview {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 10px;
        display: block;
        border: 2px solid #ccc;
    }

    /* Buttons styling */
    button {
        padding: 10px 15px;
        font-size: 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }

    .submit-button {
        background-color: #007bff;
        color: white;
    }

    .delete-button {
        background-color: #dc3545;
        color: white;
    }

    .back-button {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background-color: #6c757d;
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 4px;
        width: 100%;
    }

    button:hover,
    .back-button:hover {
        opacity: 0.9;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .edit-profile-container {
            padding: 15px;
        }

        input[type="text"],
        input[type="email"] {
            font-size: 13px;
        }

        button {
            font-size: 13px;
            width: 100%;
            margin-top: 10px;
        }

        #preview {
            width: 120px;
            height: 120px;
        }
    }
</style>
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('preview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
</head>

<body>
    <div class="edit-profile-container">
        <h1>แก้ไขโปรไฟล์</h1>

        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="first_name">ชื่อ:</label>
                <input type="text" id="first_name" name="first_name"
                    value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">นามสกุล:</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>"
                    required>
            </div>

            <div class="form-group">

                <button type="button"><a href="edit_password.php">รหัสผ่าน</a></button>
            </div>

            <div class="form-group">
                <label for="profile_pic">รูปโปรไฟล์:</label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(event)">
            </div>

            <div class="form-group">
                <img id="preview" src="#" alt="ตัวอย่างรูปโปรไฟล์" style="display: none;">
                <p>ตัวอย่างรูปโปรไฟล์</p>
            </div>

            <div class="form-group">
                <button type="submit" name="delete_profile_pic" class="delete-button">ลบรูปโปรไฟล์</button>
            </div>

            <button type="submit" class="submit-button">บันทึกการเปลี่ยนแปลง</button>
        </form>

        <a href="profile.php" class="back-button">ยกเลิก</a>
    </div>
    <script>
        document.getElementById('profile_pic').addEventListener('change', function (event) {
            const preview = document.getElementById('preview');
            preview.style.display = 'block';
        });
    </script>
</body>

</html>
<?php include_once('bottom_layouts.php'); ?>