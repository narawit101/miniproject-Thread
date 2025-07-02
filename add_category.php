<?php
require 'server.php'; // เรียกใช้ไฟล์ server.php เพื่อเชื่อมต่อกับฐานข้อมูล
include_once('dataheader.php'); // เรียกใช้ไฟล์ dataheader.php เพื่อรวมส่วนหัวของหน้าเว็บ

// ตรวจสอบสิทธิ์ของผู้ใช้
if ($_SESSION['role'] !== 'admin') { // ถ้าผู้ใช้ไม่ใช่ผู้ดูแลระบบ
    header('Location: logout.php'); // เปลี่ยนเส้นทางไปที่หน้า logout.php
    exit(); // หยุดการทำงานของสคริปต์
}

// จัดการการเพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) { // ถ้ามีการส่งข้อมูลแบบ POST และมีการกดปุ่มเพิ่มหมวดหมู่
    $category_name = $_POST['category_name']; // รับค่าชื่อหมวดหมู่จากฟอร์ม
    $icon = $_FILES['icon']; // รับค่าไฟล์ไอคอนจากฟอร์ม

    // ตรวจสอบว่ามีหมวดหมู่นี้อยู่แล้วหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ?"); // เตรียมคำสั่ง SQL
    $stmt->execute([$category_name]); // รันคำสั่ง SQL พร้อมกับค่าชื่อหมวดหมู่
    $count = $stmt->fetchColumn(); // รับค่าจำนวนแถวที่ตรงกับเงื่อนไข

    if ($count > 0) { // ถ้ามีหมวดหมู่นี้อยู่แล้ว
        $error_message = "หมวดหมู่ \"$category_name\" มีอยู่แล้วในระบบ กรุณาเลือกชื่ออื่น!"; // แสดงข้อความข้อผิดพลาด
    } else {
        // จัดการการอัปโหลดไอคอน
        $icon_path = null; // กำหนดค่าเริ่มต้นของเส้นทางไอคอน
        if ($icon['error'] == UPLOAD_ERR_OK) { // ถ้าไม่มีข้อผิดพลาดในการอัปโหลดไฟล์
            $file_type = mime_content_type($icon['tmp_name']); // ตรวจสอบประเภทของไฟล์
            if (strpos($file_type, 'image/') === 0) { // ถ้าไฟล์เป็นรูปภาพ
                $icon_path = 'uploads/' . basename($icon['name']); // กำหนดเส้นทางไฟล์ไอคอน
                move_uploaded_file($icon['tmp_name'], $icon_path); // ย้ายไฟล์ไปยังเส้นทางที่กำหนด
            } else {
                $error_message = "กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น!"; // แสดงข้อความข้อผิดพลาด
            }
        } elseif ($icon['error'] == UPLOAD_ERR_NO_FILE) { // ถ้าไม่มีการเลือกไฟล์
            $error_message = "กรุณาเลือกไฟล์ไอคอน!"; // แสดงข้อความข้อผิดพลาด
        }

        // แทรกหมวดหมู่ใหม่
        if (!isset($error_message)) { // ถ้าไม่มีข้อผิดพลาด
            $stmt = $conn->prepare("INSERT INTO categories (category_name, categorie_icon) VALUES (?, ?)"); // เตรียมคำสั่ง SQL
            $stmt->execute([$category_name, $icon_path]); // รันคำสั่ง SQL พร้อมกับค่าชื่อหมวดหมู่และเส้นทางไอคอน
            header('Location: add_category.php'); // เปลี่ยนเส้นทางไปที่หน้า add_category.php
            exit(); // หยุดการทำงานของสคริปต์
        }
    }
}

// จัดการการลบหมวดหมู่
if (isset($_GET['delete_category_id'])) { // ถ้ามีการส่งค่า ID ของหมวดหมู่ที่ต้องการลบ
    $category_id = $_GET['delete_category_id']; // รับค่า ID ของหมวดหมู่

    // ตรวจสอบว่ามีโพสต์ที่เชื่อมโยงกับหมวดหมู่นี้หรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?"); // เตรียมคำสั่ง SQL
    $stmt->execute([$category_id]); // รันคำสั่ง SQL พร้อมกับค่า ID ของหมวดหมู่
    $count = $stmt->fetchColumn(); // รับค่าจำนวนแถวที่ตรงกับเงื่อนไข

    if ($count > 0) { // ถ้ามีโพสต์ที่เชื่อมโยงกับหมวดหมู่นี้
        $error_message = "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากมีโพสต์ที่เชื่อมโยงอยู่"; // แสดงข้อความข้อผิดพลาด
    } else {
        // ดำเนินการลบหมวดหมู่
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?"); // เตรียมคำสั่ง SQL
        $stmt->execute([$category_id]); // รันคำสั่ง SQL พร้อมกับค่า ID ของหมวดหมู่
        header('Location: add_category.php'); // เปลี่ยนเส้นทางไปที่หน้า add_category.php
        exit(); // หยุดการทำงานของสคริปต์
    }
}

