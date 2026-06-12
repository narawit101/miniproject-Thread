<?php
date_default_timezone_set('Asia/Bangkok');

if (!function_exists('formatThaiDate')) {
    function formatThaiDate(?string $datetime, bool $shortYear = false, bool $showTime = true): string {
        if (!$datetime) return '';
        try {
            $date = new DateTime($datetime, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('Asia/Bangkok'));
            $time = $date->getTimestamp();
        } catch (Exception $e) {
            $time = strtotime($datetime); // Fallback
        }
        
        $thai_months = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];
        $date_day = date('j', $time);
        $month = $thai_months[(int)date('n', $time)];
        
        $full_year = (int)date('Y', $time) + 543;
        $year = $shortYear ? ($full_year % 100) : $full_year;
        
        $result = "$date_day $month $year";
        if ($showTime) {
            $hour_min = date('H:i', $time);
            $result .= ($shortYear ? " • $hour_min" : " เวลา $hour_min น.");
        }
        return $result;
    }
}
?>
