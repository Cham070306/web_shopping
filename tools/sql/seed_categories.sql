-- ============================================================
-- seed_categories.sql — 3legant Web Shopping
-- ============================================================
-- Đồng bộ 7 categories khớp với CATEGORY_QUERIES trong tiki_crawler.py
-- và navbar dropdown trong includes/navbar.php
--
-- HOW TO USE:
--   Chạy file này MỘT LẦN sau khi import database.sql
--   hoặc khi cần reset categories về trạng thái chuẩn.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Reset và seed lại bảng categories (Dùng DELETE để tránh lỗi FK)
DELETE FROM `categories`;
ALTER TABLE `categories` AUTO_INCREMENT = 1;

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `image`, `description`, `sort_order`, `is_active`) VALUES
(1, 'Living Room',  'living-room',  NULL, 'living-room.jpg',  'Sofa, ghế thư giãn, kệ tivi, bàn trà phòng khách', 1, 1),
(2, 'Bedroom',      'bedroom',      NULL, 'bedroom.jpg',      'Giường ngủ, tủ quần áo, đèn ngủ, nội thất phòng ngủ', 2, 1),
(3, 'Kitchen',      'kitchen',      NULL, 'kitchen.jpg',      'Tủ bếp, bàn ăn, ghế ăn, phụ kiện nhà bếp', 3, 1),
(4, 'Decor',        'decor',        NULL, 'decor.jpg',        'Phụ kiện trang trí, đèn, bình hoa, nến thơm', 4, 1),
(5, 'Dining Room',  'dining-room',  NULL, 'dining-room.jpg',  'Bàn ăn, ghế ăn, tủ rượu, đèn bàn ăn', 5, 1),
(6, 'Outdoor',      'outdoor',      NULL, 'outdoor.jpg',      'Bàn ghế sân vườn, xích đu, ghế ngoài trời', 6, 1),
(7, 'Accessories & Decor', 'accessories-decor', NULL, 'accessories.jpg', 'Phụ kiện và đồ trang trí đa năng', 7, 1);

SET FOREIGN_KEY_CHECKS = 1;

-- Kiểm tra kết quả
SELECT id, name, slug FROM `categories` ORDER BY id;
