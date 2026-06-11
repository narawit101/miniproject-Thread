<?php
// อ่าน SweetAlert flash message จาก session (ถ้ามี)
$swal_data = null;
if (session_status() !== PHP_SESSION_NONE && isset($_SESSION['swal'])) {
    $swal_data = $_SESSION['swal'];
    unset($_SESSION['swal']); // ล้างทันทีหลังอ่าน (flash = แสดงครั้งเดียว)
}
?>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ─── Navbar dropdown toggle ─────────────────────────────────
    let subMenu = document.getElementById("subMenu");
    function toggleMenu() {
        subMenu.classList.toggle("open-menu");
    }

    // ─── 1. Flash Message (PHP session → SweetAlert) ────────────
    <?php if ($swal_data): ?>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon:  <?= json_encode($swal_data['icon']) ?>,
            title: <?= json_encode($swal_data['title']) ?>,
            text:  <?= json_encode($swal_data['text']) ?>,
            confirmButtonColor: '#123524',
            timer: <?= $swal_data['icon'] === 'success' ? 2500 : 0 ?>,
            timerProgressBar: <?= $swal_data['icon'] === 'success' ? 'true' : 'false' ?>,
            showConfirmButton: <?= $swal_data['icon'] === 'success' ? 'false' : 'true' ?>,
        });
    });
    <?php endif; ?>

    // ─── 2. Delete Confirm (ปุ่มที่ใส่ data-confirm) ────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const href = el.getAttribute('href') || el.getAttribute('data-href');
                const msg  = el.getAttribute('data-confirm') || 'คุณแน่ใจหรือไม่?';
                Swal.fire({
                    title: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor:  '#718096',
                    confirmButtonText: 'ยืนยัน ลบ',
                    cancelButtonText:  'ยกเลิก',
                }).then(function (result) {
                    if (result.isConfirmed && href) {
                        window.location.href = href;
                    }
                });
            });
        });
    });

    // ─── 3. Form Submit Confirm (ปุ่มที่ใส่ data-confirm-form) ──
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-confirm-form]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const msg  = btn.getAttribute('data-confirm-form') || 'ยืนยันการดำเนินการ?';
                const form = btn.closest('form');
                Swal.fire({
                    title: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor:  '#718096',
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText:  'ยกเลิก',
                }).then(function (result) {
                    if (result.isConfirmed && form) form.submit();
                });
            });
        });
    });

    // ─── 4. Logout Confirm ───────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const logoutBtn = document.querySelector('[data-confirm-logout]');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const href = logoutBtn.getAttribute('data-confirm-logout');
                Swal.fire({
                    title: 'ออกจากระบบ?',
                    text:  'คุณต้องการออกจากระบบใช่ไหม?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#123524',
                    cancelButtonColor:  '#718096',
                    confirmButtonText: 'ใช่ ออกจากระบบ',
                    cancelButtonText:  'ยกเลิก',
                }).then(function (result) {
                    if (result.isConfirmed) window.location.href = href;
                });
            });
        }
    });

    // ─── 5. AJAX Like (ปรับปรุงจากเดิม) ────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.like-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const postId = this.dataset.postId;
                const btn    = this.querySelector('button');
                const action = btn.value;

                fetch('index.php?page=toggle_like', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ post_id: postId, action: action })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.action === 'unlike') {
                        btn.textContent = 'ยกเลิกไลค์';
                        btn.value = 'unlike';
                    } else {
                        btn.textContent = 'ไลค์';
                        btn.value = 'like';
                    }
                    const countEl = form.nextElementSibling;
                    if (countEl) countEl.textContent = 'จำนวนไลค์: ' + data.like_count;
                });
            });
        });
    });
</script>
</body>
</html>