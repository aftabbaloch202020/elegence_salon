-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2026 at 06:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elegance_salon`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` varchar(50) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `stylist_id` varchar(50) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` varchar(50) NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `customer_id`, `service_id`, `stylist_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `amount`, `created_at`, `updated_at`) VALUES
('APT-2465', 9, 'srv-01', 'stf-01', '2026-09-07', '02:00 PM', 'Cancelled', 'Automated E2E Test Booking', 3500.00, '2026-09-04 14:03:48', '2026-09-04 15:29:34'),
('APT-2931', 14, 'srv-01', 'stf-01', '2026-09-07', '15:30', 'Confirmed', 'Automated test appointment for Jalal Khan', 3500.00, '2026-09-04 15:16:02', '2026-09-04 15:16:02'),
('APT-6528', 13, 'srv-06', 'stf-05', '2026-09-07', '06:30 PM', 'Pending', '', 2800.00, '2026-09-04 15:20:34', '2026-09-04 15:43:51'),
('APT-8043', 13, 'srv-01', 'stf-01', '2026-09-04', '10:00 AM', 'Confirmed', ',jkjkjkj', 3500.00, '2026-09-04 15:42:31', '2026-09-04 15:42:31'),
('APT-8046', 13, 'srv-04', 'stf-04', '2026-09-04', '11:00 AM', 'Confirmed', '', 15000.00, '2026-09-04 15:17:55', '2026-09-04 15:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_submissions`
--

INSERT INTO `contact_submissions` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Zara Mansoor', 'zara.real_1788451890@elegance.salon', '+92 300 777 8899', 'Bridal Inquiry', 'Would like to inquire about full bridal packages.', 'unread', '2026-09-03 16:11:30', '2026-09-03 16:11:30'),
(2, 'Guest Inquirer', 'guest@example.com', '+923001112233', 'Bridal Package Inquiry', 'Would like to book a private suite for wedding glam.', 'unread', '2026-09-04 09:11:06', '2026-09-04 09:11:06');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_items`
--

CREATE TABLE `gallery_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery_items`
--

INSERT INTO `gallery_items` (`id`, `category`, `title`, `description`, `image`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Salon', 'Atelier Interior & Suites', 'Private treatment rooms with champagne gold lighting', 'assets/images/gallerys/gallery.1.jpg', 1, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(2, 'Hair', 'Editorial Hair Styling', 'Couture updos & soft waves for special occasions', 'assets/images/gallerys/gallery.2.jpg', 2, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(3, 'Hair', 'Precision Cut & Styling', 'Architectural haircutting tailored to your face structure', 'assets/images/gallerys/gallery.3.jpg', 3, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(4, 'Hair', 'Champagne Balayage & Gloss', 'Hand-painted dimensional blonde highlights with gloss finish', 'assets/images/gallerys/gallery.4.jpg', 4, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(5, 'Makeup', 'Editorial Glam Makeup', 'Luminous camera-ready skin and soft glam artistry', 'assets/images/gallerys/gallery.5.jpg', 5, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(6, 'Facial', 'Hydra Facial Therapy', 'Medical-grade skin extraction & serum infusion ritual', 'assets/images/gallerys/gallery.6.jpg', 6, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(7, 'Salon', 'Styling Chairs & Stations', 'Sunlit styling stations on MM Alam Road', 'assets/images/gallerys/gallery.7.jpg', 7, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(8, 'Salon', 'Vanity & Mirror Details', 'Ambient backlit mirrors & private dressing suites', 'assets/images/gallerys/gallery.8.jpg', 8, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(9, 'Hair', 'Master Stylist Consultation', 'Dedicated one-on-one consultation with senior stylists', 'assets/images/gallerys/gallery.9.jpg', 9, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(10, 'Facial', '24k Gold Radiance Mask', 'Illuminating luxury gold leaf mask to plump & restore skin', 'assets/images/gallerys/gallery.10.jpg', 10, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(11, 'Nails', 'Luxury Spa Pedicure & Nails', 'Restorative botanical foot soak with gel polish finish', 'assets/images/gallerys/gallery.11.jpg', 11, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(12, 'Makeup', 'Haute Couture Look Reveal', 'Flawless final reveal for gala events and bridal occasions', 'assets/images/gallerys/gallery.12.jpg', 12, 'active', '2026-09-04 13:31:36', '2026-09-04 13:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` varchar(50) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Card',
  `status` enum('Pending','Paid','Fulfilled','Cancelled') DEFAULT 'Pending',
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT 0,
  `min_qty` int(11) DEFAULT 5,
  `purchase_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `status` enum('in','low','out') DEFAULT 'in',
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `supplier_name`, `qty`, `min_qty`, `purchase_price`, `selling_price`, `status`, `image`, `description`, `created_at`, `updated_at`) VALUES
('prd-01', 'Sulfate-Free Luxury Shampoo', 'Hair Care', 'Luxe Beauty Dist.', 11, 5, 850.00, 1450.00, 'in', 'assets/images/services/Signature Haircut.1.png', 'Sulfate-free luxury shampoo for color protection.', '2026-09-03 16:08:55', '2026-09-04 09:11:06'),
('prd-02', 'Ammonia-Free Color Gloss Cream', 'Color', 'Atelier Color Co.', 22, 8, 1200.00, 2100.00, 'in', 'assets/images/services/Balayage Color.3.jpg', 'Ammonia-free glossing cream for intense shine.', '2026-09-03 16:08:55', '2026-09-03 16:08:55'),
('prd-03', '24k Gold Radiance Face Mask', 'Skincare', 'Gold Leaf Skincare', 8, 6, 1800.00, 3200.00, 'in', 'assets/images/services/Luxury Hydra Facial.5.jpg', '24k gold leaf mask for instant plump and skin glow.', '2026-09-03 16:08:55', '2026-09-03 16:08:55'),
('prd-04', 'Botanical Cuticle Elixir Oil', 'Nails', 'Luxe Beauty Dist.', 18, 8, 220.00, 480.00, 'in', 'assets/images/services/Classic Manicure.6.jpg', 'Vitamin E cuticle elixir for nail health.', '2026-09-03 16:08:55', '2026-09-03 16:08:55'),
('prd-05', 'Professional Keratin Smoothing Kit', 'Treatments', 'Atelier Color Co.', 6, 5, 4500.00, 7800.00, 'in', 'assets/images/services/Keratin Treatment.4.jpg', 'Professional smoothing treatment kit.', '2026-09-03 16:08:55', '2026-09-03 16:08:55');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` varchar(50) DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `message` text NOT NULL,
  `status` enum('published','pending','archived') DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `rating` decimal(3,2) DEFAULT 5.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `category`, `description`, `duration`, `price`, `rating`, `status`, `image`, `created_at`, `updated_at`) VALUES
