-- SQL script to seed mock data for DPI Forum (Categories, Users, Posts, Announcements)
-- Disables foreign key checks during truncation/deletion to prevent constraints from blocking the reset
SET FOREIGN_KEY_CHECKS = 0;

-- Clear all existing tables to start fresh
DELETE FROM users WHERE email != 'admin@gmail.com';
ALTER TABLE users AUTO_INCREMENT = 2; -- Reset auto increment to start at 2 since admin user is 1

TRUNCATE TABLE `categories`;
TRUNCATE TABLE `posts`;
TRUNCATE TABLE `comments`;
TRUNCATE TABLE `likes`;
TRUNCATE TABLE `announcements`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Insert 10 Categories (Using Google Material Symbols as icon names)
INSERT INTO `categories` (`category_id`, `category_name`, `categorie_icon`) VALUES
(1, 'ทั่วไป', 'forum'),
(2, 'การเขียนโปรแกรม', 'code'),
(3, 'ข่าวสาร', 'newspaper'),
(4, 'การออกแบบ', 'brush'),
(5, 'เทคโนโลยี', 'devices'),
(6, 'กีฬา', 'sports_soccer'),
(7, 'เกม', 'sports_esports'),
(8, 'ความบันเทิง', 'movie'),
(9, 'การศึกษา', 'school'),
(10, 'ไลฟ์สไตล์', 'spa');

