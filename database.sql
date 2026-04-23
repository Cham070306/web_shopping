-- =========================================================
-- WEB_SHOPPING - FINAL CLEAN DATABASE
-- Compatible: MySQL 8.0+ / XAMPP / phpMyAdmin
-- Purpose: clean final schema + demo data, ready to import
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
DROP TABLE IF EXISTS `notification_subscribers`;
DROP TABLE IF EXISTS `customer_notes`;
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
  `gender` ENUM('male','female','other') DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `avatar` VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_active` (`is_active`),
  INDEX `idx_users_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_otps`;

CREATE TABLE `password_reset_otps` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `request_ip` VARCHAR(45) DEFAULT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `purpose` ENUM('forgot_password','verify_email') NOT NULL DEFAULT 'forgot_password',
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
  INDEX `idx_reset_expires` (`expires_at`),
  INDEX `idx_reset_purpose` (`purpose`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `user_addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `ward` VARCHAR(100) DEFAULT NULL,
  `district` VARCHAR(100) DEFAULT NULL,
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
  `brand` VARCHAR(100) DEFAULT NULL,
  `material` VARCHAR(100) DEFAULT NULL,
  `color` VARCHAR(100) DEFAULT NULL,
  `size` VARCHAR(100) DEFAULT NULL,
  `weight` DECIMAL(10,2) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_products_price` CHECK (`price` >= 0),
  CONSTRAINT `chk_products_sale_price` CHECK (`sale_price` IS NULL OR `sale_price` >= 0),
  CONSTRAINT `chk_products_stock` CHECK (`stock` >= 0),
  CONSTRAINT `chk_products_sold` CHECK (`sold` >= 0),
  CONSTRAINT `chk_products_sale_logic` CHECK (`sale_price` IS NULL OR `sale_price` <= `price`),
  INDEX `idx_products_category` (`category_id`),
  INDEX `idx_products_active_featured` (`is_active`, `is_featured`),
  INDEX `idx_products_price` (`price`),
  INDEX `idx_products_sale_price` (`sale_price`),
  INDEX `idx_products_created_at` (`created_at`),
  INDEX `idx_products_name` (`name`),
  INDEX `idx_products_brand` (`brand`)
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
  `sku` VARCHAR(80) DEFAULT NULL UNIQUE,
  `color` VARCHAR(50) DEFAULT NULL,
  `size` VARCHAR(20) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `price_diff` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_product_variants_stock` CHECK (`stock` >= 0),
  INDEX `idx_product_variants_product` (`product_id`),
  INDEX `idx_product_variants_combo` (`product_id`, `color`, `size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT DEFAULT NULL,
  `admin_reply` TEXT DEFAULT NULL,
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
  `description` VARCHAR(255) DEFAULT NULL,
  `type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` DECIMAL(10,2) NOT NULL,
  `min_order` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `max_discount` DECIMAL(10,2) DEFAULT NULL,
  `max_uses` INT NOT NULL DEFAULT 0,
  `used_count` INT NOT NULL DEFAULT 0,
  `starts_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `chk_coupons_value` CHECK (`value` >= 0),
  CONSTRAINT `chk_coupons_min_order` CHECK (`min_order` >= 0),
  CONSTRAINT `chk_coupons_max_uses` CHECK (`max_uses` >= 0),
  CONSTRAINT `chk_coupons_used_count` CHECK (`used_count` >= 0),
  CONSTRAINT `chk_coupons_max_discount` CHECK (`max_discount` IS NULL OR `max_discount` >= 0),
  INDEX `idx_coupons_active_expire` (`is_active`, `expires_at`),
  INDEX `idx_coupons_starts_at` (`starts_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `order_code` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `billing_address` TEXT DEFAULT NULL,
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
  `tracking_code` VARCHAR(100) DEFAULT NULL,
  `payment_method` ENUM('cod','bank_transfer','momo') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `status` ENUM('pending','confirmed','shipping','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `delivered_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_orders_subtotal` CHECK (`subtotal` >= 0),
  CONSTRAINT `chk_orders_discount` CHECK (`discount` >= 0),
  CONSTRAINT `chk_orders_shipping_fee` CHECK (`shipping_fee` >= 0),
  CONSTRAINT `chk_orders_total` CHECK (`total` >= 0),
  INDEX `idx_orders_user` (`user_id`),
  INDEX `idx_orders_status` (`status`),
  INDEX `idx_orders_payment_status` (`payment_status`),
  INDEX `idx_orders_created_at` (`created_at`),
  INDEX `idx_orders_order_code` (`order_code`),
  INDEX `idx_orders_tracking_code` (`tracking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `variant_id` INT UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `product_sku` VARCHAR(80) DEFAULT NULL,
  `variant` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `quantity` INT NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_order_items_price` CHECK (`price` >= 0),
  CONSTRAINT `chk_order_items_quantity` CHECK (`quantity` > 0),
  CONSTRAINT `chk_order_items_subtotal` CHECK (`subtotal` >= 0),
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
  `note` VARCHAR(255) DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_payments_amount` CHECK (`amount` >= 0),
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
  `stock_before` INT DEFAULT NULL,
  `stock_after` INT DEFAULT NULL,
  `type` ENUM('import','order','adjust') NOT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `supplier_name` VARCHAR(150) DEFAULT NULL,
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
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `excerpt` VARCHAR(500) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
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
  `reply_message` TEXT DEFAULT NULL,
  `replied_at` DATETIME DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_contacts_read` (`is_read`, `created_at`),
  INDEX `idx_contacts_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) DEFAULT NULL,
  `subtitle` VARCHAR(300) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `position` VARCHAR(50) NOT NULL DEFAULT 'homepage',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_banners_active_sort` (`is_active`, `sort_order`),
  INDEX `idx_banners_position` (`position`)
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

CREATE TABLE `customer_notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `note` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_customer_notes_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_customer_notes_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_customer_notes_user` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notification_subscribers_active` (`is_active`)
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






INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `max_uses`, `used_count`, `expires_at`, `is_active`) VALUES
('WELCOME10', 'percent', 10.00, 100.00, 100, 0, '2027-12-31 23:59:59', 1),
('SAVE50', 'fixed', 50.00, 300.00, 50, 1, '2027-12-31 23:59:59', 1);







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






INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `max_uses`, `used_count`, `expires_at`, `is_active`) VALUES
('SPRING15', 'percent', 15.00, 150.00, 200, 12, '2026-06-30 23:59:59', 1),
('HOME25', 'fixed', 25.00, 200.00, 150, 28, '2026-12-31 23:59:59', 1),
('VIP20', 'percent', 20.00, 500.00, 50, 9, '2026-10-31 23:59:59', 1),
('FREESHIP', 'fixed', 15.00, 120.00, 500, 40, '2026-09-30 23:59:59', 1),
('OLDSALE', 'percent', 10.00, 100.00, 100, 100, '2025-12-31 23:59:59', 0);








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


UPDATE `users` SET `last_login` = '2026-04-05 09:00:00' WHERE `id` = 1;
UPDATE `users` SET `last_login` = '2026-04-04 20:30:00' WHERE `id` = 2;





UPDATE `coupons`
SET `description` = 'Discount for first-time customers', `max_discount` = 100.00, `starts_at` = '2026-01-01 00:00:00'
WHERE `code` = 'WELCOME10';

UPDATE `coupons`
SET `description` = 'Fixed discount for higher-value carts', `max_discount` = NULL, `starts_at` = '2026-01-01 00:00:00'
WHERE `code` = 'SAVE50';





UPDATE `posts`
SET `meta_title` = `title`, `meta_description` = `excerpt`
WHERE `meta_title` IS NULL;

INSERT INTO `customer_notes` (`user_id`, `admin_id`, `note`) VALUES
(2, 1, 'Frequent returning customer. Good sample account for order-history testing.'),
(3, 1, 'Used for dashboard customer metrics and admin search demo.');

INSERT INTO `notification_subscribers` (`email`) VALUES
('newsletter1@example.com'),
('newsletter2@example.com')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);


SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- END OF FINAL CLEAN FILE
-- =========================================================

-- =========================================================
-- OFFICIAL ADMIN ACCOUNT
-- Email : admin@3legant.com
-- Password: Admin@3legant
-- Only @3legant.com emails with role=admin can access /admin
-- =========================================================
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `avatar`, `role`, `is_active`) VALUES
('3legant Admin', 'admin@3legant.com', '$2y$10$4G0O8Qn3TusLImzl2SWuJO9PF2R72djXn8Qbj8g5VlZelIxryNSLa', '0900000000', 'default.jpg', 'admin', 1);

SET FOREIGN_KEY_CHECKS = 1;

