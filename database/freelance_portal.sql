-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2026 at 05:35 PM
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
-- Database: `freelance_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `logged_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `action`, `entity_type`, `entity_id`, `detail`, `logged_at`) VALUES
(1, 1, 'Created client', 'client', 1, 'Added Ali bazzal', '2026-05-26 21:57:23'),
(2, 1, 'Updated client', 'client', 1, 'Updated Ali bazzal', '2026-05-26 22:04:35'),
(3, 1, 'Updated client', 'client', 1, 'Updated Ali bazzal', '2026-05-26 22:04:50'),
(4, 1, 'Updated client', 'client', 1, 'Updated Ali bazzal', '2026-05-26 22:05:01'),
(5, 1, 'Updated client', 'client', 1, 'Updated hsn bazzal', '2026-05-26 22:05:18'),
(6, 1, 'Updated client', 'client', 1, 'Updated hsn bazzal', '2026-05-26 22:05:39'),
(7, 1, 'Updated client', 'client', 1, 'Updated Ali bazzal', '2026-05-26 22:56:49'),
(8, 1, 'Created project', 'project', 1, 'Created \"mobile App\"', '2026-05-26 22:58:28'),
(9, 1, 'Created invoice', 'invoice', 1, 'Created INV-2026-001', '2026-05-26 23:02:30'),
(10, 1, 'Created task', 'task', 1, 'Added \"create db\"', '2026-05-26 23:03:20'),
(11, 1, 'Moved task', 'task', 1, '→ todo', '2026-05-26 23:03:47'),
(12, 1, 'Moved task', 'task', 1, '→ in_progress', '2026-05-26 23:03:49'),
(13, 1, 'Moved task', 'task', 1, '→ done', '2026-05-26 23:03:51'),
(14, 1, 'Moved task', 'task', 1, '→ todo', '2026-05-26 23:03:53'),
(15, 1, 'Moved task', 'task', 1, '→ in_progress', '2026-05-26 23:03:54'),
(16, 1, 'Moved task', 'task', 1, '→ todo', '2026-05-26 23:03:55'),
(17, 1, 'Created task', 'task', 2, 'Added \"Create flutter files\"', '2026-05-26 23:04:25'),
(18, 1, 'Created task', 'task', 3, 'Added \"login page\"', '2026-05-26 23:05:07'),
(19, 1, 'Created task', 'task', 4, 'Added \"create stickers for design\"', '2026-05-26 23:05:36'),
(20, 1, 'Moved task', 'task', 1, '→ in_progress', '2026-05-26 23:05:40'),
(21, 1, 'Moved task', 'task', 4, '→ done', '2026-05-26 23:05:46'),
(22, 1, 'Deleted invoice', 'invoice', 1, 'Deleted INV-2026-001', '2026-05-26 23:12:25'),
(23, 1, 'Created invoice', 'invoice', 2, 'Created INV-2026-001', '2026-05-26 23:13:20'),
(24, 1, 'Changed project status', 'project', 1, 'Status → in_progress', '2026-05-26 23:15:18'),
(25, 1, 'Updated invoice status', 'invoice', 2, 'Status → unpaid', '2026-05-26 23:20:19'),
(26, 1, 'Updated invoice status', 'invoice', 2, 'Status → unpaid', '2026-05-26 23:26:16'),
(27, 1, 'Created client', 'client', 2, 'Added Rami H', '2026-05-26 23:53:51'),
(28, 1, 'Updated client', 'client', 2, 'Updated Rami H', '2026-05-26 23:54:11'),
(29, 1, 'Created client', 'client', 3, 'Added Ali Bazzal', '2026-05-27 00:08:33'),
(30, 1, 'Created project', 'project', 2, 'Created \"Pharmacy Mobile Application\"', '2026-05-27 00:39:11'),
(31, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-05-27 00:39:14'),
(32, 1, 'Changed project status', 'project', 2, 'Status → draft', '2026-05-27 00:39:16'),
(33, 1, 'Created task', 'task', 5, 'Added \"create db\"', '2026-05-27 00:39:32'),
(34, 1, 'Created task', 'task', 6, 'Added \"add logo\"', '2026-05-27 00:39:45'),
(35, 1, 'Created task', 'task', 7, 'Added \"generate stickers\"', '2026-05-27 00:40:00'),
(36, 1, 'Moved task', 'task', 7, '→ in_progress', '2026-05-27 00:40:02'),
(37, 1, 'Moved task', 'task', 7, '→ done', '2026-05-27 00:40:03'),
(38, 1, 'Created task', 'task', 8, 'Added \"Create flutter files\"', '2026-05-27 00:40:10'),
(39, 1, 'Created task', 'task', 9, 'Added \"login page\"', '2026-05-27 00:40:19'),
(40, 1, 'Moved task', 'task', 6, '→ in_progress', '2026-05-27 00:40:23'),
(41, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-05-27 00:41:01'),
(42, 1, 'Changed project status', 'project', 2, 'Status → draft', '2026-05-27 00:41:05'),
(43, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-05-27 00:41:06'),
(44, 1, 'Changed project status', 'project', 2, 'Status → draft', '2026-05-27 00:41:08'),
(45, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-05-27 00:41:10'),
(46, 1, 'Changed project status', 'project', 2, 'Status → review', '2026-05-27 00:41:12'),
(47, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-05-27 00:41:13'),
(48, 1, 'Changed project status', 'project', 2, 'Status → draft', '2026-05-27 00:42:07'),
(49, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-05-27 00:42:08'),
(50, 1, 'Changed project status', 'project', 2, 'Status → draft', '2026-05-27 00:42:09'),
(51, 1, 'Changed project status', 'project', 2, 'Status → review', '2026-05-27 00:42:10'),
(52, 1, 'Changed project status', 'project', 2, 'Status → completed', '2026-05-27 00:42:11'),
(53, 1, 'Changed project status', 'project', 2, 'Status → cancelled', '2026-05-27 00:42:12'),
(54, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-05-27 00:42:13'),
(55, 1, 'Created invoice', 'invoice', 3, 'Created INV-2026-001', '2026-05-27 00:50:16'),
(56, 1, 'Updated invoice status', 'invoice', 3, 'Status → draft', '2026-05-27 00:50:23'),
(57, 1, 'Updated invoice status', 'invoice', 3, 'Status → unpaid', '2026-05-27 01:04:01'),
(58, 1, 'Created client', 'client', 4, 'Added zahraa', '2026-05-28 11:45:06'),
(59, 1, 'Created project', 'project', 3, 'Created \"Artificial Intelligence and Data\"', '2026-05-28 11:54:43'),
(60, 1, 'Created invoice', 'invoice', 4, 'Created INV-2026-002', '2026-05-28 12:00:18'),
(61, 1, 'Created task', 'task', 10, 'Added \"AI and NLP Layer\"', '2026-05-28 12:02:57'),
(62, 1, 'Created task', 'task', 11, 'Added \"security layer\"', '2026-05-28 12:03:35'),
(63, 1, 'Created task', 'task', 12, 'Added \"ux\"', '2026-05-28 12:03:44'),
(64, 1, 'Moved task', 'task', 11, '→ in_progress', '2026-05-28 13:02:35'),
(65, 1, 'Moved task', 'task', 10, '→ in_progress', '2026-05-28 13:02:53'),
(66, 1, 'Moved task', 'task', 10, '→ in_progress', '2026-05-28 13:02:54'),
(67, 1, 'Moved task', 'task', 11, '→ todo', '2026-05-28 13:02:57'),
(68, 1, 'Moved task', 'task', 11, '→ in_progress', '2026-05-28 13:03:00'),
(69, 1, 'Moved task', 'task', 12, '→ in_progress', '2026-05-28 13:03:02'),
(70, 1, 'Moved task', 'task', 10, '→ todo', '2026-05-28 13:03:03'),
(71, 1, 'Moved task', 'task', 11, '→ todo', '2026-05-28 13:03:05'),
(72, 1, 'Moved task', 'task', 12, '→ todo', '2026-05-28 13:03:06'),
(73, 1, 'Deleted task', 'task', 10, 'Deleted \"AI and NLP Layer\"', '2026-05-28 13:15:38'),
(74, 1, 'Deleted task', 'task', 12, 'Deleted \"ux\"', '2026-05-28 13:15:42'),
(75, 1, 'Deleted task', 'task', 11, 'Deleted \"security layer\"', '2026-05-28 13:15:45'),
(76, 1, 'Created task', 'task', 13, 'Added \"security\"', '2026-05-28 13:21:22'),
(77, 1, 'Created task', 'task', 14, 'Added \"ux\"', '2026-05-28 13:21:58'),
(78, 1, 'Created task', 'task', 15, 'Added \"chatbot\"', '2026-05-28 13:22:40'),
(79, 1, 'Moved task', 'task', 13, '→ todo', '2026-05-28 13:22:43'),
(80, 1, 'Moved task', 'task', 14, '→ in_progress', '2026-05-28 13:22:45'),
(81, 1, 'Changed project status', 'project', 3, 'Status → in_progress', '2026-05-28 13:24:08'),
(82, 1, 'Created project', 'project', 4, 'Created \"aa\"', '2026-05-28 13:24:24'),
(83, 1, 'Changed project status', 'project', 4, 'Status → in_progress', '2026-05-28 13:24:27'),
(84, 1, 'Changed project status', 'project', 3, 'Status → draft', '2026-05-28 13:41:25'),
(85, 1, 'Deleted project', 'project', 4, 'Deleted \"aa\"', '2026-05-28 13:41:30'),
(86, 1, 'Created project', 'project', 5, 'Created \"s\"', '2026-05-28 13:41:49'),
(87, 1, 'Changed project status', 'project', 5, 'Status → review', '2026-05-28 13:41:57'),
(88, 1, 'Changed project status', 'project', 5, 'Status → completed', '2026-05-28 13:42:01'),
(89, 1, 'Changed project status', 'project', 5, 'Status → review', '2026-05-28 13:42:02'),
(90, 1, 'Changed project status', 'project', 5, 'Status → completed', '2026-05-28 13:42:10'),
(91, 1, 'Changed project status', 'project', 5, 'Status → review', '2026-05-28 13:42:14'),
(92, 1, 'Changed project status', 'project', 3, 'Status → in_progress', '2026-05-28 14:22:17'),
(93, 1, 'Changed project status', 'project', 3, 'Status → draft', '2026-05-28 15:02:15'),
(94, 1, 'Moved task', 'task', 15, '→ todo', '2026-05-28 15:11:58'),
(95, 1, 'Moved task', 'task', 13, '→ todo', '2026-05-28 15:11:59'),
(96, 1, 'Updated project', 'project', 3, 'Updated \"Artificial Intelligence and Data\"', '2026-05-28 15:12:50'),
(97, 1, 'Updated project', 'project', 3, 'Updated \"Artificial Intelligence and Data\"', '2026-05-28 15:20:10'),
(98, 1, 'Updated project', 'project', 5, 'Updated \"hardware\"', '2026-05-28 15:20:53'),
(99, 1, 'Changed project status', 'project', 5, 'Status → review', '2026-05-28 15:21:00'),
(100, 1, 'Changed project status', 'project', 5, 'Status → completed', '2026-05-28 15:49:31'),
(101, 1, 'Changed project status', 'project', 5, 'Status → cancelled', '2026-05-28 15:49:34'),
(102, 1, 'Changed project status', 'project', 5, 'Status → review', '2026-05-28 15:49:37'),
(103, 1, 'Updated invoice status', 'invoice', 3, 'Status → paid', '2026-05-28 16:49:13'),
(104, 1, 'Updated invoice status', 'invoice', 4, 'Status → unpaid', '2026-05-28 16:51:12'),
(105, 1, 'Created client', 'client', 5, 'Added xx', '2026-06-03 21:40:21'),
(106, 1, 'Updated client', 'client', 5, 'Updated xx', '2026-06-03 21:42:13'),
(107, 1, 'Created project', 'project', 6, 'Created \"art app\"', '2026-06-03 21:44:42'),
(108, 1, 'Created task', 'task', 16, 'Added \"cc\"', '2026-06-03 21:45:23'),
(109, 1, 'Moved task', 'task', 16, '→ in_progress', '2026-06-03 21:45:29'),
(110, 1, 'Moved task', 'task', 16, '→ in_progress', '2026-06-03 21:45:34'),
(111, 1, 'Moved task', 'task', 16, '→ done', '2026-06-03 21:45:36'),
(112, 1, 'Moved task', 'task', 16, '→ in_progress', '2026-06-03 21:45:56'),
(113, 1, 'Moved task', 'task', 14, '→ done', '2026-06-03 21:45:58'),
(114, 1, 'Moved task', 'task', 14, '→ in_progress', '2026-06-03 21:46:04'),
(115, 1, 'Moved task', 'task', 14, '→ done', '2026-06-03 21:46:05'),
(116, 1, 'Changed project status', 'project', 6, 'Status → completed', '2026-06-03 21:46:38'),
(117, 1, 'Created invoice', 'invoice', 5, 'Created INV-2026-003', '2026-06-03 21:48:01'),
(118, 1, 'Updated invoice status', 'invoice', 5, 'Status → unpaid', '2026-06-03 21:48:46'),
(119, 1, 'Moved task', 'task', 15, '→ todo', '2026-06-03 21:55:18'),
(120, 1, 'Moved task', 'task', 15, '→ done', '2026-06-03 21:55:21'),
(121, 1, 'Moved task', 'task', 15, '→ todo', '2026-06-03 21:55:22'),
(122, 1, 'Changed project status', 'project', 2, 'Status → draft', '2026-06-06 13:37:14'),
(123, 1, 'Created project', 'project', 7, 'Created \"\'\'\"', '2026-06-06 13:38:16'),
(124, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-06-06 13:38:27'),
(125, 1, 'Changed project status', 'project', 3, 'Status → in_progress', '2026-06-06 13:38:30'),
(126, 1, 'Updated project', 'project', 7, 'Updated \"smart inventory management\"', '2026-06-08 18:43:17'),
(127, 1, 'Updated project', 'project', 5, 'Updated \"custom Saas Dashboard\"', '2026-06-08 18:46:41'),
(128, 1, 'Created task', 'task', 17, 'Added \"ui / ux\"', '2026-06-08 18:47:15'),
(129, 1, 'Moved task', 'task', 17, '→ done', '2026-06-08 18:47:19'),
(130, 1, 'Created task', 'task', 18, 'Added \"images\"', '2026-06-08 18:47:33'),
(131, 1, 'Moved task', 'task', 18, '→ done', '2026-06-08 18:47:40'),
(132, 1, 'Created task', 'task', 19, 'Added \"file upload\"', '2026-06-08 18:48:00'),
(133, 1, 'Moved task', 'task', 19, '→ done', '2026-06-08 18:48:08'),
(134, 1, 'Updated client', 'client', 5, 'Updated shahzanan B', '2026-06-08 18:49:40'),
(135, 1, 'Updated invoice status', 'invoice', 5, 'Status → paid', '2026-06-08 18:50:32'),
(136, 1, 'Created invoice', 'invoice', 6, 'Created INV-2026-004', '2026-06-08 18:54:39'),
(137, 1, 'Updated project', 'project', 7, 'Updated \"smart inventory management\"', '2026-06-08 18:55:22'),
(138, 1, 'Moved task', 'task', 8, '→ in_progress', '2026-06-08 19:21:52'),
(139, 1, 'Moved task', 'task', 8, '→ todo', '2026-06-08 19:21:57'),
(140, 1, 'Changed project status', 'project', 6, 'Status → review', '2026-06-09 22:16:35'),
(141, 1, 'Changed project status', 'project', 6, 'Status → completed', '2026-06-09 22:16:36'),
(142, 1, 'Changed project status', 'project', 2, 'Status → review', '2026-06-09 22:16:38'),
(143, 1, 'Changed project status', 'project', 2, 'Status → in_progress', '2026-06-09 22:16:39'),
(144, 1, 'Changed project status', 'project', 3, 'Status → completed', '2026-06-09 22:16:42'),
(145, 1, 'Changed project status', 'project', 3, 'Status → in_progress', '2026-06-09 22:16:44'),
(146, 1, 'Changed project status', 'project', 7, 'Status → review', '2026-06-09 22:16:47'),
(147, 1, 'Changed project status', 'project', 7, 'Status → draft', '2026-06-09 22:16:54'),
(148, 1, 'Changed project status', 'project', 6, 'Status → review', '2026-06-09 22:19:51'),
(149, 1, 'Changed project status', 'project', 6, 'Status → completed', '2026-06-09 22:20:05'),
(150, 1, 'Changed project status', 'project', 6, 'Status → review', '2026-06-09 22:20:21'),
(151, 1, 'Changed project status', 'project', 3, 'Status → review', '2026-06-11 16:57:04'),
(152, 1, 'Changed project status', 'project', 3, 'Status → in_progress', '2026-06-11 16:57:05'),
(153, 1, 'Changed project status', 'project', 3, 'Status → review', '2026-06-12 10:04:02'),
(154, 1, 'Changed project status', 'project', 3, 'Status → in_progress', '2026-06-12 10:04:03'),
(155, 1, 'Created client', 'client', 6, 'Added Sarah Khalil', '2026-06-12 10:22:18'),
(156, 1, 'Changed project status', 'project', 7, 'Status → review', '2026-06-12 10:23:00'),
(157, 1, 'Changed project status', 'project', 7, 'Status → draft', '2026-06-12 10:23:01'),
(158, 1, 'Moved task', 'task', 15, '→ in_progress', '2026-06-12 10:23:18'),
(159, 1, 'Moved task', 'task', 15, '→ in_progress', '2026-06-12 10:23:19'),
(160, 1, 'Created project', 'project', 8, 'Created \"xx\"', '2026-06-12 10:23:52'),
(161, 1, 'Updated invoice status', 'invoice', 4, 'Status → paid', '2026-06-12 10:28:25'),
(162, 1, 'Changed project status', 'project', 8, 'Status → review', '2026-06-12 19:19:37'),
(163, 1, 'Changed project status', 'project', 5, 'Status → in_progress', '2026-06-12 19:19:39'),
(164, 1, 'Changed project status', 'project', 7, 'Status → completed', '2026-06-12 19:19:41'),
(165, 1, 'Changed project status', 'project', 6, 'Status → completed', '2026-06-12 19:19:42'),
(166, 1, 'Changed project status', 'project', 3, 'Status → draft', '2026-06-12 19:19:44'),
(167, 1, 'Updated invoice status', 'invoice', 3, 'Status → paid', '2026-06-12 19:20:28'),
(168, 1, 'Updated invoice status', 'invoice', 3, 'Status → draft', '2026-06-12 19:20:34'),
(169, 1, 'Moved task', 'task', 5, '→ in_progress', '2026-06-12 19:25:43'),
(170, 1, 'Moved task', 'task', 6, '→ todo', '2026-06-12 19:25:44'),
(171, 1, 'Moved task', 'task', 5, '→ in_progress', '2026-06-12 19:25:51'),
(172, 1, 'Moved task', 'task', 6, '→ in_progress', '2026-06-12 19:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `tag_color` varchar(7) DEFAULT '#C9C2F0',
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `user_id`, `company`, `phone`, `tag_color`, `notes`, `is_active`) VALUES
(3, 4, 'Pharmacy', '701234567', '#BFE3F9', 'vip', 1),
(4, 5, 'tech. Company', '76761575', '#C9C2F0', 'Technology Center CEO', 1),
(5, 6, 'Artist co', '222222', '#B6E8D3', 'urgent', 1),
(6, 7, 'Sara Design', '2222', '#FAE4A2', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `status` enum('draft','unpaid','paid','overdue') DEFAULT 'draft',
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `issued_at` datetime DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `project_id`, `invoice_no`, `status`, `due_date`, `notes`, `total`, `issued_at`, `paid_at`) VALUES
(3, 2, 'INV-2026-001', 'draft', '2026-06-27', 'wish money on nb `1234\'', 900.00, '2026-05-27 00:50:16', NULL),
(4, 3, 'INV-2026-002', 'paid', '2026-07-02', 'wish money 12345', 1200.00, '2026-05-28 12:00:18', '2026-06-12 09:28:25'),
(5, 6, 'INV-2026-003', 'paid', '2026-07-22', 'wish money', 400.00, '2026-06-03 21:48:01', '2026-06-08 17:50:32'),
(6, 7, 'INV-2026-004', 'draft', '2026-06-13', 'OMT 1234566', 2500.00, '2026-06-08 18:54:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(8,2) DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`item_id`, `invoice_id`, `description`, `quantity`, `unit_price`) VALUES
(3, 3, 'design phase', 1.00, 300.00),
(4, 3, 'Filling elements', 1.00, 400.00),
(5, 3, 'add a QR code', 1.00, 200.00),
(6, 4, 'AI and NLP Layer', 1.00, 500.00),
(7, 4, 'connections', 1.00, 200.00),
(8, 4, 'UX and Deployment', 1.00, 300.00),
(9, 4, 'security layer', 1.00, 200.00),
(10, 5, 'logo', 1.00, 100.00),
(11, 5, 'security', 1.00, 300.00),
(12, 6, 'core system', 1.00, 1500.00),
(13, 6, 'PoS integration', 1.00, 600.00),
(14, 6, 'database and security', 1.00, 400.00);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','in_progress','review','completed','cancelled') DEFAULT 'draft',
  `deadline` date DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `cover_color` varchar(7) DEFAULT '#FFCBB4',
  `emoji` varchar(10) DEFAULT '?',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `client_id`, `title`, `description`, `status`, `deadline`, `budget`, `cover_color`, `emoji`, `created_at`, `updated_at`) VALUES
(2, 3, 'Pharmacy Mobile Application', 'Mobile Application for showing pharmacy\'s elements ...', 'in_progress', '2026-06-30', 700.00, '#C9C2F0', 'folder', '2026-05-27 00:39:11', '2026-06-09 22:16:39'),
(3, 4, 'Artificial Intelligence and Data', 'custom chatbot Development', 'draft', '2026-07-03', 1050.00, '#B6E8D3', 'mail', '2026-05-28 11:54:43', '2026-06-12 19:19:44'),
(5, 4, 'custom Saas Dashboard', 'integrate analytical tools ..', 'in_progress', '2026-08-29', 400.00, '#F9D4DC', 'plant', '2026-05-28 13:41:49', '2026-06-12 19:19:39'),
(6, 5, 'art app', 'xxx', 'completed', '2026-07-22', 700.00, '#B6E8D3', 'folder', '2026-06-03 21:44:42', '2026-06-12 19:19:42'),
(7, 3, 'smart inventory management', 'real time stock updates', 'completed', '2026-06-24', 1800.00, '#FAE4A2', 'folder', '2026-06-06 13:38:16', '2026-06-12 19:19:41'),
(8, 3, 'xx', 'xx', 'review', NULL, 0.00, '#FAE4A2', 'folder', '2026-06-12 10:23:52', '2026-06-12 19:19:37');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `column_name` enum('todo','in_progress','done') DEFAULT 'todo',
  `due_date` date DEFAULT NULL,
  `is_done` tinyint(1) DEFAULT 0,
  `position` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `project_id`, `title`, `description`, `priority`, `column_name`, `due_date`, `is_done`, `position`, `created_at`) VALUES
(5, 2, 'create db', '', 'high', 'in_progress', NULL, 0, 0, '2026-05-27 00:39:32'),
(6, 2, 'add logo', '', 'low', 'in_progress', NULL, 0, 1, '2026-05-27 00:39:45'),
(7, 2, 'generate stickers', '', 'low', 'done', NULL, 1, 0, '2026-05-27 00:40:00'),
(8, 2, 'Create flutter files', '', 'high', 'todo', NULL, 0, 2, '2026-05-27 00:40:10'),
(9, 2, 'login page', '', 'medium', 'todo', NULL, 0, 3, '2026-05-27 00:40:19'),
(13, 3, 'security', '', 'medium', 'todo', '2026-06-17', 0, 1, '2026-05-28 13:21:22'),
(14, 3, 'ux', '', 'medium', 'done', '2026-06-20', 1, 0, '2026-05-28 13:21:58'),
(15, 3, 'chatbot', '', 'high', 'in_progress', '2026-06-26', 0, 1, '2026-05-28 13:22:40'),
(16, 3, 'cc', '', 'medium', 'in_progress', '2026-06-19', 0, 1, '2026-06-03 21:45:23'),
(17, 6, 'ui / ux', '', 'medium', 'done', '2026-05-20', 1, 0, '2026-06-08 18:47:15'),
(18, 6, 'images', '', 'medium', 'done', '2026-05-06', 1, 1, '2026-06-08 18:47:33'),
(19, 6, 'file upload', '', 'medium', 'done', '2026-04-14', 1, 2, '2026-06-08 18:48:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('freelancer','client') NOT NULL,
  `gender` enum('male','female') DEFAULT 'female',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `gender`, `created_at`) VALUES
(1, 'israa', 'israa@portal.com', '$2y$10$/db/OwZh0kJZbdKyLIbqVeKAIzbOJdU2u8FzT.DMpGNp5jFqnrYFm', 'freelancer', 'female', '2026-05-25 23:11:23'),
(4, 'Ali Bazzal', 'ali@portal.com', '$2y$10$QdqGt2bGRxfYFJRzhrUWteyigIT5bYYiuo3q2/VqpmtJGEuSTFhcq', 'client', 'male', '2026-05-27 00:08:33'),
(5, 'zahraa', 'zahraa@portal.com', '$2y$10$lTsvxh/wtmLwUIJBQ3gTzeOGUHXmPiyJWqg5TP/gQSAdZXUkHhJIi', 'client', 'female', '2026-05-28 11:45:06'),
(6, 'shahzanan B', 'xx@portal.com', '$2y$10$f8fm2OJxKpIT1zkNMl3z3e07yf55r1EHaoDm/VUoFWk9xOI16eg0e', 'client', 'male', '2026-06-03 21:40:21'),
(7, 'Sarah Khalil', 'sarah@portal.com', '$2y$10$Y80o6FXdEIPDEeX5Ay8z6e8vtD6uISheVXI/IsaDsw3OKwttDJCLe', 'client', 'female', '2026-06-12 10:22:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
