-- ============================================================
-- truncate.sql — 3legant Web Shopping
-- ============================================================
-- Xóa sạch dữ liệu sản phẩm để import lại từ crawler.
-- KHÔNG xóa users, orders, categories.
--
-- HOW TO USE:
--   Chạy file này TRƯỚC khi import direct_seed.sql
--   để tránh trùng ID và conflict foreign key.
--
-- CẢNH BÁO: Sẽ xóa sạch:
--   - wishlist, product_variants, product_images,
--     order_items (chỉ xóa liên kết SP), products
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `wishlist`;
TRUNCATE TABLE `product_variants`;
TRUNCATE TABLE `product_images`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `products`;

SET FOREIGN_KEY_CHECKS = 1;

-- Kiểm tra sau khi truncate
SELECT
  (SELECT COUNT(*) FROM products)         AS products_count,
  (SELECT COUNT(*) FROM product_images)   AS images_count,
  (SELECT COUNT(*) FROM product_variants) AS variants_count;
