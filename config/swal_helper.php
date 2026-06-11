<?php
/**
 * SweetAlert2 Flash Message Helper
 * 
 * ใช้ฟังก์ชันนี้ก่อน header('Location: ...') เพื่อแสดง SweetAlert
 * ในหน้าปลายทางอัตโนมัติ
 * 
 * @param string $icon    'success' | 'error' | 'warning' | 'info' | 'question'
 * @param string $title   หัวข้อ popup
 * @param string $text    ข้อความ (optional)
 */
function set_swal(string $icon, string $title, string $text = ''): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['swal'] = [
        'icon'  => $icon,
        'title' => $title,
        'text'  => $text,
    ];
}
