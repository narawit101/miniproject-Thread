<?php
include_once('dataheader.php');
$file_path = 'posts.json';

// Create the file if it doesn't exist
if (!file_exists($file_path)) {
    file_put_contents($file_path, json_encode([]));
}

// Load existing posts
$json_data = file_get_contents($file_path);
$posts = json_decode($json_data, true);

// Handle post editing
if (isset($_GET['edit'])) {
    $edit_index = $_GET['edit'];
    $post_to_edit = $posts[$edit_index];
}

// Handle form submission (create/edit post)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $time = date('Y-m-d H:i:s');

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $unique_name = $target_dir . uniqid() . '-' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $unique_name);
        $image = $unique_name;
    } else {
        $image = isset($post_to_edit['image']) ? $post_to_edit['image'] : '';
    }

    // Create new post array
    $new_post = [
        'title' => $title,
        'description' => $description,
        'image' => $image,
        'time' => $time
    ];

    // Update existing post or add a new one
    if (isset($_POST['edit_index']) && $_POST['edit_index'] !== '') {
        $edit_index = $_POST['edit_index'];
        $posts[$edit_index] = $new_post;
    } else {
        $posts[] = $new_post;
    }

    // Save posts to file and redirect
    file_put_contents($file_path, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: homepage.php");
    exit();
}
?>

<?php include_once('top_layouts.php') ?>
<div class="item layoutofcon3">
<h1>เขียนประกาศ</h1>
<div class="insidecon3">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post_to_edit) ? 'แก้ไขประกาศ' : 'โพสต์ประกาศใหม่'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function previewImage() {
            const file = document.getElementById('image').files[0];
            const preview = document.getElementById('imagePreview');
            const reader = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
                document.getElementById('cancelButton').style.display = 'inline-block';
            };

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
                document.getElementById('cancelButton').style.display = 'none';
            }
        }

        function cancelImage() {
            const fileInput = document.getElementById('image');
            const preview = document.getElementById('imagePreview');
            fileInput.value = "";
            preview.src = "";
            document.getElementById('cancelButton').style.display = 'none';
        }
    </script>
</head>
<body>
    <link rel="stylesheet" href="styles/feedbyadmin.css">
    <h2><?php echo isset($post_to_edit) ? 'แก้ไขประกาศ' : 'โพสต์ประกาศใหม่'; ?></h2>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="edit_index" value="<?php echo isset($edit_index) ? htmlspecialchars($edit_index) : ''; ?>">

        <label for="title">หัวข้อ:</label>
        <input type="text" id="title" name="title" value="<?php echo isset($post_to_edit) ? htmlspecialchars($post_to_edit['title']) : ''; ?>" required>

        <label for="description">รายละเอียด:</label>
        <textarea id="description" name="description" required><?php echo isset($post_to_edit) ? htmlspecialchars($post_to_edit['description']) : ''; ?></textarea>

        <label for="image">อัปโหลดรูปภาพ:</label>
        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage()">
        <img id="imagePreview" src="<?php echo isset($post_to_edit) && $post_to_edit['image'] ? htmlspecialchars($post_to_edit['image']) : ''; ?>" alt="Image Preview">

        <div class="button-group">
            <button type="submit"><?php echo isset($post_to_edit) ? 'บันทึกการแก้ไข' : 'โพสต์'; ?></button>
            <button type="button" id="cancelButton" onclick="cancelImage()">ยกเลิก</button>
            <button type="button" onclick="history.back()">ย้อนกลับ</button>
        </div>
    </form>
</body>
</html>
</div>
</div>
<?php include_once('bottom_layouts.php'); ?>