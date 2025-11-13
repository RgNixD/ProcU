-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 08:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_procurement`
--

-- --------------------------------------------------------

--
-- Table structure for table `app`
--

CREATE TABLE `app` (
  `app_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `app_code` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL CHECK (`status` in ('DRAFT','SUBMITTED','APPROVED','REJECTED','CONSOLIDATED')),
  `creation_date` date NOT NULL DEFAULT curdate(),
  `approval_date` date DEFAULT NULL,
  `total_budget` decimal(15,2) DEFAULT NULL,
  `consolidation_notes` varchar(500) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `table_name`, `record_id`, `created_at`) VALUES
(14, 1, 'Added new user (Sectors and Deans): ddad dsada', 'users', 7, '2025-10-05 15:52:01'),
(15, 1, 'Added new user (Sectors and Deans): ddada dda', 'users', 9, '2025-10-05 15:55:56'),
(16, 1, 'Added new user (Sectors and Deans): dadadad ad', 'users', 10, '2025-10-05 15:56:16'),
(17, 1, 'Added new user (Budget Office): xcxcx cx', 'users', 11, '2025-10-05 15:56:32'),
(18, 1, 'Added new user (Sectors and Deans): ss ss', 'users', 12, '2025-10-05 16:06:39'),
(19, 1, 'Added new user (Sectors and Deans): cxcxcx cx', 'users', 13, '2025-10-05 16:10:30'),
(20, 1, 'Added new user (Budget Office): ds fdsfs', 'users', 14, '2025-10-05 16:12:09'),
(21, 1, 'Added new user (Sectors and Deans): ss ss', 'users', 15, '2025-10-05 17:06:44'),
(22, 1, 'Updated user (Procurement Head): Rodrigo Duterte', 'users', 1, '2025-10-06 19:54:01'),
(23, 1, 'Added new user (Sectors and Deans): dsa dsa', 'users', 16, '2025-10-06 19:54:42'),
(24, 1, 'Added new user (Budget Office): dsada dsa', 'users', 17, '2025-10-06 19:56:54'),
(25, 1, 'Updated user (Budget Office): dsada dsa', 'users', 17, '2025-10-06 19:57:22'),
(26, 1, 'Updated user (Sectors and Deans): dsads dsa', 'users', 16, '2025-10-06 20:01:55'),
(27, 1, 'Updated user (Budget Office): Jose Rizal', 'users', 17, '2025-10-06 20:16:43'),
(28, 1, 'Updated user (Sectors and Deans): Apolinario Mabin', 'users', 16, '2025-10-06 20:17:11'),
(29, 1, 'Added new category: dsa', 'item_categories', 1, '2025-10-07 14:51:55'),
(30, 1, 'Added new category: cat', 'item_categories', 2, '2025-10-07 14:52:24'),
(31, 1, 'Updated category: catss', 'item_categories', 2, '2025-10-07 14:55:06'),
(32, 1, 'Updated category: catss', 'item_categories', 2, '2025-10-07 15:09:44'),
(33, 1, 'Added new Fiscal Year: 2025', 'fiscal_years', 1, '2025-10-07 15:42:51'),
(34, 1, 'Added new category: dsadsa', 'item_categories', 3, '2025-10-07 15:49:02'),
(35, 1, 'Added new category: Category 1', 'item_categories', 4, '2025-10-07 15:49:27'),
(36, 1, 'Added new category: Category 2', 'item_categories', 5, '2025-10-07 15:49:36'),
(37, 1, 'Added new Fiscal Year: 2056', 'fiscal_years', 2, '2025-10-07 15:50:08'),
(38, 1, 'Updated category: ', 'item_categories', 0, '2025-10-07 15:50:53'),
(39, 1, 'Updated Fiscal Year: 2053', 'fiscal_years', 2, '2025-10-07 15:51:34'),
(40, 1, 'Updated Fiscal Year: 2053', 'fiscal_years', 2, '2025-10-07 15:54:14'),
(41, 1, 'Updated Fiscal Year: 2053', 'fiscal_years', 2, '2025-10-07 15:58:18'),
(42, 1, 'Updated Fiscal Year: 2025', 'fiscal_years', 1, '2025-10-07 15:58:32'),
(43, 1, 'Updated category: Office Supplies', 'item_categories', 4, '2025-10-07 16:14:37'),
(44, 1, 'Updated category: Equipment', 'item_categories', 5, '2025-10-07 16:15:10'),
(45, 1, 'Updated category: Equipment', 'item_categories', 5, '2025-10-07 16:18:35'),
(46, 1, 'Added new category: Maintenance', 'item_categories', 6, '2025-10-07 16:18:46'),
(47, 1, 'Updated category: Equipment', 'item_categories', 5, '2025-10-07 19:35:55'),
(48, 1, 'Updated Fiscal Year: 2053', 'fiscal_years', 2, '2025-10-07 19:37:43'),
(49, 1, 'Updated Fiscal Year: 2053', 'fiscal_years', 2, '2025-10-07 19:37:47'),
(50, 16, 'Added new user ():  ', 'users', 18, '2025-10-07 20:06:36'),
(51, 1, 'Added new office: dsa', 'offices', 1, '2025-10-07 23:04:27'),
(52, 1, 'Added new office: dsad', 'offices', 2, '2025-10-07 23:10:23'),
(53, 1, 'Added new office: dsaddsada', 'offices', 3, '2025-10-07 23:10:32'),
(54, 1, 'Added new office: dd', 'offices', 4, '2025-10-07 23:12:09'),
(55, 1, 'Added new user (Sectors and Deans): dsa dsa', 'users', 19, '2025-10-07 23:18:27'),
(56, 1, 'Updated office details: ddss', 'offices', 4, '2025-10-07 23:19:15'),
(57, 1, 'Updated office details: Office 1', 'offices', 4, '2025-10-07 23:22:09'),
(58, 16, 'Updated user password', 'users', 16, '2025-10-07 23:23:38'),
(59, 17, 'Updated user password', 'users', 17, '2025-10-07 23:25:31'),
(60, 1, 'Added new office: Office 1', 'offices', 5, '2025-10-09 11:16:05'),
(61, 1, 'Updated category: Goods and Services', 'item_categories', 5, '2025-10-14 09:43:54'),
(62, 1, 'Updated category: Infrastructure Projects', 'item_categories', 6, '2025-10-14 09:44:23'),
(63, 1, 'Updated category: Consultancy Services', 'item_categories', 4, '2025-10-14 09:44:44'),
(64, 1, 'Updated user (Budget Office): Jose Rizal', 'users', 17, '2025-10-14 09:58:38'),
(65, 1, 'Added new sub-category: ss (Category ID: 4)', 'sub_categories', 1, '2025-10-14 10:02:37'),
(66, 1, 'Added new sub-category: sss (Category ID: 5)', 'sub_categories', 2, '2025-10-14 10:03:06'),
(67, 1, 'Updated sub-category: ssss (Category ID: 4)', 'sub_categories', 1, '2025-10-14 10:03:31'),
(68, 1, 'Updated sub-category: ssssdd (Category ID: 6)', 'sub_categories', 1, '2025-10-14 10:03:39'),
(69, 1, 'Added new sub-category: ss (Category ID: 4)', 'sub_categories', 3, '2025-10-14 10:04:40'),
(70, 1, 'Added new category: ss', 'item_categories', 7, '2025-10-14 10:25:18'),
(71, 17, 'Added new Fiscal Year: 2025', 'fiscal_years', 3, '2025-10-15 16:41:01'),
(72, 17, 'Updated Fiscal Year: 2025', 'fiscal_years', 3, '2025-10-15 16:41:10'),
(73, 17, 'Added budget allocation (₱2,500,000.00) for Office', 'budget_allocation', 1, '2025-10-15 16:48:31'),
(74, 17, 'Updated office details: OP - Office of the Preside', 'offices', 5, '2025-10-17 13:19:15'),
(75, 17, 'Updated office details: Office of the President', 'offices', 5, '2025-10-17 13:19:23'),
(76, 17, 'Added new user (Sectors): Jose Rizal', 'users', 20, '2025-10-17 13:20:14'),
(77, 17, 'Added new user (Sectors): Rodrigo Duterte', 'users', 21, '2025-10-17 13:20:44'),
(78, 17, 'Added new user (Sectors): Sara Duterte', 'users', 22, '2025-10-17 13:21:13'),
(79, 17, 'Added new user (Sectors): Pambato ng Bulag', 'users', 23, '2025-10-17 13:21:56'),
(80, 17, 'Updated office details: Office of the President', 'offices', 5, '2025-10-17 13:33:18'),
(81, 17, 'Added new office: Office of the Vice President for', 'offices', 7, '2025-10-17 13:33:46'),
(82, 17, 'Added new office: Office of the Vice President for', 'offices', 9, '2025-10-17 13:36:30'),
(83, 17, 'Added new office: Office of the Vice President for', 'offices', 10, '2025-10-17 13:36:43'),
(84, 17, 'Added new office: Office of the Vice President for', 'offices', 11, '2025-10-17 13:37:03'),
(85, 17, 'Added budget allocation (₱5,000,000.00) for Office', 'budget_allocation', 2, '2025-10-17 14:14:30'),
(86, 17, 'Added budget allocation (₱2,222.00) for Office ID:', 'budget_allocation', 5, '2025-10-17 16:10:14'),
(87, 17, 'Updated budget allocation (₱2,222.00) for Office I', 'budget_allocation', 5, '2025-10-17 16:23:40'),
(88, 17, 'Updated budget allocation (₱450,000.00) for Office', 'budget_allocation', 2, '2025-10-17 16:23:54'),
(89, 17, 'Added budget allocation (₱2.00) for Office ID: 7 (', 'budget_allocation', 9, '2025-10-17 16:25:26'),
(90, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 10, '2025-10-17 16:28:29'),
(91, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 11, '2025-10-17 16:28:40'),
(92, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 12, '2025-10-17 16:28:45'),
(93, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 13, '2025-10-17 16:28:51'),
(94, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 14, '2025-10-17 16:29:00'),
(95, 1, 'Updated budget allocation (₱2,000,000.00) for Offi', 'budget_allocation', 14, '2025-10-17 16:36:17'),
(96, 1, 'Updated budget allocation (₱2,000,000.00) for Offi', 'budget_allocation', 14, '2025-10-17 16:49:21'),
(97, 16, 'Added new PPMP (PPMP-2025-68F241FD59971) with 1 it', 'ppmp', 14, '2025-10-17 21:17:49'),
(98, 16, 'Added new PPMP (PPMP-2025-68F24D0C07775) with 2 it', 'ppmp', 19, '2025-10-17 22:05:00'),
(99, 16, 'Added new PPMP (PPMP-2025-68F24D75EDDD4) with 2 it', 'ppmp', 20, '2025-10-17 22:06:45'),
(100, 16, 'Added new PPMP (PPMP-2025-68F24E097DE7C) with 1 it', 'ppmp', 21, '2025-10-17 22:09:13'),
(101, 16, 'Added new PPMP (PPMP-2025-68F24E1F24465) with 1 it', 'ppmp', 22, '2025-10-17 22:09:35'),
(102, 16, 'Added new PPMP (PPMP-2025-68F24EEA71E86) with 1 it', 'ppmp', 23, '2025-10-17 22:12:58'),
(103, 16, 'Added new PPMP (PPMP-2025-68F2724E340A8) with 1 it', 'ppmp', 24, '2025-10-18 00:43:58'),
(104, 16, 'Added new PPMP (PPMP-2025-68F2750ED953E) with 2 it', 'ppmp', 25, '2025-10-18 00:55:42'),
(105, 16, 'Added new PPMP (PPMP-2025-68F276B3D5149) with 1 it', 'ppmp', 26, '2025-10-18 01:02:43'),
(106, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 11, '2025-10-18 01:04:21'),
(107, 16, 'Added new PPMP (PPMP-2025-68F31BE0946E5) with 1 it', 'ppmp', 27, '2025-10-18 12:47:28'),
(108, 16, 'Added new PPMP (PPMP-2025-68F32119C73D3) with 2 it', 'ppmp', 28, '2025-10-18 13:09:45'),
(109, 16, 'Added new PPMP (PPMP-2025-68F32234A654F) with 1 it', 'ppmp', 29, '2025-10-18 13:14:28'),
(110, 16, 'Added new PPMP (PPMP-2025-68F32AA65E820) with 1 it', 'ppmp', 30, '2025-10-18 13:50:30'),
(111, 16, 'Added new PPMP (PPMP-2025-68F32AB616C24) with 1 it', 'ppmp', 31, '2025-10-18 13:50:46'),
(112, 16, 'Added new PPMP (PPMP-2025-68F32AC48FBE5) with 1 it', 'ppmp', 32, '2025-10-18 13:51:00'),
(113, 16, 'Added new PPMP (PPMP-2025-68F4C5A7E777D) with 2 it', 'ppmp', 33, '2025-10-19 19:04:07'),
(114, 16, 'Added new PPMP (PPMP-2025-68F4CF00BD013) with 1 it', 'ppmp', 39, '2025-10-19 19:44:00'),
(115, 16, 'Added new PPMP (PPMP-2025-68F4D0AC826E5) with 2 it', 'ppmp', 40, '2025-10-19 19:51:08'),
(116, 16, 'Added new PPMP (PPMP-2025-68F4D3E3D741A) with 1 it', 'ppmp', 41, '2025-10-19 20:04:51'),
(117, 16, 'Updated PPMP (#40) with 1 revised items.', 'ppmp', 40, '2025-10-19 21:04:10'),
(118, 16, 'Added new PPMP (PPMP-2025-68F4E53DA8D86) with 2 it', 'ppmp', 42, '2025-10-19 21:18:53'),
(119, 16, 'Updated PPMP (#42) with 3 revised items.', 'ppmp', 42, '2025-10-19 21:19:20'),
(120, 16, 'Updated PPMP (#42) with 2 revised items.', 'ppmp', 42, '2025-10-19 21:20:10'),
(121, 16, 'Added new PPMP (PPMP-2025-68F9DAED33784) with 1 it', 'ppmp', 43, '2025-10-23 15:36:13'),
(122, 17, 'Updated profile picture', 'users', 17, '2025-10-23 19:06:54'),
(123, 1, 'Added new item name: wa (Sub-category ID: 20)', 'item_names', 1, '2025-10-26 04:10:10'),
(124, 1, 'Added new item name: was (Sub-category ID: 20)', 'item_names', 2, '2025-10-26 04:19:46'),
(125, 1, 'Updated item name: wass (Sub-category ID: 20)', 'item_names', 2, '2025-10-26 04:19:52'),
(126, 1, 'Updated item name: wass (Sub-category ID: 22)', 'item_names', 2, '2025-10-26 04:19:56'),
(127, 1, 'Added new item name: dsa (Sub-category ID: 22)', 'item_names', 3, '2025-10-26 04:20:36'),
(128, 1, 'Added new item name: Item 1 (Sub-category ID: 20)', 'item_names', 4, '2025-10-27 02:42:31'),
(129, 1, 'Added new item name: Item 2 (Sub-category ID: 20)', 'item_names', 5, '2025-10-27 02:42:36'),
(130, 1, 'Added new item name: Item 3 (Sub-category ID: 21)', 'item_names', 6, '2025-10-27 02:42:42'),
(131, 1, 'Added new item name: Item 4 (Sub-category ID: 21)', 'item_names', 7, '2025-10-27 02:42:46'),
(132, 1, 'Added new item name: sssss (Sub-category ID: 21)', 'item_names', 8, '2025-10-27 02:59:00'),
(133, 1, 'Updated item name: sssssddd (Sub-category ID: 21)', 'item_names', 0, '2025-10-27 02:59:06'),
(134, 16, 'Added new PPMP (PPMP-2025-68FEFF4EA42E7) with 2 it', 'ppmp', 48, '2025-10-27 13:12:46'),
(135, 16, 'Added new PPMP (PPMP-2025-68FF0751E09B3) with 2 it', 'ppmp', 49, '2025-10-27 13:46:57'),
(136, 16, 'Added new PPMP (PPMP-2025-68FF148125460) with 2 it', 'ppmp', 50, '2025-10-27 14:43:13'),
(137, 16, 'Updated PPMP (#50) with 2 revised items.', 'ppmp', 50, '2025-10-27 15:07:26'),
(138, 16, 'Updated PPMP (#50) with 4 revised items.', 'ppmp', 50, '2025-10-27 15:11:35'),
(139, 16, 'Added new PPMP (PPMP-2025-69105AF4F290C) with 1 it', 'ppmp', 53, '2025-11-09 17:12:21'),
(140, 16, 'Added new PPMP (PPMP-2025-6910606833668) with 1 it', 'ppmp', 54, '2025-11-09 17:35:36'),
(141, 16, 'Added new PPMP (PPMP-2025-691062B1639E9) with 1 it', 'ppmp', 55, '2025-11-09 17:45:21'),
(142, 16, 'Added new PPMP (PPMP-2025-69106C3091FAC) with 2 it', 'ppmp', 56, '2025-11-09 18:25:52'),
(143, 16, 'Added new PPMP (PPMP-2025-69106D1D254CE) with 2 it', 'ppmp', 57, '2025-11-09 18:29:49'),
(144, 16, 'Added new PPMP (PPMP-2025-69106EA4A2174) with 2 it', 'ppmp', 58, '2025-11-09 18:36:20'),
(145, 16, 'Added new PPMP (PPMP-2025-6910704F80B3C) with 2 it', 'ppmp', 59, '2025-11-09 18:43:27'),
(146, 16, 'Added new PPMP (PPMP-2025-6910712B6BF87) with 2 it', 'ppmp', 60, '2025-11-09 18:47:07'),
(147, 16, 'Added new PPMP (PPMP-2025-691071BC6B795) with 2 it', 'ppmp', 61, '2025-11-09 18:49:32'),
(148, 16, 'Updated PPMP (#61) with 1 total items.', 'ppmp', 61, '2025-11-10 03:08:19'),
(149, 16, 'Updated PPMP (#61) with 2 total items.', 'ppmp', 61, '2025-11-10 03:09:13');

