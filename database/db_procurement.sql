-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 07:59 PM
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
-- Table structure for table `annual_budget`
--

CREATE TABLE `annual_budget` (
  `annual_budget_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `total_budget_amount` decimal(15,2) NOT NULL,
  `remaining_budget_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Draft',
  `submitted_by_user_id` int(11) DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annual_budget`
--

INSERT INTO `annual_budget` (`annual_budget_id`, `fiscal_year_id`, `total_budget_amount`, `remaining_budget_amount`, `status`, `submitted_by_user_id`, `updated_by_user_id`, `approval_date`, `created_at`, `updated_at`) VALUES
(10, 3, 5000000.00, 3900000.00, 'Draft', 1, NULL, NULL, '2025-11-15 18:26:46', '2025-11-15 18:48:50'),
(11, 4, 10000000.00, 10000000.00, 'Draft', 1, NULL, NULL, '2025-11-15 18:31:30', '2025-11-15 18:31:30');

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
(150, 17, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 1, '2025-11-15 00:37:16'),
(151, 17, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 2, '2025-11-15 00:40:12'),
(152, 17, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 3, '2025-11-15 00:42:30'),
(153, 17, 'Added budget allocation (₱22,223,222.00) for Offic', 'budget_allocation', 15, '2025-11-15 00:44:40'),
(154, 1, 'Updated budget allocation (₱23,333.00) for Office ', 'budget_allocation', 15, '2025-11-15 00:56:48'),
(155, 17, 'Added budget allocation (₱222,222.00) for Office I', 'budget_allocation', 16, '2025-11-15 00:58:51'),
(156, 1, 'Updated budget allocation (₱30,000.00) for Office ', 'budget_allocation', 16, '2025-11-15 00:59:07'),
(157, 17, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 4, '2025-11-15 01:02:31'),
(158, 17, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 5, '2025-11-15 01:02:36'),
(159, 17, 'Updated Annual Budget ID: 5 for Fiscal Year ID: 3', 'annual_budget', 5, '2025-11-15 01:21:36'),
(160, 17, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 6, '2025-11-15 01:24:37'),
(161, 1, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 7, '2025-11-15 01:37:28'),
(162, 17, 'Added budget allocation (₱100,000.00) for Office I', 'budget_allocation', 17, '2025-11-15 01:40:44'),
(163, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 17, '2025-11-15 01:41:14'),
(164, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 17, '2025-11-15 19:28:32'),
(165, 16, 'Added new PPMP (PPMP-2025-691863E3237EB) with 1 it', 'ppmp', 62, '2025-11-15 19:28:35'),
(166, 1, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 8, '2025-11-16 01:05:20'),
(167, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 18, '2025-11-16 01:05:57'),
(168, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 18, '2025-11-16 01:06:14'),
(169, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 18, '2025-11-16 01:15:47'),
(170, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 18, '2025-11-16 01:18:50'),
(171, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 18, '2025-11-16 01:23:02'),
(172, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 18, '2025-11-16 01:26:42'),
(173, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 18, '2025-11-16 01:29:39'),
(174, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 18, '2025-11-16 01:41:41'),
(175, 1, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 9, '2025-11-16 01:44:28'),
(176, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 19, '2025-11-16 01:44:36'),
(177, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 01:56:34'),
(178, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 01:57:15'),
(179, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:15:20'),
(180, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:16:43'),
(181, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:18:00'),
(182, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:18:25'),
(183, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:19:50'),
(184, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:20:33'),
(185, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:21:06'),
(186, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 19, '2025-11-16 02:21:26'),
(187, 1, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 10, '2025-11-16 02:26:46'),
(188, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 20, '2025-11-16 02:26:52'),
(189, 1, 'Updated budget allocation (₱1,000,000.00) for Offi', 'budget_allocation', 20, '2025-11-16 02:27:09'),
(190, 1, 'Added new Fiscal Year: 2026', 'fiscal_years', 4, '2025-11-16 02:30:22'),
(191, 1, 'Updated Fiscal Year: 2026', 'fiscal_years', 4, '2025-11-16 02:31:01'),
(192, 1, 'Updated Fiscal Year: 2025', 'fiscal_years', 3, '2025-11-16 02:31:18'),
(193, 1, 'Added new Annual Budget for Fiscal Year ID: 4', 'annual_budget', 11, '2025-11-16 02:31:30'),
(194, 1, 'Updated Fiscal Year: 2025', 'fiscal_years', 3, '2025-11-16 02:31:46'),
(195, 1, 'Updated Fiscal Year: 2026', 'fiscal_years', 4, '2025-11-16 02:31:52'),
(196, 16, 'Added new PPMP (PPMP-2025-6918CAFF96441) with 1 it', 'ppmp', 63, '2025-11-16 02:48:31');

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
  `status` enum('Pending','Approved','Disapproved') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_allocation`
--

INSERT INTO `budget_allocation` (`allocation_id`, `office_id`, `fiscal_year_id`, `allocated_amount`, `remaining_amount`, `status`, `created_at`) VALUES
(20, 5, 3, 1000000.00, 900000.00, 'Approved', '2025-11-16 02:26:52');

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
(3, 2025, '2025-11-17', '2025-12-31', 1, 1),
(4, 2026, '2026-01-01', '2026-12-31', 0, 0);

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
  `notes` text NOT NULL,
  `approval_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `submitted_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ppmp`
--

INSERT INTO `ppmp` (`ppmp_id`, `office_id`, `fiscal_year_id`, `ppmp_code`, `status`, `notes`, `approval_date`, `total_amount`, `submitted_by`, `created_at`) VALUES
(63, 5, 3, 'PPMP-2025-6918CAFF96441', 'Approved', 'sample only', '2025-11-16', 100000.00, 16, '2025-11-16 02:48:31');

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

--
-- Dumping data for table `ppmp_items`
--

INSERT INTO `ppmp_items` (`item_id`, `ppmp_id`, `item_description`, `category_id`, `sub_category_id`, `item_name_id`, `quantity`, `specifications`, `mode_of_procurement`, `pre_procurement_conference`, `procurement_start_date`, `bidding_date`, `contract_signing_date`, `source_of_funds`, `estimated_budget`, `file_attachment`, `procurement_method`, `remarks`) VALUES
(108, 63, 'dsa', 4, 21, 6, 100, 'dsa', 'Competitive Bidding', 'Yes', '2025-11-17', '2025-11-19', '2025-11-22', 'Current Appropriation', 1000.00, '1763232511_6918caff91b07.pdf,1763232511_6918caff91e53.pdf', NULL, 'dsa');

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
(1, 'admin', '$2y$10$pZLsb/4e36DdhP7RFqQY0OPkHbWDdTQhee6i6ZSWUkm.BF2vPJd5.', 'admin@gmail.com', 'Rodrigo', 'Duterte', '9509972084', 'doe_20250929_160658.png', 1, '2025-09-28 10:33:37', '2025-11-16 00:54:51'),
(16, 'dean', '$2y$10$iDyW8R57OXQBmBqfP.FfmeRi6JZhbyVfii3Sxm7xtyUXsXj.Nrcha', 'dean@gmail.com', 'Apolinario', 'Mabini', '9509972082', 'avatar.png', 1, '2025-10-06 19:54:42', '2025-11-16 02:47:52'),
(17, 'finance', '$2y$10$Zt8b1bx.XlL.z//DrMxVMe9VajlgyepHRL60qSTHU0Gy.71JlZQoS', 'finance@gmail.com', 'Jose', 'Rizal', '9509972322', 'rizal_20251023_190654.jpg', 1, '2025-10-06 19:56:54', '2025-11-16 01:05:35'),
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
(133, 16, '58e49b6f9b5eaf3a86d02da8d3c29ee5b553a8e4b5dbe5757bd0a004efaf164a', '2025-11-09 19:23:46', 1, '2025-11-10 01:23:46'),
(134, 16, 'b10bc76a2133bf232db3a9a935305df1d60ce424a6c8dc4153c15460e3a30ec5', '2025-11-13 20:38:41', 0, '2025-11-14 02:38:41'),
(135, 1, '420938fff0ec73a444394b57fbd93bb21df0c14c2d77463946e91c873fda91ff', '2025-11-13 20:39:15', 1, '2025-11-14 02:39:15'),
(136, 17, 'fc36cf22630f89e74ffe38ad98e4f2590b9386f6946aa69dbef81aeb753b0621', '2025-11-13 20:47:17', 1, '2025-11-14 02:47:17'),
(137, 1, 'e9a628671d6b038b1adc19519b61c967e2d2d8ae692074dca13e75c9fc26278b', '2025-11-14 14:18:26', 0, '2025-11-14 20:18:26'),
(138, 17, '60c3a2f50fca81d0deb7f8837fcbb3f3cdd7267a64383d3e232fb1fb0df24c10', '2025-11-14 17:55:42', 0, '2025-11-14 23:55:42'),
(139, 1, '842a4ea24e76026e78a780eaf4303d753217f6a2208d0f106f73f556b4a8edaf', '2025-11-14 17:55:51', 0, '2025-11-14 23:55:51'),
(140, 17, 'b7a5ccd1a33091f3adfc4d8b423645f937d5657af9d7361f2d193cabf057ac15', '2025-11-14 20:21:58', 1, '2025-11-15 02:21:58'),
(141, 1, '0a6f47b9fdafc9b6b77d1ca71df498333359692cbe41ea18c08f1083870caa34', '2025-11-14 20:23:34', 1, '2025-11-15 02:23:34'),
(142, 1, 'd2f28f9824770935132fac03bbdc13a43a50f1b07e836bd9b17be81cf1d68769', '2025-11-15 11:41:36', 0, '2025-11-15 17:41:36'),
(143, 17, '9e67aa79475aa6267d26141a8be5e805ea713d67c98105ff448c5e333c44e2f7', '2025-11-15 11:41:43', 0, '2025-11-15 17:41:43'),
(144, 16, '4b1e484260436e29b009321aff01699a2801f1f97fb6cfb431aa380ea83ca000', '2025-11-15 11:41:56', 0, '2025-11-15 17:41:56'),
(145, 1, 'ad236d8dd4b6143361c028f5a1cb26ccf4056ac9d3e2747e148f5e5a27ce3b27', '2025-11-15 13:47:09', 0, '2025-11-15 19:47:09'),
(146, 16, '1c3bc4d283797c5c4f191698328dd97e08a38aed92635a7619cd673903ddc4a0', '2025-11-15 13:54:52', 0, '2025-11-15 19:54:52'),
(147, 17, '7600a992af05c78776962e718548c21b6443d031f0cb533b5011432e3c275674', '2025-11-15 13:55:00', 0, '2025-11-15 19:55:00'),
(148, 16, '3b389137c25b846705db5eadc0f8755e05e5dc9b2801be288d2fcafd464f2c06', '2025-11-15 16:48:03', 0, '2025-11-15 22:48:03'),
(149, 1, 'c9aaaed715888c122522ea1218e97d87b830d8d2fa8a4c335c894bb8f4030214', '2025-11-15 16:48:10', 0, '2025-11-15 22:48:10'),
(150, 17, '64104b09697039aed7f79efb80fa57d8f9fe4b3154488ccaa86c3c84ac4f50de', '2025-11-15 16:59:53', 0, '2025-11-15 22:59:53'),
(151, 1, 'e49096d02cb54208db070ef1549415c051af140d7dd97b6025af3c0b00aa5c52', '2025-11-15 18:54:51', 0, '2025-11-16 00:54:51'),
(152, 16, '40d91ab4d83df3b598d7a1807d0f7a92690fb4cd350a17769ca7d785954af115', '2025-11-15 18:54:59', 1, '2025-11-16 00:54:59'),
(153, 17, 'a90bf11be5a3bf3bfa1f02d5fcd20262e7e7eeddb8e20081e8c8fbda6e49d872', '2025-11-15 19:05:35', 1, '2025-11-16 01:05:35'),
(154, 16, '2f09d110609fb3e03488f7f582b3b2b4d757db149f819fa71fe4bb694a553c44', '2025-11-15 20:47:52', 1, '2025-11-16 02:47:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annual_budget`
--
ALTER TABLE `annual_budget`
  ADD PRIMARY KEY (`annual_budget_id`),
  ADD KEY `fiscal_year_id` (`fiscal_year_id`),
  ADD KEY `submitted_by_user_id` (`submitted_by_user_id`),
  ADD KEY `updated_by_user_id` (`updated_by_user_id`);

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
-- AUTO_INCREMENT for table `annual_budget`
--
ALTER TABLE `annual_budget`
  MODIFY `annual_budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `app`
--
ALTER TABLE `app`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `budget_allocation`
--
ALTER TABLE `budget_allocation`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  MODIFY `fiscal_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `ppmp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `ppmp_items`
--
ALTER TABLE `ppmp_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

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
  MODIFY `session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `annual_budget`
--
ALTER TABLE `annual_budget`
  ADD CONSTRAINT `annual_budget_ibfk_1` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`fiscal_year_id`),
  ADD CONSTRAINT `annual_budget_ibfk_2` FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `annual_budget_ibfk_3` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`user_id`);

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
