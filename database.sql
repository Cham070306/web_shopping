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
DROP TABLE IF EXISTS `password_reset_otps`;
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

DROP TABLE IF EXISTS `password_reset_otps`;

CREATE TABLE `password_reset_otps` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `attempt_count` INT NOT NULL DEFAULT 0,
  `max_attempts` INT NOT NULL DEFAULT 5,
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `verified_at` DATETIME DEFAULT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_password_reset_otps_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_reset_email` (`email`),
  INDEX `idx_reset_otp` (`otp_code`),
  INDEX `idx_reset_expires` (`expires_at`)
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


-- =========================================================
-- EXTRA ADMIN DEMO SEED DATA
-- Added for richer dashboard / admin testing
-- =========================================================

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `role`, `is_active`) VALUES
(3, 'Nguyen Thi Anh', 'anh.nguyen@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000001', 'default.jpg', 'user', 1),
(4, 'Tran Van Binh', 'binh.tran@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000002', 'default.jpg', 'user', 1),
(5, 'Le Hoang Nam', 'nam.le@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000003', 'default.jpg', 'user', 1),
(6, 'Pham Thu Ha', 'ha.pham@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000004', 'default.jpg', 'user', 1),
(7, 'Vo Minh Khoa', 'khoa.vo@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000005', 'default.jpg', 'user', 1),
(8, 'Bui Mai Lan', 'lan.bui@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000006', 'default.jpg', 'user', 1),
(9, 'Do Quoc Viet', 'viet.do@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000007', 'default.jpg', 'user', 1),
(10, 'Dang Ngoc Linh', 'linh.dang@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000008', 'default.jpg', 'user', 1),
(11, 'Hoang Gia Bao', 'bao.hoang@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000009', 'default.jpg', 'user', 1),
(12, 'Nguyen Kim Oanh', 'oanh.nguyen@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000010', 'default.jpg', 'user', 1),
(13, 'Phan Duc Huy', 'huy.phan@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000011', 'default.jpg', 'user', 1),
(14, 'Ly Thao My', 'my.ly@shop.com', '$2y$12$DYF1lAsG9qcgUePeLkjCE.82zb7JoPRCbTBz/yua6Jz1.Bolg5zdu', '0912000012', 'default.jpg', 'user', 1),
(15, 'Admin Support', 'support-admin@shop.com', '$2y$12$uZal/hZLC2x6j.IJtRic3uBApW4ASimfP/AJ1gm2f8kxlAZ1vTME.', '0912000099', 'default.jpg', 'admin', 1);

INSERT INTO `user_addresses` (`user_id`, `full_name`, `phone`, `address`, `city`, `province`, `country`, `zip_code`, `is_default`, `type`) VALUES
(3, 'Nguyen Thi Anh', '0912000001', '12 Le Loi', 'Ho Chi Minh City', 'District 1', 'Vietnam', '700000', 1, 'shipping'),
(4, 'Tran Van Binh', '0912000002', '45 Tran Hung Dao', 'Da Nang', 'Hai Chau', 'Vietnam', '550000', 1, 'shipping'),
(5, 'Le Hoang Nam', '0912000003', '88 Nguyen Van Cu', 'Can Tho', 'Ninh Kieu', 'Vietnam', '900000', 1, 'shipping'),
(6, 'Pham Thu Ha', '0912000004', '22 Xuan Thuy', 'Ha Noi', 'Cau Giay', 'Vietnam', '100000', 1, 'shipping'),
(7, 'Vo Minh Khoa', '0912000005', '136 Vo Thi Sau', 'Ho Chi Minh City', 'District 3', 'Vietnam', '700000', 1, 'shipping'),
(8, 'Bui Mai Lan', '0912000006', '17 Hai Ba Trung', 'Hai Phong', 'Hong Bang', 'Vietnam', '180000', 1, 'shipping'),
(9, 'Do Quoc Viet', '0912000007', '65 Quang Trung', 'Ha Noi', 'Ha Dong', 'Vietnam', '100000', 1, 'shipping'),
(10, 'Dang Ngoc Linh', '0912000008', '9 Nguyen Hue', 'Hue', 'Phu Hoi', 'Vietnam', '530000', 1, 'shipping'),
(11, 'Hoang Gia Bao', '0912000009', '55 Cach Mang Thang 8', 'Ho Chi Minh City', 'District 10', 'Vietnam', '700000', 1, 'shipping'),
(12, 'Nguyen Kim Oanh', '0912000010', '301 Le Duan', 'Da Nang', 'Thanh Khe', 'Vietnam', '550000', 1, 'shipping'),
(13, 'Phan Duc Huy', '0912000011', '76 Pham Ngu Lao', 'Ha Noi', 'Dong Da', 'Vietnam', '100000', 1, 'shipping'),
(14, 'Ly Thao My', '0912000012', '150 Bach Dang', 'Da Nang', 'Son Tra', 'Vietnam', '550000', 1, 'shipping');

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `image`, `description`, `sort_order`, `is_active`) VALUES
(5, 'Dining Room', 'dining-room', NULL, 'dining-room.jpg', 'Dining tables and chairs', 5, 1),
(6, 'Office', 'office', NULL, 'office.jpg', 'Workspace furniture and lighting', 6, 1),
(7, 'Outdoor', 'outdoor', NULL, 'outdoor.jpg', 'Outdoor living essentials', 7, 1),
(8, 'Storage', 'storage', NULL, 'storage.jpg', 'Shelves and storage solutions', 8, 1);

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `description`, `short_desc`, `price`, `sale_price`, `stock`, `sold`, `thumbnail`, `meta_title`, `meta_description`, `is_featured`, `is_active`) VALUES
(5, 2, 'Oak Bed Frame', 'oak-bed-frame', 'BF-001', 'Solid oak bed frame with simple Scandinavian lines.', 'Queen-size oak bed frame.', 520.00, 459.00, 16, 14, 'oak-bed-frame.jpg', 'Oak Bed Frame', 'Solid oak bed frame for minimal bedroom.', 1, 1),
(6, 2, 'Cloud Nightstand', 'cloud-nightstand', 'NS-001', 'Compact nightstand with drawer and soft edges.', 'Minimal bedside table.', 129.00, 99.00, 28, 31, 'cloud-nightstand.jpg', 'Cloud Nightstand', 'Compact and elegant nightstand.', 0, 1),
(7, 5, 'Nordic Dining Chair', 'nordic-dining-chair', 'DC-001', 'Upholstered dining chair with ash wood legs.', 'Comfortable dining chair.', 149.00, 119.00, 42, 52, 'nordic-dining-chair.jpg', 'Nordic Dining Chair', 'Ash wood dining chair.', 1, 1),
(8, 5, 'Round Dining Table', 'round-dining-table', 'DT-001', 'Round dining table for four people.', 'Elegant round dining table.', 420.00, 379.00, 11, 18, 'round-dining-table.jpg', 'Round Dining Table', 'Round dining table for family meals.', 1, 1),
(9, 6, 'Ergo Office Chair', 'ergo-office-chair', 'OC-001', 'Ergonomic office chair with breathable mesh.', 'Ergonomic mesh chair.', 259.00, 219.00, 26, 33, 'ergo-office-chair.jpg', 'Ergo Office Chair', 'Ergonomic office chair.', 1, 1),
(10, 6, 'Walnut Desk', 'walnut-desk', 'DK-001', 'Walnut desk with storage drawer.', 'Work desk for modern office.', 349.00, NULL, 14, 21, 'walnut-desk.jpg', 'Walnut Desk', 'Walnut desk for workspace.', 0, 1),
(11, 8, 'Open Shelf Rack', 'open-shelf-rack', 'SR-001', 'Five-tier shelf rack for decor and books.', 'Open storage shelf.', 189.00, 159.00, 19, 24, 'open-shelf-rack.jpg', 'Open Shelf Rack', 'Five-tier open shelf.', 0, 1),
(12, 8, 'Drawer Cabinet', 'drawer-cabinet', 'CB-001', 'Cabinet with six drawers for home storage.', 'Large drawer cabinet.', 279.00, 239.00, 9, 16, 'drawer-cabinet.jpg', 'Drawer Cabinet', 'Storage cabinet for bedroom or living room.', 0, 1),
(13, 4, 'Ceramic Vase Set', 'ceramic-vase-set', 'VS-001', 'Set of three matte ceramic vases.', 'Modern decor vase set.', 59.00, 45.00, 64, 71, 'ceramic-vase-set.jpg', 'Ceramic Vase Set', 'Set of decorative ceramic vases.', 0, 1),
(14, 4, 'Wall Art Canvas', 'wall-art-canvas', 'WA-001', 'Abstract wall art canvas in neutral tones.', 'Neutral wall art.', 99.00, NULL, 37, 43, 'wall-art-canvas.jpg', 'Wall Art Canvas', 'Abstract canvas for living room.', 0, 1),
(15, 7, 'Patio Lounge Chair', 'patio-lounge-chair', 'PL-001', 'Outdoor lounge chair made with weather-resistant material.', 'Outdoor lounge seat.', 229.00, 199.00, 12, 8, 'patio-lounge-chair.jpg', 'Patio Lounge Chair', 'Outdoor lounge chair.', 0, 1),
(16, 7, 'Garden Side Table', 'garden-side-table', 'GT-001', 'Compact garden side table for balconies and patios.', 'Outdoor side table.', 89.00, 69.00, 22, 15, 'garden-side-table.jpg', 'Garden Side Table', 'Compact outdoor table.', 0, 1),
(17, 3, 'Stoneware Bowl Set', 'stoneware-bowl-set', 'BW-001', 'Set of four stoneware bowls.', 'Kitchen bowl set.', 49.00, 39.00, 48, 61, 'stoneware-bowl-set.jpg', 'Stoneware Bowl Set', 'Stoneware bowl set for kitchen.', 0, 1),
(18, 3, 'Kitchen Trolley', 'kitchen-trolley', 'KT-001', 'Wood and steel trolley for kitchen storage.', 'Mobile kitchen trolley.', 199.00, 169.00, 15, 18, 'kitchen-trolley.jpg', 'Kitchen Trolley', 'Kitchen storage trolley.', 0, 1),
(19, 2, 'Soft Cotton Blanket', 'soft-cotton-blanket', 'BL-001', 'Warm cotton blanket for all-season comfort.', 'Soft bedroom blanket.', 79.00, 59.00, 73, 80, 'soft-cotton-blanket.jpg', 'Soft Cotton Blanket', 'Soft cotton blanket.', 1, 1),
(20, 1, 'Accent Armchair', 'accent-armchair', 'AC-001', 'Accent armchair with curved backrest.', 'Statement armchair.', 329.00, 289.00, 8, 19, 'accent-armchair.jpg', 'Accent Armchair', 'Accent armchair for living room.', 1, 1),
(21, 1, 'Minimal TV Stand', 'minimal-tv-stand', 'TV-001', 'Low-profile TV stand with hidden storage.', 'TV console with storage.', 269.00, 229.00, 13, 23, 'minimal-tv-stand.jpg', 'Minimal TV Stand', 'TV stand with storage.', 0, 1),
(22, 6, 'Desk Lamp Pro', 'desk-lamp-pro', 'DL-001', 'Adjustable task lamp for focused work.', 'Adjustable desk lamp.', 69.00, 55.00, 34, 39, 'desk-lamp-pro.jpg', 'Desk Lamp Pro', 'Desk lamp for office.', 0, 1),
(23, 8, 'Woven Storage Box', 'woven-storage-box', 'SB-001', 'Woven box for shelves and closets.', 'Storage box set.', 35.00, 29.00, 90, 112, 'woven-storage-box.jpg', 'Woven Storage Box', 'Woven storage box.', 0, 1),
(24, 4, 'Scented Candle Trio', 'scented-candle-trio', 'CD-001', 'Three scented candles in glass jars.', 'Decor candle trio.', 45.00, 35.00, 58, 65, 'scented-candle-trio.jpg', 'Scented Candle Trio', 'Set of scented candles.', 0, 1);

INSERT INTO `product_images` (`product_id`, `image_url`, `sort_order`) VALUES
(5, 'oak-bed-frame-1.jpg', 1),
(5, 'oak-bed-frame-2.jpg', 2),
(6, 'cloud-nightstand-1.jpg', 1),
(7, 'nordic-dining-chair-1.jpg', 1),
(8, 'round-dining-table-1.jpg', 1),
(9, 'ergo-office-chair-1.jpg', 1),
(10, 'walnut-desk-1.jpg', 1),
(11, 'open-shelf-rack-1.jpg', 1),
(12, 'drawer-cabinet-1.jpg', 1),
(13, 'ceramic-vase-set-1.jpg', 1),
(14, 'wall-art-canvas-1.jpg', 1),
(15, 'patio-lounge-chair-1.jpg', 1),
(16, 'garden-side-table-1.jpg', 1),
(17, 'stoneware-bowl-set-1.jpg', 1),
(18, 'kitchen-trolley-1.jpg', 1),
(19, 'soft-cotton-blanket-1.jpg', 1),
(20, 'accent-armchair-1.jpg', 1),
(21, 'minimal-tv-stand-1.jpg', 1),
(22, 'desk-lamp-pro-1.jpg', 1),
(23, 'woven-storage-box-1.jpg', 1),
(24, 'scented-candle-trio-1.jpg', 1);

INSERT INTO `product_variants` (`id`, `product_id`, `color`, `size`, `stock`, `price_diff`) VALUES
(5, 5, 'Oak', 'Queen', 8, 0.00),
(6, 5, 'Walnut', 'Queen', 8, 20.00),
(7, 7, 'Gray', NULL, 20, 0.00),
(8, 7, 'Cream', NULL, 22, 10.00),
(9, 9, 'Black', NULL, 12, 0.00),
(10, 9, 'White', NULL, 14, 0.00),
(11, 19, 'Beige', 'M', 25, 0.00),
(12, 19, 'White', 'L', 20, 10.00),
(13, 20, 'Olive', NULL, 4, 0.00),
(14, 20, 'Brown', NULL, 4, 15.00);

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `comment`, `is_approved`) VALUES
(5, 3, 5, 'The frame feels sturdy and the finish is beautiful.', 1),
(6, 4, 4, 'Good value and easy to match with other furniture.', 1),
(7, 5, 5, 'Comfortable chair, good for long family dinners.', 1),
(8, 6, 4, 'Looks elegant in my apartment dining area.', 1),
(9, 7, 5, 'Very comfortable for daily office use.', 1),
(10, 8, 4, 'Desk size is perfect for a small workspace.', 1),
(13, 9, 5, 'The vase set makes my shelf look more premium.', 1),
(19, 10, 5, 'Blanket is soft and warm, very worth the price.', 1),
(20, 11, 4, 'Beautiful accent chair but delivery took a bit longer.', 1),
(21, 12, 5, 'TV stand is clean, minimal, and easy to install.', 1),
(22, 13, 4, 'Lamp brightness is good for work and studying.', 1),
(24, 14, 5, 'Nice scent and elegant packaging.', 1);

INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `max_uses`, `used_count`, `expires_at`, `is_active`) VALUES
('SPRING15', 'percent', 15.00, 150.00, 200, 12, '2026-06-30 23:59:59', 1),
('HOME25', 'fixed', 25.00, 200.00, 150, 28, '2026-12-31 23:59:59', 1),
('VIP20', 'percent', 20.00, 500.00, 50, 9, '2026-10-31 23:59:59', 1),
('FREESHIP', 'fixed', 15.00, 120.00, 500, 40, '2026-09-30 23:59:59', 1),
('OLDSALE', 'percent', 10.00, 100.00, 100, 100, '2025-12-31 23:59:59', 0);

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `full_name`, `email`, `phone`, `address`, `city`, `province`, `country`, `zip_code`, `note`, `subtotal`, `discount`, `shipping_fee`, `total`, `coupon_code`, `payment_method`, `payment_status`, `status`, `created_at`, `updated_at`) VALUES
(2, 3, 'ORD-00002', 'Nguyen Thi Anh', 'anh.nguyen@shop.com', '0912000001', '12 Le Loi', 'Ho Chi Minh City', 'District 1', 'Vietnam', '700000', 'Leave at reception', 478.00, 15.00, 15.00, 478.00, 'FREESHIP', 'momo', 'paid', 'delivered', '2026-03-02 09:15:00', '2026-03-05 14:30:00'),
(3, 4, 'ORD-00003', 'Tran Van Binh', 'binh.tran@shop.com', '0912000002', '45 Tran Hung Dao', 'Da Nang', 'Hai Chau', 'Vietnam', '550000', 'Call before delivery', 408.00, 0.00, 15.00, 423.00, NULL, 'cod', 'pending', 'pending', '2026-03-04 11:30:00', '2026-03-04 11:30:00'),
(4, 5, 'ORD-00004', 'Le Hoang Nam', 'nam.le@shop.com', '0912000003', '88 Nguyen Van Cu', 'Can Tho', 'Ninh Kieu', 'Vietnam', '900000', 'Office hour delivery', 298.00, 25.00, 15.00, 288.00, 'HOME25', 'bank_transfer', 'paid', 'confirmed', '2026-03-06 15:00:00', '2026-03-07 08:00:00'),
(5, 6, 'ORD-00005', 'Pham Thu Ha', 'ha.pham@shop.com', '0912000004', '22 Xuan Thuy', 'Ha Noi', 'Cau Giay', 'Vietnam', '100000', 'Gift wrap please', 614.00, 61.40, 15.00, 567.60, 'WELCOME10', 'momo', 'paid', 'shipping', '2026-03-08 19:20:00', '2026-03-09 09:10:00'),
(6, 7, 'ORD-00006', 'Vo Minh Khoa', 'khoa.vo@shop.com', '0912000005', '136 Vo Thi Sau', 'Ho Chi Minh City', 'District 3', 'Vietnam', '700000', NULL, 129.00, 0.00, 15.00, 144.00, NULL, 'cod', 'pending', 'cancelled', '2026-03-10 10:05:00', '2026-03-10 13:30:00'),
(7, 8, 'ORD-00007', 'Bui Mai Lan', 'lan.bui@shop.com', '0912000006', '17 Hai Ba Trung', 'Hai Phong', 'Hong Bang', 'Vietnam', '180000', NULL, 418.00, 15.00, 15.00, 418.00, 'FREESHIP', 'bank_transfer', 'paid', 'delivered', '2026-03-11 16:40:00', '2026-03-14 17:25:00'),
(8, 9, 'ORD-00008', 'Do Quoc Viet', 'viet.do@shop.com', '0912000007', '65 Quang Trung', 'Ha Noi', 'Ha Dong', 'Vietnam', '100000', NULL, 505.00, 50.00, 0.00, 455.00, 'SAVE50', 'cod', 'pending', 'confirmed', '2026-03-12 09:50:00', '2026-03-12 12:00:00'),
(9, 10, 'ORD-00009', 'Dang Ngoc Linh', 'linh.dang@shop.com', '0912000008', '9 Nguyen Hue', 'Hue', 'Phu Hoi', 'Vietnam', '530000', NULL, 338.00, 0.00, 15.00, 353.00, NULL, 'momo', 'paid', 'shipping', '2026-03-15 13:00:00', '2026-03-16 08:45:00'),
(10, 11, 'ORD-00010', 'Hoang Gia Bao', 'bao.hoang@shop.com', '0912000009', '55 Cach Mang Thang 8', 'Ho Chi Minh City', 'District 10', 'Vietnam', '700000', 'Handle with care', 115.00, 0.00, 15.00, 130.00, NULL, 'cod', 'pending', 'pending', '2026-03-17 18:22:00', '2026-03-17 18:22:00'),
(11, 12, 'ORD-00011', 'Nguyen Kim Oanh', 'oanh.nguyen@shop.com', '0912000010', '301 Le Duan', 'Da Nang', 'Thanh Khe', 'Vietnam', '550000', NULL, 818.00, 122.70, 15.00, 710.30, 'SPRING15', 'bank_transfer', 'paid', 'delivered', '2026-03-19 09:00:00', '2026-03-23 16:00:00'),
(12, 13, 'ORD-00012', 'Phan Duc Huy', 'huy.phan@shop.com', '0912000011', '76 Pham Ngu Lao', 'Ha Noi', 'Dong Da', 'Vietnam', '100000', NULL, 94.00, 0.00, 15.00, 109.00, NULL, 'cod', 'pending', 'confirmed', '2026-03-21 14:10:00', '2026-03-21 16:20:00'),
(13, 14, 'ORD-00013', 'Ly Thao My', 'my.ly@shop.com', '0912000012', '150 Bach Dang', 'Da Nang', 'Son Tra', 'Vietnam', '550000', 'Please deliver after 5 PM', 548.00, 25.00, 15.00, 538.00, 'HOME25', 'momo', 'paid', 'shipping', '2026-03-23 12:35:00', '2026-03-24 09:30:00'),
(14, 3, 'ORD-00014', 'Nguyen Thi Anh', 'anh.nguyen@shop.com', '0912000001', '12 Le Loi', 'Ho Chi Minh City', 'District 1', 'Vietnam', '700000', NULL, 104.00, 0.00, 15.00, 119.00, NULL, 'cod', 'pending', 'delivered', '2026-03-25 11:45:00', '2026-03-28 10:00:00'),
(15, 4, 'ORD-00015', 'Tran Van Binh', 'binh.tran@shop.com', '0912000002', '45 Tran Hung Dao', 'Da Nang', 'Hai Chau', 'Vietnam', '550000', NULL, 678.00, 101.70, 15.00, 591.30, 'SPRING15', 'bank_transfer', 'paid', 'delivered', '2026-03-27 09:12:00', '2026-03-30 15:30:00');

INSERT INTO `order_items` (`order_id`, `product_id`, `variant_id`, `product_name`, `variant`, `price`, `quantity`, `subtotal`, `thumbnail`) VALUES
(2, 7, 7, 'Nordic Dining Chair', 'Gray', 119.00, 2, 238.00, 'nordic-dining-chair.jpg'),
(2, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 2, 70.00, 'scented-candle-trio.jpg'),
(2, 22, NULL, 'Desk Lamp Pro', NULL, 55.00, 1, 55.00, 'desk-lamp-pro.jpg'),

(3, 6, NULL, 'Cloud Nightstand', NULL, 99.00, 1, 99.00, 'cloud-nightstand.jpg'),
(3, 11, NULL, 'Open Shelf Rack', NULL, 159.00, 1, 159.00, 'open-shelf-rack.jpg'),
(3, 23, NULL, 'Woven Storage Box', NULL, 30.00, 5, 150.00, 'woven-storage-box.jpg'),

(4, 18, NULL, 'Kitchen Trolley', NULL, 169.00, 1, 169.00, 'kitchen-trolley.jpg'),
(4, 17, NULL, 'Stoneware Bowl Set', NULL, 39.00, 2, 78.00, 'stoneware-bowl-set.jpg'),
(4, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 1, 35.00, 'scented-candle-trio.jpg'),
(4, 23, NULL, 'Woven Storage Box', NULL, 16.00, 1, 16.00, 'woven-storage-box.jpg'),

(5, 20, 13, 'Accent Armchair', 'Olive', 289.00, 1, 289.00, 'accent-armchair.jpg'),
(5, 19, 11, 'Soft Cotton Blanket', 'Beige / M', 59.00, 2, 118.00, 'soft-cotton-blanket.jpg'),
(5, 13, NULL, 'Ceramic Vase Set', NULL, 45.00, 1, 45.00, 'ceramic-vase-set.jpg'),
(5, 21, NULL, 'Minimal TV Stand', NULL, 162.00, 1, 162.00, 'minimal-tv-stand.jpg'),

(6, 6, NULL, 'Cloud Nightstand', NULL, 99.00, 1, 99.00, 'cloud-nightstand.jpg'),
(6, 24, NULL, 'Scented Candle Trio', NULL, 30.00, 1, 30.00, 'scented-candle-trio.jpg'),

(7, 8, NULL, 'Round Dining Table', NULL, 379.00, 1, 379.00, 'round-dining-table.jpg'),
(7, 24, NULL, 'Scented Candle Trio', NULL, 39.00, 1, 39.00, 'scented-candle-trio.jpg'),

(8, 5, 5, 'Oak Bed Frame', 'Oak / Queen', 455.00, 1, 455.00, 'oak-bed-frame.jpg'),
(8, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 1, 35.00, 'scented-candle-trio.jpg'),
(8, 17, NULL, 'Stoneware Bowl Set', NULL, 15.00, 1, 15.00, 'stoneware-bowl-set.jpg'),

(9, 10, NULL, 'Walnut Desk', NULL, 349.00, 1, 349.00, 'walnut-desk.jpg'),
(9, 22, NULL, 'Desk Lamp Pro', NULL, 55.00, 1, 55.00, 'desk-lamp-pro.jpg'),
(9, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 1, 35.00, 'scented-candle-trio.jpg'),

(10, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 1, 35.00, 'scented-candle-trio.jpg'),
(10, 17, NULL, 'Stoneware Bowl Set', NULL, 39.00, 1, 39.00, 'stoneware-bowl-set.jpg'),
(10, 23, NULL, 'Woven Storage Box', NULL, 41.00, 1, 41.00, 'woven-storage-box.jpg'),

(11, 5, 6, 'Oak Bed Frame', 'Walnut / Queen', 479.00, 1, 479.00, 'oak-bed-frame.jpg'),
(11, 6, NULL, 'Cloud Nightstand', NULL, 99.00, 2, 198.00, 'cloud-nightstand.jpg'),
(11, 19, 12, 'Soft Cotton Blanket', 'White / L', 69.00, 2, 138.00, 'soft-cotton-blanket.jpg'),
(11, 23, NULL, 'Woven Storage Box', NULL, 3.00, 1, 3.00, 'woven-storage-box.jpg'),

(12, 17, NULL, 'Stoneware Bowl Set', NULL, 39.00, 1, 39.00, 'stoneware-bowl-set.jpg'),
(12, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 1, 35.00, 'scented-candle-trio.jpg'),
(12, 23, NULL, 'Woven Storage Box', NULL, 20.00, 1, 20.00, 'woven-storage-box.jpg'),

(13, 9, 9, 'Ergo Office Chair', 'Black', 219.00, 2, 438.00, 'ergo-office-chair.jpg'),
(13, 22, NULL, 'Desk Lamp Pro', NULL, 55.00, 2, 110.00, 'desk-lamp-pro.jpg'),

(14, 13, NULL, 'Ceramic Vase Set', NULL, 45.00, 1, 45.00, 'ceramic-vase-set.jpg'),
(14, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 1, 35.00, 'scented-candle-trio.jpg'),
(14, 23, NULL, 'Woven Storage Box', NULL, 24.00, 1, 24.00, 'woven-storage-box.jpg'),

(15, 20, 14, 'Accent Armchair', 'Brown', 304.00, 1, 304.00, 'accent-armchair.jpg'),
(15, 21, NULL, 'Minimal TV Stand', NULL, 229.00, 1, 229.00, 'minimal-tv-stand.jpg'),
(15, 13, NULL, 'Ceramic Vase Set', NULL, 45.00, 1, 45.00, 'ceramic-vase-set.jpg'),
(15, 24, NULL, 'Scented Candle Trio', NULL, 35.00, 1, 35.00, 'scented-candle-trio.jpg'),
(15, 23, NULL, 'Woven Storage Box', NULL, 65.00, 1, 65.00, 'woven-storage-box.jpg');

INSERT INTO `payments` (`order_id`, `method`, `amount`, `transaction_id`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES
(2, 'momo', 478.00, 'MOMO-00002', 'success', '2026-03-02 09:16:00', '2026-03-02 09:16:00', '2026-03-02 09:16:00'),
(3, 'cod', 423.00, NULL, 'pending', NULL, '2026-03-04 11:30:00', '2026-03-04 11:30:00'),
(4, 'bank_transfer', 288.00, 'BANK-00004', 'success', '2026-03-06 15:10:00', '2026-03-06 15:10:00', '2026-03-06 15:10:00'),
(5, 'momo', 567.60, 'MOMO-00005', 'success', '2026-03-08 19:21:00', '2026-03-08 19:21:00', '2026-03-08 19:21:00'),
(6, 'cod', 144.00, NULL, 'failed', NULL, '2026-03-10 10:05:00', '2026-03-10 13:30:00'),
(7, 'bank_transfer', 418.00, 'BANK-00007', 'success', '2026-03-11 16:45:00', '2026-03-11 16:45:00', '2026-03-11 16:45:00'),
(8, 'cod', 455.00, NULL, 'pending', NULL, '2026-03-12 09:50:00', '2026-03-12 12:00:00'),
(9, 'momo', 353.00, 'MOMO-00009', 'success', '2026-03-15 13:01:00', '2026-03-15 13:01:00', '2026-03-15 13:01:00'),
(10, 'cod', 130.00, NULL, 'pending', NULL, '2026-03-17 18:22:00', '2026-03-17 18:22:00'),
(11, 'bank_transfer', 710.30, 'BANK-00011', 'success', '2026-03-19 09:05:00', '2026-03-19 09:05:00', '2026-03-19 09:05:00'),
(12, 'cod', 109.00, NULL, 'pending', NULL, '2026-03-21 14:10:00', '2026-03-21 16:20:00'),
(13, 'momo', 538.00, 'MOMO-00013', 'success', '2026-03-23 12:36:00', '2026-03-23 12:36:00', '2026-03-23 12:36:00'),
(14, 'cod', 119.00, NULL, 'pending', NULL, '2026-03-25 11:45:00', '2026-03-28 10:00:00'),
(15, 'bank_transfer', 591.30, 'BANK-00015', 'success', '2026-03-27 09:13:00', '2026-03-27 09:13:00', '2026-03-27 09:13:00');

INSERT INTO `order_status_logs` (`order_id`, `status`, `note`, `changed_by`, `changed_at`) VALUES
(2, 'pending', 'Order created', 3, '2026-03-02 09:15:00'),
(2, 'confirmed', 'Payment verified', 1, '2026-03-02 10:00:00'),
(2, 'shipping', 'Shipped by warehouse', 15, '2026-03-03 09:00:00'),
(2, 'delivered', 'Customer received order', 15, '2026-03-05 14:30:00'),

(3, 'pending', 'Awaiting confirmation', 4, '2026-03-04 11:30:00'),

(4, 'pending', 'Order created', 5, '2026-03-06 15:00:00'),
(4, 'confirmed', 'Bank transfer received', 1, '2026-03-07 08:00:00'),

(5, 'pending', 'Order created', 6, '2026-03-08 19:20:00'),
(5, 'confirmed', 'Payment success', 1, '2026-03-08 19:22:00'),
(5, 'shipping', 'Sent to courier', 15, '2026-03-09 09:10:00'),

(6, 'pending', 'Order created', 7, '2026-03-10 10:05:00'),
(6, 'cancelled', 'Customer cancelled after confirmation call', 1, '2026-03-10 13:30:00'),

(7, 'pending', 'Order created', 8, '2026-03-11 16:40:00'),
(7, 'confirmed', 'Payment success', 1, '2026-03-11 17:00:00'),
(7, 'shipping', 'Handed over to carrier', 15, '2026-03-12 10:00:00'),
(7, 'delivered', 'Completed delivery', 15, '2026-03-14 17:25:00'),

(8, 'pending', 'Order created', 9, '2026-03-12 09:50:00'),
(8, 'confirmed', 'Confirmed via phone', 1, '2026-03-12 12:00:00'),

(9, 'pending', 'Order created', 10, '2026-03-15 13:00:00'),
(9, 'confirmed', 'Payment success', 1, '2026-03-15 13:05:00'),
(9, 'shipping', 'Courier picked up', 15, '2026-03-16 08:45:00'),

(10, 'pending', 'Awaiting staff confirmation', 11, '2026-03-17 18:22:00'),

(11, 'pending', 'Order created', 12, '2026-03-19 09:00:00'),
(11, 'confirmed', 'Payment success', 1, '2026-03-19 09:05:00'),
(11, 'shipping', 'Packed and dispatched', 15, '2026-03-20 08:30:00'),
(11, 'delivered', 'Delivered successfully', 15, '2026-03-23 16:00:00'),

(12, 'pending', 'Order created', 13, '2026-03-21 14:10:00'),
(12, 'confirmed', 'Admin confirmed order', 1, '2026-03-21 16:20:00'),

(13, 'pending', 'Order created', 14, '2026-03-23 12:35:00'),
(13, 'confirmed', 'Payment success', 1, '2026-03-23 12:36:00'),
(13, 'shipping', 'Ready for final-mile delivery', 15, '2026-03-24 09:30:00'),

(14, 'pending', 'Order created', 3, '2026-03-25 11:45:00'),
(14, 'confirmed', 'Confirmed by staff', 1, '2026-03-25 13:00:00'),
(14, 'shipping', 'Dispatched to customer', 15, '2026-03-26 09:20:00'),
(14, 'delivered', 'Delivered successfully', 15, '2026-03-28 10:00:00'),

(15, 'pending', 'Order created', 4, '2026-03-27 09:12:00'),
(15, 'confirmed', 'Payment received', 1, '2026-03-27 09:13:00'),
(15, 'shipping', 'Sent to warehouse line haul', 15, '2026-03-28 08:30:00'),
(15, 'delivered', 'Completed order', 15, '2026-03-30 15:30:00');

INSERT INTO `cart` (`user_id`, `session_id`, `product_id`, `variant_id`, `quantity`, `added_at`) VALUES
(3, NULL, 21, NULL, 1, '2026-04-01 09:00:00'),
(5, NULL, 22, NULL, 2, '2026-04-01 10:00:00'),
(NULL, 'guest_demo_001', 24, NULL, 3, '2026-04-02 08:30:00'),
(NULL, 'guest_demo_002', 13, NULL, 1, '2026-04-02 08:35:00');

INSERT INTO `wishlist` (`user_id`, `product_id`) VALUES
(3, 5),
(3, 20),
(4, 9),
(5, 14),
(6, 21),
(7, 24),
(8, 8),
(9, 19),
(10, 22);

INSERT INTO `inventory_logs` (`product_id`, `variant_id`, `change_qty`, `type`, `note`, `created_by`, `created_at`) VALUES
(5, 5, 12, 'import', 'March warehouse restock', 15, '2026-03-01 08:00:00'),
(7, 7, 25, 'import', 'Dining chair import batch A', 15, '2026-03-01 08:30:00'),
(9, 9, 15, 'import', 'Office chair import batch A', 15, '2026-03-01 09:00:00'),
(19, 11, 30, 'import', 'Blanket seasonal stock', 15, '2026-03-01 09:15:00'),
(20, 13, 6, 'import', 'Accent chair restock', 15, '2026-03-01 09:30:00'),
(22, NULL, 40, 'import', 'Desk lamp new shipment', 15, '2026-03-01 10:00:00'),
(23, NULL, 60, 'import', 'Storage box warehouse refill', 15, '2026-03-01 10:15:00'),
(24, NULL, 40, 'import', 'Candle trio monthly stock', 15, '2026-03-01 10:30:00'),
(7, 7, -2, 'order', 'Sold by order ORD-00002', 1, '2026-03-02 09:15:00'),
(24, NULL, -2, 'order', 'Sold by order ORD-00002', 1, '2026-03-02 09:15:00'),
(18, NULL, -1, 'order', 'Sold by order ORD-00004', 1, '2026-03-06 15:00:00'),
(20, 13, -1, 'order', 'Sold by order ORD-00005', 1, '2026-03-08 19:20:00'),
(8, NULL, -1, 'order', 'Sold by order ORD-00007', 1, '2026-03-11 16:40:00'),
(5, 6, -1, 'order', 'Sold by order ORD-00011', 1, '2026-03-19 09:00:00'),
(9, 9, -2, 'order', 'Sold by order ORD-00013', 1, '2026-03-23 12:35:00'),
(20, 14, -1, 'order', 'Sold by order ORD-00015', 1, '2026-03-27 09:12:00');

INSERT INTO `posts` (`category_id`, `author_id`, `title`, `slug`, `thumbnail`, `excerpt`, `content`, `views`, `is_published`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Small living room ideas that still feel luxurious', 'small-living-room-ideas-that-still-feel-luxurious', 'post-4.jpg', 'Simple styling ideas for a small living room.', 'Expanded sample content for small living room styling and admin demo.', 74, 1, '2026-03-26 09:00:00', '2026-03-26 09:00:00', '2026-03-26 09:00:00'),
(2, 1, 'How to choose the right dining set for your home', 'how-to-choose-the-right-dining-set-for-your-home', 'post-5.jpg', 'Practical tips to choose a dining table and chair set.', 'Expanded sample content for dining set guide and admin demo.', 61, 1, '2026-03-27 10:00:00', '2026-03-27 10:00:00', '2026-03-27 10:00:00'),
(3, 15, '5 decor details that make your bedroom feel calm', '5-decor-details-that-make-your-bedroom-feel-calm', 'post-6.jpg', 'Bedroom styling tips with a calm and warm feeling.', 'Expanded sample content for bedroom styling article.', 49, 1, '2026-03-28 14:00:00', '2026-03-28 14:00:00', '2026-03-28 14:00:00'),
(1, 15, 'Workspace refresh checklist for a more focused week', 'workspace-refresh-checklist-for-a-more-focused-week', 'post-7.jpg', 'Quick office refresh ideas for productivity.', 'Expanded sample content for workspace setup.', 37, 1, '2026-03-30 08:30:00', '2026-03-30 08:30:00', '2026-03-30 08:30:00');

INSERT INTO `contacts` (`name`, `email`, `phone`, `subject`, `message`, `is_read`, `created_at`) VALUES
('Tran My Linh', 'linh.contact@gmail.com', '0901112233', 'Order status', 'Can you help me check my order status for last week?', 1, '2026-03-18 10:10:00'),
('Pham Van Duc', 'duc.ask@gmail.com', '0902223344', 'Return policy', 'I want to know your return policy for sale items.', 0, '2026-03-20 15:20:00'),
('Le Thu Trang', 'trang.home@gmail.com', '0903334455', 'Product dimensions', 'Please send exact dimensions for the walnut desk.', 0, '2026-03-22 09:30:00'),
('Bui Hoang Son', 'son.bui@gmail.com', '0904445566', 'Bulk order', 'Do you have discount for ordering 10 dining chairs?', 1, '2026-03-23 11:05:00'),
('Ngo Minh Chau', 'chau.ngo@gmail.com', '0905556677', 'Shipping issue', 'My package arrived late. Please support.', 0, '2026-03-25 16:45:00'),
('Dang Bao Ngoc', 'ngoc.dang@gmail.com', '0906667788', 'Warranty question', 'Does the office chair include a warranty period?', 0, '2026-03-28 13:10:00');

INSERT INTO `banners` (`title`, `subtitle`, `image`, `link`, `sort_order`, `is_active`) VALUES
('New Season Dining Collection', 'Modern dining sets for everyday moments', '/assets/images/banner-dining.jpg', '/user/shop.php?category=dining-room', 3, 1),
('Work Better At Home', 'Office furniture that keeps your space productive', '/assets/images/banner-office.jpg', '/user/shop.php?category=office', 4, 1);

INSERT INTO `logs` (`user_id`, `action`, `created_at`) VALUES
(1, 'Seeded extended admin demo data', '2026-04-05 18:00:00'),
(15, 'Reviewed inventory imports for March batch', '2026-04-05 18:05:00'),
(1, 'Confirmed multiple sample orders for admin testing', '2026-04-05 18:10:00');

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- END OF FILE
-- =========================================================