-- --------------------------------------------------------

--
-- Table structure for table `budget_allocation`
--

CREATE TABLE `budget_allocation` (
  `allocation_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `remaining_amount` decimal(15,2) NOT NULL,
  `status` enum('Pending','Approved','Dispproved') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_allocation`
--

INSERT INTO `budget_allocation` (`allocation_id`, `office_id`, `fiscal_year_id`, `allocated_amount`, `remaining_amount`, `status`, `created_at`) VALUES
(11, 5, 3, 1000000.00, 990276.00, 'Approved', '2025-10-17 16:28:40'),
(12, 9, 3, 1000000.00, 1000000.00, 'Pending', '2025-10-17 16:28:45'),
(13, 11, 3, 1000000.00, 1000000.00, 'Pending', '2025-10-17 16:28:51'),
(14, 10, 3, 2000000.00, 2000000.00, 'Approved', '2025-10-17 16:29:00');

-- --------------------------------------------------------

--
-- Table structure for table `fiscal_years`
--

CREATE TABLE `fiscal_years` (
  `fiscal_year_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fiscal_years`
--

INSERT INTO `fiscal_years` (`fiscal_year_id`, `year`, `start_date`, `end_date`, `is_current`, `status`) VALUES
(3, 2025, '2025-10-15', '2025-12-31', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `item_categories`
--

CREATE TABLE `item_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(10) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_categories`
--

INSERT INTO `item_categories` (`category_id`, `category_name`, `category_code`, `description`, `is_active`) VALUES
(4, 'Consultancy Services', '0003', 'Consultancy Services Description', 1),
(5, 'Goods and Services', '0001', 'Goods and Services Description', 1),
(6, 'Infrastructure Projects', '0002', 'Infrastructure Projects Description', 1);

-- --------------------------------------------------------

--
-- Table structure for table `item_names`
--

CREATE TABLE `item_names` (
  `item_name_id` int(11) NOT NULL,
  `sub_category_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_names`
--

INSERT INTO `item_names` (`item_name_id`, `sub_category_id`, `item_name`, `created_at`, `updated_at`) VALUES
(4, 20, 'Item 1', '2025-10-26 18:42:31', '2025-10-26 18:42:31'),
(5, 20, 'Item 2', '2025-10-26 18:42:36', '2025-10-26 18:42:36'),
(6, 21, 'Item 3', '2025-10-26 18:42:42', '2025-10-26 18:42:42'),
(7, 21, 'Item 4', '2025-10-26 18:42:46', '2025-10-26 18:42:46');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `office_id` int(11) NOT NULL,
  `office_name` varchar(100) NOT NULL,
  `office_code` varchar(10) DEFAULT NULL,
  `head_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`office_id`, `office_name`, `office_code`, `head_id`, `description`, `is_active`, `created_at`) VALUES
(5, 'Office of the President', '0001', 16, 'Sample', 1, '2025-10-09 11:16:05'),
(7, 'Office of the Vice President for Academic Affairs', '0002', 23, 'Sample', 1, '2025-10-17 13:33:46'),
(9, 'Office of the Vice President for Administration and Finance', '0003', 22, 'Sample', 1, '2025-10-17 13:36:30'),
(10, 'Office of the Vice President for Research and Extension', '0004', 21, 'Sample', 1, '2025-10-17 13:36:43'),
(11, 'Office of the Vice President for Planning, Development and Special Concerns', '0005', 20, 'Sample', 1, '2025-10-17 13:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `email`, `code`, `expires_at`, `created_at`) VALUES
(9, 1, 'sonerwin12@gmail.com', '100092', '2025-09-30 00:59:51', '2025-09-29 16:56:51');

-- --------------------------------------------------------

--
-- Table structure for table `ppmp`
--

CREATE TABLE `ppmp` (
  `ppmp_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `ppmp_code` varchar(50) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `approval_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `submitted_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppmp_items`
--

CREATE TABLE `ppmp_items` (
  `item_id` int(11) NOT NULL,
  `ppmp_id` int(11) NOT NULL,
  `item_description` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `sub_category_id` int(11) NOT NULL,
  `item_name_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `specifications` varchar(255) DEFAULT NULL,
  `mode_of_procurement` varchar(255) NOT NULL,
  `pre_procurement_conference` varchar(20) NOT NULL,
  `procurement_start_date` date NOT NULL,
  `bidding_date` date NOT NULL,
  `contract_signing_date` date NOT NULL,
  `source_of_funds` varchar(100) NOT NULL,
  `estimated_budget` decimal(15,2) NOT NULL,
  `total_cost` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `estimated_budget`) STORED,
  `file_attachment` varchar(255) NOT NULL,
  `procurement_method` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `sub_category_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `sub_cat_name` varchar(255) NOT NULL,
  `sub_cat_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`sub_category_id`, `category_id`, `sub_cat_name`, `sub_cat_description`, `created_at`, `updated_at`) VALUES
(4, 5, 'Laboratory Equipments', 'Procurement of laboratory equipment and instruments', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(5, 5, 'Subscription', 'Subscription services for software, journals, or memberships', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(6, 5, 'Repair and Maintenance', 'Repair and maintenance of facilities, vehicles, and equipment', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(7, 5, 'Fixtures', 'Office or facility fixtures and furnishings', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(8, 5, 'Hotel Accommodation', 'Hotel or lodging accommodations for official activities', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(9, 5, 'Office Equipment', 'Office machinery, IT equipment, and related tools', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(10, 5, 'Office Supplies', 'Procurement of general office supplies and consumables', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(11, 5, 'Hardware Supplies and Materials', 'Hardware materials and construction-related supplies', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(12, 5, 'Meals and Snacks', 'Catering services, meals, and snacks for events or meetings', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(13, 5, 'Services', 'Professional or support services under goods and services', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(14, 6, 'Building Construction', 'Construction of new buildings and facilities', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(15, 6, 'Building Renovation', 'Renovation and improvement of existing structures', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(16, 6, 'Facility Improvement', 'Upgrading and improving institutional facilities', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(17, 6, 'Electrical Installation', 'Electrical wiring and installation works', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(18, 6, 'Plumbing and Drainage System Works', 'Installation and repair of plumbing and drainage systems', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(19, 6, 'Classroom/Office Repair', 'Repair and maintenance of classrooms and offices', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(20, 4, 'Architectural Design Services', 'Architectural planning and design consultancy', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(21, 4, 'Engineering Design Services', 'Engineering design and technical consultancy', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(22, 4, 'IT Systems Development Consultancy', 'IT systems analysis, design, and development consultancy', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(23, 4, 'Project Management/Construction Supervision', 'Consultancy for project management and supervision', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(24, 4, 'Financial or Accounting Consultancy', 'Financial management, auditing, and accounting consultancy', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(25, 4, 'Legal Consultancy', 'Legal advisory and consultancy services', '2025-10-14 02:07:25', '2025-10-14 02:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `first_name`, `last_name`, `phone`, `profile`, `is_active`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$pZLsb/4e36DdhP7RFqQY0OPkHbWDdTQhee6i6ZSWUkm.BF2vPJd5.', 'admin@gmail.com', 'Rodrigo', 'Duterte', '9509972084', 'doe_20250929_160658.png', 1, '2025-09-28 10:33:37', '2025-10-29 03:37:24'),
(16, 'dean', '$2y$10$iDyW8R57OXQBmBqfP.FfmeRi6JZhbyVfii3Sxm7xtyUXsXj.Nrcha', 'dean@gmail.com', 'Apolinario', 'Mabini', '9509972082', 'avatar.png', 1, '2025-10-06 19:54:42', '2025-11-10 01:23:46'),
(17, 'finance', '$2y$10$Zt8b1bx.XlL.z//DrMxVMe9VajlgyepHRL60qSTHU0Gy.71JlZQoS', 'finance@gmail.com', 'Jose', 'Rizal', '9509972322', 'rizal_20251023_190654.jpg', 1, '2025-10-06 19:56:54', '2025-10-29 01:37:16'),
(20, 'sector2', '$2y$10$Zt8b1bx.XlL.z//DrMxVMe9VajlgyepHRL60qSTHU0Gy.71JlZQoS', 'rizal@gmail.com', 'Jose', 'Rizal', '9432432424', 'avatar.png', 1, '2025-10-17 13:20:14', '2025-10-23 19:36:00'),
(21, 'sector3', '$2y$10$Km4kpzeVTuZkuLLCnC2yKuioZjQT5FkDlXkIYUjCuyM/wKbse1PUC', 'duterte@gmail.com', 'Rodrigo', 'Duterte', '9503943434', 'avatar.png', 1, '2025-10-17 13:20:44', NULL),
(22, 'sector4', '$2y$10$B..aMHZIeV.WsKzuOjmDFOif2iVmtXITj.92PkIxeR2.KpM.nKKwi', 'sara@gmail.com', 'Sara', 'Duterte', '9404343434', 'avatar.png', 1, '2025-10-17 13:21:13', NULL),
(23, 'sector5', '$2y$10$J8surTOynxRuY15ih96QL.fZwl/D6rsTwGeWJmX.TTm2ZjGoPG3Sa', 'bulag@gmail.com', 'Pambato ng', 'Bulag', '9503442424', 'avatar.png', 1, '2025-10-17 13:21:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_access`
--

CREATE TABLE `user_access` (
  `access_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `can_create_ppmp` tinyint(1) NOT NULL DEFAULT 0,
  `can_approve_ppmp` tinyint(1) NOT NULL DEFAULT 0,
  `can_view_reports` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_budget` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_access`
--

INSERT INTO `user_access` (`access_id`, `user_id`, `access_name`, `description`, `can_create_ppmp`, `can_approve_ppmp`, `can_view_reports`, `can_manage_budget`, `is_active`, `created_at`) VALUES
(1, 1, 'Procurement Head', 'Procurement Head role access', 0, 1, 1, 1, 1, '2025-09-28 10:33:37'),
(116, 16, 'Sectors', 'Sectors and Deans role access', 1, 0, 0, 0, 1, '2025-10-06 19:54:42'),
(117, 17, 'Budget Office', 'Budget Office role access', 0, 0, 0, 1, 1, '2025-10-06 19:56:54'),
(120, 20, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-17 13:20:14'),
(121, 21, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-17 13:20:44'),
(122, 22, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-17 13:21:13'),
(123, 23, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-17 13:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `session_token`, `expires_at`, `is_active`, `created_at`) VALUES
(112, 1, '87bf6a1461018106b681f5fc31afea78afae1a65da3f218c52d5c62cd6b75007', '2025-10-26 22:43:54', 1, '2025-10-27 04:43:54'),
(113, 16, '6485bd6e5509144a4baacb41c32705239dca9353cda80d50067973b998d55447', '2025-10-27 06:53:23', 1, '2025-10-27 12:53:23'),
(114, 1, '8e43064fba8ee4875be3a252997e78664aedca6485e84117ea9750ae63f8d879', '2025-10-27 06:53:30', 0, '2025-10-27 12:53:30'),
(115, 16, '1c1d56687e2835262de414a11a2639b1e77c4259d360348e28c1b76a606e728d', '2025-10-27 08:28:57', 1, '2025-10-27 14:28:57'),
(116, 1, 'dd74952d5d678724b3432481fb4d41fa314e6ffd115f1521bf891208a01b138e', '2025-10-27 09:12:19', 1, '2025-10-27 15:12:19'),
(117, 17, '41d2d7683276c76ba391901f6f33f7c08fdfe0c53a8d7036a83572962d13d3e2', '2025-10-27 09:41:58', 1, '2025-10-27 15:41:58'),
(118, 16, '4bfeacb302abd28608985a6b174b68e062a90e8a84b64ce67d03df4162cfa910', '2025-10-28 13:46:44', 0, '2025-10-28 19:46:44'),
(119, 1, '3ee577e2af98ac988341bc1b965a1368baab5b5a7c747564ddc5bbcab9abc147', '2025-10-28 19:36:52', 0, '2025-10-29 01:36:52'),
(120, 16, '4e449690723b1f1f99786512ff9ea25cdcb4d273c4eaefe5beb65117d5fe3010', '2025-10-28 19:37:01', 1, '2025-10-29 01:37:01'),
(121, 17, 'cdf534eedc8c1f88d2ab20a6f1c71b1c27af0eb02a61c8d421e12de972d4ac29', '2025-10-28 19:37:16', 1, '2025-10-29 01:37:16'),
(122, 1, '1cfc97470e3e5e8e5ccdcf4632cf38b7b5c4aa39120d0af1de9df0b3f524852e', '2025-10-28 21:37:24', 1, '2025-10-29 03:37:24'),
(123, 16, 'e466a1eabc6eaaf8cd1d31f3bca16bbdde63b25e9b7d28a80329872838995b63', '2025-11-08 05:58:42', 0, '2025-11-08 11:58:42'),
(124, 16, 'e7618d3ad4838b9b3598b7f53e7a617ec79744cf67b4b9817d2ef8376bdc5a31', '2025-11-08 08:15:12', 1, '2025-11-08 14:15:12'),
(125, 16, '9a78dbd2c27d4f51c65ed5e28c358ce1d5466b30e470fab2827cac2e5590ed12', '2025-11-08 09:53:59', 0, '2025-11-08 15:53:59'),
(126, 16, 'b54e7b868a8291bcd76cdb402d0c51770f154a69d797741f46f2c625406c7a46', '2025-11-08 11:54:33', 0, '2025-11-08 17:54:33'),
(127, 16, '621e5cbd932200662968bd3ca63683db49f75dcfd936cef9dc8ca77022e6ec29', '2025-11-08 14:30:22', 0, '2025-11-08 20:30:22'),
(128, 16, '207f1d783faf3d3b919f161807f533fe1f006daa157bab375e7e5261bc71fc7c', '2025-11-08 19:31:24', 1, '2025-11-09 01:31:24'),
(129, 16, 'aca125cceb783c7046291c0e0867a5cbde2f9df9b16ce269fd6cfe2be296d5e1', '2025-11-09 10:33:51', 1, '2025-11-09 16:33:51'),
(130, 16, '9b337e5e9557d24a5353053586740f73ec461782974492efe4273845ebf7b252', '2025-11-09 12:14:09', 0, '2025-11-09 18:14:09'),
(131, 16, 'f0fabfdfca2aac12041bc0efd41b3a4ea6dd72639202f9f2bb3941ff1706bb31', '2025-11-09 14:17:45', 0, '2025-11-09 20:17:45'),
(132, 16, 'e6fe6b44da076c10e878f11820c45771a9f25b9984142fcda96a5662e16a1763', '2025-11-09 16:57:11', 0, '2025-11-09 22:57:11'),
(133, 16, '58e49b6f9b5eaf3a86d02da8d3c29ee5b553a8e4b5dbe5757bd0a004efaf164a', '2025-11-09 19:23:46', 1, '2025-11-10 01:23:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app`
--
ALTER TABLE `app`
  ADD PRIMARY KEY (`app_id`),
  ADD UNIQUE KEY `app_code` (`app_code`),
  ADD KEY `fk_app_fiscal_year` (`fiscal_year_id`),
  ADD KEY `fk_app_creator` (`created_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `audit_logs_ibfk_1` (`user_id`);

--
-- Indexes for table `budget_allocation`
--
ALTER TABLE `budget_allocation`
  ADD PRIMARY KEY (`allocation_id`),
  ADD UNIQUE KEY `uc_office_year_allocation` (`office_id`,`fiscal_year_id`),
  ADD KEY `fk_allocation_fiscal_year` (`fiscal_year_id`);

--
-- Indexes for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  ADD PRIMARY KEY (`fiscal_year_id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- Indexes for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD UNIQUE KEY `category_code` (`category_code`);

--
-- Indexes for table `item_names`
--
ALTER TABLE `item_names`
  ADD PRIMARY KEY (`item_name_id`),
  ADD KEY `sub_category_id` (`sub_category_id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`office_id`),
  ADD UNIQUE KEY `office_name` (`office_name`),
  ADD UNIQUE KEY `office_code` (`office_code`),
  ADD KEY `offices_ibfk_1` (`head_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ppmp`
--
ALTER TABLE `ppmp`
  ADD PRIMARY KEY (`ppmp_id`),
  ADD UNIQUE KEY `ppmp_code` (`ppmp_code`),
  ADD KEY `fk_ppmp_fiscal_year` (`fiscal_year_id`),
  ADD KEY `fk_ppmp_office` (`office_id`),
  ADD KEY `fk_ppmp_submitter` (`submitted_by`);

--
-- Indexes for table `ppmp_items`
--
ALTER TABLE `ppmp_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_ppmp_items_category` (`category_id`),
  ADD KEY `fk_ppmp_items_ppmp` (`ppmp_id`),
  ADD KEY `fk_ppmp_items_sub_category` (`sub_category_id`),
  ADD KEY `fk_ppmp_items_item_name` (`item_name_id`);

--
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`sub_category_id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_access`
--
ALTER TABLE `user_access`
  ADD PRIMARY KEY (`access_id`),
  ADD KEY `user_access_ibfk_1` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app`
--
ALTER TABLE `app`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `budget_allocation`
--
ALTER TABLE `budget_allocation`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  MODIFY `fiscal_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `item_names`
--
ALTER TABLE `item_names`
  MODIFY `item_name_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ppmp`
--
ALTER TABLE `ppmp`
  MODIFY `ppmp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `ppmp_items`
--
ALTER TABLE `ppmp_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `sub_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_access`
--
ALTER TABLE `user_access`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app`
--
ALTER TABLE `app`
  ADD CONSTRAINT `fk_app_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_app_fiscal_year` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`fiscal_year_id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `budget_allocation`
--
ALTER TABLE `budget_allocation`
  ADD CONSTRAINT `fk_allocation_fiscal_year` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`fiscal_year_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_allocation_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `item_names`
--
ALTER TABLE `item_names`
  ADD CONSTRAINT `item_names_ibfk_1` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`sub_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `offices`
--
ALTER TABLE `offices`
  ADD CONSTRAINT `offices_ibfk_1` FOREIGN KEY (`head_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ppmp`
--
ALTER TABLE `ppmp`
  ADD CONSTRAINT `fk_ppmp_fiscal_year` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`fiscal_year_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ppmp_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ppmp_submitter` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ppmp_items`
--
ALTER TABLE `ppmp_items`
  ADD CONSTRAINT `fk_ppmp_items_category` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ppmp_items_item_name` FOREIGN KEY (`item_name_id`) REFERENCES `item_names` (`item_name_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ppmp_items_ppmp` FOREIGN KEY (`ppmp_id`) REFERENCES `ppmp` (`ppmp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ppmp_items_sub_category` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`sub_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_access`
--
ALTER TABLE `user_access`
  ADD CONSTRAINT `user_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
