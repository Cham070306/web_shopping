-- =========================================================
-- WEB_SHOPPING - TEAM READY DATABASE
-- Compatible: MySQL 8.0+ / XAMPP / phpMyAdmin
-- Purpose: import 1 file and run immediately
-- Spec source: web_shopping_design_spec.docx
-- =========================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `web_shopping`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `web_shopping`;

-- =========================================================
-- DROP OLD TABLES (safe re-import for team dev)
-- =========================================================
DROP TABLE IF EXISTS `logs`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `banners`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `post_categories`;
DROP TABLE IF EXISTS `inventory_logs`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `order_status_logs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `product_variants`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `user_addresses`;
DROP TABLE IF EXISTS `users`;

-- =========================================================
-- USERS
-- =========================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `province` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) NOT NULL DEFAULT 'Vietnam',
  `zip_code` VARCHAR(20) DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `type` ENUM('billing','shipping') NOT NULL DEFAULT 'shipping',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_addresses_user` (`user_id`),
  INDEX `idx_user_addresses_default` (`user_id`, `is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- CATEGORIES / PRODUCTS
-- =========================================================
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_categories_parent` (`parent_id`),
  INDEX `idx_categories_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `sku` VARCHAR(80) DEFAULT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `short_desc` VARCHAR(500) DEFAULT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `sale_price` DECIMAL(12,2) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `sold` INT NOT NULL DEFAULT 0,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_products_category` (`category_id`),
  INDEX `idx_products_active_featured` (`is_active`, `is_featured`),
  INDEX `idx_products_price` (`price`),
  INDEX `idx_products_sale_price` (`sale_price`),
  INDEX `idx_products_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product_images_product` (`product_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_variants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `size` VARCHAR(20) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `price_diff` DECIMAL(10,2) NOT NULL DEFAULT 0,
  CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product_variants_product` (`product_id`),
  INDEX `idx_product_variants_combo` (`product_id`, `color`, `size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT DEFAULT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5),
  UNIQUE KEY `uq_reviews_user_product` (`product_id`, `user_id`),
  INDEX `idx_reviews_product` (`product_id`, `is_approved`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- ORDERS / CART / WISHLIST / PAYMENT
-- =========================================================
CREATE TABLE `coupons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` DECIMAL(10,2) NOT NULL,
  `min_order` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `max_uses` INT NOT NULL DEFAULT 0,
  `used_count` INT NOT NULL DEFAULT 0,
  `expires_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_coupons_active_expire` (`is_active`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `order_code` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `province` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) NOT NULL DEFAULT 'Vietnam',
  `zip_code` VARCHAR(20) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `discount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total` DECIMAL(12,2) NOT NULL,
  `coupon_code` VARCHAR(50) DEFAULT NULL,
  `payment_method` ENUM('cod','bank_transfer','momo') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `status` ENUM('pending','confirmed','shipping','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_orders_user` (`user_id`),
  INDEX `idx_orders_status` (`status`),
  INDEX `idx_orders_payment_status` (`payment_status`),
  INDEX `idx_orders_created_at` (`created_at`),
  INDEX `idx_orders_order_code` (`order_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `variant_id` INT UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `variant` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `quantity` INT NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
  INDEX `idx_order_items_order` (`order_id`),
  INDEX `idx_order_items_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `method` ENUM('cod','bank_transfer','momo') NOT NULL DEFAULT 'cod',
  `amount` DECIMAL(12,2) NOT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_payments_transaction` (`transaction_id`),
  INDEX `idx_payments_order` (`order_id`),
  INDEX `idx_payments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_status_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `status` ENUM('pending','confirmed','shipping','delivered','cancelled') NOT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `changed_by` INT UNSIGNED DEFAULT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_order_status_logs_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_status_logs_user` FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_order_status_logs_order` (`order_id`, `changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cart` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `variant_id` INT UNSIGNED DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
  INDEX `idx_cart_user` (`user_id`),
  INDEX `idx_cart_session` (`session_id`),
  INDEX `idx_cart_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wishlist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_wishlist_user_product` (`user_id`, `product_id`),
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_wishlist_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventory_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `variant_id` INT UNSIGNED DEFAULT NULL,
  `change_qty` INT NOT NULL,
  `type` ENUM('import','order','adjust') NOT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_inventory_logs_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inventory_logs_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inventory_logs_user` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_inventory_logs_product` (`product_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- BLOG / CONTACT / BANNER / SETTINGS
-- =========================================================
CREATE TABLE `post_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `author_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(250) NOT NULL,
  `slug` VARCHAR(270) NOT NULL UNIQUE,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  `excerpt` VARCHAR(500) DEFAULT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `views` INT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `post_categories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_posts_category` (`category_id`),
  INDEX `idx_posts_author` (`author_id`),
  INDEX `idx_posts_publish` (`is_published`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contacts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_contacts_read` (`is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) DEFAULT NULL,
  `subtitle` VARCHAR(300) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_banners_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Extra utility table for admin/system activity log.
CREATE TABLE `logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_logs_user` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- SEED DATA
-- Test accounts required by spec:
-- Admin: admin@shop.com / Admin@123
-- User : user@shop.com  / User@123
-- =========================================================
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `role`, `is_active`) VALUES
(1, 'Admin', 'admin@shop.com', '$2y$12$uZal/hZLC2x6j.IJtRic3uBApW4ASimfP/AJ1gm2f8kxlAZ1vTME.', '0123456789', 'default.jpg', 'admin', 1),
(2, 'User Test', 'user@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0987654321', 'default.jpg', 'user', 1);

INSERT INTO `user_addresses` (`user_id`, `full_name`, `phone`, `address`, `city`, `province`, `country`, `zip_code`, `is_default`, `type`) VALUES
(2, 'User Test', '0987654321', '123 Nguyen Trai', 'Ha Noi', 'Thanh Xuan', 'Vietnam', '100000', 1, 'shipping'),
(2, 'User Test', '0987654321', '123 Nguyen Trai', 'Ha Noi', 'Thanh Xuan', 'Vietnam', '100000', 0, 'billing');

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `image`, `description`, `sort_order`, `is_active`) VALUES
(1, 'Living Room', 'living-room', NULL, 'living-room.jpg', 'Furniture for living room', 1, 1),
(2, 'Bedroom', 'bedroom', NULL, 'bedroom.jpg', 'Furniture for bedroom', 2, 1),
(3, 'Kitchen', 'kitchen', NULL, 'kitchen.jpg', 'Kitchen decor and furniture', 3, 1),
(4, 'Decor', 'decor', NULL, 'decor.jpg', 'Decor accessories', 4, 1);

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `description`, `short_desc`, `price`, `sale_price`, `stock`, `sold`, `thumbnail`, `meta_title`, `meta_description`, `is_featured`, `is_active`) VALUES
(1, 1, 'Tray Table', 'tray-table', 'TT-001', 'Light and easy tray table for living room.', 'Minimal round tray table.', 199.00, 159.00, 50, 10, 'tray-table.jpg', 'Tray Table', 'Minimal round tray table for modern living room.', 1, 1),
(2, 1, 'Loveseat Sofa', 'loveseat-sofa', 'LS-001', 'Comfortable loveseat sofa for modern space.', 'Soft and cozy loveseat.', 399.00, 299.00, 20, 5, 'loveseat-sofa.jpg', 'Loveseat Sofa', 'Comfortable loveseat sofa for modern living room.', 1, 1),
(3, 4, 'Bamboo Basket', 'bamboo-basket', 'BB-001', 'Handmade bamboo basket for decoration and storage.', 'Natural bamboo decor basket.', 39.00, NULL, 100, 30, 'bamboo-basket.jpg', 'Bamboo Basket', 'Handmade bamboo basket for storage and decor.', 0, 1),
(4, 4, 'Table Lamp', 'table-lamp', 'TL-001', 'Elegant glass table lamp.', 'Warm light glass lamp.', 89.00, NULL, 35, 8, 'table-lamp.jpg', 'Table Lamp', 'Warm light glass lamp for bedroom and living room.', 0, 1);

INSERT INTO `product_images` (`product_id`, `image_url`, `sort_order`) VALUES
(1, 'tray-table-1.jpg', 1),
(1, 'tray-table-2.jpg', 2),
(1, 'tray-table-3.jpg', 3),
(2, 'loveseat-sofa-1.jpg', 1),
(3, 'bamboo-basket-1.jpg', 1),
(4, 'table-lamp-1.jpg', 1);

INSERT INTO `product_variants` (`id`, `product_id`, `color`, `size`, `stock`, `price_diff`) VALUES
(1, 1, 'Black', NULL, 20, 0.00),
(2, 1, 'Red', NULL, 10, 0.00),
(3, 2, 'Gray', NULL, 10, 0.00),
(4, 2, 'Beige', NULL, 10, 20.00);

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `comment`, `is_approved`) VALUES
(1, 2, 5, 'Very nice table, minimalist and sturdy.', 1),
(2, 2, 4, 'Comfortable sofa and good value for the sale price.', 1),
(4, 2, 5, 'Lamp looks premium and gives warm light.', 1);

INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `max_uses`, `used_count`, `expires_at`, `is_active`) VALUES
('WELCOME10', 'percent', 10.00, 100.00, 100, 0, '2027-12-31 23:59:59', 1),
('SAVE50', 'fixed', 50.00, 300.00, 50, 1, '2027-12-31 23:59:59', 1);

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `full_name`, `email`, `phone`, `address`, `city`, `province`, `country`, `zip_code`, `note`, `subtotal`, `discount`, `shipping_fee`, `total`, `coupon_code`, `payment_method`, `payment_status`, `status`) VALUES
(1, 2, 'ORD-00001', 'User Test', 'user@shop.com', '0987654321', '123 Nguyen Trai', 'Ha Noi', 'Thanh Xuan', 'Vietnam', '100000', 'Please call before delivery', 617.00, 50.00, 0.00, 567.00, 'SAVE50', 'cod', 'pending', 'confirmed');

INSERT INTO `order_items` (`order_id`, `product_id`, `variant_id`, `product_name`, `variant`, `price`, `quantity`, `subtotal`, `thumbnail`) VALUES
(1, 1, 1, 'Tray Table', 'Black', 159.00, 2, 318.00, 'tray-table.jpg'),
(1, 2, 3, 'Loveseat Sofa', 'Gray', 299.00, 1, 299.00, 'loveseat-sofa.jpg');

INSERT INTO `payments` (`order_id`, `method`, `amount`, `transaction_id`, `status`, `paid_at`) VALUES
(1, 'cod', 567.00, NULL, 'pending', NULL);

INSERT INTO `order_status_logs` (`order_id`, `status`, `note`, `changed_by`) VALUES
(1, 'pending', 'Order created successfully', 2),
(1, 'confirmed', 'Admin confirmed order', 1);

INSERT INTO `wishlist` (`user_id`, `product_id`) VALUES
(2, 1),
(2, 3);

INSERT INTO `inventory_logs` (`product_id`, `variant_id`, `change_qty`, `type`, `note`, `created_by`) VALUES
(1, 1, 20, 'import', 'Initial stock import for black variant', 1),
(1, 1, -2, 'order', 'Sold by order ORD-00001', 1),
(2, 3, 10, 'import', 'Initial stock import for gray variant', 1),
(2, 3, -1, 'order', 'Sold by order ORD-00001', 1);

INSERT INTO `post_categories` (`id`, `name`, `slug`) VALUES
(1, 'Design Tips', 'design-tips'),
(2, 'Interior Inspiration', 'interior-inspiration'),
(3, 'Decor Guide', 'decor-guide');

INSERT INTO `posts` (`category_id`, `author_id`, `title`, `slug`, `thumbnail`, `excerpt`, `content`, `views`, `is_published`, `published_at`) VALUES
(1, 1, '7 ways to decor your home like a professional', '7-ways-to-decor-your-home-like-a-professional', 'post-1.jpg', 'Ideas to make your home more elegant and warm.', 'Sample blog content for design tips.', 25, 1, '2026-03-24 10:00:00'),
(2, 1, 'Inside a beautiful kitchen organization', 'inside-a-beautiful-kitchen-organization', 'post-2.jpg', 'Kitchen organization ideas for modern homes.', 'Sample blog content for kitchen organization.', 18, 1, '2026-03-24 10:10:00'),
(3, 1, 'Decor your bedroom for your children', 'decor-your-bedroom-for-your-children', 'post-3.jpg', 'Easy tips to arrange a peaceful bedroom.', 'Sample blog content for bedroom decor.', 12, 1, '2026-03-24 10:20:00');

INSERT INTO `contacts` (`name`, `email`, `phone`, `subject`, `message`, `is_read`) VALUES
('Nguyen Van A', 'a@gmail.com', '0912345678', 'Need support', 'I want to ask about shipping policy.', 0);

INSERT INTO `banners` (`title`, `subtitle`, `image`, `link`, `sort_order`, `is_active`) VALUES
('Simply Unique / Simply Better.', 'Discover our elegant furniture collection', '/assets/images/banner-home-1.jpg', '/user/shop.php', 1, 1),
('HUNDREDS of New lower prices!', 'It is more affordable than ever', '/assets/images/banner-home-2.jpg', '/user/shop.php', 2, 1);

INSERT INTO `settings` (`key`, `value`) VALUES
('site_name', '3legant Store'),
('hotline', '0123 456 789'),
('email', 'hello@3legant.com'),
('address', 'Ha Noi, Vietnam'),
('facebook', 'https://facebook.com/3legant'),
('instagram', 'https://instagram.com/3legant'),
('youtube', 'https://youtube.com/@3legant'),
('logo', 'logo.png'),
('footer_text', 'Gift & Decoration Store');

INSERT INTO `logs` (`user_id`, `action`) VALUES
(1, 'Initialized database and sample data'),
(2, 'Created first sample order ORD-00001');

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- END OF FILE
-- =========================================================
