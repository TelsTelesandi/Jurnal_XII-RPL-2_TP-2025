-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 17, 2025 at 03:44 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `compro_pt_rka`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` bigint UNSIGNED NOT NULL,
  `blog_post_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_comments`
--

INSERT INTO `blog_comments` (`id`, `blog_post_id`, `user_id`, `isi`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'haloo', '2025-08-24 21:11:12', '2025-08-24 21:11:12'),
(2, 2, 2, 'halo juga', '2025-08-27 01:40:14', '2025-08-27 01:40:14'),
(3, 3, 2, 'haiii ini adalah user 1', '2025-09-03 02:44:29', '2025-09-03 02:44:29'),
(4, 3, 3, 'woii', '2025-09-11 19:46:51', '2025-09-11 19:46:51'),
(5, 2, 1, 'hello', '2025-09-23 00:20:58', '2025-09-23 00:20:58'),
(6, 3, 1, 'hello', '2025-10-03 00:09:19', '2025-10-03 00:09:19'),
(7, 3, 2, 'a', '2025-10-12 21:30:05', '2025-10-12 21:30:05');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` bigint UNSIGNED NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `isi` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `views_count` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `judul`, `slug`, `excerpt`, `isi`, `thumbnail`, `user_id`, `category_id`, `published_at`, `views_count`, `created_at`, `updated_at`) VALUES
(2, 'Pelatihan GIS Batch 2025 Resmi Dimulai', 'pelatihan-gis-batch-2025', 'Batch baru dimulai untuk pelatihan GIS & Remote Sensing...', 'Konten lengkap artikel...', NULL, 1, NULL, '2025-08-24 20:14:27', 9, '2025-08-24 20:14:27', '2025-10-16 20:23:27'),
(3, 'Workshop Remote Sensing untuk Profesional', 'workshop-remote-sensing', 'Konten lengkap workshop remote sensing....', 'Konten lengkap workshop remote sensing....', 'thumbnails/7dWiHn17bGbwIUpBCzznl22zgegcLreg8Ljpbnek.png', 1, NULL, '2025-08-14 21:05:31', 8, '2025-08-24 21:05:31', '2025-10-12 21:29:58'),
(4, 'Kerja Sama Baru dengan Mitra Strategis', 'kerja-sama-baru', 'PT RKA menjalin kerja sama dengan institusi pendidikan dan perusahaan teknologi...', 'Konten lengkap kerja sama baru...', NULL, 1, NULL, '2025-07-25 21:05:31', 0, '2025-08-24 21:05:31', '2025-08-24 21:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `blog_views`
--

CREATE TABLE `blog_views` (
  `id` bigint UNSIGNED NOT NULL,
  `blog_post_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `viewed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_views`
--

INSERT INTO `blog_views` (`id`, `blog_post_id`, `user_id`, `session_id`, `ip`, `user_agent`, `viewed_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'szdm2Yi2kV5mQ5xpfqmbHkbLSmR6Xh7teu6SuRRI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 00:37:30', '2025-09-23 00:37:30', '2025-09-23 00:37:30'),
(2, 3, 1, 'szdm2Yi2kV5mQ5xpfqmbHkbLSmR6Xh7teu6SuRRI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 00:37:45', '2025-09-23 00:37:45', '2025-09-23 00:37:45'),
(3, 4, 1, 'szdm2Yi2kV5mQ5xpfqmbHkbLSmR6Xh7teu6SuRRI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 00:37:53', '2025-09-23 00:37:53', '2025-09-23 00:37:53'),
(4, 3, 1, 'RgrdKdiJZXJPoE5ee1b1UGQ4kppH9A59PHtfK6Eu', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 00:08:46', '2025-10-03 00:08:46', '2025-10-03 00:08:46'),
(5, 2, 1, 'RgrdKdiJZXJPoE5ee1b1UGQ4kppH9A59PHtfK6Eu', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 00:16:07', '2025-10-03 00:16:07', '2025-10-03 00:16:07'),
(6, 2, NULL, 'bgfsCusgOjODrsyTUj4tGfWAqG1yXPpFUb4TJnoJ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 01:21:12', '2025-10-04 01:21:12', '2025-10-04 01:21:12'),
(7, 3, NULL, 'bgfsCusgOjODrsyTUj4tGfWAqG1yXPpFUb4TJnoJ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 01:21:20', '2025-10-04 01:21:20', '2025-10-04 01:21:20'),
(8, 3, NULL, 'BTwMNrcz8CtMxotTkBDiFDdjwlGoms7jbGIOuERE', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-06 19:23:06', '2025-10-06 19:23:06', '2025-10-06 19:23:06'),
(9, 3, NULL, '6xn0WlcWfg57vieQ51yns7WREpaqnbezgDdR2P39', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-08 00:33:04', '2025-10-08 00:33:04', '2025-10-08 00:33:04'),
(10, 2, 2, 'iDgkJIqSdZN5R5ssTr5hAo4UMJCMchpNjBOPuzIc', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-08 00:44:20', '2025-10-08 00:44:20', '2025-10-08 00:44:20'),
(11, 3, 1, 'PSF8wQFEUFQI0sqUMjXXqolOxfoJUGBFp26RnKjr', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 20:33:36', '2025-10-11 20:33:36', '2025-10-11 20:33:36'),
(12, 3, NULL, 'Q7PZX8a5I3r7Hv5eUkxrw8Rl8kHckTq83azXNIfL', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 21:10:24', '2025-10-11 21:10:24', '2025-10-11 21:10:24'),
(13, 3, NULL, 'KMJ23WBSIMGOfBFtppD2GBZdE6kmTSBvkwu8oaAo', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 17:40:24', '2025-10-12 17:40:24', '2025-10-12 17:40:24'),
(14, 2, 2, 'cp8vxYIvhB2zBRMRoFGblglr3QjlUGrJk9MsJFfY', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 19:29:01', '2025-10-12 19:29:01', '2025-10-12 19:29:01'),
(15, 3, 2, 'wGrEsInMFDwPksYRzq7aedh1JL3RQDsCWgF6uGZf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 21:29:58', '2025-10-12 21:29:58', '2025-10-12 21:29:58'),
(16, 2, 2, 'sy0obKkq2UoPjHRuYMNq2mjZCYunAqAsXVIMBKKE', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 21:50:23', '2025-10-12 21:50:23', '2025-10-12 21:50:23'),
(17, 2, NULL, 'mLZDfkueq3XS3vrceIW1Inb0KfmFRl1fyQBE9KUD', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 20:23:27', '2025-10-16 20:23:27', '2025-10-16 20:23:27');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_bans`
--

CREATE TABLE `forum_bans` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `banned_by` bigint UNSIGNED NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `ban_type` enum('kick','temporary','permanent') COLLATE utf8mb4_unicode_ci DEFAULT 'kick',
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_bans`
--

INSERT INTO `forum_bans` (`id`, `user_id`, `banned_by`, `reason`, `ban_type`, `expires_at`, `is_active`, `created_at`, `updated_at`) VALUES
(9, 2, 1, 'Melanggar aturan forum', 'permanent', NULL, 0, '2025-10-12 19:43:52', '2025-10-12 20:39:26'),
(10, 2, 1, 'Melanggar aturan forum', 'permanent', NULL, 0, '2025-10-12 19:44:07', '2025-10-12 20:39:26'),
(11, 2, 1, 'No reason provided', 'temporary', '2025-10-13 20:17:21', 0, '2025-10-12 20:17:21', '2025-10-12 20:39:26'),
(12, 2, 1, 'No reason provided', 'permanent', NULL, 0, '2025-10-12 20:17:25', '2025-10-12 20:39:26'),
(13, 2, 1, 'No reason provided', 'temporary', '2025-10-13 20:17:39', 0, '2025-10-12 20:17:39', '2025-10-12 20:39:26'),
(14, 2, 1, 'No reason provided', 'temporary', '2025-10-13 20:17:57', 0, '2025-10-12 20:17:57', '2025-10-12 20:39:26'),
(15, 2, 1, 'Kicked from forum', 'kick', '2025-10-12 20:23:10', 0, '2025-10-12 20:18:10', '2025-10-12 20:39:26'),
(16, 2, 3, 'No reason provided', 'permanent', NULL, 0, '2025-10-12 20:38:33', '2025-10-12 20:39:26'),
(17, 2, 1, 'No reason provided', 'temporary', '2025-10-13 20:39:26', 0, '2025-10-12 20:39:26', '2025-10-12 21:46:39');

-- --------------------------------------------------------

--
-- Table structure for table `forum_messages`
--

CREATE TABLE `forum_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `message_type` enum('text','image','document','voice','contact','location','poll','video') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `metadata` json DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reply_to_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_messages`
--

INSERT INTO `forum_messages` (`id`, `user_id`, `message`, `message_type`, `metadata`, `is_deleted`, `deleted_by`, `deleted_at`, `attachment`, `reply_to_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'Selamat datang di forum diskusi PT RKA! Silakan bertanya dan berbagi informasi seputar GIS dan Remote Sensing.', 'text', '{\"is_welcome\": true}', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-08-26 19:11:48', '2025-09-25 21:10:00'),
(2, 2, 'Terima kasih! Saya ingin bertanya tentang pelatihan GIS yang akan datang.', 'text', NULL, 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-08-26 19:11:48', '2025-09-25 21:10:00'),
(3, 3, 'Untuk informasi pelatihan terbaru, silakan cek halaman blog kami secara berkala.', 'text', NULL, 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-08-26 19:11:48', '2025-09-25 21:10:00'),
(4, 2, 'j', 'text', '[]', 1, 2, '2025-09-03 00:19:36', NULL, NULL, '2025-09-03 00:19:13', '2025-09-03 00:19:36'),
(5, 2, 'haii', 'text', '[]', 1, 2, '2025-09-03 00:33:29', NULL, NULL, '2025-09-03 00:33:10', '2025-09-03 00:33:29'),
(6, 2, 'j', 'text', '[]', 1, 2, '2025-09-03 00:38:09', NULL, NULL, '2025-09-03 00:34:17', '2025-09-03 00:38:09'),
(7, 2, 'uu', 'text', '[]', 1, 3, '2025-09-10 23:35:46', NULL, NULL, '2025-09-03 00:38:12', '2025-09-10 23:35:46'),
(8, 3, 'haii juga', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, 7, '2025-09-03 00:47:23', '2025-09-25 21:10:00'),
(9, 3, 'oke min', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, 1, '2025-09-03 00:47:31', '2025-09-25 21:10:00'),
(10, 2, 'haii mod', 'text', '[]', 1, 3, '2025-09-10 19:44:39', NULL, NULL, '2025-09-03 00:56:57', '2025-09-10 19:44:39'),
(11, 2, 'aku user baru nihh', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-03 00:57:03', '2025-09-25 21:10:00'),
(12, 3, 'okayy', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, 11, '2025-09-03 00:59:25', '2025-09-25 21:10:00'),
(13, 2, NULL, 'document', '[]', 1, 2, '2025-09-03 01:26:04', 'forum/1756887934_rtJlpxCcNT.pdf', NULL, '2025-09-03 01:25:35', '2025-09-03 01:26:04'),
(14, 2, NULL, 'document', '[]', 1, 2, '2025-09-03 01:26:01', 'forum/1756887937_26LMTYeVsc.pdf', NULL, '2025-09-03 01:25:37', '2025-09-03 01:26:01'),
(15, 2, NULL, 'document', '[]', 1, 2, '2025-09-03 01:25:57', 'forum/1756887938_pNeGqHf41I.pdf', NULL, '2025-09-03 01:25:38', '2025-09-03 01:25:57'),
(16, 2, NULL, 'image', '[]', 1, 2, '2025-09-03 01:26:35', 'forum/1756887980_gTABheo4JJ.png', NULL, '2025-09-03 01:26:20', '2025-09-03 01:26:35'),
(17, 2, NULL, 'document', '[]', 1, 1, '2025-09-25 21:10:00', 'forum/1756890207_4N23IEepTI.pdf', NULL, '2025-09-03 02:03:27', '2025-09-25 21:10:00'),
(18, 2, 'haii', 'text', '[]', 1, 2, '2025-09-03 19:04:37', NULL, NULL, '2025-09-03 19:03:45', '2025-09-03 19:04:37'),
(19, 2, 'haloo', 'text', '[]', 1, 3, '2025-09-09 23:59:18', NULL, NULL, '2025-09-03 19:14:39', '2025-09-09 23:59:18'),
(20, 2, '👌❤️😴🥳📌🤤', 'text', '[]', 1, 2, '2025-09-03 21:35:40', NULL, NULL, '2025-09-03 20:00:39', '2025-09-03 21:35:40'),
(21, 2, '💤💤💤💤💤', 'text', '[]', 1, 2, '2025-09-03 21:35:45', NULL, NULL, '2025-09-03 20:30:38', '2025-09-03 21:35:45'),
(22, 2, 'jhdja', 'text', '[]', 1, 2, '2025-09-03 20:52:07', NULL, NULL, '2025-09-03 20:51:53', '2025-09-03 20:52:07'),
(23, 2, '😂', 'text', '[]', 1, 2, '2025-09-03 21:35:49', NULL, NULL, '2025-09-03 20:58:22', '2025-09-03 21:35:49'),
(24, 2, '👍', 'text', '[]', 1, 2, '2025-09-03 21:57:32', NULL, 12, '2025-09-03 21:56:17', '2025-09-03 21:57:32'),
(25, 2, 'aduhh pusing', 'text', '[]', 1, 2, '2025-09-03 23:53:37', NULL, NULL, '2025-09-03 23:51:56', '2025-09-03 23:53:37'),
(26, 3, 'oke', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, 2, '2025-09-04 02:35:48', '2025-09-25 21:10:00'),
(27, 3, NULL, 'document', '[]', 1, 1, '2025-09-25 21:10:00', 'forum/1757469380_L1Mdovflas.pptx', NULL, '2025-09-09 18:56:21', '2025-09-25 21:10:00'),
(28, 3, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 271732, \"filename\": \"Screenshot 2025-09-02 075024.png\"}', 1, 3, '2025-09-09 19:23:39', 'forum/images/1757470992_QWIlWCsYM6.png', NULL, '2025-09-09 19:23:12', '2025-09-09 19:23:39'),
(29, 3, NULL, 'document', '{\"mime\": \"application/vnd.openxmlformats-officedocument.presentationml.presentation\", \"size\": 796410, \"filename\": \"Biru dan Putih Modern Presentasi Struktur Organisasi Dalam Bisnis.pptx\"}', 1, 3, '2025-09-09 19:30:50', 'forum/docs/1757471444_Ys17iIXt7A.pptx', NULL, '2025-09-09 19:30:44', '2025-09-09 19:30:50'),
(30, 3, NULL, 'document', '{\"mime\": \"application/vnd.openxmlformats-officedocument.wordprocessingml.document\", \"size\": 29418, \"filename\": \"Summary_Rapih.docx\"}', 1, 1, '2025-09-25 21:10:00', 'forum/docs/1757476409_s7pCsXFUPg.docx', NULL, '2025-09-09 20:53:29', '2025-09-25 21:10:00'),
(31, 3, NULL, 'document', '{\"mime\": \"application/pdf\", \"size\": 6285921, \"filename\": \"Aplikasi DSS Kehutanan Kemenhut.pdf\"}', 1, 1, '2025-09-25 21:10:00', 'forum/docs/1757476438_d8ouZ8STYG.pdf', NULL, '2025-09-09 20:53:59', '2025-09-25 21:10:00'),
(32, 3, NULL, 'image', '{\"mime\": \"image/jpeg\", \"size\": 43112, \"filename\": \"fe3fbffc-a202-4573-9aec-3dfe228735d7.jpeg\"}', 1, 3, '2025-09-10 20:59:23', 'forum/images/1757477207_111Ww1mWQ5.jpeg', NULL, '2025-09-09 21:06:47', '2025-09-10 20:59:23'),
(33, 3, NULL, 'contact', '[]', 1, 3, '2025-09-10 19:34:22', NULL, NULL, '2025-09-10 01:14:48', '2025-09-10 19:34:22'),
(34, 2, '🚀', 'text', '[]', 1, 3, '2025-09-10 02:05:33', NULL, NULL, '2025-09-10 01:27:59', '2025-09-10 02:05:33'),
(35, 2, 'sjdhaj', 'text', '[]', 1, 3, '2025-09-10 02:05:29', NULL, 27, '2025-09-10 01:36:56', '2025-09-10 02:05:29'),
(36, 3, 'tes', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-10 02:07:07', '2025-09-25 21:10:00'),
(37, 3, 'haii juga', 'text', '[]', 1, 3, '2025-09-11 19:24:55', NULL, 10, '2025-09-10 02:07:59', '2025-09-11 19:24:55'),
(38, 3, 'p', 'text', '[]', 1, 3, '2025-09-10 23:41:36', NULL, NULL, '2025-09-10 19:45:41', '2025-09-10 23:41:36'),
(39, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 145061, \"filename\": \"voice-1757642250795.webm\"}', 1, 3, '2025-09-11 18:57:56', 'forum/voices/1757642252_M89jd83FQE.webm', NULL, '2025-09-11 18:57:33', '2025-09-11 18:57:56'),
(40, 3, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 8385, \"filename\": \"document.png\"}', 1, 3, '2025-09-11 19:08:21', 'forum/images/1757642884_g136XMMkr5.png', NULL, '2025-09-11 19:08:04', '2025-09-11 19:08:21'),
(41, 3, 'haii aku adris', 'image', '{\"mime\": \"image/png\", \"size\": 19626, \"filename\": \"Picture1.png\"}', 1, 1, '2025-09-25 21:10:00', 'forum/images/1757644543_fAVDQ2Gok8.png', NULL, '2025-09-11 19:35:43', '2025-09-25 21:10:00'),
(42, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 166313, \"filename\": \"voice-1757644883045.webm\"}', 1, 3, '2025-09-16 21:33:49', 'forum/voices/1757644887_DSj7wMfp4x.webm', NULL, '2025-09-11 19:41:27', '2025-09-16 21:33:49'),
(43, 3, 'jebdfjs', 'text', '[]', 1, 3, '2025-09-11 19:43:02', NULL, NULL, '2025-09-11 19:42:44', '2025-09-11 19:43:02'),
(44, 3, 'dfs', 'voice', '{\"mime\": \"audio/webm\", \"size\": 132501, \"filename\": \"voice-1757644967818.webm\"}', 1, 3, '2025-09-11 19:43:06', 'forum/voices/1757644972_1t1pQQBcFO.webm', NULL, '2025-09-11 19:42:52', '2025-09-11 19:43:06'),
(45, 2, 'tes', 'text', '[]', 1, 3, '2025-09-15 00:25:12', NULL, NULL, '2025-09-15 00:23:09', '2025-09-15 00:25:12'),
(46, 4, 'tess', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-15 00:23:31', '2025-09-25 21:10:00'),
(47, 4, 'tes lagi', 'text', '[]', 1, 3, '2025-09-15 00:25:08', NULL, NULL, '2025-09-15 00:23:40', '2025-09-15 00:25:08'),
(48, 3, 'hai', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-15 00:40:34', '2025-09-25 21:10:00'),
(49, 3, '🖤🚢✅', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-15 01:14:27', '2025-09-25 21:10:00'),
(50, 3, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 1313500, \"filename\": \"Screenshot 2025-09-02 074959.png\"}', 1, 1, '2025-09-25 21:10:00', 'forum/images/1757924602_6bHajiYiR7.png', NULL, '2025-09-15 01:23:23', '2025-09-25 21:10:00'),
(51, 3, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 1495157, \"filename\": \"Screenshot 2025-09-02 075041.png\"}', 1, 1, '2025-09-25 21:10:00', 'forum/images/1757924919_zygksDilnB.png', NULL, '2025-09-15 01:28:39', '2025-09-25 21:10:00'),
(52, 3, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 1495157, \"filename\": \"Screenshot 2025-09-02 075041.png\"}', 1, 3, '2025-09-15 01:38:13', 'forum/images/1757925292_nIOhM4DFtS.png', NULL, '2025-09-15 01:34:52', '2025-09-15 01:38:13'),
(53, 3, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 19268, \"filename\": \"doc.png\"}', 1, 3, '2025-09-15 01:39:05', 'forum/images/1757925537_nDi2iWsmJt.png', NULL, '2025-09-15 01:38:57', '2025-09-15 01:39:05'),
(54, 3, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 30903, \"filename\": \"cropped-Logo-Itenas-KCL.png\"}', 1, 3, '2025-09-15 02:07:35', 'forum/images/1757925755_Y509xg3x33.png', NULL, '2025-09-15 01:42:35', '2025-09-15 02:07:35'),
(55, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 69713, \"filename\": \"voice-1757926619717.webm\"}', 1, 3, '2025-09-15 01:57:11', 'forum/voices/1757926620_dueq4Mrml1.webm', NULL, '2025-09-15 01:57:00', '2025-09-15 01:57:11'),
(56, 3, NULL, 'contact', '[]', 1, 3, '2025-09-15 18:45:43', NULL, NULL, '2025-09-15 18:45:36', '2025-09-15 18:45:43'),
(57, 3, NULL, 'image', '{\"mime\": \"image/jpeg\", \"size\": 50649, \"filename\": \"photo-1757988117544.jpg\"}', 1, 3, '2025-09-15 19:02:06', 'forum/images/1757988119_YFNSNX1Rmb.jpg', NULL, '2025-09-15 19:02:00', '2025-09-15 19:02:06'),
(58, 3, NULL, 'image', '{\"mime\": \"image/jpeg\", \"size\": 56271, \"filename\": \"photo-1757993657264.jpg\"}', 1, 3, '2025-09-15 20:34:25', 'forum/images/1757993659_kewPLRrElL.jpg', NULL, '2025-09-15 20:34:19', '2025-09-15 20:34:25'),
(59, 3, 'halo', 'text', '[]', 1, 3, '2025-09-16 00:15:42', NULL, NULL, '2025-09-16 00:15:37', '2025-09-16 00:15:42'),
(60, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 17549, \"filename\": \"voice-1758007053777.webm\"}', 1, 3, '2025-09-16 00:17:43', 'forum/voices/1758007055_XCvFElUpN6.webm', NULL, '2025-09-16 00:17:36', '2025-09-16 00:17:43'),
(61, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 22379, \"filename\": \"voice-1758007154012.webm\"}', 1, 3, '2025-09-16 00:19:21', 'forum/voices/1758007156_twg6HJUoSy.webm', NULL, '2025-09-16 00:19:16', '2025-09-16 00:19:21'),
(62, 3, NULL, 'image', '{\"mime\": \"image/jpeg\", \"size\": 65165, \"filename\": \"photo-1758007597986.jpg\"}', 1, 3, '2025-09-16 00:26:46', 'forum/images/1758007601_1CL6GUTNnR.jpg', NULL, '2025-09-16 00:26:41', '2025-09-16 00:26:46'),
(63, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 23345, \"filename\": \"voice-1758008281029.webm\"}', 1, 3, '2025-09-16 00:38:32', 'forum/voices/1758008283_02hBhkP9qT.webm', NULL, '2025-09-16 00:38:03', '2025-09-16 00:38:32'),
(64, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 67781, \"filename\": \"voice-1758008323331.webm\"}', 1, 3, '2025-09-16 00:39:05', 'forum/voices/1758008325_htpyotHc6Q.webm', NULL, '2025-09-16 00:38:45', '2025-09-16 00:39:05'),
(65, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 61019, \"filename\": \"voice-1758008390795.webm\"}', 1, 3, '2025-09-16 00:40:17', 'forum/voices/1758008392_gWXxskgJFE.webm', NULL, '2025-09-16 00:39:52', '2025-09-16 00:40:17'),
(66, 3, NULL, 'contact', '[]', 1, 3, '2025-09-16 00:41:54', NULL, NULL, '2025-09-16 00:41:46', '2025-09-16 00:41:54'),
(67, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 1078414, \"filename\": \"video-1758011610590.mp4\"}', 1, 3, '2025-09-16 01:33:47', 'forum/videos/1758011613_6lg4Srt3Dy.mp4', NULL, '2025-09-16 01:33:33', '2025-09-16 01:33:47'),
(68, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 1320712, \"filename\": \"video-1758011927347.mp4\"}', 1, 3, '2025-09-16 01:39:03', 'forum/videos/1758011929_zdaD74uGK3.mp4', NULL, '2025-09-16 01:38:49', '2025-09-16 01:39:03'),
(69, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 1201801, \"filename\": \"video-1758012307234.mp4\"}', 1, 3, '2025-09-16 01:45:21', 'forum/videos/1758012309_hTOPsQrDZ8.mp4', NULL, '2025-09-16 01:45:09', '2025-09-16 01:45:21'),
(70, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 29141, \"filename\": \"voice-1758013594784.webm\"}', 1, 3, '2025-09-16 02:06:40', 'forum/voices/1758013595_rUY8vHsU5V.webm', NULL, '2025-09-16 02:06:35', '2025-09-16 02:06:40'),
(71, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 92897, \"filename\": \"voice-1758014496662.webm\"}', 1, 3, '2025-09-16 02:21:41', 'forum/voices/1758014497_p7Iw2fjU0S.webm', NULL, '2025-09-16 02:21:37', '2025-09-16 02:21:41'),
(72, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 16583, \"filename\": \"voice-1758014551426.webm\"}', 1, 3, '2025-09-16 02:22:36', 'forum/voices/1758014551_dcs0uYgiOq.webm', NULL, '2025-09-16 02:22:31', '2025-09-16 02:22:36'),
(73, 3, '🐔🐔🐔🐔🐔🐔🐔🐔', 'text', '[]', 1, 3, '2025-09-16 02:23:00', NULL, NULL, '2025-09-16 02:22:55', '2025-09-16 02:23:00'),
(74, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 25277, \"filename\": \"voice-1758071520931.webm\"}', 1, 3, '2025-09-16 18:12:05', 'forum/voices/1758071521_eE58zq03jw.webm', NULL, '2025-09-16 18:12:01', '2025-09-16 18:12:05'),
(75, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 1149733, \"filename\": \"voice-1758071606756.webm\"}', 1, 3, '2025-09-16 18:13:31', 'forum/voices/1758071606_6dXulpCA98.webm', NULL, '2025-09-16 18:13:26', '2025-09-16 18:13:31'),
(76, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 2093, \"filename\": \"voice-1758073322395.webm\"}', 1, 3, '2025-09-16 18:42:18', 'forum/voices/1758073322_GW0epQUCAU.webm', NULL, '2025-09-16 18:42:02', '2025-09-16 18:42:18'),
(77, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 2093, \"filename\": \"voice-1758073325328.webm\"}', 1, 3, '2025-09-16 18:42:13', 'forum/voices/1758073325_Dv4Y7xxW6b.webm', NULL, '2025-09-16 18:42:05', '2025-09-16 18:42:13'),
(78, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 811396, \"filename\": \"video-1758073346330.mp4\"}', 1, 3, '2025-09-16 18:42:33', 'forum/videos/1758073348_1B5cmQHoDa.mp4', NULL, '2025-09-16 18:42:28', '2025-09-16 18:42:33'),
(79, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 1127, \"filename\": \"voice-1758073461761.webm\"}', 1, 3, '2025-09-16 18:44:26', 'forum/voices/1758073461_S2pTTTDYdL.webm', NULL, '2025-09-16 18:44:21', '2025-09-16 18:44:26'),
(80, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 33005, \"filename\": \"voice-1758073485561.webm\"}', 1, 3, '2025-09-16 18:44:50', 'forum/voices/1758073486_zp3gnjyeOu.webm', NULL, '2025-09-16 18:44:46', '2025-09-16 18:44:50'),
(81, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 378519, \"filename\": \"voice-1758073516030.webm\"}', 1, 3, '2025-09-16 18:56:53', 'forum/voices/1758073516_BVMbsA49Kv.webm', NULL, '2025-09-16 18:45:16', '2025-09-16 18:56:53'),
(82, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 841743, \"filename\": \"video-1758074193709.mp4\"}', 1, 3, '2025-09-16 18:56:43', 'forum/videos/1758074199_3jBrgBMSaI.mp4', NULL, '2025-09-16 18:56:39', '2025-09-16 18:56:43'),
(83, 3, 'tess', 'text', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-16 19:05:26', '2025-09-25 21:10:00'),
(84, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 61019, \"filename\": \"voice-1758075588474.webm\"}', 1, 3, '2025-09-16 19:20:08', 'forum/voices/1758075603_8qUIfOnJRa.webm', NULL, '2025-09-16 19:20:03', '2025-09-16 19:20:08'),
(85, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 54909, \"filename\": \"voice-1758075678558.webm\"}', 1, 3, '2025-09-16 19:42:36', 'forum/voices/1758075680_DydqG5jkod.webm', NULL, '2025-09-16 19:21:20', '2025-09-16 19:42:36'),
(86, 3, 'mnd', 'text', '[]', 1, 3, '2025-09-16 19:42:31', NULL, NULL, '2025-09-16 19:42:27', '2025-09-16 19:42:31'),
(87, 3, '😂😂😂😂😂😂😂', 'text', '[]', 1, 3, '2025-09-16 19:43:17', NULL, NULL, '2025-09-16 19:43:13', '2025-09-16 19:43:17'),
(88, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 19481, \"filename\": \"voice-1758079061364.webm\"}', 1, 3, '2025-09-16 20:17:47', 'forum/voices/1758079061_tmvMiooVed.webm', NULL, '2025-09-16 20:17:41', '2025-09-16 20:17:47'),
(89, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 64883, \"filename\": \"voice-1758079075463.webm\"}', 1, 3, '2025-09-16 20:18:01', 'forum/voices/1758079076_qQtAh3FYOG.webm', NULL, '2025-09-16 20:17:56', '2025-09-16 20:18:01'),
(90, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 110285, \"filename\": \"voice-1758079348241.webm\"}', 1, 3, '2025-09-16 20:22:46', 'forum/voices/1758079348_1uliEB1obI.webm', NULL, '2025-09-16 20:22:28', '2025-09-16 20:22:46'),
(91, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 60053, \"filename\": \"voice-1758079379711.webm\"}', 1, 3, '2025-09-16 20:23:04', 'forum/voices/1758079379_8YkQ1Krwwp.webm', NULL, '2025-09-16 20:23:00', '2025-09-16 20:23:04'),
(92, 3, '😎😎😎😎😎🖐️🖐️🖐️👋👋👋🐧🐧🐧🍿🍿🍿🍿🍩🍩🍩', 'text', '[]', 1, 3, '2025-09-16 21:23:18', NULL, NULL, '2025-09-16 21:22:32', '2025-09-16 21:23:18'),
(93, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 16583, \"filename\": \"voice-1758083066183.webm\"}', 1, 3, '2025-09-16 21:24:41', 'forum/voices/1758083066_vPn6alcOGW.webm', NULL, '2025-09-16 21:24:26', '2025-09-16 21:24:41'),
(94, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 57155, \"filename\": \"voice-1758083087150.webm\"}', 1, 3, '2025-09-16 21:27:14', 'forum/voices/1758083087_AWXNo7E37R.webm', NULL, '2025-09-16 21:24:47', '2025-09-16 21:27:14'),
(95, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 27209, \"filename\": \"voice-1758083228945.webm\"}', 1, 3, '2025-09-16 21:27:18', 'forum/voices/1758083229_hbfj3CJGLZ.webm', NULL, '2025-09-16 21:27:09', '2025-09-16 21:27:18'),
(96, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 17549, \"filename\": \"voice-1758083298911.webm\"}', 1, 3, '2025-09-16 21:28:24', 'forum/voices/1758083299_TMxObb22tr.webm', NULL, '2025-09-16 21:28:19', '2025-09-16 21:28:24'),
(97, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 70679, \"filename\": \"voice-1758083310794.webm\"}', 1, 3, '2025-09-16 21:28:45', 'forum/voices/1758083311_XNYDzGRiZc.webm', NULL, '2025-09-16 21:28:31', '2025-09-16 21:28:45'),
(98, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 54257, \"filename\": \"voice-1758083504243.webm\"}', 1, 3, '2025-09-16 21:31:55', 'forum/voices/1758083504_FCTWiS8MFF.webm', NULL, '2025-09-16 21:31:44', '2025-09-16 21:31:55'),
(99, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 40733, \"filename\": \"voice-1758083614089.webm\"}', 1, 1, '2025-09-25 21:10:00', 'forum/voices/1758083614_oMeJAIstpl.webm', NULL, '2025-09-16 21:33:34', '2025-09-25 21:10:00'),
(100, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 115115, \"filename\": \"voice-1758084401202.webm\"}', 1, 3, '2025-09-16 21:46:46', 'forum/voices/1758084401_FopXhH5M4y.webm', NULL, '2025-09-16 21:46:41', '2025-09-16 21:46:46'),
(101, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 260015, \"filename\": \"voice-1758089667761.webm\"}', 1, 3, '2025-09-16 23:14:35', 'forum/voices/1758089668_k4uJqI68RF.webm', NULL, '2025-09-16 23:14:29', '2025-09-16 23:14:35'),
(102, 3, NULL, 'contact', '{\"name\": \"werw\", \"phone\": \"+1997878789\", \"country\": \"US\"}', 1, 3, '2025-09-16 23:47:41', NULL, NULL, '2025-09-16 23:47:35', '2025-09-16 23:47:41'),
(103, 3, NULL, 'contact', '{\"name\": \"j\", \"phone\": \"d45\"}', 1, 3, '2025-09-16 23:48:11', NULL, NULL, '2025-09-16 23:48:05', '2025-09-16 23:48:11'),
(104, 3, NULL, 'contact', '{\"name\": \"Adris\", \"phone\": \"+658485378435\", \"country\": \"SG\"}', 1, 3, '2025-09-17 00:00:27', NULL, NULL, '2025-09-16 23:56:54', '2025-09-17 00:00:27'),
(105, 3, NULL, 'contact', '{\"name\": \"dfsds\", \"phone\": \"+62854435\", \"country\": \"ID\"}', 1, 3, '2025-09-17 01:10:08', NULL, NULL, '2025-09-17 00:00:14', '2025-09-17 01:10:08'),
(106, 4, NULL, 'contact', '{\"name\": \"MUHAMMAD ADRISELA\", \"phone\": \"+6285211459523\", \"country\": \"ID\"}', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-17 00:14:47', '2025-09-25 21:10:00'),
(107, 3, NULL, 'contact', '{\"name\": \"Adris\", \"phone\": \"0823123445\"}', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-17 00:20:49', '2025-09-25 21:10:00'),
(108, 3, NULL, 'contact', '{\"name\": \"MUHAMMAD ADRISELA\", \"phone\": \"0823123445\"}', 1, 3, '2025-09-17 01:10:17', NULL, NULL, '2025-09-17 00:23:47', '2025-09-17 01:10:17'),
(109, 3, NULL, 'location', '{\"latitude\": -6.27903396888412, \"longitude\": 106.83004646888412}', 1, 3, '2025-09-17 01:14:50', NULL, NULL, '2025-09-17 01:14:46', '2025-09-17 01:14:50'),
(110, 3, NULL, 'location', '{\"latitude\": -6.279032920085082, \"longitude\": 106.83005168034032}', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-17 01:17:11', '2025-09-25 21:10:00'),
(111, 3, NULL, 'poll', '[]', 1, 3, '2025-09-17 02:09:26', NULL, NULL, '2025-09-17 01:46:17', '2025-09-17 02:09:26'),
(112, 3, NULL, 'poll', '[]', 1, 3, '2025-09-17 02:09:29', NULL, NULL, '2025-09-17 01:52:05', '2025-09-17 02:09:29'),
(113, 3, NULL, 'poll', '[]', 1, 3, '2025-09-18 01:40:18', NULL, NULL, '2025-09-17 02:09:45', '2025-09-18 01:40:18'),
(114, 3, NULL, 'poll', '[]', 1, 3, '2025-09-18 01:40:23', NULL, NULL, '2025-09-17 02:10:37', '2025-09-18 01:40:23'),
(115, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 759044, \"filename\": \"video-1758162357290.mp4\"}', 1, 3, '2025-09-17 19:26:06', 'forum/videos/1758162360_XXBi7zd3X4.mp4', NULL, '2025-09-17 19:26:00', '2025-09-17 19:26:06'),
(116, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 1149812, \"filename\": \"video-1758163456650.mp4\"}', 1, 3, '2025-09-17 19:44:32', 'forum/videos/1758163459_G5BbUirsPQ.mp4', NULL, '2025-09-17 19:44:19', '2025-09-17 19:44:32'),
(117, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 477358, \"filename\": \"video-1758164118373.mp4\"}', 1, 3, '2025-09-17 19:55:25', 'forum/videos/1758164121_yrTKidzOSD.mp4', NULL, '2025-09-17 19:55:21', '2025-09-17 19:55:25'),
(118, 3, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 167279, \"filename\": \"voice-1758164148549.webm\"}', 1, 1, '2025-09-25 21:10:00', 'forum/voices/1758164148_WNRcrLfsdV.webm', NULL, '2025-09-17 19:55:48', '2025-09-25 21:10:00'),
(119, 3, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 1436823, \"filename\": \"video-1758165962145.mp4\"}', 1, 1, '2025-09-25 21:10:00', 'forum/videos/1758165966_JWFmgRGdvx.mp4', NULL, '2025-09-17 20:26:06', '2025-09-25 21:10:00'),
(120, 3, NULL, 'poll', '[]', 1, 3, '2025-09-17 21:23:41', NULL, NULL, '2025-09-17 21:23:33', '2025-09-17 21:23:41'),
(121, 3, NULL, 'poll', '[]', 1, 3, '2025-09-17 21:25:24', NULL, NULL, '2025-09-17 21:25:20', '2025-09-17 21:25:24'),
(122, 3, NULL, 'poll', '[]', 1, 3, '2025-09-17 21:27:56', NULL, NULL, '2025-09-17 21:27:51', '2025-09-17 21:27:56'),
(123, 3, NULL, 'poll', '[]', 1, 3, '2025-09-18 00:16:27', NULL, NULL, '2025-09-18 00:15:52', '2025-09-18 00:16:27'),
(124, 3, NULL, 'poll', '[]', 1, 3, '2025-09-18 01:40:11', NULL, NULL, '2025-09-18 00:39:53', '2025-09-18 01:40:11'),
(125, 3, NULL, 'poll', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-18 01:24:30', '2025-09-25 21:10:00'),
(126, 3, NULL, 'poll', '[]', 1, 3, '2025-09-18 01:42:12', NULL, NULL, '2025-09-18 01:41:50', '2025-09-18 01:42:12'),
(127, 3, NULL, 'poll', '[]', 1, 3, '2025-09-18 01:47:57', NULL, NULL, '2025-09-18 01:47:38', '2025-09-18 01:47:57'),
(128, 3, NULL, 'poll', '[]', 1, 3, '2025-09-18 01:47:51', NULL, NULL, '2025-09-18 01:47:39', '2025-09-18 01:47:51'),
(129, 1, NULL, 'image', '{\"mime\": \"image/jpeg\", \"size\": 57980, \"filename\": \"photo-1758511834048.jpg\"}', 1, 1, '2025-09-25 21:10:00', 'forum/images/1758511837_D8aIFrZW5z.jpg', NULL, '2025-09-21 20:30:38', '2025-09-25 21:10:00'),
(130, 1, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 3150815, \"filename\": \"video-1758511857855.mp4\"}', 1, 1, '2025-09-25 21:10:00', 'forum/videos/1758511860_9bFNFbq7Ml.mp4', NULL, '2025-09-21 20:31:00', '2025-09-25 21:10:00'),
(131, 1, 'sdasd', 'image', '{\"mime\": \"image/png\", \"size\": 456138, \"filename\": \"Screenshot 2025-09-22 091021.png\"}', 1, 1, '2025-09-21 20:34:38', 'forum/images/1758512071_vsPKgTngw5.png', NULL, '2025-09-21 20:34:31', '2025-09-21 20:34:38'),
(132, 1, NULL, 'poll', '[]', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-21 21:31:53', '2025-09-25 21:10:00'),
(133, 1, 'asd', 'text', '[]', 1, 1, '2025-09-25 20:21:33', NULL, NULL, '2025-09-22 20:56:17', '2025-09-25 20:21:33'),
(134, 1, NULL, 'poll', '[]', 1, 1, '2025-09-22 21:01:31', NULL, NULL, '2025-09-22 21:01:23', '2025-09-22 21:01:31'),
(135, 1, 'heloo helooo heloo heloo helooo', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:18:31.138046Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 20:21:38', NULL, NULL, '2025-09-25 20:18:31', '2025-09-25 20:21:38'),
(136, 1, 'heloo helooo heloo heloo helooo', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:18:35.780811Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 20:21:43', NULL, NULL, '2025-09-25 20:18:35', '2025-09-25 20:21:43'),
(137, 1, 'heloo helooo heloo heloo helooo', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:18:41.102950Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 20:21:52', NULL, NULL, '2025-09-25 20:18:41', '2025-09-25 20:21:52'),
(138, 1, 'heloo helooo heloo heloo helooo', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:18:45.878170Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 20:21:47', NULL, NULL, '2025-09-25 20:18:45', '2025-09-25 20:21:47'),
(139, 1, 'heloo helooo heloo heloo helooo', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:18:51.095864Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 20:21:56', NULL, NULL, '2025-09-25 20:18:51', '2025-09-25 20:21:56'),
(140, 1, 'heloo', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:21:05.447792Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 20:22:02', NULL, NULL, '2025-09-25 20:21:05', '2025-09-25 20:22:02'),
(141, 1, 'hsaghdgahgdshada', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:23:05.760273Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-25 20:23:05', '2025-09-25 21:10:00'),
(142, 1, 'as', 'text', '{\"kind\": \"announcement\", \"broadcast_at\": \"2025-09-26T03:26:31.859898Z\", \"is_broadcast\": true}', 1, 1, '2025-09-25 21:10:00', NULL, NULL, '2025-09-25 20:26:31', '2025-09-25 21:10:00'),
(143, 1, 'Selamat Datang di Forum Diskusi RKA', 'text', '[]', 0, NULL, NULL, NULL, NULL, '2025-09-25 21:10:48', '2025-09-25 21:10:48'),
(144, 1, NULL, 'poll', '[]', 1, 1, '2025-09-26 02:01:49', NULL, NULL, '2025-09-26 02:01:42', '2025-09-26 02:01:49'),
(145, 2, 'halo mimin', 'text', '[]', 0, NULL, NULL, NULL, NULL, '2025-09-28 18:22:51', '2025-09-28 18:22:51'),
(146, 2, 'halo mimin', 'text', '[]', 0, NULL, NULL, NULL, NULL, '2025-09-28 18:22:54', '2025-09-28 18:22:54'),
(147, 1, 'tes', 'text', '[]', 0, NULL, NULL, NULL, NULL, '2025-09-28 18:23:50', '2025-09-28 18:23:50'),
(148, 4, 'tes 123', 'text', '[]', 1, 1, '2025-09-30 19:52:59', NULL, NULL, '2025-09-28 18:24:50', '2025-09-30 19:52:59'),
(149, 1, NULL, 'poll', '[]', 1, 1, '2025-09-30 19:09:15', NULL, NULL, '2025-09-30 18:21:32', '2025-09-30 19:09:15'),
(150, 1, NULL, 'poll', '[]', 1, 1, '2025-09-30 19:36:10', NULL, NULL, '2025-09-30 19:17:09', '2025-09-30 19:36:10'),
(151, 1, NULL, 'poll', '[]', 0, NULL, NULL, NULL, NULL, '2025-09-30 19:52:28', '2025-09-30 19:52:28'),
(152, 1, 'halooo', 'text', '[]', 0, NULL, NULL, NULL, NULL, '2025-09-30 20:08:22', '2025-09-30 20:08:22'),
(153, 1, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 48462, \"filename\": \"DFDaplikasiemisikarbon.drawio.png\"}', 0, NULL, NULL, 'forum/images/1759292842_du0PY5zoYV.png', NULL, '2025-09-30 21:27:23', '2025-09-30 21:27:23'),
(154, 1, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 48462, \"filename\": \"DFDaplikasiemisikarbon.drawio.png\"}', 1, 1, '2025-09-30 21:32:05', 'forum/images/1759292844_xGQieXwXMZ.png', NULL, '2025-09-30 21:27:24', '2025-09-30 21:32:05'),
(155, 1, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 31073, \"filename\": \"voice-1759292847600.webm\"}', 0, NULL, NULL, 'forum/voices/1759292848_ZOGCkbCaxN.webm', NULL, '2025-09-30 21:27:28', '2025-09-30 21:27:28'),
(156, 1, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 53291, \"filename\": \"voice-1759292851948.webm\"}', 1, 1, '2025-09-30 21:27:38', 'forum/voices/1759292852_DXXoVjvvtx.webm', NULL, '2025-09-30 21:27:32', '2025-09-30 21:27:38'),
(157, 1, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 255086, \"filename\": \"ERDaplikasiemisikarbon.drawio.png\"}', 1, 1, '2025-09-30 21:32:00', 'forum/images/1759293114_eyZDDnKMOV.png', NULL, '2025-09-30 21:31:54', '2025-09-30 21:32:00'),
(158, 1, 'ini adalah pengumuman pertama', 'text', '{\"broadcast_at\": \"2025-10-01T04:47:43+00:00\", \"is_broadcast\": true}', 1, 1, '2025-09-30 21:48:00', NULL, NULL, '2025-09-30 21:47:43', '2025-09-30 21:48:00'),
(159, 1, NULL, 'poll', '[]', 0, NULL, NULL, NULL, NULL, '2025-10-01 19:12:50', '2025-10-01 19:12:50'),
(160, 1, NULL, 'document', '{\"mime\": \"application/pdf\", \"size\": 90215, \"filename\": \"Rev_KonsepAplikasiemisikarbon_29_9_2025 (1).pdf\"}', 0, NULL, NULL, 'forum/docs/1759371214_3CtvCBcIUk.pdf', NULL, '2025-10-01 19:13:35', '2025-10-01 19:13:35'),
(161, 1, NULL, 'document', '{\"mime\": \"application/pdf\", \"size\": 90215, \"filename\": \"Rev_KonsepAplikasiemisikarbon_29_9_2025 (1).pdf\"}', 1, 1, '2025-10-01 19:13:44', 'forum/docs/1759371216_lTBkFe1Azw.pdf', NULL, '2025-10-01 19:13:36', '2025-10-01 19:13:44'),
(162, 1, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 33971, \"filename\": \"voice-1759378044526.webm\"}', 1, 1, '2025-10-01 21:07:32', 'forum/voices/1759378045_oVIIM7RwJP.webm', NULL, '2025-10-01 21:07:26', '2025-10-01 21:07:32'),
(163, 1, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 45563, \"filename\": \"voice-1759379444302.webm\"}', 1, 1, '2025-10-01 21:30:50', 'forum/voices/1759379444_GQKngooQI0.webm', NULL, '2025-10-01 21:30:44', '2025-10-01 21:30:50'),
(164, 1, NULL, 'poll', '[]', 1, 1, '2025-10-01 21:44:55', NULL, NULL, '2025-10-01 21:31:08', '2025-10-01 21:44:55'),
(165, 1, NULL, 'poll', '[]', 1, 1, '2025-10-01 21:31:16', NULL, NULL, '2025-10-01 21:31:09', '2025-10-01 21:31:16'),
(166, 1, NULL, 'poll', '[]', 1, 1, '2025-10-01 21:44:51', NULL, NULL, '2025-10-01 21:44:46', '2025-10-01 21:44:51'),
(167, 2, NULL, 'poll', '[]', 1, 2, '2025-10-01 23:42:00', NULL, NULL, '2025-10-01 23:41:55', '2025-10-01 23:42:00'),
(168, 1, NULL, 'poll', '[]', 0, NULL, NULL, NULL, NULL, '2025-10-01 23:55:22', '2025-10-01 23:55:22'),
(169, 1, NULL, 'poll', '[]', 0, NULL, NULL, NULL, NULL, '2025-10-02 00:31:20', '2025-10-02 00:31:20'),
(170, 2, NULL, 'voice', '{\"mime\": \"audio/webm\", \"size\": 34937, \"filename\": \"voice-1759394271860.webm\"}', 1, 2, '2025-10-02 01:46:21', 'forum/voices/1759394272_bbcunzksAN.webm', NULL, '2025-10-02 01:37:52', '2025-10-02 01:46:21'),
(171, 2, NULL, 'document', '{\"mime\": \"application/pdf\", \"size\": 90215, \"filename\": \"Rev_KonsepAplikasiemisikarbon_29_9_2025 (1).pdf\"}', 1, 1, '2025-10-02 01:45:44', 'forum/docs/1759394300_OGQo5rhXHs.pdf', NULL, '2025-10-02 01:38:20', '2025-10-02 01:45:44'),
(172, 4, NULL, 'document', '{\"mime\": \"application/pdf\", \"size\": 3795562, \"filename\": \"Social Media Policy Presentation.pdf\"}', 0, NULL, NULL, 'forum/docs/1760493067_ogZD04wpI3.pdf', NULL, '2025-10-14 18:51:08', '2025-10-14 18:51:08'),
(173, 4, NULL, 'image', '{\"mime\": \"image/png\", \"size\": 152926, \"filename\": \"FlowchartModeratorForum.drawio (1).png\"}', 0, NULL, NULL, 'forum/images/1760493882_ZFKo1KUoJo.png', NULL, '2025-10-14 19:04:42', '2025-10-14 19:04:42'),
(174, 4, NULL, 'image', '{\"mime\": \"image/jpeg\", \"size\": 66276, \"filename\": \"photo-1760495004633.jpg\"}', 0, NULL, NULL, 'forum/images/1760495015_6LskBsgVhe.jpg', NULL, '2025-10-14 19:23:35', '2025-10-14 19:23:35'),
(175, 4, NULL, 'video', '{\"mime\": \"video/mp4\", \"size\": 2716912, \"filename\": \"video-1760495071845.mp4\"}', 0, NULL, NULL, 'forum/videos/1760495083_3O2wuURM7I.mp4', NULL, '2025-10-14 19:24:43', '2025-10-14 19:24:43'),
(176, 4, NULL, 'location', '{\"latitude\": -6.27902757524012, \"longitude\": 106.8300370136308}', 0, NULL, NULL, NULL, NULL, '2025-10-14 19:35:30', '2025-10-14 19:35:30'),
(177, 4, NULL, 'poll', '[]', 0, NULL, NULL, NULL, NULL, '2025-10-14 19:49:18', '2025-10-14 19:49:18');

-- --------------------------------------------------------

--
-- Table structure for table `forum_message_reactions`
--

CREATE TABLE `forum_message_reactions` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `emoji` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_message_reactions`
--

INSERT INTO `forum_message_reactions` (`id`, `message_id`, `user_id`, `emoji`, `created_at`, `updated_at`) VALUES
(164, 37, 2, '👍', '2025-09-11 01:16:38', '2025-09-11 01:16:38'),
(165, 36, 2, '❤️', '2025-09-11 01:16:41', '2025-09-11 01:16:41'),
(166, 37, 3, '😂', '2025-09-11 01:17:03', '2025-09-11 01:17:03'),
(169, 31, 3, '❤️', '2025-09-11 01:33:11', '2025-09-11 01:33:11'),
(171, 17, 3, '❤️', '2025-09-11 01:47:50', '2025-09-11 01:47:50'),
(172, 17, 3, '😢', '2025-09-11 01:47:54', '2025-09-11 01:47:54'),
(173, 36, 3, '😮', '2025-09-11 02:49:25', '2025-09-11 02:49:25'),
(174, 31, 3, '🙏', '2025-09-11 02:52:15', '2025-09-11 02:52:15'),
(175, 41, 3, '❤️', '2025-09-15 00:27:38', '2025-09-15 00:27:38'),
(177, 169, 2, '❤️', '2025-10-02 01:46:24', '2025-10-02 01:46:24'),
(178, 160, 2, '👍', '2025-10-02 01:46:29', '2025-10-02 01:46:29'),
(179, 169, 1, '😂', '2025-10-02 01:48:53', '2025-10-02 01:48:53'),
(180, 168, 4, '❤️', '2025-10-14 18:49:35', '2025-10-14 18:49:35'),
(181, 168, 4, '😂', '2025-10-14 18:49:38', '2025-10-14 18:49:38');

-- --------------------------------------------------------

--
-- Table structure for table `forum_polls`
--

CREATE TABLE `forum_polls` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint UNSIGNED NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json NOT NULL,
  `multiple_choice` tinyint(1) DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_polls`
--

INSERT INTO `forum_polls` (`id`, `message_id`, `question`, `options`, `multiple_choice`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 111, 'pilih ayam atau bebek??', '[\"ayam\", \"bebek\"]', 1, NULL, '2025-09-17 01:46:17', '2025-09-17 01:46:17'),
(2, 112, 's', '[\"hdhfgs\", \"dhjfs\"]', 0, NULL, '2025-09-17 01:52:05', '2025-09-17 01:52:05'),
(3, 113, 'kamu pilih apaa', '[\"nasgor\", \"mie ayam\"]', 1, NULL, '2025-09-17 02:09:45', '2025-09-17 02:09:45'),
(4, 114, 'sda', '[\"ikan\", \"auahs\"]', 0, NULL, '2025-09-17 02:10:37', '2025-09-17 02:10:37'),
(5, 120, 'a', '[\"s\", \"2\"]', 1, NULL, '2025-09-17 21:23:33', '2025-09-17 21:23:33'),
(6, 121, '1', '[\"1\", \"2\"]', 1, NULL, '2025-09-17 21:25:20', '2025-09-17 21:25:20'),
(7, 122, '1', '[\"2\", \"3\"]', 1, NULL, '2025-09-17 21:27:51', '2025-09-17 21:27:51'),
(8, 123, '1', '[\"2\", \"3\"]', 0, NULL, '2025-09-18 00:15:52', '2025-09-18 00:15:52'),
(9, 124, '12', '[\"32\", \"43\"]', 1, NULL, '2025-09-18 00:39:53', '2025-09-18 00:39:53'),
(10, 125, 'makanan', '[\"ayam\", \"baso\", \"sate\", \"mie ayam\"]', 1, NULL, '2025-09-18 01:24:30', '2025-09-18 01:24:30'),
(11, 126, 'pilih mie ayam apa baso??', '[\"mie ayam\", \"baso\", \"ikan\", \"nasgor\", \"kewetiaw\", \"bebek\", \"pohon\", \"bambu\", \"osis\", \"labu\"]', 0, NULL, '2025-09-18 01:41:50', '2025-09-18 01:41:50'),
(12, 127, '1', '[\"112\", \"2\", \"3\", \"4\", \"5\", \"6\", \"7\", \"8\", \"9\", \"10\"]', 1, NULL, '2025-09-18 01:47:38', '2025-09-18 01:47:38'),
(13, 128, '1', '[\"112\", \"2\", \"3\", \"4\", \"5\", \"6\", \"7\", \"8\", \"9\", \"10\"]', 1, NULL, '2025-09-18 01:47:39', '2025-09-18 01:47:39'),
(14, 132, 'Bahasa pemrograman yang ingin dipelajari/tambah dalam', '[\"python\", \"java\", \"Rust\", \"JS\"]', 1, NULL, '2025-09-21 21:31:53', '2025-09-21 21:31:53'),
(15, 134, '2', '[\"3\", \"3\"]', 1, NULL, '2025-09-22 21:01:23', '2025-09-22 21:01:23'),
(16, 144, 'df', '[\"1\", \"2\"]', 1, NULL, '2025-09-26 02:01:42', '2025-09-26 02:01:42'),
(17, 149, 'Bahasa pemrograman yang ingin dipelajari', '[\"php\", \"next.js\"]', 0, NULL, '2025-09-30 18:21:32', '2025-09-30 18:21:32'),
(18, 150, 'php atau next js', '[\"php\", \"next.js\"]', 0, NULL, '2025-09-30 19:17:09', '2025-09-30 19:17:09'),
(19, 151, 'css atau html', '[\"css\", \"html\"]', 0, NULL, '2025-09-30 19:52:28', '2025-09-30 19:52:28'),
(20, 159, 'haiii kamu jago di apa', '[\"design\", \"coding\"]', 0, NULL, '2025-10-01 19:12:50', '2025-10-01 19:12:50'),
(21, 164, 'a', '[\"1\", \"2\"]', 1, NULL, '2025-10-01 21:31:08', '2025-10-01 21:31:08'),
(22, 165, 'a', '[\"1\", \"2\"]', 1, NULL, '2025-10-01 21:31:09', '2025-10-01 21:31:09'),
(23, 166, '1', '[\"1\", \"2\"]', 1, NULL, '2025-10-01 21:44:46', '2025-10-01 21:44:46'),
(24, 167, 'a', '[\"a\", \"a\"]', 1, NULL, '2025-10-01 23:41:55', '2025-10-01 23:41:55'),
(25, 168, '11', '[\"1\", \"1\"]', 1, NULL, '2025-10-01 23:55:22', '2025-10-01 23:55:22'),
(26, 169, '1', '[\"1\", \"1\"]', 1, NULL, '2025-10-02 00:31:20', '2025-10-02 00:31:20'),
(27, 177, 'Lebih enak php native atau pakai laravel', '[\"php native\", \"laravel\"]', 0, NULL, '2025-10-14 19:49:18', '2025-10-14 19:49:18');

-- --------------------------------------------------------

--
-- Table structure for table `forum_poll_votes`
--

CREATE TABLE `forum_poll_votes` (
  `id` bigint UNSIGNED NOT NULL,
  `poll_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `option_index` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_poll_votes`
--

INSERT INTO `forum_poll_votes` (`id`, `poll_id`, `user_id`, `option_index`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 0, '2025-09-17 01:46:27', '2025-09-17 01:46:27'),
(2, 1, 3, 1, '2025-09-17 01:46:27', '2025-09-17 01:46:27'),
(12, 2, 3, 1, '2025-09-17 02:04:21', '2025-09-17 02:04:21'),
(22, 5, 3, 0, '2025-09-17 21:23:36', '2025-09-17 21:23:36'),
(23, 5, 3, 1, '2025-09-17 21:23:38', '2025-09-17 21:23:38'),
(26, 8, 3, 0, '2025-09-18 00:16:06', '2025-09-18 00:16:06'),
(59, 3, 3, 1, '2025-09-18 01:31:11', '2025-09-18 01:31:11'),
(60, 3, 3, 0, '2025-09-18 01:31:13', '2025-09-18 01:31:13'),
(61, 4, 3, 0, '2025-09-18 01:31:16', '2025-09-18 01:31:16'),
(64, 10, 3, 0, '2025-09-18 01:35:22', '2025-09-18 01:35:22'),
(69, 10, 3, 2, '2025-09-18 01:39:54', '2025-09-18 01:39:54'),
(70, 9, 3, 1, '2025-09-18 01:40:04', '2025-09-18 01:40:04'),
(71, 9, 3, 0, '2025-09-18 01:40:07', '2025-09-18 01:40:07'),
(72, 10, 3, 3, '2025-09-18 01:40:31', '2025-09-18 01:40:31'),
(75, 11, 3, 6, '2025-09-18 01:42:01', '2025-09-18 01:42:01'),
(76, 12, 3, 7, '2025-09-18 01:47:42', '2025-09-18 01:47:42'),
(77, 13, 3, 8, '2025-09-18 01:47:44', '2025-09-18 01:47:44'),
(78, 10, 1, 0, '2025-09-21 20:31:50', '2025-09-21 20:31:50'),
(79, 10, 1, 2, '2025-09-21 20:31:52', '2025-09-21 20:31:52'),
(81, 10, 1, 1, '2025-09-21 20:31:57', '2025-09-21 20:31:57'),
(84, 14, 1, 1, '2025-09-21 21:31:58', '2025-09-21 21:31:58'),
(88, 14, 1, 3, '2025-09-21 21:37:10', '2025-09-21 21:37:10'),
(95, 16, 1, 1, '2025-09-26 02:01:44', '2025-09-26 02:01:44'),
(96, 16, 1, 0, '2025-09-26 02:01:45', '2025-09-26 02:01:45'),
(97, 17, 1, 0, '2025-09-30 18:21:34', '2025-09-30 18:21:34'),
(98, 18, 1, 0, '2025-09-30 19:17:12', '2025-09-30 19:17:12'),
(113, 26, 2, 0, '2025-10-04 01:47:55', '2025-10-04 01:47:55'),
(122, 25, 2, 0, '2025-10-04 01:50:14', '2025-10-04 01:50:14'),
(125, 20, 2, 0, '2025-10-04 01:50:22', '2025-10-04 01:50:22'),
(128, 19, 2, 1, '2025-10-04 01:50:33', '2025-10-04 01:50:33'),
(130, 25, 2, 1, '2025-10-04 01:50:46', '2025-10-04 01:50:46'),
(145, 25, 1, 0, '2025-10-05 19:31:03', '2025-10-05 19:31:03'),
(158, 26, 1, 1, '2025-10-05 19:38:05', '2025-10-05 19:38:05'),
(160, 26, 1, 0, '2025-10-05 19:38:10', '2025-10-05 19:38:10'),
(161, 19, 1, 1, '2025-10-05 19:38:20', '2025-10-05 19:38:20'),
(162, 25, 1, 1, '2025-10-05 19:44:02', '2025-10-05 19:44:02'),
(165, 20, 1, 0, '2025-10-05 19:46:15', '2025-10-05 19:46:15'),
(167, 26, 2, 1, '2025-10-12 21:47:39', '2025-10-12 21:47:39'),
(168, 25, 4, 0, '2025-10-14 18:48:24', '2025-10-14 18:48:24'),
(169, 26, 4, 0, '2025-10-14 18:48:31', '2025-10-14 18:48:31'),
(173, 20, 4, 1, '2025-10-14 18:49:03', '2025-10-14 18:49:03'),
(174, 27, 4, 0, '2025-10-14 19:49:33', '2025-10-14 19:49:33'),
(175, 27, 2, 0, '2025-10-14 19:56:50', '2025-10-14 19:56:50');

-- --------------------------------------------------------

--
-- Table structure for table `forum_settings`
--

CREATE TABLE `forum_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `is_open` tinyint(1) DEFAULT '1',
  `max_users` int DEFAULT '100',
  `allow_attachments` tinyint(1) DEFAULT '1',
  `allow_polls` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `allow_voice` tinyint(1) NOT NULL DEFAULT '0',
  `max_attachment_kb` int DEFAULT NULL,
  `default_poll_duration_minutes` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_settings`
--

INSERT INTO `forum_settings` (`id`, `is_open`, `max_users`, `allow_attachments`, `allow_polls`, `created_at`, `updated_at`, `allow_voice`, `max_attachment_kb`, `default_poll_duration_minutes`) VALUES
(2, 1, NULL, 0, 0, '2025-10-01 23:41:25', '2025-10-14 19:53:54', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(2, 'default', '{\"uuid\":\"8f1fa8a3-29dd-4474-af84-6f994a16e66f\",\"displayName\":\"App\\\\Events\\\\ForumSettingsUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:31:\\\"App\\\\Events\\\\ForumSettingsUpdated\\\":1:{s:8:\\\"settings\\\";a:7:{s:7:\\\"is_open\\\";b:1;s:17:\\\"allow_attachments\\\";b:1;s:11:\\\"allow_polls\\\";b:1;s:11:\\\"allow_voice\\\";b:1;s:9:\\\"max_users\\\";i:100;s:17:\\\"max_attachment_kb\\\";i:10240;s:29:\\\"default_poll_duration_minutes\\\";i:600;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1759715052, 1759715052),
(3, 'default', '{\"uuid\":\"1698b6df-bd89-4a48-9b35-b9370e24bb2c\",\"displayName\":\"App\\\\Events\\\\ForumSettingsUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:31:\\\"App\\\\Events\\\\ForumSettingsUpdated\\\":1:{s:8:\\\"settings\\\";a:7:{s:7:\\\"is_open\\\";b:1;s:17:\\\"allow_attachments\\\";b:1;s:11:\\\"allow_polls\\\";b:1;s:11:\\\"allow_voice\\\";b:1;s:9:\\\"max_users\\\";i:10;s:17:\\\"max_attachment_kb\\\";i:1024;s:29:\\\"default_poll_duration_minutes\\\";i:60;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1759715064, 1759715064),
(4, 'default', '{\"uuid\":\"6818eaa2-91b6-4dd1-85d4-2bd99ec252c6\",\"displayName\":\"App\\\\Events\\\\ForumSettingsUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:31:\\\"App\\\\Events\\\\ForumSettingsUpdated\\\":1:{s:8:\\\"settings\\\";a:7:{s:7:\\\"is_open\\\";b:1;s:17:\\\"allow_attachments\\\";b:1;s:11:\\\"allow_polls\\\";b:1;s:11:\\\"allow_voice\\\";b:1;s:9:\\\"max_users\\\";i:100;s:17:\\\"max_attachment_kb\\\";i:10240;s:29:\\\"default_poll_duration_minutes\\\";i:600;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1759715077, 1759715077),
(5, 'default', '{\"uuid\":\"3ccd4b4e-5ec7-4ef2-b1df-23034ab285c9\",\"displayName\":\"App\\\\Events\\\\ForumSettingsUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:31:\\\"App\\\\Events\\\\ForumSettingsUpdated\\\":1:{s:8:\\\"settings\\\";a:7:{s:7:\\\"is_open\\\";b:1;s:17:\\\"allow_attachments\\\";b:1;s:11:\\\"allow_polls\\\";b:1;s:11:\\\"allow_voice\\\";b:1;s:9:\\\"max_users\\\";i:100;s:17:\\\"max_attachment_kb\\\";i:10240;s:29:\\\"default_poll_duration_minutes\\\";i:600;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1759716541, 1759716541),
(6, 'default', '{\"uuid\":\"52d6fcd8-b6a2-42b2-b789-51d38669dc22\",\"displayName\":\"App\\\\Events\\\\ForumSettingsUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:31:\\\"App\\\\Events\\\\ForumSettingsUpdated\\\":1:{s:8:\\\"settings\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:23:\\\"App\\\\Models\\\\ForumSetting\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1760496976, 1760496976),
(7, 'default', '{\"uuid\":\"a4a1a221-a4d9-44bb-9128-2caf9dcb565a\",\"displayName\":\"App\\\\Events\\\\ForumSettingsUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:31:\\\"App\\\\Events\\\\ForumSettingsUpdated\\\":1:{s:8:\\\"settings\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:23:\\\"App\\\\Models\\\\ForumSetting\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1760496977, 1760496977);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2025_08_22_093042_add_two_factor_columns_to_users_table', 1),
(3, '2025_08_25_013236_create_forum_messages_table', 1),
(4, '2025_08_25_025419_create_blog_posts_table', 2),
(5, '2025_08_25_025445_create_blog_comments_table', 2),
(6, '2025_08_25_043325_add_role_id_to_users_table', 3),
(7, '2025_08_26_040505_add_role_id_to_users_table', 4),
(8, '2025_08_26_074358_create_jobs_table', 5),
(9, '2025_08_27_044349_create_categories_table', 6),
(10, '2025_08_27_091403_create_settings_table', 7),
(11, '2025_09_04_000000_create_forum_message_reactions', 8),
(12, '2025_09_11_042934_create_personal_access_tokens_table', 9),
(13, '2025_01_01_000000_create_blog_views_table', 10),
(14, '2025_09_23_000001_add_views_count_to_blog_posts', 10),
(15, '2025_10_02_020842_add_forum_settings_columns', 11);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('keciltikus29@gmail.com', '$2y$12$x3KCaHoWIrnHhYFTA2r38uBkAhSoTkIUprnTchfSSUQlLcC.rI2cW', '2025-09-21 18:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'PT Rekan Kinerja Abadi', NULL, NULL),
(2, 'maintenance_mode', '0', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `role_id` tinyint UNSIGNED NOT NULL DEFAULT '2',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'RKA', 'admin@gmail.com', NULL, '$2y$12$Q1GSMcS70MyubT538XUMFOGRUvD.DiEXkdFOAQ.brD3SO5s3tw20.', NULL, NULL, NULL, '7qSGvnMmPPNKVf7hOqiOb6RBfzAqhj3m2SbSMzjhmfjLztLDtppKqoWnihC6', NULL, NULL, '2025-08-24 20:13:02', '2025-08-26 19:11:48'),
(2, 2, 'User', 'user@gmail.com', NULL, '$2y$12$BlQii9Q9PsCP.aMSzT.ZoeEmArpIZu3bUIHcCCZVmoJ73RI.9LJgG', NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-24 20:15:41', '2025-08-24 20:15:41'),
(3, 3, 'Moderator Forum', 'moderator@gmail.com', '2025-08-26 19:11:48', '$2y$12$fVtbDSCXsetLiFusQI78du9WzebKhz/Y37/O.qfNOwd2D/ckNGwbG', NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-26 19:11:48', '2025-08-26 19:11:48'),
(4, 2, 'Adris', 'keciltikus29@gmail.com', '2025-09-11 18:10:47', '$2y$12$koM3FmV4MsUPqNT6SN8oXOKSP8N/0JGMnqWf.pOkK2YRBKG5r6LhC', NULL, NULL, NULL, 'KzHKkr6gO6EvDNybFH0IbQDyHXZgqtfILlGTFIRaOIVRa1DNMnxk21haD3O1', NULL, NULL, '2025-09-11 18:10:47', '2025-09-11 18:42:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_comments_blog_post_id_foreign` (`blog_post_id`),
  ADD KEY `blog_comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_posts_slug_unique` (`slug`),
  ADD KEY `blog_posts_user_id_foreign` (`user_id`),
  ADD KEY `blog_posts_category_id_foreign` (`category_id`);

--
-- Indexes for table `blog_views`
--
ALTER TABLE `blog_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_views_blog_post_id_foreign` (`blog_post_id`),
  ADD KEY `blog_views_user_id_foreign` (`user_id`),
  ADD KEY `blog_views_session_id_index` (`session_id`),
  ADD KEY `blog_views_ip_index` (`ip`),
  ADD KEY `blog_views_viewed_at_index` (`viewed_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `forum_bans`
--
ALTER TABLE `forum_bans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_bans_user_id_foreign` (`user_id`),
  ADD KEY `forum_bans_banned_by_foreign` (`banned_by`);

--
-- Indexes for table `forum_messages`
--
ALTER TABLE `forum_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_messages_user_id_foreign` (`user_id`),
  ADD KEY `forum_messages_reply_to_id_foreign` (`reply_to_id`);

--
-- Indexes for table `forum_message_reactions`
--
ALTER TABLE `forum_message_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `forum_message_reactions_message_id_user_id_emoji_unique` (`message_id`,`user_id`,`emoji`),
  ADD KEY `forum_message_reactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `forum_polls`
--
ALTER TABLE `forum_polls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_polls_message_id_foreign` (`message_id`);

--
-- Indexes for table `forum_poll_votes`
--
ALTER TABLE `forum_poll_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_poll_option` (`poll_id`,`user_id`,`option_index`),
  ADD KEY `forum_poll_votes_poll_id_foreign` (`poll_id`),
  ADD KEY `forum_poll_votes_user_id_foreign` (`user_id`);

--
-- Indexes for table `forum_settings`
--
ALTER TABLE `forum_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `blog_views`
--
ALTER TABLE `blog_views`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_bans`
--
ALTER TABLE `forum_bans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `forum_messages`
--
ALTER TABLE `forum_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `forum_message_reactions`
--
ALTER TABLE `forum_message_reactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `forum_polls`
--
ALTER TABLE `forum_polls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `forum_poll_votes`
--
ALTER TABLE `forum_poll_votes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `forum_settings`
--
ALTER TABLE `forum_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD CONSTRAINT `blog_comments_blog_post_id_foreign` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `blog_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_views`
--
ALTER TABLE `blog_views`
  ADD CONSTRAINT `blog_views_blog_post_id_foreign` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_views_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `forum_bans`
--
ALTER TABLE `forum_bans`
  ADD CONSTRAINT `forum_bans_banned_by_foreign` FOREIGN KEY (`banned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_bans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_messages`
--
ALTER TABLE `forum_messages`
  ADD CONSTRAINT `forum_messages_reply_to_id_foreign` FOREIGN KEY (`reply_to_id`) REFERENCES `forum_messages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `forum_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_message_reactions`
--
ALTER TABLE `forum_message_reactions`
  ADD CONSTRAINT `forum_message_reactions_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `forum_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_message_reactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_polls`
--
ALTER TABLE `forum_polls`
  ADD CONSTRAINT `forum_polls_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `forum_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_poll_votes`
--
ALTER TABLE `forum_poll_votes`
  ADD CONSTRAINT `forum_poll_votes_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `forum_polls` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_poll_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
