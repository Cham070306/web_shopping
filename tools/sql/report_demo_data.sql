SET FOREIGN_KEY_CHECKS = 0;

-- Xóa dữ liệu demo cũ nếu đã import trước đó
DELETE oi FROM order_items oi
JOIN orders o ON oi.order_id = o.id
WHERE o.order_code LIKE 'RPT%';

DELETE FROM orders
WHERE order_code LIKE 'RPT%';

DELETE FROM contacts
WHERE email LIKE '%reportdemo.com';

SET FOREIGN_KEY_CHECKS = 1;

-- Lấy user mẫu
SET @demo_user := (
    SELECT id FROM users
    ORDER BY id ASC
    LIMIT 1
);

-- ======================
-- DEMO ORDERS
-- ======================
INSERT INTO orders (
    user_id, order_code, full_name, email, phone, address,
    city, country, subtotal, discount, shipping_fee, total,
    payment_method, payment_status, status, created_at, updated_at, delivered_at
)
VALUES
(@demo_user, 'RPT1001', 'Nguyen Thi Anh', 'anh@reportdemo.com', '0912000001', '12 Le Loi', 'Ho Chi Minh City', 'Vietnam', 82000000, 0, 0, 82000000, 'bank_transfer', 'paid', 'delivered', '2026-04-01 10:00:00', NOW(), '2026-04-03 15:00:00'),
(@demo_user, 'RPT1002', 'User Test', 'user@reportdemo.com', '0987654321', '123 Nguyen Trai', 'Ha Noi', 'Vietnam', 65000000, 0, 0, 65000000, 'cod', 'pending', 'pending', '2026-04-02 11:00:00', NOW(), NULL),
(@demo_user, 'RPT1003', 'Tran Minh Quan', 'quan@reportdemo.com', '0971111111', '22 Pasteur', 'Da Nang', 'Vietnam', 47000000, 0, 0, 47000000, 'momo', 'paid', 'confirmed', '2026-04-03 09:30:00', NOW(), NULL),
(@demo_user, 'RPT1004', 'Le Hoang Nam', 'nam@reportdemo.com', '0934444444', '45 Hai Ba Trung', 'Ha Noi', 'Vietnam', 38500000, 0, 0, 38500000, 'cod', 'paid', 'shipping', '2026-04-04 14:15:00', NOW(), NULL),
(@demo_user, 'RPT1005', 'Pham Gia Han', 'han@reportdemo.com', '0967777777', '91 Nguyen Hue', 'Ho Chi Minh City', 'Vietnam', 29500000, 0, 0, 29500000, 'bank_transfer', 'refunded', 'cancelled', '2026-04-05 13:40:00', NOW(), NULL),

(@demo_user, 'RPT1006', 'Do Ngoc Linh', 'linh@reportdemo.com', '0955555555', '18 Dien Bien Phu', 'Can Tho', 'Vietnam', 76000000, 0, 0, 76000000, 'momo', 'paid', 'delivered', '2026-04-06 08:20:00', NOW(), '2026-04-08 16:20:00'),
(@demo_user, 'RPT1007', 'Bui Thanh Dat', 'dat@reportdemo.com', '0942222222', '30 Tran Phu', 'Da Nang', 'Vietnam', 52000000, 0, 0, 52000000, 'cod', 'pending', 'pending', '2026-04-07 12:00:00', NOW(), NULL),
(@demo_user, 'RPT1008', 'Hoang Mai E', 'mai@reportdemo.com', '0933333333', '68 Vo Van Tan', 'Ho Chi Minh City', 'Vietnam', 90000000, 0, 0, 90000000, 'bank_transfer', 'paid', 'delivered', '2026-04-08 17:00:00', NOW(), '2026-04-10 11:00:00'),
(@demo_user, 'RPT1009', 'Vo Gia Bao', 'bao@reportdemo.com', '0921111111', '88 Cach Mang Thang 8', 'Ha Noi', 'Vietnam', 43000000, 0, 0, 43000000, 'cod', 'paid', 'confirmed', '2026-04-09 10:45:00', NOW(), NULL),
(@demo_user, 'RPT1010', 'Dang Thu Ha', 'ha@reportdemo.com', '0918888888', '77 Ly Thuong Kiet', 'Hue', 'Vietnam', 34000000, 0, 0, 34000000, 'momo', 'paid', 'shipping', '2026-04-10 15:10:00', NOW(), NULL),

