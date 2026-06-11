<?php
require_once 'config/server.php';
include_once 'layouts/dataheader.php';
require_once 'config/swal_helper.php';

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=logout');
    exit();
}

// ──────────────────────────────────────────────────────────
// ฟังก์ชันช่วย: บันทึกไฟล์ไอคอนลงโฟลเดอร์ uploads/categories/
// คืนค่า path สัมพัทธ์ หรือ null ถ้าผิดพลาด
// ──────────────────────────────────────────────────────────
function saveIcon(array $file, ?string $oldPath = null): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    if (!validateUploadedFileSize($file, 2)) return null;

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) return null;

    // สร้างชื่อไฟล์ unique
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'png';
    $filename = 'cat_' . uniqid() . '.' . strtolower($ext);
    $dest = 'uploads/categories/' . $filename;

    if (!uploadAndCompressImage($file, $dest, 150, 75)) return null;

    // ลบไฟล์เก่า (ถ้ามี และอยู่ใน uploads/categories/)
    if ($oldPath && str_starts_with($oldPath, 'uploads/categories/') && file_exists($oldPath)) {
        @unlink($oldPath);
    }

    return $dest;
}

// ──────────────────────────────────────────────────────────
// เพิ่มหมวดหมู่ใหม่
// ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name'] ?? '');

    if ($category_name === '') {
        set_swal('error', 'เกิดข้อผิดพลาด!', 'กรุณากรอกชื่อหมวดหมู่');
        header('Location: index.php?page=add_category');
        exit();
    } else {
        // เช็คชื่อซ้ำ
        $chk = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ?");
        $chk->execute([$category_name]);
        if ($chk->fetchColumn() > 0) {
            set_swal('error', 'เกิดข้อผิดพลาด!', "หมวดหมู่ \"$category_name\" มีอยู่แล้วในระบบ");
            header('Location: index.php?page=add_category');
            exit();
        } elseif (empty($_FILES['icon']['name'])) {
            set_swal('error', 'เกิดข้อผิดพลาด!', 'กรุณาเลือกไฟล์ไอคอน');
            header('Location: index.php?page=add_category');
            exit();
        } else {
            $icon_path = saveIcon($_FILES['icon']);
            if (!$icon_path) {
                set_swal('error', 'เกิดข้อผิดพลาด!', 'อัปโหลดไฟล์ไม่สำเร็จ หรือไฟล์ไม่ใช่รูปภาพ (jpg, png, gif, webp, svg)');
                header('Location: index.php?page=add_category');
                exit();
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (category_name, categorie_icon) VALUES (?, ?)");
                $stmt->execute([$category_name, $icon_path]);
                set_swal('success', 'เพิ่มหมวดหมู่สำเร็จ!', 'หมวดหมู่ "' . htmlspecialchars($category_name) . '" ถูกเพิ่มเข้าระบบแล้ว');
                header('Location: index.php?page=add_category');
                exit();
            }
        }
    }
}

// ──────────────────────────────────────────────────────────
// อัปเดตชื่อ + ไอคอนหมวดหมู่
// ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id   = (int)$_POST['category_id'];
    $category_name = trim($_POST['category_name'] ?? '');

    // ดึง path ไอคอนเก่า
    $old = $conn->prepare("SELECT categorie_icon FROM categories WHERE category_id = ?");
    $old->execute([$category_id]);
    $old_icon = $old->fetchColumn();

    // อัปโหลดไอคอนใหม่ (ถ้ามี)
    $new_icon = null;
    if (!empty($_FILES['icon']['name'])) {
        $new_icon = saveIcon($_FILES['icon'], $old_icon);
        if (!$new_icon) {
            set_swal('error', 'เกิดข้อผิดพลาด!', 'อัปโหลดไฟล์ไม่สำเร็จ หรือไฟล์ไม่ใช่รูปภาพ');
            header('Location: index.php?page=add_category');
            exit();
        }
    }

    if ($new_icon) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ?, categorie_icon = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $new_icon, $category_id]);
    } else {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $category_id]);
    }
    set_swal('success', 'อัปเดตหมวดหมู่สำเร็จ!', 'ข้อมูลหมวดหมู่ถูกอัปเดตแล้ว');
    header('Location: index.php?page=add_category');
    exit();
}

