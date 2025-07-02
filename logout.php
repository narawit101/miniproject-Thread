<?php
session_start();
session_unset();  // ล้างข้อมูลทั้งหมดใน session
session_destroy();  // ทำลาย session

// ตั้งค่า Cache-Control header เพื่อป้องกันการแคช
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ใช้ JavaScript เพื่อลบประวัติการเข้าชมและเปลี่ยนเส้นทางไปยังหน้า login
echo '<script>
    history.pushState(null, "", "login.php");
    window.onpopstate = function () {
        history.pushState(null, "", "login.php");
    };
    window.location.replace("login.php");
</script>';

exit();
