-- Tool Site Database Schema
-- Created for Laravel-style MVC implementation

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: tool_site_db
CREATE DATABASE IF NOT EXISTS `tool_site_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tool_site_db`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','moderator') NOT NULL DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `sessions`
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `posts`
CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_user_id_foreign` (`user_id`),
  KEY `posts_status_index` (`status`),
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `tools`
CREATE TABLE `tools` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `is_external` tinyint(1) NOT NULL DEFAULT 0,
  `tags` json DEFAULT NULL,
  `usage_count` bigint(20) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tools_slug_unique` (`slug`),
  KEY `tools_category_id_foreign` (`category_id`),
  KEY `tools_is_featured_index` (`is_featured`),
  CONSTRAINT `tools_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `type` enum('string','integer','boolean','json','text') NOT NULL DEFAULT 'string',
  `group` varchar(100) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Insert sample data

-- Sample users (password for all is 'password')
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `bio`, `is_active`) VALUES
(1, 'Admin User', 'admin@toolsite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+1234567890', 'System administrator with full access to all features.', 1),
(2, 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+1234567891', 'Regular user who loves using online tools.', 1),
(3, 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderator', '+1234567892', 'Content moderator helping maintain quality.', 1),
(4, 'Bob Wilson', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+1234567893', 'Web developer and tool enthusiast.', 1),
(5, 'Alice Johnson', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+1234567894', 'Designer who uses various online tools daily.', 1);

-- Sample categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `sort_order`, `is_active`) VALUES
(1, 'Text Tools', 'text-tools', 'Tools for text manipulation, formatting, and analysis', 1, 1),
(2, 'Image Tools', 'image-tools', 'Tools for image editing, conversion, and optimization', 2, 1),
(3, 'Development Tools', 'development-tools', 'Tools for developers including formatters, validators, and generators', 3, 1),
(4, 'Converters', 'converters', 'Various conversion tools for different file formats and data types', 4, 1),
(5, 'Calculators', 'calculators', 'Online calculators for various mathematical and financial calculations', 5, 1),
(6, 'SEO Tools', 'seo-tools', 'Search engine optimization and website analysis tools', 6, 1);

-- Sample tools
INSERT INTO `tools` (`id`, `name`, `slug`, `description`, `category_id`, `icon`, `url`, `is_external`, `tags`, `usage_count`, `is_featured`, `is_active`) VALUES
(1, 'Text Counter', 'text-counter', 'Count characters, words, and lines in your text', 1, 'fas fa-calculator', '/tools/text-counter', 0, '["text", "counter", "words", "characters"]', 1250, 1, 1),
(2, 'Base64 Encoder/Decoder', 'base64-encoder-decoder', 'Encode and decode Base64 strings easily', 3, 'fas fa-code', '/tools/base64', 0, '["base64", "encoder", "decoder", "development"]', 890, 1, 1),
(3, 'Image Compressor', 'image-compressor', 'Compress images without losing quality', 2, 'fas fa-compress', '/tools/image-compressor', 0, '["image", "compress", "optimization"]', 2100, 1, 1),
(4, 'JSON Formatter', 'json-formatter', 'Format and validate JSON data', 3, 'fas fa-file-code', '/tools/json-formatter', 0, '["json", "formatter", "validator", "development"]', 1650, 1, 1),
(5, 'Password Generator', 'password-generator', 'Generate secure passwords with custom options', 6, 'fas fa-key', '/tools/password-generator', 0, '["password", "generator", "security"]', 3200, 1, 1),
(6, 'URL Shortener', 'url-shortener', 'Create short URLs for easy sharing', 6, 'fas fa-link', '/tools/url-shortener', 0, '["url", "shortener", "link"]', 1800, 0, 1),
(7, 'QR Code Generator', 'qr-code-generator', 'Generate QR codes for text, URLs, and more', 2, 'fas fa-qrcode', '/tools/qr-generator', 0, '["qr", "code", "generator"]', 2750, 1, 1),
(8, 'Color Picker', 'color-picker', 'Pick colors and get hex, RGB, HSL values', 2, 'fas fa-palette', '/tools/color-picker', 0, '["color", "picker", "hex", "rgb"]', 920, 0, 1);

-- Sample posts
INSERT INTO `posts` (`id`, `user_id`, `title`, `slug`, `content`, `excerpt`, `status`, `published_at`) VALUES
(1, 1, 'Welcome to Our Tool Site', 'welcome-to-our-tool-site', 'Welcome to our comprehensive collection of online tools! We provide a wide variety of utilities to help you with text processing, image manipulation, development tasks, and much more.', 'Welcome to our comprehensive collection of online tools designed to make your life easier.', 'published', '2025-07-01 10:00:00'),
(2, 1, 'Best Practices for Online Security', 'best-practices-online-security', 'In today\'s digital world, online security is more important than ever. Here are some best practices to keep your data safe while using online tools.', 'Essential tips for maintaining security while using online tools and services.', 'published', '2025-07-05 14:30:00'),
(3, 2, 'How to Use Our Text Tools Effectively', 'how-to-use-text-tools-effectively', 'Our text tools can help you process and analyze text in various ways. Learn how to get the most out of these powerful utilities.', 'Maximize your productivity with our comprehensive text processing tools.', 'published', '2025-07-08 09:15:00');

-- Sample settings
INSERT INTO `settings` (`key`, `value`, `type`, `group`) VALUES
('site_name', 'Tool Site', 'string', 'general'),
('site_description', 'Your one-stop destination for online tools', 'string', 'general'),
('site_email', 'admin@toolsite.com', 'string', 'general'),
('maintenance_mode', '0', 'boolean', 'system'),
('max_file_size', '10485760', 'integer', 'uploads'),
('allowed_file_types', '["jpg", "jpeg", "png", "gif", "pdf", "txt", "doc", "docx"]', 'json', 'uploads'),
('enable_user_registration', '1', 'boolean', 'user'),
('default_user_role', 'user', 'string', 'user');

COMMIT;