(@demo_user, 'RPT1011', 'Nguyen Van Khoa', 'khoa@reportdemo.com', '0901234567', '10 Nguyen Van Cu', 'Ha Noi', 'Vietnam', 58000000, 0, 0, 58000000, 'cod', 'paid', 'delivered', '2026-04-11 10:00:00', NOW(), '2026-04-13 10:00:00'),
(@demo_user, 'RPT1012', 'Tran Bao Ngoc', 'ngoc@reportdemo.com', '0902345678', '15 Le Duan', 'Da Nang', 'Vietnam', 69000000, 0, 0, 69000000, 'bank_transfer', 'paid', 'delivered', '2026-04-12 10:30:00', NOW(), '2026-04-14 10:30:00'),
(@demo_user, 'RPT1013', 'Pham Minh Duc', 'duc@reportdemo.com', '0903456789', '19 Hoang Dieu', 'Hue', 'Vietnam', 31000000, 0, 0, 31000000, 'cod', 'pending', 'pending', '2026-04-13 11:00:00', NOW(), NULL),
(@demo_user, 'RPT1014', 'Le Quynh Anh', 'quynhanh@reportdemo.com', '0904567890', '29 Ngo Quyen', 'Hai Phong', 'Vietnam', 72000000, 0, 0, 72000000, 'momo', 'paid', 'confirmed', '2026-04-14 12:00:00', NOW(), NULL),
(@demo_user, 'RPT1015', 'Mai Thanh Tung', 'tung@reportdemo.com', '0905678901', '35 Phan Chu Trinh', 'Da Lat', 'Vietnam', 56000000, 0, 0, 56000000, 'bank_transfer', 'paid', 'shipping', '2026-04-15 13:00:00', NOW(), NULL);

-- ======================
-- DEMO ORDER ITEMS THEO CATEGORY
-- Tạo chênh lệch: Sofas 500, Chairs 435, Tables 200, Lighting 175, Textiles 80, Decor 50
-- ======================

INSERT INTO order_items (
    order_id, product_id, product_name, product_sku, price, quantity, subtotal, thumbnail
)
SELECT 
    o.id,
    p.id,
    p.name,
    p.sku,
    p.price,
    x.quantity,
    p.price * x.quantity,
    p.thumbnail
FROM (
    SELECT 'RPT1001' AS order_code, 'Sofas' AS category_name, 500 AS quantity
    UNION ALL SELECT 'RPT1002', 'Chairs', 435
    UNION ALL SELECT 'RPT1003', 'Tables', 200
    UNION ALL SELECT 'RPT1004', 'Lighting', 175
    UNION ALL SELECT 'RPT1005', 'Textiles', 80
    UNION ALL SELECT 'RPT1006', 'Decor', 50
) x
JOIN orders o ON o.order_code = x.order_code
JOIN categories c ON c.name = x.category_name
JOIN (
    SELECT p1.*
    FROM products p1
    JOIN (
        SELECT category_id, MIN(id) AS min_id
        FROM products
        GROUP BY category_id
    ) pick ON pick.min_id = p1.id
) p ON p.category_id = c.id;

-- ======================
-- DEMO CONTACTS
-- ======================
INSERT INTO contacts (name, email, phone, subject, message, is_read, created_at)
VALUES
('Nguyen Minh Anh', 'minhanh@reportdemo.com', '0911111111', 'Shipping question', 'I want to ask about delivery time.', 0, NOW()),
('Tran Hoang Nam', 'nam@reportdemo.com', '0922222222', 'Product support', 'Can you help me choose a sofa?', 1, NOW()),
('Le Thu Ha', 'ha@reportdemo.com', '0933333333', 'Return policy', 'I want to know the return policy.', 0, NOW()),
('Pham Gia Bao', 'bao@reportdemo.com', '0944444444', 'Payment issue', 'My payment was not confirmed.', 0, NOW()),
('Do Ngoc Linh', 'linh@reportdemo.com', '0955555555', 'Order status', 'Please check my order status.', 1, NOW());

-- ======================
-- DEMO REVIEWS AN TOÀN THEO PRODUCT THẬT
-- ======================
INSERT INTO reviews (product_id, user_id, rating, comment, created_at)
SELECT p.id, @demo_user, 5, 'Sản phẩm rất đẹp và chắc chắn.', NOW()
FROM products p
ORDER BY p.id ASC
LIMIT 1
ON DUPLICATE KEY UPDATE comment = VALUES(comment), rating = VALUES(rating);

INSERT INTO reviews (product_id, user_id, rating, comment, created_at)
SELECT p.id, @demo_user, 4, 'Chất lượng tốt, giao hàng nhanh.', NOW()
FROM products p
ORDER BY p.id ASC
LIMIT 1 OFFSET 1
ON DUPLICATE KEY UPDATE comment = VALUES(comment), rating = VALUES(rating);

INSERT INTO reviews (product_id, user_id, rating, comment, created_at)
SELECT p.id, @demo_user, 5, 'Rất đáng tiền.', NOW()
FROM products p
ORDER BY p.id ASC
LIMIT 1 OFFSET 2
ON DUPLICATE KEY UPDATE comment = VALUES(comment), rating = VALUES(rating);

INSERT INTO reviews (product_id, user_id, rating, comment, created_at)
SELECT p.id, @demo_user, 4, 'Thiết kế hiện đại.', NOW()
FROM products p
ORDER BY p.id ASC
LIMIT 1 OFFSET 3
ON DUPLICATE KEY UPDATE comment = VALUES(comment), rating = VALUES(rating);

INSERT INTO reviews (product_id, user_id, rating, comment, created_at)
SELECT p.id, @demo_user, 5, 'Sẽ mua lại lần sau.', NOW()
FROM products p
ORDER BY p.id ASC
LIMIT 1 OFFSET 4
ON DUPLICATE KEY UPDATE comment = VALUES(comment), rating = VALUES(rating);