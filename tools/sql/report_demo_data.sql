-- =========================================================
-- DEMO DATA: Customers + Orders for Dashboard & Reports
-- Database: web_shopping
-- Import file này sau khi đã import web_shopping.sql gốc
-- Có thể chạy lại nhiều lần, file sẽ xóa dữ liệu demo cũ trước khi thêm mới
-- =========================================================

SET NAMES utf8mb4;
START TRANSACTION;
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa dữ liệu demo cũ nếu đã import trước đó
DELETE FROM order_status_logs WHERE order_id IN (1001,1002,1003,1004,1005,1006,1007,1008,1009,1010,1011,1012,1013,1014,1015,1016,1017,1018,1019,1020,1021,1022,1023,1024);
DELETE FROM payments WHERE order_id IN (1001,1002,1003,1004,1005,1006,1007,1008,1009,1010,1011,1012,1013,1014,1015,1016,1017,1018,1019,1020,1021,1022,1023,1024);
DELETE FROM order_items WHERE order_id IN (1001,1002,1003,1004,1005,1006,1007,1008,1009,1010,1011,1012,1013,1014,1015,1016,1017,1018,1019,1020,1021,1022,1023,1024);
DELETE FROM orders WHERE id IN (1001,1002,1003,1004,1005,1006,1007,1008,1009,1010,1011,1012,1013,1014,1015,1016,1017,1018,1019,1020,1021,1022,1023,1024) OR order_code LIKE 'DEMO-%';
DELETE FROM user_addresses WHERE user_id BETWEEN 9 AND 18;
DELETE FROM customer_notes WHERE user_id BETWEEN 9 AND 18;
DELETE FROM users WHERE id BETWEEN 9 AND 18 OR email LIKE '%@shop.demo';

SET FOREIGN_KEY_CHECKS = 1;

-- Sửa nhẹ category cho nhóm sản phẩm sân vườn để Top Selling Categories hiện đúng Outdoor
UPDATE products SET category_id = 6 WHERE id IN (103,104,105,106,107,108,109,110,113,114,115,116,117,118,119);

