-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 20, 2026 lúc 10:39 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `manage`
--
CREATE DATABASE IF NOT EXISTS `manage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `manage`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('cash','bank') NOT NULL,
  `initial_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `type`, `initial_balance`, `current_balance`, `created_at`, `updated_at`) VALUES
(15, 'Tiền Mặt', 'cash', 500000.00, 500000.00, '2026-04-14 23:56:20', '2026-04-14 23:56:20'),
(16, 'VietcomBank', 'bank', 200000.00, 200000.00, '2026-04-15 00:24:31', '2026-04-15 00:24:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cash_vouchers`
--

CREATE TABLE `cash_vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_type` enum('receipt','payment') NOT NULL,
  `category` enum('debt_customer','debt_supplier','operational','other') NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `credit_logs`
--

CREATE TABLE `credit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `target_type` enum('customer','supplier') NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `ref_type` enum('order','voucher','barter') NOT NULL,
  `ref_id` bigint(20) UNSIGNED NOT NULL,
  `change_amount` decimal(15,2) NOT NULL,
  `new_balance` decimal(15,2) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total_debt` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `address`, `total_debt`, `created_at`, `updated_at`) VALUES
(11, 'Xuân Vũ Lê', '0837960247', NULL, 0.00, '2026-04-15 00:16:36', '2026-04-15 00:16:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer_returns`
--

CREATE TABLE `customer_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `sales_order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `total_return_value` decimal(15,2) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer_return_details`
--

CREATE TABLE `customer_return_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_return_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `refund_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `internal_exports`
--

CREATE TABLE `internal_exports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reason_type` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `total_cost_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `internal_export_details`
--

CREATE TABLE `internal_export_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `internal_export_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `internal_transfers`
--

CREATE TABLE `internal_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_account_id` bigint(20) UNSIGNED NOT NULL,
  `to_account_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_12_21_062134_create_suppliers_table', 1),
(5, '2025_12_21_062135_create_accounts_table', 1),
(6, '2025_12_21_062135_create_customers_table', 1),
(7, '2025_12_21_062135_create_purchase_orders_table', 1),
(8, '2025_12_21_062135_create_shipping_units_table', 1),
(9, '2025_12_21_062136_create_products_table', 1),
(10, '2025_12_21_062136_create_purchase_details_table', 1),
(11, '2025_12_21_062136_create_sales_orders_table', 1),
(12, '2025_12_21_062137_create_cash_vouchers_table', 1),
(13, '2025_12_21_062137_create_credit_logs_table', 1),
(14, '2025_12_21_062137_create_internal_transfers_table', 1),
(15, '2025_12_21_062137_create_sales_details_table', 1),
(16, '2025_12_21_062139_create_stock_logs_table', 1),
(17, '2025_12_22_030808_create_stock_audits_table', 2),
(18, '2025_12_22_031113_create_stock_audit_details_table', 2),
(19, '2026_01_03_014102_create_internal_exports_table', 3),
(20, '2026_01_03_014243_create_internal_export_details_table', 3),
(21, '2026_01_28_101446_add_indexes_to_customers_table', 4),
(22, '2026_04_11_083823_create_customer_returns_table', 5),
(23, '2026_04_11_090559_add_sales_order_id_to_customer_returns', 6),
(24, '2026_04_11_094757_add_description_to_products_table', 7),
(25, '2026_04_11_110023_add_manual_prices_to_products_table', 8),
(26, '2026_04_17_104826_add_variants_to_products_table', 9),
(27, '2026_04_20_041852_add_attributes_json_to_products_table', 10);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('vukofa9120@gmail.com', '$2y$12$xYA/w7pba2P1R/wlqQOgGuBN9ox1shNlPrlc4x2FFCfnfUYE2PEsq', '2025-12-22 02:09:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sku` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `variant_label` varchar(255) DEFAULT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `product_type` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `cost_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `factor_retail` decimal(5,2) NOT NULL DEFAULT 1.50,
  `factor_wholesale` decimal(5,2) NOT NULL DEFAULT 1.10,
  `factor_ctv` decimal(5,2) NOT NULL DEFAULT 1.20,
  `factor_eco_margin` decimal(5,2) NOT NULL DEFAULT 0.50,
  `factor_eco_fee` decimal(5,2) NOT NULL DEFAULT 0.30,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `manual_retail_price` decimal(15,2) DEFAULT NULL,
  `manual_wholesale_price` decimal(15,2) DEFAULT NULL,
  `manual_ctv_price` decimal(15,2) DEFAULT NULL,
  `manual_ecommerce_price` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `purchase_details`
--

CREATE TABLE `purchase_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `import_price` decimal(15,2) NOT NULL,
  `allocated_cost` decimal(15,2) NOT NULL,
  `final_unit_cost` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `total_product_value` decimal(15,2) NOT NULL,
  `extra_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_final_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sales_details`
--

CREATE TABLE `sales_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `cost_price_at_sale` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `shipping_unit_id` bigint(20) UNSIGNED NOT NULL,
  `shipping_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_payor` enum('customer','shop') NOT NULL DEFAULT 'customer',
  `total_product_amount` decimal(15,2) NOT NULL,
  `total_final_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `order_type` enum('sale','barter') NOT NULL DEFAULT 'sale',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('mtjWT7yAinGKYmm6az4bAXj1x3nxold3BYMlJQw2', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVkI4Y3dhcDJ5TXNIbkNPcW5JM0FUNGltZ3NWRmYwQ1BHQWd5NlZYMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9pbnZlbnRvcnkvcHJvZHVjdHMiO3M6NToicm91dGUiO3M6MTQ6InByb2R1Y3RzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1776673576);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipping_units`
--

CREATE TABLE `shipping_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `shipping_units`
--

INSERT INTO `shipping_units` (`id`, `name`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Giao Hàng Nhanh (GHN)', '1900 636677', 'Tầng 3, Tòa nhà VTC Online, 18 Tam Trinh, Minh Khai, Hai Bà Trưng, Hà Nội', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(2, 'Giao Hàng Tiết Kiệm (GHTK)', '1900 6093', 'Tầng 6, Tòa nhà Việt Úc, 402 Nguyễn Thị Minh Khai, Quận 3, TP.HCM', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(3, 'Viettel Post', '1900 8095', 'Tầng 2, Tòa nhà Viettel Post, Số 1 Trần Hưng Đạo, Quận Hoàn Kiếm, Hà Nội', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(4, 'VNPost (Bưu điện Việt Nam)', '1900 545481', 'Số 5 Phạm Hùng, Mễ Trì, Nam Từ Liêm, Hà Nội', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(5, 'J&T Express', '1900 1088', 'Tầng 4, Tòa nhà Viettel Complex, 285 Đội Cấn, Ba Đình, Hà Nội', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(6, 'Ninja Van', '1900 886677', 'Tầng 5, Tòa nhà Vietcombank, 198 Nguyễn Thị Minh Khai, Quận 3, TP.HCM', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(7, 'Best Express', '1900 1034', 'Tầng 10, Tòa nhà Viettel, 285 Cách Mạng Tháng Tám, Quận 10, TP.HCM', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(8, 'AhaMove', '1900 636363', 'Tầng 4, Tòa nhà The Manor, Me Linh Point Tower, Quận 1, TP.HCM', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(9, 'GrabExpress', NULL, 'Tầng 18, Tòa nhà CJ Tower, 6 Lê Thánh Tôn, Quận 1, TP.HCM', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(10, 'Shopee Express (SPX)', NULL, 'Tầng 12, Tòa nhà Viettel, 285 Đội Cấn, Ba Đình, Hà Nội', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(12, 'TikiNOW', NULL, 'Tầng 15, Tòa nhà Viettel Complex, 285 Đội Cấn, Ba Đình, Hà Nội', '2025-12-22 04:38:50', '2025-12-22 04:38:50'),
(13, 'BestExpress', NULL, NULL, '2026-04-15 00:16:56', '2026-04-15 00:16:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stock_audits`
--

CREATE TABLE `stock_audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `total_diff_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stock_audit_details`
--

CREATE TABLE `stock_audit_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_audit_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `system_qty` int(11) NOT NULL,
  `actual_qty` int(11) NOT NULL,
  `diff_qty` int(11) NOT NULL,
  `cost_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stock_logs`
--

CREATE TABLE `stock_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `ref_type` enum('import','export','audit','barter','internal_export') NOT NULL,
  `ref_id` bigint(20) UNSIGNED NOT NULL,
  `change_qty` int(11) NOT NULL,
  `final_qty` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total_debt` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `total_debt`, `created_at`, `updated_at`) VALUES
(5, 'Xuân Vũ Lê', '0837960247', NULL, 0.00, '2026-04-14 19:15:21', '2026-04-14 19:15:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `roles` varchar(255) NOT NULL DEFAULT 'Nhân viên',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `roles`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'vukofa9120@gmail.com', '2025-12-21 07:48:03', '$2y$12$vSvpn3SjGHtIMPzyjHEirOTvHn4dn7iKNqXXDjClsMbXQpKdyhlUq', NULL, 'Admin', '2025-12-21 07:48:03', '2025-12-22 02:26:25');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cash_vouchers`
--
ALTER TABLE `cash_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_vouchers_account_id_foreign` (`account_id`);

--
-- Chỉ mục cho bảng `credit_logs`
--
ALTER TABLE `credit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_name_index` (`name`),
  ADD KEY `customers_phone_index` (`phone`),
  ADD KEY `idx_customers_name` (`name`),
  ADD KEY `idx_customers_phone` (`phone`);

--
-- Chỉ mục cho bảng `customer_returns`
--
ALTER TABLE `customer_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_returns_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_returns_user_id_foreign` (`user_id`),
  ADD KEY `customer_returns_sales_order_id_foreign` (`sales_order_id`);

--
-- Chỉ mục cho bảng `customer_return_details`
--
ALTER TABLE `customer_return_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_return_details_customer_return_id_foreign` (`customer_return_id`),
  ADD KEY `customer_return_details_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `internal_exports`
--
ALTER TABLE `internal_exports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internal_exports_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `internal_export_details`
--
ALTER TABLE `internal_export_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internal_export_details_internal_export_id_foreign` (`internal_export_id`),
  ADD KEY `internal_export_details_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `internal_transfers`
--
ALTER TABLE `internal_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internal_transfers_from_account_id_foreign` (`from_account_id`),
  ADD KEY `internal_transfers_to_account_id_foreign` (`to_account_id`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_parent_id_foreign` (`parent_id`);

--
-- Chỉ mục cho bảng `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_details_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `purchase_details_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchase_orders_account_id_foreign` (`account_id`);

--
-- Chỉ mục cho bảng `sales_details`
--
ALTER TABLE `sales_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_details_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `sales_details_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_orders_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_orders_account_id_foreign` (`account_id`),
  ADD KEY `sales_orders_shipping_unit_id_foreign` (`shipping_unit_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `shipping_units`
--
ALTER TABLE `shipping_units`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `stock_audits`
--
ALTER TABLE `stock_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_audits_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `stock_audit_details`
--
ALTER TABLE `stock_audit_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_audit_details_stock_audit_id_foreign` (`stock_audit_id`),
  ADD KEY `stock_audit_details_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_logs_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `cash_vouchers`
--
ALTER TABLE `cash_vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `credit_logs`
--
ALTER TABLE `credit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `customer_returns`
--
ALTER TABLE `customer_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `customer_return_details`
--
ALTER TABLE `customer_return_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `internal_exports`
--
ALTER TABLE `internal_exports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `internal_export_details`
--
ALTER TABLE `internal_export_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `internal_transfers`
--
ALTER TABLE `internal_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=484;

--
-- AUTO_INCREMENT cho bảng `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `sales_details`
--
ALTER TABLE `sales_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `shipping_units`
--
ALTER TABLE `shipping_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `stock_audits`
--
ALTER TABLE `stock_audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `stock_audit_details`
--
ALTER TABLE `stock_audit_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cash_vouchers`
--
ALTER TABLE `cash_vouchers`
  ADD CONSTRAINT `cash_vouchers_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

--
-- Các ràng buộc cho bảng `customer_returns`
--
ALTER TABLE `customer_returns`
  ADD CONSTRAINT `customer_returns_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `customer_returns_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`),
  ADD CONSTRAINT `customer_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `customer_return_details`
--
ALTER TABLE `customer_return_details`
  ADD CONSTRAINT `customer_return_details_customer_return_id_foreign` FOREIGN KEY (`customer_return_id`) REFERENCES `customer_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_return_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `internal_exports`
--
ALTER TABLE `internal_exports`
  ADD CONSTRAINT `internal_exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `internal_export_details`
--
ALTER TABLE `internal_export_details`
  ADD CONSTRAINT `internal_export_details_internal_export_id_foreign` FOREIGN KEY (`internal_export_id`) REFERENCES `internal_exports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internal_export_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `internal_transfers`
--
ALTER TABLE `internal_transfers`
  ADD CONSTRAINT `internal_transfers_from_account_id_foreign` FOREIGN KEY (`from_account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `internal_transfers_to_account_id_foreign` FOREIGN KEY (`to_account_id`) REFERENCES `accounts` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_details_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Các ràng buộc cho bảng `sales_details`
--
ALTER TABLE `sales_details`
  ADD CONSTRAINT `sales_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_details_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_orders_shipping_unit_id_foreign` FOREIGN KEY (`shipping_unit_id`) REFERENCES `shipping_units` (`id`);

--
-- Các ràng buộc cho bảng `stock_audits`
--
ALTER TABLE `stock_audits`
  ADD CONSTRAINT `stock_audits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `stock_audit_details`
--
ALTER TABLE `stock_audit_details`
  ADD CONSTRAINT `stock_audit_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_audit_details_stock_audit_id_foreign` FOREIGN KEY (`stock_audit_id`) REFERENCES `stock_audits` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `stock_logs_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