// ──────────────────────────────────────────────────────────
// ลบหมวดหมู่
// ──────────────────────────────────────────────────────────
if (isset($_GET['delete_category_id'])) {
    $category_id = (int)$_GET['delete_category_id'];

    $chk = $conn->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
    $chk->execute([$category_id]);
    if ($chk->fetchColumn() > 0) {
        set_swal('warning', 'ไม่สามารถลบได้!', 'หมวดหมู่นี้มีโพสต์เชื่อมโยงอยู่ กรุณาย้ายโพสต์ก่อน');
    } else {
        // ลบไฟล์รูปออกจาก disk ด้วย
        $row = $conn->prepare("SELECT categorie_icon FROM categories WHERE category_id = ?");
        $row->execute([$category_id]);
        $icon_path = $row->fetchColumn();
        if ($icon_path && str_starts_with($icon_path, 'uploads/categories/') && file_exists($icon_path)) {
            @unlink($icon_path);
        }

        $del = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $del->execute([$category_id]);
        set_swal('success', 'ลบหมวดหมู่สำเร็จ!', 'หมวดหมู่ถูกลบออกจากระบบแล้ว');
    }
    header('Location: index.php?page=add_category');
    exit();
}

// ดึงหมวดหมู่ทั้งหมด
$stmt = $conn->query("SELECT * FROM categories ORDER BY category_id");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once 'layouts/top_layouts.php';
?>

