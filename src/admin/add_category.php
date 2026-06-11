<?php
require_once 'config/server.php';
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';

// ตรวจสอบสิทธิ์ของผู้ใช้
if ($_SESSION['role'] !== 'admin') { // ถ้าผู้ใช้ไม่ใช่ผู้ดูแลระบบ
    header('Location: index.php?page=logout'); // เปลี่ยนเส้นทางไปที่หน้า logout.php
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
        if (!isset($error_message)) {
            $stmt = $conn->prepare("INSERT INTO categories (category_name, categorie_icon) VALUES (?, ?)");
            $stmt->execute([$category_name, $icon_path]);
            set_swal('success', 'เพิ่มหมวดหมู่สำเร็จ!', 'หมวดหมู่ "' . htmlspecialchars($category_name) . '" ถูกเพิ่มเข้าระบบแล้ว');
            header('Location: index.php?page=add_category');
            exit();
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

    if ($count > 0) {
        set_swal('warning', 'ไม่สามารถลบได้!', 'หมวดหมู่นี้มีโพสต์ที่เชื่อมโยงอยู่ กรุณาย้ายโพสต์ก่อน');
        header('Location: index.php?page=add_category');
        exit();
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        set_swal('success', 'ลบหมวดหมู่สำเร็จ!', 'หมวดหมู่ถูกลบออกจากระบบแล้ว');
        header('Location: index.php?page=add_category');
        exit();
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
            $stmt->execute([$icon_path, $category_id]);
            set_swal('success', 'อัปเดตไอคอนสำเร็จ!', 'ไอคอนหมวดหมู่ถูกอัปเดตแล้ว');
            header('Location: index.php?page=add_category');
            exit();
        } else {
            $error_message = "กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น!"; // แสดงข้อความข้อผิดพลาด
        }
    } elseif ($icon['error'] == UPLOAD_ERR_NO_FILE) { // ถ้าไม่มีการเลือกไฟล์
        $error_message = "กรุณาเลือกไฟล์ไอคอน!"; // แสดงข้อความข้อผิดพลาด
    }
}

include_once 'layouts/top_layouts.php'; // เรียกใช้ไฟล์ top_layouts.php เพื่อรวมส่วนบนของหน้าเว็บ
?>

<div class="bodyofcontent">
    <link rel="stylesheet" href="homecss/add_category.css"> <!-- เรียกใช้ไฟล์ CSS -->
    <div class="item layoutofcon1">
        <?php include_once 'layouts/category_slide.php'; ?> <!-- เรียกใช้ไฟล์ category_slide.php -->
    </div>

    <div class="item layoutofcon3">
        <h1>เพิ่มหมวดหมู่ใหม่</h1>
        <div class="insidecon3">
            <div class="insidecreatepost">
                <div class="boxofcate">
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="index.php?page=add_category" enctype="multipart/form-data" class="mt-3">
                        <div class="mb-3">
                            <label for="category_name" class="form-label font-weight-bold">ชื่อหมวดหมู่:</label>
                            <input type="text" id="category_name" name="category_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="icon" class="form-label font-weight-bold">เลือกไฟล์ไอคอน:</label>
                            <input type="file" id="icon" name="icon" class="form-control" accept="image/*" required onchange="previewNewIcon(event)">
                            <div class="new-icon-preview mt-3">
                                <p class="form-label font-weight-bold">ตัวอย่างไอคอนใหม่:</p>
                                <img id="new-icon-preview" alt="New Icon Preview" class="img-thumbnail" style="max-height: 100px; display: none;">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="add_category" class="btn btn-primary">เพิ่มหมวดหมู่</button>
                            <button type="button" class="btn btn-outline-primary" onclick="history.back()">ย้อนกลับ</button>
                        </div>
                    </form>
                </div>
            </div>

            <h2 class="mt-5 mb-4 border-bottom pb-2 font-weight-bold">หมวดหมู่ที่มีอยู่:</h2>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php
                $stmt = $conn->query("SELECT * FROM categories");
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body d-flex align-items-center gap-3">
                                <?php if (!empty($category['categorie_icon'])): ?>
                                    <img src="<?= htmlspecialchars($category['categorie_icon']) ?>" alt="Icon"
                                        class="current-category-icon img-thumbnail rounded" id="current-icon-<?= $category['category_id'] ?>" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-2 text-dark font-weight-bold"><?= htmlspecialchars($category['category_name']) ?></h5>
                                    
                                    <form method="POST" action="index.php?page=add_category" enctype="multipart/form-data" class="d-flex align-items-center gap-2 mt-2">
                                        <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                        <input type="file" name="icon" accept="image/*" class="form-control form-control-sm icon-input"
                                            data-preview="current-icon-<?= $category['category_id'] ?>"
                                            onchange="previewIcon(event, <?= $category['category_id'] ?>)" style="max-width: 180px;">
                                        <button type="submit" name="update_category" class="btn btn-sm btn-primary">อัปเดต</button>
                                        <button type="button" onclick="cancelPreview(<?= $category['category_id'] ?>)" class="btn btn-sm btn-secondary">ยกเลิก</button>
                                    </form>
                                    <div class="mt-3 text-end">
                                        <a href="index.php?page=add_category&delete_category_id=<?= $category['category_id'] ?>"
                                            data-confirm="ยืนยันการลบหมวดหมู่ '<?= htmlspecialchars($category['category_name']) ?>'?"
                                            class="btn btn-sm btn-outline-danger">ลบหมวดหมู่</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="item layoutofcon4">
        <?php include_once 'layouts/con4.php'; ?> <!-- เรียกใช้ไฟล์ con4.php -->
    </div>
</div>

<?php include_once 'layouts/bottom_layouts.php'; ?> <!-- เรียกใช้ไฟล์ bottom_layouts.php -->

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
        const reader = new FileReader();
        const preview = document.getElementById('new-icon-preview');
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
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