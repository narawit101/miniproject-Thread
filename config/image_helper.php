<?php
/**
 * Image Upload, Compression & Resizing Helper
 * 
 * Helper สำหรับจัดการรูปภาพที่อัปโหลดเข้าสู่ระบบ 
 * ช่วยบีบอัดขนาดไฟล์และย่อขนาดภาพ (Resize) เพื่อประหยัดพื้นที่เซิร์ฟเวอร์
 */

/**
 * ฟังก์ชันหลักในการย้ายและบีบอัดรูปภาพ
 * 
 * @param array  $file        ไฟล์รูปภาพจาก $_FILES[...]
 * @param string $destination พาธเป้าหมายที่จะบันทึกไฟล์ (เช่น 'uploads/posts/img.jpg')
 * @param int    $max_width   ขนาดความกว้างสูงสุด (สัดส่วนความสูงจะถูกคำนวณตามอัตราส่วน)
 * @param int    $quality     คุณภาพของภาพหลังการบีบอัด (1-100 สำหรับ JPEG/WEBP)
 * @return bool               คืนค่า true เมื่อบีบอัดและบันทึกสำเร็จ คืนค่า false หากผิดพลาด
 */
function uploadAndCompressImage(array $file, string $destination, int $max_width = 1000, int $quality = 75): bool
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $tmp_path = $file['tmp_name'];

    // ตรวจสอบขนาดไฟล์เบื้องต้นไม่เกิน 15MB เพื่อป้องกัน Server Timeout
    if ($file['size'] > 15 * 1024 * 1024) {
        return false;
    }

    // สร้างโฟลเดอร์ปลายทางหากยังไม่มี
    $dest_dir = dirname($destination);
    if (!is_dir($dest_dir)) {
        @mkdir($dest_dir, 0755, true);
    }

    // ตรวจสอบความพร้อมใช้งานของ GD library
    if (!extension_loaded('gd')) {
        // กรณีไม่มี GD library ให้ย้ายไฟล์โดยตรงตามปกติ (fallback)
        return move_uploaded_file($tmp_path, $destination);
    }

    // ดึงข้อมูลรูปภาพ
    $info = @getimagesize($tmp_path);
    if (!$info) {
        // หาก getimagesize ล้มเหลว ตรวจสอบว่ารูปภาพเป็น SVG หรือไม่
        $mime = mime_content_type($tmp_path);
        if ($mime === 'image/svg+xml') {
            // ย้ายไฟล์ SVG ตรงๆ เนื่องจากไม่สามารถบีบอัดใน GD ได้
            return move_uploaded_file($tmp_path, $destination);
        }
        return false;
    }

    $width = $info[0];
    $height = $info[1];
    $type = $info[2];

    // ตรวจสอบและย้ายไฟล์ GIF ทันทีเพื่อรักษา Animation (การย่อใน GD จะทำให้ภาพ GIF กลายเป็นภาพนิ่ง)
    if ($type === IMAGETYPE_GIF) {
        return move_uploaded_file($tmp_path, $destination);
    }

    // สร้าง Resource ของภาพต้นฉบับ
    $source = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = @imagecreatefromjpeg($tmp_path);
            break;
        case IMAGETYPE_PNG:
            $source = @imagecreatefrompng($tmp_path);
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                $source = @imagecreatefromwebp($tmp_path);
            }
            break;
    }

    if (!$source) {
        // หากไม่สามารถสร้าง resource ได้ ให้ก๊อปปี้ไฟล์ตรงๆ ป้องกันภาพเสีย
        return move_uploaded_file($tmp_path, $destination);
    }

    // คำนวณสัดส่วนรูปภาพใหม่
    $new_width = $width;
    $new_height = $height;

    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = round(($height * $max_width) / $width);
    }

    // สร้าง canvas สำหรับวาดรูปภาพย่อใหม่
    $target = imagecreatetruecolor($new_width, $new_height);
    if (!$target) {
        $source = null;
        return move_uploaded_file($tmp_path, $destination);
    }

    // รักษาความโปร่งใส (Alpha Transparency) สำหรับไฟล์ PNG และ WEBP
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($target, false);
        imagesavealpha($target, true);
        if ($type === IMAGETYPE_PNG) {
            $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
            imagefilledrectangle($target, 0, 0, $new_width, $new_height, $transparent);
        }
    }

    // ทำการย่อรูปภาพ (Resample)
    if (!imagecopyresampled($target, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
        $source = null;
        $target = null;
        return move_uploaded_file($tmp_path, $destination);
    }

    // บันทึกรูปภาพลงปลายทางและบีบอัดตามประเภทไฟล์
    $saved = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $saved = imagejpeg($target, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            // PNG บีบอัดแบบ Lossless ค่าระหว่าง 0-9 (ยิ่งสูงบีบอัดไฟล์เล็กลงแต่ใช้เวลาประมวลผลนานขึ้น)
            // คุณภาพ 75% แปลงเป็นระดับความพยายามในการบีบอัดประมาณ 6-7
            $png_quality = round(9 - ($quality * 9 / 100));
            $png_quality = max(0, min(9, $png_quality));
            $saved = imagepng($target, $destination, $png_quality);
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagewebp')) {
                $saved = imagewebp($target, $destination, $quality);
            } else {
                $saved = imagejpeg($target, $destination, $quality);
            }
            break;
    }

    // คืนค่าหน่วยความจำ (ใน PHP 8+ การเคลียร์ตัวแปรออบเจกต์จะคืนค่าเมมโมรี่แทน imagedestroy)
    $source = null;
    $target = null;

    // หากบันทึกสำเร็จลุล่วง
    if (!$saved) {
        return move_uploaded_file($tmp_path, $destination);
    }

    return true;
}

/**
 * ฟังก์ชันช่วยตรวจสอบขนาดของไฟล์อัปโหลด
 * 
 * @param array $file     ไฟล์ที่ต้องการเช็คจาก $_FILES[...]
 * @param int   $max_mb   ขนาดสูงสุดที่อนุญาตในหน่วย Megabytes
 * @return bool           คืนค่า true หากไฟล์ขนาดไม่เกินกำหนด
 */
function validateUploadedFileSize(array $file, int $max_mb = 5): bool
{
    if (!isset($file['size']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    return $file['size'] <= ($max_mb * 1024 * 1024);
}
