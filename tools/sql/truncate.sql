-- ============================================================
-- truncate.sql  ★ POWER RESET ★  — 3legant Web Shopping
-- ============================================================
--  Mục đích : Xóa sạch dữ liệu sản phẩm + dữ liệu phụ thuộc,
--             reset AUTO_INCREMENT, sẵn sàng nhận seed mới.
--
--  BẢO TOÀN : users · orders · coupons · settings · banners
--             posts · contacts · logs · notification_subscribers
--
--  XÓA SẠCH :
--    • inventory_logs       (FK → products, product_variants)
--    • order_items          (FK → orders, products, product_variants)
--    • cart                 (FK → products, product_variants)
--    • wishlist             (FK → products)
--    • reviews              (FK → products)
--    • product_variants     (FK → products)
--    • product_images       (FK → products)
--    • products             (FK → categories)
--    • categories           ← reset nếu muốn seed lại từ đầu
--
--  HOW TO USE:
--    1. Mở phpMyAdmin → chọn DB web_shopping
--    2. Tab SQL → copy-paste toàn bộ file này → Go
--    3. Sau khi xong, chạy direct_seed.sql
-- ============================================================

USE `web_shopping`;

-- ── Snapshot TRƯỚC khi xóa ──────────────────────────────────
SELECT '=== BEFORE TRUNCATE ===' AS info;
SELECT
  (SELECT COUNT(*) FROM `categories`)      AS categories,
  (SELECT COUNT(*) FROM `products`)        AS products,
  (SELECT COUNT(*) FROM `product_images`)  AS product_images,
  (SELECT COUNT(*) FROM `product_variants`)AS product_variants,
  (SELECT COUNT(*) FROM `reviews`)         AS reviews,
  (SELECT COUNT(*) FROM `cart`)            AS cart,
  (SELECT COUNT(*) FROM `wishlist`)        AS wishlist,
  (SELECT COUNT(*) FROM `inventory_logs`)  AS inventory_logs,
  (SELECT COUNT(*) FROM `order_items`)     AS order_items;

-- ── Xóa dữ liệu (Dùng DELETE thay vì TRUNCATE để lách luật MySQL) ──
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM `inventory_logs`;
DELETE FROM `order_items`;
DELETE FROM `cart`;
DELETE FROM `wishlist`;
DELETE FROM `reviews`;
DELETE FROM `product_variants`;
DELETE FROM `product_images`;
DELETE FROM `products`;
DELETE FROM `categories`;
SET FOREIGN_KEY_CHECKS = 1;

-- ── Reset AUTO_INCREMENT ─────────────────────────────────────
ALTER TABLE `categories`       AUTO_INCREMENT = 1;
ALTER TABLE `products`         AUTO_INCREMENT = 1;
ALTER TABLE `product_images`   AUTO_INCREMENT = 1;
ALTER TABLE `product_variants` AUTO_INCREMENT = 1;
ALTER TABLE `reviews`          AUTO_INCREMENT = 1;
ALTER TABLE `cart`             AUTO_INCREMENT = 1;
ALTER TABLE `wishlist`         AUTO_INCREMENT = 1;
ALTER TABLE `inventory_logs`   AUTO_INCREMENT = 1;
ALTER TABLE `order_items`      AUTO_INCREMENT = 1;

-- ── Bật lại FK check ────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 1;

-- ── Snapshot SAU khi xóa ────────────────────────────────────
SELECT '=== AFTER TRUNCATE ===' AS info;
SELECT
  (SELECT COUNT(*) FROM `categories`)      AS categories,
  (SELECT COUNT(*) FROM `products`)        AS products,
  (SELECT COUNT(*) FROM `product_images`)  AS product_images,
  (SELECT COUNT(*) FROM `product_variants`)AS product_variants,
  (SELECT COUNT(*) FROM `reviews`)         AS reviews,
  (SELECT COUNT(*) FROM `cart`)            AS cart,
  (SELECT COUNT(*) FROM `wishlist`)        AS wishlist,
  (SELECT COUNT(*) FROM `inventory_logs`)  AS inventory_logs,
  (SELECT COUNT(*) FROM `order_items`)     AS order_items;

SELECT '✅ POWER RESET DONE — Sẵn sàng import direct_seed.sql' AS result;
-- ============================================================