-- 2. Insert 10 Mock Users (Passwords are '123456' hashed via bcrypt)
INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `role`) VALUES
(2, 'สมชาย', 'ดีงาม', 'somchai@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(3, 'สมศรี', 'รักดี', 'somsri@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(4, 'วิชัย', 'ปัญญา', 'wichai@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(5, 'อนันต์', 'รุ่งเรือง', 'anant@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(6, 'กิตติ', 'สว่างศรี', 'kitti@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(7, 'นภา', 'สว่างจิต', 'napa@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(8, 'จิรา', 'โชคดี', 'jira@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(9, 'ดนัย', 'มีสุข', 'danai@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(10, 'มณี', 'ใจงาม', 'manee@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user'),
(11, 'ปรีชา', 'รุ่งสว่าง', 'preecha@gmail.com', '$2y$10$tM9sEGBN60r1bZ/Dk42H5eWnBv8k.79K99T51eE9jT7sM/H4V.nO2', 'user');

-- 3. Insert 10 Mock Posts (Timestamps are in UTC, which PHP converts to local ICT timezone)
INSERT INTO `posts` (`title`, `content`, `user_id`, `category_id`, `created_at`) VALUES
('ยินดีต้อนรับสู่บอร์ดกระทู้ DPI ครับ', 'สวัสดีทุกคนครับ ยินดีที่ได้เข้ามาร่วมพูดคุยแลกเปลี่ยนข้อมูลในบอร์ดกระทู้ DPI แห่งนี้ แลกเปลี่ยนความเห็นกันได้เต็มที่เลย!', 2, 1, '2026-06-05 02:12:00'),
('เริ่มต้นตั้งกระทู้ DPI หมวดเขียนโปรแกรม', 'ผมเริ่มเขียนกระทู้ DPI แรกในหมวดนี้ อยากถามว่าแนวโน้มการเขียน PHP ในยุคนี้เน้นใช้ Framework ตัวไหนกันครับ?', 3, 2, '2026-06-06 04:20:30'),
('สรุปข่าวเทคโนโลยีในกระทู้ DPI สัปดาห์นี้', 'ขอสรุปข่าวเด่นเรื่องชิปรุ่นใหม่ล่าสุดที่ประมวลผล AI ได้เร็วขึ้นถึง 10 เท่าตัว ลงในกระทู้ DPI นี้นะครับ', 4, 3, '2026-06-07 07:05:15'),
('ตั้งกระทู้ DPI แชร์ทักษะการออกแบบ UI', 'การออกแบบ UI ที่ดีต้องเน้นความง่ายในการใช้งาน ขอแชร์เทคนิคการออกแบบในกระทู้ DPI นี้ครับ', 5, 4, '2026-06-08 09:30:00'),
('กระทู้ DPI แนะนำอุปกรณ์ Smart Home', 'ขอเปิดกระทู้ DPI ชวนคุยเรื่องอุปกรณ์สมาร์ทโฮม ตอนนี้ใครใช้แบรนด์ไหนอยู่บ้างครับ?', 6, 5, '2026-06-09 03:15:45'),
('กระทู้ DPI รายงานผลฟุตบอลเมื่อคืนนี้', 'เกมนัดสำคัญเมื่อคืนลุ้นสนุกมาก ทีมโปรดเอาชนะไปได้อย่างหวุดหวิด 2-1 ขึ้นนำตารางคะแนนชั่วคราว', 7, 6, '2026-06-10 01:45:00'),
('ชวนตั้งกระทู้ DPI รีวิวเกม RPG ปีนี้', 'สำหรับผมปีนี้ขอยกให้ภาคต่อของเกมโปรดที่เนื้อเรื่องเข้มข้น มาร่วมแชร์เกมโปรดของคุณในกระทู้ DPI นี้กัน!', 8, 7, '2026-06-11 12:10:00'),
('แนะนำซีรีส์ไซไฟน่าดูในกระทู้ DPI', 'เพิ่งดูจบไปเมื่อคืน พล็อตเรื่องเกี่ยวกับการข้ามมิติทำออกมาได้ดีมาก มาพูดคุยกันในกระทู้ DPI นี้ได้เลย', 9, 8, '2026-06-11 18:25:30'),
('กระทู้ DPI แชร์ทริกการแบ่งเวลาเรียน', 'การทำตารางเวลารายวันช่วยให้เราบริหารจัดการงานและการเรียนได้ดีขึ้นมาก เลยขอตั้งกระทู้ DPI นี้แชร์แนวคิดครับ', 10, 9, '2026-06-11 23:40:00'),
('กระทู้ DPI แนะนำการจัดโต๊ะทำงานเพื่อสุขภาพ', 'การเลือกเก้าอี้ Ergonomic และการปรับหน้าจอให้อยู่ในระดับสายตา ช่วยลดอาการปวดหลังและออฟฟิศซินโดรมได้จริง ขอแนะนำในกระทู้ DPI นี้ครับ', 11, 10, '2026-06-12 03:30:15');

-- 4. Insert 5 System Announcements (Timestamps are in UTC, which PHP converts to local ICT timezone)
INSERT INTO `announcements` (`title`, `description`, `created_at`) VALUES
('ปรับปรุงระบบบอร์ดเวอร์ชันใหม่', 'เราได้ทำการปรับปรุงหน้าตาเว็บบอร์ดให้ตอบสนองได้เร็วขึ้นและเป็นมิตรกับผู้ใช้งานมากขึ้น หวังว่าทุกคนจะชอบครับ!', '2026-06-05 01:30:00'),
('กฎระเบียบการใช้งานบอร์ดร่วมกัน', 'เพื่อสังคมที่น่าอยู่ ขอความร่วมมือผู้ใช้งานทุกท่านสุภาพและเคารพความคิดเห็นที่แตกต่างระหว่างกันด้วยนะครับ', '2026-06-06 03:15:00'),
('กิจกรรมเปิดตัว DPI Forum ซีซั่นใหม่', 'เตรียมพบกับกิจกรรมชิงรางวัลพิเศษสำหรับผู้เขียนกระทู้ยอดนิยมที่มีผู้กดไลค์สูงสุดประจำสัปడాห์นี้ รายละเอียดเพิ่มเติมจะแจ้งให้ทราบเร็ว ๆ นี้', '2026-06-08 07:00:00'),
('แจ้งปิดปรับปรุงเซิร์ฟเวอร์ประจำเดือน', 'ระบบจะปิดปรับปรุงชั่วคราวในวันอาทิตย์นี้ เวลา 02:00 - 04:00 น. ขออภัยในความไม่สะดวกล่วงหน้าครับ', '2026-06-10 11:20:00'),
('ยินดีต้อนรับสมาชิกใหม่ทุกคน!', 'ยินดีต้อนรับเข้าสู่ครอบครัว DPI Forum แหล่งรวมความคิดสร้างสรรค์และเรื่องราวน่าสนใจ สามารถแลกเปลี่ยนมุมมองกันได้อย่างอิสระ!', '2026-06-12 02:00:00');