// จัดการการอัปเดตไอคอน
if (isset($_POST['update_category'])) { // ถ้ามีการส่งข้อมูลแบบ POST และมีการกดปุ่มอัปเดตไอคอน
    $category_id = $_POST['category_id']; // รับค่า ID ของหมวดหมู่จากฟอร์ม
    $icon = $_FILES['icon']; // รับค่าไฟล์ไอคอนจากฟอร์ม

    // จัดการการอัปโหลดไอคอน
    if ($icon['error'] == UPLOAD_ERR_OK) { // ถ้าไม่มีข้อผิดพลาดในการอัปโหลดไฟล์
        $file_type = mime_content_type($icon['tmp_name']); // ตรวจสอบประเภทของไฟล์
        if (strpos($file_type, 'image/') === 0) { // ถ้าไฟล์เป็นรูปภาพ
            $icon_path = 'uploads/' . basename($icon['name']); // กำหนดเส้นทางไฟล์ไอคอน
            move_uploaded_file($icon['tmp_name'], $icon_path); // ย้ายไฟล์ไปยังเส้นทางที่กำหนด

            // อัปเดตไอคอนในฐานข้อมูล
            $stmt = $conn->prepare("UPDATE categories SET categorie_icon = ? WHERE category_id = ?"); // เตรียมคำสั่ง SQL
            $stmt->execute([$icon_path, $category_id]); // รันคำสั่ง SQL พร้อมกับค่าเส้นทางไอคอนและ ID ของหมวดหมู่
            header('Location: add_category.php'); // เปลี่ยนเส้นทางไปที่หน้า add_category.php
            exit(); // หยุดการทำงานของสคริปต์
        } else {
            $error_message = "กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น!"; // แสดงข้อความข้อผิดพลาด
        }
    } elseif ($icon['error'] == UPLOAD_ERR_NO_FILE) { // ถ้าไม่มีการเลือกไฟล์
        $error_message = "กรุณาเลือกไฟล์ไอคอน!"; // แสดงข้อความข้อผิดพลาด
    }
}

include_once('top_layouts.php'); // เรียกใช้ไฟล์ top_layouts.php เพื่อรวมส่วนบนของหน้าเว็บ
?>

