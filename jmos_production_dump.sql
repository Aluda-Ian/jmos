-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 03:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jmos`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` varchar(50) NOT NULL DEFAULT 'meeting',
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `attendees` text DEFAULT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'confirmed',
  `related_type` varchar(255) DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `thread_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_participants`
--

CREATE TABLE `chat_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `thread_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `last_read_at` datetime DEFAULT NULL,
  `notified_initial_email` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_participants`
--

INSERT INTO `chat_participants` (`id`, `thread_id`, `user_id`, `last_read_at`, `notified_initial_email`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-09-11 13:32:06', 1, '2026-09-11 10:20:14', '2026-09-11 10:32:06'),
(2, 1, 2, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(3, 1, 3, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(4, 1, 4, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(5, 1, 5, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(6, 1, 6, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(7, 1, 7, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(8, 2, 1, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(9, 2, 2, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(10, 2, 3, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(11, 2, 4, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(12, 2, 5, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(13, 2, 6, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(14, 2, 7, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(15, 3, 1, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(16, 3, 2, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(17, 3, 3, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(18, 3, 4, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(19, 3, 5, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(20, 3, 6, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(21, 3, 7, '2026-09-11 13:20:14', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(22, 4, 1, '2026-09-11 13:32:10', 1, '2026-09-11 10:32:05', '2026-09-11 10:32:10'),
(23, 4, 2, NULL, 0, '2026-09-11 10:32:05', '2026-09-11 10:32:05'),
(24, 5, 1, '2026-09-11 13:32:11', 1, '2026-09-11 10:32:07', '2026-09-11 10:32:11'),
(25, 5, 3, NULL, 0, '2026-09-11 10:32:07', '2026-09-11 10:32:07'),
(26, 6, 1, '2026-09-11 13:32:49', 1, '2026-09-11 10:32:09', '2026-09-11 10:32:49'),
(27, 6, 4, NULL, 0, '2026-09-11 10:32:09', '2026-09-11 10:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `chat_threads`
--

CREATE TABLE `chat_threads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'direct',
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_threads`
--

INSERT INTO `chat_threads` (`id`, `type`, `title`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'group', '#general', 'General team discussion, announcements & all-hands updates.', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(2, 'group', '#production', 'Shoots, camera gear, on-location crew coordination & editing.', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(3, 'group', '#client-projects', 'Live client deliverables, reviews, feedback & status briefs.', 1, '2026-09-11 10:20:14', '2026-09-11 10:20:14'),
(4, 'direct', NULL, NULL, 1, '2026-09-11 10:32:05', '2026-09-11 10:32:05'),
(5, 'direct', NULL, NULL, 1, '2026-09-11 10:32:07', '2026-09-11 10:32:07'),
(6, 'direct', NULL, NULL, 1, '2026-09-11 10:32:09', '2026-09-11 10:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_type` varchar(255) NOT NULL DEFAULT 'Direct',
  `contact_person` varchar(255) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `projects` int(11) NOT NULL DEFAULT 0,
  `service` varchar(255) DEFAULT NULL,
  `project_status` varchar(255) NOT NULL DEFAULT 'Active',
  `project_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `stage` varchar(255) NOT NULL DEFAULT 'lead',
  `value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `meta_text` varchar(255) DEFAULT NULL,
  `is_won` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deals`
--

INSERT INTO `deals` (`id`, `title`, `client_name`, `stage`, `value`, `meta_text`, `is_won`, `created_at`, `updated_at`) VALUES
(1, 'Web Devine - Promo Videos', 'Web Devine', 'lead', 50000.00, 'KES 50K', 0, '2026-09-11 09:56:48', '2026-09-11 09:56:48');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `project` varchar(255) NOT NULL DEFAULT 'overhead',
  `amount` decimal(12,2) NOT NULL,
  `etr` varchar(255) NOT NULL DEFAULT 'na',
  `date` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_settings`
--

CREATE TABLE `finance_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `numeric_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `text_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finance_settings`
--

