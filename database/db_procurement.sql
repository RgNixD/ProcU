-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 02:32 PM
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
  `submitted_by_user_id` int(11) DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_items`
--

CREATE TABLE `app_items` (
  `app_item_id` int(11) NOT NULL,
  `app_version_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `item_name_id` int(11) NOT NULL,
  `item_description` text NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `total_cost` decimal(15,2) NOT NULL,
  `mode_of_procurement` int(10) UNSIGNED DEFAULT NULL,
  `pre_procurement_conference` varchar(20) DEFAULT NULL,
  `bid_cat_id` int(11) DEFAULT NULL,
  `procurement_start_date` date DEFAULT NULL,
  `bidding_date` date DEFAULT NULL,
  `source_of_funds` varchar(150) DEFAULT NULL,
  `proc_strat_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `consolidation_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_item_sources`
--

CREATE TABLE `app_item_sources` (
  `app_item_source_id` int(11) NOT NULL,
  `app_item_id` int(11) NOT NULL,
  `ppmp_version_id` int(11) NOT NULL,
  `ppmp_version_item_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost` decimal(15,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_versions`
--

CREATE TABLE `app_versions` (
  `app_version_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `version_no` int(11) NOT NULL,
  `status` enum('Draft','Finalized','Superseded') DEFAULT 'Draft',
  `based_on_app_version_id` int(11) DEFAULT NULL,
  `triggered_by_ppmp_version_id` int(11) DEFAULT NULL,
  `finalized_by` int(11) DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
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
(734, 1, 'Updated Fiscal Year 2028', 'fiscal_years', 6, '2026-05-08 14:56:26'),
(735, 1, 'Updated Fiscal Year 2029', 'fiscal_years', 8, '2026-05-08 14:56:35'),
(736, 1, 'Updated Fiscal Year 2027', 'fiscal_years', 3, '2026-05-08 14:57:36'),
(737, 1, 'Updated Fiscal Year 2027', 'fiscal_years', 3, '2026-05-08 14:57:36'),
(738, 1, 'Updated Fiscal Year 2028', 'fiscal_years', 6, '2026-05-08 14:59:36'),
(739, 1, 'Updated Fiscal Year 2027', 'fiscal_years', 3, '2026-05-08 14:59:42'),
(740, 1, 'Added new Annual Budget for Fiscal Year ID: 6', 'annual_budget', 2, '2026-05-08 15:01:03'),
(741, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 4, '2026-05-08 15:01:37'),
(742, 17, 'Added budget allocation (₱1,000,000.00) for Office', 'budget_allocation', 5, '2026-05-08 15:01:47'),
(743, 16, 'Created PPMP Version 1', 'ppmp_versions', 4, '2026-05-08 15:08:36'),
(744, 23, 'Created PPMP Version 1', 'ppmp_versions', 5, '2026-05-08 15:11:24'),
(745, 1, 'Reviewed PPMP PPMP-2026-48E190 → Approved', 'ppmp', 4, '2026-05-08 15:12:56'),
(746, 1, 'Reviewed PPMP PPMP-2026-C11659 → Approved', 'ppmp', 5, '2026-05-08 15:13:04'),
(747, 1, 'Enabled PPMP revision for office IDs: 5', 'ppmp_versions', 0, '2026-05-08 15:20:01'),
(748, 16, 'Updated PPMP (PPMP-2026-48E190) to version 2 with ', 'ppmp', 4, '2026-05-08 15:22:47'),
(749, 1, 'Reviewed PPMP PPMP-2026-48E190 → Approved', 'ppmp', 4, '2026-05-08 15:23:20'),
(750, 1, 'Updated Fiscal Year 2028', 'fiscal_years', 6, '2026-05-08 15:23:53'),
(751, 1, 'Added new item name: Beaker (Sub-category ID: 4)', 'item_names', 70, '2026-05-09 10:48:10'),
(752, 1, 'Added new item name: Test Tube (Sub-category ID: 4', 'item_names', 71, '2026-05-09 10:48:23'),
(753, 1, 'Added new item name: Microscope (Sub-category ID: ', 'item_names', 72, '2026-05-09 10:49:44'),
(754, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 73, '2026-05-09 10:49:56'),
(755, 1, 'Added new sub-category: ICT Equipment (Category ID', 'sub_categories', 26, '2026-05-09 10:51:57'),
(756, 1, 'Added new item name: Desktop Computer (Sub-categor', 'item_names', 74, '2026-05-09 10:52:19'),
(757, 1, 'Added new item name: Printer (Sub-category ID: 26)', 'item_names', 75, '2026-05-09 10:52:32'),
(758, 1, 'Added new item name: Projector (Sub-category ID: 2', 'item_names', 76, '2026-05-09 10:52:49'),
(759, 1, 'Added new item name: Software License (Sub-categor', 'item_names', 77, '2026-05-09 10:53:37'),
(760, 1, 'Added new item name: Cloud Storage Subscription (S', 'item_names', 78, '2026-05-09 10:53:47'),
(761, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 79, '2026-05-09 10:58:23'),
(762, 1, 'Added new item name: Computer Repair Service (Sub-', 'item_names', 80, '2026-05-09 10:58:33'),
(763, 1, 'Added new item name: Printer Repair Service (Sub-c', 'item_names', 81, '2026-05-09 10:58:46'),
(764, 1, 'Added new item name: Fixtures (Sub-category ID: 7)', 'item_names', 82, '2026-05-09 10:59:00'),
(765, 1, 'Updated item name: Office Table (Sub-category ID: ', 'item_names', 82, '2026-05-09 10:59:11'),
(766, 1, 'Added new item name: Office Chair (Sub-category ID', 'item_names', 83, '2026-05-09 10:59:22'),
(767, 1, 'Added new item name: Filing Cabinet (Sub-category ', 'item_names', 84, '2026-05-09 10:59:33'),
(768, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 85, '2026-05-09 10:59:44'),
(769, 1, 'Added new item name: Hotel Room Accommodation (Sub', 'item_names', 86, '2026-05-09 10:59:55'),
(770, 1, 'Added new item name: Training Accommodation Servic', 'item_names', 87, '2026-05-09 11:00:07'),
(771, 1, 'Added new item name: Function Hall Rental (Sub-cat', 'item_names', 88, '2026-05-09 11:00:16'),
(772, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 89, '2026-05-09 11:00:25'),
(773, 1, 'Added new item name: Photocopier (Sub-category ID:', 'item_names', 90, '2026-05-09 11:00:36'),
(774, 1, 'Added new item name: Printer (Sub-category ID: 9)', 'item_names', 91, '2026-05-09 11:00:45'),
(775, 1, 'Added new item name: Scanner (Sub-category ID: 9)', 'item_names', 92, '2026-05-09 11:00:57'),
(776, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 93, '2026-05-09 11:01:06'),
(777, 1, 'Added new item name: Office Supplies (Sub-category', 'item_names', 94, '2026-05-09 11:01:21'),
(778, 1, 'Updated item name: Ballpen (Sub-category ID: 10)', 'item_names', 94, '2026-05-09 11:01:33'),
(779, 1, 'Added new item name: Bond Paper (Sub-category ID: ', 'item_names', 95, '2026-05-09 11:01:45'),
(780, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 96, '2026-05-09 11:01:59'),
(781, 1, 'Added new item name: Electrical Wire (Sub-category', 'item_names', 97, '2026-05-09 11:02:22'),
(782, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 98, '2026-05-09 11:02:33'),
(783, 1, 'Added new item name: Packed Meals (Sub-category ID', 'item_names', 99, '2026-05-09 11:02:47'),
(784, 1, 'Added new item name: Snacks / Refreshments (Sub-ca', 'item_names', 100, '2026-05-09 11:03:09'),
(785, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 101, '2026-05-09 11:03:26'),
(786, 1, 'Added new item name: School Building Construction ', 'item_names', 102, '2026-05-09 11:03:49'),
(787, 1, 'Added new item name: v (Sub-category ID: 14)', 'item_names', 103, '2026-05-09 11:04:02'),
(788, 1, 'Updated item name: Classroom Building Construction', 'item_names', 103, '2026-05-09 11:04:10'),
(789, 1, 'Added new item name: b (Sub-category ID: 14)', 'item_names', 104, '2026-05-09 11:04:20'),
(790, 1, 'Updated item name: Laboratory Building Constructio', 'item_names', 104, '2026-05-09 11:04:30'),
(791, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 105, '2026-05-09 11:04:45'),
(792, 1, 'Added new item name: Classroom Renovation (Sub-cat', 'item_names', 106, '2026-05-09 11:05:38'),
(793, 1, 'Added new item name: Office Renovation (Sub-catego', 'item_names', 107, '2026-05-09 11:05:50'),
(794, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 108, '2026-05-09 11:06:04'),
(795, 1, 'Added new item name: Comfort Room Improvement (Sub', 'item_names', 109, '2026-05-09 11:06:17'),
(796, 1, 'Added new item name: Walkway Improvement (Sub-cate', 'item_names', 110, '2026-05-09 11:06:25'),
(797, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 111, '2026-05-09 11:06:37'),
(798, 1, 'Added new item name: Lighting Installation (Sub-ca', 'item_names', 112, '2026-05-09 11:07:05'),
(799, 1, 'Added new item name: Power Supply Installation (Su', 'item_names', 113, '2026-05-09 11:07:13'),
(800, 1, 'Added new item name: Generator Installation (Sub-c', 'item_names', 114, '2026-05-09 11:07:26'),
(801, 1, 'Added new item name: Drainage System Installation ', 'item_names', 115, '2026-05-09 11:07:35'),
(802, 1, 'Added new item name: Pipe Replacement Works (Sub-c', 'item_names', 116, '2026-05-09 11:07:48'),
(803, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 117, '2026-05-09 11:07:57'),
(804, 1, 'Added new item name: Classroom Repair (Sub-categor', 'item_names', 118, '2026-05-09 11:08:12'),
(805, 1, 'Added new item name: Office Repair (Sub-category I', 'item_names', 119, '2026-05-09 11:08:23'),
(806, 1, 'Added new item name: Furniture Repair (Sub-categor', 'item_names', 120, '2026-05-09 11:08:33'),
(807, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 121, '2026-05-09 11:09:20'),
(808, 1, 'Added new item name: Building Planning Consultancy', 'item_names', 122, '2026-05-09 11:09:29'),
(809, 1, 'Added new item name: Site Development Planning Ser', 'item_names', 123, '2026-05-09 11:09:40'),
(810, 1, 'Added new item name: Interior Space Planning Servi', 'item_names', 124, '2026-05-09 11:09:49'),
(811, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 125, '2026-05-09 11:10:01'),
(812, 1, 'Added new item name: Structural Engineering Consul', 'item_names', 126, '2026-05-09 11:10:17'),
(813, 1, 'Added new item name: Electrical System Design Serv', 'item_names', 127, '2026-05-09 11:10:26'),
(814, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 128, '2026-05-09 11:10:39'),
(815, 1, 'Added new item name: Software Development Consulta', 'item_names', 129, '2026-05-09 11:10:54'),
(816, 1, 'Added new item name: IT Infrastructure Planning Se', 'item_names', 130, '2026-05-09 11:11:03'),
(817, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 131, '2026-05-09 11:11:13'),
(818, 1, 'Added new item name: Project Management/Constructi', 'item_names', 132, '2026-05-09 11:11:26'),
(819, 1, 'Added new item name: Budget Planning and Analysis ', 'item_names', 133, '2026-05-09 11:11:37'),
(820, 1, 'Added new item name: Documentation Review Services', 'item_names', 134, '2026-05-09 11:11:47'),
(821, 1, 'Updated Fiscal Year 2027', 'fiscal_years', 3, '2026-05-09 11:12:09'),
(822, 1, 'Updated user (Sectors): OVPPDSC Number 5', 'users', 24, '2026-05-09 11:13:48'),
(823, 1, 'Updated user (Sectors): OVPRE Number 4', 'users', 23, '2026-05-09 11:14:11'),
(824, 1, 'Updated user (Sectors): OVPAF  Number 3', 'users', 21, '2026-05-09 11:14:31'),
(825, 1, 'Updated user (Sectors): OVPAA Number 2', 'users', 20, '2026-05-09 11:14:48'),
(826, 1, 'Updated user (Sectors): OP Number 1', 'users', 16, '2026-05-09 11:15:02'),
(827, 1, 'Updated user (Budget Office): Budget Officer Numbe', 'users', 17, '2026-05-09 11:15:16'),
(828, 1, 'Updated profile picture', 'users', 1, '2026-05-09 11:21:26'),
(829, 1, 'Updated profile information', 'users', 1, '2026-05-09 11:21:40'),
(830, 16, 'Updated profile picture', 'users', 16, '2026-05-09 11:23:08'),
(831, 1, 'Added new office: Office of the President', 'offices', 12, '2026-05-09 11:24:54'),
(832, 1, 'Added new office: Office of the Vice President for', 'offices', 13, '2026-05-09 11:25:10'),
(833, 1, 'Added new office: Office of the Vice President for', 'offices', 14, '2026-05-09 11:25:24'),
(834, 1, 'Added new office: Office of the Vice President for', 'offices', 15, '2026-05-09 11:25:37'),
(835, 1, 'Added new office: Office of the Vice President for', 'offices', 16, '2026-05-09 11:25:52'),
(836, 1, 'Updated user (Sectors): OVPAF  Number 3', 'users', 21, '2026-05-09 11:26:52'),
(837, 21, 'Updated profile picture', 'users', 21, '2026-05-09 11:29:13'),
(838, 20, 'Updated profile picture', 'users', 20, '2026-05-09 11:29:34'),
(839, 23, 'Updated profile picture', 'users', 23, '2026-05-09 11:29:53'),
(840, 24, 'Updated profile picture', 'users', 24, '2026-05-09 11:30:17'),
(841, 1, 'Added new Annual Budget for Fiscal Year ID: 3', 'annual_budget', 3, '2026-05-09 11:32:11'),
(842, 17, 'Updated profile picture', 'users', 17, '2026-05-09 11:32:42'),
(843, 17, 'Added budget allocation (₱5,000,000.00) for Office', 'budget_allocation', 6, '2026-05-09 11:37:50'),
(844, 17, 'Added budget allocation (₱5,000,000.00) for Office', 'budget_allocation', 7, '2026-05-09 11:39:35'),
(845, 17, 'Added budget allocation (₱5,000,000.00) for Office', 'budget_allocation', 8, '2026-05-09 11:39:41'),
(846, 17, 'Added budget allocation (₱5,000,000.00) for Office', 'budget_allocation', 9, '2026-05-09 11:39:47'),
(847, 17, 'Added budget allocation (₱5,000,000.00) for Office', 'budget_allocation', 10, '2026-05-09 11:40:42'),
(848, 1, 'Added new item name: Laptop (Sub-category ID: 26)', 'item_names', 135, '2026-05-09 11:59:45'),
(849, 16, 'Created PPMP Version 1', 'ppmp_versions', 7, '2026-05-09 12:20:46'),
(850, 20, 'Created PPMP Version 1', 'ppmp_versions', 8, '2026-05-09 12:38:35'),
(851, 21, 'Created PPMP Version 1', 'ppmp_versions', 9, '2026-05-09 12:52:08'),
(852, 24, 'Created PPMP Version 1', 'ppmp_versions', 10, '2026-05-09 12:55:19'),
(853, 23, 'Created PPMP Version 1', 'ppmp_versions', 11, '2026-05-09 14:25:03'),
(854, 1, 'Reviewed PPMP PPMP-2026-E842C6 → Approved', 'ppmp', 6, '2026-05-09 14:25:46'),
(855, 1, 'Reviewed PPMP PPMP-2026-B47BC6 → Approved', 'ppmp', 7, '2026-05-09 14:25:54'),
(856, 1, 'Reviewed PPMP PPMP-2026-8AB507 → Approved', 'ppmp', 8, '2026-05-09 14:26:02'),
(857, 1, 'Reviewed PPMP PPMP-2026-7D84B8 → Approved', 'ppmp', 9, '2026-05-09 14:26:10'),
(858, 1, 'Reviewed PPMP PPMP-2026-F76129 → Approved', 'ppmp', 10, '2026-05-09 14:26:17'),
(859, 1, 'Approved PPMP revision request', 'ppmp', 7, '2026-05-09 14:49:15'),
(860, 20, 'Updated PPMP (PPMP-2026-B47BC6) to version 2 with ', 'ppmp', 7, '2026-05-09 15:03:38'),
(861, 1, 'Reviewed PPMP PPMP-2026-B47BC6 → Approved', 'ppmp', 7, '2026-05-09 15:04:15'),
(862, 17, 'Updated Annual Budget ID: 3 for Fiscal Year ID: 3', 'annual_budget', 3, '2026-05-09 15:08:22'),
(863, 17, 'Updated budget allocation (₱10,000,000.00) for Off', 'budget_allocation', 9, '2026-05-09 15:10:44'),
(864, 1, 'Enabled PPMP revision for office IDs: 16', 'ppmp_versions', 0, '2026-05-09 15:13:46'),
(865, 1, 'Updated profile picture', 'users', 1, '2026-05-17 20:01:57'),
(866, 16, 'Updated profile picture', 'users', 16, '2026-05-17 20:02:40'),
(867, 21, 'Updated profile picture', 'users', 21, '2026-05-17 20:03:26'),
(868, 23, 'Updated profile picture', 'users', 23, '2026-05-17 20:04:01'),
(869, 24, 'Updated profile picture', 'users', 24, '2026-05-17 20:04:53'),
(870, 1, 'Added new office: Office of the President', 'offices', 17, '2026-05-17 20:07:09'),
(871, 1, 'Added new office: Office of the Vice President for', 'offices', 18, '2026-05-17 20:07:24'),
(872, 1, 'Added new office: Office of the Vice President for', 'offices', 19, '2026-05-17 20:07:39'),
(873, 1, 'Added new office: Office of the Vice President for', 'offices', 20, '2026-05-17 20:07:53'),
(874, 1, 'Added new office: Office of the Vice President for', 'offices', 21, '2026-05-17 20:08:08'),
(875, 1, 'Added new item name: Beaker (Sub-category ID: 4)', 'item_names', 136, '2026-05-17 20:10:43'),
(876, 1, 'Added new item name: Test Tube (Sub-category ID: 4', 'item_names', 137, '2026-05-17 20:10:54'),
(877, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 138, '2026-05-17 20:11:44'),
(878, 1, 'Added new item name: Internet Subscription (Sub-ca', 'item_names', 139, '2026-05-17 20:11:55'),
(879, 1, 'Added new item name: Software License (Sub-categor', 'item_names', 140, '2026-05-17 20:12:06'),
(880, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 141, '2026-05-17 20:12:36'),
(881, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 142, '2026-05-17 20:13:21'),
(882, 1, 'Added new item name: Office Table (Sub-category ID', 'item_names', 143, '2026-05-17 20:13:49'),
(883, 1, 'Added new item name: Office Chair (Sub-category ID', 'item_names', 144, '2026-05-17 20:14:05'),
(884, 1, 'Added new item name: Hotel Room Accommodation (Sub', 'item_names', 145, '2026-05-17 20:14:19'),
(885, 1, 'Added new item name: Printer (Sub-category ID: 9)', 'item_names', 146, '2026-05-17 20:14:36'),
(886, 1, 'Added new item name: Shredder (Sub-category ID: 9)', 'item_names', 147, '2026-05-17 20:14:49'),
(887, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 148, '2026-05-17 20:15:37'),
(888, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 149, '2026-05-17 20:15:59'),
(889, 1, 'Added new item name: Electrical Wire (Sub-category', 'item_names', 150, '2026-05-17 20:16:08'),
(890, 1, 'Added new item name: Packed Meals (Sub-category ID', 'item_names', 151, '2026-05-17 20:16:33'),
(891, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 152, '2026-05-17 20:17:05'),
(892, 1, 'Added new item name: School Building Construction ', 'item_names', 153, '2026-05-17 20:17:20'),
(893, 1, 'Added new item name: Classroom Building Constructi', 'item_names', 154, '2026-05-17 20:18:37'),
(894, 1, 'Added new item name: Classroom Renovation (Sub-cat', 'item_names', 155, '2026-05-17 20:18:53'),
(895, 1, 'Added new item name: Building Renovation (Sub-cate', 'item_names', 156, '2026-05-17 20:19:02'),
(896, 1, 'Added new item name: Facility Improvement (Sub-cat', 'item_names', 157, '2026-05-17 20:19:14'),
(897, 1, 'Added new item name: Comfort Room Improvement (Sub', 'item_names', 158, '2026-05-17 20:19:24'),
(898, 1, 'Added new item name: Electrical Wiring Installatio', 'item_names', 159, '2026-05-17 20:19:34'),
(899, 1, 'Added new item name: Lighting Installation (Sub-ca', 'item_names', 160, '2026-05-17 20:19:43'),
(900, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 161, '2026-05-17 20:20:54'),
(901, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 162, '2026-05-17 20:21:03'),
(902, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 163, '2026-05-17 20:21:13'),
(903, 1, 'Added new item name: Drainage System Installation ', 'item_names', 164, '2026-05-17 20:21:32'),
(904, 1, 'Added new item name: Pipe Replacement Works (Sub-c', 'item_names', 165, '2026-05-17 20:21:44'),
(905, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 166, '2026-05-17 20:22:10'),
(906, 1, 'Added new item name: Classroom Repair (Sub-categor', 'item_names', 167, '2026-05-17 20:23:30'),
(907, 1, 'Added new item name: Office Repair (Sub-category I', 'item_names', 168, '2026-05-17 20:23:39'),
(908, 1, 'Added new item name: Ceiling Repair (Sub-category ', 'item_names', 169, '2026-05-17 20:23:50'),
(909, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 170, '2026-05-17 20:23:59'),
(910, 1, 'Added new item name: Architectural Design Services', 'item_names', 171, '2026-05-17 20:24:13'),
(911, 1, 'Added new item name: Building Planning Consultancy', 'item_names', 172, '2026-05-17 20:24:23'),
(912, 1, 'Added new item name: Site Development Planning Ser', 'item_names', 173, '2026-05-17 20:24:35'),
(913, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 174, '2026-05-17 20:24:43'),
(914, 1, 'Added new item name: Structural Engineering Consul', 'item_names', 175, '2026-05-17 20:24:59'),
(915, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 176, '2026-05-17 20:25:28'),
(916, 1, 'Added new item name: System Analysis and Design Se', 'item_names', 177, '2026-05-17 20:25:41'),
(917, 1, 'Added new item name: IT Systems Development Consul', 'item_names', 178, '2026-05-17 20:25:50'),
(918, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 179, '2026-05-17 20:26:02'),
(919, 1, 'Added new item name: Project Management Services (', 'item_names', 180, '2026-05-17 20:26:16'),
(920, 1, 'Added new item name: Construction Supervision Serv', 'item_names', 181, '2026-05-17 20:26:25'),
(921, 1, 'Added new item name: Financial Advisory Services (', 'item_names', 182, '2026-05-17 20:26:37'),
(922, 1, 'Added new item name: Accounting Consultancy Servic', 'item_names', 183, '2026-05-17 20:26:46'),
(923, 1, 'Added new item name: Cost Estimation and Analysis ', 'item_names', 184, '2026-05-17 20:26:55'),
(924, 1, 'Added new item name: Contract Review and Drafting ', 'item_names', 185, '2026-05-17 20:27:06'),
(925, 1, 'Added new item name: Documentation Review Services', 'item_names', 186, '2026-05-17 20:27:16'),
(926, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 187, '2026-05-17 20:27:42'),
(927, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 188, '2026-05-17 20:28:14'),
(928, 1, 'Added new item name: Others (Specify Item Name & D', 'item_names', 189, '2026-05-17 20:28:34'),
(929, 17, 'Updated profile picture', 'users', 17, '2026-05-17 20:29:27'),
(930, 20, 'Updated profile picture', 'users', 20, '2026-05-17 20:29:48'),
(931, 1, 'Updated user (Sectors): OVPPDSC Number 5', 'users', 24, '2026-05-17 20:30:41'),
(932, 1, 'Updated user (Sectors): OVPRE Number 4', 'users', 23, '2026-05-17 20:30:54'),
(933, 1, 'Updated user (Sectors): OVPAF  Number 3', 'users', 21, '2026-05-17 20:31:02'),
(934, 1, 'Updated user (Sectors): OVPAA Number 2', 'users', 20, '2026-05-17 20:31:15'),
(935, 1, 'Updated user (Sectors): OP Number 1', 'users', 16, '2026-05-17 20:31:23');

-- --------------------------------------------------------

--
-- Table structure for table `bidding_category`
--

CREATE TABLE `bidding_category` (
  `bid_cat_ID` int(11) NOT NULL,
  `bid_cat_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bidding_category`
--

INSERT INTO `bidding_category` (`bid_cat_ID`, `bid_cat_name`, `created_at`) VALUES
(1, 'Lowest Calculated Responsibe Bid or LCRB', '2025-12-08 19:01:01'),
(2, 'Most Economically Advantageous Responsive Bid or MEARB', '2025-12-08 19:01:07'),
(3, 'Most Advantageous Responsive Bid or MARB', '2025-12-08 19:01:15'),
(4, 'Highest/Single Rated Responsive Bid or HRRB/SRRV', '2025-12-08 19:01:20'),
(5, 'Lowest Comparative or Competitive Responsive Bid or LCCRB', '2025-12-08 19:01:26');

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
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fiscal_years`
--

CREATE TABLE `fiscal_years` (
  `fiscal_year_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `is_lock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fiscal_years`
--

INSERT INTO `fiscal_years` (`fiscal_year_id`, `year`, `start_date`, `end_date`, `status`, `is_lock`, `created_at`) VALUES
(3, 2027, '2027-01-01', '2027-12-31', 1, 0, '2026-05-09 06:49:12'),
(6, 2028, '2028-01-01', '2028-12-31', 0, 1, '2026-05-09 03:12:09'),
(8, 2029, '2029-01-01', '2029-12-31', 0, 1, '2026-05-08 06:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `fiscal_year_reopen_logs`
--

CREATE TABLE `fiscal_year_reopen_logs` (
  `reopen_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `opened_by` int(11) NOT NULL,
  `reason` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `opened_at` datetime DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(136, 4, 'Beaker', '2026-05-17 12:10:43', '2026-05-17 12:10:43'),
(137, 4, 'Test Tube', '2026-05-17 12:10:54', '2026-05-17 12:10:54'),
(138, 4, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:11:44', '2026-05-17 12:11:44'),
(139, 5, 'Internet Subscription', '2026-05-17 12:11:55', '2026-05-17 12:11:55'),
(140, 5, 'Software License', '2026-05-17 12:12:06', '2026-05-17 12:12:06'),
(141, 5, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:12:36', '2026-05-17 12:12:36'),
(142, 7, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:13:21', '2026-05-17 12:13:21'),
(143, 7, 'Office Table', '2026-05-17 12:13:49', '2026-05-17 12:13:49'),
(144, 7, 'Office Chair', '2026-05-17 12:14:05', '2026-05-17 12:14:05'),
(145, 8, 'Hotel Room Accommodation', '2026-05-17 12:14:19', '2026-05-17 12:14:19'),
(146, 9, 'Printer', '2026-05-17 12:14:36', '2026-05-17 12:14:36'),
(147, 9, 'Shredder', '2026-05-17 12:14:49', '2026-05-17 12:14:49'),
(148, 9, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:15:35', '2026-05-17 12:15:35'),
(149, 11, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:15:59', '2026-05-17 12:15:59'),
(150, 11, 'Electrical Wire', '2026-05-17 12:16:08', '2026-05-17 12:16:08'),
(151, 12, 'Packed Meals', '2026-05-17 12:16:33', '2026-05-17 12:16:33'),
(152, 12, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:17:05', '2026-05-17 12:17:05'),
(153, 14, 'School Building Construction', '2026-05-17 12:17:20', '2026-05-17 12:17:20'),
(154, 19, 'Classroom Building Construction', '2026-05-17 12:18:37', '2026-05-17 12:18:37'),
(155, 15, 'Classroom Renovation', '2026-05-17 12:18:53', '2026-05-17 12:18:53'),
(156, 15, 'Building Renovation', '2026-05-17 12:19:02', '2026-05-17 12:19:02'),
(157, 16, 'Facility Improvement', '2026-05-17 12:19:14', '2026-05-17 12:19:14'),
(158, 16, 'Comfort Room Improvement', '2026-05-17 12:19:24', '2026-05-17 12:19:24'),
(159, 17, 'Electrical Wiring Installation', '2026-05-17 12:19:34', '2026-05-17 12:19:34'),
(160, 17, 'Lighting Installation', '2026-05-17 12:19:43', '2026-05-17 12:19:43'),
(161, 15, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:20:54', '2026-05-17 12:20:54'),
(162, 16, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:21:03', '2026-05-17 12:21:03'),
(163, 17, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:21:13', '2026-05-17 12:21:13'),
(164, 18, 'Drainage System Installation', '2026-05-17 12:21:32', '2026-05-17 12:21:32'),
(165, 18, 'Pipe Replacement Works', '2026-05-17 12:21:44', '2026-05-17 12:21:44'),
(166, 18, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:22:10', '2026-05-17 12:22:10'),
(167, 19, 'Classroom Repair', '2026-05-17 12:23:30', '2026-05-17 12:23:30'),
(168, 19, 'Office Repair', '2026-05-17 12:23:39', '2026-05-17 12:23:39'),
(169, 19, 'Ceiling Repair', '2026-05-17 12:23:50', '2026-05-17 12:23:50'),
(170, 19, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:23:59', '2026-05-17 12:23:59'),
(171, 20, 'Architectural Design Services', '2026-05-17 12:24:13', '2026-05-17 12:24:13'),
(172, 20, 'Building Planning Consultancy Services', '2026-05-17 12:24:23', '2026-05-17 12:24:23'),
(173, 20, 'Site Development Planning Services', '2026-05-17 12:24:35', '2026-05-17 12:24:35'),
(174, 20, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:24:43', '2026-05-17 12:24:43'),
(175, 21, 'Structural Engineering Consultancy Services', '2026-05-17 12:24:59', '2026-05-17 12:24:59'),
(176, 21, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:25:28', '2026-05-17 12:25:28'),
(177, 22, 'System Analysis and Design Services', '2026-05-17 12:25:41', '2026-05-17 12:25:41'),
(178, 22, 'IT Systems Development Consultancy Services', '2026-05-17 12:25:50', '2026-05-17 12:25:50'),
(179, 22, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:26:02', '2026-05-17 12:26:02'),
(180, 23, 'Project Management Services', '2026-05-17 12:26:16', '2026-05-17 12:26:16'),
(181, 23, 'Construction Supervision Services', '2026-05-17 12:26:25', '2026-05-17 12:26:25'),
(182, 24, 'Financial Advisory Services', '2026-05-17 12:26:37', '2026-05-17 12:26:37'),
(183, 24, 'Accounting Consultancy Services', '2026-05-17 12:26:46', '2026-05-17 12:26:46'),
(184, 24, 'Cost Estimation and Analysis Services', '2026-05-17 12:26:55', '2026-05-17 12:26:55'),
(185, 25, 'Contract Review and Drafting Services', '2026-05-17 12:27:06', '2026-05-17 12:27:06'),
(186, 25, 'Documentation Review Services', '2026-05-17 12:27:16', '2026-05-17 12:27:16'),
(187, 24, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:27:42', '2026-05-17 12:27:42'),
(188, 23, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:28:14', '2026-05-17 12:28:14'),
(189, 25, 'Others (Specify Item Name & Details in ‘Specifications’)', '2026-05-17 12:28:34', '2026-05-17 12:28:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `office_id`, `fiscal_year_id`, `message`, `created_at`, `is_read`) VALUES
(8, 11, 3, 'Please submit your PPMP for Fiscal Year 2027. An admin attempted to consolidate approved PPMPs.', '2026-04-24 04:41:28', 0),
(9, 5, 3, 'Please submit your PPMP for Fiscal Year 2027. An admin attempted to consolidate approved PPMPs.', '2026-04-28 15:23:28', 1),
(10, 7, 3, 'Please submit your PPMP for Fiscal Year 2027. An admin attempted to consolidate approved PPMPs.', '2026-04-28 15:23:28', 0);

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
(17, 'Office of the President', '0001', 16, '', 1, '2026-05-17 20:07:09'),
(18, 'Office of the Vice President for Academic Affairs', '0002', 20, '', 1, '2026-05-17 20:07:24'),
(19, 'Office of the Vice President for Administration and Finance', '0003', 21, '', 1, '2026-05-17 20:07:39'),
(20, 'Office of the Vice President for Research and Extension', '0004', 23, '', 1, '2026-05-17 20:07:53'),
(21, 'Office of the Vice President for Planning, Development and Special Concerns', '0005', 24, '', 1, '2026-05-17 20:08:08');

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
(14, 23, 'sonerwin12@gmail.com', '228631', '2026-04-27 14:15:33', '2026-04-27 06:12:33');

-- --------------------------------------------------------

--
-- Table structure for table `ppmp`
--

CREATE TABLE `ppmp` (
  `ppmp_id` int(11) NOT NULL,
  `ppmp_code` varchar(50) NOT NULL,
  `office_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `current_version_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppmp_revision_requests`
--

CREATE TABLE `ppmp_revision_requests` (
  `revision_request_id` int(11) NOT NULL,
  `ppmp_id` int(11) NOT NULL,
  `ppmp_version_id` int(11) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `reason` text NOT NULL,
  `revision_phase` enum('Pre-Consolidation','Post-Finalization') NOT NULL,
  `status` enum('Requested','Approved','Rejected') DEFAULT 'Requested',
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `process_remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppmp_versions`
--

CREATE TABLE `ppmp_versions` (
  `ppmp_version_id` int(11) NOT NULL,
  `ppmp_id` int(11) NOT NULL,
  `version_no` int(11) NOT NULL,
  `status` enum('Draft','Pending','Approved','Rejected','Returned','Consolidated','Finalized','Archived') DEFAULT 'Draft',
  `lifecycle_source` enum('Initial Submission','Pre-Consolidation Revision','Post-APP Revision') DEFAULT 'Initial Submission',
  `is_editable` tinyint(1) DEFAULT 1,
  `based_on_version_id` int(11) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approve_reason` text DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `reject_reason` text DEFAULT NULL,
  `returned_by` int(11) DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `consolidated_at` datetime DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppmp_version_items`
--

CREATE TABLE `ppmp_version_items` (
  `ppmp_version_item_id` int(11) NOT NULL,
  `ppmp_version_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `item_name_id` int(11) NOT NULL,
  `item_description` text NOT NULL,
  `specifications` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `estimated_budget` decimal(15,2) NOT NULL,
  `total_cost` decimal(15,2) NOT NULL,
  `file_attachment` text DEFAULT NULL,
  `mode_of_procurement` int(10) UNSIGNED DEFAULT NULL,
  `pre_procurement_conference` varchar(20) DEFAULT NULL,
  `procurement_start_date` date DEFAULT NULL,
  `bidding_date` date DEFAULT NULL,
  `contract_signing_date` date DEFAULT NULL,
  `source_of_funds` varchar(150) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `procurement_modes`
--

CREATE TABLE `procurement_modes` (
  `proc_mode_id` int(10) UNSIGNED NOT NULL,
  `proc_mode_name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procurement_modes`
--

INSERT INTO `procurement_modes` (`proc_mode_id`, `proc_mode_name`, `created_at`) VALUES
(4, 'Competitive Bidding', '2026-04-18 12:38:31'),
(5, 'Limited Source Bidding', '2026-04-18 12:38:31'),
(6, 'Competitive Dialogue', '2026-04-18 12:38:31'),
(7, 'Unsolicited Offer with Bid Matching', '2026-04-18 12:38:31'),
(8, 'Direct Contracting', '2026-04-18 12:38:31'),
(9, 'Direct Acquisition', '2026-04-18 12:38:31'),
(10, 'Repeat Order', '2026-04-18 12:38:31'),
(11, 'Small Value Procurement', '2026-04-18 12:38:31'),
(12, 'Negotiated Procurement', '2026-04-18 12:38:31'),
(13, 'Direct Sales', '2026-04-18 12:38:31'),
(14, 'Direct Procurement for Science, Technology, and Innovation', '2026-04-18 12:38:31'),
(15, 'Common Use Supplies and Equipment', '2026-05-02 07:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `procurement_strategy`
--

CREATE TABLE `procurement_strategy` (
  `proc_strat_ID` int(11) NOT NULL,
  `proc_strat_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procurement_strategy`
--

INSERT INTO `procurement_strategy` (`proc_strat_ID`, `proc_strat_name`, `created_at`) VALUES
(1, 'Life Cycle Assessment (LCA) and Life Cycle Cost Analysis (LCCA)', '2025-12-08 19:06:38'),
(2, 'Subcontracting', '2025-12-08 19:06:44'),
(3, 'Multi-Year Contracting', '2025-12-08 19:06:51'),
(4, 'Design-and-Build Scheme for Infrastructure Projects', '2025-12-08 19:06:57'),
(5, 'Engagement of a Procurement Agent', '2025-12-08 19:07:04'),
(6, 'Use of Framework Agreement Section', '2025-12-08 19:07:09'),
(7, 'Pooled Procurement Section 17', '2025-12-08 19:07:18'),
(8, 'Renewal of Regular and Recurring Services', '2025-12-08 19:07:22'),
(9, 'Warehousing and Inventory Activities', '2025-12-08 19:07:30');

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
(25, 4, 'Legal Consultancy', 'Legal advisory and consultancy services', '2025-10-14 02:07:25', '2025-10-14 02:07:25'),
(26, 5, 'ICT Equipment', '', '2026-05-09 02:51:57', '2026-05-09 02:51:57');

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
(1, 'admin', '$2y$10$pZLsb/4e36DdhP7RFqQY0OPkHbWDdTQhee6i6ZSWUkm.BF2vPJd5.', 'admin@gmail.com', 'BAC Secretariat Head', 'BAC', '9509972084', 'bac_20260517_200157.png', 1, '2025-09-28 10:33:37', '2026-05-17 20:29:57'),
(16, 'sector1', '$2y$10$iDyW8R57OXQBmBqfP.FfmeRi6JZhbyVfii3Sxm7xtyUXsXj.Nrcha', 'sector1@gmail.com', 'OP', 'Number 1', '9509972082', 'number_1_20260517_200240.png', 1, '2025-10-06 19:54:42', '2026-05-17 20:02:17'),
(17, 'finance', '$2y$10$Zt8b1bx.XlL.z//DrMxVMe9VajlgyepHRL60qSTHU0Gy.71JlZQoS', 'finance@gmail.com', 'Budget Officer', 'Number 1', '9509972322', 'number_1_20260517_202927.png', 1, '2025-10-06 19:56:54', '2026-05-17 20:29:14'),
(20, 'sector2', '$2y$10$Zt8b1bx.XlL.z//DrMxVMe9VajlgyepHRL60qSTHU0Gy.71JlZQoS', 'sector2@gmail.com', 'OVPAA', 'Number 2', '9432432424', 'number_2_20260517_202948.png', 1, '2025-10-17 13:20:14', '2026-05-17 20:29:37'),
(21, 'sector3', '$2y$10$IAODdnhoSpmxTaHpwknwjun/h/tlJdXljli7AExk8sjT1TVmpCB4i', 'sector3@gmail.com', 'OVPAF ', 'Number 3', '9503943434', 'number_3_20260517_200326.png', 1, '2025-10-17 13:20:44', '2026-05-17 20:03:00'),
(23, 'sector4', '$2y$10$Zt8b1bx.XlL.z//DrMxVMe9VajlgyepHRL60qSTHU0Gy.71JlZQoS', 'sector4@gmail.com', 'OVPRE', 'Number 4', '9503442424', 'number_4_20260517_200401.png', 1, '2025-10-17 13:21:56', '2026-05-17 20:03:42'),
(24, 'sector5', '$2y$10$ubLF/7vUa9UKK1DINMNwTuCigY8upb3d.xm/.vBq.OhmSWuXVHxqa', 'sector5@gmail.com', 'OVPPDSC', 'Number 5', '9509223333', 'number_5_20260517_200453.png', 1, '2026-04-27 14:13:50', '2026-05-17 20:04:11');

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
(1, 1, 'Bac Sec Head', 'Procurement Head role access', 0, 1, 1, 1, 1, '2025-09-28 10:33:37'),
(116, 16, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-06 19:54:42'),
(117, 17, 'Budget Office', 'Budget Office role access', 0, 0, 0, 1, 1, '2025-10-06 19:56:54'),
(120, 20, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-17 13:20:14'),
(121, 21, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-17 13:20:44'),
(123, 23, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2025-10-17 13:21:56'),
(124, 24, 'Sectors', 'Sectors role access', 1, 0, 0, 0, 1, '2026-04-27 14:13:50');

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
(241, 1, 'a4d96792f7ca7465351fe52202c1f1afd8ef708b6e1e120517d87e61ce9fcab7', '2025-12-12 13:01:44', 1, '2025-12-12 19:01:44'),
(242, 16, 'b564c0eb0879b34c3e24d8abf21bd27359a32541733a8d20be35435c9944fe52', '2025-12-12 13:08:25', 0, '2025-12-12 19:08:25'),
(243, 1, '41a30d8b7c964dc23cf939a3ce47164867167eab6289fa859c6c4c73a5586a03', '2025-12-12 13:08:37', 1, '2025-12-12 19:08:37'),
(244, 1, '1a44a771d6b644d90e3a228c914774604d69f1c53ff21045d982f7ea05083c73', '2025-12-12 13:10:02', 0, '2025-12-12 19:10:02'),
(245, 1, '8476b7269fb5d9dee51428ec54a4927e798ee81caea32589f3a4ee00a6405619', '2025-12-12 13:10:30', 1, '2025-12-12 19:10:30'),
(246, 1, 'f53ba15f19edf5ffb8af84f5bceb211028c9bfdef742c70023d2823c9f545a5d', '2025-12-12 13:10:59', 1, '2025-12-12 19:10:59'),
(247, 1, '55ac43e59f28e4caeecdcc76f7e2581c4002f7629a93f3339bbab9ad055243a3', '2025-12-12 13:11:59', 0, '2025-12-12 19:11:59'),
(248, 1, '270956ba208e4bc51f3b3c6c4b12b28f362fff3c082e87dc0dd2874524c46881', '2025-12-12 13:13:22', 1, '2025-12-12 19:13:22'),
(249, 1, '90777e96726e931f2eac99460ebc07faee392a4812b99509a10bc8501523d1d6', '2025-12-12 13:13:53', 1, '2025-12-12 19:13:53'),
(250, 1, 'c9ba123eda3f46b2ce0a5eb80e2d2debde7274b35b4e6dcf4aafa1fbccb75ff0', '2025-12-12 13:15:06', 1, '2025-12-12 19:15:06'),
(251, 1, '6d32f7bf6ed957955bd35566aefa9d2494ee516349a1432b32e5e4e7ef1ba25d', '2025-12-12 13:16:24', 1, '2025-12-12 19:16:24'),
(252, 1, '1ae7d317a3f28bad99f0d9d62e153de2c408485fec9818b3ad5754f55b70b155', '2025-12-12 13:18:17', 1, '2025-12-12 19:18:17'),
(253, 1, '0fcba1dd83f35cb6cf35c76b66265a1902e856c121c222801c24549a5a674411', '2025-12-12 13:24:47', 1, '2025-12-12 19:24:47'),
(254, 16, '5e09f198cdb784c776b0dca54de1e88f3722a077719ae98688af2fe5a73fed93', '2025-12-12 13:28:12', 1, '2025-12-12 19:28:12'),
(255, 1, '3f7e4f58aa7aef554fa13a0c3270183f5a245fbd31591d2a62186bc0637dec33', '2025-12-12 13:29:14', 1, '2025-12-12 19:29:14'),
(256, 1, 'c69b32d66d8bce2e7f4242bda5f122968cd627ee0bbf6edab7d86cd28c2b07de', '2025-12-12 13:33:34', 1, '2025-12-12 19:33:34'),
(257, 1, 'af7c16a74ff51870d4ddb11ebd3e32b7cedd0048419d0d16e2f6f8ca92498c20', '2025-12-12 13:35:02', 1, '2025-12-12 19:35:02'),
(258, 1, '5224d52a077b65f91fb5de0e4d8c4facbcb94845cb233068356527cfad6f1c68', '2025-12-12 13:42:04', 1, '2025-12-12 19:42:04'),
(259, 1, 'd90f7b71bc1b7874c7439539ba607619074b05e5de7c37e503b9912cc79d769c', '2025-12-12 13:45:43', 1, '2025-12-12 19:45:43'),
(260, 1, 'c628b491a845c4ca43a60d5ef275c46367dc6b33dfa557467415bba105f4f888', '2025-12-12 13:47:23', 1, '2025-12-12 19:47:23'),
(261, 1, 'dbde1cd6a903085ef3351379e15c616c1438dc26469143d662dbb77855835f52', '2025-12-12 13:47:47', 1, '2025-12-12 19:47:47'),
(262, 1, '94b53ce8dc44667b0207418cdfcd7f5323d7555c7bdb59d9787604c4cd00211e', '2025-12-12 13:49:55', 1, '2025-12-12 19:49:55'),
(263, 1, '407543b382f75522e7c39cd3ad9c3e9af61263967cc6ea8eeb64d613d4265261', '2025-12-12 13:50:53', 1, '2025-12-12 19:50:53'),
(264, 1, '2104c6b5842a962dee7293cd3838980a2a61c922830fca7fda1e46a943b38a5f', '2025-12-12 13:51:17', 1, '2025-12-12 19:51:17'),
(265, 1, '108f06945d3f4f5f477e64409345e1b3f58a26241ed37066f8839c5174525b9b', '2025-12-12 13:51:53', 1, '2025-12-12 19:51:53'),
(266, 1, 'bb893929920f9c7aec2bb9e845d64e241847d101adb24bb999de485e27cacd73', '2025-12-12 13:52:33', 1, '2025-12-12 19:52:33'),
(267, 1, '3e6fe47e4efeb8ad104f0ce6c82d21d10e2d8d1676c97055ee8c634d315e28e3', '2025-12-12 13:53:00', 1, '2025-12-12 19:53:00'),
(268, 1, '1b1abd44afbe6bfae781115493f75db22203e0222a504723c61c5d8c95451798', '2025-12-12 13:59:17', 1, '2025-12-12 19:59:17'),
(269, 1, 'fde5d04ba50384ee951013dc38ca34ab419109aa6cf303a4000f7805c9466c70', '2025-12-12 14:02:31', 0, '2025-12-12 20:02:31'),
(270, 1, 'e955fd580edfaa46f9b3a514ee0c86599b3be89b061998ab82fe7a537ddd1a34', '2026-01-06 14:47:28', 0, '2026-01-06 20:47:28'),
(271, 16, '79158ea5d69c572ebb86dfbabfad5e904757d031ad1337cfd0e5e164126a77ad', '2026-01-06 14:51:47', 0, '2026-01-06 20:51:47'),
(272, 1, '1b9e01094402fd3b6f0a6dd2ce8b3f037544de8efc0e8dd62c801d9a2ed61c78', '2026-01-06 15:03:00', 0, '2026-01-06 21:03:00'),
(273, 1, 'de069cba39483998ab304c0f59fa08017487e3bd387a90e3680a6e9101a7f377', '2026-02-09 06:41:27', 1, '2026-02-09 12:41:27'),
(274, 17, '607ac3ef20b9888a65c4210f7f84869ab813b02854b0911ca68bb34733c4a346', '2026-02-09 06:41:34', 0, '2026-02-09 12:41:34'),
(275, 16, 'e693649f392aa90925e914e48ef2725147230a4268bbb4fbd0d14865a8d8c742', '2026-02-09 06:41:44', 0, '2026-02-09 12:41:44'),
(276, 1, 'e47c36d8f20b4850e74d023399e15917da6f38fca47b264a637d4e2c00ece043', '2026-02-09 07:08:39', 0, '2026-02-09 13:08:39'),
(277, 17, 'f8599405d1c1c9eb6526ab000eaa74314d0d18f360c903a626c5f73017de5566', '2026-02-09 08:49:37', 0, '2026-02-09 14:49:37'),
(278, 16, '9e9001215617a2873272ae58372276b8d4e6da8a6dd23ad36af4a1c1cf28e612', '2026-02-09 08:49:45', 1, '2026-02-09 14:49:45'),
(279, 20, '0782b5a2faa6a0b3cc15ac156faff0f8b3f92a68f9bce1b6b3549d4451cabd22', '2026-02-09 09:05:51', 1, '2026-02-09 15:05:51'),
(280, 17, '73f9b057a2d69aa01ee8401ddc8cd5325913013baf4bf23c7966b4445e7885b6', '2026-02-09 09:06:23', 1, '2026-02-09 15:06:23'),
(281, 1, '595c9c27173923baca52e624dd98327e4b8e2364ffab127edc52f2187374969a', '2026-02-09 09:12:07', 0, '2026-02-09 15:12:07'),
(282, 1, 'd8166cc0a479e8c1400058e7fb54c94b8d69d9437278ad9865829a7f77781c79', '2026-02-09 11:33:08', 1, '2026-02-09 17:33:08'),
(283, 1, 'f6b6ce69debc418d91a11f6a960dfd3b679b4d503cbbdb7f59723888c22efca5', '2026-02-15 11:25:39', 0, '2026-02-15 17:25:39'),
(284, 1, 'd4590a58cf44b81da4f3650375305fc9b4e5bc93d6579976c093dd5f6b16c30d', '2026-02-16 18:26:54', 0, '2026-02-17 00:26:54'),
(285, 17, '9bb83091e8b757d68f99f1c0d3c5f97214acb89a6e1a82179b48b3270fab18fe', '2026-02-16 18:29:40', 0, '2026-02-17 00:29:40'),
(286, 16, 'e6b2c57ef9a55b3657fcc4ab7d377ee5d23450ba9fabdf4eac59ce12f9081e6c', '2026-02-16 18:29:48', 0, '2026-02-17 00:29:48'),
(287, 16, 'c904f3acbd9b8290fcc142d884e037062dea61c2c0cc7823acc3d70dd0e42105', '2026-02-16 20:41:37', 1, '2026-02-17 02:41:37'),
(288, 1, '05d441d11e660d643ba2b8ec8440839b413f22dff9f2716b0c8f225339412f03', '2026-02-16 20:48:39', 1, '2026-02-17 02:48:39'),
(289, 1, '857f98ff77f4b6c4ce8a2c2020b14e26ef4cc5a6abe52bbd167e6e72df54a71b', '2026-02-17 12:19:08', 0, '2026-02-17 18:19:08'),
(290, 16, '60056695d73708136a421249e74158f33b1ba74bbe519a696e80b63f607e6292', '2026-02-17 21:25:17', 1, '2026-02-18 03:25:17'),
(291, 1, '856f92bc348faaf2cdbe46a8d367720a98bf0ada488a82fe0a891f6c68515df9', '2026-02-24 07:50:04', 0, '2026-02-24 13:50:04'),
(292, 17, 'ef4951b040d28871f2c5375f51027972cc9f48dbb24bc4ca8e3d85feffcb3a21', '2026-02-24 07:50:12', 0, '2026-02-24 13:50:12'),
(293, 16, '9a275e51cb5eca7d7c2f6ded10168218be0fafc4899ea9244f0c30fb0267aaa3', '2026-02-24 07:50:29', 0, '2026-02-24 13:50:29'),
(294, 16, '6a032d0fc4a70b442114d21409a28a233fcdbaea0223a0280f145699f0321bc1', '2026-02-24 09:11:48', 0, '2026-02-24 15:11:48'),
(295, 1, '964812fa5009b37d5efeaf9275b62b5165a915c63ab0f1bf9131f80eb51767b8', '2026-02-24 10:46:17', 0, '2026-02-24 16:46:17'),
(296, 17, '53bf909e75af5830af8e860c9d2a4a3f1aa6ede34b86856f0ebc4eb1418ca8db', '2026-02-24 10:46:25', 0, '2026-02-24 16:46:25'),
(297, 23, '62a432b5d273e242e919151d78fa560694480e2b5a8d5dbe4ea13d946a6988d0', '2026-02-24 10:49:26', 0, '2026-02-24 16:49:26'),
(298, 16, '9e176f3b393ac5ec73678882eb0daf8fd8987fac410d7c20549506f8d306d31f', '2026-02-24 11:46:18', 1, '2026-02-24 17:46:18'),
(299, 1, '2ec1a5bce9fd6fe587dd74c326598134097a8d3c4313865891da104cc56c4ac1', '2026-02-24 12:56:23', 1, '2026-02-24 18:56:23'),
(300, 1, '5cf472ea8e08054de5574e500abb0bffc38efeea6e06a7209d9d05ed8691cdb5', '2026-02-24 12:57:09', 0, '2026-02-24 18:57:09'),
(301, 23, 'b1c9f3990960270facc0452a0fd69a740daeac0bf12d653d6a826c0df1abcecc', '2026-02-24 13:12:49', 1, '2026-02-24 19:12:49'),
(302, 1, '09812026a932e06d7dadf25f9faeda9552bf8116112d92e24d451cbc71162506', '2026-02-24 14:58:13', 0, '2026-02-24 20:58:13'),
(303, 1, '5660ea36f09a98706fea18a5abf9c8a728aa4f7e4bf4a073bce1da1ffe34e6de', '2026-02-24 20:08:03', 1, '2026-02-25 02:08:03'),
(304, 1, '6bf918dfd9d4e70bde1e36d31c231fcf3786a1813c49c2ea226bd953f8a89c7a', '2026-02-26 10:43:21', 0, '2026-02-26 16:43:21'),
(305, 16, '228e5e546e3e46accd43368a480ae34ba863b362d0ab1134c683801272fcdeb0', '2026-02-26 11:02:46', 0, '2026-02-26 17:02:46'),
(306, 23, '646513d548338d3d300d00775695bc77744edfcdb1e6a75a3093b75a632d6549', '2026-02-26 12:52:22', 0, '2026-02-26 18:52:22'),
(307, 1, '14812fae8ab885816eef50192df6a419034bfe58f7e04a5be74b5b3f57fb8443', '2026-02-26 12:52:32', 1, '2026-02-26 18:52:32'),
(308, 17, '1c8427137ca48db16ceee5c7f2dc126b5b78842521abe5a93a2493fb37309206', '2026-02-26 12:55:38', 0, '2026-02-26 18:55:38'),
(309, 16, '5437fdf05c9d88462c87d2ae7380efbd910d3dabc141a518ea30ba4b4156ba8f', '2026-02-26 13:33:47', 1, '2026-02-26 19:33:47'),
(310, 16, 'df03b4809b3953dfeb06eb08f31b7d89d1d92e8be64ebda62b76c11fd72f7161', '2026-03-13 08:19:04', 0, '2026-03-13 14:19:04'),
(311, 1, 'f01eb16984a470117167be42472af386608c496188276515ef1659ec5df4b0b6', '2026-03-13 08:19:09', 0, '2026-03-13 14:19:09'),
(312, 1, '522602e867613c9b0b8b4359516121e66c5d6a7660eb0169536456a2dc64453e', '2026-04-17 07:42:14', 0, '2026-04-17 13:42:14'),
(313, 16, '4f5e34c8cd1c0234c2b5cf4de67ee5622e998fe4139eb9e1ba8f5f3cb0ba3c7c', '2026-04-17 07:43:55', 0, '2026-04-17 13:43:55'),
(314, 17, 'ee84af94bcc108d057b021543a9ce389e809928dd197b400ed634abaad501d2c', '2026-04-17 07:44:06', 0, '2026-04-17 13:44:06'),
(315, 20, '3029ba1e1b5668f3b0ca70546d72db3e0bc84357e34c8057d05143bec95548e4', '2026-04-17 07:44:37', 1, '2026-04-17 13:44:37'),
(316, 1, 'fd1cccf3ba54330d23eb1b5789748b990438855dcb67c1b0da14952cdbd889b8', '2026-04-18 12:13:45', 0, '2026-04-18 18:13:45'),
(317, 17, 'da66bbf4b19c80a3735629cec4c9139b4e9e8555417c543bdcbe708ff5fb371f', '2026-04-18 12:13:50', 0, '2026-04-18 18:13:50'),
(318, 16, '1eb64553a8163ebfbde586471a3a72f45678ea96edc82e04e89ba79e4a6558c1', '2026-04-18 12:13:55', 0, '2026-04-18 18:13:55'),
(319, 16, '4528016d46f4cf8aff50582702cda40a7a8ae44e29ea78afa1ea13d4366a3a8f', '2026-04-18 14:22:38', 0, '2026-04-18 20:22:38'),
(320, 17, '1819ee0f7907bc1bda339842a94a7274da490d787f38d868d6dd911ef9a20d0d', '2026-04-18 14:22:49', 0, '2026-04-18 20:22:49'),
(321, 1, '33df0d8570768ea22ed2bf714169dd1b8ab03368dcd0012367e234661c4a9e8e', '2026-04-18 14:22:58', 0, '2026-04-18 20:22:58'),
(322, 16, '2afd5a46d3ccb994bb72e093eb580c3eaa0fb1c3d6979d7a64b7043f4098e5df', '2026-04-18 16:51:00', 1, '2026-04-18 22:51:00'),
(323, 17, 'd90a589b7a84faeeeca975124e8a87793691ae7ea8b96e8250f62f1ccb55ff8f', '2026-04-18 16:51:29', 1, '2026-04-18 22:51:29'),
(324, 1, '7675a69b342a764d07a8356a152d7a30733c46be9e186158864857b11e07fc7a', '2026-04-18 16:51:40', 1, '2026-04-18 22:51:40'),
(325, 1, '237305a1b8221f561c1c0a5cb2af56f2bb4bec32e61d78d0a9f21abce9c6121a', '2026-04-19 16:20:06', 1, '2026-04-19 22:20:06'),
(326, 17, '26c14cae6b0ab00e08ec387677bb5513a5793d01de23a3afc5a85f3ad370c723', '2026-04-19 16:22:50', 0, '2026-04-19 22:22:50'),
(327, 16, 'c64422073f2c7679cb40e9a4cdd85cd89c9ff4a34352a81fce0336bb9cf5ef4a', '2026-04-19 16:23:11', 0, '2026-04-19 22:23:11'),
(328, 16, '86c480d1a3fd919322b7b075632081726b8e64bfe7eb1105c826e543f1222124', '2026-04-19 18:38:34', 1, '2026-04-20 00:38:34'),
(329, 16, '6d55c6aa2bb025d82acdc918b0db456b91abc88a298b285c604bb28fee1a6719', '2026-04-20 05:39:12', 0, '2026-04-20 11:39:12'),
(330, 16, '181a125c86d35d74c87ee5c4d9e32dc3ef0da089a3d3ac057895255e8b8fdcde', '2026-04-20 05:40:17', 0, '2026-04-20 11:40:17'),
(331, 16, 'a6816fdd84f23162e4035a52041c1b7743b2bdf08e67412a47fac10904706240', '2026-04-20 05:42:04', 0, '2026-04-20 11:42:04'),
(332, 17, '95f655684cccd22ff768de873dab83b9f1bdfee42dc64939ce64a31465c2d4b8', '2026-04-20 06:07:00', 0, '2026-04-20 12:07:00'),
(333, 16, '666f8625fb725e1270dca9067bc45d13cb17c8b4ce053d07b211655bdf7c2db2', '2026-04-20 07:43:14', 0, '2026-04-20 13:43:14'),
(334, 16, 'b96b6bdeee671383116d5f2f16a6b7aa279ea3a148f58f141b1990c66328004a', '2026-04-20 08:13:05', 0, '2026-04-20 14:13:05'),
(335, 17, '74c151b65ce802d66bf3eade7a3b37a728cc852eac1da095635ce012cbbc5697', '2026-04-20 08:13:10', 0, '2026-04-20 14:13:10'),
(336, 1, '864e76643cb096a1e664c894cf47368d1138142a457bf6c4fc3f30177ac358f0', '2026-04-20 08:13:19', 0, '2026-04-20 14:13:19'),
(337, 1, '35d9131cab45fc55d478e7d5596ed3537a3ab405b98ba5d5b68a736d5699ea68', '2026-04-20 10:23:19', 0, '2026-04-20 16:23:19'),
(338, 17, '6ec289db5a15b96dd5c266e41505d9ef5eca8565f43b4188d3a734cacd280ad3', '2026-04-20 10:24:40', 0, '2026-04-20 16:24:40'),
(339, 1, 'c320e4138e12be0f4fdbadc75801adca73962da7f0972b7d9a45456602db4794', '2026-04-20 10:24:45', 0, '2026-04-20 16:24:45'),
(340, 16, 'fafe10ccaa6e49f263edbbef91b65d667c18da69978546111dcab157cb50d210', '2026-04-20 10:25:05', 0, '2026-04-20 16:25:05'),
(341, 1, 'd9462600e343f34cc65d550e252e1c25876a758e704e9d872c89294d26b4cbab', '2026-04-20 12:24:50', 0, '2026-04-20 18:24:50'),
(342, 1, '39218a5aa9dfa4a2b0651899f5986407245f058511c89dc988576de23b0fafb8', '2026-04-21 03:04:44', 0, '2026-04-21 09:04:44'),
(343, 17, '70e3d5c03b24a625b6bf4aab9b30a4103fd8fcbab2d8fe40e7e2637e9e722ec2', '2026-04-21 03:04:53', 0, '2026-04-21 09:04:53'),
(344, 16, 'fbb74786fb2de4184c6c5588f8881b900535b89fb01dd35600cac87a5cf752df', '2026-04-21 03:05:04', 0, '2026-04-21 09:05:04'),
(345, 16, '8a5a25309b49baffeb801e0484765c7ed13007672fe2e17e0b526942e8b858b6', '2026-04-21 06:41:16', 0, '2026-04-21 12:41:16'),
(346, 1, 'd457a6f489d0d647ef9c8f9849619ce4dcd5b5bd4029791e66a0b748e54402ed', '2026-04-21 07:09:17', 0, '2026-04-21 13:09:17'),
(347, 16, 'fc42e0fad8db0594284b798f8accc80fa7069a038fb2e60a0f8f7734605e776e', '2026-04-21 08:42:47', 0, '2026-04-21 14:42:47'),
(348, 17, '23ed0734e53f00507a93b59b234ddc6e32439f75d4ef20f57888bee3ff10777e', '2026-04-21 09:05:50', 0, '2026-04-21 15:05:50'),
(349, 1, 'ce089277c12e0bfbd77be60ef241c4994a836be8ffd23fa52c5d6994e15e3606', '2026-04-21 09:12:23', 0, '2026-04-21 15:12:23'),
(350, 16, '02651b05ebbbbae230623fda9111758d517fee4dec145e47f697db2928681d18', '2026-04-21 10:56:05', 0, '2026-04-21 16:56:05'),
(351, 1, '5e32b8f5595eff3cbaa7baf1322de0a339356add8f3dd145c68e83807da181bc', '2026-04-21 11:12:34', 0, '2026-04-21 17:12:34'),
(352, 17, '60da8379418152c34e758b37f0d337ca73a300f84ad6d1180071650ef97708b6', '2026-04-21 11:44:50', 0, '2026-04-21 17:44:50'),
(353, 1, 'd01d399f2fa4fbe1fe7a04e0bb044fe9729104626c9159ebca8bbbadee3f87e6', '2026-04-21 13:18:30', 0, '2026-04-21 19:18:30'),
(354, 16, '3acd856f248a4a72b2e2bd3ba8f836978771b6ddeb6e4352df3ed87df72f12c1', '2026-04-21 15:17:12', 0, '2026-04-21 21:17:12'),
(355, 16, '4dd1d1020d3a92beabdfb07ddfaef65579521ece766be8c45320dcbabf437edb', '2026-04-21 15:17:21', 0, '2026-04-21 21:17:21'),
(356, 17, '4e34c49dd2917f364cb4b75423246cdb63f37bb8a2e3d12c8462de2e129664cd', '2026-04-21 15:17:42', 0, '2026-04-21 21:17:42'),
(357, 23, 'e828d2ccc6275d5b3dd38355ddcef29b4c58e6be38e280a2be47e1265a5c47d3', '2026-04-21 15:19:01', 0, '2026-04-21 21:19:01'),
(358, 1, 'fa82efd7e9932462d5d2ed36ec905aee6f353942b81042fd7909ecd59bf100b5', '2026-04-21 15:19:13', 0, '2026-04-21 21:19:13'),
(359, 1, '164c188fb644b0748714a1c68232ad87ff9b6b565bd6a4d8555a9a2c7866bf0a', '2026-04-21 17:23:05', 0, '2026-04-21 23:23:05'),
(360, 1, '78b1270fbe689ef0f0dae6f03bc02c3645de4eb9bc25b53cfed958b3e238c234', '2026-04-21 19:30:11', 0, '2026-04-22 01:30:11'),
(361, 16, 'b5881eb26342330407fadbaeae18616e708886b6cfe1b3272a462e23b0c990e4', '2026-04-21 20:24:05', 0, '2026-04-22 02:24:05'),
(362, 16, 'fb09a3c9ae4ae1748dbd6beba2f120271d09c5759f2f1d8b379ce2993dfeebf5', '2026-04-21 21:31:53', 0, '2026-04-22 03:31:53'),
(363, 1, '90bb9a2ec0ea9b780766b73d82a790232213df13f7043dd9cf53ffd6ccc356bf', '2026-04-21 21:32:06', 0, '2026-04-22 03:32:06'),
(364, 17, 'bf7471ec0eff421f65a0a6121dae826a307f67a6941fcec3aa6c397ae37b9a42', '2026-04-21 21:50:16', 1, '2026-04-22 03:50:16'),
(365, 1, '064e5d0c4f8ef0dceda34ea40fba57a0d0a826e6e7ee30d259a32f1c0e249819', '2026-04-21 23:36:12', 1, '2026-04-22 05:36:12'),
(366, 1, '364a4be45a3e14d7d3e4a762cbb43fd4bc159833b5b9fa02441a8f65edf490c0', '2026-04-22 07:20:42', 0, '2026-04-22 13:20:42'),
(367, 17, '4c7690ed6a11421dfbc2d0e956320682864d4fed6ccce406e25ae8fc88c06cbd', '2026-04-22 07:21:11', 0, '2026-04-22 13:21:11'),
(368, 16, '1529e08139cf70b18ecfcb5919d8fd3b7e9dd8d3c0566fd6677fc495f4403d8c', '2026-04-22 07:21:28', 0, '2026-04-22 13:21:28'),
(369, 16, 'f5d1a48faf0f836ee8ec3e95f2bb560a910917a66bbea1e295412cd895c9163e', '2026-04-22 09:24:51', 0, '2026-04-22 15:24:51'),
(370, 1, '10760efd7ef5940c12ae0e0437f0e66debee4c8a4387873ef308cc0c8ecbd8ca', '2026-04-22 09:48:39', 1, '2026-04-22 15:48:39'),
(371, 16, 'e1fea9f2ebd2ba7b9092a39733dd2d67a521879e9e190936f5ac1a6d81887e75', '2026-04-22 10:37:53', 0, '2026-04-22 16:37:53'),
(372, 1, '20d7d96f70f6fb3af246525996caccc5a758f112be49e0b832d1125d93c3743b', '2026-04-22 10:43:41', 0, '2026-04-22 16:43:41'),
(373, 23, '456f13406eac1d92ad36f6e8a2c1a57212da2c30d078f1674b58296167dd1d07', '2026-04-22 10:54:05', 0, '2026-04-22 16:54:05'),
(374, 1, '64cf6763c0d49c492ef57070fca90d33517750db1e6739f8fc5a251042bdad82', '2026-04-22 12:44:12', 0, '2026-04-22 18:44:12'),
(375, 17, '1a556b4ba02d1d9aa96d9732f2a276881912cb12f467bc14ba035061d68386c0', '2026-04-22 12:47:01', 0, '2026-04-22 18:47:01'),
(376, 16, '1dcb0160130e7cc4f299694df59d508ef74f9c1d6735db4e1159e01245549117', '2026-04-22 12:58:44', 0, '2026-04-22 18:58:44'),
(377, 16, '71bc5cb541a1d5e2d7f7773fdd3d8c3dbf17a7fdc400e8d80f680001515ed753', '2026-04-22 13:36:23', 0, '2026-04-22 19:36:23'),
(378, 23, '3e11f6279ee8385b1d6e91064d3bd161d3b22c11bcc41da2fda0c7fb3f70eaee', '2026-04-22 13:40:22', 0, '2026-04-22 19:40:22'),
(379, 1, '49ad0e390b7193c6281c967e92cc6068ada4ce05755902323a2e674f4beb3c05', '2026-04-22 16:23:00', 0, '2026-04-22 22:23:00'),
(380, 16, '1402997bffbe033a9d229104ff4e9da74f19b69bc6f411845b5efa8efae450fb', '2026-04-22 16:27:30', 0, '2026-04-22 22:27:30'),
(381, 23, '16ebfe94db511aba03a9332af81696f153af7194475cf5b831cd2d9befbec28c', '2026-04-22 16:27:36', 0, '2026-04-22 22:27:36'),
(382, 1, 'efa5468d291096c7d05551a0db09125c944e4007e240d8bd7d7ae570875b9ec4', '2026-04-22 19:11:25', 0, '2026-04-23 01:11:25'),
(383, 16, 'c0f875ee37f5d38e7f307a275647db6d438383f37dfc70e467da516e28eaecb2', '2026-04-22 19:11:34', 0, '2026-04-23 01:11:34'),
(384, 23, 'd0c31277778ce50c1c563f1cf90b5bc3c74e54723c31bee29fbcefb4bfa3e6bc', '2026-04-22 19:11:43', 0, '2026-04-23 01:11:43'),
(385, 1, '81cb18a977381647b3d704fe7b119eb7e4eb7d3e2ac7179e3efc5fb4f4f87112', '2026-04-22 21:21:36', 1, '2026-04-23 03:21:36'),
(386, 16, 'e257d4ff707a4630af42f56b4d1baf979f918c8b0a8bf1ecd7b53a354db0be70', '2026-04-22 21:21:42', 1, '2026-04-23 03:21:42'),
(387, 23, '57117846f667a3e5947ab89d681541a2966e3981ad6b5964e73a21daae50036a', '2026-04-22 21:21:52', 1, '2026-04-23 03:21:52'),
(388, 23, '808841c48154873d895ea27167226e781b5622defe059aa3a2c2780dd7b674bd', '2026-04-23 08:39:37', 0, '2026-04-23 14:39:37'),
(389, 16, '85b719cf404cf255e356cf30bdfcfe75232459437cba705756874de4888a50ab', '2026-04-23 08:39:41', 0, '2026-04-23 14:39:41'),
(390, 1, '1bd26abf84d02a3fa956f5ff0f40a9affaf78408c376b8abcb662bf7acf20530', '2026-04-23 08:39:46', 0, '2026-04-23 14:39:46'),
(391, 20, 'a920c9b1d72039b2acb4157b8b529201186c5f7ff229b4286827a419a9a90a37', '2026-04-23 09:29:50', 0, '2026-04-23 15:29:50'),
(392, 1, 'f35fa9203cd1930a3af87cd2fe3cf2a836f4bba6ebf9d122575b644bf97b5ac8', '2026-04-23 10:39:55', 0, '2026-04-23 16:39:55'),
(393, 16, '892c8649806c17f8e1d67fba18a39eac585ac237fdb1cb51f85e0c8b5e46888e', '2026-04-23 10:40:04', 0, '2026-04-23 16:40:04'),
(394, 23, 'c7522abbcbcb671f22972f48fc6dec85f3741b6dbdbe6175cc67d42ba7ccdbc6', '2026-04-23 10:40:11', 0, '2026-04-23 16:40:11'),
(395, 1, 'e29b2bf41ccd86e4356be61c94ccb53bf430d4ec60f6a5a1cf800c67de2929da', '2026-04-23 21:05:59', 1, '2026-04-24 03:05:59'),
(396, 16, '988fc1baf02db8eaf4ef083159e58d77431a8066511b2f2f15f2750424f29034', '2026-04-23 21:06:12', 1, '2026-04-24 03:06:12'),
(397, 23, '0d6e7727b0df58bece632149b63f5e4b4c2f2d7f2e3f9f6ccf92647e8443776a', '2026-04-23 21:06:16', 1, '2026-04-24 03:06:16'),
(398, 20, '42e2f96a59a6e8a289c1b4b819a48caf74173b834a67425da1c1c2c342a7ee73', '2026-04-23 21:06:21', 1, '2026-04-24 03:06:21'),
(399, 16, '36e23497dd6570f66f2721453b2fed07b5da60d48b8879dc69d268f21f7c3428', '2026-04-24 08:33:18', 1, '2026-04-24 14:33:18'),
(400, 23, 'f0a890e40daab061d54a06ffde3cf3939a5d98213810a0fa722f0cfb4d231afa', '2026-04-24 08:33:24', 1, '2026-04-24 14:33:24'),
(401, 1, '0ccca230ad42df7b492ec600d975a87cad7e066a3626da2f5b04dfb2800d7d76', '2026-04-24 08:33:31', 1, '2026-04-24 14:33:31'),
(402, 16, 'e9b80109b43e6cf5d46d901730c24d03572a66a5a6fe6da5c7fc8a0e2c07b53c', '2026-04-24 19:57:55', 1, '2026-04-25 01:57:55'),
(403, 23, 'b11ff10b68abd116193c265db8ab55c657a95930b3b174c8d737e4a08b4f90c1', '2026-04-24 19:58:03', 1, '2026-04-25 01:58:03'),
(404, 20, '0ae1f28a4a3eeafad0c71c8b20e4a02df1a93033b1964298e14ac014b929c70f', '2026-04-24 21:00:54', 1, '2026-04-25 03:00:54'),
(405, 1, 'ce174362a59f4c21c4251d77299fd649c123341b73a22484598b91268472052d', '2026-04-25 05:40:51', 0, '2026-04-25 11:40:51'),
(406, 1, '5440dc3291a990418f6c7374711e200d8c0cf21e001a2e9058f58f09940407fb', '2026-04-25 05:42:20', 1, '2026-04-25 11:42:20'),
(407, 1, '0f5f7e9aafff6f12752e123236a535b8340f913544282dfc682c4fe6c206e782', '2026-04-27 08:13:26', 1, '2026-04-27 14:13:26'),
(408, 23, '43127fd74209c0030e3c2ba5573869763330cd8533ac37c3ec4728dfc0988154', '2026-04-27 18:40:01', 1, '2026-04-28 00:40:01'),
(409, 16, 'bdc10b88bad6e8fef902c8e3a45a3b84a9462cd36424cf871eb9492b35c3a89e', '2026-04-27 18:40:07', 1, '2026-04-28 00:40:07'),
(410, 20, '8561dc844c219ee33e32e9b07fde931dc1d6ef88dccd5b046c4e4d0759a14edb', '2026-04-27 22:27:48', 1, '2026-04-28 04:27:48'),
(411, 20, '27280c374f85d45c961bd2e35a898402ffe008653085ba64b81df2b42686d43f', '2026-04-28 08:27:22', 1, '2026-04-28 14:27:22'),
(412, 23, '79003e2f766a296b8bcf89f56ba2b5cd90b807adf6f931eed8cb20a3c6ed5dc1', '2026-04-28 08:27:25', 1, '2026-04-28 14:27:25'),
(413, 16, '9f8ba911bc6e05cdcdfbcb626c8c9f969a2890d794c54ef9ee974803e5547c23', '2026-04-28 08:27:29', 1, '2026-04-28 14:27:29'),
(414, 1, '03b7efcb944710ded517bba8d251360c48a769efdefef7c7b44b4366572e9e5d', '2026-04-28 08:27:33', 1, '2026-04-28 14:27:33'),
(415, 17, '0df0a904e0688e31d80b77680d542ebc5efaf33d502d45d508c645501fc8b18d', '2026-04-28 15:20:33', 1, '2026-04-28 21:20:33'),
(416, 1, '04a4acc536d705e31bf1fc29ca39a7ba2d53b2a1e534b99baa5f3dfcd77923da', '2026-04-28 20:01:21', 1, '2026-04-29 02:01:21'),
(417, 23, 'f305492fd299bddacae871a3cc605d7bc94ca7126e42cc5bb684923304a4e9b4', '2026-04-28 20:25:07', 1, '2026-04-29 02:25:07'),
(418, 17, '6f949736cb07bc7155f57550b65dc95d6b2bda82f33c90668fdb408cc2cad0b2', '2026-04-28 21:42:36', 1, '2026-04-29 03:42:36'),
(419, 16, 'd5e2bae11d1e0349623b229353191a2aa35d7736c10a07d59264b249297285aa', '2026-04-29 06:27:45', 1, '2026-04-29 12:27:45'),
(420, 1, '1bb3241e9b089181c21df1dd0e868904b49b9364bc5296562d084ecbe95542b2', '2026-04-29 07:22:44', 1, '2026-04-29 13:22:44'),
(421, 1, 'de92a299ceeb4f18a4aa370a46b3d8434e46739b793606a1c6d32d14ae748359', '2026-05-02 09:40:37', 1, '2026-05-02 15:40:37'),
(422, 16, '39dcdbb7bb66df15f4bef879c7231c9a3c1e159408441e923f6eb71d7cae785e', '2026-05-02 09:43:44', 1, '2026-05-02 15:43:44'),
(423, 17, '1a00c008b0f5da66149c642d82b2d8f3a016de0aea888e03ca8e09846767217d', '2026-05-02 11:32:55', 1, '2026-05-02 17:32:55'),
(424, 16, '5ce9946e224d96aec644b12672e6cec7ea26209246025b63e6411ee6a8b0186b', '2026-05-02 13:22:05', 1, '2026-05-02 19:22:05'),
(425, 1, '57e7d2c409f936a7bcaa965a3e11ff60813b85a06552d97004b137af21d83fac', '2026-05-03 10:12:13', 1, '2026-05-03 16:12:14'),
(426, 16, '758c112b899f0230fea5db6155bcfe5a1662b9d2b8e1a84583989a4127aef079', '2026-05-03 10:12:27', 0, '2026-05-03 16:12:27'),
(427, 1, '6cb4a81e7da8faabdf8194e270c92ab4a229291e55e9fc3da4f31831430dc742', '2026-05-04 09:55:34', 1, '2026-05-04 15:55:34'),
(428, 16, '1a98659bbc2795ff579e2a66642acc93f6682b2a826cde9aa9b146b10857c36c', '2026-05-04 10:36:03', 1, '2026-05-04 16:36:03'),
(429, 23, '5f91fdf985f281157e49a99a8b3d27cf5c9c5e5f1cb4f232b857dcf7338c8cbe', '2026-05-04 14:01:14', 1, '2026-05-04 20:01:14'),
(430, 1, 'c1eabcd54c84cb5c7e95c814a7a136c9cb955e0d02168829dda2f95acad418a5', '2026-05-07 21:19:01', 1, '2026-05-08 03:19:01'),
(431, 16, '04a056d3df717b06f45dcb29b91a83318cc044e8cf9e3321c3e072f4b55cbbe0', '2026-05-07 21:19:08', 1, '2026-05-08 03:19:08'),
(432, 23, '8a156fa78e4500fd7cdc99fbee00135784f5a0f78b7e99f405d8cdaee25be446', '2026-05-07 21:19:21', 1, '2026-05-08 03:19:21'),
(433, 20, 'e835d577c0ffcf111d7f726a7aaff62af3a6c49a1dd936d9639cf22832faafb3', '2026-05-07 21:19:29', 1, '2026-05-08 03:19:29'),
(434, 17, 'cafb891d745b61f7dc82518d4567145b127f5627010b3e0d66ebcfd6146411a1', '2026-05-07 21:19:37', 1, '2026-05-08 03:19:37'),
(435, 1, 'aa5d31d200ea2691a8d991d5bb8d54d03e08a8789de5b55705032d08620297f5', '2026-05-08 08:55:31', 0, '2026-05-08 14:55:31'),
(436, 17, '054d437fcd34d72e94c6d5952ad600417c61206cce5831c424fb622eea4a3410', '2026-05-08 09:01:21', 0, '2026-05-08 15:01:21'),
(437, 16, '9357d207e2c2db67eb89074f76676721e1f6660718276fceca11c734f72e1f49', '2026-05-08 09:02:06', 0, '2026-05-08 15:02:06'),
(438, 23, '453326ecb6f291b95c457a9d7dadfd1817b8500f8bfcbc60e786f8aec9de6a20', '2026-05-08 09:08:44', 0, '2026-05-08 15:08:44'),
(439, 1, '062b2851c227e3b40ac1b8dc171f8e29bccc02c288c58a1f2b427e4fde49f5b0', '2026-05-08 09:11:50', 0, '2026-05-08 15:11:50'),
(440, 16, '6f9bb50b28ba4f004d338ace96caa6fa3667a6506ecbeb7f773cbf251127ec95', '2026-05-08 09:21:11', 0, '2026-05-08 15:21:11'),
(441, 1, 'a7f226550a71a7361a32869f9f15e916db34e8e0e4db5de5eeaace70649ed1ca', '2026-05-08 09:22:59', 0, '2026-05-08 15:22:59'),
(442, 16, 'ac52bc0933ae3975716982384c8358c1000f37f36badce019856a300985f058f', '2026-05-08 09:28:26', 0, '2026-05-08 15:28:26'),
(443, 1, 'f29495c17691693f1e0a089aca8b1728613cd928c26934dcef086c177d05f625', '2026-05-08 09:29:33', 0, '2026-05-08 15:29:33'),
(444, 1, '976f282cfbb5259d01290f8a475f88421830cdb7848769a1f0db4528235f2ec3', '2026-05-08 09:31:04', 0, '2026-05-08 15:31:04'),
(445, 1, '1d0ac73cc00f4126b2cad426457335352dbd1ff4654c8504c8ba419f9c73b7c0', '2026-05-08 09:35:01', 1, '2026-05-08 15:35:01'),
(446, 1, 'db5af628f3d39bc95a4290b3dbfd94a9ae43ac3786595a32c238fa3b05bbcf80', '2026-05-08 14:07:48', 0, '2026-05-08 20:07:48'),
(447, 1, 'dde4dadc8504c64c3e37ec436b9e5d8c778fc867d018180e19e63636d5559111', '2026-05-09 04:39:09', 0, '2026-05-09 10:39:09'),
(448, 16, '41be732ba4ccc405eb0b7015c1b919c009b4252885e60228166bcd0985b03d26', '2026-05-09 05:21:51', 0, '2026-05-09 11:21:51'),
(449, 20, '0a015123ef70f04b383e5489211190e13297e5dec3232e4e73061135273f061d', '2026-05-09 05:23:15', 0, '2026-05-09 11:23:15'),
(450, 1, 'fc7c7d3810bf3729d077b8f42fe9015db30036000263fcc8218669bec6ed83be', '2026-05-09 05:23:54', 0, '2026-05-09 11:23:54'),
(451, 16, '435d7c5e2dadd2d9bffaa9c334ed11add716488bead4700da73ce4e45dbebc3e', '2026-05-09 05:26:00', 0, '2026-05-09 11:26:00'),
(452, 20, 'ca8aed62819049dd09033447e251c8c3fdb3a7d04a97c8ad69d0faeb8688c7c2', '2026-05-09 05:26:12', 0, '2026-05-09 11:26:12'),
(453, 1, '4dcacfd8a9582e10dd2eeb0603bb35ac161ddf737c673dee91fff2ad3778a555', '2026-05-09 05:26:36', 0, '2026-05-09 11:26:36'),
(454, 23, '90486a2871a9263e87591e5872077bd99bc01093d0946fb32a987edfb4a1e4a4', '2026-05-09 05:27:03', 0, '2026-05-09 11:27:03'),
(455, 1, '574d523a28225d37351e3540447927421a1ebbb946659fc46c4547090cd355eb', '2026-05-09 05:27:18', 0, '2026-05-09 11:27:18'),
(456, 21, '86952971d79a71eb7eb782bfb35910291cb556c7b0fa3eac6d91df2f43311fff', '2026-05-09 05:29:00', 0, '2026-05-09 11:29:00'),
(457, 20, 'fa132af24bb34625befd46bcb8443252c921b087c423028111cdc50d1ffa3e5a', '2026-05-09 05:29:22', 0, '2026-05-09 11:29:22'),
(458, 23, '943de89718ed93faf614f755506add8aec9b54ec3537b613bf2764d90ef67317', '2026-05-09 05:29:42', 0, '2026-05-09 11:29:42'),
(459, 24, '3fb8367bb8aa9a2f2f9e39b498ce8f56e90994f7835b13c1dbfb76093374490b', '2026-05-09 05:30:03', 0, '2026-05-09 11:30:03'),
(460, 1, '6bcc024de74d37d2d61fc9b90606c3dfd8bc115ea93a348f2170368bc3caf5ed', '2026-05-09 05:31:21', 0, '2026-05-09 11:31:21'),
(461, 17, '75ee22bf869e3563186c8a627453e216faebd69d1080aa5f90f8bcb50e7cb5b9', '2026-05-09 05:32:30', 0, '2026-05-09 11:32:30'),
(462, 1, '52b8983ad7919307e87c05fe59105eed2db986dae37bd208bce269c08cf4fd9f', '2026-05-09 05:40:52', 0, '2026-05-09 11:40:52'),
(463, 1, '915ac38baf9a9c909d3784b2594d4e48efb1cfbcd418978a60d31eb2dfc66dfa', '2026-05-09 05:44:52', 0, '2026-05-09 11:44:52'),
(464, 1, '2093745eef78ee462d68424b5342640166f4c5d7e901f0febdc9b8c7f6772d2e', '2026-05-09 06:06:33', 0, '2026-05-09 12:06:33'),
(465, 20, 'f9b8966a9512ec2139fe28b1ce1b2225cba8c9b170214545c5a681cf8565a832', '2026-05-09 06:06:49', 0, '2026-05-09 12:06:49'),
(466, 16, 'fc81a30c28dcf0d6204418c0cd36a683e74206e46165310d848507dfb3a48e85', '2026-05-09 06:07:03', 0, '2026-05-09 12:07:03'),
(467, 16, '170a36bc68e3c99a3902d85f89d45f70a5c5985024374aa9de8c63b1cdd39664', '2026-05-09 06:08:10', 0, '2026-05-09 12:08:10'),
(468, 20, 'cd8f2cafe2931e55fee02776a4e867dd9470817acc534d06addc4edb69aeef3b', '2026-05-09 06:20:55', 0, '2026-05-09 12:20:55'),
(469, 21, '708c1b8b130351ad37d8fc56d1e96a7d72f689eb9aa1a50183a1e37d6900357d', '2026-05-09 06:38:54', 1, '2026-05-09 12:38:54'),
(470, 21, '95d62fcc754c3ea950e3252eac03f2406e6d1c5d08fcd5628bc0bb8137344e2f', '2026-05-09 06:38:56', 0, '2026-05-09 12:38:56'),
(471, 23, '3fe2c5271868d42a5229b38765376e8478a944b67128e87376b23ea49822bcaf', '2026-05-09 06:52:19', 0, '2026-05-09 12:52:19'),
(472, 24, 'b6c14012da05e21a7e3a204a63bdbb88fb305a5e88c6af455c6f11b2df0a4692', '2026-05-09 06:52:28', 0, '2026-05-09 12:52:28'),
(473, 16, 'bd25434fb0231d491875839b48ab3f2aa750142b7ac4372a468315e046db41cc', '2026-05-09 06:52:37', 0, '2026-05-09 12:52:37'),
(474, 24, '4901ac995e497e02173db115b259f7519c4c065fe115a694dc809229eb2a464f', '2026-05-09 06:52:57', 0, '2026-05-09 12:52:57'),
(475, 1, '41636161b3736e880f9dd57c31f6e27add8da41d569f4e58099446e7259902d2', '2026-05-09 06:55:32', 0, '2026-05-09 12:55:32'),
(476, 23, '0ffc5afe7699bbd682e7632890376346b40a58d21ebaf9a6f623eab6271aa700', '2026-05-09 06:57:26', 0, '2026-05-09 12:57:26'),
(477, 1, 'cf806667eea92a339dcc7b887d9faaa033a52cfa4407b348f0b7103d7b1939a7', '2026-05-09 07:31:46', 0, '2026-05-09 13:31:46'),
(478, 1, 'ed2ca4dc25bf80244ac9bfb3d29db3d77ce70ead556e49a9d8f684778f92cbe9', '2026-05-09 07:36:48', 0, '2026-05-09 13:36:48'),
(479, 1, '6da88be2ecdf37ae0b7e29be9c76678d6a45e1c13fa3d7d0d51fa3de79fd40ab', '2026-05-09 07:53:06', 0, '2026-05-09 13:53:06'),
(480, 23, '2d78f02cd8b172674f7d1405bf3d914d23f25a4b56668fb3e9714eabb6d0fc41', '2026-05-09 07:53:21', 0, '2026-05-09 13:53:21'),
(481, 1, '18ea1e75f04c9bf3fd100993615c16ec597e05a63fb010be1d5e74f9ff9810d5', '2026-05-09 08:21:24', 0, '2026-05-09 14:21:24'),
(482, 23, '5570d567b5f894646d3dbeb7d8a2fa04a2620e9a1b8939153e30da119d4ded16', '2026-05-09 08:22:40', 0, '2026-05-09 14:22:40'),
(483, 1, 'ebb3d0d8b31d0fd28e45303a2b369b6223cb95537c0a1adeb48fcf68b8561efe', '2026-05-09 08:25:20', 0, '2026-05-09 14:25:20'),
(484, 20, '4dafd7f085400fa7756a167ebda5a1df0fe8a0512eb7b66eaa08cc69815a56c2', '2026-05-09 08:47:51', 0, '2026-05-09 14:47:51'),
(485, 1, '53800d62f7e78fb94cd5d3e319c116489bf5f54c76e61076199daa99584b5733', '2026-05-09 08:48:37', 0, '2026-05-09 14:48:37'),
(486, 20, 'a8c886b030cf52c29b19249327e8aada2516bca9626db5a8dd1ec86be3de98d0', '2026-05-09 08:50:22', 1, '2026-05-09 14:50:22'),
(487, 20, 'c0d3ea00457a2a7002428aab875d40fff519d8116ac5cd46e969eefe8de7b09c', '2026-05-09 08:50:33', 0, '2026-05-09 14:50:33'),
(488, 1, '7bd331ebc739417d2cefdf37c56fd3edbd26775eebdd9a7684ce3df51236a435', '2026-05-09 09:03:49', 0, '2026-05-09 15:03:49'),
(489, 17, '62e8902d014add06ef8592583f599a0e2de2e54c1f7de40f5879551472b8b240', '2026-05-09 09:08:04', 0, '2026-05-09 15:08:04'),
(490, 24, '2ad8fd667252b63fa70b8bdc1b3e6627a207db492da54b40033f2023a3e9ef7d', '2026-05-09 09:11:37', 0, '2026-05-09 15:11:37'),
(491, 1, 'b5a2385cd4128c2ab9c3a921a85e49fa4ab9a6d6bb4770921ff22f9126d0cd35', '2026-05-09 09:13:12', 0, '2026-05-09 15:13:12'),
(492, 24, 'eb0787afcf6758e4e5b875310c3896a5ed369d6fb2ef7ced9666ba8e37938dfa', '2026-05-09 09:13:55', 0, '2026-05-09 15:13:55'),
(493, 1, 'fab194c6225d16087ccad6f4d83929e4b197a1682cf0948e8ca7bfdc0b51ba51', '2026-05-09 09:24:50', 0, '2026-05-09 15:24:50'),
(494, 16, '940c8d6eb9d08d07792bae750b51507301d465848fe84a4301a2e15ccc639583', '2026-05-09 09:30:07', 0, '2026-05-09 15:30:07'),
(495, 1, '2bc155d521a2ffbac42e9c338a0b84dd6e7400199f88fd9b1f6921f62ae2f6bd', '2026-05-17 13:56:43', 0, '2026-05-17 19:56:43'),
(496, 16, '70010e7d370b5df1f8642cfdc416e2fa892e78f7d8707deda85060ece774825d', '2026-05-17 14:02:17', 0, '2026-05-17 20:02:17'),
(497, 20, '4c1b3fc58fb5d0e7f375a33d24d1e07dab88e723001ce3e880ffad0967baf9b4', '2026-05-17 14:02:47', 0, '2026-05-17 20:02:47'),
(498, 21, '0387fc2079410852786649fde4699218e61e8c7fdce145b70850f9bcd8949f5f', '2026-05-17 14:03:00', 0, '2026-05-17 20:03:00'),
(499, 23, '182d0dd0f163fca7f9416d663424b464891a19a9ef8a2d76a7c0a4f1908675e3', '2026-05-17 14:03:42', 0, '2026-05-17 20:03:42'),
(500, 24, '9dd3d4a8330a9a8fe96683c0837b7b48dde076e75d2c82a2567335d69fd72d91', '2026-05-17 14:04:11', 0, '2026-05-17 20:04:11'),
(501, 1, '5d95bfc0b2ba25246ef556bf572b5779493fffe28f46699ef64e09fae4d316e5', '2026-05-17 14:05:01', 0, '2026-05-17 20:05:01'),
(502, 17, '2fd5e6e138d9f04eb9de1ad2c6c295afce5c30d17ee9cb08c2fafa9d7210e039', '2026-05-17 14:29:14', 0, '2026-05-17 20:29:14'),
(503, 20, '1aae07b866c9ca35d6426dd560ede492bcca054ffcdc8d3aa32cd01f02767968', '2026-05-17 14:29:37', 0, '2026-05-17 20:29:37'),
(504, 1, '16e973b5f82e7839a4425fc19a2b594eaefb90b6fcdb421d98331a8327d66c48', '2026-05-17 14:29:57', 1, '2026-05-17 20:29:57');

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
-- Indexes for table `app_items`
--
ALTER TABLE `app_items`
  ADD PRIMARY KEY (`app_item_id`);

--
-- Indexes for table `app_item_sources`
--
ALTER TABLE `app_item_sources`
  ADD PRIMARY KEY (`app_item_source_id`);

--
-- Indexes for table `app_versions`
--
ALTER TABLE `app_versions`
  ADD PRIMARY KEY (`app_version_id`),
  ADD UNIQUE KEY `uq_app_version_year` (`fiscal_year_id`,`version_no`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `audit_logs_ibfk_1` (`user_id`);

--
-- Indexes for table `bidding_category`
--
ALTER TABLE `bidding_category`
  ADD PRIMARY KEY (`bid_cat_ID`);

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
-- Indexes for table `fiscal_year_reopen_logs`
--
ALTER TABLE `fiscal_year_reopen_logs`
  ADD PRIMARY KEY (`reopen_id`),
  ADD KEY `fk_reopen_fy` (`fiscal_year_id`),
  ADD KEY `fk_reopen_office` (`office_id`),
  ADD KEY `fk_reopen_user` (`opened_by`);

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_office` (`office_id`);

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
  ADD UNIQUE KEY `uq_ppmp_office_year` (`office_id`,`fiscal_year_id`);

--
-- Indexes for table `ppmp_revision_requests`
--
ALTER TABLE `ppmp_revision_requests`
  ADD PRIMARY KEY (`revision_request_id`);

--
-- Indexes for table `ppmp_versions`
--
ALTER TABLE `ppmp_versions`
  ADD PRIMARY KEY (`ppmp_version_id`),
  ADD UNIQUE KEY `uq_ppmp_version` (`ppmp_id`,`version_no`);

--
-- Indexes for table `ppmp_version_items`
--
ALTER TABLE `ppmp_version_items`
  ADD PRIMARY KEY (`ppmp_version_item_id`);

--
-- Indexes for table `procurement_modes`
--
ALTER TABLE `procurement_modes`
  ADD PRIMARY KEY (`proc_mode_id`),
  ADD UNIQUE KEY `proc_mode_name` (`proc_mode_name`);

--
-- Indexes for table `procurement_strategy`
--
ALTER TABLE `procurement_strategy`
  ADD PRIMARY KEY (`proc_strat_ID`);

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
  MODIFY `annual_budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `app_items`
--
ALTER TABLE `app_items`
  MODIFY `app_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `app_item_sources`
--
ALTER TABLE `app_item_sources`
  MODIFY `app_item_source_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `app_versions`
--
ALTER TABLE `app_versions`
  MODIFY `app_version_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=936;

--
-- AUTO_INCREMENT for table `bidding_category`
--
ALTER TABLE `bidding_category`
  MODIFY `bid_cat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `budget_allocation`
--
ALTER TABLE `budget_allocation`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  MODIFY `fiscal_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fiscal_year_reopen_logs`
--
ALTER TABLE `fiscal_year_reopen_logs`
  MODIFY `reopen_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `item_names`
--
ALTER TABLE `item_names`
  MODIFY `item_name_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ppmp`
--
ALTER TABLE `ppmp`
  MODIFY `ppmp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ppmp_revision_requests`
--
ALTER TABLE `ppmp_revision_requests`
  MODIFY `revision_request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ppmp_versions`
--
ALTER TABLE `ppmp_versions`
  MODIFY `ppmp_version_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ppmp_version_items`
--
ALTER TABLE `ppmp_version_items`
  MODIFY `ppmp_version_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `procurement_modes`
--
ALTER TABLE `procurement_modes`
  MODIFY `proc_mode_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `procurement_strategy`
--
ALTER TABLE `procurement_strategy`
  MODIFY `proc_strat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `sub_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_access`
--
ALTER TABLE `user_access`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=505;

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
-- Constraints for table `fiscal_year_reopen_logs`
--
ALTER TABLE `fiscal_year_reopen_logs`
  ADD CONSTRAINT `fk_reopen_fy` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`fiscal_year_id`),
  ADD CONSTRAINT `fk_reopen_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`),
  ADD CONSTRAINT `fk_reopen_user` FOREIGN KEY (`opened_by`) REFERENCES `users` (`user_id`);

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
