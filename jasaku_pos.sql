-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Jan 2026 pada 15.39
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jasaku_pos`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(120) DEFAULT NULL,
  `role` varchar(30) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(50) DEFAULT NULL,
  `entity_id` varchar(120) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `ua` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `role`, `action`, `entity`, `entity_id`, `detail`, `ip`, `ua`, `created_at`) VALUES
(1, 4, 'Admin', 'admin', 'create', 'supplier', '1', '{\"name\":\"V-Gen\",\"phone\":\"085552233411\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 17:20:01'),
(2, 4, 'Admin', 'admin', 'create', 'purchase', '1', '{\"code\":\"PO-D6F3A5\",\"supplier_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 17:40:35'),
(3, 4, 'Admin', 'admin', 'receive', 'purchase', '1', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 17:40:59'),
(4, 4, 'Admin', 'admin', 'create', 'purchase', '2', '{\"code\":\"PO-602349\",\"supplier_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:29:56'),
(5, 4, 'Admin', 'admin', 'receive', 'purchase', '2', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:30:03'),
(6, 4, 'Admin', 'admin', 'create', 'purchase', '3', '{\"code\":\"PO-F2B52B\",\"supplier_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:32:54'),
(7, 4, 'Admin', 'admin', 'receive', 'purchase', '3', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:32:54'),
(8, 4, 'Admin', 'admin', 'create', 'purchase', '4', '{\"code\":\"PO-251373\",\"supplier_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:34:11'),
(9, 4, 'Admin', 'admin', 'receive', 'purchase', '4', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:34:11'),
(10, 4, 'Admin', 'admin', 'create', 'item', '1', '{\"name\":\"SSD Sata 500gb\",\"sku\":\"12345678901112\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:38:45'),
(11, 4, 'Admin', 'admin', 'create', 'purchase', '5', '{\"code\":\"PO-F92085\",\"supplier_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:39:17'),
(12, 4, 'Admin', 'admin', 'receive', 'purchase', '5', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:39:17'),
(13, 4, 'Admin', 'admin', 'update', 'item', '1', '{\"name\":\"SSD Sata 500gb\",\"sku\":\"12345678901112\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 18:39:57'),
(14, 4, 'Admin', 'admin', 'create', 'purchase', '6', '{\"code\":\"PO-4B718D\",\"supplier_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 06:07:53'),
(15, 4, 'Admin', 'admin', 'receive', 'purchase', '6', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 06:07:53'),
(16, 4, 'Admin', 'admin', 'create', 'customer', '3', '{\"name\":\"Guest\",\"phone\":\"0a\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 06:41:45'),
(17, 4, 'Admin', 'admin', 'delete', 'customer', '3', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 06:53:40'),
(18, 4, 'Admin', 'admin', 'create', 'customer', '4', '{\"name\":\"Guest\",\"phone\":\"01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 06:54:28'),
(19, 4, 'Admin', 'admin', 'create', 'sale', '1', '{\"code\":\"SL-C195F1\",\"customer_name\":\"\",\"payment_method\":\"Tunai\",\"total\":600000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 06:56:41'),
(20, 4, 'Admin', 'admin', 'create', 'sale', '2', '{\"code\":\"SL-48E0AD\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":600000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 06:59:57'),
(21, 4, 'Admin', 'admin', 'adjust', 'item', '1', '{\"delta\":2,\"note\":\"Penyesuaian Stok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 07:03:10'),
(22, 4, 'Admin', 'admin', 'update', 'item', '1', '{\"name\":\"SSD Sata 500gb\",\"sku\":\"12345678901112\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 07:10:42'),
(23, 4, 'Admin', 'admin', 'create', 'item', '2', '{\"name\":\"RAM DDR4 8Gb\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 08:16:52'),
(24, 4, 'Admin', 'admin', 'create', 'purchase', '7', '{\"code\":\"PO-43C3A1\",\"supplier_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 08:16:52'),
(25, 4, 'Admin', 'admin', 'receive', 'purchase', '7', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 08:16:52'),
(26, 4, 'Admin', 'admin', 'update', 'item', '2', '{\"name\":\"RAM DDR4 8Gb\",\"sku\":\"098765455371\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 08:17:50'),
(27, 4, 'Admin', 'admin', 'create', 'sale', '3', '{\"code\":\"SL-480E9D\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":170000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 09:30:16'),
(28, 4, 'Admin', 'admin', 'create', 'sale', '4', '{\"code\":\"SL-97473B\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":600000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 10:15:49'),
(29, 4, 'Admin', 'admin', 'create', 'sale', '5', '{\"code\":\"SL-36DCAA\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":170000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 11:38:36'),
(30, 4, 'Admin', 'admin', 'create', 'sale', '6', '{\"code\":\"SL-8B4F0E\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":170000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 12:25:19'),
(31, 4, 'Admin', 'admin', 'create', 'sale', '7', '{\"code\":\"SL-99A91F\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":170000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 16:45:25'),
(32, 4, 'Admin', 'admin', 'create', 'sale', '8', '{\"code\":\"SL-62E8BF\",\"customer_name\":\"Guest\",\"payment_method\":\"QRIS\",\"total\":600000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 16:45:50'),
(33, 4, 'Admin', 'admin', 'create', 'sale', '9', '{\"code\":\"SL-1B3176\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":600000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 12:52:43'),
(34, 4, 'Admin', 'admin', 'create', 'sale', '10', '{\"code\":\"SL-5F3412\",\"customer_name\":\"Guest\",\"payment_method\":\"Tunai\",\"total\":170000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 13:07:36'),
(35, 4, 'Admin', 'admin', 'create', 'customer', '5', '{\"name\":\"zona\",\"phone\":\"088802175324\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 18:09:30'),
(36, 4, 'Admin', 'admin', 'create', 'ticket', '4', '{\"code\":\"TKT-C040B6\",\"status\":\"Baru\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 18:12:14'),
(37, 4, 'Admin', 'admin', 'update', 'ticket', '4', '{\"code\":\"TKT-C040B6\",\"status\":\"Dalam Pemeriksaan\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 18:15:03'),
(38, 4, 'Admin', 'admin', 'update', 'ticket', '4', '{\"code\":\"TKT-C040B6\",\"status\":\"Dalam Perbaikan\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 18:34:37'),
(39, 4, 'Admin', 'admin', 'update', 'ticket', '4', '{\"code\":\"TKT-C040B6\",\"status\":\"Selesai\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 13:31:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `note`, `created_at`, `updated_at`) VALUES
(4, 'Guest', '01', 'guest@email.com', '-', '-', '2025-12-05 06:54:28', '2025-12-05 06:54:28'),
(5, 'zona', '088802175324', 'zona@email.com', 'Kadungora', 'xxxxxxxxxx', '2025-12-11 18:09:30', '2025-12-11 18:09:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `items`
--

INSERT INTO `items` (`id`, `name`, `sku`, `price`, `stock`, `min_stock`, `created_at`, `updated_at`) VALUES
(1, 'SSD Sata 500gb', '12345678901112', 600000.00, 15, 1, '2025-12-04 18:38:45', '2025-12-11 12:52:43'),
(2, 'RAM DDR4 8Gb', '098765455371', 170000.00, 5, 0, '2025-12-05 08:16:52', '2025-12-11 13:07:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pricelist`
--