INSERT INTO `finance_settings` (`id`, `key`, `numeric_value`, `text_value`, `created_at`, `updated_at`) VALUES
(1, 'brought_forward', 0.00, 'Initial Account Balance', '2026-09-11 09:51:50', '2026-09-11 10:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `client` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Deposit 60%',
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(255) DEFAULT NULL,
  `etims` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'Sent',
  `due_date` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_no`, `client`, `type`, `amount`, `method`, `etims`, `status`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 'JM-0146', 'Moyo Honey', 'Balance 40%', 12334.00, 'M-Pesa', 1, 'Paid', 'Sep 15', '2026-09-11 10:27:49', '2026-09-11 10:27:59');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_11_115657_create_personal_access_tokens_table', 1),
(5, '2026_09_11_120000_create_clients_table', 1),
(6, '2026_09_11_120001_create_projects_table', 1),
(7, '2026_09_11_120002_create_deals_table', 1),
(8, '2026_09_11_120003_create_tasks_table', 1),
(9, '2026_09_11_120004_create_invoices_table', 1),
(10, '2026_09_11_120005_create_expenses_table', 1),
(11, '2026_09_11_120006_create_service_recipes_table', 1),
(12, '2026_09_11_120007_create_finance_settings_table', 1),
(13, '2026_09_11_160000_create_system_settings_table', 2),
(14, '2026_09_11_160001_create_calendar_events_table', 2),
(15, '2026_09_11_170000_create_chat_tables', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'jmos_api_token', 'b5f64af56dd426f424c40d2b81e39d71fd28696cf0fccceb478a8ef2e72c9228', '[\"*\"]', NULL, NULL, '2026-09-11 09:52:54', '2026-09-11 09:52:54'),
(2, 'App\\Models\\User', 1, 'jmos_api_token', '9ab8c97d5e70006df6193a1218d2fd085058ddf5c8e527224ba89bd8a7519d3c', '[\"*\"]', NULL, NULL, '2026-09-11 10:00:31', '2026-09-11 10:00:31'),
(3, 'App\\Models\\User', 1, 'jmos_api_token', '31407fcd070461bd5a174f9d0ea6313fae0de41258b6cec8ed64d894188c3048', '[\"*\"]', NULL, NULL, '2026-09-11 10:06:45', '2026-09-11 10:06:45'),
(4, 'App\\Models\\User', 1, 'jmos_api_token', 'f7449002a0f0d5ce7019297b2bea26387f4ee3048651fea64d90ba6693b54935', '[\"*\"]', NULL, NULL, '2026-09-11 10:31:15', '2026-09-11 10:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `client` varchar(255) NOT NULL,
  `project_type` varchar(255) DEFAULT NULL,
  `project_manager` varchar(255) DEFAULT NULL,
  `stage` varchar(255) NOT NULL DEFAULT 'brief',
  `status` varchar(255) NOT NULL DEFAULT 'On track',
  `priority` varchar(255) NOT NULL DEFAULT 'Medium',
  `deadline` varchar(255) DEFAULT NULL,
  `budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `progress_pct` int(11) NOT NULL DEFAULT 0,
  `waiting_on` varchar(255) NOT NULL DEFAULT 'us',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_recipes`
--

CREATE TABLE `service_recipes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `stages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`stages`)),
  `deliverables` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_recipes`
--

INSERT INTO `service_recipes` (`id`, `name`, `code`, `stages`, `deliverables`, `created_at`, `updated_at`) VALUES
(1, 'Brand film', 'BF', '[\"brief\",\"concept\",\"pre-pro\",\"shoot\",\"edit\",\"color\",\"review\",\"delivery\"]', 'Final film + 30s cutdowns', '2026-09-11 09:51:50', '2026-09-11 09:51:50'),
(2, 'Documentary', 'DC', '[\"brief\",\"concept\",\"pre-pro\",\"shoot\",\"edit\",\"color\",\"review\",\"delivery\"]', 'Final film + 30s cutdowns', '2026-09-11 09:51:50', '2026-09-11 09:51:50'),
(3, 'Social media reels', 'SR', '[\"brief\",\"concept\",\"pre-pro\",\"shoot\",\"edit\",\"color\",\"review\",\"delivery\"]', '1-minute reels', '2026-09-11 09:51:50', '2026-09-11 09:51:50'),
(4, 'Corporate photography', 'CP', '[\"brief\",\"concept\",\"pre-pro\",\"shoot\",\"edit\",\"review\",\"delivery\"]', 'Edited photos', '2026-09-11 09:51:50', '2026-09-11 09:51:50'),
(5, 'Event coverage', 'EC', '[\"brief\",\"pre-pro\",\"shoot\",\"edit\",\"review\",\"delivery\"]', '3-min highlight + edited photos', '2026-09-11 09:51:50', '2026-09-11 09:51:50'),
(6, 'Podcast production', 'PP', '[\"brief\",\"concept\",\"pre-pro\",\"shoot\",\"edit\",\"review\",\"delivery\"]', 'Trailer + teaser + full episode', '2026-09-11 09:51:50', '2026-09-11 09:51:50'),
(7, 'Livestream', 'LS', '[\"brief\",\"pre-pro\",\"shoot\",\"delivery\"]', 'Live stream (YouTube/FB/Zoom)', '2026-09-11 09:51:50', '2026-09-11 09:51:50'),
(8, 'Social media management', 'SM', '[\"brief\",\"concept\",\"content calendar\",\"review\",\"publish\"]', 'Content calendars + assets', '2026-09-11 09:51:50', '2026-09-11 09:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
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
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('aSvPf6Acczy4v65Pkh1xjb43Dcf8Yk3AmYXuzBtH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJIMGpXbjhORTVjd29sR0poZVBYQlhjbWJvZ1FWWmg3N3RRMG1ETnlhIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9idWRnZXQtY2FsY3VsYXRvciIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1789133469);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `is_secret` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `group`, `key`, `value`, `is_secret`, `created_at`, `updated_at`) VALUES
(1, 'smtp', 'mail_host', 'mail.jeotamedia.co.ke', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(2, 'smtp', 'mail_port', '587', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(3, 'smtp', 'mail_username', 'jmos@jeotamedia.co.ke', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(4, 'smtp', 'mail_password', '@Munangwe212', 1, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(5, 'smtp', 'mail_encryption', 'tls', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(6, 'smtp', 'mail_from_address', 'jmos@jeotamedia.co.ke', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(7, 'smtp', 'mail_from_name', 'JMOS — Jeota Media', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(8, 'google_calendar', 'google_client_id', '', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(9, 'google_calendar', 'google_client_secret', '', 1, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(10, 'google_calendar', 'google_calendar_id', 'primary', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(11, 'google_calendar', 'google_api_key', '', 1, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(12, 'google_calendar', 'google_sync_enabled', '1', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(13, 'general', 'company_name', 'Jeota Media Ltd', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(14, 'general', 'currency', 'KES', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22'),
(15, 'general', 'timezone', 'Africa/Nairobi', 0, '2026-09-11 09:57:22', '2026-09-11 09:57:22');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `stage` varchar(255) NOT NULL DEFAULT 'todo',
  `assigned_to` varchar(255) DEFAULT NULL,
  `assigned_initials` varchar(255) DEFAULT NULL,
  `assigned_color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Team',
  `role` varchar(255) NOT NULL DEFAULT 'team',
  `type` varchar(255) NOT NULL DEFAULT 'Full-time',
  `pay` varchar(255) NOT NULL DEFAULT '—',
  `color` varchar(255) DEFAULT NULL,
  `initials` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `title`, `role`, `type`, `pay`, `color`, `initials`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Barny Kiome', 'barny@jeotamedia.co.ke', NULL, '$2y$12$tePHAGRu6GcSHijTY7GHpeCtotPKhtrq1gD6FyuUtT5.8vlTEtx0u', 'Founder & Executive Producer', 'owner', 'Full-time', '60,000/mo', '#C52523', 'BK', NULL, '2026-09-11 09:51:48', '2026-09-11 10:20:12'),
(2, 'Matthew Muange', 'matthew@jeotamedia.co.ke', NULL, '$2y$12$Ynw8nKP/eYNasC4SPofkFeXK3Jgjp1SKHO5OweAlO3vlTZiEhjxt2', 'Finance & Accounting', 'finance', 'Full-time', '—', '#2B6E8A', 'MM', NULL, '2026-09-11 09:51:49', '2026-09-11 10:20:13'),
(3, 'Patrick Mwendwa', 'patrick@jeotamedia.co.ke', NULL, '$2y$12$9ueT0p4s4E9HdIAgEI8/bOf1WoVuZsWKVD9iLek9CoSj0OwZ5NT..', 'Sales & Business Development', 'sales', 'Per-project', '—', '#8A5A2B', 'PM', NULL, '2026-09-11 09:51:49', '2026-09-11 10:20:13'),
(4, 'Stephen Otieno', 'stephen@jeotamedia.co.ke', NULL, '$2y$12$uR6CwYB3IVE3WzP0nk/zR.lH4il/9u1eK2cVgAiX7vNXTMSkGdn6K', 'Lead Video Editor', 'team', 'Full-time', '30,000/mo', '#5A7A2B', 'SO', NULL, '2026-09-11 09:51:49', '2026-09-11 10:20:13'),
(5, 'Amos Muthama', 'amos@jeotamedia.co.ke', NULL, '$2y$12$/uJMPb5T6jwEJ077F8GLVOzg2Dg9gviYodkOwM6t7TXYIfnRUlWK2', 'Cinematographer & Drone Pilot', 'team', 'Per-project', '5,000/day', '#6E2B8A', 'AM', NULL, '2026-09-11 09:51:49', '2026-09-11 10:20:14'),
(6, 'Ian Aluda', 'ian@jeotamedia.co.ke', NULL, '$2y$12$yq0CcqJvBylxP1XnF0V79e7GEPGroktdo69OVfW7Yuqyl42WzCPNG', 'IT & Systems Admin / Designer', 'owner', 'Full-time', '—', '#2B8A5A', 'IA', NULL, '2026-09-11 09:51:50', '2026-09-11 10:20:13'),
(7, 'Lesley Chacha', 'lesley@jeotamedia.co.ke', NULL, '$2y$12$3cV/Y4FHj4xttbRjM.W4/upCQ.7nGfv27rw7TJWd6BREoyvr9vXUS', 'Copywriter / Social & Client Relations', 'team', 'Per-project', '150/caption', '#B4780F', 'LC', NULL, '2026-09-11 09:51:50', '2026-09-11 10:20:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_thread_id_foreign` (`thread_id`),
  ADD KEY `chat_messages_sender_id_foreign` (`sender_id`);

--
-- Indexes for table `chat_participants`
--
ALTER TABLE `chat_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chat_participants_thread_id_user_id_unique` (`thread_id`,`user_id`),
  ADD KEY `chat_participants_user_id_foreign` (`user_id`);

--
-- Indexes for table `chat_threads`
--
ALTER TABLE `chat_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_threads_created_by_foreign` (`created_by`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `finance_settings`
--
ALTER TABLE `finance_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `finance_settings_key_unique` (`key`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_no_unique` (`invoice_no`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_recipes`
--
ALTER TABLE `service_recipes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_project_id_foreign` (`project_id`);

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
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_participants`
--
ALTER TABLE `chat_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `chat_threads`
--
ALTER TABLE `chat_threads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_settings`
--
ALTER TABLE `finance_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_recipes`
--
ALTER TABLE `service_recipes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_thread_id_foreign` FOREIGN KEY (`thread_id`) REFERENCES `chat_threads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_participants`
--
ALTER TABLE `chat_participants`
  ADD CONSTRAINT `chat_participants_thread_id_foreign` FOREIGN KEY (`thread_id`) REFERENCES `chat_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_threads`
--
ALTER TABLE `chat_threads`
  ADD CONSTRAINT `chat_threads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