<div class="single-card-layout">



    <!-- ── ฟอร์มเพิ่มหมวดหมู่ ───────────────────────────── -->
    <div class="cat-add-card">
        <h1>เพิ่มหมวดหมู่ใหม่</h1>
        <form method="POST" action="index.php?page=add_category" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="add_name" class="form-label fw-semibold">ชื่อหมวดหมู่</label>
                <input type="text" id="add_name" name="category_name" class="form-control"
                    placeholder="เช่น เทคโนโลยี, กีฬา, ท่องเที่ยว" required>
            </div>

            <div class="mb-3">
                <label for="add_icon" class="form-label fw-semibold">รูปไอคอน
                    <!-- <small class="text-muted fw-normal">(jpg, png, gif, webp, svg — ไม่จำกัดขนาด)</small> -->
                </label>
                <input type="file" id="add_icon" name="icon" class="form-control" accept="image/*" required
                    onchange="previewAddIcon(event)">
                <div class="icon-preview-wrap mt-2">
                    <div class="icon-placeholder" id="add-placeholder"></div>
                    <img id="add-icon-preview" class="icon-preview-add" alt="ตัวอย่างไอคอน">
                    <span id="add-preview-label" class="text-muted small" style="display:none;">ตัวอย่างไอคอน</span>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" name="add_category" class="btn btn-primary">
                    เพิ่มหมวดหมู่
                </button>
            </div>
        </form>
    </div>

    <!-- ── รายการหมวดหมู่ ──────────────────────────────────── -->
    <div class="cat-list-title">หมวดหมู่ที่มีอยู่ (<?= count($categories) ?> รายการ)</div>

    <div class="row row-cols-1 row-cols-md-2 g-3">
        <?php foreach ($categories as $cat): ?>
            <div class="col">
                <div class="cat-card">
                    <!-- ── ไอคอนหมวดหมู่ (คลิกเพื่อเปลี่ยน) ── -->
                    <div class="cat-icon-wrap">
                        <?php if (!empty($cat['categorie_icon']) && file_exists($cat['categorie_icon'])): ?>
                            <img src="<?= htmlspecialchars($cat['categorie_icon']) ?>" class="cat-icon"
                                id="icon-display-<?= $cat['category_id'] ?>"
                                data-original="<?= htmlspecialchars($cat['categorie_icon']) ?>"
                                alt="<?= htmlspecialchars($cat['category_name']) ?>" title="คลิกที่ไอคอนเพื่อเปลี่ยนรูป"
                                onclick="openEditRow(<?= $cat['category_id'] ?>)">
                        <?php else: ?>
                            <div class="cat-icon-placeholder" onclick="openEditRow(<?= $cat['category_id'] ?>)"
                                title="คลิกเพื่อเพิ่มไอคอน"></div>
                        <?php endif; ?>
                    </div>

                    <!-- ── ข้อมูลและฟอร์มแก้ไข ──── -->
                    <div class="cat-info">
                        <div class="cat-name-display"><?= htmlspecialchars($cat['category_name']) ?></div>
                        <div class="text-muted small mb-2"
                            style="word-break:break-all; overflow-wrap:break-word; max-width:100%;">
                            เก็บที่: <code><?= htmlspecialchars($cat['categorie_icon'] ?: '(ยังไม่มีรูป)') ?></code>
                        </div>

                        <!-- แถวแก้ไข (ซ่อนอยู่จนกว่าจะกดแก้ไข) -->
                        <div class="cat-edit-row" id="edit-row-<?= $cat['category_id'] ?>">
                            <form method="POST" action="index.php?page=add_category" enctype="multipart/form-data">
                                <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">

                                <div class="mb-2">
                                    <label class="form-label fw-semibold small">ชื่อหมวดหมู่</label>
                                    <input type="text" name="category_name"
                                        class="form-control form-control-sm cat-name-input"
                                        value="<?= htmlspecialchars($cat['category_name']) ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label fw-semibold small">
                                        เปลี่ยนไอคอน
                                        <span class="text-muted fw-normal">(ไม่เลือก = ใช้ไอคอนเดิม)</span>
                                    </label>
                                    <input type="file" name="icon" accept="image/*" class="form-control form-control-sm"
                                        onchange="previewEditIcon(event, <?= $cat['category_id'] ?>)">
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="submit" name="update_category" class="btn btn-sm btn-primary">
                                        บันทึก
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        onclick="closeEditRow(<?= $cat['category_id'] ?>)">
                                        ยกเลิก
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- ปุ่มหลัก -->
                        <div class="cat-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="openEditRow(<?= $cat['category_id'] ?>)" id="edit-btn-<?= $cat['category_id'] ?>">
                                แก้ไข
                            </button>
                            <a href="index.php?page=add_category&delete_category_id=<?= $cat['category_id'] ?>"
                                data-confirm="ยืนยันการลบหมวดหมู่ '<?= htmlspecialchars($cat['category_name']) ?>'? รูปไอคอนจะถูกลบออกด้วย"
                                class="btn btn-sm btn-outline-danger">
                                ลบ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($categories)): ?>
            <div class="col-12">
                <div class="alert alert-info">ยังไม่มีหมวดหมู่ เพิ่มหมวดหมู่แรกได้เลย!</div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include_once 'layouts/bottom_layouts.php'; ?>

<script>
    // ── Preview ไอคอนตอนเพิ่ม ──────────────────────────────────
    function previewAddIcon(event) {
        const file = event.target.files[0];
        if (!file) return;
        const preview = document.getElementById('add-icon-preview');
        const placeholder = document.getElementById('add-placeholder');
        const label = document.getElementById('add-preview-label');
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            label.style.display = 'inline';
        };
        reader.readAsDataURL(file);
    }

    // ── Preview ไอคอนตอนแก้ไข ─────────────────────────────────
    function previewEditIcon(event, catId) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('icon-display-' + catId);
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // ── เปิด/ปิดแถวแก้ไข ────────────────────────────────────────
    function openEditRow(catId) {
        const row = document.getElementById('edit-row-' + catId);
        const btn = document.getElementById('edit-btn-' + catId);
        if (!row) return;
        row.classList.add('open');
        if (btn) btn.style.display = 'none';
    }

    function closeEditRow(catId) {
        const row = document.getElementById('edit-row-' + catId);
        const btn = document.getElementById('edit-btn-' + catId);
        const img = document.getElementById('icon-display-' + catId);
        if (!row) return;
        row.classList.remove('open');
        if (btn) btn.style.display = '';
        // คืนรูปเดิมถ้ามีการ preview ไปแล้ว
        if (img && img.dataset.original) img.src = img.dataset.original;
        // reset file input
        row.querySelectorAll('input[type="file"]').forEach(f => f.value = '');
    }
</script>