-- Thêm khách hàng demo
INSERT INTO users (`id`, `name`, `email`, `password`, `phone`, `gender`, `date_of_birth`, `avatar`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(9, 'Nguyen Minh Chau', 'chau.nguyen@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000001', 'female', '1999-02-14', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 9 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(10, 'Tran Gia Bao', 'bao.tran@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000002', 'male', '1998-07-22', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 10 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(11, 'Le Thu Trang', 'trang.le@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000003', 'female', '2000-11-03', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 11 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(12, 'Pham Quoc Huy', 'huy.pham@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000004', 'male', '1997-04-18', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 12 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(13, 'Vo Ngoc Mai', 'mai.vo@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000005', 'female', '2001-09-10', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 13 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(14, 'Dang Hoang Phuc', 'phuc.dang@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000006', 'male', '1996-12-25', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 14 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(15, 'Ho Thi Kim Ngan', 'ngan.ho@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000007', 'female', '1999-06-06', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 15 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(16, 'Bui Tuan Kiet', 'kiet.bui@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000008', 'male', '1995-03-19', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 16 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(17, 'Do Thanh Tam', 'tam.do@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000009', 'other', '2002-01-30', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 17 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW()),
(18, 'Mai Anh Duong', 'duong.mai@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0913000010', 'female', '1998-05-11', 'default.jpg', 'user', 1, DATE_SUB(NOW(), INTERVAL 18 HOUR), DATE_SUB(NOW(), INTERVAL 35 DAY), NOW());

-- Địa chỉ giao hàng mặc định cho khách hàng demo
INSERT INTO user_addresses (`id`, `user_id`, `full_name`, `phone`, `address`, `ward`, `district`, `city`, `province`, `country`, `zip_code`, `is_default`, `type`, `created_at`, `updated_at`) VALUES
(1001, 9, 'Nguyen Minh Chau', '0913000001', '15 Nguyen Hue', 'Ben Nghe', 'District 1', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 1, 'shipping', NOW(), NOW()),
(1002, 10, 'Tran Gia Bao', '0913000002', '27 Phan Chu Trinh', 'Hai Chau 1', 'Hai Chau', 'Da Nang', '550000', 'Vietnam', '550000', 1, 'shipping', NOW(), NOW()),
(1003, 11, 'Le Thu Trang', '0913000003', '68 Lang Ha', 'Lang Ha', 'Dong Da', 'Ha Noi', '100000', 'Vietnam', '100000', 1, 'shipping', NOW(), NOW()),
(1004, 12, 'Pham Quoc Huy', '0913000004', '91 Cach Mang Thang 8', 'An Hoa', 'Ninh Kieu', 'Can Tho', '900000', 'Vietnam', '900000', 1, 'shipping', NOW(), NOW()),
(1005, 13, 'Vo Ngoc Mai', '0913000005', '42 Nguyen Van Linh', 'Tan Phong', 'District 7', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 1, 'shipping', NOW(), NOW()),
(1006, 14, 'Dang Hoang Phuc', '0913000006', '11 Hoang Dieu', 'Minh An', 'Hoi An', 'Quang Nam', '560000', 'Vietnam', '560000', 1, 'shipping', NOW(), NOW()),
(1007, 15, 'Ho Thi Kim Ngan', '0913000007', '36 Tran Phu', 'Loc Tho', 'Nha Trang', 'Khanh Hoa', '650000', 'Vietnam', '650000', 1, 'shipping', NOW(), NOW()),
(1008, 16, 'Bui Tuan Kiet', '0913000008', '72 Dien Bien Phu', 'Da Kao', 'District 1', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 1, 'shipping', NOW(), NOW()),
(1009, 17, 'Do Thanh Tam', '0913000009', '24 Ly Thai To', 'Hang Trong', 'Hoan Kiem', 'Ha Noi', '100000', 'Vietnam', '100000', 1, 'shipping', NOW(), NOW()),
(1010, 18, 'Mai Anh Duong', '0913000010', '99 Nguyen Dinh Chieu', 'Vo Thi Sau', 'District 3', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 1, 'shipping', NOW(), NOW());

-- Đơn hàng demo trong 30 ngày gần nhất: đủ pending / confirmed / shipping / delivered / cancelled
INSERT INTO orders (`id`, `user_id`, `order_code`, `full_name`, `email`, `phone`, `address`, `billing_address`, `city`, `province`, `country`, `zip_code`, `note`, `subtotal`, `discount`, `shipping_fee`, `total`, `coupon_code`, `tracking_code`, `payment_method`, `payment_status`, `status`, `created_at`, `updated_at`, `delivered_at`) VALUES
(1001, 9, 'DEMO-1001', 'Nguyen Minh Chau', 'chau.nguyen@shop.com', '0913000001', '15 Nguyen Hue', '15 Nguyen Hue', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 6030000.00, 500000.00, 0.00, 5530000.00, 'WELCOME10', 'TRK-DEMO-1001', 'momo', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY), ' 11:11:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 27 DAY), ' 16:30:00')),
(1002, 10, 'DEMO-1002', 'Tran Gia Bao', 'bao.tran@shop.com', '0913000002', '27 Phan Chu Trinh', '27 Phan Chu Trinh', 'Da Nang', '550000', 'Vietnam', '550000', 'Demo order for dashboard/report testing', 5470000.00, 0.00, 0.00, 5470000.00, NULL, 'TRK-DEMO-1002', 'bank_transfer', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 27 DAY), ' 12:12:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 25 DAY), ' 16:30:00')),
(1003, 11, 'DEMO-1003', 'Le Thu Trang', 'trang.le@shop.com', '0913000003', '68 Lang Ha', '68 Lang Ha', 'Ha Noi', '100000', 'Vietnam', '100000', 'Demo order for dashboard/report testing', 2570000.00, 0.00, 30000.00, 2600000.00, NULL, NULL, 'cod', 'pending', 'confirmed', CONCAT(DATE_SUB(CURDATE(), INTERVAL 25 DAY), ' 13:13:00'), NOW(), NULL),
(1004, 12, 'DEMO-1004', 'Pham Quoc Huy', 'huy.pham@shop.com', '0913000004', '91 Cach Mang Thang 8', '91 Cach Mang Thang 8', 'Can Tho', '900000', 'Vietnam', '900000', 'Demo order for dashboard/report testing', 6710000.00, 50000.00, 0.00, 6660000.00, 'SAVE50', 'TRK-DEMO-1004', 'momo', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 23 DAY), ' 14:14:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 21 DAY), ' 16:30:00')),
(1005, 13, 'DEMO-1005', 'Vo Ngoc Mai', 'mai.vo@shop.com', '0913000005', '42 Nguyen Van Linh', '42 Nguyen Van Linh', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 7810000.00, 700000.00, 0.00, 7110000.00, 'SPRING15', 'TRK-DEMO-1005', 'bank_transfer', 'paid', 'shipping', CONCAT(DATE_SUB(CURDATE(), INTERVAL 21 DAY), ' 15:15:00'), NOW(), NULL),
(1006, 14, 'DEMO-1006', 'Dang Hoang Phuc', 'phuc.dang@shop.com', '0913000006', '11 Hoang Dieu', '11 Hoang Dieu', 'Quang Nam', '560000', 'Vietnam', '560000', 'Demo order for dashboard/report testing', 6200000.00, 0.00, 0.00, 6200000.00, NULL, NULL, 'cod', 'failed', 'cancelled', CONCAT(DATE_SUB(CURDATE(), INTERVAL 19 DAY), ' 16:16:00'), NOW(), NULL),
(1007, 15, 'DEMO-1007', 'Ho Thi Kim Ngan', 'ngan.ho@shop.com', '0913000007', '36 Tran Phu', '36 Tran Phu', 'Khanh Hoa', '650000', 'Vietnam', '650000', 'Demo order for dashboard/report testing', 4290000.00, 15000.00, 0.00, 4275000.00, 'FREESHIP', 'TRK-DEMO-1007', 'momo', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 17 DAY), ' 17:17:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 15 DAY), ' 16:30:00')),
(1008, 16, 'DEMO-1008', 'Bui Tuan Kiet', 'kiet.bui@shop.com', '0913000008', '72 Dien Bien Phu', '72 Dien Bien Phu', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 4030000.00, 0.00, 0.00, 4030000.00, NULL, 'TRK-DEMO-1008', 'bank_transfer', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 15 DAY), ' 09:18:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 13 DAY), ' 16:30:00')),
(1009, 17, 'DEMO-1009', 'Do Thanh Tam', 'tam.do@shop.com', '0913000009', '24 Ly Thai To', '24 Ly Thai To', 'Ha Noi', '100000', 'Vietnam', '100000', 'Demo order for dashboard/report testing', 3040000.00, 0.00, 0.00, 3040000.00, NULL, NULL, 'cod', 'pending', 'confirmed', CONCAT(DATE_SUB(CURDATE(), INTERVAL 13 DAY), ' 10:19:00'), NOW(), NULL),
(1010, 18, 'DEMO-1010', 'Mai Anh Duong', 'duong.mai@shop.com', '0913000010', '99 Nguyen Dinh Chieu', '99 Nguyen Dinh Chieu', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 4550000.00, 25000.00, 0.00, 4525000.00, 'HOME25', 'TRK-DEMO-1010', 'momo', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 11 DAY), ' 11:20:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY), ' 16:30:00')),
(1011, 5, 'DEMO-1011', 'Nguyen Thi Anh', 'anh.nguyen@shop.com', '0912000001', '12 Le Loi', '12 Le Loi', 'Ho Chi Minh City', 'District 1', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 9300000.00, 0.00, 0.00, 9300000.00, NULL, 'TRK-DEMO-1011', 'bank_transfer', 'paid', 'shipping', CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY), ' 12:21:00'), NOW(), NULL),
(1012, 6, 'DEMO-1012', 'Tran Van Binh', 'binh.tran@shop.com', '0912000002', '45 Tran Hung Dao', '45 Tran Hung Dao', 'Da Nang', 'Hai Chau', 'Vietnam', '550000', 'Demo order for dashboard/report testing', 8830000.00, 1000000.00, 0.00, 7830000.00, 'VIP20', 'TRK-DEMO-1012', 'momo', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 8 DAY), ' 13:22:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 6 DAY), ' 16:30:00')),
(1013, 7, 'DEMO-1013', 'Le Hoang Nam', 'nam.le@shop.com', '0912000003', '88 Nguyen Van Cu', '88 Nguyen Van Cu', 'Can Tho', 'Ninh Kieu', 'Vietnam', '900000', 'Demo order for dashboard/report testing', 1010000.00, 0.00, 30000.00, 1040000.00, NULL, NULL, 'cod', 'pending', 'pending', CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 14:23:00'), NOW(), NULL),
(1014, 8, 'DEMO-1014', 'Pham Thu Ha', 'ha.pham@shop.com', '0912000004', '22 Xuan Thuy', '22 Xuan Thuy', 'Ha Noi', 'Cau Giay', 'Vietnam', '100000', 'Demo order for dashboard/report testing', 6480000.00, 0.00, 0.00, 6480000.00, NULL, 'TRK-DEMO-1014', 'bank_transfer', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 6 DAY), ' 15:24:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY), ' 16:30:00')),
(1015, 9, 'DEMO-1015', 'Nguyen Minh Chau', 'chau.nguyen@shop.com', '0913000001', '15 Nguyen Hue', '15 Nguyen Hue', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 4180000.00, 0.00, 0.00, 4180000.00, NULL, NULL, 'momo', 'paid', 'confirmed', CONCAT(DATE_SUB(CURDATE(), INTERVAL 5 DAY), ' 16:25:00'), NOW(), NULL),
(1016, 10, 'DEMO-1016', 'Tran Gia Bao', 'bao.tran@shop.com', '0913000002', '27 Phan Chu Trinh', '27 Phan Chu Trinh', 'Da Nang', '550000', 'Vietnam', '550000', 'Demo order for dashboard/report testing', 9590000.00, 15000.00, 0.00, 9575000.00, 'FREESHIP', 'TRK-DEMO-1016', 'cod', 'pending', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY), ' 17:26:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 16:30:00')),
(1017, 11, 'DEMO-1017', 'Le Thu Trang', 'trang.le@shop.com', '0913000003', '68 Lang Ha', '68 Lang Ha', 'Ha Noi', '100000', 'Vietnam', '100000', 'Demo order for dashboard/report testing', 4670000.00, 0.00, 0.00, 4670000.00, NULL, 'TRK-DEMO-1017', 'momo', 'paid', 'shipping', CONCAT(DATE_SUB(CURDATE(), INTERVAL 3 DAY), ' 09:27:00'), NOW(), NULL),
(1018, 12, 'DEMO-1018', 'Pham Quoc Huy', 'huy.pham@shop.com', '0913000004', '91 Cach Mang Thang 8', '91 Cach Mang Thang 8', 'Can Tho', '900000', 'Vietnam', '900000', 'Demo order for dashboard/report testing', 5650000.00, 50000.00, 0.00, 5600000.00, 'SAVE50', 'TRK-DEMO-1018', 'bank_transfer', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 10:28:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 16:30:00')),
(1019, 13, 'DEMO-1019', 'Vo Ngoc Mai', 'mai.vo@shop.com', '0913000005', '42 Nguyen Van Linh', '42 Nguyen Van Linh', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 3640000.00, 0.00, 0.00, 3640000.00, NULL, NULL, 'momo', 'failed', 'cancelled', CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 11:29:00'), NOW(), NULL),
(1020, 14, 'DEMO-1020', 'Dang Hoang Phuc', 'phuc.dang@shop.com', '0913000006', '11 Hoang Dieu', '11 Hoang Dieu', 'Quang Nam', '560000', 'Vietnam', '560000', 'Demo order for dashboard/report testing', 3340000.00, 0.00, 0.00, 3340000.00, NULL, NULL, 'cod', 'pending', 'confirmed', CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:30:00'), NOW(), NULL),
(1021, 15, 'DEMO-1021', 'Ho Thi Kim Ngan', 'ngan.ho@shop.com', '0913000007', '36 Tran Phu', '36 Tran Phu', 'Khanh Hoa', '650000', 'Vietnam', '650000', 'Demo order for dashboard/report testing', 6510000.00, 750000.00, 0.00, 5760000.00, 'SPRING15', 'TRK-DEMO-1021', 'momo', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 13:31:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 16:30:00')),
(1022, 16, 'DEMO-1022', 'Bui Tuan Kiet', 'kiet.bui@shop.com', '0913000008', '72 Dien Bien Phu', '72 Dien Bien Phu', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 1010000.00, 0.00, 30000.00, 1040000.00, NULL, NULL, 'cod', 'pending', 'pending', CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 14:32:00'), NOW(), NULL),
(1023, 17, 'DEMO-1023', 'Do Thanh Tam', 'tam.do@shop.com', '0913000009', '24 Ly Thai To', '24 Ly Thai To', 'Ha Noi', '100000', 'Vietnam', '100000', 'Demo order for dashboard/report testing', 9760000.00, 0.00, 0.00, 9760000.00, NULL, 'TRK-DEMO-1023', 'bank_transfer', 'paid', 'delivered', CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 15:33:00'), NOW(), CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 16:30:00')),
(1024, 18, 'DEMO-1024', 'Mai Anh Duong', 'duong.mai@shop.com', '0913000010', '99 Nguyen Dinh Chieu', '99 Nguyen Dinh Chieu', 'Ho Chi Minh City', '700000', 'Vietnam', '700000', 'Demo order for dashboard/report testing', 7490000.00, 25000.00, 0.00, 7465000.00, 'HOME25', 'TRK-DEMO-1024', 'momo', 'paid', 'shipping', CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 16:34:00'), NOW(), NULL);

-- Chi tiết sản phẩm trong từng đơn hàng
INSERT INTO order_items (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `product_sku`, `variant`, `price`, `quantity`, `subtotal`, `thumbnail`) VALUES
(10001, 1001, 1, NULL, 'Ghế sofa bed phòng khách', 'TK-102783096', NULL, 2470000.00, 2, 4940000.00, NULL),
(10002, 1001, 13, NULL, 'Bàn sofa bàn trà phòng khách SMLIFE Chiba', 'TK-120550531', NULL, 1090000.00, 1, 1090000.00, NULL),
(10003, 1002, 43, NULL, 'Bộ bàn ăn gia đình giá rẻ 4 ghế Cherry', 'TK-19412911', NULL, 2190000.00, 1, 2190000.00, NULL),
(10004, 1002, 55, NULL, 'Ghế ăn gỗ ASH có nệm', 'TK-116185348', NULL, 820000.00, 4, 3280000.00, NULL),
(10005, 1003, 63, NULL, 'Set 3 bình hoa Bát Tràng decor', 'TK-81302402', NULL, 450000.00, 3, 1350000.00, NULL),
(10006, 1003, 67, NULL, 'Đèn trang trí gắn tường LED hình con nai', 'TK-11423673', NULL, 390000.00, 2, 780000.00, NULL),
(10007, 1003, 73, NULL, 'Tranh treo tường PP_89', 'TK-50418970', NULL, 220000.00, 2, 440000.00, NULL),
(10008, 1004, 25, NULL, 'Giường ngủ gỗ cổ điển xuất khẩu', 'TK-90353928', NULL, 4690000.00, 1, 4690000.00, NULL),
(10009, 1004, 31, NULL, 'Tủ Để Đầu Giường - Tab Đầu Giường Gỗ MDF Cao Cấp kdg02', 'TK-271317452', NULL, 690000.00, 2, 1380000.00, NULL),
(10010, 1004, 40, NULL, 'Đèn bàn phòng ngủ elip', 'TK-194105327', NULL, 320000.00, 2, 640000.00, NULL),
(10011, 1005, 85, NULL, 'Bộ Bàn Ăn Candy 4 ghế', 'TK-274587884', NULL, 3890000.00, 1, 3890000.00, NULL),
(10012, 1005, 91, NULL, 'Ghế ăn bọc da Tundo trám giữa', 'TK-271294074', NULL, 980000.00, 4, 3920000.00, NULL),
(10013, 1006, 2, NULL, 'Sofa Phòng Khách Juno Sofa 06', 'TK-10005991', NULL, 6200000.00, 1, 6200000.00, NULL),
(10014, 1007, 103, NULL, 'Bộ bàn ghế sân vườn - NAVICOM', 'TK-58445324', NULL, 1790000.00, 1, 1790000.00, NULL),
(10015, 1007, 105, NULL, 'Ghế sân vườn thông minh Juno Sofa', 'TK-244173556', NULL, 1250000.00, 2, 2500000.00, NULL),
(10016, 1008, 1, NULL, 'Ghế sofa bed phòng khách', 'TK-102783096', NULL, 2470000.00, 1, 2470000.00, NULL),
(10017, 1008, 63, NULL, 'Set 3 bình hoa Bát Tràng decor', 'TK-81302402', NULL, 450000.00, 2, 900000.00, NULL),
(10018, 1008, 73, NULL, 'Tranh treo tường PP_89', 'TK-50418970', NULL, 220000.00, 3, 660000.00, NULL),
(10019, 1009, 49, NULL, 'Kệ inox để đồ đa năng nhà bếp', 'TK-191417562', NULL, 280000.00, 5, 1400000.00, NULL),
(10020, 1009, 55, NULL, 'Ghế ăn gỗ ASH có nệm', 'TK-116185348', NULL, 820000.00, 2, 1640000.00, NULL),
(10021, 1010, 97, NULL, 'Tủ đựng rượu bằng gỗ công nghiệp SMLIFE Abigale', 'TK-120355980', NULL, 2590000.00, 1, 2590000.00, NULL),
(10022, 1010, 91, NULL, 'Ghế ăn bọc da Tundo trám giữa', 'TK-271294074', NULL, 980000.00, 2, 1960000.00, NULL),
(10023, 1011, 43, NULL, 'Bộ bàn ăn gia đình giá rẻ 4 ghế Cherry', 'TK-19412911', NULL, 2190000.00, 2, 4380000.00, NULL),
(10024, 1011, 55, NULL, 'Ghế ăn gỗ ASH có nệm', 'TK-116185348', NULL, 820000.00, 6, 4920000.00, NULL),
(10025, 1012, 2, NULL, 'Sofa Phòng Khách Juno Sofa 06', 'TK-10005991', NULL, 6200000.00, 1, 6200000.00, NULL),
(10026, 1012, 13, NULL, 'Bàn sofa bàn trà phòng khách SMLIFE Chiba', 'TK-120550531', NULL, 1090000.00, 2, 2180000.00, NULL),
(10027, 1012, 63, NULL, 'Set 3 bình hoa Bát Tràng decor', 'TK-81302402', NULL, 450000.00, 1, 450000.00, NULL),
(10028, 1013, 31, NULL, 'Tủ Để Đầu Giường - Tab Đầu Giường Gỗ MDF Cao Cấp kdg02', 'TK-271317452', NULL, 690000.00, 1, 690000.00, NULL),
(10029, 1013, 40, NULL, 'Đèn bàn phòng ngủ elip', 'TK-194105327', NULL, 320000.00, 1, 320000.00, NULL),
(10030, 1014, 85, NULL, 'Bộ Bàn Ăn Candy 4 ghế', 'TK-274587884', NULL, 3890000.00, 1, 3890000.00, NULL),
(10031, 1014, 97, NULL, 'Tủ đựng rượu bằng gỗ công nghiệp SMLIFE Abigale', 'TK-120355980', NULL, 2590000.00, 1, 2590000.00, NULL),
(10032, 1015, 115, NULL, 'xích đu sân vườn TVL 102', 'TK-30010790', NULL, 2390000.00, 1, 2390000.00, NULL),
(10033, 1015, 103, NULL, 'Bộ bàn ghế sân vườn - NAVICOM', 'TK-58445324', NULL, 1790000.00, 1, 1790000.00, NULL),
(10034, 1016, 1, NULL, 'Ghế sofa bed phòng khách', 'TK-102783096', NULL, 2470000.00, 3, 7410000.00, NULL),
(10035, 1016, 13, NULL, 'Bàn sofa bàn trà phòng khách SMLIFE Chiba', 'TK-120550531', NULL, 1090000.00, 2, 2180000.00, NULL),
(10036, 1017, 43, NULL, 'Bộ bàn ăn gia đình giá rẻ 4 ghế Cherry', 'TK-19412911', NULL, 2190000.00, 1, 2190000.00, NULL),
(10037, 1017, 49, NULL, 'Kệ inox để đồ đa năng nhà bếp', 'TK-191417562', NULL, 280000.00, 3, 840000.00, NULL),
(10038, 1017, 55, NULL, 'Ghế ăn gỗ ASH có nệm', 'TK-116185348', NULL, 820000.00, 2, 1640000.00, NULL),
(10039, 1018, 25, NULL, 'Giường ngủ gỗ cổ điển xuất khẩu', 'TK-90353928', NULL, 4690000.00, 1, 4690000.00, NULL),
(10040, 1018, 40, NULL, 'Đèn bàn phòng ngủ elip', 'TK-194105327', NULL, 320000.00, 3, 960000.00, NULL),
(10041, 1019, 105, NULL, 'Ghế sân vườn thông minh Juno Sofa', 'TK-244173556', NULL, 1250000.00, 1, 1250000.00, NULL),
(10042, 1019, 115, NULL, 'xích đu sân vườn TVL 102', 'TK-30010790', NULL, 2390000.00, 1, 2390000.00, NULL),
(10043, 1020, 67, NULL, 'Đèn trang trí gắn tường LED hình con nai', 'TK-11423673', NULL, 390000.00, 4, 1560000.00, NULL),
(10044, 1020, 73, NULL, 'Tranh treo tường PP_89', 'TK-50418970', NULL, 220000.00, 4, 880000.00, NULL),
(10045, 1020, 63, NULL, 'Set 3 bình hoa Bát Tràng decor', 'TK-81302402', NULL, 450000.00, 2, 900000.00, NULL),
(10046, 1021, 91, NULL, 'Ghế ăn bọc da Tundo trám giữa', 'TK-271294074', NULL, 980000.00, 4, 3920000.00, NULL),
(10047, 1021, 97, NULL, 'Tủ đựng rượu bằng gỗ công nghiệp SMLIFE Abigale', 'TK-120355980', NULL, 2590000.00, 1, 2590000.00, NULL),
(10048, 1022, 49, NULL, 'Kệ inox để đồ đa năng nhà bếp', 'TK-191417562', NULL, 280000.00, 2, 560000.00, NULL),
(10049, 1022, 63, NULL, 'Set 3 bình hoa Bát Tràng decor', 'TK-81302402', NULL, 450000.00, 1, 450000.00, NULL),
(10050, 1023, 2, NULL, 'Sofa Phòng Khách Juno Sofa 06', 'TK-10005991', NULL, 6200000.00, 1, 6200000.00, NULL),
(10051, 1023, 1, NULL, 'Ghế sofa bed phòng khách', 'TK-102783096', NULL, 2470000.00, 1, 2470000.00, NULL),
(10052, 1023, 13, NULL, 'Bàn sofa bàn trà phòng khách SMLIFE Chiba', 'TK-120550531', NULL, 1090000.00, 1, 1090000.00, NULL),
(10053, 1024, 85, NULL, 'Bộ Bàn Ăn Candy 4 ghế', 'TK-274587884', NULL, 3890000.00, 1, 3890000.00, NULL),
(10054, 1024, 91, NULL, 'Ghế ăn bọc da Tundo trám giữa', 'TK-271294074', NULL, 980000.00, 2, 1960000.00, NULL),
(10055, 1024, 55, NULL, 'Ghế ăn gỗ ASH có nệm', 'TK-116185348', NULL, 820000.00, 2, 1640000.00, NULL);

-- Thanh toán demo
INSERT INTO payments (`id`, `order_id`, `method`, `amount`, `transaction_id`, `status`, `note`, `paid_at`, `created_at`, `updated_at`) VALUES
(1001, 1001, 'momo', 5530000.00, 'PAY-DEMO-1001', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY), ' 12:05:00'), NOW()),
(1002, 1002, 'bank_transfer', 5470000.00, 'PAY-DEMO-1002', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 27 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 27 DAY), ' 12:05:00'), NOW()),
(1003, 1003, 'cod', 2600000.00, NULL, 'pending', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 25 DAY), ' 12:05:00'), NOW()),
(1004, 1004, 'momo', 6660000.00, 'PAY-DEMO-1004', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 23 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 23 DAY), ' 12:05:00'), NOW()),
(1005, 1005, 'bank_transfer', 7110000.00, 'PAY-DEMO-1005', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 21 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 21 DAY), ' 12:05:00'), NOW()),
(1006, 1006, 'cod', 6200000.00, NULL, 'failed', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 19 DAY), ' 12:05:00'), NOW()),
(1007, 1007, 'momo', 4275000.00, 'PAY-DEMO-1007', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 17 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 17 DAY), ' 12:05:00'), NOW()),
(1008, 1008, 'bank_transfer', 4030000.00, 'PAY-DEMO-1008', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 15 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 15 DAY), ' 12:05:00'), NOW()),
(1009, 1009, 'cod', 3040000.00, NULL, 'pending', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 13 DAY), ' 12:05:00'), NOW()),
(1010, 1010, 'momo', 4525000.00, 'PAY-DEMO-1010', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 11 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 11 DAY), ' 12:05:00'), NOW()),
(1011, 1011, 'bank_transfer', 9300000.00, 'PAY-DEMO-1011', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY), ' 12:05:00'), NOW()),
(1012, 1012, 'momo', 7830000.00, 'PAY-DEMO-1012', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 8 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 8 DAY), ' 12:05:00'), NOW()),
(1013, 1013, 'cod', 1040000.00, NULL, 'pending', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:05:00'), NOW()),
(1014, 1014, 'bank_transfer', 6480000.00, 'PAY-DEMO-1014', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 6 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 6 DAY), ' 12:05:00'), NOW()),
(1015, 1015, 'momo', 4180000.00, 'PAY-DEMO-1015', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 5 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 5 DAY), ' 12:05:00'), NOW()),
(1016, 1016, 'cod', 9575000.00, NULL, 'pending', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY), ' 12:05:00'), NOW()),
(1017, 1017, 'momo', 4670000.00, 'PAY-DEMO-1017', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 3 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 3 DAY), ' 12:05:00'), NOW()),
(1018, 1018, 'bank_transfer', 5600000.00, 'PAY-DEMO-1018', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:05:00'), NOW()),
(1019, 1019, 'momo', 3640000.00, 'PAY-DEMO-1019', 'failed', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:05:00'), NOW()),
(1020, 1020, 'cod', 3340000.00, NULL, 'pending', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:05:00'), NOW()),
(1021, 1021, 'momo', 5760000.00, 'PAY-DEMO-1021', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:05:00'), NOW()),
(1022, 1022, 'cod', 1040000.00, NULL, 'pending', 'Demo payment for report testing', NULL, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:05:00'), NOW()),
(1023, 1023, 'bank_transfer', 9760000.00, 'PAY-DEMO-1023', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:05:00'), NOW()),
(1024, 1024, 'momo', 7465000.00, 'PAY-DEMO-1024', 'success', 'Demo payment for report testing', CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:05:00'), NOW());

-- Lịch sử trạng thái đơn hàng demo
INSERT INTO order_status_logs (`id`, `order_id`, `status`, `note`, `changed_by`, `changed_at`) VALUES
(10001, 1001, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY), ' 10:00:00')),
(10002, 1001, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 28 DAY), ' 11:00:00')),
(10003, 1001, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 27 DAY), ' 12:00:00')),
(10004, 1001, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 26 DAY), ' 13:00:00')),
(10005, 1002, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 27 DAY), ' 10:00:00')),
(10006, 1002, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 26 DAY), ' 11:00:00')),
(10007, 1002, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 25 DAY), ' 12:00:00')),
(10008, 1002, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 24 DAY), ' 13:00:00')),
(10009, 1003, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 25 DAY), ' 10:00:00')),
(10010, 1003, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 24 DAY), ' 11:00:00')),
(10011, 1004, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 23 DAY), ' 10:00:00')),
(10012, 1004, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 22 DAY), ' 11:00:00')),
(10013, 1004, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 21 DAY), ' 12:00:00')),
(10014, 1004, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 20 DAY), ' 13:00:00')),
(10015, 1005, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 21 DAY), ' 10:00:00')),
(10016, 1005, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 20 DAY), ' 11:00:00')),
(10017, 1005, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 19 DAY), ' 12:00:00')),
(10018, 1006, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 19 DAY), ' 10:00:00')),
(10019, 1006, 'cancelled', 'Demo status: cancelled', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 18 DAY), ' 11:00:00')),
(10020, 1007, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 17 DAY), ' 10:00:00')),
(10021, 1007, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 16 DAY), ' 11:00:00')),
(10022, 1007, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 15 DAY), ' 12:00:00')),
(10023, 1007, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 14 DAY), ' 13:00:00')),
(10024, 1008, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 15 DAY), ' 10:00:00')),
(10025, 1008, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 14 DAY), ' 11:00:00')),
(10026, 1008, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 13 DAY), ' 12:00:00')),
(10027, 1008, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 12 DAY), ' 13:00:00')),
(10028, 1009, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 13 DAY), ' 10:00:00')),
(10029, 1009, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 12 DAY), ' 11:00:00')),
(10030, 1010, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 11 DAY), ' 10:00:00')),
(10031, 1010, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 10 DAY), ' 11:00:00')),
(10032, 1010, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY), ' 12:00:00')),
(10033, 1010, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 8 DAY), ' 13:00:00')),
(10034, 1011, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY), ' 10:00:00')),
(10035, 1011, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 8 DAY), ' 11:00:00')),
(10036, 1011, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:00:00')),
(10037, 1012, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 8 DAY), ' 10:00:00')),
(10038, 1012, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 11:00:00')),
(10039, 1012, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 6 DAY), ' 12:00:00')),
(10040, 1012, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 5 DAY), ' 13:00:00')),
(10041, 1013, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 10:00:00')),
(10042, 1014, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 6 DAY), ' 10:00:00')),
(10043, 1014, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 5 DAY), ' 11:00:00')),
(10044, 1014, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY), ' 12:00:00')),
(10045, 1014, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 3 DAY), ' 13:00:00')),
(10046, 1015, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 5 DAY), ' 10:00:00')),
(10047, 1015, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY), ' 11:00:00')),
(10048, 1016, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY), ' 10:00:00')),
(10049, 1016, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 3 DAY), ' 11:00:00')),
(10050, 1016, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:00:00')),
(10051, 1016, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 13:00:00')),
(10052, 1017, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 3 DAY), ' 10:00:00')),
(10053, 1017, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 11:00:00')),
(10054, 1017, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:00:00')),
(10055, 1018, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 10:00:00')),
(10056, 1018, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 11:00:00')),
(10057, 1018, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:00:00')),
(10058, 1018, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 13:00:00')),
(10059, 1019, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 10:00:00')),
(10060, 1019, 'cancelled', 'Demo status: cancelled', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 11:00:00')),
(10061, 1020, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 10:00:00')),
(10062, 1020, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 11:00:00')),
(10063, 1021, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 10:00:00')),
(10064, 1021, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 11:00:00')),
(10065, 1021, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:00:00')),
(10066, 1021, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 13:00:00')),
(10067, 1022, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 10:00:00')),
(10068, 1023, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 10:00:00')),
(10069, 1023, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 11:00:00')),
(10070, 1023, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:00:00')),
(10071, 1023, 'delivered', 'Demo status: delivered', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 13:00:00')),
(10072, 1024, 'pending', 'Demo status: pending', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 10:00:00')),
(10073, 1024, 'confirmed', 'Demo status: confirmed', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 11:00:00')),
(10074, 1024, 'shipping', 'Demo status: shipping', 1, CONCAT(DATE_SUB(CURDATE(), INTERVAL 0 DAY), ' 12:00:00'));

-- Ghi chú khách hàng để phần customer detail có dữ liệu
INSERT INTO customer_notes (`id`, `user_id`, `admin_id`, `note`, `created_at`) VALUES
(1001, 9, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1002, 10, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1003, 11, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1004, 12, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1005, 13, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1006, 14, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1007, 15, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1008, 16, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1009, 17, 1, 'Demo customer generated for dashboard and report testing.', NOW()),
(1010, 18, 1, 'Demo customer generated for dashboard and report testing.', NOW());

-- Cập nhật lại số lượng đã bán theo dữ liệu đơn hàng thật
UPDATE products p
SET p.sold = COALESCE((
    SELECT SUM(oi.quantity)
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE oi.product_id = p.id
      AND o.status IN ('confirmed', 'shipping', 'delivered')
), 0);

-- Cập nhật used_count coupon theo đơn hàng demo/đơn hàng hiện có
UPDATE coupons c
SET c.used_count = COALESCE((
    SELECT COUNT(*)
    FROM orders o
    WHERE o.coupon_code = c.code
      AND o.status <> 'cancelled'
), 0);

COMMIT;