CREATE TABLE `pricelist` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `item_id`, `qty`, `price`) VALUES
(1, 5, 1, 10, 530000.00),
(2, 6, 1, 10, 500000.00),
(3, 7, 2, 10, 150000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Draft',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `code`, `supplier_id`, `status`, `note`, `created_at`, `updated_at`) VALUES
(5, 'PO-F92085', 1, 'Received', '-', '2025-12-04 18:39:17', '2025-12-04 18:39:17'),
(6, 'PO-4B718D', 1, 'Received', '-', '2025-12-05 06:07:53', '2025-12-05 06:07:53'),
(7, 'PO-43C3A1', 1, 'Received', '-', '2025-12-05 08:16:52', '2025-12-05 08:16:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sales`
--

INSERT INTO `sales` (`id`, `code`, `customer_name`, `payment_method`, `total`, `created_at`) VALUES
(3, 'SL-480E9D', 'Guest', 'Tunai', 170000.00, '2025-12-05 09:30:16'),
(4, 'SL-97473B', 'Guest', 'Tunai', 600000.00, '2025-12-05 10:15:49'),
(5, 'SL-36DCAA', 'Guest', 'Tunai', 170000.00, '2025-12-05 11:38:36'),
(6, 'SL-8B4F0E', 'Guest', 'Tunai', 170000.00, '2025-12-05 12:25:19'),
(7, 'SL-99A91F', 'Guest', 'Tunai', 170000.00, '2025-12-05 16:45:25'),
(8, 'SL-62E8BF', 'Guest', 'QRIS', 600000.00, '2025-12-05 16:45:50'),
(9, 'SL-1B3176', 'Guest', 'Tunai', 600000.00, '2025-12-11 12:52:43'),
(10, 'SL-5F3412', 'Guest', 'Tunai', 170000.00, '2025-12-11 13:07:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `item_id`, `qty`, `price`) VALUES
(1, 1, 1, 1, 600000.00),
(2, 2, 1, 1, 600000.00),
(3, 3, 2, 1, 170000.00),
(4, 4, 1, 1, 600000.00),
(5, 5, 2, 1, 170000.00),
(6, 6, 2, 1, 170000.00),
(7, 7, 2, 1, 170000.00),
(8, 8, 1, 1, 600000.00),
(9, 9, 1, 1, 600000.00),
(10, 10, 2, 1, 170000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `k` varchar(100) NOT NULL,
  `v` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`k`, `v`, `updated_at`) VALUES
('company_address', 'Kadungora, Batulawang', '2025-12-03 16:48:07'),
('company_name', 'JasaKu Com', '2025-12-03 16:48:07'),
('company_phone', '081234567890', '2025-12-03 16:48:07'),
('print_footer', 'Terimakasih telah percaya kepada kami', '2025-12-03 16:48:07'),
('store_address', 'kadungora, perum batulawang', '2025-12-03 18:39:11'),
('store_footer', 'Terimakasih atas kepercayaan anda', '2025-12-03 18:39:11'),
('store_logo', 'public/uploads/logo-20251203200112.jpg', '2025-12-03 19:01:12'),
('store_name', 'JasaKU Com', '2025-12-03 18:39:11'),
('store_phone', '081234567890', '2025-12-03 18:39:11'),
('thermal_width', '58', '2025-12-03 16:48:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `type` enum('IN','OUT','ADJUST') NOT NULL DEFAULT 'IN',
  `qty` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `ref_type` varchar(30) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `item_id`, `type`, `qty`, `note`, `ref_type`, `ref_id`, `created_at`) VALUES
(1, 1, 'IN', 10, 'Terima PO', 'PO', 5, '2025-12-04 18:39:17'),
(2, 1, 'IN', 10, 'Terima PO', 'PO', 6, '2025-12-05 06:07:53'),
(3, 1, 'OUT', 1, 'Penjualan POS', 'SALE', 1, '2025-12-05 06:56:41'),
(4, 1, 'OUT', 1, 'Penjualan POS', 'SALE', 2, '2025-12-05 06:59:57'),
(5, 1, 'ADJUST', 2, 'Penyesuaian Stok', 'ITEM', 1, '2025-12-05 07:03:10'),
(6, 2, 'OUT', 1, 'Penjualan POS', 'SALE', 3, '2025-12-05 09:30:16'),
(7, 1, 'OUT', 1, 'Penjualan POS', 'SALE', 4, '2025-12-05 10:15:49'),
(8, 2, 'OUT', 1, 'Penjualan POS', 'SALE', 5, '2025-12-05 11:38:36'),
(9, 2, 'OUT', 1, 'Penjualan POS', 'SALE', 6, '2025-12-05 12:25:19'),
(10, 2, 'OUT', 1, 'Penjualan POS', 'SALE', 7, '2025-12-05 16:45:25'),
(11, 1, 'OUT', 1, 'Penjualan POS', 'SALE', 8, '2025-12-05 16:45:50'),
(12, 1, 'OUT', 1, 'Penjualan POS', 'SALE', 9, '2025-12-11 12:52:43'),
(13, 2, 'OUT', 1, 'Penjualan POS', 'SALE', 10, '2025-12-11 13:07:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `address`, `note`, `created_at`, `updated_at`) VALUES
(1, 'V-Gen', '085552233411', 'vgen@email.com', 'Jakarta Pusat', '-', '2025-12-04 17:20:01', '2025-12-04 17:20:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `device_type` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estimate_price` decimal(12,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(120) DEFAULT NULL,
  `accessories` varchar(255) DEFAULT NULL,
  `cost_items` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tickets`
--

INSERT INTO `tickets` (`id`, `code`, `customer_name`, `phone`, `device_type`, `status`, `description`, `created_at`, `updated_at`, `estimate_price`, `payment_method`, `brand`, `model`, `serial_number`, `accessories`, `cost_items`) VALUES
(4, 'TKT-C040B6', 'zona', '088802175324', 'Laptop', 'Selesai', '-', '2025-12-11 18:12:14', '2026-01-20 13:31:38', 150000.00, 'Tunai', 'Dell', 'xx123xx', 'asd123456asd', 'Charger', '[{\"name\":\"install ulang\",\"qty\":1,\"price\":150000}]');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_assignments`
--

CREATE TABLE `ticket_assignments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `status` varchar(30) DEFAULT 'Ditugaskan',
  `sla_hours` int(11) DEFAULT 48,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sla_deadline` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teknisi','kasir') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(4, 'Admin', 'admin@example.com', '$2y$10$TUN.etgEpJ8ZWjI1MWsTquVL6otnmRC7v7OU6dSxIsdHvdkcggNda', 'admin', '2025-12-03 15:14:40'),
(5, 'kaif', 'kaif@email.com', '$2y$10$ndwysxZMIRVu4Ia5mefAkO6317fOcaLx99I5i.SExWko7XIYz0pT6', 'teknisi', '2025-12-11 18:32:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `warranties`
--

CREATE TABLE `warranties` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `ticket_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `device_type` varchar(120) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `duration_months` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku_unique` (`sku`);

--
-- Indeks untuk tabel `pricelist`
--
ALTER TABLE `pricelist`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`k`);

--
-- Indeks untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `warranties`
--
ALTER TABLE `warranties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pricelist`
--
ALTER TABLE `pricelist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `warranties`
--
ALTER TABLE `warranties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