<div class="bodyofcontent">
    <link rel="stylesheet" href="homecss/add_category.css"> <!-- เรียกใช้ไฟล์ CSS -->
    <div class="item layoutofcon1">
        <?php include_once('category_slide.php'); ?> <!-- เรียกใช้ไฟล์ category_slide.php -->
    </div>

    <div class="item layoutofcon3">
        <h1>เพิ่มหมวดหมู่ใหม่</h1>
        <div class="insidecon3">
            <div class="insidecreatepost">
                <div class="boxofcate">

                    <?php if (isset($error_message)): ?> <!-- ถ้ามีข้อความข้อผิดพลาด -->
                        <div class="error-message" style="color: red;"><?= htmlspecialchars($error_message) ?></div>
                        <!-- แสดงข้อความข้อผิดพลาด -->
                    <?php endif; ?>
                    <form method="POST" action="add_category.php" enctype="multipart/form-data">
                        <!-- ฟอร์มสำหรับเพิ่มหมวดหมู่ -->
                        <label for="category_name">ชื่อหมวดหมู่:</label>
                        <input type="text" id="category_name" name="category_name" required>
                        <!-- ช่องกรอกชื่อหมวดหมู่ -->
                        <input type="file" id="icon" name="icon" accept="image/*" required
                            onchange="previewNewIcon(event)"> <!-- ช่องเลือกไฟล์ไอคอน -->
                        <div class="new-icon-preview">
                            <p>ตัวอย่างไอคอนใหม่:</p>
                            <img id="new-icon-preview" alt="New Icon Preview" class="new-category-icon">
                            <!-- แสดงตัวอย่างไอคอนใหม่ -->
                        </div>
                        <button type="submit" name="add_category" class="submit-button">เพิ่มหมวดหมู่</button>
                        <!-- ปุ่มเพิ่มหมวดหมู่ -->
                        <button type="button" onclick="history.back()">ย้อนกลับ</button> <!-- ปุ่มย้อนกลับ -->
                    </form>
                </div>
            </div>

            <h1 style="color: #333; margin-top: 30px;">หมวดหมู่ที่มีอยู่:</h1>
            <div class="categories-container">
                <?php
                $stmt = $conn->query("SELECT * FROM categories"); // ดึงข้อมูลหมวดหมู่ทั้งหมดจากฐานข้อมูล
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?> <!-- วนลูปแสดงข้อมูลหมวดหมู่ -->
                    <div class="category-card">
                        <?php if (!empty($category['categorie_icon'])): ?> <!-- ถ้ามีไอคอน -->
                            <img src="<?= htmlspecialchars($category['categorie_icon']) ?>" alt="Icon"
                                class="category-icon current-category-icon" id="current-icon-<?= $category['category_id'] ?>">
                            <!-- แสดงไอคอน -->
                        <?php endif; ?>
                        <div class="category-details">
                            <span class="category-name"><?= htmlspecialchars($category['category_name']) ?></span>
                            <!-- แสดงชื่อหมวดหมู่ -->
                            <div class="category-actions">
                                <form method="POST" action="add_category.php" enctype="multipart/form-data"
                                    style="display:inline;"> <!-- ฟอร์มสำหรับอัปเดตไอคอน -->
                                    <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                    <!-- ซ่อนค่า ID ของหมวดหมู่ -->
                                    <input type="file" name="icon" accept="image/*" class="icon-input"
                                        data-preview="current-icon-<?= $category['category_id'] ?>"
                                        onchange="previewIcon(event, <?= $category['category_id'] ?>)">
                                    <!-- ช่องเลือกไฟล์ไอคอน -->
                                    <button type="submit" name="update_category" class="update-button">อัปเดตไอคอน</button>
                                    <!-- ปุ่มอัปเดตไอคอน -->
                                    <button type="button" onclick="cancelPreview(<?= $category['category_id'] ?>)"
                                        class="cancel-button">ยกเลิก</button> <!-- ปุ่มยกเลิก -->
                                </form>
                                <a href="add_category.php?delete_category_id=<?= $category['category_id'] ?>"
                                    onclick="return confirm('ยืนยันการลบหมวดหมู่นี้?')" class="delete-button">ลบ</a>
                                <!-- ลิงก์สำหรับลบหมวดหมู่ -->
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="item layoutofcon4">
        <?php include_once('con4.php'); ?> <!-- เรียกใช้ไฟล์ con4.php -->
    </div>
</div>

<?php include_once('bottom_layouts.php'); ?> <!-- เรียกใช้ไฟล์ bottom_layouts.php -->

<script>
    function goBack() {
        window.history.back(); // ฟังก์ชันสำหรับย้อนกลับไปหน้าก่อนหน้า
    }

    // เพิ่ม event listener ให้กับช่องเลือกไฟล์ไอคอนทั้งหมด
    document.querySelectorAll('.icon-input').forEach(function (input) {
        input.addEventListener('change', function (event) {
            const reader = new FileReader(); // สร้างออบเจ็กต์ FileReader
            const previewId = input.getAttribute('data-preview'); // รับค่า ID ของตัวอย่างไอคอน
            reader.onload = function (e) {
                document.getElementById(previewId).src = e.target.result; // แสดงตัวอย่างไอคอน
            };
            reader.readAsDataURL(event.target.files[0]); // อ่านไฟล์ไอคอน
        });
    });

    function previewNewIcon(event) {
        const reader = new FileReader(); // สร้างออบเจ็กต์ FileReader
        reader.onload = function (e) {
            document.getElementById('new-icon-preview').src = e.target.result; // แสดงตัวอย่างไอคอนใหม่
        };
        reader.readAsDataURL(event.target.files[0]); // อ่านไฟล์ไอคอน
    }

    function previewIcon(event, categoryId) {
        const reader = new FileReader(); // สร้างออบเจ็กต์ FileReader
        reader.onload = function (e) {
            document.getElementById('current-icon-' + categoryId).src = e.target.result; // แสดงตัวอย่างไอคอน
        };
        reader.readAsDataURL(event.target.files[0]); // อ่านไฟล์ไอคอน
    }

    function cancelPreview(categoryId) {
        const currentIcon = document.getElementById('current-icon-' + categoryId).getAttribute('data-original-src'); // รับค่าเส้นทางไอคอนเดิม
        document.getElementById('current-icon-' + categoryId).src = currentIcon; // แสดงไอคอนเดิม
    }

    // เก็บค่าเส้นทางไอคอนเดิมสำหรับฟังก์ชันยกเลิก
    document.querySelectorAll('.current-category-icon').forEach(function (img) {
        img.setAttribute('data-original-src', img.src); // เก็บค่าเส้นทางไอคอนเดิม
    });
</script>