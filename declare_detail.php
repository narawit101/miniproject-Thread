<?php
include_once('dataheader.php');

$file_path = 'posts.json';
if (!file_exists($file_path)) {
    die('ไม่พบไฟล์ข้อมูล');
}

$json_data = file_get_contents($file_path);
$posts = json_decode($json_data, true);

if (isset($_GET['post'])) {
    $post_index = $_GET['post'];
    if (isset($posts[$post_index])) {
        $post = $posts[$post_index];
    } else {
        die('ไม่พบประกาศ');
    }
} else {
    die('ไม่พบประกาศ');
}
?>
<?php include_once('top_layouts.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <style>
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #4b0082;
            margin-bottom: 20px;
        }

        .post-description {
            /* white-space: pre-wrap; */
            font-size: 16px;
            color: #333;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .container img {
            width: 100%;
            height: auto;
            display: block;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        button {
            background-color: #4b0082;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #360061;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <?php if (!empty($post['image'])): ?>
            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="News Image">
        <?php endif; ?>
        <div class="post-description">
            <?php echo nl2br(htmlspecialchars($post['description'])); ?>
        </div>
        <p><small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($post['time'])); ?></small></p>
        <button onclick="window.location.href='homepage.php'">ย้อนกลับ</button>
    </div>
</body>
</html>
<?php include_once('bottom_layouts.php'); ?>