('srv-01', 'Signature Haircut', 'Hair', 'Precision cut tailored to your face shape and lifestyle.', 45, 3500.00, 4.90, 'active', 'assets/images/services/Signature Haircut.1.png', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-02', 'Bridal Hair Styling', 'Hair', 'Editorial updos and soft waves for weddings and events.', 90, 12000.00, 5.00, 'active', 'assets/images/services/Bridal Hair Styling.2.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-03', 'Balayage Color', 'Hair', 'Hand-painted highlights with gloss for luminous dimension.', 180, 18000.00, 4.80, 'active', 'assets/images/services/Balayage Color.3.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-04', 'Keratin Treatment', 'Treatments', 'Smoothing treatment that restores shine and manageability.', 150, 15000.00, 4.70, 'active', 'assets/images/services/Keratin Treatment.4.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-05', 'Luxury Hydra Facial', 'Facial', 'Deep cleanse, extract, and infuse with medical-grade serums.', 60, 8500.00, 4.90, 'active', 'assets/images/services/Luxury Hydra Facial.5.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-06', 'Classic Manicure', 'Nails', 'Shape, cuticle care, massage, and long-wear polish.', 40, 2800.00, 4.60, 'active', 'assets/images/services/Classic Manicure.6.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-07', 'Spa Pedicure', 'Nails', 'Soak, exfoliation, massage, and gel color for lasting shine.', 55, 3800.00, 4.80, 'active', 'assets/images/services/Spa Pedicure.7.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-08', 'Glam Makeup', 'Makeup', 'Soft glam to full editorial looks for any occasion.', 75, 9500.00, 4.90, 'active', 'assets/images/services/Glam Makeup.8.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-09', 'Bridal Makeup', 'Makeup', 'Long-wear bridal artistry with trial-ready consultation.', 120, 22000.00, 5.00, 'active', 'assets/images/services/Bridal Makeup.9.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-10', 'Gold Radiance Facial', 'Beauty Care', '24k gold mask ritual to plump and illuminate tired skin.', 70, 11000.00, 4.80, 'active', 'assets/images/services/Gold Radiance Facial.10.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-11', 'Scalp Therapy', 'Treatments', 'Detoxifying scalp massage with botanical oils.', 50, 4500.00, 4.50, 'active', 'assets/images/services/Scalp Therapy.11.jpg', '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('srv-93', 'makeup', 'facial', 'best make-up', 40, 9000.00, 5.00, 'active', 'assets/images/services/Signature Haircut.1.png', '2026-09-04 15:46:12', '2026-09-04 15:46:12');

-- --------------------------------------------------------

--
-- Table structure for table `service_stylist`
--

CREATE TABLE `service_stylist` (
  `service_id` varchar(50) NOT NULL,
  `stylist_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_stylist`
--

INSERT INTO `service_stylist` (`service_id`, `stylist_id`) VALUES
('srv-01', 'stf-01'),
('srv-01', 'stf-02'),
('srv-01', 'stf-04'),
('srv-02', 'stf-01'),
('srv-03', 'stf-01'),
('srv-03', 'stf-04'),
('srv-04', 'stf-02'),
('srv-05', 'stf-03'),
('srv-06', 'stf-05'),
('srv-07', 'stf-05'),
('srv-08', 'stf-03'),
('srv-09', 'stf-03'),
('srv-10', 'stf-03'),
('srv-10', 'stf-05'),
('srv-11', 'stf-02'),
('srv-11', 'stf-04');

-- --------------------------------------------------------

--
-- Table structure for table `special_offers`
--

CREATE TABLE `special_offers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_tag` varchar(100) DEFAULT NULL,
  `badge_text` varchar(100) DEFAULT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `special_price` decimal(10,2) NOT NULL,
  `discount_percent` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stylists`
--

CREATE TABLE `stylists` (
  `id` varchar(50) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `experience` int(11) NOT NULL,
  `rating` decimal(3,2) DEFAULT 5.00,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `hours` varchar(100) DEFAULT NULL,
  `days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`days`)),
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `sales` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stylists`
--

INSERT INTO `stylists` (`id`, `user_id`, `name`, `position`, `specialization`, `experience`, `rating`, `phone`, `email`, `address`, `bio`, `hours`, `days`, `image`, `status`, `sales`, `created_at`, `updated_at`) VALUES
('stf-01', NULL, 'Sarah Khan', 'Senior Hair Stylist', 'Hair Styling & Coloring', 12, 4.90, '+92 300 111 2201', 'sarah@elegancesalon.com', 'DHA Phase 5, Lahore', 'Sarah leads the color atelier with a decade of editorial work and a signature approach to lived-in luxury color.', '10:00 AM – 7:00 PM', '[\"Mon\",\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\"]', 'assets/images/stylists/Senior Hair Stylist.1.jpg', 'active', 50000.00, '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('stf-02', NULL, 'Ayesha Malik', 'Hair Therapist', 'Treatments & Cuts', 8, 4.80, '+92 300 111 2202', 'ayesha@elegancesalon.com', 'Johar Town, Lahore', 'Ayesha restores hair health with keratin systems and precision cutting for naturally refined movement.', '11:00 AM – 8:00 PM', '[\"Tue\",\"Wed\",\"Thu\",\"Fri\",\"Sat\"]', 'assets/images/stylists/Hair Therapist.2.jpg', 'active', 38000.00, '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('stf-03', NULL, 'Hina Raza', 'Beauty Director', 'Makeup & Facials', 10, 5.00, '+92 300 111 2203', 'hina@elegancesalon.com', 'Model Town, Lahore', 'Hina crafts camera-ready skin and bridal looks that photograph beautifully from nikkah to walima.', '10:00 AM – 8:00 PM', '[\"Wed\",\"Thu\",\"Fri\",\"Sat\",\"Sun\"]', 'assets/images/stylists/Beauty Director.3.jpg', 'active', 72000.00, '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('stf-04', NULL, 'Omar Sheikh', 'Creative Stylist', 'Men\'s & Color', 6, 4.70, '+92 300 111 2204', 'omar@elegancesalon.com', 'Bahria Town, Lahore', 'Omar brings contemporary barbering and color artistry with a calm, consultative chair-side manner.', '12:00 PM – 8:00 PM', '[\"Mon\",\"Tue\",\"Thu\",\"Fri\",\"Sat\"]', 'assets/images/stylists/Creative Stylist.4.jpg', 'active', 29000.00, '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
('stf-05', NULL, 'Mehwish Ali', 'Nail Artist', 'Manicure & Pedicure', 7, 4.80, '+92 300 111 2205', 'mehwish@elegancesalon.com', 'Garden Town, Lahore', 'Mehwish is known for architectural nail design, gel art, and meticulous spa pedicure rituals.', '10:00 AM – 6:00 PM', '[\"Mon\",\"Wed\",\"Fri\",\"Sat\",\"Sun\"]', 'assets/images/stylists/Nail Artist.5.jpg', 'active', 24000.00, '2026-09-04 13:31:36', '2026-09-04 13:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer','stylist') DEFAULT 'customer',
  `status` enum('active','inactive') DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `avatar`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Elegance Concierge Admin', 'admin@elegancesalon.com', '+92 300 111 0000', '$2y$10$ambnF48VJO2LS35ln0MNnePd1fWaAHH4bQua7iXfIsBHSCRL/3abG', 'admin', 'active', NULL, NULL, '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(2, 'Elegance Salon Admin', 'admin@elegance.com', '+92 300 000 0000', '$2y$10$ambnF48VJO2LS35ln0MNnePd1fWaAHH4bQua7iXfIsBHSCRL/3abG', 'admin', 'active', NULL, NULL, '2026-09-04 13:31:36', '2026-09-04 13:31:36'),
(3, 'Zainab Qureshi', 'customer_1788529190@example.com', '+92 300 5551234', '$2y$10$PfLMZHj1V74o/JV4T1B/9OIHJF5qh0IUhsWcbgblV.A0UMU5p81xC', 'customer', 'active', NULL, NULL, '2026-09-04 13:39:51', '2026-09-04 13:39:51'),
(4, 'Zainab Qureshi', 'customer_1788529212@example.com', '+92 300 5551234', '$2y$10$OHdbi1zo34e/caw1FjCdieLM8S3WdIkk21fQWFW9dB6KFsJSsit4m', 'customer', 'active', NULL, NULL, '2026-09-04 13:40:12', '2026-09-04 13:40:12'),
(5, 'Zainab Qureshi', 'customer_1788530459@example.com', '+92 300 5551234', '$2y$10$f0TNfHW7A1ykvKgnrEVojeuMKICRWVuKYKFmyFaTMwMKWnY8pB0v.', 'customer', 'active', NULL, NULL, '2026-09-04 14:01:00', '2026-09-04 14:01:00'),
(6, 'Aftab Baloch', 'aftab_baloch_1788530514@elegance.test', '03001234567', '$2y$10$3weeTy1zQRrgq2guZfrXkOD8NmQ4MzZGEtfuRriwT..tF4k7ZB0NS', 'customer', 'active', NULL, NULL, '2026-09-04 14:01:54', '2026-09-04 14:01:54'),
(7, 'Aftab Baloch', 'aftab_baloch_1788530543@elegance.test', '03001234567', '$2y$10$i2Jd.lOUovDKtWx.35N7n.3AS7pg17EGZDjzTTM0XMLhNOMPkkgq.', 'customer', 'active', NULL, NULL, '2026-09-04 14:02:23', '2026-09-04 14:02:23'),
(8, 'Aftab Baloch', 'aftab_baloch_1788530616@elegance.test', '03001234567', '$2y$10$2PYOzx7VMTeyZwrpa4av2O7t5dRiJAHJ4gcVVdRD3p.WU1jiUd0/q', 'customer', 'active', NULL, NULL, '2026-09-04 14:03:36', '2026-09-04 14:03:36'),
(9, 'Aftab Baloch', 'aftab_baloch_1788530627@elegance.test', '03001234567', '$2y$10$8rwhTIzsuhPT.2663NK.0OvOxcsoAgnFNQtub5yatLTZu4reN/e.q', 'customer', 'active', NULL, NULL, '2026-09-04 14:03:47', '2026-09-04 14:03:47'),
(11, 'Jalal Khan', 'jalal_1788533269@example.com', '03001234567', '$2y$10$Jt9WA8n61KIQ39tHWMX//OIrgl5VC0YyRyocKq6WzcHSCNhMmPX/C', 'customer', 'active', NULL, NULL, '2026-09-04 14:47:50', '2026-09-04 14:47:50'),
(13, 'developer', 'developer@gmail.com', '+923219009876', '$2y$10$H9LDj0yFWarLGV2KwGqSL.11UTmiJQEZjjSU16cizq7UFYTTwpPVC', 'customer', 'active', NULL, NULL, '2026-09-04 14:51:59', '2026-09-04 14:51:59'),
(14, 'Jalal Khan', 'jalal_1788534962@example.com', '03001234567', '$2y$10$ipZi9Ee1mzH5EKsEzzry3.6u0ESNch8YUPj3FaXwdk5hlb8yXj2lW', 'customer', 'active', NULL, NULL, '2026-09-04 15:16:02', '2026-09-04 15:16:02'),
(15, 'Jalal Khan', 'jalal_1788536328@example.com', '03001234567', '$2y$10$BXfo2IZIQG/roi9J1P4vNuUJcKlcRl.3A2NEkz21Dadd/WVqHmFcW', 'customer', 'active', NULL, NULL, '2026-09-04 15:38:49', '2026-09-04 15:38:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `stylist_id` (`stylist_id`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_items`
--
ALTER TABLE `gallery_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_stylist`
--
ALTER TABLE `service_stylist`
  ADD PRIMARY KEY (`service_id`,`stylist_id`),
  ADD KEY `stylist_id` (`stylist_id`);

--
-- Indexes for table `special_offers`
--
ALTER TABLE `special_offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stylists`
--
ALTER TABLE `stylists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gallery_items`
--
ALTER TABLE `gallery_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `special_offers`
--
ALTER TABLE `special_offers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`stylist_id`) REFERENCES `stylists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_stylist`
--
ALTER TABLE `service_stylist`
  ADD CONSTRAINT `service_stylist_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_stylist_ibfk_2` FOREIGN KEY (`stylist_id`) REFERENCES `stylists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stylists`
--
ALTER TABLE `stylists`
  ADD CONSTRAINT `stylists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
