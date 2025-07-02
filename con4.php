<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Card</title>
    <style>

        .insidecon4 {
            margin: 20px;
    padding: 10px;
    border-radius: 10px;
    background: #fff;
    border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .insidecon4 h2 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
        }

        /* Flexbox to arrange the cards */
        .news-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }

        .news-card {
            border-radius: 12px;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 300px; /* กำหนดขนาดความกว้างของการ์ด */
        }

        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .news-card img {
            width: 100%;
            height: auto;
            display: block;
        }

        .news-content {
            padding: 20px;
            background-color: #fff;
        }

        .news-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.4;
        }

        .news-title a {
            text-decoration: none;
            color: #1a73e8; /* สีลิงก์ */
            transition: color 0.3s ease;
        }

        .news-title a:hover {
            color: #0056b3;
        }

        .news-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .news-time {
            font-size: 12px;
            color: #999;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .news-card {
                width: 100%; /* กำหนดขนาดการ์ดให้เต็มหน้าจอในอุปกรณ์เล็ก */
            }
        }

    </style>
    <script>
        function deletePost(index) {
            if (confirm('คุณต้องการลบประกาศนี้หรือไม่?')) {
                window.location.href = 'con4.php?delete=' + index;
            }
        }
    </script>
</head>
<body>
    <div class="insidecon4">
        <h2>ประกาศ</h2>
        <div class="news-wrapper">
            <?php
            $file_path = 'posts.json';
            if (!file_exists($file_path)) {
                file_put_contents($file_path, json_encode([]));
            }

            $json_data = file_get_contents($file_path);
            $posts = json_decode($json_data, true);

            if (isset($_GET['delete'])) {
                $delete_index = $_GET['delete'];
                if (is_numeric($delete_index) && isset($posts[$delete_index])) {
                    array_splice($posts, $delete_index, 1);
                    file_put_contents($file_path, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    // ใช้ JavaScript เพื่อรีเฟรชหน้า
                    echo '<script>window.location.href = "homepage.php";</script>';
                    exit();
                } else {
                    echo '<p>ไม่พบประกาศที่ต้องการลบ</p>';
                }
            }

            if (is_array($posts)) {
                foreach ($posts as $index => $post) {
                    echo '<div class="news-card">';
                    echo '<img src="' . htmlspecialchars($post['image']) . '" alt="News Image">';
                    echo '<div class="news-content">';
                    echo '<div class="news-title">';
                    echo '<a href="declare_detail.php?post=' . $index . '" target="_blank">' . htmlspecialchars($post['title']) . '</a>';
                    echo '</div>';
                    echo '<button onclick="window.location.href=\'feedbyadmin.php?edit=' . $index . '\'">แก้ไข</button>';
                    echo '<button onclick="deletePost(' . $index . ')">ลบ</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>ไม่มีข้อมูลโพสต์</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>