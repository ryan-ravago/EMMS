-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2026 at 10:26 AM
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
-- Database: `emms`
--

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `a_id` varchar(10) NOT NULL,
  `a_present_tense` varchar(30) NOT NULL,
  `a_past_tense` varchar(30) NOT NULL,
  `a_icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`a_id`, `a_present_tense`, `a_past_tense`, `a_icon`) VALUES
('approve', 'Approve', 'Approved', 'heroicon-o-hand-thumb-up'),
('cancel', 'Cancel', 'Cancelled', 'heroicon-o-x-circle'),
('create', 'Create', 'Created', 'heroicon-o-plus-circle'),
('drg', 'Disregard', 'Disregarded', 'heroicon-o-x-mark'),
('mac', 'Mark as Completed', 'Marked as Completed', 'heroicon-o-check-circle'),
('mwo', 'Make Work Order', 'Made Work Order', 'heroicon-o-wrench-screwdriver'),
('reject', 'Reject', 'Rejected', 'heroicon-o-no-symbol'),
('reqcom', 'Request Completion Approval', 'Requested Completion Approval', 'heroicon-o-check-badge'),
('rtv', 'Reactivate', 'Reactivated', 'heroicon-m-arrow-path'),
('snz', 'Snooze', 'Snoozed', 'heroicon-o-clock'),
('upt', 'Update', 'Updated', 'heroicon-o-arrow-path-rounded-square');

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'default', 'created', 'App\\Models\\WorkOrder', 'created', 31, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_title\":\"66\",\"wo_status_id\":\"inprog\",\"wo_dep_id\":2}}', NULL, '2026-06-11 02:55:50', '2026-06-11 02:55:50'),
(2, 'default', 'created', 'App\\Models\\Inspection', 'created', 47, 'App\\Models\\AppUser', 5, '{\"attributes\":{\"ins_dep_id\":2,\"ins_eqm_id\":6,\"ins_by\":5}}', NULL, '2026-06-11 03:06:17', '2026-06-11 03:06:17'),
(3, 'default', 'created', 'App\\Models\\RequestorWorkOrder', 'created', 32, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_title\":\"test\",\"wo_status_id\":\"pnd\",\"wo_dep_id\":2}}', NULL, '2026-06-16 02:35:41', '2026-06-16 02:35:41'),
(4, 'default', 'updated', 'App\\Models\\WorkOrder', 'updated', 32, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"rej\"},\"old\":{\"wo_status_id\":\"pnd\"}}', NULL, '2026-06-16 03:11:34', '2026-06-16 03:11:34'),
(5, 'default', 'created', 'App\\Models\\RequestorWorkOrder', 'created', 33, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_title\":\"Voluptatem dignissim\",\"wo_status_id\":\"pnd\",\"wo_dep_id\":2}}', NULL, '2026-06-16 03:13:46', '2026-06-16 03:13:46'),
(8, 'default', 'updated', 'App\\Models\\WorkOrder', 'updated', 33, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"inprog\"},\"old\":{\"wo_status_id\":\"pnd\"}}', NULL, '2026-06-16 03:27:02', '2026-06-16 03:27:02'),
(9, 'default', 'created', 'App\\Models\\RequestorWorkOrder', 'created', 34, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_title\":\"tt\",\"wo_status_id\":\"pnd\",\"wo_dep_id\":2}}', NULL, '2026-06-16 05:15:56', '2026-06-16 05:15:56'),
(10, 'default', 'created', 'App\\Models\\RequestorWorkOrder', 'created', 35, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_title\":\"te\",\"wo_status_id\":\"pnd\",\"wo_dep_id\":2}}', NULL, '2026-06-16 05:21:02', '2026-06-16 05:21:02'),
(11, 'default', 'created', 'App\\Models\\RequestorWorkOrder', 'created', 36, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_title\":\"ere\",\"wo_status_id\":\"pnd\",\"wo_dep_id\":2}}', NULL, '2026-06-16 05:21:49', '2026-06-16 05:21:49'),
(12, 'default', 'created', 'App\\Models\\WorkOrder', 'created', 37, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_title\":\"tty\",\"wo_status_id\":\"pnd\",\"wo_dep_id\":2}}', NULL, '2026-06-16 06:57:07', '2026-06-16 06:57:07'),
(13, 'default', 'updated', 'App\\Models\\WorkOrder', 'updated', 37, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"rej\"},\"old\":{\"wo_status_id\":\"pnd\"}}', NULL, '2026-06-16 07:19:41', '2026-06-16 07:19:41'),
(14, 'default', 'updated', 'App\\Models\\WorkOrder', 'updated', 36, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"inprog\"},\"old\":{\"wo_status_id\":\"pnd\"}}', NULL, '2026-06-16 07:20:35', '2026-06-16 07:20:35'),
(15, 'WorkOrder', 'Work Order Action: Rejected - Status: Rejected', 'App\\Models\\WorkOrderLog', 'created', 64, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wol_id\":64,\"wol_wo_id\":35,\"wol_a_id\":\"reject\",\"wol_status_id\":\"rej\",\"wol_a_log\":\"Rejected\",\"wol_note\":\"ty\",\"wol_status_log\":\"Rejected\",\"wol_by\":3,\"wol_dt\":\"2026-06-16 16:09:41\"}}', NULL, '2026-06-16 08:09:41', '2026-06-16 08:09:41'),
(16, 'WorkOrder', 'Work Order has been updated', 'App\\Models\\WorkOrder', 'updated', 35, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"rej\",\"wo_closed_dt\":\"2026-06-16 16:09:41\"},\"old\":{\"wo_status_id\":\"pnd\",\"wo_closed_dt\":null}}', NULL, '2026-06-16 08:09:41', '2026-06-16 08:09:41'),
(17, 'WorkOrder', 'Work Order has been updated', 'App\\Models\\WorkOrder', 'updated', 34, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"rej\",\"wo_closed_dt\":\"2026-06-16 16:14:07\"},\"old\":{\"wo_status_id\":\"pnd\",\"wo_closed_dt\":null}}', NULL, '2026-06-16 08:14:07', '2026-06-16 08:14:07'),
(18, 'WorkOrder', 'Rejected', 'App\\Models\\WorkOrder', 'reject', 34, 'App\\Models\\AppUser', 3, '{\"action_id\":\"reject\",\"status_id\":\"rej\",\"note\":\"wwewew\"}', NULL, '2026-06-16 08:14:07', '2026-06-16 08:14:07'),
(19, 'Inspection', 'Inspection Item has been updated', 'App\\Models\\InspectionItem', 'updated', 41, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"insi_status_id\":\"pnd\"},\"old\":{\"insi_status_id\":\"inprog\"}}', NULL, '2026-06-17 03:04:46', '2026-06-17 03:04:46'),
(20, 'WorkOrder', 'Work Order has been updated', 'App\\Models\\WorkOrder', 'updated', 16, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"cnc\",\"wo_closed_dt\":\"2026-06-17 11:04:46\"},\"old\":{\"wo_status_id\":\"inprog\",\"wo_closed_dt\":\"2026-06-05 15:57:53\"}}', NULL, '2026-06-17 03:04:46', '2026-06-17 03:04:46'),
(21, 'WorkOrder', 'Cancelled', 'App\\Models\\WorkOrder', 'cancel', 16, 'App\\Models\\AppUser', 3, '{\"action_id\":\"cancel\",\"status_id\":\"cnc\",\"note\":\"d\"}', NULL, '2026-06-17 03:04:46', '2026-06-17 03:04:46'),
(22, 'Inspection', 'Inspection Item has been updated', 'App\\Models\\InspectionItem', 'updated', 41, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"insi_status_id\":\"pnd\"},\"old\":{\"insi_status_id\":\"inprog\"}}', NULL, '2026-06-17 03:10:11', '2026-06-17 03:10:11'),
(23, 'WorkOrder', 'Work Order has been updated', 'App\\Models\\WorkOrder', 'updated', 16, 'App\\Models\\AppUser', 3, '{\"attributes\":{\"wo_status_id\":\"cnc\",\"wo_closed_dt\":\"2026-06-17 11:10:11\"},\"old\":{\"wo_status_id\":\"inprog\",\"wo_closed_dt\":\"2026-06-17 11:04:46\"}}', NULL, '2026-06-17 03:10:11', '2026-06-17 03:10:11'),
(24, 'WorkOrder', 'Cancelled', 'App\\Models\\WorkOrder', 'cancel', 16, 'App\\Models\\AppUser', 3, '{\"action_id\":\"cancel\",\"status_id\":\"cnc\",\"note\":\"k\"}', NULL, '2026-06-17 03:10:11', '2026-06-17 03:10:11'),
(25, 'WorkOrder', 'Work Order has been created', 'App\\Models\\RequestorWorkOrder', 'created', 38, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_id\":38,\"wo_no\":\"WO-ELEC-260618001\",\"wo_eqm_id\":5,\"wo_dep_id\":3,\"wo_mt_id\":null,\"wo_insi_id\":null,\"wo_title\":\"weew\",\"wo_req_desc\":\"wererw\",\"wo_desc\":null,\"wo_prio_id\":1,\"wo_status_id\":\"pnd\",\"wo_attachments\":[],\"wo_created_by\":11,\"wo_created_dt\":\"2026-06-18 15:57:05\",\"wo_closed_dt\":null}}', NULL, '2026-06-18 07:57:05', '2026-06-18 07:57:05'),
(26, 'WorkOrder', 'Work Order has been created', 'App\\Models\\RequestorWorkOrder', 'created', 39, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_id\":39,\"wo_no\":\"WO-ELEC-260618002\",\"wo_eqm_id\":5,\"wo_dep_id\":3,\"wo_mt_id\":null,\"wo_insi_id\":null,\"wo_title\":\"dded\",\"wo_req_desc\":\"d\",\"wo_desc\":null,\"wo_prio_id\":1,\"wo_status_id\":\"pnd\",\"wo_attachments\":[],\"wo_created_by\":11,\"wo_created_dt\":\"2026-06-18 15:58:44\",\"wo_closed_dt\":null}}', NULL, '2026-06-18 07:58:44', '2026-06-18 07:58:44'),
(27, 'WorkOrder', 'Work Order has been updated', 'App\\Models\\WorkOrder', 'updated', 39, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_status_id\":\"cnc\",\"wo_closed_dt\":\"2026-06-18 16:08:49\"},\"old\":{\"wo_status_id\":\"pnd\",\"wo_closed_dt\":null}}', NULL, '2026-06-18 08:08:49', '2026-06-18 08:08:49'),
(28, 'WorkOrder', 'Cancelled', 'App\\Models\\WorkOrder', 'cancel', 39, 'App\\Models\\AppUser', 11, '{\"action_id\":\"cancel\",\"status_id\":\"cnc\",\"note\":\"df\"}', NULL, '2026-06-18 08:08:49', '2026-06-18 08:08:49'),
(29, 'WorkOrder', 'Work Order has been created', 'App\\Models\\RequestorWorkOrder', 'created', 40, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_id\":40,\"wo_no\":\"WO-ELEC-260618003\",\"wo_eqm_id\":5,\"wo_dep_id\":3,\"wo_mt_id\":null,\"wo_insi_id\":null,\"wo_title\":\"Doloremque nostrud v\",\"wo_req_desc\":\"Aliqua Molestias no\",\"wo_desc\":null,\"wo_prio_id\":1,\"wo_status_id\":\"pnd\",\"wo_attachments\":[],\"wo_created_by\":11,\"wo_created_dt\":\"2026-06-18 16:16:53\",\"wo_closed_dt\":null}}', NULL, '2026-06-18 08:16:53', '2026-06-18 08:16:53'),
(30, 'WorkOrder', 'Work Order has been updated', 'App\\Models\\WorkOrder', 'updated', 40, 'App\\Models\\AppUser', 11, '{\"attributes\":{\"wo_status_id\":\"cnc\",\"wo_closed_dt\":\"2026-06-18 16:17:54\"},\"old\":{\"wo_status_id\":\"pnd\",\"wo_closed_dt\":null}}', NULL, '2026-06-18 08:17:54', '2026-06-18 08:17:54'),
(31, 'WorkOrder', 'Cancelled', 'App\\Models\\WorkOrder', 'cancel', 40, 'App\\Models\\AppUser', 11, '{\"action_id\":\"cancel\",\"status_id\":\"cnc\",\"note\":\"f\"}', NULL, '2026-06-18 08:17:54', '2026-06-18 08:17:54');

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `last_equipment_sync` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`id`, `key`, `value`, `last_equipment_sync`, `created_at`, `updated_at`) VALUES
(1, 'sap_sync_time', '07:00', '2026-05-29 09:31:35', '2026-05-20 08:15:26', '2026-05-29 01:31:35');

-- --------------------------------------------------------

--
-- Table structure for table `app_users`
--

CREATE TABLE `app_users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_fname` varchar(255) NOT NULL,
  `user_mname` varchar(255) DEFAULT NULL,
  `user_lname` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_avatar` varchar(255) DEFAULT NULL,
  `user_contact_no` varchar(255) DEFAULT NULL,
  `user_fb_profile_link` text DEFAULT NULL,
  `user_dep_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_users`
--

INSERT INTO `app_users` (`user_id`, `user_fname`, `user_mname`, `user_lname`, `user_email`, `user_avatar`, `user_contact_no`, `user_fb_profile_link`, `user_dep_id`) VALUES
(1, 'Ryan Albert', 'Sulapas', 'Masungsong', 'ryan.masungsong@ravago.com.ph', 'https://lh3.googleusercontent.com/a/ACg8ocINyb5qLl-pa7dA7z85GnXPBMdfLaRneu5y9bPSfj9R1cSaRA=s96-c', '09477928236', NULL, 1),
(2, 'Kennery', NULL, 'Villacaol', 'kennery.villacaol@ravago.com.ph', NULL, NULL, NULL, 1),
(3, 'Alain', NULL, 'Evangelista', 'alain.evangelista@ravago.com.ph', NULL, NULL, NULL, 2),
(4, 'Richard', NULL, 'Vea', 'richard.vea@ravago.com.ph', NULL, NULL, NULL, 2),
(5, 'Angelo', NULL, 'Gaspar', 'angelo.gaspar@ravago.com.ph', NULL, NULL, NULL, 2),
(6, 'Richelle', NULL, 'Dionisio', 'rich.dionisio@ravago.com.ph', NULL, NULL, NULL, 3),
(7, 'Michelle Lyn', NULL, 'Yap', 'michelle.yap@ravago.com.ph', NULL, NULL, NULL, 4),
(8, 'Charles', NULL, 'Pearson', 'zazo@mailinator.com', NULL, NULL, NULL, 4),
(9, 'Igor', NULL, 'Dale', 'ginyj@mailinator.com', NULL, NULL, NULL, 4),
(10, 'Whitney', NULL, 'Norman', 'ludon@mailinator.com', NULL, NULL, NULL, 4),
(11, 'Reinier', NULL, 'Intendencia', 'reinier.intendencia@ravago.com.ph', NULL, NULL, NULL, 5),
(12, 'Joan', NULL, 'Beltran', 'joan.beltran@ravago.com.ph', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dep_id` bigint(20) UNSIGNED NOT NULL,
  `dep_code` varchar(255) NOT NULL,
  `dep_name` varchar(255) NOT NULL,
  `is_maintenance` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dep_id`, `dep_code`, `dep_name`, `is_maintenance`) VALUES
(1, 'IT', 'Information Technology', 0),
(2, 'MECH', 'Mechanical', 1),
(3, 'ELEC', 'Electrical', 1),
(4, 'PREV', 'Preventive', 1),
(5, 'OPS', 'Operations', 0);

-- --------------------------------------------------------

--
-- Table structure for table `equipment_brands`
--

CREATE TABLE `equipment_brands` (
  `eqmb_id` bigint(20) UNSIGNED NOT NULL,
  `eqmb_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_categories`
--

CREATE TABLE `equipment_categories` (
  `eqmc_id` bigint(20) UNSIGNED NOT NULL,
  `eqmc_name` varchar(100) NOT NULL,
  `eqmc_parent_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_models`
--

CREATE TABLE `equipment_models` (
  `eqmm_id` bigint(20) UNSIGNED NOT NULL,
  `eqmm_name` varchar(150) NOT NULL,
  `eqmm_eqmc_id` bigint(20) UNSIGNED DEFAULT NULL,
  `eqmm_brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `eqmm_fuel_type` bigint(20) UNSIGNED DEFAULT NULL,
  `eqmm_eqmt_id` bigint(20) UNSIGNED DEFAULT NULL,
  `eqmm_max_capacity_tons` decimal(8,2) DEFAULT NULL,
  `eqmm_max_reach_meters` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_tasks_schedules`
--

CREATE TABLE `equipment_tasks_schedules` (
  `ets_id` bigint(20) UNSIGNED NOT NULL,
  `ets_dep_id` bigint(20) UNSIGNED NOT NULL,
  `ets_eqm_id` bigint(20) UNSIGNED NOT NULL,
  `ets_task_id` bigint(20) UNSIGNED NOT NULL,
  `ets_sort_order` int(11) NOT NULL DEFAULT 0,
  `ets_itrv_years` int(11) DEFAULT NULL,
  `ets_itrv_months` int(11) DEFAULT NULL,
  `ets_itrv_weeks` int(11) DEFAULT NULL,
  `ets_itrv_days` int(11) DEFAULT NULL,
  `ets_sched_time` time DEFAULT NULL,
  `ets_due_effectivity_dt` date NOT NULL,
  `ets_due_dt` datetime DEFAULT NULL,
  `ets_assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `ets_assigned_at` datetime NOT NULL,
  `ets_last_assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `ets_last_assigned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_tasks_schedules`
--

INSERT INTO `equipment_tasks_schedules` (`ets_id`, `ets_dep_id`, `ets_eqm_id`, `ets_task_id`, `ets_sort_order`, `ets_itrv_years`, `ets_itrv_months`, `ets_itrv_weeks`, `ets_itrv_days`, `ets_sched_time`, `ets_due_effectivity_dt`, `ets_due_dt`, `ets_assigned_by`, `ets_assigned_at`, `ets_last_assigned_by`, `ets_last_assigned_at`) VALUES
(5, 2, 6, 6, 0, 1, 0, 0, 0, '08:00:00', '2026-06-01', '2027-06-01 08:00:00', 3, '2026-06-01 16:57:16', NULL, NULL),
(6, 1, 6, 11, 0, 0, 1, 0, 0, '08:00:00', '2026-05-05', NULL, 1, '2026-06-06 01:28:21', NULL, NULL),
(15, 4, 6, 14, 0, 1, 0, 0, 0, '09:00:00', '2025-06-15', NULL, 7, '2026-06-15 08:51:58', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `equipment_task_checklist_template`
--

CREATE TABLE `equipment_task_checklist_template` (
  `etct_id` bigint(20) UNSIGNED NOT NULL,
  `etct_dep_id` bigint(20) UNSIGNED NOT NULL,
  `etct_eqm_id` bigint(20) UNSIGNED NOT NULL,
  `etct_task_id` bigint(20) UNSIGNED NOT NULL,
  `etct_created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `etct_created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_task_checklist_template`
--

INSERT INTO `equipment_task_checklist_template` (`etct_id`, `etct_dep_id`, `etct_eqm_id`, `etct_task_id`, `etct_created_by`, `etct_created_at`) VALUES
(3, 2, 6, 7, 3, '2026-06-04 09:54:25'),
(4, 2, 6, 8, 3, '2026-06-05 13:21:59');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_types`
--

CREATE TABLE `equipment_types` (
  `eqmt_id` bigint(20) UNSIGNED NOT NULL,
  `eqmt_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_units`
--

CREATE TABLE `equipment_units` (
  `eqm_id` bigint(20) UNSIGNED NOT NULL,
  `eqm_eqmm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `eqm_name` varchar(255) NOT NULL,
  `eqm_vin` varchar(255) DEFAULT NULL,
  `eqm_plate_num` varchar(255) DEFAULT NULL,
  `eqm_prc_code` varchar(30) NOT NULL,
  `eqm_serial_num` varchar(255) DEFAULT NULL,
  `eqm_engine` varchar(255) DEFAULT NULL,
  `eqm_is_active` tinyint(1) NOT NULL DEFAULT 1,
  `eqm_updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_units`
--

INSERT INTO `equipment_units` (`eqm_id`, `eqm_eqmm_id`, `eqm_name`, `eqm_vin`, `eqm_plate_num`, `eqm_prc_code`, `eqm_serial_num`, `eqm_engine`, `eqm_is_active`, `eqm_updated_at`) VALUES
(1, NULL, 'RAVAGO EQUIPMENT RENTALS INC.', NULL, NULL, '1010', NULL, NULL, 0, NULL),
(2, NULL, 'SMART RIGS CRANE CORP', NULL, NULL, '1020', NULL, NULL, 0, NULL),
(3, NULL, 'LIFTRITE, INC.', NULL, NULL, '1030', NULL, NULL, 0, NULL),
(4, NULL, 'TELROSA HOLDINGS', NULL, NULL, '1040', NULL, NULL, 0, NULL),
(5, NULL, '10 Wheeler Truck #1', NULL, NULL, '10w#1', NULL, NULL, 1, NULL),
(6, NULL, '10 Wheeler Truck #2', NULL, NULL, '10w#2', NULL, NULL, 1, NULL),
(7, NULL, '10 Wheeler Truck #3', NULL, NULL, '10w#3', NULL, NULL, 1, NULL),
(8, NULL, '10-Wheeler No. 4', NULL, NULL, '10W#4', NULL, NULL, 1, NULL),
(9, NULL, '10 Wheeler Truck #5', NULL, NULL, '10w#5', NULL, NULL, 1, NULL),
(10, NULL, '10 Wheeler Truck #6', NULL, NULL, '10w#6', NULL, NULL, 1, NULL),
(11, NULL, '10 Wheeler Truck #7', NULL, NULL, '10w#7', NULL, NULL, 1, NULL),
(12, NULL, 'CANLUBANG GATEWAY INC', NULL, NULL, '1110', NULL, NULL, 0, NULL),
(13, NULL, '12 Wheeler Truck#2', NULL, NULL, '12W#2', NULL, NULL, 1, NULL),
(14, NULL, '12 Wheeler Truck #3', NULL, NULL, '12W#3', NULL, NULL, 1, NULL),
(15, NULL, '12 Wheeler Truck #4', NULL, NULL, '12W#4', NULL, NULL, 1, NULL),
(16, NULL, '12 Wheeler Truck #5', NULL, NULL, '12W#5', NULL, NULL, 1, NULL),
(17, NULL, '12 Wheeler Truck #6', NULL, NULL, '12W#6', NULL, NULL, 1, NULL),
(18, NULL, '12 Wheeler Truck #7', NULL, NULL, '12W#7', NULL, NULL, 1, NULL),
(19, NULL, '12 Wheeler Truck #8', NULL, NULL, '12W#8', NULL, NULL, 1, NULL),
(20, NULL, '12 Wheeler Truck #9', NULL, NULL, '12W#9', NULL, NULL, 1, NULL),
(21, NULL, '50 NBB', NULL, NULL, '50', NULL, NULL, 1, NULL),
(22, NULL, '52 NBB (Texwood)', NULL, NULL, '52', NULL, NULL, 1, NULL),
(23, NULL, '64 NBB', NULL, NULL, '64', NULL, NULL, 1, NULL),
(24, NULL, '6 Wheeler Truck #1', NULL, NULL, '6W#1', NULL, NULL, 1, NULL),
(25, NULL, '6 Wheeler Truck #2', NULL, NULL, '6W#2', NULL, NULL, 1, NULL),
(26, NULL, '70 C3 Road (Outlast 2)', NULL, NULL, '70', NULL, NULL, 1, NULL),
(27, NULL, 'EX-700-G crawler', NULL, NULL, '700-G CR', NULL, NULL, 1, NULL),
(28, NULL, 'Hyundai Forklift 7T #1', NULL, NULL, '70DF#1', NULL, NULL, 1, NULL),
(29, NULL, 'Hyundai Forklift 7T #2', NULL, NULL, '70DF#2', NULL, NULL, 1, NULL),
(30, NULL, '72 C-3', NULL, NULL, '72', NULL, NULL, 1, NULL),
(31, NULL, '73 C-3', NULL, NULL, '73', NULL, NULL, 1, NULL),
(32, NULL, '75 C3 Rd', NULL, NULL, '75', NULL, NULL, 1, NULL),
(33, NULL, '88 C-3', NULL, NULL, '88', NULL, NULL, 1, NULL),
(34, NULL, 'Demag AC100 #1', NULL, NULL, 'AC100#1', NULL, NULL, 1, NULL),
(35, NULL, 'Demag AC100 #2', NULL, NULL, 'AC100#2', NULL, NULL, 0, NULL),
(36, NULL, 'Demag AC100 #3', NULL, NULL, 'AC100#3', NULL, NULL, 0, NULL),
(37, NULL, 'Demag AC100 #4', NULL, NULL, 'AC100#4', NULL, NULL, 0, NULL),
(38, NULL, 'Demag AC-200 #1', NULL, NULL, 'AC200#1', NULL, NULL, 1, NULL),
(39, NULL, 'Demag AC-200#2', NULL, NULL, 'AC200#2', NULL, NULL, 1, NULL),
(40, NULL, 'Demag AC-200#3', NULL, NULL, 'AC200#3', NULL, NULL, 1, NULL),
(41, NULL, 'Demag AC500', NULL, NULL, 'AC500#1', NULL, NULL, 1, NULL),
(42, NULL, 'Demag 700 tonner', NULL, NULL, 'AC650', NULL, NULL, 0, NULL),
(43, NULL, 'Demag 700 tonner', NULL, NULL, 'AC700#1', NULL, NULL, 1, NULL),
(44, NULL, 'MAN 1014 8x8 Tractor #1', NULL, NULL, 'ARMYTH#1', NULL, NULL, 1, NULL),
(45, NULL, 'MAN 1014 8x8 Tractor #2', NULL, NULL, 'ARMYTH#2', NULL, NULL, 1, NULL),
(46, NULL, 'MAN 1014 8x8 Tractor #3', NULL, NULL, 'ARMYTH#3', NULL, NULL, 1, NULL),
(47, NULL, 'MAN 1014 8x8 Tractor #4', NULL, NULL, 'ARMYTH#4', NULL, NULL, 1, NULL),
(48, NULL, 'Bagombong Caloocan Lot', NULL, NULL, 'BAGOMBNG', NULL, NULL, 1, NULL),
(49, NULL, 'BATAAN', NULL, NULL, 'BATAAN', NULL, NULL, 1, NULL),
(50, NULL, 'Huabang 70m Ex Blade Carrier#1', NULL, NULL, 'BC4X70#1', NULL, NULL, 1, NULL),
(51, NULL, 'Huabang 70m Ex Blade Carrier#2', NULL, NULL, 'BC4X70#2', NULL, NULL, 1, NULL),
(52, NULL, 'Hyst Blade Carrier #1', NULL, NULL, 'BC6X67#1', NULL, NULL, 1, NULL),
(53, NULL, 'Hyst Blade Carrier #2', NULL, NULL, 'BC6X67#2', NULL, NULL, 1, NULL),
(54, NULL, 'Hyst Blade Carrier #3', NULL, NULL, 'BC6X67#3', NULL, NULL, 1, NULL),
(55, NULL, 'Beam', NULL, NULL, 'BEAM', NULL, NULL, 1, NULL),
(56, NULL, 'Bensan Valyard', NULL, NULL, 'BENSAN', NULL, NULL, 1, NULL),
(57, NULL, 'Blastmaster Yard 78 C3', NULL, NULL, 'BLAST', NULL, NULL, 1, NULL),
(58, NULL, 'Hyst Blade Lifter #1', NULL, NULL, 'BLFTR 01', NULL, NULL, 1, NULL),
(59, NULL, 'Hyst Blade Lifter #2', NULL, NULL, 'BLFTR 02', NULL, NULL, 1, NULL),
(60, NULL, 'Hyst Blade Lifter #3', NULL, NULL, 'BLFTR 03', NULL, NULL, 1, NULL),
(61, NULL, 'Ballast Trailer #1 (Goldhofer)', NULL, NULL, 'BLST#1', NULL, NULL, 1, NULL),
(62, NULL, 'Ballast Trailer #2 (ES-GE)', NULL, NULL, 'BLST#2', NULL, NULL, 1, NULL),
(63, NULL, 'Ballast Trailer #3 (Nooteboom)', NULL, NULL, 'BLST#3', NULL, NULL, 1, NULL),
(64, NULL, 'Ballast Trailer #4 (Nooteboom)', NULL, NULL, 'BLST#4', NULL, NULL, 1, NULL),
(65, NULL, 'Canter Trucks', NULL, NULL, 'CANTER', NULL, NULL, 1, NULL),
(66, NULL, 'Demag CC2800-1 #2', NULL, NULL, 'CC2800#2', NULL, NULL, 1, NULL),
(67, NULL, 'Demag CC2800-1 #1', NULL, NULL, 'CC2800-1', NULL, NULL, 1, NULL),
(68, NULL, 'CDO Yard', NULL, NULL, 'CDO', NULL, NULL, 1, NULL),
(69, NULL, 'CDO EQUIPMENT DEPOT', NULL, NULL, 'CDOEQPT', NULL, NULL, 1, NULL),
(70, NULL, 'Cebu Yard', NULL, NULL, 'CEBU', NULL, NULL, 1, NULL),
(71, NULL, 'General Centre', NULL, NULL, 'Centr_z', NULL, NULL, 1, NULL),
(72, NULL, 'General Centre 2', NULL, NULL, 'Centr_z2', NULL, NULL, 1, NULL),
(73, NULL, 'General Centre 3', NULL, NULL, 'Centr_z3', NULL, NULL, 1, NULL),
(74, NULL, 'General Centre 4', NULL, NULL, 'Centr_z4', NULL, NULL, 1, NULL),
(75, NULL, 'General Centre 5', NULL, NULL, 'Centr_z5', NULL, NULL, 1, NULL),
(76, NULL, 'CGH LOT', NULL, NULL, 'CGH', NULL, NULL, 1, NULL),
(77, NULL, 'CANLUBANG GATEWAY INC', NULL, NULL, 'CGI', NULL, NULL, 1, NULL),
(78, NULL, 'Counterweight 10T', NULL, NULL, 'CWT 10', NULL, NULL, 1, NULL),
(79, NULL, 'Counterweight 15T', NULL, NULL, 'CWT 15', NULL, NULL, 1, NULL),
(80, NULL, 'Counterweight 30T', NULL, NULL, 'CWT 30', NULL, NULL, 1, NULL),
(81, NULL, 'Counterweight 32T', NULL, NULL, 'CWT 32', NULL, NULL, 1, NULL),
(82, NULL, 'Counterweight 40T', NULL, NULL, 'CWT 40', NULL, NULL, 1, NULL),
(83, NULL, 'Counterweight 50T', NULL, NULL, 'CWT 50', NULL, NULL, 1, NULL),
(84, NULL, 'FL - Boss L33 25T', NULL, NULL, 'D2512G#2', NULL, NULL, 0, NULL),
(85, NULL, 'FL - Boss L34 D2512GP 25T', NULL, NULL, 'D2512GP', NULL, NULL, 0, NULL),
(86, NULL, 'Davao Yard', NULL, NULL, 'DAVAO', NULL, NULL, 1, NULL),
(87, NULL, 'DAVAO CRANE DEPOT CORP', NULL, NULL, 'DCDC', NULL, NULL, 1, NULL),
(88, NULL, 'Drop Deck Trailer SL', NULL, NULL, 'DDECK SL', NULL, NULL, 0, NULL),
(89, NULL, 'Drop Deck Trailer UT', NULL, NULL, 'DDECK UT', NULL, NULL, 0, NULL),
(90, NULL, 'HOWO Dump Truck #1', NULL, NULL, 'DTHOWO#1', NULL, NULL, 1, NULL),
(91, NULL, 'HOWO Dump Truck #2', NULL, NULL, 'DTHOWO#2', NULL, NULL, 1, NULL),
(92, NULL, 'HOWO Dump Truck #3', NULL, NULL, 'DTHOWO#3', NULL, NULL, 1, NULL),
(93, NULL, 'HOWO Dump Truck #4', NULL, NULL, 'DTHOWO#4', NULL, NULL, 1, NULL),
(94, NULL, 'HOWO Dump Truck #5', NULL, NULL, 'DTHOWO#5', NULL, NULL, 1, NULL),
(95, NULL, 'Elf Manlift (white) RFP 694', NULL, NULL, 'ELF 694', NULL, NULL, 1, NULL),
(96, NULL, '12W#2', NULL, NULL, 'EQ002', NULL, NULL, 0, NULL),
(97, NULL, '70DF#1', NULL, NULL, 'EQ004', NULL, NULL, 0, NULL),
(98, NULL, 'AC100#1', NULL, NULL, 'EQ006', NULL, NULL, 0, NULL),
(99, NULL, 'AC200#1', NULL, NULL, 'EQ007', NULL, NULL, 0, NULL),
(100, NULL, 'AC200#2', NULL, NULL, 'EQ008', NULL, NULL, 0, NULL),
(101, NULL, 'AC650', NULL, NULL, 'EQ011', NULL, NULL, 0, NULL),
(102, NULL, 'ARMYTH#3', NULL, NULL, 'EQ014', NULL, NULL, 0, NULL),
(103, NULL, 'BLST#1', NULL, NULL, 'EQ016', NULL, NULL, 0, NULL),
(104, NULL, 'CC2800-1', NULL, NULL, 'EQ021', NULL, NULL, 0, NULL),
(105, NULL, 'FD 2.5', NULL, NULL, 'EQ035', NULL, NULL, 0, NULL),
(106, NULL, 'FD1500#1', NULL, NULL, 'EQ037', NULL, NULL, 0, NULL),
(107, NULL, 'FD60 #5', NULL, NULL, 'EQ044', NULL, NULL, 0, NULL),
(108, NULL, 'FHD 120Z', NULL, NULL, 'EQ047', NULL, NULL, 0, NULL),
(109, NULL, 'G-5130#5', NULL, NULL, 'EQ055', NULL, NULL, 0, NULL),
(110, NULL, 'GMK5220', NULL, NULL, 'EQ056', NULL, NULL, 0, NULL),
(111, NULL, 'G-5220#2', NULL, NULL, 'EQ057', NULL, NULL, 0, NULL),
(112, NULL, 'GMK-5130', NULL, NULL, 'EQ067', NULL, NULL, 0, NULL),
(113, NULL, 'GMK5170', NULL, NULL, 'EQ068', NULL, NULL, 0, NULL),
(114, NULL, 'GMK6300L', NULL, NULL, 'EQ070', NULL, NULL, 0, NULL),
(115, NULL, 'GMK63 #4', NULL, NULL, 'EQ073', NULL, NULL, 0, NULL),
(116, NULL, 'GMK63 #5', NULL, NULL, 'EQ074', NULL, NULL, 0, NULL),
(117, NULL, 'GTJZ12#4', NULL, NULL, 'EQ091', NULL, NULL, 0, NULL),
(118, NULL, 'HBT3#26', NULL, NULL, 'EQ131', NULL, NULL, 0, NULL),
(119, NULL, 'KR-25H#6', NULL, NULL, 'EQ183', NULL, NULL, 0, NULL),
(120, NULL, 'KR-25H#7', NULL, NULL, 'EQ184', NULL, NULL, 0, NULL),
(121, NULL, 'LBT#3', NULL, NULL, 'EQ187', NULL, NULL, 0, NULL),
(122, NULL, 'LBT#5', NULL, NULL, 'EQ189', NULL, NULL, 0, NULL),
(123, NULL, 'LBT#8', NULL, NULL, 'EQ192', NULL, NULL, 0, NULL),
(124, NULL, 'LBT#9', NULL, NULL, 'EQ193', NULL, NULL, 0, NULL),
(125, NULL, 'LT1500#1', NULL, NULL, 'EQ195', NULL, NULL, 0, NULL),
(126, NULL, 'LT1500#2', NULL, NULL, 'EQ196', NULL, NULL, 0, NULL),
(127, NULL, 'Omega25T', NULL, NULL, 'EQ223', NULL, NULL, 0, NULL),
(128, NULL, 'RT530 #2', NULL, NULL, 'EQ236', NULL, NULL, 0, NULL),
(129, NULL, 'RT-551#4', NULL, NULL, 'EQ241', NULL, NULL, 0, NULL),
(130, NULL, 'RT765E#1', NULL, NULL, 'EQ250', NULL, NULL, 0, NULL),
(131, NULL, 'SJ85AJ#1', NULL, NULL, 'EQ283', NULL, NULL, 0, NULL),
(132, NULL, 'TCM FD28', NULL, NULL, 'EQ320', NULL, NULL, 0, NULL),
(133, NULL, 'TH22', NULL, NULL, 'EQ342', NULL, NULL, 0, NULL),
(134, NULL, 'TH3', NULL, NULL, 'EQ350', NULL, NULL, 0, NULL),
(135, NULL, 'TH32', NULL, NULL, 'EQ353', NULL, NULL, 0, NULL),
(136, NULL, 'TH33', NULL, NULL, 'EQ354', NULL, NULL, 0, NULL),
(137, NULL, 'TR-500', NULL, NULL, 'EQ383', NULL, NULL, 0, NULL),
(138, NULL, 'XBT16#1', NULL, NULL, 'EQ386', NULL, NULL, 0, NULL),
(139, NULL, 'XBT16#10', NULL, NULL, 'EQ387', NULL, NULL, 0, NULL),
(140, NULL, 'XBT16#14', NULL, NULL, 'EQ391', NULL, NULL, 0, NULL),
(141, NULL, 'XBT16#16', NULL, NULL, 'EQ393', NULL, NULL, 0, NULL),
(142, NULL, 'XBT16#23', NULL, NULL, 'EQ401', NULL, NULL, 0, NULL),
(143, NULL, 'XBT16#8', NULL, NULL, 'EQ408', NULL, NULL, 0, NULL),
(144, NULL, 'XC 8 #1', NULL, NULL, 'EQ410', NULL, NULL, 0, NULL),
(145, NULL, 'XC30 #12', NULL, NULL, 'EQ415', NULL, NULL, 0, NULL),
(146, NULL, 'XC30 #14', NULL, NULL, 'EQ417', NULL, NULL, 0, NULL),
(147, NULL, 'XC30 #18', NULL, NULL, 'EQ421', NULL, NULL, 0, NULL),
(148, NULL, 'XC30 #19', NULL, NULL, 'EQ422', NULL, NULL, 0, NULL),
(149, NULL, 'XC30 #2', NULL, NULL, 'EQ423', NULL, NULL, 0, NULL),
(150, NULL, 'XC30 #3', NULL, NULL, 'EQ425', NULL, NULL, 0, NULL),
(151, NULL, 'XC30 #4', NULL, NULL, 'EQ426', NULL, NULL, 0, NULL),
(152, NULL, 'XC30 #5', NULL, NULL, 'EQ427', NULL, NULL, 0, NULL),
(153, NULL, 'XC30 #9', NULL, NULL, 'EQ431', NULL, NULL, 0, NULL),
(154, NULL, 'XC70 #1', NULL, NULL, 'EQ432', NULL, NULL, 0, NULL),
(155, NULL, 'XC70#10', NULL, NULL, 'EQ436', NULL, NULL, 0, NULL),
(156, NULL, 'XC70#11', NULL, NULL, 'EQ437', NULL, NULL, 0, NULL),
(157, NULL, 'XC70#13', NULL, NULL, 'EQ439', NULL, NULL, 0, NULL),
(158, NULL, 'XC70#14', NULL, NULL, 'EQ440', NULL, NULL, 0, NULL),
(159, NULL, 'XC70#16', NULL, NULL, 'EQ442', NULL, NULL, 0, NULL),
(160, NULL, 'XC70#5', NULL, NULL, 'EQ450', NULL, NULL, 0, NULL),
(161, NULL, 'XC70#6', NULL, NULL, 'EQ451', NULL, NULL, 0, NULL),
(162, NULL, 'XC70#8', NULL, NULL, 'EQ453', NULL, NULL, 0, NULL),
(163, NULL, 'XC70#9', NULL, NULL, 'EQ454', NULL, NULL, 0, NULL),
(164, NULL, 'XCR70#2', NULL, NULL, 'EQ456', NULL, NULL, 0, NULL),
(165, NULL, 'XCT150#2', NULL, NULL, 'EQ458', NULL, NULL, 0, NULL),
(166, NULL, 'XCT80#2', NULL, NULL, 'EQ464', NULL, NULL, 0, NULL),
(167, NULL, 'XG5120#2', NULL, NULL, 'EQ472', NULL, NULL, 0, NULL),
(168, NULL, 'XG535#3', NULL, NULL, 'EQ475', NULL, NULL, 0, NULL),
(169, NULL, 'XG575#1', NULL, NULL, 'EQ479', NULL, NULL, 0, NULL),
(170, NULL, 'XG575#3', NULL, NULL, 'EQ481', NULL, NULL, 0, NULL),
(171, NULL, 'XG575#4', NULL, NULL, 'EQ482', NULL, NULL, 0, NULL),
(172, NULL, 'XGC130#3', NULL, NULL, 'EQ487', NULL, NULL, 0, NULL),
(173, NULL, 'XGC55#1', NULL, NULL, 'EQ492', NULL, NULL, 0, NULL),
(174, NULL, 'Z80 #1', NULL, NULL, 'EQ506', NULL, NULL, 0, NULL),
(175, NULL, 'Z80 #5', NULL, NULL, 'EQ510', NULL, NULL, 0, NULL),
(176, NULL, 'Z80 #7', NULL, NULL, 'EQ512', NULL, NULL, 0, NULL),
(177, NULL, 'Z80 #8', NULL, NULL, 'EQ513', NULL, NULL, 0, NULL),
(178, NULL, 'Z80 #9', NULL, NULL, 'EQ514', NULL, NULL, 0, NULL),
(179, NULL, 'Z80 #11', NULL, NULL, 'EQ516', NULL, NULL, 0, NULL),
(180, NULL, 'Z80 #13', NULL, NULL, 'EQ518', NULL, NULL, 0, NULL),
(181, NULL, 'Z80 #14', NULL, NULL, 'EQ519', NULL, NULL, 0, NULL),
(182, NULL, 'Z80 #16', NULL, NULL, 'EQ521', NULL, NULL, 0, NULL),
(183, NULL, 'Zoom70#1', NULL, NULL, 'EQ528', NULL, NULL, 0, NULL),
(184, NULL, 'Zoom70#3', NULL, NULL, 'EQ530', NULL, NULL, 0, NULL),
(185, NULL, 'Zoom70#5', NULL, NULL, 'EQ532', NULL, NULL, 0, NULL),
(186, NULL, 'Zoom70#6', NULL, NULL, 'EQ533', NULL, NULL, 0, NULL),
(187, NULL, 'Zoom70#7', NULL, NULL, 'EQ534', NULL, NULL, 0, NULL),
(188, NULL, 'Espino Lot in Makiling', NULL, NULL, 'ESPINO', NULL, NULL, 1, NULL),
(189, NULL, 'Ext. Lowbed Trailer #1 (G\'hof)', NULL, NULL, 'EXTLB#1', NULL, NULL, 1, NULL),
(190, NULL, 'Extendable SemiLowbed#1 (GHof)', NULL, NULL, 'EXTSL#1', NULL, NULL, 1, NULL),
(191, NULL, 'Extendable SemiLowbed#2 (GHof)', NULL, NULL, 'EXTSL#2', NULL, NULL, 1, NULL),
(192, NULL, 'Extendable SemiLowbed#3 (Faym)', NULL, NULL, 'EXTSL#3', NULL, NULL, 1, NULL),
(193, NULL, 'Extendable SemiLowbed#4 (Bros)', NULL, NULL, 'EXTSL#4', NULL, NULL, 1, NULL),
(194, NULL, 'Ext SemiLowbed#5 (GH Stepstar)', NULL, NULL, 'EXTSL#5', NULL, NULL, 1, NULL),
(195, NULL, 'Ext SemiLowbed#6 (GH Stepstar)', NULL, NULL, 'EXTSL#6', NULL, NULL, 1, NULL),
(196, NULL, 'FL - 2.5 Tons', NULL, NULL, 'FD 2.5', NULL, NULL, 1, NULL),
(197, NULL, 'TCM Forklift 10 Tons #1', NULL, NULL, 'FD100#1', NULL, NULL, 1, NULL),
(198, NULL, 'TCM Forklift 10 Tons #2', NULL, NULL, 'FD100#2', NULL, NULL, 1, NULL),
(199, NULL, 'TCM Forklift 10 Tons #3', NULL, NULL, 'FD100#3', NULL, NULL, 1, NULL),
(200, NULL, 'FL-Komatsu FD11.5-5', NULL, NULL, 'FD11.5-5', NULL, NULL, 1, NULL),
(201, NULL, 'FHD 150ZS #1', NULL, NULL, 'FD1500#1', NULL, NULL, 1, NULL),
(202, NULL, 'Socma FL 16T #1', NULL, NULL, 'FD160T#1', NULL, NULL, 1, NULL),
(203, NULL, 'Socma FL 16T #2', NULL, NULL, 'FD160T#2', NULL, NULL, 1, NULL),
(204, NULL, 'FD1800#1', NULL, NULL, 'FD1800#1', NULL, NULL, 1, NULL),
(205, NULL, 'FD1800#2', NULL, NULL, 'FD1800#2', NULL, NULL, 0, NULL),
(206, NULL, 'Hifoune 2.5T FL #1', NULL, NULL, 'FD25 #1', NULL, NULL, 1, NULL),
(207, NULL, 'Hifoune 2.5T FL #2', NULL, NULL, 'FD25 #2', NULL, NULL, 1, NULL),
(208, NULL, 'FL TCM 28 tons', NULL, NULL, 'FD280 Z5', NULL, NULL, 1, NULL),
(209, NULL, 'FL-TCM FD35 #5', NULL, NULL, 'FD35 #5', NULL, NULL, 1, NULL),
(210, NULL, 'FL-FD35 #6', NULL, NULL, 'FD35 #6', NULL, NULL, 1, NULL),
(211, NULL, 'Socma FL 35T #1', NULL, NULL, 'FD350T#1', NULL, NULL, 1, NULL),
(212, NULL, 'TCM Forklift 5 Tons', NULL, NULL, 'FD50', NULL, NULL, 1, NULL),
(213, NULL, 'Hifoune 5T FL Small #1', NULL, NULL, 'FD50S #1', NULL, NULL, 1, NULL),
(214, NULL, 'Hifoune 5T FL Small #2', NULL, NULL, 'FD50S #2', NULL, NULL, 1, NULL),
(215, NULL, 'XCMG 5T FL Small #1', NULL, NULL, 'FD50S #3', NULL, NULL, 1, NULL),
(216, NULL, 'FL-FD60 #3', NULL, NULL, 'FD60 #3', NULL, NULL, 1, NULL),
(217, NULL, 'FL-FD60 #4', NULL, NULL, 'FD60 #4', NULL, NULL, 1, NULL),
(218, NULL, 'FL-FD60 #5', NULL, NULL, 'FD60 #5', NULL, NULL, 1, NULL),
(219, NULL, 'FL-FD60 #6', NULL, NULL, 'FD60 #6', NULL, NULL, 1, NULL),
(220, NULL, 'Hifoune 7T FL #1', NULL, NULL, 'FD70 #1', NULL, NULL, 1, NULL),
(221, NULL, 'Hifoune 7T FL #2', NULL, NULL, 'FD70 #2', NULL, NULL, 1, NULL),
(222, NULL, 'TCM Forklift 7 Tons', NULL, NULL, 'FD70Z7', NULL, NULL, 1, NULL),
(223, NULL, 'FD80 Z8', NULL, NULL, 'FD80 #1', NULL, NULL, 1, NULL),
(224, NULL, 'FESTIVAL LOT', NULL, NULL, 'FESTIVAL', NULL, NULL, 1, NULL),
(225, NULL, 'FL-FHD 120Z', NULL, NULL, 'FHD 120Z', NULL, NULL, 1, NULL),
(226, NULL, 'Fuel Tanker (Hubei Simu) #1', NULL, NULL, 'FT #1', NULL, NULL, 1, NULL),
(227, NULL, 'Fuel Tanker (Hubei Simu) #2', NULL, NULL, 'FT #2', NULL, NULL, 1, NULL),
(228, NULL, 'Fuel Tanker (Hubei Simu) #3', NULL, NULL, 'FT #3', NULL, NULL, 1, NULL),
(229, NULL, 'FL-TCM FVD180 #1', NULL, NULL, 'FVD180#1', NULL, NULL, 1, NULL),
(230, NULL, 'FVD60Z#1', NULL, NULL, 'FVD60Z#1', NULL, NULL, 1, NULL),
(231, NULL, '3 Tons Forward Truck #1', NULL, NULL, 'FWD#1', NULL, NULL, 1, NULL),
(232, NULL, 'FL-Lancer Boss G2512 25 Tons', NULL, NULL, 'G2512', NULL, NULL, 0, NULL),
(233, NULL, 'GMK-5130 #2', NULL, NULL, 'G-5130#2', NULL, NULL, 1, NULL),
(234, NULL, 'GMK 5130 #3', NULL, NULL, 'G-5130#3', NULL, NULL, 1, NULL),
(235, NULL, 'GMK-5130 #4', NULL, NULL, 'G-5130#4', NULL, NULL, 1, NULL),
(236, NULL, 'GMK-5130 #5', NULL, NULL, 'G-5130#5', NULL, NULL, 1, NULL),
(237, NULL, 'GMK 5220 #2', NULL, NULL, 'G-5220#2', NULL, NULL, 1, NULL),
(238, NULL, 'GMK-5220 #3', NULL, NULL, 'G-5220#3', NULL, NULL, 1, NULL),
(239, NULL, 'Forklift 3 tons', NULL, NULL, 'GE 204', NULL, NULL, 0, NULL),
(240, NULL, 'TM Telescopic CR 8T', NULL, NULL, 'GE001', NULL, NULL, 1, NULL),
(241, NULL, 'TM Telescopic CR 30 T', NULL, NULL, 'GE002', NULL, NULL, 1, NULL),
(242, NULL, 'TM Telescopic CR 70 T', NULL, NULL, 'GE003', NULL, NULL, 1, NULL),
(243, NULL, 'TM Telescopic CR 80 T', NULL, NULL, 'GE004', NULL, NULL, 1, NULL),
(244, NULL, 'AT Telescopic CR 100 T', NULL, NULL, 'GE005', NULL, NULL, 1, NULL),
(245, NULL, 'AT Telescopic CR 130 T', NULL, NULL, 'GE006', NULL, NULL, 1, NULL),
(246, NULL, 'AT Telescopic CR 150 T', NULL, NULL, 'GE007', NULL, NULL, 1, NULL),
(247, NULL, 'AT Telescopic CR 150T LONGBOOM', NULL, NULL, 'GE008', NULL, NULL, 1, NULL),
(248, NULL, 'AT Telescopic CR 160 T', NULL, NULL, 'GE009', NULL, NULL, 1, NULL),
(249, NULL, 'AT Telescopic CR 170 T', NULL, NULL, 'GE010', NULL, NULL, 1, NULL),
(250, NULL, 'AT Telescopic CR 180 T', NULL, NULL, 'GE011', NULL, NULL, 1, NULL),
(251, NULL, 'AT Telescopic CR 200 T', NULL, NULL, 'GE012', NULL, NULL, 1, NULL),
(252, NULL, 'AT Telescopic CR 220 T', NULL, NULL, 'GE013', NULL, NULL, 1, NULL),
(253, NULL, 'AT Telescopic CR 300 T', NULL, NULL, 'GE014', NULL, NULL, 1, NULL),
(254, NULL, 'AT Telescopic CR 300T LONGBOOM', NULL, NULL, 'GE015', NULL, NULL, 1, NULL),
(255, NULL, 'AT Telescopic CR 500 T', NULL, NULL, 'GE016', NULL, NULL, 1, NULL),
(256, NULL, 'AT Telescopic CR 700 T', NULL, NULL, 'GE017', NULL, NULL, 1, NULL),
(257, NULL, 'Crawler CR Lattice Boom 55 T', NULL, NULL, 'GE018', NULL, NULL, 1, NULL),
(258, NULL, 'Crawler CR Lattice Boom 85 T', NULL, NULL, 'GE019', NULL, NULL, 1, NULL),
(259, NULL, 'Crawler CR Lattice Boom 130 T', NULL, NULL, 'GE020', NULL, NULL, 1, NULL),
(260, NULL, 'Crawler CR Lattice Boom 180 T', NULL, NULL, 'GE021', NULL, NULL, 1, NULL),
(261, NULL, 'Crawler CR Lattice Boom 300 T', NULL, NULL, 'GE022', NULL, NULL, 1, NULL),
(262, NULL, 'Crawler CR Lattice Boom 600 T', NULL, NULL, 'GE023', NULL, NULL, 1, NULL),
(263, NULL, 'Crawler CR Lattice Boom 280 T', NULL, NULL, 'GE024', NULL, NULL, 1, NULL),
(264, NULL, 'Crawler CR Lattice Boom 230 T', NULL, NULL, 'GE025', NULL, NULL, 1, NULL),
(265, NULL, 'RT Crane 8 tons', NULL, NULL, 'GE026', NULL, NULL, 1, NULL),
(266, NULL, 'RT Telescopic CR 25 T', NULL, NULL, 'GE027', NULL, NULL, 1, NULL),
(267, NULL, 'RT Telescopic CR 35 T', NULL, NULL, 'GE028', NULL, NULL, 1, NULL),
(268, NULL, 'RT Telescopic CR 45 T', NULL, NULL, 'GE029', NULL, NULL, 1, NULL),
(269, NULL, 'RT Telescopic CR 30 T', NULL, NULL, 'GE030', NULL, NULL, 1, NULL),
(270, NULL, 'RT Telescopic CR 55 T', NULL, NULL, 'GE031', NULL, NULL, 1, NULL),
(271, NULL, 'RT Telescopic CR 60 T', NULL, NULL, 'GE032', NULL, NULL, 1, NULL),
(272, NULL, 'RT Telescopic CR 70 T', NULL, NULL, 'GE033', NULL, NULL, 1, NULL),
(273, NULL, 'RT Telescopic CR 80 T', NULL, NULL, 'GE034', NULL, NULL, 1, NULL),
(274, NULL, 'RT Telescopic CR 90 T', NULL, NULL, 'GE035', NULL, NULL, 1, NULL),
(275, NULL, 'RT Telescopic CR 145 T', NULL, NULL, 'GE036', NULL, NULL, 1, NULL),
(276, NULL, 'Forklift 2.5 tons', NULL, NULL, 'GE037', NULL, NULL, 1, NULL),
(277, NULL, 'Forklift 3.5 tons', NULL, NULL, 'GE038', NULL, NULL, 1, NULL),
(278, NULL, 'Forklift 5 tons', NULL, NULL, 'GE039', NULL, NULL, 1, NULL),
(279, NULL, 'Forklift 6 tons', NULL, NULL, 'GE040', NULL, NULL, 1, NULL),
(280, NULL, 'Forklift 7 tons', NULL, NULL, 'GE041', NULL, NULL, 1, NULL),
(281, NULL, 'Forklift 7.5 tons', NULL, NULL, 'GE042', NULL, NULL, 1, NULL),
(282, NULL, 'Forklift 8 tons', NULL, NULL, 'GE043', NULL, NULL, 1, NULL),
(283, NULL, 'Forklift 10 tons', NULL, NULL, 'GE044', NULL, NULL, 1, NULL),
(284, NULL, 'Forklift 11.5 tons', NULL, NULL, 'GE045', NULL, NULL, 1, NULL),
(285, NULL, 'Forklift 12 tons', NULL, NULL, 'GE046', NULL, NULL, 1, NULL),
(286, NULL, 'Forklift 15 tons', NULL, NULL, 'GE047', NULL, NULL, 1, NULL),
(287, NULL, 'Forklift 18 tons', NULL, NULL, 'GE048', NULL, NULL, 1, NULL),
(288, NULL, 'Forklift 25 tons', NULL, NULL, 'GE049', NULL, NULL, 1, NULL),
(289, NULL, 'Forklift 28 tons', NULL, NULL, 'GE050', NULL, NULL, 1, NULL),
(290, NULL, 'Telehandler 3 tons', NULL, NULL, 'GE051', NULL, NULL, 1, NULL),
(291, NULL, 'Telehandler 4.5 tons', NULL, NULL, 'GE052', NULL, NULL, 1, NULL),
(292, NULL, 'Telehandler 4 tons', NULL, NULL, 'GE053', NULL, NULL, 1, NULL),
(293, NULL, 'Telehandler 5 tons', NULL, NULL, 'GE054', NULL, NULL, 1, NULL),
(294, NULL, 'Manlift Articulated Boom 12.9', NULL, NULL, 'GE055', NULL, NULL, 1, NULL),
(295, NULL, 'Manlift Straight Boom 12 m', NULL, NULL, 'GE056', NULL, NULL, 1, NULL),
(296, NULL, 'Manlift Straight Boom 15 m', NULL, NULL, 'GE057', NULL, NULL, 1, NULL),
(297, NULL, 'Manlift Articulated Boom 18 m', NULL, NULL, 'GE058', NULL, NULL, 1, NULL),
(298, NULL, 'Manlift Straight Boom 19 m', NULL, NULL, 'GE059', NULL, NULL, 1, NULL),
(299, NULL, 'Manlift Straight Boom 21 m', NULL, NULL, 'GE060', NULL, NULL, 1, NULL),
(300, NULL, 'Manlift Articulated Boom 24 m', NULL, NULL, 'GE061', NULL, NULL, 1, NULL),
(301, NULL, 'Manlift Straight Boom 25 m', NULL, NULL, 'GE062', NULL, NULL, 1, NULL),
(302, NULL, 'Manlift Straight Boom 26 m', NULL, NULL, 'GE063', NULL, NULL, 1, NULL),
(303, NULL, 'Manlift Articulated Boom 25 m', NULL, NULL, 'GE064', NULL, NULL, 1, NULL),
(304, NULL, 'Manlift Straight Boom 30 m', NULL, NULL, 'GE065', NULL, NULL, 1, NULL),
(305, NULL, 'Manlift Straight Boom 36 m', NULL, NULL, 'GE066', NULL, NULL, 1, NULL),
(306, NULL, 'Manlift Articulated Boom 38 m', NULL, NULL, 'GE067', NULL, NULL, 1, NULL),
(307, NULL, 'Scissor Lift 7.92 meters', NULL, NULL, 'GE068', NULL, NULL, 1, NULL),
(308, NULL, 'Scissor Lift 9.68 meters', NULL, NULL, 'GE069', NULL, NULL, 1, NULL),
(309, NULL, 'Scissor Lift 12 meters', NULL, NULL, 'GE070', NULL, NULL, 1, NULL),
(310, NULL, 'Scissor Lift 14 meters', NULL, NULL, 'GE071', NULL, NULL, 1, NULL),
(311, NULL, 'ScissorLift 16m RT,Dsl Driven', NULL, NULL, 'GE072', NULL, NULL, 1, NULL),
(312, NULL, 'Truck Mounted Manlift 9 meters', NULL, NULL, 'GE073', NULL, NULL, 1, NULL),
(313, NULL, 'Backhoe 0.7 cubic meter bucket', NULL, NULL, 'GE074', NULL, NULL, 1, NULL),
(314, NULL, 'Backhoe 0.9 cubic meter bucket', NULL, NULL, 'GE075', NULL, NULL, 1, NULL),
(315, NULL, 'Backhoe 0.8 cubic meter bucket', NULL, NULL, 'GE076', NULL, NULL, 1, NULL),
(316, NULL, 'Backhoe 0.6 cu m Long Arm', NULL, NULL, 'GE077', NULL, NULL, 1, NULL),
(317, NULL, '\"Wheel Loader 5T, 3.6 cu. M\"', NULL, NULL, 'GE078', NULL, NULL, 1, NULL),
(318, NULL, 'Road Roller', NULL, NULL, 'GE079', NULL, NULL, 1, NULL),
(319, NULL, 'Grader  170HP', NULL, NULL, 'GE080', NULL, NULL, 1, NULL),
(320, NULL, 'Bulldozer Str8tilt Blade 4.5m3', NULL, NULL, 'GE081', NULL, NULL, 1, NULL),
(321, NULL, 'Welding Machine 400 Amperes', NULL, NULL, 'GE082', NULL, NULL, 1, NULL),
(322, NULL, 'Boomtruck 16 tons', NULL, NULL, 'GE083', NULL, NULL, 1, NULL),
(323, NULL, '6 wheeler Truck', NULL, NULL, 'GE084', NULL, NULL, 1, NULL),
(324, NULL, '10 wheeler Truck', NULL, NULL, 'GE085', NULL, NULL, 1, NULL),
(325, NULL, '12 wheeler Truck', NULL, NULL, 'GE086', NULL, NULL, 1, NULL),
(326, NULL, 'Army Tractor Head', NULL, NULL, 'GE087', NULL, NULL, 1, NULL),
(327, NULL, 'Tractor Head', NULL, NULL, 'GE088', NULL, NULL, 1, NULL),
(328, NULL, 'Low Bed Trailer', NULL, NULL, 'GE089', NULL, NULL, 1, NULL),
(329, NULL, 'Extendable Low Bed Trailer', NULL, NULL, 'GE090', NULL, NULL, 1, NULL),
(330, NULL, 'High Bed Trailer', NULL, NULL, 'GE091', NULL, NULL, 1, NULL),
(331, NULL, 'Multi Axle 4 Lines', NULL, NULL, 'GE092', NULL, NULL, 0, NULL),
(332, NULL, 'Multi Axle 6 Lines', NULL, NULL, 'GE093', NULL, NULL, 0, NULL),
(333, NULL, 'Extendable Semi-LowBed Trailer', NULL, NULL, 'GE094', NULL, NULL, 1, NULL),
(334, NULL, 'Ballast Trailer', NULL, NULL, 'GE095', NULL, NULL, 1, NULL),
(335, NULL, 'Dumptruck', NULL, NULL, 'GE096', NULL, NULL, 1, NULL),
(336, NULL, 'Skid Loader/Bobcat 0.4 cu m', NULL, NULL, 'GE097', NULL, NULL, 1, NULL),
(337, NULL, 'Generator Set 75 kva', NULL, NULL, 'GE098', NULL, NULL, 1, NULL),
(338, NULL, 'Generator Set 150 kva', NULL, NULL, 'GE099', NULL, NULL, 1, NULL),
(339, NULL, 'Generator Set 220 kva', NULL, NULL, 'GE100', NULL, NULL, 1, NULL),
(340, NULL, 'Generator Set 400 kva', NULL, NULL, 'GE101', NULL, NULL, 1, NULL),
(341, NULL, 'Tower Light 5 kva', NULL, NULL, 'GE102', NULL, NULL, 1, NULL),
(342, NULL, 'Fuel Tanker', NULL, NULL, 'GE103', NULL, NULL, 1, NULL),
(343, NULL, 'Vessel Bridge Trailer', NULL, NULL, 'GE104', NULL, NULL, 1, NULL),
(344, NULL, 'Vsl Bridge Trailer #1 (Faymon)', NULL, NULL, 'GE105', NULL, NULL, 0, NULL),
(345, NULL, 'Drop Deck UT', NULL, NULL, 'GE106', NULL, NULL, 1, NULL),
(346, NULL, 'Drop Deck Trailer #1 (Faymon)', NULL, NULL, 'GE107', NULL, NULL, 0, NULL),
(347, NULL, 'Self-Propelled Trailer 6-Lines', NULL, NULL, 'GE108', NULL, NULL, 0, NULL),
(348, NULL, 'Self-Prop Trailer 6L (Gold)#1', NULL, NULL, 'GE109', NULL, NULL, 0, NULL),
(349, NULL, 'Self-Prop Trailer 6L (Gold) #2', NULL, NULL, 'GE110', NULL, NULL, 0, NULL),
(350, NULL, 'Rough Terrain Crane 50 tons', NULL, NULL, 'GE111', NULL, NULL, 1, NULL),
(351, NULL, 'TM Telescopic CR 50 T', NULL, NULL, 'GE112', NULL, NULL, 1, NULL),
(352, NULL, 'TM Telescopic CR 25 T', NULL, NULL, 'GE113', NULL, NULL, 1, NULL),
(353, NULL, 'AT Telescopic CR 120T', NULL, NULL, 'GE114', NULL, NULL, 1, NULL),
(354, NULL, 'TM Telescopic CR 35T', NULL, NULL, 'GE115', NULL, NULL, 1, NULL),
(355, NULL, 'Crawler CR Lattice Boom 150T', NULL, NULL, 'GE116', NULL, NULL, 1, NULL),
(356, NULL, 'TM Telescopic CR 55T', NULL, NULL, 'GE117', NULL, NULL, 1, NULL),
(357, NULL, 'Crawler CR (Mini Spider), 3T', NULL, NULL, 'GE118', NULL, NULL, 1, NULL),
(358, NULL, 'Crawler CR Lattice Boom 80T', NULL, NULL, 'GE119', NULL, NULL, 1, NULL),
(359, NULL, 'Scissor Lift 18 meters', NULL, NULL, 'GE120', NULL, NULL, 1, NULL),
(360, NULL, 'Manlift Articulated Boom 20m', NULL, NULL, 'GE121', NULL, NULL, 1, NULL),
(361, NULL, 'Scissor Lift 10 meters', NULL, NULL, 'GE122', NULL, NULL, 1, NULL),
(362, NULL, 'TM Telescopic CR 45T', NULL, NULL, 'GE123', NULL, NULL, 1, NULL),
(363, NULL, 'Multi Axle Trailer 10 Lines', NULL, NULL, 'GE124', NULL, NULL, 0, NULL),
(364, NULL, 'Forklift 20 tons', NULL, NULL, 'GE125', NULL, NULL, 1, NULL),
(365, NULL, 'Generator Set 62.5 kva', NULL, NULL, 'GE126', NULL, NULL, 1, NULL),
(366, NULL, 'Self-Propelled Trailer 10Lines', NULL, NULL, 'GE127', NULL, NULL, 1, NULL),
(367, NULL, 'Stool Support', NULL, NULL, 'GE128', NULL, NULL, 1, NULL),
(368, NULL, 'Beams', NULL, NULL, 'GE129', NULL, NULL, 1, NULL),
(369, NULL, 'Lashing Equipment', NULL, NULL, 'GE130', NULL, NULL, 1, NULL),
(370, NULL, 'Boomtruck 10 tons', NULL, NULL, 'GE131', NULL, NULL, 1, NULL),
(371, NULL, 'Spreader Bar', NULL, NULL, 'GE132', NULL, NULL, 1, NULL),
(372, NULL, 'Multi Axle 8 Lines', NULL, NULL, 'GE133', NULL, NULL, 0, NULL),
(373, NULL, 'Multi Axle 12 Lines', NULL, NULL, 'GE134', NULL, NULL, 0, NULL),
(374, NULL, 'Crawler CR Telescopic Boom120T', NULL, NULL, 'GE135', NULL, NULL, 1, NULL),
(375, NULL, 'Crawler CR Telescopic Boom 55T', NULL, NULL, 'GE136', NULL, NULL, 1, NULL),
(376, NULL, 'AT Telescopic CR 125T', NULL, NULL, 'GE137', NULL, NULL, 1, NULL),
(377, NULL, 'AT Telescopic CR 250T', NULL, NULL, 'GE138', NULL, NULL, 1, NULL),
(378, NULL, 'Boomtruck 6 tons', NULL, NULL, 'GE139', NULL, NULL, 1, NULL),
(379, NULL, 'RT Telescopic CR 65 T', NULL, NULL, 'GE140', NULL, NULL, 1, NULL),
(380, NULL, 'Manlift Articulated Boom 14m', NULL, NULL, 'GE141', NULL, NULL, 1, NULL),
(381, NULL, 'RT Telescopic CR 120 T', NULL, NULL, 'GE142', NULL, NULL, 1, NULL),
(382, NULL, 'TM Telescopic CR 75 T', NULL, NULL, 'GE143', NULL, NULL, 1, NULL),
(383, NULL, 'Manlift Articulated Boom 32 m', NULL, NULL, 'GE144', NULL, NULL, 1, NULL),
(384, NULL, 'Crawler CR Lattice Boom 50T', NULL, NULL, 'GE145', NULL, NULL, 1, NULL),
(385, NULL, 'Self-Propelled Trailer 12Lines', NULL, NULL, 'GE146', NULL, NULL, 0, NULL),
(386, NULL, 'Self-Propelled Trailer 16Lines', NULL, NULL, 'GE147', NULL, NULL, 0, NULL),
(387, NULL, 'Self-Propelled Trailer 8 Lines', NULL, NULL, 'GE148', NULL, NULL, 0, NULL),
(388, NULL, 'Self-Propelled Trailer 9 Lines', NULL, NULL, 'GE149', NULL, NULL, 0, NULL),
(389, NULL, 'Counterweight', NULL, NULL, 'GE150', NULL, NULL, 1, NULL),
(390, NULL, 'Manlift Articulated Boom 26 m', NULL, NULL, 'GE151', NULL, NULL, 1, NULL),
(391, NULL, 'Tools - Jacking & Skidding', NULL, NULL, 'GE152', NULL, NULL, 1, NULL),
(392, NULL, 'Generator Set 60 kva', NULL, NULL, 'GE153', NULL, NULL, 1, NULL),
(393, NULL, 'Crawler CR Lattice Boom 800T', NULL, NULL, 'GE154', NULL, NULL, 1, NULL),
(394, NULL, 'PST 48 Lines', NULL, NULL, 'GE155', NULL, NULL, 0, NULL),
(395, NULL, 'Manlift Articulated Boom 10 m', NULL, NULL, 'GE156', NULL, NULL, 1, NULL),
(396, NULL, 'Manlift Straight Boom 10 m', NULL, NULL, 'GE157', NULL, NULL, 1, NULL),
(397, NULL, 'RT Telescopic CR 75 T', NULL, NULL, 'GE158', NULL, NULL, 1, NULL),
(398, NULL, 'Crawler CR Lattice Boom 100 T', NULL, NULL, 'GE159', NULL, NULL, 1, NULL),
(399, NULL, 'Crawler CR Lattice Boom 250 T', NULL, NULL, 'GE160', NULL, NULL, 1, NULL),
(400, NULL, 'Generator Set 250 kva', NULL, NULL, 'GE161', NULL, NULL, 1, NULL),
(401, NULL, 'Multi-Axle Trailer', NULL, NULL, 'GE162', NULL, NULL, 1, NULL),
(402, NULL, 'Roller', NULL, NULL, 'GE163', NULL, NULL, 1, NULL),
(403, NULL, 'AT Telescopic 450 T', NULL, NULL, 'GE164', NULL, NULL, 1, NULL),
(404, NULL, 'Crawler CR Lattice Boom 650 T', NULL, NULL, 'GE165', NULL, NULL, 1, NULL),
(405, NULL, 'Manlift Articulated Boom 36 m', NULL, NULL, 'GE166', NULL, NULL, 1, NULL),
(406, NULL, 'Spine Deck', NULL, NULL, 'GE167', NULL, NULL, 1, NULL),
(407, NULL, 'Goose Neck', NULL, NULL, 'GE168', NULL, NULL, 1, NULL),
(408, NULL, 'Turntable', NULL, NULL, 'GE169', NULL, NULL, 1, NULL),
(409, NULL, 'SPT', NULL, NULL, 'GE170', NULL, NULL, 1, NULL),
(410, NULL, 'SPMT', NULL, NULL, 'GE171', NULL, NULL, 1, NULL),
(411, NULL, 'SPMT Side-by-side', NULL, NULL, 'GE172', NULL, NULL, 1, NULL),
(412, NULL, 'Multi-axle Trailer with Split', NULL, NULL, 'GE173', NULL, NULL, 1, NULL),
(413, NULL, 'Vessel Bridge', NULL, NULL, 'GE174', NULL, NULL, 1, NULL),
(414, NULL, 'Vessel Bridge - Telescopic', NULL, NULL, 'GE175', NULL, NULL, 1, NULL),
(415, NULL, 'Drop Deck', NULL, NULL, 'GE176', NULL, NULL, 1, NULL),
(416, NULL, 'Tower Light 11.2 kva', NULL, NULL, 'GE177', NULL, NULL, 1, NULL),
(417, NULL, 'Multi-axle Trailer UT', NULL, NULL, 'GE178', NULL, NULL, 1, NULL),
(418, NULL, 'Backhoe 1.2 cu. meter bucket', NULL, NULL, 'GE179', NULL, NULL, 1, NULL),
(419, NULL, 'Crawler CR Telescopic Boom 90T', NULL, NULL, 'GE180', NULL, NULL, 1, NULL),
(420, NULL, 'Generator Set 750 kva', NULL, NULL, 'GE181', NULL, NULL, 1, NULL),
(421, NULL, 'Tower Light 2.2KVA', NULL, NULL, 'GE182', NULL, NULL, 1, NULL),
(422, NULL, 'Semi Lowbed Trailer', NULL, NULL, 'GE183', NULL, NULL, 1, NULL),
(423, NULL, 'Crawler CR Telescopic Boom150T', NULL, NULL, 'GE184', NULL, NULL, 1, NULL),
(424, NULL, 'Forklift 4 tons', NULL, NULL, 'GE185', NULL, NULL, 1, NULL),
(425, NULL, 'Manlift Articulated Boom 41 m', NULL, NULL, 'GE186', NULL, NULL, 1, NULL),
(426, NULL, 'Crawler CR Lattice Boom 750T', NULL, NULL, 'GE187', NULL, NULL, 1, NULL),
(427, NULL, 'Manlift Articulated Boom 16 m', NULL, NULL, 'GE188', NULL, NULL, 1, NULL),
(428, NULL, 'TM Telescopic CR 60T', NULL, NULL, 'GE189', NULL, NULL, 1, NULL),
(429, NULL, 'Generator Set 300 kva', NULL, NULL, 'GE190', NULL, NULL, 1, NULL),
(430, NULL, 'Boomtruck 15 tons', NULL, NULL, 'GE191', NULL, NULL, 1, NULL),
(431, NULL, 'AT Telescopic CR 600 T', NULL, NULL, 'GE192', NULL, NULL, 1, NULL),
(432, NULL, 'AT Telescopic CR 800T', NULL, NULL, 'GE193', NULL, NULL, 1, NULL),
(433, NULL, 'Manlift Straight Boom 27 m', NULL, NULL, 'GE194', NULL, NULL, 1, NULL),
(434, NULL, 'Telehandler 3.5 tons', NULL, NULL, 'GE195', NULL, NULL, 1, NULL),
(435, NULL, 'Crawler CR Lattice Boom 1250T', NULL, NULL, 'GE196', NULL, NULL, 1, NULL),
(436, NULL, 'Forklift 16 tons', NULL, NULL, 'GE197', NULL, NULL, 1, NULL),
(437, NULL, 'AT Telescopic CR 260 T', NULL, NULL, 'GE199', NULL, NULL, 1, NULL),
(438, NULL, 'Generator Set 200 kva', NULL, NULL, 'GE200', NULL, NULL, 1, NULL),
(439, NULL, 'Manlift Articulated Boom 15m', NULL, NULL, 'GE201', NULL, NULL, 1, NULL),
(440, NULL, 'Boomtruck 5 tons', NULL, NULL, 'GE202', NULL, NULL, 1, NULL),
(441, NULL, 'Backhoe 1 cu. meter bucket', NULL, NULL, 'GE203', NULL, NULL, 1, NULL),
(442, NULL, 'Blade Carrier', NULL, NULL, 'GE204', NULL, NULL, 1, NULL),
(443, NULL, 'Blade Lifter', NULL, NULL, 'GE205', NULL, NULL, 1, NULL),
(444, NULL, 'Forklift 3 tons', NULL, NULL, 'GE206', NULL, NULL, 1, NULL),
(445, NULL, 'AT Telescopic CR 400T', NULL, NULL, 'GE207', NULL, NULL, 1, NULL),
(446, NULL, 'Manlift Articulated Boom 45m', NULL, NULL, 'GE208', NULL, NULL, 1, NULL),
(447, NULL, 'Knuckle Boom 90T', NULL, NULL, 'GE209', NULL, NULL, 1, NULL),
(448, NULL, 'Forklift 35 tons', NULL, NULL, 'GE210', NULL, NULL, 1, NULL),
(449, NULL, 'Crawler CR Lattice Boom 350T', NULL, NULL, 'GE211', NULL, NULL, 1, NULL),
(450, NULL, 'Genie Manlift Z60/34 #1', NULL, NULL, 'GENIE #1', NULL, NULL, 0, NULL),
(451, NULL, 'Genie Manlift Z60/34 #2', NULL, NULL, 'GENIE #2', NULL, NULL, 0, NULL),
(452, NULL, 'Genie Manlift Z60/34 #3', NULL, NULL, 'GENIE #3', NULL, NULL, 0, NULL),
(453, NULL, 'Genie Manlift Z60/34 #4', NULL, NULL, 'GENIE #4', NULL, NULL, 0, NULL),
(454, NULL, 'Genie Manlift Z60/34 #5', NULL, NULL, 'GENIE #5', NULL, NULL, 0, NULL),
(455, NULL, 'Genie Manlift Z60/34 #6', NULL, NULL, 'GENIE #6', NULL, NULL, 0, NULL),
(456, NULL, 'Grove GMK 5100 #2 - 100 Tons', NULL, NULL, 'GMK-51#2', NULL, NULL, 1, NULL),
(457, NULL, 'Grove GMK 5100#1 - 100 Tons', NULL, NULL, 'GMK-5100', NULL, NULL, 1, NULL),
(458, NULL, 'GMK-5130 #1', NULL, NULL, 'GMK-5130', NULL, NULL, 1, NULL),
(459, NULL, 'GMK-5170 (170 tons)', NULL, NULL, 'GMK5170', NULL, NULL, 1, NULL),
(460, NULL, 'GMK-5220 (220 tons)', NULL, NULL, 'GMK5220', NULL, NULL, 1, NULL),
(461, NULL, 'GMK-6180', NULL, NULL, 'GMK-6180', NULL, NULL, 1, NULL),
(462, NULL, 'GMK 6300L #2', NULL, NULL, 'GMK63 #2', NULL, NULL, 1, NULL),
(463, NULL, 'GMK 6300L #3', NULL, NULL, 'GMK63 #3', NULL, NULL, 1, NULL),
(464, NULL, 'GMK 6300L #4', NULL, NULL, 'GMK63 #4', NULL, NULL, 1, NULL),
(465, NULL, 'GMK 6300L #5', NULL, NULL, 'GMK63 #5', NULL, NULL, 1, NULL),
(466, NULL, 'GMK 6300L #6', NULL, NULL, 'GMK63 #6', NULL, NULL, 1, NULL),
(467, NULL, 'GMK 6300L #7', NULL, NULL, 'GMK63 #7', NULL, NULL, 1, NULL),
(468, NULL, 'GMK 6300L #8', NULL, NULL, 'GMK63 #8', NULL, NULL, 1, NULL),
(469, NULL, 'GMK 6300L', NULL, NULL, 'GMK6300L', NULL, NULL, 1, NULL),
(470, NULL, 'Genie Manlift Z60/34 #1', NULL, NULL, 'GNIE #1', NULL, NULL, 1, NULL),
(471, NULL, 'Genie Manlift Z60/34 #2', NULL, NULL, 'GNIE #2', NULL, NULL, 1, NULL),
(472, NULL, 'Genie Manlift Z60/34 #3', NULL, NULL, 'GNIE #3', NULL, NULL, 1, NULL),
(473, NULL, 'Genie Manlift Z60/34 #4', NULL, NULL, 'GNIE #4', NULL, NULL, 1, NULL),
(474, NULL, 'Genie Manlift Z60/34 #5', NULL, NULL, 'GNIE #5', NULL, NULL, 1, NULL),
(475, NULL, 'Genie Manlift Z60/34 #6', NULL, NULL, 'GNIE #6', NULL, NULL, 1, NULL),
(476, NULL, 'PST Goldhofer #1', NULL, NULL, 'GoldH #1', NULL, NULL, 0, NULL),
(477, NULL, 'PST Goldhofer #2', NULL, NULL, 'GoldH #2', NULL, NULL, 1, NULL),
(478, NULL, 'Tadano GR1450-EX #1', NULL, NULL, 'GR1450#1', NULL, NULL, 1, NULL),
(479, NULL, 'XCMG GR 165 Grader #1', NULL, NULL, 'GR165 #1', NULL, NULL, 1, NULL),
(480, NULL, 'Tadano GR800#1', NULL, NULL, 'GR800#1', NULL, NULL, 1, NULL),
(481, NULL, 'Tadano GR800#2', NULL, NULL, 'GR800#2', NULL, NULL, 1, NULL),
(482, NULL, 'Tadano GR800#3', NULL, NULL, 'GR800#3', NULL, NULL, 1, NULL),
(483, NULL, 'Tadano GR800#4', NULL, NULL, 'GR800#4', NULL, NULL, 1, NULL),
(484, NULL, 'Tadano GR800#5', NULL, NULL, 'GR800#5', NULL, NULL, 1, NULL),
(485, NULL, 'Genie GS5390RT #1', NULL, NULL, 'GS5390#1', NULL, NULL, 1, NULL),
(486, NULL, 'Genie GS5390RT #2', NULL, NULL, 'GS5390#2', NULL, NULL, 1, NULL),
(487, NULL, 'Genie GS5390RT #3', NULL, NULL, 'GS5390#3', NULL, NULL, 1, NULL),
(488, NULL, 'Genie GS5390RT #4', NULL, NULL, 'GS5390#4', NULL, NULL, 1, NULL),
(489, NULL, 'XCMG Boom Truck 12T #1', NULL, NULL, 'GSQ300#1', NULL, NULL, 1, NULL),
(490, NULL, 'Tadano GTC1200#1', NULL, NULL, 'GTC120#1', NULL, NULL, 1, NULL),
(491, NULL, 'GTJZ12 12m Scissor Lift #1', NULL, NULL, 'GTJZ12#1', NULL, NULL, 1, NULL),
(492, NULL, 'GTJZ12 12m Scissor Lift #2', NULL, NULL, 'GTJZ12#2', NULL, NULL, 1, NULL),
(493, NULL, 'GTJZ12 12m Scissor Lift #3', NULL, NULL, 'GTJZ12#3', NULL, NULL, 1, NULL),
(494, NULL, 'GTJZ12 12m Scissor Lift #4', NULL, NULL, 'GTJZ12#4', NULL, NULL, 1, NULL),
(495, NULL, 'GTJZ14 14m Scissor Lift #1', NULL, NULL, 'GTJZ14#1', NULL, NULL, 1, NULL),
(496, NULL, 'GTJZ14 14m Scissor Lift #2', NULL, NULL, 'GTJZ14#2', NULL, NULL, 1, NULL),
(497, NULL, 'GTJZ14 14m Scissor Lift #3', NULL, NULL, 'GTJZ14#3', NULL, NULL, 1, NULL),
(498, NULL, 'GTJZ14 14m Scissor Lift #4', NULL, NULL, 'GTJZ14#4', NULL, NULL, 1, NULL),
(499, NULL, 'FL-Hyster H10.00XL #1', NULL, NULL, 'H10.XL#1', NULL, NULL, 1, NULL),
(500, NULL, 'FL-Hyster H10.00XL #2', NULL, NULL, 'H10.XL#2', NULL, NULL, 1, NULL),
(501, NULL, 'FL-Hyster H10.00XL #3', NULL, NULL, 'H10.XL#3', NULL, NULL, 1, NULL),
(502, NULL, 'FL-Hyster H10.00XL #4', NULL, NULL, 'H10.XL#4', NULL, NULL, 1, NULL),
(503, NULL, 'FL-Hyster H7.00XL #1', NULL, NULL, 'H7.0XL#1', NULL, NULL, 1, NULL),
(504, NULL, 'FL-Hyster H7.00XL #2', NULL, NULL, 'H7.0XL#2', NULL, NULL, 1, NULL),
(505, NULL, 'FL-Hyster H7.00XL #3', NULL, NULL, 'H7.0XL#3', NULL, NULL, 1, NULL),
(506, NULL, 'FL-Hyster H7.00XL #4', NULL, NULL, 'H7.0XL#4', NULL, NULL, 1, NULL),
(507, NULL, 'FL-Hyster H8.00XL', NULL, NULL, 'H8.00XL', NULL, NULL, 1, NULL),
(508, NULL, 'HARBOR CENTER (LRI)', NULL, NULL, 'HARBOR', NULL, NULL, 1, NULL),
(509, NULL, 'Hi Bed Trailer 2Axle #1', NULL, NULL, 'HBT2#1', NULL, NULL, 1, NULL),
(510, NULL, 'Hi Bed Trailer 2Axle #2', NULL, NULL, 'HBT2#2', NULL, NULL, 1, NULL),
(511, NULL, 'Hi Bed Trailer 2Axle #3', NULL, NULL, 'HBT2#3', NULL, NULL, 1, NULL),
(512, NULL, 'Hi Bed Trailer 2Axle #4', NULL, NULL, 'HBT2#4', NULL, NULL, 1, NULL),
(513, NULL, 'Hi Bed Trailer 2Axle #5', NULL, NULL, 'HBT2#5', NULL, NULL, 1, NULL),
(514, NULL, 'Hi Bed Trailer 2Axle #6', NULL, NULL, 'HBT2#6', NULL, NULL, 1, NULL),
(515, NULL, 'Hi Bed Trailer 2Axle #7', NULL, NULL, 'HBT2#7', NULL, NULL, 1, NULL),
(516, NULL, 'Hi Bed Trailer 2Axle #8', NULL, NULL, 'HBT2#8', NULL, NULL, 1, NULL),
(517, NULL, 'Hi Bed Trailer 3Axle #1', NULL, NULL, 'HBT3#1', NULL, NULL, 1, NULL),
(518, NULL, 'Hi Bed Trailer 3Axle #10', NULL, NULL, 'HBT3#10', NULL, NULL, 1, NULL),
(519, NULL, 'Hi Bed Trailer 3Axle #11', NULL, NULL, 'HBT3#11', NULL, NULL, 1, NULL),
(520, NULL, 'Hi Bed Trailer 3Axle #12', NULL, NULL, 'HBT3#12', NULL, NULL, 1, NULL),
(521, NULL, 'Hi Bed Trailer 3Axle #13', NULL, NULL, 'HBT3#13', NULL, NULL, 1, NULL),
(522, NULL, 'Hi Bed Trailer 3Axle #14', NULL, NULL, 'HBT3#14', NULL, NULL, 1, NULL),
(523, NULL, 'Hi Bed Trailer 3Axle #15', NULL, NULL, 'HBT3#15', NULL, NULL, 1, NULL),
(524, NULL, 'Hi Bed Trailer 3Axle #16', NULL, NULL, 'HBT3#16', NULL, NULL, 1, NULL),
(525, NULL, 'Hi Bed Trailer 3Axle #17', NULL, NULL, 'HBT3#17', NULL, NULL, 1, NULL),
(526, NULL, 'Hi Bed Trailer 3Axle #18', NULL, NULL, 'HBT3#18', NULL, NULL, 1, NULL),
(527, NULL, 'Hi Bed Trailer 3Axle #19', NULL, NULL, 'HBT3#19', NULL, NULL, 1, NULL),
(528, NULL, 'Hi Bed Trailer 3Axle #2', NULL, NULL, 'HBT3#2', NULL, NULL, 1, NULL),
(529, NULL, 'Hi Bed Trailer 3Axle #20', NULL, NULL, 'HBT3#20', NULL, NULL, 1, NULL),
(530, NULL, 'Hi Bed Trailer 3Axle #21', NULL, NULL, 'HBT3#21', NULL, NULL, 1, NULL),
(531, NULL, 'Hi Bed Trailer 3Axle #22', NULL, NULL, 'HBT3#22', NULL, NULL, 1, NULL),
(532, NULL, 'Hi Bed Trailer 3Axle #23', NULL, NULL, 'HBT3#23', NULL, NULL, 1, NULL),
(533, NULL, 'Hi Bed Trailer 3Axle #24', NULL, NULL, 'HBT3#24', NULL, NULL, 1, NULL),
(534, NULL, 'Hi Bed Trailer 3Axle #25', NULL, NULL, 'HBT3#25', NULL, NULL, 1, NULL),
(535, NULL, 'Hi Bed Trailer 3Axle #26', NULL, NULL, 'HBT3#26', NULL, NULL, 1, NULL),
(536, NULL, 'Hi Bed Trailer 3Axle #27', NULL, NULL, 'HBT3#27', NULL, NULL, 1, NULL),
(537, NULL, 'Hi Bed Trailer 3Axle #28', NULL, NULL, 'HBT3#28', NULL, NULL, 1, NULL),
(538, NULL, 'Hi Bed Trailer 3Axle #29', NULL, NULL, 'HBT3#29', NULL, NULL, 1, NULL),
(539, NULL, 'Hi Bed Trailer 3Axle #3', NULL, NULL, 'HBT3#3', NULL, NULL, 1, NULL),
(540, NULL, 'Hi Bed Trailer 3Axle #30', NULL, NULL, 'HBT3#30', NULL, NULL, 1, NULL),
(541, NULL, 'Hi Bed Trailer 3Axle #31', NULL, NULL, 'HBT3#31', NULL, NULL, 1, NULL),
(542, NULL, 'Hi Bed Trailer 3Axle #4', NULL, NULL, 'HBT3#4', NULL, NULL, 1, NULL),
(543, NULL, 'Hi Bed Trailer 3Axle #5', NULL, NULL, 'HBT3#5', NULL, NULL, 1, NULL),
(544, NULL, 'Hi Bed Trailer 3Axle #6', NULL, NULL, 'HBT3#6', NULL, NULL, 1, NULL),
(545, NULL, 'Hi Bed Trailer 3Axle #7', NULL, NULL, 'HBT3#7', NULL, NULL, 1, NULL),
(546, NULL, 'Hi Bed Trailer 3Axle #8', NULL, NULL, 'HBT3#8', NULL, NULL, 1, NULL),
(547, NULL, 'Hi Bed Trailer 3Axle #9', NULL, NULL, 'HBT3#9', NULL, NULL, 1, NULL),
(548, NULL, 'CTM-Demag HC810 SL', NULL, NULL, 'HC810-SL', NULL, NULL, 1, NULL),
(549, NULL, 'Howo Heavy Haul Tractor 6x6 #1', NULL, NULL, 'HH6x6 01', NULL, NULL, 1, NULL),
(550, NULL, 'Howo Heavy Haul Tractor 6x6 #2', NULL, NULL, 'HH6x6 02', NULL, NULL, 1, NULL),
(551, NULL, 'Howo Heavy Haul Tractor 6x6 #3', NULL, NULL, 'HH6x6 03', NULL, NULL, 1, NULL),
(552, NULL, 'Howo Heavy Haul Tractor 6x6 #4', NULL, NULL, 'HH6x6 04', NULL, NULL, 1, NULL),
(553, NULL, 'Howo Heavy Haul Tractor 6x6 #5', NULL, NULL, 'HH6x6 05', NULL, NULL, 1, NULL),
(554, NULL, 'Howo Heavy Haul Tractor 6x6 #6', NULL, NULL, 'HH6x6 06', NULL, NULL, 1, NULL),
(555, NULL, 'ILOILO HEAVYLIFT', NULL, NULL, 'ILOHEAVY', NULL, NULL, 1, NULL),
(556, NULL, 'Iloilo Yard', NULL, NULL, 'ILOILO', NULL, NULL, 1, NULL),
(557, NULL, 'JLG 1200SJP Boomlift #1', NULL, NULL, 'JL1200#1', NULL, NULL, 1, NULL),
(558, NULL, 'JLG 1200SJP Boomlift #2', NULL, NULL, 'JL1200#2', NULL, NULL, 1, NULL),
(559, NULL, 'JLG 1250 AJP Manlift #2', NULL, NULL, 'JL1250#2', NULL, NULL, 1, NULL),
(560, NULL, 'JLG 1250 AJP Manlift #3', NULL, NULL, 'JL1250#3', NULL, NULL, 1, NULL),
(561, NULL, 'JLG 1250 AJP Manlift #4', NULL, NULL, 'JL1250#4', NULL, NULL, 1, NULL),
(562, NULL, 'JLG 1250 AJP Manlift #5', NULL, NULL, 'JL1250#5', NULL, NULL, 1, NULL),
(563, NULL, 'JLG 1250 AJP Manlift #6', NULL, NULL, 'JL1250#6', NULL, NULL, 1, NULL),
(564, NULL, 'JLG 1250 AJP Manlift #7', NULL, NULL, 'JL1250#7', NULL, NULL, 1, NULL),
(565, NULL, 'JLG 1500 AJP Manlift #1', NULL, NULL, 'JL1500#1', NULL, NULL, 1, NULL),
(566, NULL, 'JLG 2646 Scissorlift #1', NULL, NULL, 'JL2646#1', NULL, NULL, 1, NULL),
(567, NULL, 'JLG 2646 Scissorlift #2', NULL, NULL, 'JL2646#2', NULL, NULL, 1, NULL),
(568, NULL, 'JLG 3246 Scissorlift #1', NULL, NULL, 'JL3246#1', NULL, NULL, 1, NULL),
(569, NULL, 'JLG 3246 Scissorlift #2', NULL, NULL, 'JL3246#2', NULL, NULL, 1, NULL),
(570, NULL, 'JLG 3246 Scissorlift #3', NULL, NULL, 'JL3246#3', NULL, NULL, 1, NULL),
(571, NULL, 'JLG 3246 Scissorlift #4', NULL, NULL, 'JL3246#4', NULL, NULL, 1, NULL),
(572, NULL, 'JLG 3246 Scissorlift #5', NULL, NULL, 'JL3246#5', NULL, NULL, 1, NULL),
(573, NULL, 'JLG 3246 Scissorlift #6', NULL, NULL, 'JL3246#6', NULL, NULL, 1, NULL),
(574, NULL, 'JLG 4017 Telehandler #1', NULL, NULL, 'JL4017#1', NULL, NULL, 1, NULL),
(575, NULL, 'JLG 4017 Telehandler #2', NULL, NULL, 'JL4017#2', NULL, NULL, 1, NULL),
(576, NULL, 'JLG 4017 Telehandler #3', NULL, NULL, 'JL4017#3', NULL, NULL, 1, NULL),
(577, NULL, 'JLG 1250 AJP Manlift #1', NULL, NULL, 'JLG1250', NULL, NULL, 1, NULL),
(578, NULL, 'JLG E400 Articulated BmLft #1', NULL, NULL, 'JLG400#1', NULL, NULL, 1, NULL),
(579, NULL, 'JLG E400 Articulated BmLft #2', NULL, NULL, 'JLG400#2', NULL, NULL, 1, NULL),
(580, NULL, 'JLG600 AJ #1', NULL, NULL, 'JLG600#1', NULL, NULL, 1, NULL),
(581, NULL, 'JLG600 AJ #2', NULL, NULL, 'JLG600#2', NULL, NULL, 1, NULL),
(582, NULL, 'JLG600 AJ #3', NULL, NULL, 'JLG600#3', NULL, NULL, 1, NULL),
(583, NULL, 'JLG600 AJ #4', NULL, NULL, 'JLG600#4', NULL, NULL, 1, NULL),
(584, NULL, 'JLG600 AJ #5', NULL, NULL, 'JLG600#5', NULL, NULL, 1, NULL),
(585, NULL, 'JLG600 AJ #6', NULL, NULL, 'JLG600#6', NULL, NULL, 1, NULL),
(586, NULL, 'JLG600 AJ #7', NULL, NULL, 'JLG600#7', NULL, NULL, 1, NULL),
(587, NULL, 'JLG600 AJ #8', NULL, NULL, 'JLG600#8', NULL, NULL, 1, NULL),
(588, NULL, 'JLG-G642A Telehandler #1', NULL, NULL, 'JLG642#1', NULL, NULL, 1, NULL),
(589, NULL, 'JLG-G642A Telehandler #2', NULL, NULL, 'JLG642#2', NULL, NULL, 1, NULL),
(590, NULL, 'JLG-G642A Telehandler #3', NULL, NULL, 'JLG642#3', NULL, NULL, 1, NULL),
(591, NULL, 'JLG800 AJ #1', NULL, NULL, 'JLG800#1', NULL, NULL, 1, NULL),
(592, NULL, 'JLG800 AJ #2', NULL, NULL, 'JLG800#2', NULL, NULL, 1, NULL),
(593, NULL, 'JLG800 AJ #3', NULL, NULL, 'JLG800#3', NULL, NULL, 1, NULL),
(594, NULL, 'JLG 860SJ Manlift #1', NULL, NULL, 'JLG860#1', NULL, NULL, 1, NULL),
(595, NULL, 'JLG 860SJ Manlift #2', NULL, NULL, 'JLG860#2', NULL, NULL, 1, NULL),
(596, NULL, 'JLG 860SJ Manlift #3', NULL, NULL, 'JLG860#3', NULL, NULL, 1, NULL),
(597, NULL, 'XCMG Knuckle Boom #1  CR4500', NULL, NULL, 'KB4500#1', NULL, NULL, 1, NULL),
(598, NULL, 'Tower Light 5 KVA 4 Lamps #1', NULL, NULL, 'KDE67#1', NULL, NULL, 1, NULL),
(599, NULL, 'Tower Light 5 KVA 4 Lamps #2', NULL, NULL, 'KDE67#2', NULL, NULL, 1, NULL),
(600, NULL, 'Tower Light 5 KVA 4 Lamps #3', NULL, NULL, 'KDE67#3', NULL, NULL, 1, NULL),
(601, NULL, 'Tower Light 5 KVA 4 Lamps #4', NULL, NULL, 'KDE67#4', NULL, NULL, 1, NULL),
(602, NULL, 'Tower Light 5 KVA 4 Lamps #5', NULL, NULL, 'KDE67#5', NULL, NULL, 1, NULL),
(603, NULL, 'Tower Light 5 KVA 4 Lamps #6', NULL, NULL, 'KDE67#6', NULL, NULL, 1, NULL),
(604, NULL, 'Tower Light 5 KVA 4 Lamps #7', NULL, NULL, 'KDE67#7', NULL, NULL, 1, NULL),
(605, NULL, 'Tower Light 11.2KVA 4 Lamps #1', NULL, NULL, 'KLT80#1', NULL, NULL, 1, NULL),
(606, NULL, 'Tower Light 11.2KVA 4 Lamps #2', NULL, NULL, 'KLT80#2', NULL, NULL, 1, NULL),
(607, NULL, 'Tower Light 11.2KVA 4 Lamps #3', NULL, NULL, 'KLT80#3', NULL, NULL, 1, NULL),
(608, NULL, 'Tower Light 11.2KVA 4 Lamps #4', NULL, NULL, 'KLT80#4', NULL, NULL, 1, NULL),
(609, NULL, 'Tower Light 11.2KVA 4 Lamps #5', NULL, NULL, 'KLT80#5', NULL, NULL, 1, NULL),
(610, NULL, 'Tower Light 11.2KVA 4 Lamps #6', NULL, NULL, 'KLT80#6', NULL, NULL, 1, NULL),
(611, NULL, 'DO NOT USE!!! Use GMK-6180', NULL, NULL, 'KMK-6180', NULL, NULL, 0, NULL),
(612, NULL, 'CRT-Kato KR-25H #1', NULL, NULL, 'KR-25H#1', NULL, NULL, 1, NULL),
(613, NULL, 'CRT-Kato KR-25H #2', NULL, NULL, 'KR-25H#2', NULL, NULL, 1, NULL),
(614, NULL, 'Kato KR-25H #3', NULL, NULL, 'KR-25H#3', NULL, NULL, 1, NULL),
(615, NULL, 'Kato KR-25H #4', NULL, NULL, 'KR-25H#4', NULL, NULL, 1, NULL),
(616, NULL, 'Kato KR25-H #5', NULL, NULL, 'KR-25H#5', NULL, NULL, 1, NULL),
(617, NULL, 'Kato KR-25H #6', NULL, NULL, 'KR-25H#6', NULL, NULL, 1, NULL),
(618, NULL, 'Kato KR-25H #7', NULL, NULL, 'KR-25H#7', NULL, NULL, 1, NULL),
(619, NULL, 'Abra', NULL, NULL, 'L01', NULL, NULL, 1, NULL),
(620, NULL, 'Batangas-Bauan', NULL, NULL, 'L010', NULL, NULL, 1, NULL),
(621, NULL, 'Batangas-Mabini', NULL, NULL, 'L011', NULL, NULL, 1, NULL),
(622, NULL, 'Batangas-Simlong', NULL, NULL, 'L012', NULL, NULL, 1, NULL),
(623, NULL, 'Benguet', NULL, NULL, 'L013', NULL, NULL, 1, NULL),
(624, NULL, 'Bulacan', NULL, NULL, 'L014', NULL, NULL, 1, NULL),
(625, NULL, 'Cagayan', NULL, NULL, 'L015', NULL, NULL, 1, NULL),
(626, NULL, 'Cagayan-Port Irene', NULL, NULL, 'L016', NULL, NULL, 1, NULL),
(627, NULL, 'Camarines Norte', NULL, NULL, 'L017', NULL, NULL, 1, NULL),
(628, NULL, 'Camarines Sur', NULL, NULL, 'L018', NULL, NULL, 1, NULL),
(629, NULL, 'Catanduanes', NULL, NULL, 'L019', NULL, NULL, 1, NULL),
(630, NULL, 'Albay', NULL, NULL, 'L02', NULL, NULL, 1, NULL),
(631, NULL, 'Cavite', NULL, NULL, 'L020', NULL, NULL, 1, NULL),
(632, NULL, 'Cavite-Bacoor', NULL, NULL, 'L021', NULL, NULL, 1, NULL),
(633, NULL, 'Cavite-Imus', NULL, NULL, 'L022', NULL, NULL, 1, NULL),
(634, NULL, 'Cavite-Dasmarinas', NULL, NULL, 'L023', NULL, NULL, 1, NULL),
(635, NULL, 'Cavite-Silang', NULL, NULL, 'L024', NULL, NULL, 1, NULL),
(636, NULL, 'Cavite-Rosario', NULL, NULL, 'L025', NULL, NULL, 1, NULL),
(637, NULL, 'Cavite-Kawit', NULL, NULL, 'L026', NULL, NULL, 1, NULL),
(638, NULL, 'Cavite-Noveleta', NULL, NULL, 'L027', NULL, NULL, 1, NULL),
(639, NULL, 'Cavite-Carmona', NULL, NULL, 'L028', NULL, NULL, 1, NULL),
(640, NULL, 'Cavite-Gen. Trias', NULL, NULL, 'L029', NULL, NULL, 1, NULL),
(641, NULL, 'Apayao', NULL, NULL, 'L03', NULL, NULL, 1, NULL),
(642, NULL, 'Cavite-Amadeo', NULL, NULL, 'L030', NULL, NULL, 1, NULL),
(643, NULL, 'Cavite-Naic', NULL, NULL, 'L031', NULL, NULL, 1, NULL),
(644, NULL, 'Cavite-Ternate', NULL, NULL, 'L032', NULL, NULL, 1, NULL),
(645, NULL, 'Cavite-Trece Martires', NULL, NULL, 'L033', NULL, NULL, 1, NULL),
(646, NULL, 'Cavite-Tanza', NULL, NULL, 'L034', NULL, NULL, 1, NULL),
(647, NULL, 'Ifugao', NULL, NULL, 'L035', NULL, NULL, 1, NULL),
(648, NULL, 'Ilocos Norte', NULL, NULL, 'L036', NULL, NULL, 1, NULL),
(649, NULL, 'Ilocos Norte-Currimao', NULL, NULL, 'L037', NULL, NULL, 1, NULL),
(650, NULL, 'Ilocos Norte-Bangui', NULL, NULL, 'L038', NULL, NULL, 1, NULL),
(651, NULL, 'Ilocos Norte-Burgos', NULL, NULL, 'L039', NULL, NULL, 1, NULL),
(652, NULL, 'Aurora', NULL, NULL, 'L04', NULL, NULL, 1, NULL),
(653, NULL, 'Ilocos Sur', NULL, NULL, 'L040', NULL, NULL, 1, NULL),
(654, NULL, 'Isabela', NULL, NULL, 'L041', NULL, NULL, 1, NULL),
(655, NULL, 'Kalinga', NULL, NULL, 'L042', NULL, NULL, 1, NULL),
(656, NULL, 'La Union', NULL, NULL, 'L043', NULL, NULL, 1, NULL);
INSERT INTO `equipment_units` (`eqm_id`, `eqm_eqmm_id`, `eqm_name`, `eqm_vin`, `eqm_plate_num`, `eqm_prc_code`, `eqm_serial_num`, `eqm_engine`, `eqm_is_active`, `eqm_updated_at`) VALUES
(657, NULL, 'La Union-San Fernando', NULL, NULL, 'L044', NULL, NULL, 1, NULL),
(658, NULL, 'La Union-Poro Pt.', NULL, NULL, 'L045', NULL, NULL, 1, NULL),
(659, NULL, 'Laguna', NULL, NULL, 'L046', NULL, NULL, 1, NULL),
(660, NULL, 'Laguna-Calamba', NULL, NULL, 'L047', NULL, NULL, 1, NULL),
(661, NULL, 'Laguna-Sta. Rosa', NULL, NULL, 'L048', NULL, NULL, 1, NULL),
(662, NULL, 'Laguna-Binan', NULL, NULL, 'L049', NULL, NULL, 1, NULL),
(663, NULL, 'Bataan', NULL, NULL, 'L05', NULL, NULL, 1, NULL),
(664, NULL, 'Laguna-San Pablo', NULL, NULL, 'L050', NULL, NULL, 1, NULL),
(665, NULL, 'Laguna-Cabuyao', NULL, NULL, 'L051', NULL, NULL, 1, NULL),
(666, NULL, 'Laguna-San Pedro', NULL, NULL, 'L052', NULL, NULL, 1, NULL),
(667, NULL, 'Laguna-Los Banos', NULL, NULL, 'L053', NULL, NULL, 1, NULL),
(668, NULL, 'Laguna-Santa Cruz', NULL, NULL, 'L054', NULL, NULL, 1, NULL),
(669, NULL, 'Laguna-Canlubang', NULL, NULL, 'L055', NULL, NULL, 1, NULL),
(670, NULL, 'Laguna-Liliw', NULL, NULL, 'L056', NULL, NULL, 1, NULL),
(671, NULL, 'Marinduque', NULL, NULL, 'L057', NULL, NULL, 1, NULL),
(672, NULL, 'Masbate', NULL, NULL, 'L058', NULL, NULL, 1, NULL),
(673, NULL, 'Mountain Province', NULL, NULL, 'L059', NULL, NULL, 1, NULL),
(674, NULL, 'Bataan-Limay', NULL, NULL, 'L06', NULL, NULL, 1, NULL),
(675, NULL, 'Nueva Ecija', NULL, NULL, 'L060', NULL, NULL, 1, NULL),
(676, NULL, 'Occidental Mindoro', NULL, NULL, 'L061', NULL, NULL, 1, NULL),
(677, NULL, 'Oriental Mindoro', NULL, NULL, 'L062', NULL, NULL, 1, NULL),
(678, NULL, 'Palawan', NULL, NULL, 'L063', NULL, NULL, 1, NULL),
(679, NULL, 'Pampanga', NULL, NULL, 'L064', NULL, NULL, 1, NULL),
(680, NULL, 'Pampanga-Clark', NULL, NULL, 'L065', NULL, NULL, 1, NULL),
(681, NULL, 'Pangasinan', NULL, NULL, 'L066', NULL, NULL, 1, NULL),
(682, NULL, 'Quezon', NULL, NULL, 'L067', NULL, NULL, 1, NULL),
(683, NULL, 'Quezon-Pagbilao', NULL, NULL, 'L068', NULL, NULL, 1, NULL),
(684, NULL, 'Quezon-Lucban', NULL, NULL, 'L069', NULL, NULL, 1, NULL),
(685, NULL, 'Bataan-Mariveles', NULL, NULL, 'L07', NULL, NULL, 1, NULL),
(686, NULL, 'Quirino', NULL, NULL, 'L070', NULL, NULL, 1, NULL),
(687, NULL, 'Rizal', NULL, NULL, 'L071', NULL, NULL, 1, NULL),
(688, NULL, 'Sorsogon', NULL, NULL, 'L072', NULL, NULL, 1, NULL),
(689, NULL, 'Tarlac', NULL, NULL, 'L073', NULL, NULL, 1, NULL),
(690, NULL, 'Zambales', NULL, NULL, 'L074', NULL, NULL, 1, NULL),
(691, NULL, 'Zambales-Subic', NULL, NULL, 'L075', NULL, NULL, 1, NULL),
(692, NULL, 'Nueva Vizcaya', NULL, NULL, 'L076', NULL, NULL, 1, NULL),
(693, NULL, 'Batanes', NULL, NULL, 'L08', NULL, NULL, 1, NULL),
(694, NULL, 'Batangas', NULL, NULL, 'L09', NULL, NULL, 1, NULL),
(695, NULL, 'L300 Vans', NULL, NULL, 'L300', NULL, NULL, 1, NULL),
(696, NULL, 'Lashing Equipment', NULL, NULL, 'LASHEQ', NULL, NULL, 1, NULL),
(697, NULL, 'Lancer Boss G2512 25T', NULL, NULL, 'LB  01', NULL, NULL, 1, NULL),
(698, NULL, 'LowBed Trailer #1', NULL, NULL, 'LBT#1', NULL, NULL, 1, NULL),
(699, NULL, 'LowBed Trailer #10 Tokyu', NULL, NULL, 'LBT#10', NULL, NULL, 1, NULL),
(700, NULL, 'LowBed Trailer #11 Shunhe', NULL, NULL, 'LBT#11', NULL, NULL, 1, NULL),
(701, NULL, 'LowBed Trailer #12 Shunhe', NULL, NULL, 'LBT#12', NULL, NULL, 1, NULL),
(702, NULL, 'LowBed Trailer #2', NULL, NULL, 'LBT#2', NULL, NULL, 1, NULL),
(703, NULL, 'LowBed Trailer #3', NULL, NULL, 'LBT#3', NULL, NULL, 1, NULL),
(704, NULL, 'LowBed Trailer #4', NULL, NULL, 'LBT#4', NULL, NULL, 1, NULL),
(705, NULL, 'LowBed Trailer #5', NULL, NULL, 'LBT#5', NULL, NULL, 1, NULL),
(706, NULL, 'LowBed Trailer #6', NULL, NULL, 'LBT#6', NULL, NULL, 1, NULL),
(707, NULL, 'EXTLB #1 as of 7/3/17', NULL, NULL, 'LBT#7', NULL, NULL, 1, NULL),
(708, NULL, 'LowBed Trailer #8', NULL, NULL, 'LBT#8', NULL, NULL, 1, NULL),
(709, NULL, 'LowBed Trailer #9', NULL, NULL, 'LBT#9', NULL, NULL, 1, NULL),
(710, NULL, 'Hyundai 22T Backhoe', NULL, NULL, 'LC220', NULL, NULL, 1, NULL),
(711, NULL, 'LIFTRITE, INC', NULL, NULL, 'LRI', NULL, NULL, 1, NULL),
(712, NULL, 'Liebherr LTM 1500-8.1 #1', NULL, NULL, 'LT1500#1', NULL, NULL, 1, NULL),
(713, NULL, 'Liebherr LTM 1500-8.1 #2', NULL, NULL, 'LT1500#2', NULL, NULL, 1, NULL),
(714, NULL, 'Komatsu RT LW80#1 (8Ton)', NULL, NULL, 'LW80#1', NULL, NULL, 1, NULL),
(715, NULL, 'Komatsu RT LW80#2 (8Ton)', NULL, NULL, 'LW80#2', NULL, NULL, 1, NULL),
(716, NULL, 'Agusan del Norte', NULL, NULL, 'M01', NULL, NULL, 1, NULL),
(717, NULL, 'Davao Oriental', NULL, NULL, 'M010', NULL, NULL, 1, NULL),
(718, NULL, 'Dinagat Islands', NULL, NULL, 'M011', NULL, NULL, 1, NULL),
(719, NULL, 'Lanao del Norte', NULL, NULL, 'M012', NULL, NULL, 1, NULL),
(720, NULL, 'Lanao del Norte-Iligan', NULL, NULL, 'M013', NULL, NULL, 1, NULL),
(721, NULL, 'Lanao del Sur', NULL, NULL, 'M014', NULL, NULL, 1, NULL),
(722, NULL, 'Maguindanao', NULL, NULL, 'M015', NULL, NULL, 1, NULL),
(723, NULL, 'Misamis Occidental', NULL, NULL, 'M016', NULL, NULL, 1, NULL),
(724, NULL, 'Misamis Oriental', NULL, NULL, 'M017', NULL, NULL, 1, NULL),
(725, NULL, 'Misamis Oriental-CDO', NULL, NULL, 'M018', NULL, NULL, 1, NULL),
(726, NULL, 'Misamis Oriental-Lugait', NULL, NULL, 'M019', NULL, NULL, 1, NULL),
(727, NULL, 'Agusan del Sur', NULL, NULL, 'M02', NULL, NULL, 1, NULL),
(728, NULL, 'Misamis Oriental-Tagoloan', NULL, NULL, 'M020', NULL, NULL, 1, NULL),
(729, NULL, 'Sarangani', NULL, NULL, 'M021', NULL, NULL, 1, NULL),
(730, NULL, 'South Cotabato', NULL, NULL, 'M022', NULL, NULL, 1, NULL),
(731, NULL, 'Sultan Kudarat', NULL, NULL, 'M023', NULL, NULL, 1, NULL),
(732, NULL, 'Sulu', NULL, NULL, 'M024', NULL, NULL, 1, NULL),
(733, NULL, 'Surigao del Norte', NULL, NULL, 'M025', NULL, NULL, 1, NULL),
(734, NULL, 'Surigao del Sur', NULL, NULL, 'M026', NULL, NULL, 1, NULL),
(735, NULL, 'Tawi-Tawi', NULL, NULL, 'M027', NULL, NULL, 1, NULL),
(736, NULL, 'Zamboanga del Norte', NULL, NULL, 'M028', NULL, NULL, 1, NULL),
(737, NULL, 'Zamboanga del Sur', NULL, NULL, 'M029', NULL, NULL, 1, NULL),
(738, NULL, 'Basilan', NULL, NULL, 'M03', NULL, NULL, 1, NULL),
(739, NULL, 'Zamboanga Sibugay', NULL, NULL, 'M030', NULL, NULL, 1, NULL),
(740, NULL, 'Bukidnon', NULL, NULL, 'M04', NULL, NULL, 1, NULL),
(741, NULL, 'Compostela Valley', NULL, NULL, 'M05', NULL, NULL, 1, NULL),
(742, NULL, 'Cotabato', NULL, NULL, 'M06', NULL, NULL, 1, NULL),
(743, NULL, 'Davao del Norte', NULL, NULL, 'M07', NULL, NULL, 1, NULL),
(744, NULL, 'Davao del Sur', NULL, NULL, 'M08', NULL, NULL, 1, NULL),
(745, NULL, 'Davao Occidental', NULL, NULL, 'M09', NULL, NULL, 1, NULL),
(746, NULL, 'Manitowoc crawler crane 230T', NULL, NULL, 'M4100W#1', NULL, NULL, 1, NULL),
(747, NULL, 'PST 6 axles-E Goldhofer #1', NULL, NULL, 'MALE6 01', NULL, NULL, 1, NULL),
(748, NULL, 'PST 6 axles-E Goldhofer #2', NULL, NULL, 'MALE6 02', NULL, NULL, 1, NULL),
(749, NULL, 'MAN Truck #1', NULL, NULL, 'MAN#1', NULL, NULL, 0, NULL),
(750, NULL, 'MAN Truck #2', NULL, NULL, 'MAN#2', NULL, NULL, 0, NULL),
(751, NULL, 'MAN Truck #3', NULL, NULL, 'MAN#3', NULL, NULL, 0, NULL),
(752, NULL, 'MAN Truck #4', NULL, NULL, 'MAN#4', NULL, NULL, 0, NULL),
(753, NULL, 'MAN Truck #5', NULL, NULL, 'MAN#5', NULL, NULL, 0, NULL),
(754, NULL, 'MAN Truck #6', NULL, NULL, 'MAN#6', NULL, NULL, 0, NULL),
(755, NULL, 'MAN-2150MRT Telehandler', NULL, NULL, 'MAN2150', NULL, NULL, 0, NULL),
(756, NULL, 'Cebu Mandaue Yard', NULL, NULL, 'Mandaue', NULL, NULL, 1, NULL),
(757, NULL, 'MAN TGX 41.540 8x4 Tractor #1', NULL, NULL, 'MANTH#1', NULL, NULL, 1, NULL),
(758, NULL, 'MAN TGX 41.540 8x4 Tractor #2', NULL, NULL, 'MANTH#2', NULL, NULL, 0, NULL),
(759, NULL, 'MAN TGX 41.540 8x4 Tractor #3', NULL, NULL, 'MANTH#3', NULL, NULL, 1, NULL),
(760, NULL, 'MAN TGX 41.540 8x4 Tractor #4', NULL, NULL, 'MANTH#4', NULL, NULL, 1, NULL),
(761, NULL, 'MAN TGX 41.540 8x4 Tractor #5', NULL, NULL, 'MANTH#5', NULL, NULL, 1, NULL),
(762, NULL, 'MAN TGX 41.540 8x4 Tractor #6', NULL, NULL, 'MANTH#6', NULL, NULL, 1, NULL),
(763, NULL, 'MAN TGX 41.680 8x4 Tractor #7', NULL, NULL, 'MANTH#7', NULL, NULL, 1, NULL),
(764, NULL, 'MAN TGX 41.680 8x4 Tractor #8', NULL, NULL, 'MANTH#8', NULL, NULL, 1, NULL),
(765, NULL, 'MAN TGX 41.680 8x4 Tractor #9', NULL, NULL, 'MANTH#9', NULL, NULL, 1, NULL),
(766, NULL, 'Spider Crane 2.93 tons', NULL, NULL, 'MC355C#1', NULL, NULL, 1, NULL),
(767, NULL, 'Drop Deck Trailer', NULL, NULL, 'MD   01', NULL, NULL, 1, NULL),
(768, NULL, 'Drop Deck SL w/o ramp #2 (FV)', NULL, NULL, 'MD   02', NULL, NULL, 1, NULL),
(769, NULL, 'Drop Deck SL 2m Insert #1 (FV)', NULL, NULL, 'MDI2 01', NULL, NULL, 1, NULL),
(770, NULL, 'Drop Deck SL 3m Insert #2 (FV)', NULL, NULL, 'MDI3 02', NULL, NULL, 1, NULL),
(771, NULL, 'Drop Deck UT Insert 2m', NULL, NULL, 'MDIU2 01', NULL, NULL, 1, NULL),
(772, NULL, 'Drop Deck SL w/ Ramp #1 (FV)', NULL, NULL, 'MDR   01', NULL, NULL, 1, NULL),
(773, NULL, 'Drop Deck SL w/ ramp #2 (FV)', NULL, NULL, 'MDR  02', NULL, NULL, 1, NULL),
(774, NULL, 'Drop Deck UT #1', NULL, NULL, 'MDU   01', NULL, NULL, 1, NULL),
(775, NULL, 'Drop Deck UT w/o Ramp', NULL, NULL, 'MDU  02', NULL, NULL, 1, NULL),
(776, NULL, 'Drop Deck UT w/ Ramp', NULL, NULL, 'MDUR 02', NULL, NULL, 1, NULL),
(777, NULL, 'Gooseneck SL#1 Goldhofer', NULL, NULL, 'MGN  01', NULL, NULL, 1, NULL),
(778, NULL, 'Gooseneck SL#2 Goldhofer', NULL, NULL, 'MGN  02', NULL, NULL, 1, NULL),
(779, NULL, 'Gooseneck UT#3 Goldhofer', NULL, NULL, 'MGN  03', NULL, NULL, 1, NULL),
(780, NULL, 'Gooseneck SL#4 Hyst', NULL, NULL, 'MGN  04', NULL, NULL, 1, NULL),
(781, NULL, 'Powerpack Gooseneck #1', NULL, NULL, 'MGPP  01', NULL, NULL, 1, NULL),
(782, NULL, 'Powerpack Gooseneck #2', NULL, NULL, 'MGPP  02', NULL, NULL, 1, NULL),
(783, NULL, 'Powerpack Hydronic #1 (GH)', NULL, NULL, 'MHPP  01', NULL, NULL, 1, NULL),
(784, NULL, 'Powerpack Hydronic #2 (GH)', NULL, NULL, 'MHPP  02', NULL, NULL, 1, NULL),
(785, NULL, 'FL-Mitsubishi 18T', NULL, NULL, 'MIT-18T', NULL, NULL, 0, NULL),
(786, NULL, 'Spreader Bar', NULL, NULL, 'MOD 110', NULL, NULL, 1, NULL),
(787, NULL, 'Spreader Bar', NULL, NULL, 'MOD 110H', NULL, NULL, 1, NULL),
(788, NULL, '\"Spreader Bar, Mod 34, 1m-10m\"', NULL, NULL, 'MOD 34', NULL, NULL, 1, NULL),
(789, NULL, '\"Spreader Bar, Mod 50, 1m-13m\"', NULL, NULL, 'MOD 50', NULL, NULL, 1, NULL),
(790, NULL, '\"Spreader Bar, Mod 70, 1m-14m\"', NULL, NULL, 'MOD 70', NULL, NULL, 1, NULL),
(791, NULL, 'Mod. Trailer 4L #1 -Goldhofer', NULL, NULL, 'MOD4L#1', NULL, NULL, 0, NULL),
(792, NULL, 'Mod. Trailer 4L #2 -Goldhofer', NULL, NULL, 'MOD4L#2', NULL, NULL, 0, NULL),
(793, NULL, 'Mod. Trailer 4L #3 -Goldhofer', NULL, NULL, 'MOD4L#3', NULL, NULL, 0, NULL),
(794, NULL, 'Mod. Trailer 6L #1 -Goldhofer', NULL, NULL, 'MOD6L#1', NULL, NULL, 0, NULL),
(795, NULL, 'Mod. Trailer 6L #2 -Goldhofer', NULL, NULL, 'MOD6L#2', NULL, NULL, 0, NULL),
(796, NULL, 'Mod. Trailer 6L #3 Faymonville', NULL, NULL, 'MOD6L#3', NULL, NULL, 0, NULL),
(797, NULL, 'Mod. Trailer 6L #4 Goldhofer', NULL, NULL, 'MOD6L#4', NULL, NULL, 0, NULL),
(798, NULL, 'Mod. Trailer 6L #5 Goldhofer', NULL, NULL, 'MOD6L#5', NULL, NULL, 0, NULL),
(799, NULL, 'Powerpack #1 UT', NULL, NULL, 'MPP   01', NULL, NULL, 1, NULL),
(800, NULL, 'Powerpack #2 UT', NULL, NULL, 'MPP   02', NULL, NULL, 1, NULL),
(801, NULL, 'Powerpack #3 SL', NULL, NULL, 'MPP   03', NULL, NULL, 1, NULL),
(802, NULL, 'Powerpack #4 SL', NULL, NULL, 'MPP   04', NULL, NULL, 1, NULL),
(803, NULL, 'Powerpack #5 SL', NULL, NULL, 'MPP   05', NULL, NULL, 1, NULL),
(804, NULL, 'Powerpack #6 SL', NULL, NULL, 'MPP   06', NULL, NULL, 1, NULL),
(805, NULL, 'Powerpack #7 SL', NULL, NULL, 'MPP   07', NULL, NULL, 1, NULL),
(806, NULL, 'Powerpack #8 SL', NULL, NULL, 'MPP   08', NULL, NULL, 1, NULL),
(807, NULL, 'Powerpack #9 SL', NULL, NULL, 'MPP   09', NULL, NULL, 1, NULL),
(808, NULL, 'Powerpack #10 SL', NULL, NULL, 'MPP   10', NULL, NULL, 1, NULL),
(809, NULL, 'Powerpack #11 SL', NULL, NULL, 'MPP   11', NULL, NULL, 1, NULL),
(810, NULL, 'Powerpack #12 SL', NULL, NULL, 'MPP   12', NULL, NULL, 1, NULL),
(811, NULL, 'Powerpack #13 SL', NULL, NULL, 'MPP   13', NULL, NULL, 1, NULL),
(812, NULL, 'Powerpack #14 SL', NULL, NULL, 'MPP   14', NULL, NULL, 1, NULL),
(813, NULL, 'Powerpack #15 SL', NULL, NULL, 'MPP   15', NULL, NULL, 1, NULL),
(814, NULL, 'Powerpack #16', NULL, NULL, 'MPP   16', NULL, NULL, 1, NULL),
(815, NULL, 'Powerpack #17', NULL, NULL, 'MPP   17', NULL, NULL, 1, NULL),
(816, NULL, 'Powerpack #18 SL', NULL, NULL, 'MPP   18', NULL, NULL, 1, NULL),
(817, NULL, 'Powerpack #19 SL', NULL, NULL, 'MPP   19', NULL, NULL, 1, NULL),
(818, NULL, 'Powerpack #20 SL', NULL, NULL, 'MPP   20', NULL, NULL, 1, NULL),
(819, NULL, 'Powerpack #21 SL', NULL, NULL, 'MPP   21', NULL, NULL, 1, NULL),
(820, NULL, 'Powerpack #22 SL', NULL, NULL, 'MPP   22', NULL, NULL, 1, NULL),
(821, NULL, 'Powerpack #23 SL', NULL, NULL, 'MPP   23', NULL, NULL, 1, NULL),
(822, NULL, 'Powerpack #24 SL', NULL, NULL, 'MPP   24', NULL, NULL, 1, NULL),
(823, NULL, 'Powerpack #25 SL', NULL, NULL, 'MPP   25', NULL, NULL, 1, NULL),
(824, NULL, 'Powerpack #26 SL', NULL, NULL, 'MPP   26', NULL, NULL, 1, NULL),
(825, NULL, 'Powerpack #27 SL', NULL, NULL, 'MPP   27', NULL, NULL, 1, NULL),
(826, NULL, 'Powerpack #28 SL', NULL, NULL, 'MPP   28', NULL, NULL, 1, NULL),
(827, NULL, 'Powerpack #29 SL', NULL, NULL, 'MPP   29', NULL, NULL, 1, NULL),
(828, NULL, 'Powerpack 490HP #1', NULL, NULL, 'MPP490 1', NULL, NULL, 1, NULL),
(829, NULL, 'Powerpack 490HP #2', NULL, NULL, 'MPP490 2', NULL, NULL, 1, NULL),
(830, NULL, 'Powerpack 490HP #3', NULL, NULL, 'MPP490 3', NULL, NULL, 1, NULL),
(831, NULL, 'Powerpack 490HP #4', NULL, NULL, 'MPP490 4', NULL, NULL, 1, NULL),
(832, NULL, 'Manitou MRT 2150 Telehandler', NULL, NULL, 'MRT2150', NULL, NULL, 1, NULL),
(833, NULL, 'Turntable SET #1 (GH)', NULL, NULL, 'MTT   01', NULL, NULL, 1, NULL),
(834, NULL, 'Turntable SET #2 (GH)', NULL, NULL, 'MTT   02', NULL, NULL, 1, NULL),
(835, NULL, 'Bridge Set 10M #7', NULL, NULL, 'MVB10 07', NULL, NULL, 1, NULL),
(836, NULL, 'BRIDGE SET LR 2M #1', NULL, NULL, 'MVB2  01', NULL, NULL, 1, NULL),
(837, NULL, 'BRIDGE SET LR 3M #2', NULL, NULL, 'MVB3  02', NULL, NULL, 1, NULL),
(838, NULL, 'Bridge Fix SET 3M LR #2 (FV)', NULL, NULL, 'MVB3  04', NULL, NULL, 1, NULL),
(839, NULL, 'BRIDGE SET LR 4M #3', NULL, NULL, 'MVB4  03', NULL, NULL, 1, NULL),
(840, NULL, 'Bridge Set 4M #8', NULL, NULL, 'MVB4  08', NULL, NULL, 1, NULL),
(841, NULL, 'BRIDGE SET LR 5M #4', NULL, NULL, 'MVB5  04', NULL, NULL, 1, NULL),
(842, NULL, 'Bridge Fix SET 5M LR#2 (FV)', NULL, NULL, 'MVB5 03', NULL, NULL, 1, NULL),
(843, NULL, 'Bridge Set 6M #9', NULL, NULL, 'MVB6  09', NULL, NULL, 1, NULL),
(844, NULL, 'Bridge Set 6M #10', NULL, NULL, 'MVB6  10', NULL, NULL, 1, NULL),
(845, NULL, 'BRIDGE SET LR 7M #5', NULL, NULL, 'MVB7  05', NULL, NULL, 1, NULL),
(846, NULL, 'Bridge Fix SET 8M LR #2 (FV)', NULL, NULL, 'MVB8 02', NULL, NULL, 1, NULL),
(847, NULL, 'Bridge Ext SET #1 LR (FV)', NULL, NULL, 'MVBE  01', NULL, NULL, 1, NULL),
(848, NULL, 'Bridge Ext SET #6', NULL, NULL, 'MVBE  06', NULL, NULL, 1, NULL),
(849, NULL, 'THP SL 2-axle #1 Goldhofer', NULL, NULL, 'MXL2  01', NULL, NULL, 1, NULL),
(850, NULL, 'THP SL 2-axle #2 Hyst', NULL, NULL, 'MXL2  02', NULL, NULL, 1, NULL),
(851, NULL, 'THP SL 2-axle #3 Hyst', NULL, NULL, 'MXL2  03', NULL, NULL, 1, NULL),
(852, NULL, 'THP SL 2-axle #4 Hyst', NULL, NULL, 'MXL2  04', NULL, NULL, 1, NULL),
(853, NULL, 'THP SL 2-axle #5 Hyst', NULL, NULL, 'MXL2  05', NULL, NULL, 1, NULL),
(854, NULL, 'THP/SL3 #1 Goldhofer', NULL, NULL, 'MXL3  01', NULL, NULL, 1, NULL),
(855, NULL, 'THP/SL3 #2 Hyst', NULL, NULL, 'MXL3  02', NULL, NULL, 1, NULL),
(856, NULL, 'THP/SL3 #3 Hsyt', NULL, NULL, 'MXL3  03', NULL, NULL, 1, NULL),
(857, NULL, 'THP/SL3 #4 Hyst', NULL, NULL, 'MXL3  04', NULL, NULL, 1, NULL),
(858, NULL, 'THP/SL3 #5 Hyst', NULL, NULL, 'MXL3  05', NULL, NULL, 1, NULL),
(859, NULL, 'THP/SL3 #6 Hyst', NULL, NULL, 'MXL3  06', NULL, NULL, 1, NULL),
(860, NULL, 'THP/SL4 #1 Goldhofer', NULL, NULL, 'MXL4  01', NULL, NULL, 1, NULL),
(861, NULL, 'THP/SL4 #2 Goldhofer', NULL, NULL, 'MXL4  02', NULL, NULL, 1, NULL),
(862, NULL, 'THP/SL4 #3 Goldhofer', NULL, NULL, 'MXL4  03', NULL, NULL, 1, NULL),
(863, NULL, 'THP/SL4 #4 Faymonville', NULL, NULL, 'MXL4  04', NULL, NULL, 1, NULL),
(864, NULL, 'THP/SL4 #5 Faymonville', NULL, NULL, 'MXL4  05', NULL, NULL, 1, NULL),
(865, NULL, 'THP/SL4 #6 Faymonville', NULL, NULL, 'MXL4  06', NULL, NULL, 1, NULL),
(866, NULL, 'THP/SL4 #7 Faymonville', NULL, NULL, 'MXL4  07', NULL, NULL, 1, NULL),
(867, NULL, 'THP/SL4 #8 Faymonville', NULL, NULL, 'MXL4  08', NULL, NULL, 1, NULL),
(868, NULL, 'THP/SL4 #9 Hyst', NULL, NULL, 'MXL4  09', NULL, NULL, 1, NULL),
(869, NULL, 'THP/SL4 #10 Hyst', NULL, NULL, 'MXL4  10', NULL, NULL, 1, NULL),
(870, NULL, 'THP/SL4 #11 Hyst', NULL, NULL, 'MXL4  11', NULL, NULL, 1, NULL),
(871, NULL, 'THP/SL4 #12 Hyst', NULL, NULL, 'MXL4  12', NULL, NULL, 1, NULL),
(872, NULL, 'THP/SL4 #13 Hyst', NULL, NULL, 'MXL4  13', NULL, NULL, 1, NULL),
(873, NULL, 'THP/SL4 #14 Huabang', NULL, NULL, 'MXL4  14', NULL, NULL, 1, NULL),
(874, NULL, 'THP/SL4 #15 Hyst', NULL, NULL, 'MXL4  15', NULL, NULL, 1, NULL),
(875, NULL, 'THP/SL4 #16 Hyst', NULL, NULL, 'MXL4  16', NULL, NULL, 1, NULL),
(876, NULL, 'THP/SL4 #17 Hyst', NULL, NULL, 'MXL4  17', NULL, NULL, 1, NULL),
(877, NULL, 'THP/SL4 #18 Hyst', NULL, NULL, 'MXL4  18', NULL, NULL, 1, NULL),
(878, NULL, 'THP/SL5 #1 Hyst', NULL, NULL, 'MXL5  01', NULL, NULL, 1, NULL),
(879, NULL, 'THP/SL5 #2 Hyst', NULL, NULL, 'MXL5  02', NULL, NULL, 1, NULL),
(880, NULL, 'THP/SL5 #3 Hyst', NULL, NULL, 'MXL5  03', NULL, NULL, 1, NULL),
(881, NULL, 'THP/SL5 #4 Hyst', NULL, NULL, 'MXL5  04', NULL, NULL, 1, NULL),
(882, NULL, 'THP SL 6-Axle #1 Goldhofer', NULL, NULL, 'MXL6  01', NULL, NULL, 1, NULL),
(883, NULL, 'THP SL 6-Axle #2 Goldhofer', NULL, NULL, 'MXL6  02', NULL, NULL, 1, NULL),
(884, NULL, 'THP SL 6-Axle #3 Faymonville', NULL, NULL, 'MXL6  03', NULL, NULL, 1, NULL),
(885, NULL, 'THP SL 6-Axle #4 Goldhofer', NULL, NULL, 'MXL6  04', NULL, NULL, 1, NULL),
(886, NULL, 'THP SL 6-Axle #5 Goldhofer', NULL, NULL, 'MXL6  05', NULL, NULL, 1, NULL),
(887, NULL, 'THP SL 6-Axle #6 Faymonville', NULL, NULL, 'MXL6  06', NULL, NULL, 1, NULL),
(888, NULL, 'THP SL 6-Axle #7 Hyst', NULL, NULL, 'MXL6  07', NULL, NULL, 1, NULL),
(889, NULL, 'THP SL 6-Axle #8 Hyst', NULL, NULL, 'MXL6  08', NULL, NULL, 1, NULL),
(890, NULL, 'THP SL 6-Axle #9 Hyst', NULL, NULL, 'MXL6  09', NULL, NULL, 1, NULL),
(891, NULL, 'THP SL 6-Axle #10 Hyst', NULL, NULL, 'MXL6  10', NULL, NULL, 1, NULL),
(892, NULL, 'THP SL 6-Axle #11 Hyst', NULL, NULL, 'MXL6  11', NULL, NULL, 1, NULL),
(893, NULL, 'THP SL 6-Axle #12 Hyst', NULL, NULL, 'MXL6  12', NULL, NULL, 1, NULL),
(894, NULL, 'THP SL 6-Axle #13 Hyst', NULL, NULL, 'MXL6  13', NULL, NULL, 1, NULL),
(895, NULL, 'THP/SL6 Split #4 Huabang', NULL, NULL, 'MXL6S 04', NULL, NULL, 1, NULL),
(896, NULL, 'PST 4 Axles-E #1 Goldhofer', NULL, NULL, 'MXLE4 01', NULL, NULL, 1, NULL),
(897, NULL, 'PST 4 Axles-E #2 Goldhofer', NULL, NULL, 'MXLE4 02', NULL, NULL, 1, NULL),
(898, NULL, 'PST 6-Axle #1 Goldhofer', NULL, NULL, 'MXLP6 01', NULL, NULL, 1, NULL),
(899, NULL, 'PST 6-Axle #2 Goldhofer', NULL, NULL, 'MXLP6 02', NULL, NULL, 1, NULL),
(900, NULL, 'THP/SL4 Split #1 Hyst', NULL, NULL, 'MXLS4 01', NULL, NULL, 1, NULL),
(901, NULL, 'THP/SL4 Split #2 Hyst', NULL, NULL, 'MXLS4 02', NULL, NULL, 1, NULL),
(902, NULL, 'THP/SL4 Split #3 Hyst', NULL, NULL, 'MXLS4 03', NULL, NULL, 1, NULL),
(903, NULL, 'THP/SL4 Split #4 Hyst', NULL, NULL, 'MXLS4 04', NULL, NULL, 1, NULL),
(904, NULL, 'THP/SL4 Split #5 Huabang', NULL, NULL, 'MXLS4 05', NULL, NULL, 1, NULL),
(905, NULL, 'THP/SL6 Split #1  Hyst', NULL, NULL, 'MXLS6 01', NULL, NULL, 1, NULL),
(906, NULL, 'THP/SL6 Split #2  Hyst', NULL, NULL, 'MXLS6 02', NULL, NULL, 1, NULL),
(907, NULL, 'THP/SL6 Split #3 Hyst', NULL, NULL, 'MXLS6 03', NULL, NULL, 1, NULL),
(908, NULL, 'THP UT 4-Axle #1 Goldhofer', NULL, NULL, 'MXLU4 01', NULL, NULL, 1, NULL),
(909, NULL, 'THP UT 4-Axle #2 Goldhofer', NULL, NULL, 'MXLU4 02', NULL, NULL, 1, NULL),
(910, NULL, 'THP UT 4-Axle #3 Goldhofer', NULL, NULL, 'MXLU4 03', NULL, NULL, 1, NULL),
(911, NULL, 'ASIAN MANDAUE YARD', NULL, NULL, 'MY', NULL, NULL, 1, NULL),
(912, NULL, 'Tower Light 2.2KVA Nishio #1', NULL, NULL, 'N220  01', NULL, NULL, 1, NULL),
(913, NULL, 'Tower Light 2.2KVA Nishio #2', NULL, NULL, 'N220  02', NULL, NULL, 1, NULL),
(914, NULL, 'Tower Light 2.2KVA Nishio #3', NULL, NULL, 'N220  03', NULL, NULL, 1, NULL),
(915, NULL, 'Tower Light 2.2KVA Nishio #4', NULL, NULL, 'N220  04', NULL, NULL, 1, NULL),
(916, NULL, 'Tower Light 2.2KVA Nishio #5', NULL, NULL, 'N220  05', NULL, NULL, 1, NULL),
(917, NULL, 'Tower Light 2.2KVA Nishio #6', NULL, NULL, 'N220  06', NULL, NULL, 1, NULL),
(918, NULL, 'Tower Light 2.2KVA Nishio #7', NULL, NULL, 'N220  07', NULL, NULL, 1, NULL),
(919, NULL, 'Metro Manila', NULL, NULL, 'NCR01', NULL, NULL, 1, NULL),
(920, NULL, 'Makati', NULL, NULL, 'NCR010', NULL, NULL, 1, NULL),
(921, NULL, 'Manila', NULL, NULL, 'NCR011', NULL, NULL, 1, NULL),
(922, NULL, 'Mandaluyong', NULL, NULL, 'NCR012', NULL, NULL, 1, NULL),
(923, NULL, 'San Juan', NULL, NULL, 'NCR013', NULL, NULL, 1, NULL),
(924, NULL, 'Pasay', NULL, NULL, 'NCR014', NULL, NULL, 1, NULL),
(925, NULL, 'Paranaque', NULL, NULL, 'NCR015', NULL, NULL, 1, NULL),
(926, NULL, 'Las Pinas', NULL, NULL, 'NCR016', NULL, NULL, 1, NULL),
(927, NULL, 'Muntinlupa', NULL, NULL, 'NCR017', NULL, NULL, 1, NULL),
(928, NULL, 'Caloocan', NULL, NULL, 'NCR02', NULL, NULL, 1, NULL),
(929, NULL, 'Malabon', NULL, NULL, 'NCR03', NULL, NULL, 1, NULL),
(930, NULL, 'Navotas', NULL, NULL, 'NCR04', NULL, NULL, 1, NULL),
(931, NULL, 'Valenzuela', NULL, NULL, 'NCR05', NULL, NULL, 1, NULL),
(932, NULL, 'Quezon City', NULL, NULL, 'NCR06', NULL, NULL, 1, NULL),
(933, NULL, 'Marikina', NULL, NULL, 'NCR07', NULL, NULL, 1, NULL),
(934, NULL, 'Pasig', NULL, NULL, 'NCR08', NULL, NULL, 1, NULL),
(935, NULL, 'Taguig', NULL, NULL, 'NCR09', NULL, NULL, 1, NULL),
(936, NULL, 'CTM-Kato NK-300', NULL, NULL, 'NK-300', NULL, NULL, 1, NULL),
(937, NULL, 'CTM-Kato NK-350', NULL, NULL, 'NK-350', NULL, NULL, 1, NULL),
(938, NULL, 'CTM-Kato NK-450 #1', NULL, NULL, 'NK-450#1', NULL, NULL, 1, NULL),
(939, NULL, 'CTM-Kato NK-450 #3', NULL, NULL, 'NK-450#3', NULL, NULL, 1, NULL),
(940, NULL, 'CTM-Kato NK-500', NULL, NULL, 'NK-500', NULL, NULL, 1, NULL),
(941, NULL, 'CTM-Kato NK-800', NULL, NULL, 'NK-800', NULL, NULL, 1, NULL),
(942, NULL, 'NONE', NULL, NULL, 'NONE', NULL, NULL, 1, NULL),
(943, NULL, 'FK-Clark Omega 25T', NULL, NULL, 'Omega25T', NULL, NULL, 1, NULL),
(944, NULL, 'FL-Omega 28C', NULL, NULL, 'Omega28', NULL, NULL, 0, NULL),
(945, NULL, 'PADACO', NULL, NULL, 'PADACO', NULL, NULL, 1, NULL),
(946, NULL, 'Puting Bato', NULL, NULL, 'PB', NULL, NULL, 1, NULL),
(947, NULL, 'AC-PDS-175S', NULL, NULL, 'PDS-175S', NULL, NULL, 1, NULL),
(948, NULL, 'AC-PDS-370S', NULL, NULL, 'PDS-370S', NULL, NULL, 1, NULL),
(949, NULL, 'AC-PDS-390S', NULL, NULL, 'PDS-390S', NULL, NULL, 1, NULL),
(950, NULL, 'AC-PDS-655S', NULL, NULL, 'PDS-655S', NULL, NULL, 1, NULL),
(951, NULL, 'AC-PDSF-750S #1', NULL, NULL, 'PDS750S1', NULL, NULL, 1, NULL),
(952, NULL, 'AC-PDSF-750S #2', NULL, NULL, 'PDS750S2', NULL, NULL, 1, NULL),
(953, NULL, 'EX-Komatsu PW-60 wheel type', NULL, NULL, 'PW-60 WT', NULL, NULL, 1, NULL),
(954, NULL, 'Zoom Lattice Crawler 180T #1', NULL, NULL, 'QUY180#1', NULL, NULL, 1, NULL),
(955, NULL, 'Zoom Lattice Crawler 180T #2', NULL, NULL, 'QUY180#2', NULL, NULL, 1, NULL),
(956, NULL, 'XCMG Crawler QUY55#1', NULL, NULL, 'QUY55#1', NULL, NULL, 1, NULL),
(957, NULL, 'XCMG Crawler QUY55#2', NULL, NULL, 'QUY55#2', NULL, NULL, 1, NULL),
(958, NULL, 'Zoom Lattice Crawler 650T #1', NULL, NULL, 'QUY650#1', NULL, NULL, 1, NULL),
(959, NULL, 'XCMG Truck Crane 8 Tons #1', NULL, NULL, 'QY8B#1', NULL, NULL, 1, NULL),
(960, NULL, 'XCMG Truck Crane 8 Tons #2', NULL, NULL, 'QY8B#2', NULL, NULL, 1, NULL),
(961, NULL, 'XCMG Truck Crane 8 Tons #3', NULL, NULL, 'QY8B#3', NULL, NULL, 1, NULL),
(962, NULL, 'XC 8T crane QY8B5 (ASCs)', NULL, NULL, 'QY8B5#1', NULL, NULL, 0, NULL),
(963, NULL, 'RAPAVIA ENTERPRISES', NULL, NULL, 'RAPAVIA', NULL, NULL, 1, NULL),
(964, NULL, 'ROYALE CAVITE PROPERTIES INC.', NULL, NULL, 'RCPI', NULL, NULL, 1, NULL),
(965, NULL, 'Dumptruck - Blue', NULL, NULL, 'RDT639', NULL, NULL, 0, NULL),
(966, NULL, 'RAVAGO EQUIPMENT RENTALS INC', NULL, NULL, 'RERI', NULL, NULL, 1, NULL),
(967, NULL, 'Grove RT530E-2 #1', NULL, NULL, 'RT530 #1', NULL, NULL, 1, NULL),
(968, NULL, 'Grove RT530E-2 #2', NULL, NULL, 'RT530 #2', NULL, NULL, 1, NULL),
(969, NULL, 'Grove RT530E-2 #3', NULL, NULL, 'RT530 #3', NULL, NULL, 1, NULL),
(970, NULL, 'Zoomlion RT-551 #1', NULL, NULL, 'RT-551#1', NULL, NULL, 1, NULL),
(971, NULL, 'Zoomlion RT-551 #2', NULL, NULL, 'RT-551#2', NULL, NULL, 1, NULL),
(972, NULL, 'Zoomlion RT-551 #3', NULL, NULL, 'RT-551#3', NULL, NULL, 1, NULL),
(973, NULL, 'Zoomlion RT-551 #4', NULL, NULL, 'RT-551#4', NULL, NULL, 1, NULL),
(974, NULL, 'Zoomlion RT-60 #1', NULL, NULL, 'RT60#1', NULL, NULL, 1, NULL),
(975, NULL, 'XCMG RT70E#1', NULL, NULL, 'RT70E #1', NULL, NULL, 1, NULL),
(976, NULL, 'XCMG RT70E#2', NULL, NULL, 'RT70E #2', NULL, NULL, 1, NULL),
(977, NULL, 'Grove RT750 #1', NULL, NULL, 'RT750 #1', NULL, NULL, 1, NULL),
(978, NULL, 'Grove RT750 #2', NULL, NULL, 'RT750 #2', NULL, NULL, 1, NULL),
(979, NULL, 'Grove RT750 #3', NULL, NULL, 'RT750#3', NULL, NULL, 1, NULL),
(980, NULL, 'Grove RT750 #4', NULL, NULL, 'RT750#4', NULL, NULL, 1, NULL),
(981, NULL, 'Grove RT750 #5', NULL, NULL, 'RT750#5', NULL, NULL, 1, NULL),
(982, NULL, 'Grove RT 765E-2 #1', NULL, NULL, 'RT765E#1', NULL, NULL, 1, NULL),
(983, NULL, 'Grove RT 765E-2 #2', NULL, NULL, 'RT765E#2', NULL, NULL, 1, NULL),
(984, NULL, 'Grove RT765E-2 #3', NULL, NULL, 'RT765E#3', NULL, NULL, 1, NULL),
(985, NULL, 'Grove RT765E-2 #4', NULL, NULL, 'RT765E#4', NULL, NULL, 1, NULL),
(986, NULL, 'Grove RT765E-2 #5', NULL, NULL, 'RT765E#5', NULL, NULL, 1, NULL),
(987, NULL, 'Grove RT765E-2 #6', NULL, NULL, 'RT765E#6', NULL, NULL, 1, NULL),
(988, NULL, 'Grove RT765E-2 #7', NULL, NULL, 'RT765E#7', NULL, NULL, 1, NULL),
(989, NULL, 'Grove RT765E-2 #8', NULL, NULL, 'RT765E#8', NULL, NULL, 1, NULL),
(990, NULL, 'Grove RT 865 #1', NULL, NULL, 'RT865#1', NULL, NULL, 1, NULL),
(991, NULL, 'Grove RT865 #2', NULL, NULL, 'RT865#2', NULL, NULL, 1, NULL),
(992, NULL, 'Grove RT-890 #2', NULL, NULL, 'RT890#2', NULL, NULL, 1, NULL),
(993, NULL, 'Grove RT-890 #3', NULL, NULL, 'RT890#3', NULL, NULL, 1, NULL),
(994, NULL, 'Grove RT-890 #4', NULL, NULL, 'RT890#4', NULL, NULL, 1, NULL),
(995, NULL, 'Grove RT-890 #5', NULL, NULL, 'RT890#5', NULL, NULL, 1, NULL),
(996, NULL, 'Grove RT-890E', NULL, NULL, 'RT890E', NULL, NULL, 1, NULL),
(997, NULL, 'Bobcat Skid Steer Loader #1', NULL, NULL, 'S175 #1', NULL, NULL, 1, NULL),
(998, NULL, 'Bobcat Skid Steer Loader #2', NULL, NULL, 'S175 #2', NULL, NULL, 1, NULL),
(999, NULL, 'Sumitomo Crawler SCX2800-2 #1', NULL, NULL, 'SCX280#1', NULL, NULL, 1, NULL),
(1000, NULL, 'Sumitomo Crawler SCX2800-2 #2', NULL, NULL, 'SCX280#2', NULL, NULL, 1, NULL),
(1001, NULL, 'Bulldozer SD 16 #1', NULL, NULL, 'SD16#1', NULL, NULL, 1, NULL),
(1002, NULL, 'GEN-Airman SDG150S #1', NULL, NULL, 'SDG150S1', NULL, NULL, 1, NULL),
(1003, NULL, 'GEN-Airman SDG150S #2', NULL, NULL, 'SDG150S2', NULL, NULL, 1, NULL),
(1004, NULL, 'GEN - Airman SDG150S #3', NULL, NULL, 'SDG150S3', NULL, NULL, 1, NULL),
(1005, NULL, 'GEN - Airman SDG150S #4', NULL, NULL, 'SDG150S4', NULL, NULL, 1, NULL),
(1006, NULL, 'GEN - Airman SDG150S #5', NULL, NULL, 'SDG150S5', NULL, NULL, 1, NULL),
(1007, NULL, 'GEN - Airman SDG150S #6', NULL, NULL, 'SDG150S6', NULL, NULL, 1, NULL),
(1008, NULL, 'GEN-Airman SDG220S #1', NULL, NULL, 'SDG220S', NULL, NULL, 1, NULL),
(1009, NULL, 'GEN-Airman SDG220S #2', NULL, NULL, 'SDG220S2', NULL, NULL, 1, NULL),
(1010, NULL, 'GEN-Airman SDG400S', NULL, NULL, 'SDG400S', NULL, NULL, 1, NULL),
(1011, NULL, 'Service Vehicles', NULL, NULL, 'SERVICE', NULL, NULL, 1, NULL),
(1012, NULL, 'Yuchai Genset 150 KVA #1', NULL, NULL, 'SG150#1', NULL, NULL, 1, NULL),
(1013, NULL, 'Xingnuo Genset 150 KVA #10', NULL, NULL, 'SG150#10', NULL, NULL, 1, NULL),
(1014, NULL, 'Yuchai Genset 150 KVA #2', NULL, NULL, 'SG150#2', NULL, NULL, 1, NULL),
(1015, NULL, 'Yuchai Genset 150 KVA #3', NULL, NULL, 'SG150#3', NULL, NULL, 1, NULL),
(1016, NULL, 'Xingnuo Genset 150 KVA #4', NULL, NULL, 'SG150#4', NULL, NULL, 1, NULL),
(1017, NULL, 'Xingnuo Genset 150 KVA #5', NULL, NULL, 'SG150#5', NULL, NULL, 1, NULL),
(1018, NULL, 'Xingnuo Genset 150 KVA #6', NULL, NULL, 'SG150#6', NULL, NULL, 1, NULL),
(1019, NULL, 'Xingnuo Genset 150 KVA #7', NULL, NULL, 'SG150#7', NULL, NULL, 1, NULL),
(1020, NULL, 'Xingnuo Genset 150 KVA #8', NULL, NULL, 'SG150#8', NULL, NULL, 1, NULL),
(1021, NULL, 'Xingnuo Genset 150 KVA #9', NULL, NULL, 'SG150#9', NULL, NULL, 1, NULL),
(1022, NULL, 'Yuchai Genset 250 KVA #1', NULL, NULL, 'SG250#1', NULL, NULL, 1, NULL),
(1023, NULL, 'Yuchai Genset 250 KVA #2', NULL, NULL, 'SG250#2', NULL, NULL, 1, NULL),
(1024, NULL, 'Yuchai Genset 250 KVA #3', NULL, NULL, 'SG250#3', NULL, NULL, 1, NULL),
(1025, NULL, 'Yuchai Genset 250 KVA #4', NULL, NULL, 'SG250#4', NULL, NULL, 1, NULL),
(1026, NULL, 'Yuchai Genset 250 KVA #5', NULL, NULL, 'SG250#5', NULL, NULL, 1, NULL),
(1027, NULL, 'Denyo Genset 60KVA #1', NULL, NULL, 'SG60 #1', NULL, NULL, 1, NULL),
(1028, NULL, 'Xingnuo Genset 62.5 Kva #1', NULL, NULL, 'SG62 #1', NULL, NULL, 1, NULL),
(1029, NULL, 'Xingnuo Genset 62.5 kva #2', NULL, NULL, 'SG62 #2', NULL, NULL, 1, NULL),
(1030, NULL, 'Xingnuo Genset 62.5 kva #3', NULL, NULL, 'SG62 #3', NULL, NULL, 1, NULL),
(1031, NULL, 'Xingnuo Genset 62.5 kva #4', NULL, NULL, 'SG62 #4', NULL, NULL, 1, NULL),
(1032, NULL, 'Xingnuo Genset 62.5 kva #5', NULL, NULL, 'SG62 #5', NULL, NULL, 1, NULL),
(1033, NULL, 'Xingnuo Genset 62.5 kva #6', NULL, NULL, 'SG62 #6', NULL, NULL, 1, NULL),
(1034, NULL, 'Xingnuo Genset 62.5 kva #7', NULL, NULL, 'SG62 #7', NULL, NULL, 1, NULL),
(1035, NULL, 'Xingnuo Genset 62.5 kva #8', NULL, NULL, 'SG62 #8', NULL, NULL, 1, NULL),
(1036, NULL, 'Xingnuo Genset 62.5 kva #9', NULL, NULL, 'SG62 #9', NULL, NULL, 1, NULL),
(1037, NULL, 'Xingnuo Genset 62.5 KVA #10', NULL, NULL, 'SG62#10', NULL, NULL, 1, NULL),
(1038, NULL, 'Xingnuo Genset 62.5 KVA #11', NULL, NULL, 'SG62#11', NULL, NULL, 1, NULL),
(1039, NULL, 'Xingnuo Genset 62.5 KVA #12', NULL, NULL, 'SG62#12', NULL, NULL, 1, NULL),
(1040, NULL, 'Xingnuo Genset 62.5 KVA #13', NULL, NULL, 'SG62#13', NULL, NULL, 1, NULL),
(1041, NULL, 'Yuchai Genset 750 kva #1', NULL, NULL, 'SG750#1', NULL, NULL, 1, NULL),
(1042, NULL, 'EX-Sumitomo SH-200 crawler #1', NULL, NULL, 'SH-200#1', NULL, NULL, 1, NULL),
(1043, NULL, 'EX-Sumitomo SH-200 crawler #2', NULL, NULL, 'SH-200#2', NULL, NULL, 1, NULL),
(1044, NULL, 'EX-Sumitomo SH-200 crawler #3', NULL, NULL, 'SH-200#3', NULL, NULL, 1, NULL),
(1045, NULL, 'EX-Sumitomo SH-200 crawler #4', NULL, NULL, 'SH-200#4', NULL, NULL, 1, NULL),
(1046, NULL, 'Shacman HvyHaul Tractor 6x6 #1', NULL, NULL, 'SHH6X6#1', NULL, NULL, 1, NULL),
(1047, NULL, 'Shacman HvyHaul Tractor 6x6 #2', NULL, NULL, 'SHH6X6#2', NULL, NULL, 1, NULL),
(1048, NULL, 'Shacman HvyHaul Tractor 6x6 #3', NULL, NULL, 'SHH6X6#3', NULL, NULL, 1, NULL),
(1049, NULL, 'Shacman HvyHaul Tractor 6x6 #4', NULL, NULL, 'SHH6X6#4', NULL, NULL, 1, NULL),
(1050, NULL, 'Shacman HvyHaul Tractor 6x6 #5', NULL, NULL, 'SHH6X6#5', NULL, NULL, 1, NULL),
(1051, NULL, 'Shacman HvyHaul Tractor 6x6 #6', NULL, NULL, 'SHH6X6#6', NULL, NULL, 1, NULL),
(1052, NULL, 'Shacman HvyHaul Tractor 6x6 #7', NULL, NULL, 'SHH6X6#7', NULL, NULL, 1, NULL),
(1053, NULL, 'Shacman HvyHaul Tractor 6x6 #8', NULL, NULL, 'SHH6X6#8', NULL, NULL, 1, NULL),
(1054, NULL, 'SILANG LOT 1 (CGI)', NULL, NULL, 'SILANG1', NULL, NULL, 1, NULL),
(1055, NULL, 'SILANGAN LOT 2 (CGI)', NULL, NULL, 'SILANG2', NULL, NULL, 1, NULL),
(1056, NULL, 'Skyjack SJ85AJ #10', NULL, NULL, 'SJ85 #10', NULL, NULL, 1, NULL),
(1057, NULL, 'Skyjack SJ85AJ #11', NULL, NULL, 'SJ85 #11', NULL, NULL, 1, NULL),
(1058, NULL, 'Skyjack SJ85AJ #12', NULL, NULL, 'SJ85 #12', NULL, NULL, 1, NULL),
(1059, NULL, 'Skyjack SJ85AJ #13', NULL, NULL, 'SJ85 #13', NULL, NULL, 1, NULL),
(1060, NULL, 'Skyjack SJ85AJ #14', NULL, NULL, 'SJ85 #14', NULL, NULL, 1, NULL),
(1061, NULL, 'Skyjack SJ85AJ #15', NULL, NULL, 'SJ85 #15', NULL, NULL, 1, NULL),
(1062, NULL, 'Skyjack SJ85AJ #16', NULL, NULL, 'SJ85 #16', NULL, NULL, 1, NULL),
(1063, NULL, 'Skyjack SJ85AJ #1', NULL, NULL, 'SJ85AJ#1', NULL, NULL, 1, NULL),
(1064, NULL, 'Skyjack SJ85AJ #2', NULL, NULL, 'SJ85AJ#2', NULL, NULL, 1, NULL),
(1065, NULL, 'Skyjack SJ85AJ #3', NULL, NULL, 'SJ85AJ#3', NULL, NULL, 1, NULL),
(1066, NULL, 'Skyjack SJ85AJ #4', NULL, NULL, 'SJ85AJ#4', NULL, NULL, 1, NULL),
(1067, NULL, 'Skyjack SJ85AJ #5', NULL, NULL, 'SJ85AJ#5', NULL, NULL, 1, NULL),
(1068, NULL, 'Skyjack SJ85AJ #6', NULL, NULL, 'SJ85AJ#6', NULL, NULL, 1, NULL),
(1069, NULL, 'Skyjack SJ85AJ #7', NULL, NULL, 'SJ85AJ#7', NULL, NULL, 1, NULL),
(1070, NULL, 'Skyjack SJ85AJ #8', NULL, NULL, 'SJ85AJ#8', NULL, NULL, 1, NULL),
(1071, NULL, 'Skyjack SJ85AJ #9', NULL, NULL, 'SJ85AJ#9', NULL, NULL, 1, NULL),
(1072, NULL, 'EX-Kobelco SK-100W wheel type', NULL, NULL, 'SK-100WT', NULL, NULL, 1, NULL),
(1073, NULL, 'Jacking and Skidding', NULL, NULL, 'SKIDDING', NULL, NULL, 1, NULL),
(1074, NULL, 'Xin Jin You Semi Lowbed #1', NULL, NULL, 'SLT 01', NULL, NULL, 1, NULL),
(1075, NULL, 'Xin Jin You Semi Lowbed #2', NULL, NULL, 'SLT 02', NULL, NULL, 1, NULL),
(1076, NULL, 'Shunhe Semi Lowbed #3', NULL, NULL, 'SLT 03', NULL, NULL, 1, NULL),
(1077, NULL, 'Shunhe Semi Lowbed #4', NULL, NULL, 'SLT 04', NULL, NULL, 1, NULL),
(1078, NULL, 'Shunhe Semi Lowbed #5', NULL, NULL, 'SLT 05', NULL, NULL, 1, NULL),
(1079, NULL, 'Shunhe Semi Lowbed #6', NULL, NULL, 'SLT 06', NULL, NULL, 1, NULL),
(1080, NULL, 'FL-SMV/Stocka 25/1200', NULL, NULL, 'SMV 25', NULL, NULL, 0, NULL),
(1081, NULL, 'AP-Aichi SP-151 #1 Manlift 15m', NULL, NULL, 'SP 151', NULL, NULL, 1, NULL),
(1082, NULL, 'AP-Aichi SP-120', NULL, NULL, 'SP-120', NULL, NULL, 1, NULL),
(1083, NULL, 'AP-Aichi SP-150 #1', NULL, NULL, 'SP150#1', NULL, NULL, 1, NULL),
(1084, NULL, 'AP-Aichi SP-150 #2', NULL, NULL, 'SP150#2', NULL, NULL, 1, NULL),
(1085, NULL, 'AP-Aichi SP-150 #3', NULL, NULL, 'SP150#3', NULL, NULL, 1, NULL),
(1086, NULL, 'AP-Aichi SP-150 #4', NULL, NULL, 'SP150#4', NULL, NULL, 1, NULL),
(1087, NULL, 'AP Aichi SP-181', NULL, NULL, 'SP-181', NULL, NULL, 1, NULL),
(1088, NULL, 'AP-Aichi SP-210 #1', NULL, NULL, 'SP210#1', NULL, NULL, 1, NULL),
(1089, NULL, 'AP-Aichi SP-210 #2', NULL, NULL, 'SP210#2', NULL, NULL, 1, NULL),
(1090, NULL, 'AP-Aichi SP-210 #3', NULL, NULL, 'SP210#3', NULL, NULL, 1, NULL),
(1091, NULL, 'AP-Aichi SP-210 #4', NULL, NULL, 'SP210#4', NULL, NULL, 1, NULL),
(1092, NULL, 'SP-251 #1', NULL, NULL, 'SP251#1', NULL, NULL, 1, NULL),
(1093, NULL, 'SP-251 #2', NULL, NULL, 'SP251#2', NULL, NULL, 1, NULL),
(1094, NULL, 'AP-Aichi SP-300', NULL, NULL, 'SP-300', NULL, NULL, 1, NULL),
(1095, NULL, 'Hyst SL 6M Spacer #1', NULL, NULL, 'SPA6M 01', NULL, NULL, 1, NULL),
(1096, NULL, 'Hyst SL 6M Spacer #2', NULL, NULL, 'SPA6M 02', NULL, NULL, 1, NULL),
(1097, NULL, 'SPMT SL 8.4M Spacer #1', NULL, NULL, 'SPA8M 01', NULL, NULL, 1, NULL),
(1098, NULL, 'SPMT SL 8.4M Spacer #2', NULL, NULL, 'SPA8M 02', NULL, NULL, 1, NULL),
(1099, NULL, 'SPMT SL 8.4M Spacer #3', NULL, NULL, 'SPA8M 03', NULL, NULL, 1, NULL),
(1100, NULL, 'SPMT SL 8.4M Spacer #4', NULL, NULL, 'SPA8M 04', NULL, NULL, 1, NULL),
(1101, NULL, 'Hyst SL 9M Spacer #1', NULL, NULL, 'SPA9M 01', NULL, NULL, 1, NULL),
(1102, NULL, 'Hyst SL 9M Spacer #2', NULL, NULL, 'SPA9M 02', NULL, NULL, 1, NULL),
(1103, NULL, 'Hyst SL 9M Spacer #3', NULL, NULL, 'SPA9M 03', NULL, NULL, 1, NULL),
(1104, NULL, 'Hyst SL 9M Spacer #4', NULL, NULL, 'SPA9M 04', NULL, NULL, 1, NULL),
(1105, NULL, 'Hyst SPMT 4 Lines #1', NULL, NULL, 'SPMT4 01', NULL, NULL, 1, NULL),
(1106, NULL, 'Hyst SPMT 4 Lines #2', NULL, NULL, 'SPMT4 02', NULL, NULL, 1, NULL),
(1107, NULL, 'Hyst SPMT 4 Lines #3', NULL, NULL, 'SPMT4 03', NULL, NULL, 1, NULL),
(1108, NULL, 'Hyst SPMT 4 Lines #4', NULL, NULL, 'SPMT4 04', NULL, NULL, 1, NULL),
(1109, NULL, 'Hyst SPMT 4 Lines #5', NULL, NULL, 'SPMT4 05', NULL, NULL, 1, NULL),
(1110, NULL, 'Hyst SPMT 4 Lines #6', NULL, NULL, 'SPMT4 06', NULL, NULL, 1, NULL),
(1111, NULL, 'Hyst SPMT 6 Lines #1', NULL, NULL, 'SPMT6 01', NULL, NULL, 1, NULL),
(1112, NULL, 'Hyst SPMT 6 Lines #2', NULL, NULL, 'SPMT6 02', NULL, NULL, 1, NULL),
(1113, NULL, 'Hyst SPMT 6 Lines #3', NULL, NULL, 'SPMT6 03', NULL, NULL, 1, NULL),
(1114, NULL, 'Hyst SPMT 6 Lines #4', NULL, NULL, 'SPMT6 04', NULL, NULL, 1, NULL),
(1115, NULL, 'Hyst SPMT 6 Lines #5', NULL, NULL, 'SPMT6 05', NULL, NULL, 1, NULL),
(1116, NULL, 'Hyst SPMT 6 Lines #6', NULL, NULL, 'SPMT6 06', NULL, NULL, 1, NULL),
(1117, NULL, 'POWERPACK 330 01', NULL, NULL, 'SPMTPP 1', NULL, NULL, 1, NULL),
(1118, NULL, 'POWERPACK 330 02', NULL, NULL, 'SPMTPP 2', NULL, NULL, 1, NULL),
(1119, NULL, 'POWERPACK 330 03', NULL, NULL, 'SPMTPP 3', NULL, NULL, 1, NULL),
(1120, NULL, '\"Spreader Bar, 5m\"', NULL, NULL, 'SPREAD 5', NULL, NULL, 1, NULL),
(1121, NULL, '\"Spreader Bar, 9m\"', NULL, NULL, 'SPREAD 9', NULL, NULL, 1, NULL),
(1122, NULL, 'Kato SR700-L #1', NULL, NULL, 'SR700#1', NULL, NULL, 1, NULL),
(1123, NULL, 'Kato SR700-L #2', NULL, NULL, 'SR700#2', NULL, NULL, 1, NULL),
(1124, NULL, 'Kato SR700-L #3', NULL, NULL, 'SR700#3', NULL, NULL, 1, NULL),
(1125, NULL, 'Kato SR700-L #4', NULL, NULL, 'SR700#4', NULL, NULL, 1, NULL),
(1126, NULL, 'Sany RT Telescopic Cr 120T #1', NULL, NULL, 'SRC120#1', NULL, NULL, 1, NULL),
(1127, NULL, 'Sany SRC900C RT Crane #2', NULL, NULL, 'SRC900#2', NULL, NULL, 1, NULL),
(1128, NULL, 'Sany SRC900C RT Crane #3', NULL, NULL, 'SRC900#3', NULL, NULL, 1, NULL),
(1129, NULL, 'Sany SRC900C RT Crane #4', NULL, NULL, 'SRC900#4', NULL, NULL, 1, NULL),
(1130, NULL, 'Sany SRC900C RT Crane #5', NULL, NULL, 'SRC900#5', NULL, NULL, 1, NULL),
(1131, NULL, 'Sany SRC900C RT Crane #6', NULL, NULL, 'SRC900#6', NULL, NULL, 1, NULL),
(1132, NULL, 'Sany SRC900C RT Crane #7', NULL, NULL, 'SRC900#7', NULL, NULL, 1, NULL),
(1133, NULL, 'Sany SRC900C RT Crane #8', NULL, NULL, 'SRC900#8', NULL, NULL, 1, NULL),
(1134, NULL, 'Sany SRC900C RT Crane #1', NULL, NULL, 'SRC900C', NULL, NULL, 1, NULL),
(1135, NULL, 'SMART RIGS CRANE CORP', NULL, NULL, 'SRCC', NULL, NULL, 1, NULL),
(1136, NULL, 'STA ROSA', NULL, NULL, 'SRY', NULL, NULL, 1, NULL),
(1137, NULL, 'CRT-Kato SS-350', NULL, NULL, 'SS-350', NULL, NULL, 1, NULL),
(1138, NULL, 'CRT-Kato SS-350 #2', NULL, NULL, 'SS-350#2', NULL, NULL, 1, NULL),
(1139, NULL, 'CRT-Kato SS-500 #1', NULL, NULL, 'SS-500#1', NULL, NULL, 1, NULL),
(1140, NULL, 'CRT-Kato SS-500 #2', NULL, NULL, 'SS-500#2', NULL, NULL, 1, NULL),
(1141, NULL, 'Stool support', NULL, NULL, 'STOOL', NULL, NULL, 1, NULL),
(1142, NULL, 'Grove Manlift T65J', NULL, NULL, 'T65J #1', NULL, NULL, 1, NULL),
(1143, NULL, 'Grove Manlift T86J', NULL, NULL, 'T86J #1', NULL, NULL, 1, NULL),
(1144, NULL, 'Grove Manlift T86J #2', NULL, NULL, 'T86J#2', NULL, NULL, 1, NULL),
(1145, NULL, 'Tanza RCPI Lot', NULL, NULL, 'TANZA 1', NULL, NULL, 1, NULL),
(1146, NULL, 'FK-TCM FD28', NULL, NULL, 'TCM FD28', NULL, NULL, 0, NULL),
(1147, NULL, 'FL-TCM FD50', NULL, NULL, 'TCM FD50', NULL, NULL, 0, NULL),
(1148, NULL, 'TELROSA HOLDINGS', NULL, NULL, 'TELROSA', NULL, NULL, 1, NULL),
(1149, NULL, 'FL-TCM FD100 (new)', NULL, NULL, 'TFD100#1', NULL, NULL, 0, NULL),
(1150, NULL, 'FL-TCM FD100 (old)', NULL, NULL, 'TFD100#2', NULL, NULL, 0, NULL),
(1151, NULL, 'FL-TCM FD100 #3', NULL, NULL, 'TFD100#3', NULL, NULL, 0, NULL),
(1152, NULL, 'FL-TCM FD70Z7', NULL, NULL, 'TFD70Z7', NULL, NULL, 0, NULL),
(1153, NULL, 'CTM-Tadano TG-1600', NULL, NULL, 'TG-1600', NULL, NULL, 1, NULL),
(1154, NULL, 'CTM-Tadano TG-500', NULL, NULL, 'TG-500', NULL, NULL, 1, NULL),
(1155, NULL, 'Tractor Head #1', NULL, NULL, 'TH1', NULL, NULL, 1, NULL),
(1156, NULL, 'Tractor Head #10', NULL, NULL, 'TH10', NULL, NULL, 1, NULL),
(1157, NULL, 'Tractor Head #11', NULL, NULL, 'TH11', NULL, NULL, 1, NULL),
(1158, NULL, 'Tractor Head #12', NULL, NULL, 'TH12', NULL, NULL, 1, NULL),
(1159, NULL, 'Tractor Head #13', NULL, NULL, 'TH13', NULL, NULL, 1, NULL),
(1160, NULL, 'Tractor Head #14', NULL, NULL, 'TH14', NULL, NULL, 1, NULL),
(1161, NULL, 'Tractor Head #15', NULL, NULL, 'TH15', NULL, NULL, 1, NULL),
(1162, NULL, 'Tractor Head #16', NULL, NULL, 'TH16', NULL, NULL, 1, NULL),
(1163, NULL, 'Tractor Head #17', NULL, NULL, 'TH17', NULL, NULL, 1, NULL),
(1164, NULL, 'Tractor Head #18', NULL, NULL, 'TH18', NULL, NULL, 1, NULL),
(1165, NULL, 'Tractor Head #19', NULL, NULL, 'TH19', NULL, NULL, 1, NULL),
(1166, NULL, 'Tractor Head #2', NULL, NULL, 'TH2', NULL, NULL, 1, NULL),
(1167, NULL, 'Tractor Head #20', NULL, NULL, 'TH20', NULL, NULL, 1, NULL),
(1168, NULL, 'Tractor Head #21', NULL, NULL, 'TH21', NULL, NULL, 1, NULL),
(1169, NULL, 'Tractor Head #22', NULL, NULL, 'TH22', NULL, NULL, 1, NULL),
(1170, NULL, 'Tractor Head #23', NULL, NULL, 'TH23', NULL, NULL, 1, NULL),
(1171, NULL, 'Tractor Head #24', NULL, NULL, 'TH24', NULL, NULL, 1, NULL),
(1172, NULL, 'Tractor Head #25', NULL, NULL, 'TH25', NULL, NULL, 1, NULL),
(1173, NULL, 'Tractor Head #26', NULL, NULL, 'TH26', NULL, NULL, 1, NULL),
(1174, NULL, 'Tractor Head #27', NULL, NULL, 'TH27', NULL, NULL, 1, NULL),
(1175, NULL, 'Tractor Head #28', NULL, NULL, 'TH28', NULL, NULL, 1, NULL),
(1176, NULL, 'Tractor Head #29', NULL, NULL, 'TH29', NULL, NULL, 1, NULL),
(1177, NULL, 'Tractor Head #3', NULL, NULL, 'TH3', NULL, NULL, 1, NULL),
(1178, NULL, 'Tractor Head #30', NULL, NULL, 'TH30', NULL, NULL, 1, NULL),
(1179, NULL, 'Tractor Head #31', NULL, NULL, 'TH31', NULL, NULL, 1, NULL),
(1180, NULL, 'Tractor Head #32', NULL, NULL, 'TH32', NULL, NULL, 1, NULL),
(1181, NULL, 'Tractor Head #33', NULL, NULL, 'TH33', NULL, NULL, 1, NULL),
(1182, NULL, 'Tractor Head #34', NULL, NULL, 'TH34', NULL, NULL, 1, NULL),
(1183, NULL, 'Tractor Head #35', NULL, NULL, 'TH35', NULL, NULL, 1, NULL),
(1184, NULL, 'Tractor Head #36', NULL, NULL, 'TH36', NULL, NULL, 1, NULL),
(1185, NULL, 'Tractor Head #37', NULL, NULL, 'TH37', NULL, NULL, 1, NULL),
(1186, NULL, 'Tractor Head #38', NULL, NULL, 'TH38', NULL, NULL, 1, NULL),
(1187, NULL, 'Tractor Head #39', NULL, NULL, 'TH39', NULL, NULL, 1, NULL),
(1188, NULL, 'Tractor Head #4', NULL, NULL, 'TH4', NULL, NULL, 1, NULL),
(1189, NULL, 'Tractor Head #40', NULL, NULL, 'TH40', NULL, NULL, 1, NULL),
(1190, NULL, 'Tractor Head #41', NULL, NULL, 'TH41', NULL, NULL, 1, NULL),
(1191, NULL, 'Tractor Head #42', NULL, NULL, 'TH42', NULL, NULL, 1, NULL),
(1192, NULL, 'Tractor Head #43', NULL, NULL, 'TH43', NULL, NULL, 1, NULL),
(1193, NULL, 'Tractor Head #44', NULL, NULL, 'TH44', NULL, NULL, 1, NULL),
(1194, NULL, 'Tractor Head #45', NULL, NULL, 'TH45', NULL, NULL, 1, NULL),
(1195, NULL, 'Tractor Head #46', NULL, NULL, 'TH46', NULL, NULL, 1, NULL),
(1196, NULL, 'Tractor Head #47', NULL, NULL, 'TH47', NULL, NULL, 1, NULL),
(1197, NULL, 'Tractor Head #48', NULL, NULL, 'TH48', NULL, NULL, 1, NULL),
(1198, NULL, 'Tractor Head #49', NULL, NULL, 'TH49', NULL, NULL, 1, NULL),
(1199, NULL, 'Tractor Head #5', NULL, NULL, 'TH5', NULL, NULL, 1, NULL),
(1200, NULL, 'Tractor Head #50', NULL, NULL, 'TH50', NULL, NULL, 1, NULL),
(1201, NULL, 'Tractor Head #51', NULL, NULL, 'TH51', NULL, NULL, 1, NULL),
(1202, NULL, 'Tractor Head #52', NULL, NULL, 'TH52', NULL, NULL, 1, NULL),
(1203, NULL, 'Tractor Head #53', NULL, NULL, 'TH53', NULL, NULL, 1, NULL),
(1204, NULL, 'Tractor Head #54', NULL, NULL, 'TH54', NULL, NULL, 1, NULL),
(1205, NULL, 'Tractor Head #55', NULL, NULL, 'TH55', NULL, NULL, 1, NULL),
(1206, NULL, 'Tractor Head #56', NULL, NULL, 'TH56', NULL, NULL, 1, NULL),
(1207, NULL, 'Tractor Head #57', NULL, NULL, 'TH57', NULL, NULL, 1, NULL),
(1208, NULL, 'Tractor Head #58', NULL, NULL, 'TH58', NULL, NULL, 1, NULL),
(1209, NULL, 'Tractor Head #59', NULL, NULL, 'TH59', NULL, NULL, 1, NULL),
(1210, NULL, 'Tractor Head #6', NULL, NULL, 'TH6', NULL, NULL, 1, NULL),
(1211, NULL, 'Tractor Head #7', NULL, NULL, 'TH7', NULL, NULL, 1, NULL),
(1212, NULL, 'Tractor Head #8', NULL, NULL, 'TH8', NULL, NULL, 1, NULL),
(1213, NULL, 'Tractor Head #9', NULL, NULL, 'TH9', NULL, NULL, 1, NULL),
(1214, NULL, 'Multi Axle Trailer SL 1-10L', NULL, NULL, 'THPSL 10', NULL, NULL, 0, NULL),
(1215, NULL, 'Multi Axle Trailer SL 11-20L', NULL, NULL, 'THPSL 20', NULL, NULL, 0, NULL),
(1216, NULL, 'Multi Axle Trailer SL 21-30L', NULL, NULL, 'THPSL 30', NULL, NULL, 0, NULL),
(1217, NULL, 'Multi Axle Trailer SL 31-40L', NULL, NULL, 'THPSL 40', NULL, NULL, 0, NULL),
(1218, NULL, 'Multi Axle Trailer SL 41-50L', NULL, NULL, 'THPSL 50', NULL, NULL, 0, NULL),
(1219, NULL, 'Multi Axle Trailer SL 51-60L', NULL, NULL, 'THPSL 60', NULL, NULL, 0, NULL),
(1220, NULL, 'Multi Axle Trailer SL 61-70L', NULL, NULL, 'THPSL 70', NULL, NULL, 0, NULL),
(1221, NULL, 'Multi Axle Trailer SL 71-80L', NULL, NULL, 'THPSL 80', NULL, NULL, 0, NULL),
(1222, NULL, 'Multi Axle Trailer SL 81-90L', NULL, NULL, 'THPSL 90', NULL, NULL, 0, NULL),
(1223, NULL, 'Multi Axle Trailer SL 91-100L', NULL, NULL, 'THPSL100', NULL, NULL, 0, NULL),
(1224, NULL, 'Multi Axle Trailer SL 101-110L', NULL, NULL, 'THPSL110', NULL, NULL, 0, NULL),
(1225, NULL, 'Multi Axle Trailer SL 111-120L', NULL, NULL, 'THPSL120', NULL, NULL, 0, NULL),
(1226, NULL, 'Multi Axle Trailer UT 1-10L', NULL, NULL, 'THPUT 10', NULL, NULL, 0, NULL),
(1227, NULL, 'Multi Axle Trailer UT 11-20L', NULL, NULL, 'THPUT 20', NULL, NULL, 0, NULL),
(1228, NULL, 'Tower Light 5 KVA #1', NULL, NULL, 'TL 5 #1', NULL, NULL, 0, NULL),
(1229, NULL, 'Tower Light 5 KVA #2', NULL, NULL, 'TL 5 #2', NULL, NULL, 0, NULL),
(1230, NULL, 'Tower Light 5 KVA #3', NULL, NULL, 'TL 5 #3', NULL, NULL, 0, NULL),
(1231, NULL, 'Tower Light 5 KVA #4', NULL, NULL, 'TL 5 #4', NULL, NULL, 0, NULL),
(1232, NULL, 'Tower Light 5 KVA #5', NULL, NULL, 'TL 5 #5', NULL, NULL, 0, NULL),
(1233, NULL, 'Tower Light 5 KVA #6', NULL, NULL, 'TL 5 #6', NULL, NULL, 0, NULL),
(1234, NULL, 'Tower Light 5 KVA #7', NULL, NULL, 'TL 5 #7', NULL, NULL, 0, NULL),
(1235, NULL, 'Tower Light 13KVA #1', NULL, NULL, 'TL13 #1', NULL, NULL, 0, NULL),
(1236, NULL, 'Tower Light 13KVA #2', NULL, NULL, 'TL13 #2', NULL, NULL, 0, NULL),
(1237, NULL, 'Tower Light 13KVA #3', NULL, NULL, 'TL13 #3', NULL, NULL, 0, NULL),
(1238, NULL, 'Tower Light 13KVA #4', NULL, NULL, 'TL13 #4', NULL, NULL, 0, NULL),
(1239, NULL, 'Tower Light 13KVA #5', NULL, NULL, 'TL13 #5', NULL, NULL, 0, NULL),
(1240, NULL, 'Tower Light 13KVA #6', NULL, NULL, 'TL13 #6', NULL, NULL, 0, NULL),
(1241, NULL, 'CTM-Grove TM-1275 #1', NULL, NULL, 'TM1275#1', NULL, NULL, 1, NULL),
(1242, NULL, 'CTM-Grove TM-1275 #2', NULL, NULL, 'TM1275#2', NULL, NULL, 1, NULL),
(1243, NULL, 'CTM-Grove TM-875', NULL, NULL, 'TM-875', NULL, NULL, 1, NULL),
(1244, NULL, 'CTM-Grove TM-990E', NULL, NULL, 'TM-990E', NULL, NULL, 1, NULL),
(1245, NULL, 'TM - 9M Boom', NULL, NULL, 'TM-9m', NULL, NULL, 1, NULL),
(1246, NULL, 'Tower Light #1', NULL, NULL, 'TOWLT#1', NULL, NULL, 0, NULL),
(1247, NULL, 'Tower Light #2', NULL, NULL, 'TOWLT#2', NULL, NULL, 0, NULL),
(1248, NULL, 'Tower Light #3', NULL, NULL, 'TOWLT#3', NULL, NULL, 0, NULL),
(1249, NULL, 'Tower Light #4', NULL, NULL, 'TOWLT#4', NULL, NULL, 0, NULL),
(1250, NULL, 'Tower Light #5', NULL, NULL, 'TOWLT#5', NULL, NULL, 0, NULL),
(1251, NULL, 'Tower Light #6', NULL, NULL, 'TOWLT#6', NULL, NULL, 0, NULL),
(1252, NULL, 'CRT-Tadano TR-250', NULL, NULL, 'TR-250', NULL, NULL, 0, NULL),
(1253, NULL, 'CRT-Tadano TR-350 #1', NULL, NULL, 'TR-350#1', NULL, NULL, 1, NULL),
(1254, NULL, 'CRT-Tadano TR-350 #2', NULL, NULL, 'TR-350#2', NULL, NULL, 1, NULL),
(1255, NULL, 'CRT-Tadano TR-500', NULL, NULL, 'TR-500', NULL, NULL, 1, NULL),
(1256, NULL, 'Turbina Makiling Calamba Lot', NULL, NULL, 'TURBINA', NULL, NULL, 1, NULL),
(1257, NULL, 'Aklan', NULL, NULL, 'V01', NULL, NULL, 1, NULL),
(1258, NULL, 'Cebu-Toledo', NULL, NULL, 'V010', NULL, NULL, 1, NULL),
(1259, NULL, 'Cebu-San Fernando', NULL, NULL, 'V011', NULL, NULL, 1, NULL),
(1260, NULL, 'Eastern Samar', NULL, NULL, 'V012', NULL, NULL, 1, NULL),
(1261, NULL, 'Guimaras', NULL, NULL, 'V013', NULL, NULL, 1, NULL),
(1262, NULL, 'Iloilo', NULL, NULL, 'V014', NULL, NULL, 1, NULL),
(1263, NULL, 'Leyte', NULL, NULL, 'V015', NULL, NULL, 1, NULL),
(1264, NULL, 'Leyte-Ormoc', NULL, NULL, 'V016', NULL, NULL, 1, NULL),
(1265, NULL, 'Leyte-Isabel', NULL, NULL, 'V017', NULL, NULL, 1, NULL),
(1266, NULL, 'Negros Occidental', NULL, NULL, 'V018', NULL, NULL, 1, NULL),
(1267, NULL, 'Negros Occidental-Bacolod', NULL, NULL, 'V019', NULL, NULL, 1, NULL),
(1268, NULL, 'Antique', NULL, NULL, 'V02', NULL, NULL, 1, NULL),
(1269, NULL, 'Negros Oriental', NULL, NULL, 'V020', NULL, NULL, 1, NULL),
(1270, NULL, 'Negros Oriental-Manjuyod', NULL, NULL, 'V021', NULL, NULL, 1, NULL),
(1271, NULL, 'Negros Oriental-Ayungon', NULL, NULL, 'V022', NULL, NULL, 1, NULL),
(1272, NULL, 'Negros Oriental-Dumaguete', NULL, NULL, 'V023', NULL, NULL, 1, NULL),
(1273, NULL, 'Northern Samar', NULL, NULL, 'V024', NULL, NULL, 1, NULL),
(1274, NULL, 'Romblon', NULL, NULL, 'V025', NULL, NULL, 1, NULL),
(1275, NULL, 'Samar', NULL, NULL, 'V026', NULL, NULL, 1, NULL),
(1276, NULL, 'Siquijor', NULL, NULL, 'V027', NULL, NULL, 1, NULL),
(1277, NULL, 'Southern Leyte', NULL, NULL, 'V028', NULL, NULL, 1, NULL),
(1278, NULL, 'Biliran', NULL, NULL, 'V03', NULL, NULL, 1, NULL),
(1279, NULL, 'Bohol', NULL, NULL, 'V04', NULL, NULL, 1, NULL),
(1280, NULL, 'Camiguin', NULL, NULL, 'V05', NULL, NULL, 1, NULL),
(1281, NULL, 'Capiz', NULL, NULL, 'V06', NULL, NULL, 1, NULL),
(1282, NULL, 'Cebu', NULL, NULL, 'V07', NULL, NULL, 1, NULL),
(1283, NULL, 'Cebu-Mandaue', NULL, NULL, 'V08', NULL, NULL, 1, NULL),
(1284, NULL, 'Cebu-Naga', NULL, NULL, 'V09', NULL, NULL, 1, NULL),
(1285, NULL, 'VITAS 1', NULL, NULL, 'V1', NULL, NULL, 1, NULL),
(1286, NULL, 'VITAS 2', NULL, NULL, 'V2', NULL, NULL, 1, NULL),
(1287, NULL, 'Vitas 3 - Fortune', NULL, NULL, 'V3', NULL, NULL, 1, NULL),
(1288, NULL, 'VITAS 4 - Cold Storage', NULL, NULL, 'V4', NULL, NULL, 1, NULL),
(1289, NULL, 'VALENZUELA YARD', NULL, NULL, 'VAL', NULL, NULL, 1, NULL),
(1290, NULL, 'VALENZIA HOLDINGS INC', NULL, NULL, 'VHI', NULL, NULL, 1, NULL),
(1291, NULL, 'XCMG Vibratory Roller 12T #1', NULL, NULL, 'VR12#1', NULL, NULL, 1, NULL),
(1292, NULL, 'XCMG Vibratory Roller 14T #1', NULL, NULL, 'VR14#1', NULL, NULL, 1, NULL),
(1293, NULL, 'Vessel Bridge#1 (Faymon 8m+5m)', NULL, NULL, 'VSLBR #1', NULL, NULL, 0, NULL),
(1294, NULL, 'XCMG QY100 #1', NULL, NULL, 'X100#1', NULL, NULL, 1, NULL),
(1295, NULL, 'XCMG QY100 #2', NULL, NULL, 'X100#2', NULL, NULL, 1, NULL),
(1296, NULL, 'XCMG XBT 16MT #1', NULL, NULL, 'XBT16#1', NULL, NULL, 1, NULL),
(1297, NULL, 'XCMG XBT 16MT #10', NULL, NULL, 'XBT16#10', NULL, NULL, 1, NULL),
(1298, NULL, 'XCMG XBT 16MT #11', NULL, NULL, 'XBT16#11', NULL, NULL, 1, NULL),
(1299, NULL, 'XCMG XBT 16MT #12', NULL, NULL, 'XBT16#12', NULL, NULL, 1, NULL);
INSERT INTO `equipment_units` (`eqm_id`, `eqm_eqmm_id`, `eqm_name`, `eqm_vin`, `eqm_plate_num`, `eqm_prc_code`, `eqm_serial_num`, `eqm_engine`, `eqm_is_active`, `eqm_updated_at`) VALUES
(1300, NULL, 'XCMG XBT 16MT #13', NULL, NULL, 'XBT16#13', NULL, NULL, 1, NULL),
(1301, NULL, 'XCMG XBT 16MT #14', NULL, NULL, 'XBT16#14', NULL, NULL, 1, NULL),
(1302, NULL, 'XCMG XBT 16MT #15', NULL, NULL, 'XBT16#15', NULL, NULL, 1, NULL),
(1303, NULL, 'XCMG XBT 16MT #16', NULL, NULL, 'XBT16#16', NULL, NULL, 1, NULL),
(1304, NULL, 'XCMG XBT 16MT #17', NULL, NULL, 'XBT16#17', NULL, NULL, 1, NULL),
(1305, NULL, 'XCMG XBT 16MT #18', NULL, NULL, 'XBT16#18', NULL, NULL, 1, NULL),
(1306, NULL, 'XCMG XBT 16MT #19', NULL, NULL, 'XBT16#19', NULL, NULL, 1, NULL),
(1307, NULL, 'XCMG XBT 16MT #2', NULL, NULL, 'XBT16#2', NULL, NULL, 1, NULL),
(1308, NULL, 'XCMG XBT 16MT #20', NULL, NULL, 'XBT16#20', NULL, NULL, 1, NULL),
(1309, NULL, 'XCMG XBT 16MT #21', NULL, NULL, 'XBT16#21', NULL, NULL, 1, NULL),
(1310, NULL, 'XCMG XBT 16MT #22', NULL, NULL, 'XBT16#22', NULL, NULL, 1, NULL),
(1311, NULL, 'XCMG XBT 16MT #23', NULL, NULL, 'XBT16#23', NULL, NULL, 1, NULL),
(1312, NULL, 'XCMG XBT 16MT #24', NULL, NULL, 'XBT16#24', NULL, NULL, 1, NULL),
(1313, NULL, 'XCMG XBT 16MT #3', NULL, NULL, 'XBT16#3', NULL, NULL, 1, NULL),
(1314, NULL, 'XCMG XBT 16MT #4', NULL, NULL, 'XBT16#4', NULL, NULL, 1, NULL),
(1315, NULL, 'XCMG XBT 16MT #5', NULL, NULL, 'XBT16#5', NULL, NULL, 1, NULL),
(1316, NULL, 'XCMG XBT 16MT #6', NULL, NULL, 'XBT16#6', NULL, NULL, 1, NULL),
(1317, NULL, 'XCMG XBT 16MT #7', NULL, NULL, 'XBT16#7', NULL, NULL, 1, NULL),
(1318, NULL, 'XCMG XBT 16MT #8', NULL, NULL, 'XBT16#8', NULL, NULL, 1, NULL),
(1319, NULL, 'XCMG XBT 16MT #9', NULL, NULL, 'XBT16#9', NULL, NULL, 1, NULL),
(1320, NULL, 'XCMG QY8 #1', NULL, NULL, 'XC 8 #1', NULL, NULL, 0, NULL),
(1321, NULL, 'XCMG QY8 #2', NULL, NULL, 'XC 8 #2', NULL, NULL, 0, NULL),
(1322, NULL, 'XCMG QY8 #3', NULL, NULL, 'XC 8 #3', NULL, NULL, 0, NULL),
(1323, NULL, 'XCMG QY30 #1', NULL, NULL, 'XC30 #1', NULL, NULL, 1, NULL),
(1324, NULL, 'XCMG QY30 #10', NULL, NULL, 'XC30 #10', NULL, NULL, 1, NULL),
(1325, NULL, 'XCMG QY30 #11', NULL, NULL, 'XC30 #11', NULL, NULL, 1, NULL),
(1326, NULL, 'XCMG QY30 #12', NULL, NULL, 'XC30 #12', NULL, NULL, 1, NULL),
(1327, NULL, 'XCMG QY30 #13', NULL, NULL, 'XC30 #13', NULL, NULL, 1, NULL),
(1328, NULL, 'XCMG QY30 #14', NULL, NULL, 'XC30 #14', NULL, NULL, 1, NULL),
(1329, NULL, 'XCMG QY30 #15', NULL, NULL, 'XC30 #15', NULL, NULL, 1, NULL),
(1330, NULL, 'XCMG QY30 #16', NULL, NULL, 'XC30 #16', NULL, NULL, 1, NULL),
(1331, NULL, 'XCMG QY30 #17', NULL, NULL, 'XC30 #17', NULL, NULL, 1, NULL),
(1332, NULL, 'XCMG QY30 #18', NULL, NULL, 'XC30 #18', NULL, NULL, 1, NULL),
(1333, NULL, 'XCMG QY30 #19', NULL, NULL, 'XC30 #19', NULL, NULL, 1, NULL),
(1334, NULL, 'XCMG QY30 #2', NULL, NULL, 'XC30 #2', NULL, NULL, 1, NULL),
(1335, NULL, 'XCMG QY30 #20', NULL, NULL, 'XC30 #20', NULL, NULL, 1, NULL),
(1336, NULL, 'XCMG QY30 #3', NULL, NULL, 'XC30 #3', NULL, NULL, 1, NULL),
(1337, NULL, 'XCMG QY30 #4', NULL, NULL, 'XC30 #4', NULL, NULL, 1, NULL),
(1338, NULL, 'XCMG QY30 #5', NULL, NULL, 'XC30 #5', NULL, NULL, 1, NULL),
(1339, NULL, 'XCMG QY30 #6', NULL, NULL, 'XC30 #6', NULL, NULL, 1, NULL),
(1340, NULL, 'XCMG QY30 #7', NULL, NULL, 'XC30 #7', NULL, NULL, 1, NULL),
(1341, NULL, 'XCMG QY30 #8', NULL, NULL, 'XC30 #8', NULL, NULL, 1, NULL),
(1342, NULL, 'XCMG QY30 #9', NULL, NULL, 'XC30 #9', NULL, NULL, 1, NULL),
(1343, NULL, 'XCMG QY70 #1', NULL, NULL, 'XC70 #1', NULL, NULL, 1, NULL),
(1344, NULL, 'XCMG QY70 #2', NULL, NULL, 'XC70 #2', NULL, NULL, 0, NULL),
(1345, NULL, 'XCMG QY70 #3', NULL, NULL, 'XC70 #3', NULL, NULL, 1, NULL),
(1346, NULL, 'XCMG QY70 #4', NULL, NULL, 'XC70 #4', NULL, NULL, 1, NULL),
(1347, NULL, 'XCMG QY70 #10', NULL, NULL, 'XC70#10', NULL, NULL, 1, NULL),
(1348, NULL, 'XCMG QY70 #11', NULL, NULL, 'XC70#11', NULL, NULL, 1, NULL),
(1349, NULL, 'XCMG QY70 #12', NULL, NULL, 'XC70#12', NULL, NULL, 1, NULL),
(1350, NULL, 'XCMG QY70 #13', NULL, NULL, 'XC70#13', NULL, NULL, 1, NULL),
(1351, NULL, 'XCMG QY70 #14', NULL, NULL, 'XC70#14', NULL, NULL, 1, NULL),
(1352, NULL, 'XCMG QY70 #15', NULL, NULL, 'XC70#15', NULL, NULL, 1, NULL),
(1353, NULL, 'XCMG QY70 #16', NULL, NULL, 'XC70#16', NULL, NULL, 1, NULL),
(1354, NULL, 'XCMG QY70 #17', NULL, NULL, 'XC70#17', NULL, NULL, 1, NULL),
(1355, NULL, 'XCMG QY70 #18', NULL, NULL, 'XC70#18', NULL, NULL, 1, NULL),
(1356, NULL, 'XCMG QY70 #19', NULL, NULL, 'XC70#19', NULL, NULL, 1, NULL),
(1357, NULL, 'XCMG QY70 #20', NULL, NULL, 'XC70#20', NULL, NULL, 1, NULL),
(1358, NULL, 'XCMG QY70 #21', NULL, NULL, 'XC70#21', NULL, NULL, 1, NULL),
(1359, NULL, 'XCMG QY70 #22', NULL, NULL, 'XC70#22', NULL, NULL, 1, NULL),
(1360, NULL, 'XCMG QY70 #23', NULL, NULL, 'XC70#23', NULL, NULL, 1, NULL),
(1361, NULL, 'XCMG QY70 #5', NULL, NULL, 'XC70#5', NULL, NULL, 1, NULL),
(1362, NULL, 'XCMG QY70 #6', NULL, NULL, 'XC70#6', NULL, NULL, 1, NULL),
(1363, NULL, 'XCMG QY70 #7', NULL, NULL, 'XC70#7', NULL, NULL, 1, NULL),
(1364, NULL, 'XCMG QY70 #8', NULL, NULL, 'XC70#8', NULL, NULL, 1, NULL),
(1365, NULL, 'XCMG QY70 #9', NULL, NULL, 'XC70#9', NULL, NULL, 1, NULL),
(1366, NULL, 'XCMG All Terrain 260T #1', NULL, NULL, 'XCA260 1', NULL, NULL, 1, NULL),
(1367, NULL, 'XCMG All Terrain 260T #2', NULL, NULL, 'XCA260 2', NULL, NULL, 1, NULL),
(1368, NULL, 'XCMG All Terrain 260T #3', NULL, NULL, 'XCA260 3', NULL, NULL, 1, NULL),
(1369, NULL, 'XCMG Forklift 5T #1', NULL, NULL, 'XCF50 01', NULL, NULL, 1, NULL),
(1370, NULL, 'XCMG RT-70E #1', NULL, NULL, 'XCR70#1', NULL, NULL, 0, NULL),
(1371, NULL, 'XCMG RT-70E #2', NULL, NULL, 'XCR70#2', NULL, NULL, 0, NULL),
(1372, NULL, 'XCMG 150 Tons Crane #1', NULL, NULL, 'XCT150#1', NULL, NULL, 0, NULL),
(1373, NULL, 'XCMG 150 Tons Crane #2', NULL, NULL, 'XCT150#2', NULL, NULL, 0, NULL),
(1374, NULL, 'XCMG 150 Tons Crane #3', NULL, NULL, 'XCT150#3', NULL, NULL, 0, NULL),
(1375, NULL, 'XCMG 150 Tons Crane #4', NULL, NULL, 'XCT150#4', NULL, NULL, 0, NULL),
(1376, NULL, 'XCMG 150 Tons Crane #5', NULL, NULL, 'XCT150#5', NULL, NULL, 1, NULL),
(1377, NULL, 'XCMG 150 Tons Crane #6', NULL, NULL, 'XCT150#6', NULL, NULL, 0, NULL),
(1378, NULL, 'XCMG 150T Long Boom Crane #1', NULL, NULL, 'XCT150L1', NULL, NULL, 1, NULL),
(1379, NULL, 'XCMG 150T Long Boom Crane #2', NULL, NULL, 'XCT150L2', NULL, NULL, 1, NULL),
(1380, NULL, 'XCMG 150T Long Boom Crane #3', NULL, NULL, 'XCT150L3', NULL, NULL, 1, NULL),
(1381, NULL, 'XCMG 150T Long Boom Crane #4', NULL, NULL, 'XCT150L4', NULL, NULL, 1, NULL),
(1382, NULL, 'XCMG 150Tons Short Boom #1', NULL, NULL, 'XCT150S1', NULL, NULL, 1, NULL),
(1383, NULL, 'XCMG 150Tons Short Boom #2', NULL, NULL, 'XCT150S2', NULL, NULL, 1, NULL),
(1384, NULL, 'XCMG 150Tons Short Boom #3', NULL, NULL, 'XCT150S3', NULL, NULL, 1, NULL),
(1385, NULL, 'XCMG 150Tons Short Boom #4', NULL, NULL, 'XCT150S4', NULL, NULL, 1, NULL),
(1386, NULL, 'XCMG 150Tons Short Boom #5', NULL, NULL, 'XCT150S5', NULL, NULL, 1, NULL),
(1387, NULL, 'XCMG 150Tons Short Boom #6', NULL, NULL, 'XCT150S6', NULL, NULL, 1, NULL),
(1388, NULL, 'XCMG 80 Tons #1', NULL, NULL, 'XCT80#1', NULL, NULL, 1, NULL),
(1389, NULL, 'XCMG 80 Tons #2', NULL, NULL, 'XCT80#2', NULL, NULL, 1, NULL),
(1390, NULL, 'XCMG Excavator 1 Cu #1', NULL, NULL, 'XE215 01', NULL, NULL, 1, NULL),
(1391, NULL, 'XGMA 10T Forklift #1', NULL, NULL, 'XG5100#1', NULL, NULL, 1, NULL),
(1392, NULL, 'XGMA 10T Forklift #2', NULL, NULL, 'XG5100#2', NULL, NULL, 1, NULL),
(1393, NULL, 'XGMA 10T Forklift #3', NULL, NULL, 'XG5100#3', NULL, NULL, 1, NULL),
(1394, NULL, 'XGMA 10T Forklift #4', NULL, NULL, 'XG5100#4', NULL, NULL, 1, NULL),
(1395, NULL, 'XGMA 10T Forklift #5', NULL, NULL, 'XG5100#5', NULL, NULL, 1, NULL),
(1396, NULL, 'XGMA 10T Forklift #6', NULL, NULL, 'XG5100#6', NULL, NULL, 1, NULL),
(1397, NULL, 'XGMA 12 Ton FL #1', NULL, NULL, 'XG5120#1', NULL, NULL, 1, NULL),
(1398, NULL, 'XGMA 12 Ton FL #2', NULL, NULL, 'XG5120#2', NULL, NULL, 1, NULL),
(1399, NULL, 'XGMA 3.5 Ton FL #1', NULL, NULL, 'XG535#1', NULL, NULL, 1, NULL),
(1400, NULL, 'XGMA 3.5 Ton FL #2', NULL, NULL, 'XG535#2', NULL, NULL, 1, NULL),
(1401, NULL, 'XGMA 3.5 Ton FL #3', NULL, NULL, 'XG535#3', NULL, NULL, 1, NULL),
(1402, NULL, 'XGMA 3.5 Ton FL #4', NULL, NULL, 'XG535#4', NULL, NULL, 1, NULL),
(1403, NULL, 'XGMA 3.5 Ton FL #5', NULL, NULL, 'XG535#5', NULL, NULL, 1, NULL),
(1404, NULL, 'XGMA 3.5 Ton FL #6', NULL, NULL, 'XG535#6', NULL, NULL, 1, NULL),
(1405, NULL, 'XGMA 7.5 Ton FL #1', NULL, NULL, 'XG575#1', NULL, NULL, 1, NULL),
(1406, NULL, 'XGMA 7.5 Ton FL #2', NULL, NULL, 'XG575#2', NULL, NULL, 1, NULL),
(1407, NULL, 'XGMA 7.5 Ton FL #3', NULL, NULL, 'XG575#3', NULL, NULL, 1, NULL),
(1408, NULL, 'XGMA 7.5 Ton FL #4', NULL, NULL, 'XG575#4', NULL, NULL, 1, NULL),
(1409, NULL, 'XGMA Wheel Loader XG951H #1', NULL, NULL, 'XG951H#1', NULL, NULL, 1, NULL),
(1410, NULL, 'XGMA Wheel Loader XG951H #2', NULL, NULL, 'XG951H#2', NULL, NULL, 1, NULL),
(1411, NULL, 'XGMA Wheel Loader XG951H #3', NULL, NULL, 'XG951H#3', NULL, NULL, 0, NULL),
(1412, NULL, 'XCMG XGC 120T #1', NULL, NULL, 'XGC120#1', NULL, NULL, 1, NULL),
(1413, NULL, 'XCMG XGC 120T #2', NULL, NULL, 'XGC120#2', NULL, NULL, 1, NULL),
(1414, NULL, 'XCMG XGC 120T #3', NULL, NULL, 'XGC120#3', NULL, NULL, 1, NULL),
(1415, NULL, 'XCMG Lattice Crawler 800 tons', NULL, NULL, 'XGC12000', NULL, NULL, 1, NULL),
(1416, NULL, 'XCMG XGC130 #1', NULL, NULL, 'XGC130#1', NULL, NULL, 1, NULL),
(1417, NULL, 'XCMG XGC130 #2', NULL, NULL, 'XGC130#2', NULL, NULL, 1, NULL),
(1418, NULL, 'XCMG XGC130 #3', NULL, NULL, 'XGC130#3', NULL, NULL, 1, NULL),
(1419, NULL, 'XCMG XGC130 #4', NULL, NULL, 'XGC130#4', NULL, NULL, 1, NULL),
(1420, NULL, 'XCMG XGC300E #1', NULL, NULL, 'XGC300#1', NULL, NULL, 1, NULL),
(1421, NULL, 'XCMG XGC300E #2', NULL, NULL, 'XGC300#2', NULL, NULL, 1, NULL),
(1422, NULL, 'XCMG Crawler 350T#1', NULL, NULL, 'XGC350#1', NULL, NULL, 1, NULL),
(1423, NULL, 'XCMG Lattice Crawler 55Tons #1', NULL, NULL, 'XGC55 #1', NULL, NULL, 1, NULL),
(1424, NULL, 'XCMG XGC55T#1 Crawler (INACTIV', NULL, NULL, 'XGC55#1', NULL, NULL, 0, NULL),
(1425, NULL, 'XCMG XGC55T#2 Crawler (Inactiv', NULL, NULL, 'XGC55#2', NULL, NULL, 0, NULL),
(1426, NULL, 'XCMG Telecrawler 55 Tons #1', NULL, NULL, 'XGC55T 1', NULL, NULL, 1, NULL),
(1427, NULL, 'XCMG Telecrawler 55 Tons #2', NULL, NULL, 'XGC55T 2', NULL, NULL, 1, NULL),
(1428, NULL, 'XCMG XGC85#1', NULL, NULL, 'XGC85#1', NULL, NULL, 1, NULL),
(1429, NULL, 'XCMG XGC85#2', NULL, NULL, 'XGC85#2', NULL, NULL, 1, NULL),
(1430, NULL, 'XCMG XGC85#3', NULL, NULL, 'XGC85#3', NULL, NULL, 1, NULL),
(1431, NULL, 'XCMG XGC85#4', NULL, NULL, 'XGC85#4', NULL, NULL, 1, NULL),
(1432, NULL, 'XCMG XGC85#5', NULL, NULL, 'XGC85#5', NULL, NULL, 1, NULL),
(1433, NULL, 'XCMG Crawler Crane 1250T', NULL, NULL, 'XLC17000', NULL, NULL, 1, NULL),
(1434, NULL, 'GEN - Xingnuo 62.5 kva', NULL, NULL, 'XN62 #1', NULL, NULL, 0, NULL),
(1435, NULL, 'XCMG XT670-140 Telehandler #1', NULL, NULL, 'XT670#1', NULL, NULL, 1, NULL),
(1436, NULL, 'XCMG XT680-170 Telehandler #1', NULL, NULL, 'XT680#1', NULL, NULL, 1, NULL),
(1437, NULL, 'XCMG XT680-170 Telehandler #2', NULL, NULL, 'XT680#2', NULL, NULL, 1, NULL),
(1438, NULL, 'XCMG XT680-170 Telehandler #3', NULL, NULL, 'XT680#3', NULL, NULL, 1, NULL),
(1439, NULL, 'XCMG XT680-170 Telehandler #4', NULL, NULL, 'XT680#4', NULL, NULL, 1, NULL),
(1440, NULL, 'XCMG XT680-170 Telehandler #5', NULL, NULL, 'XT680#5', NULL, NULL, 1, NULL),
(1441, NULL, 'XCMG XT680-170 Telehandler #6', NULL, NULL, 'XT680#6', NULL, NULL, 1, NULL),
(1442, NULL, '50 NBB', NULL, NULL, 'Y01', NULL, NULL, 0, NULL),
(1443, NULL, '64 NBB', NULL, NULL, 'Y02', NULL, NULL, 0, NULL),
(1444, NULL, '73 C-3', NULL, NULL, 'Y03', NULL, NULL, 0, NULL),
(1445, NULL, 'Bataan', NULL, NULL, 'Y04', NULL, NULL, 0, NULL),
(1446, NULL, 'CGH Lot', NULL, NULL, 'Y05', NULL, NULL, 0, NULL),
(1447, NULL, 'Festival lot', NULL, NULL, 'Y06', NULL, NULL, 0, NULL),
(1448, NULL, 'Asian Mandaue Yard', NULL, NULL, 'Y07', NULL, NULL, 0, NULL),
(1449, NULL, 'Silangan Lot 1 (CGI)', NULL, NULL, 'Y08', NULL, NULL, 0, NULL),
(1450, NULL, 'Silangan Lot 2 (CGI)', NULL, NULL, 'Y09', NULL, NULL, 0, NULL),
(1451, NULL, 'Valenzuela Yard', NULL, NULL, 'Y10', NULL, NULL, 0, NULL),
(1452, NULL, 'VITAS 1', NULL, NULL, 'Y11', NULL, NULL, 0, NULL),
(1453, NULL, 'VITAS 2', NULL, NULL, 'Y12', NULL, NULL, 0, NULL),
(1454, NULL, '88 C-3', NULL, NULL, 'Y13', NULL, NULL, 0, NULL),
(1455, NULL, 'Sta Rosa', NULL, NULL, 'Y14', NULL, NULL, 0, NULL),
(1456, NULL, 'Harbour Center (LRI)', NULL, NULL, 'Y15', NULL, NULL, 0, NULL),
(1457, NULL, 'Yuchai Genset 150KVA #1', NULL, NULL, 'YU 150#1', NULL, NULL, 0, NULL),
(1458, NULL, 'Yuchai Genset 150KVA #2', NULL, NULL, 'YU 150#2', NULL, NULL, 0, NULL),
(1459, NULL, 'Yuchai Genset 150KVA #3', NULL, NULL, 'YU 150#3', NULL, NULL, 0, NULL),
(1460, NULL, 'Yuchai Genset 250KVA #1', NULL, NULL, 'YU 250#1', NULL, NULL, 0, NULL),
(1461, NULL, 'Yuchai Genset 250KVA #2', NULL, NULL, 'YU 250#2', NULL, NULL, 0, NULL),
(1462, NULL, 'Yuchai Genset 250KVA #3', NULL, NULL, 'YU 250#3', NULL, NULL, 0, NULL),
(1463, NULL, 'Yuchai Genset 250KVA #4', NULL, NULL, 'YU 250#4', NULL, NULL, 0, NULL),
(1464, NULL, 'Yuchai Genset 250KVA #5', NULL, NULL, 'YU 250#5', NULL, NULL, 0, NULL),
(1465, NULL, 'Zoomlion ZTC800V552 #1', NULL, NULL, 'Z80 #1', NULL, NULL, 1, NULL),
(1466, NULL, 'Zoomlion ZTC800V552 #10', NULL, NULL, 'Z80 #10', NULL, NULL, 1, NULL),
(1467, NULL, 'Zoomlion ZTC800V552 #11', NULL, NULL, 'Z80 #11', NULL, NULL, 1, NULL),
(1468, NULL, 'Zoomlion ZTC800V552 #12', NULL, NULL, 'Z80 #12', NULL, NULL, 1, NULL),
(1469, NULL, 'Zoomlion ZTC800V552 #13', NULL, NULL, 'Z80 #13', NULL, NULL, 1, NULL),
(1470, NULL, 'Zoomlion ZTC800V552 #14', NULL, NULL, 'Z80 #14', NULL, NULL, 1, NULL),
(1471, NULL, 'Zoomlion ZTC800V552 #15', NULL, NULL, 'Z80 #15', NULL, NULL, 1, NULL),
(1472, NULL, 'Zoomlion ZTC800V552 #16', NULL, NULL, 'Z80 #16', NULL, NULL, 1, NULL),
(1473, NULL, 'Zoomlion ZTC800V552 #2', NULL, NULL, 'Z80 #2', NULL, NULL, 1, NULL),
(1474, NULL, 'Zoomlion ZTC800V552 #3', NULL, NULL, 'Z80 #3', NULL, NULL, 1, NULL),
(1475, NULL, 'Zoomlion ZTC800V552 #4', NULL, NULL, 'Z80 #4', NULL, NULL, 1, NULL),
(1476, NULL, 'Zoomlion ZTC800V552 #5', NULL, NULL, 'Z80 #5', NULL, NULL, 1, NULL),
(1477, NULL, 'Zoomlion ZTC800V552 #6', NULL, NULL, 'Z80 #6', NULL, NULL, 1, NULL),
(1478, NULL, 'Zoomlion ZTC800V552 #7', NULL, NULL, 'Z80 #7', NULL, NULL, 1, NULL),
(1479, NULL, 'Zoomlion ZTC800V552 #8', NULL, NULL, 'Z80 #8', NULL, NULL, 1, NULL),
(1480, NULL, 'Zoomlion ZTC800V552 #9', NULL, NULL, 'Z80 #9', NULL, NULL, 1, NULL),
(1481, NULL, 'Zoomlion All Terrain Cr 800T#2', NULL, NULL, 'ZAT800#2', NULL, NULL, 1, NULL),
(1482, NULL, 'Zoomlion All Terrain Cr 800T', NULL, NULL, 'ZAT8000', NULL, NULL, 1, NULL),
(1483, NULL, 'Zoom Telecrawler 150T #1', NULL, NULL, 'ZCT150#1', NULL, NULL, 1, NULL),
(1484, NULL, 'Zoom Telecrawler 150T #2', NULL, NULL, 'ZCT150#2', NULL, NULL, 1, NULL),
(1485, NULL, 'Zoom Telescopic Crawler 90T #1', NULL, NULL, 'ZCT90#1', NULL, NULL, 1, NULL),
(1486, NULL, 'Zoom Telescopic Crawler 90T #2', NULL, NULL, 'ZCT90#2', NULL, NULL, 1, NULL),
(1487, NULL, 'Zoom 20.5T Backhoe #1', NULL, NULL, 'ZE205#1', NULL, NULL, 1, NULL),
(1488, NULL, 'Zoom 20.5T Backhoe #2', NULL, NULL, 'ZE205#2', NULL, NULL, 1, NULL),
(1489, NULL, 'Zoom 20.5T Backhoe #3', NULL, NULL, 'ZE205#3', NULL, NULL, 1, NULL),
(1490, NULL, 'Zoom 20.5T Backhoe #4', NULL, NULL, 'ZE205#4', NULL, NULL, 1, NULL),
(1491, NULL, 'Zoomlion Excavator ZE230ELR #1', NULL, NULL, 'ZE230 #1', NULL, NULL, 1, NULL),
(1492, NULL, 'Zoomlion Excavator ZE230ELR #2', NULL, NULL, 'ZE230 #2', NULL, NULL, 1, NULL),
(1493, NULL, 'Zoomlion Backhoe 1.2 cu m #1', NULL, NULL, 'ZE245#1', NULL, NULL, 1, NULL),
(1494, NULL, 'Zoomlion QY70V #1', NULL, NULL, 'Zoom70#1', NULL, NULL, 1, NULL),
(1495, NULL, 'Zoomlion QY70V #2', NULL, NULL, 'Zoom70#2', NULL, NULL, 1, NULL),
(1496, NULL, 'Zoomlion QY70V#3', NULL, NULL, 'Zoom70#3', NULL, NULL, 1, NULL),
(1497, NULL, 'Zoomlion QY70V#4', NULL, NULL, 'Zoom70#4', NULL, NULL, 1, NULL),
(1498, NULL, 'Zoomlion QY70V#5', NULL, NULL, 'Zoom70#5', NULL, NULL, 1, NULL),
(1499, NULL, 'Zoomlion QY70V#6', NULL, NULL, 'Zoom70#6', NULL, NULL, 1, NULL),
(1500, NULL, 'Zoomlion QY70V#7', NULL, NULL, 'Zoom70#7', NULL, NULL, 1, NULL),
(1501, NULL, 'Zoomlion QY70V#8', NULL, NULL, 'Zoom70#8', NULL, NULL, 1, NULL),
(1502, NULL, 'Zoom QUY180 #1', NULL, NULL, 'ZQ 180#1', NULL, NULL, 0, NULL),
(1503, NULL, 'Zoom QUY180#2', NULL, NULL, 'ZQ 180#2', NULL, NULL, 0, NULL),
(1504, NULL, 'Zoomlion QUY 650 Crawler #1', NULL, NULL, 'ZQ 650#1', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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

--
-- Dumping data for table `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(1, '6b1be511-9135-415a-ab61-e1774c2085e8', 'database', 'default', '{\"uuid\":\"6b1be511-9135-415a-ab61-e1774c2085e8\",\"displayName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"command\":\"O:29:\\\"App\\\\Jobs\\\\ProcessDueDateChecks\\\":0:{}\",\"batchId\":null},\"createdAt\":1779351354,\"delay\":null}', 'PDOException: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`emms`.`maintenance_tasks`, CONSTRAINT `maintenance_tasks_mt_status_id_foreign` FOREIGN KEY (`mt_status_id`) REFERENCES `statuses` (`status_id`)) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php:53\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(53): PDOStatement->execute()\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(827): Illuminate\\Database\\MySqlConnection->Illuminate\\Database\\{closure}(\'insert into `ma...\', Array)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `ma...\', Array, Object(Closure))\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `ma...\', Array, Object(Closure))\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4121): Illuminate\\Database\\MySqlConnection->insert(\'insert into `ma...\', Array)\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(106): Illuminate\\Database\\Query\\Builder->insert(Array)\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#47 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`emms`.`maintenance_tasks`, CONSTRAINT `maintenance_tasks_mt_status_id_foreign` FOREIGN KEY (`mt_status_id`) REFERENCES `statuses` (`status_id`)) (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: emms, SQL: insert into `maintenance_tasks` (`mt_batch`, `mt_by`, `mt_dep_id`, `mt_dt`, `mt_due_dt`, `mt_eqm_id`, `mt_eqm_log`, `mt_ets_id`, `mt_remarks`, `mt_scheduled_dt`, `mt_status_id`, `mt_task_id`, `mt_task_log`) values (152294ff-668d-40dd-9443-7c9761d70fbb, ?, 1, 2026-05-21 16:15:56, 2026-05-21 08:00:00, 6, 10 Wheeler Truck #2, 3, System-generated, 2026-05-21 08:00:00, pnd, 2, Change Oil)) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:838\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `ma...\', Array, Object(Closure))\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `ma...\', Array, Object(Closure))\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4121): Illuminate\\Database\\MySqlConnection->insert(\'insert into `ma...\', Array)\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(106): Illuminate\\Database\\Query\\Builder->insert(Array)\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#45 {main}', '2026-05-21 08:15:56'),
(2, 'b8818091-2b65-43dd-b03a-4b454c1c2ee1', 'database', 'default', '{\"uuid\":\"b8818091-2b65-43dd-b03a-4b454c1c2ee1\",\"displayName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"command\":\"O:29:\\\"App\\\\Jobs\\\\ProcessDueDateChecks\\\":0:{}\",\"batchId\":null},\"createdAt\":1779352007,\"delay\":null}', 'PDOException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:420\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(420): PDO->prepare(\'select `mt_id` ...\')\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(827): Illuminate\\Database\\Connection->Illuminate\\Database\\{closure}(\'select `mt_id` ...\', Array)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: emms, SQL: select `mt_id` from `maintenance_tasks` where `mt_batch` = eaf0c794-97d2-48c4-9c4d-f19dc244143d) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:838\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#48 {main}', '2026-05-21 08:26:49');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(3, 'f0d2b2cc-a02f-447b-951c-40c0506e2578', 'database', 'default', '{\"uuid\":\"f0d2b2cc-a02f-447b-951c-40c0506e2578\",\"displayName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"command\":\"O:29:\\\"App\\\\Jobs\\\\ProcessDueDateChecks\\\":0:{}\",\"batchId\":null},\"createdAt\":1779352030,\"delay\":null}', 'PDOException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:420\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(420): PDO->prepare(\'select `mt_id` ...\')\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(827): Illuminate\\Database\\Connection->Illuminate\\Database\\{closure}(\'select `mt_id` ...\', Array)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: emms, SQL: select `mt_id` from `maintenance_tasks` where `mt_batch` = b302f107-1ded-4334-8b79-c76ab0a1da04) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:838\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#48 {main}', '2026-05-21 08:27:12'),
(4, 'c1ba787d-929d-42c6-b45f-643c912bd6f9', 'database', 'default', '{\"uuid\":\"c1ba787d-929d-42c6-b45f-643c912bd6f9\",\"displayName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"command\":\"O:29:\\\"App\\\\Jobs\\\\ProcessDueDateChecks\\\":0:{}\",\"batchId\":null},\"createdAt\":1779352100,\"delay\":null}', 'PDOException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:420\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(420): PDO->prepare(\'select `mt_id` ...\')\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(827): Illuminate\\Database\\Connection->Illuminate\\Database\\{closure}(\'select `mt_id` ...\', Array)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: emms, SQL: select `mt_id` from `maintenance_tasks` where `mt_batch` = 12a635f5-0828-44d7-a041-d4d1e342149f) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:838\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#48 {main}', '2026-05-21 08:28:22');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(5, '9e98efd3-d9bb-4c97-a5f6-3cc8733bae26', 'database', 'default', '{\"uuid\":\"9e98efd3-d9bb-4c97-a5f6-3cc8733bae26\",\"displayName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessDueDateChecks\",\"command\":\"O:29:\\\"App\\\\Jobs\\\\ProcessDueDateChecks\\\":0:{}\",\"batchId\":null},\"createdAt\":1779352140,\"delay\":null}', 'PDOException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:420\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(420): PDO->prepare(\'select `mt_id` ...\')\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(827): Illuminate\\Database\\Connection->Illuminate\\Database\\{closure}(\'select `mt_id` ...\', Array)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'mt_batch\' in \'where clause\' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: emms, SQL: select `mt_id` from `maintenance_tasks` where `mt_batch` = b2ac68c7-86c2-40fc-8f81-9db3e3fee79f) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:838\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(794): Illuminate\\Database\\Connection->runQueryCallback(\'select `mt_id` ...\', Array, Object(Closure))\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(411): Illuminate\\Database\\Connection->run(\'select `mt_id` ...\', Array, Object(Closure))\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3505): Illuminate\\Database\\Connection->select(\'select `mt_id` ...\', Array, true)\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3763): Illuminate\\Database\\Query\\Builder->runSelect()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(4080): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3759): Illuminate\\Database\\Query\\Builder->onceWithColumns(Array, Object(Closure))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(110): Illuminate\\Database\\Query\\Builder->pluck(\'mt_id\')\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(68): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Support\\Collection), 1)\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(79): Illuminate\\Database\\Query\\Builder->chunk(200, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(42): App\\Jobs\\ProcessDueDateChecks->processEquipmentTasksSchedules(Object(Illuminate\\Support\\Carbon))\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\ManagesTransactions.php(35): App\\Jobs\\ProcessDueDateChecks->App\\Jobs\\{closure}(Object(Illuminate\\Database\\MySqlConnection))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\DatabaseManager.php(491): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Facade.php(363): Illuminate\\Database\\DatabaseManager->__call(\'transaction\', Array)\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\app\\Jobs\\ProcessDueDateChecks.php(41): Illuminate\\Support\\Facades\\Facade::__callStatic(\'transaction\', Array)\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessDueDateChecks->handle()\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessDueDateChecks), false)\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessDueDateChecks))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessDueDateChecks))\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#48 {main}', '2026-05-21 08:29:01'),
(6, '6d4e327a-41d0-4eb2-a5c5-ee8f1800d42c', 'database', 'default', '{\"uuid\":\"6d4e327a-41d0-4eb2-a5c5-ee8f1800d42c\",\"displayName\":\"App\\\\Mail\\\\WorkOrderConfirmationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderConfirmationMail\\\":3:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:11;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:8:\\\"priority\\\";i:2;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1780629401,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #233)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-05 03:16:45'),
(7, '2c528e4d-aa22-4c9f-988e-b43841fe2040', 'database', 'default', '{\"uuid\":\"2c528e4d-aa22-4c9f-988e-b43841fe2040\",\"displayName\":\"App\\\\Mail\\\\WorkOrderConfirmationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderConfirmationMail\\\":3:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:8:\\\"priority\\\";i:2;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1780630640,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #248)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-05 03:37:24');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(8, '64ad7d68-4747-4b2d-9572-211561f6c204', 'database', 'default', '{\"uuid\":\"64ad7d68-4747-4b2d-9572-211561f6c204\",\"displayName\":\"App\\\\Mail\\\\WorkOrderConfirmationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderConfirmationMail\\\":3:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:13;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:8:\\\"priority\\\";i:2;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1780637393,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #250)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-05 05:29:58'),
(9, '50e254b8-155d-4601-983a-fc749051ffd2', 'database', 'default', '{\"uuid\":\"50e254b8-155d-4601-983a-fc749051ffd2\",\"displayName\":\"App\\\\Mail\\\\WorkOrderConfirmationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderConfirmationMail\\\":3:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:14;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:8:\\\"priority\\\";i:2;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1780639315,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #252)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-05 06:01:57'),
(10, 'ff14d4ab-b5ff-461b-9b7a-1dbb93a90cd4', 'database', 'default', '{\"uuid\":\"ff14d4ab-b5ff-461b-9b7a-1dbb93a90cd4\",\"displayName\":\"App\\\\Mail\\\\WorkOrderConfirmationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderConfirmationMail\\\":3:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:15;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:8:\\\"priority\\\";i:2;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1780640718,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #254)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-05 06:25:22'),
(11, '6aff6bd2-3c7f-4443-924d-b2df43ee26af', 'database', 'default', '{\"uuid\":\"6aff6bd2-3c7f-4443-924d-b2df43ee26af\",\"displayName\":\"App\\\\Mail\\\\WorkOrderConfirmationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderConfirmationMail\\\":3:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:8:\\\"priority\\\";i:2;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781146550,\"delay\":null}', 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Mail\\WorkOrderConfirmationMail has been attempted too many times. in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\MaxAttemptsExceededException.php:24\nStack trace:\n#0 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(862): Illuminate\\Queue\\MaxAttemptsExceededException::forJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob))\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(573): Illuminate\\Queue\\Worker->maxAttemptsExceededException(Object(Illuminate\\Queue\\Jobs\\DatabaseJob))\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(474): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), 1)\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#21 {main}', '2026-06-15 00:19:22');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(12, '16a12c84-593d-4d8d-b686-b0176a8404fe', 'database', 'default', '{\"uuid\":\"16a12c84-593d-4d8d-b686-b0176a8404fe\",\"displayName\":\"App\\\\Mail\\\\WorkOrderAssignedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:30:\\\"App\\\\Mail\\\\WorkOrderAssignedMail\\\":4:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:8:\\\"priority\\\";i:2;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"worker\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:4;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:25:\\\"richard.vea@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781146550,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #261)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-15 00:19:25'),
(13, 'a669b0fa-c31e-4d4c-a08a-1d25878a7692', 'database', 'default', '{\"uuid\":\"a669b0fa-c31e-4d4c-a08a-1d25878a7692\",\"displayName\":\"App\\\\Mail\\\\InspectionConductedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:32:\\\"App\\\\Mail\\\\InspectionConductedMail\\\":6:{s:10:\\\"inspection\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\Inspection\\\";s:2:\\\"id\\\";i:47;s:9:\\\"relations\\\";a:4:{i:0;s:9:\\\"equipment\\\";i:1;s:11:\\\"conductedBy\\\";i:2;s:15:\\\"inspectionItems\\\";i:3;s:10:\\\"department\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:13:\\\"recipientType\\\";s:10:\\\"technician\\\";s:14:\\\"hasFailedItems\\\";b:1;s:11:\\\"failedItems\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\InspectionItem\\\";s:2:\\\"id\\\";a:1:{i:0;i:73;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:27:\\\"angelo.gaspar@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781147177,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #276)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.inspecti...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-15 00:19:27'),
(14, '7cb9ab26-3c4a-4129-baa4-f205755699ce', 'database', 'default', '{\"uuid\":\"7cb9ab26-3c4a-4129-baa4-f205755699ce\",\"displayName\":\"App\\\\Mail\\\\InspectionConductedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:32:\\\"App\\\\Mail\\\\InspectionConductedMail\\\":6:{s:10:\\\"inspection\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\Inspection\\\";s:2:\\\"id\\\";i:47;s:9:\\\"relations\\\";a:4:{i:0;s:9:\\\"equipment\\\";i:1;s:11:\\\"conductedBy\\\";i:2;s:15:\\\"inspectionItems\\\";i:3;s:10:\\\"department\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:13:\\\"recipientType\\\";s:7:\\\"manager\\\";s:14:\\\"hasFailedItems\\\";b:1;s:11:\\\"failedItems\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\InspectionItem\\\";s:2:\\\"id\\\";a:1:{i:0;i:73;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781147177,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #279)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.inspecti...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-15 00:19:29');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(15, 'e348494c-b4b3-4f1c-a436-162416930d00', 'database', 'default', '{\"uuid\":\"e348494c-b4b3-4f1c-a436-162416930d00\",\"displayName\":\"App\\\\Mail\\\\WorkOrderCancellationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderCancellationMail\\\":6:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:9:\\\"createdBy\\\";i:2;s:8:\\\"priority\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:9:\\\"canceller\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:2:{i:0;s:5:\\\"roles\\\";i:1;s:11:\\\"permissions\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"reason\\\";s:1:\\\"d\\\";s:13:\\\"recipientType\\\";s:7:\\\"manager\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781665486,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #223)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-17 03:04:49'),
(16, '80c12ddd-cbed-4b50-80b5-6badd3174891', 'database', 'default', '{\"uuid\":\"80c12ddd-cbed-4b50-80b5-6badd3174891\",\"displayName\":\"App\\\\Mail\\\\WorkOrderCancellationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderCancellationMail\\\":6:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:9:\\\"createdBy\\\";i:2;s:8:\\\"priority\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:9:\\\"canceller\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:2:{i:0;s:5:\\\"roles\\\";i:1;s:11:\\\"permissions\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"reason\\\";s:1:\\\"d\\\";s:13:\\\"recipientType\\\";s:10:\\\"technician\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:25:\\\"richard.vea@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781665486,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #239)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-17 03:04:51'),
(17, '5314de67-e7e7-4e0b-a793-b1889866fa02', 'database', 'default', '{\"uuid\":\"5314de67-e7e7-4e0b-a793-b1889866fa02\",\"displayName\":\"App\\\\Mail\\\\WorkOrderCancellationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderCancellationMail\\\":6:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:9:\\\"createdBy\\\";i:2;s:8:\\\"priority\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:9:\\\"canceller\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:2:{i:0;s:5:\\\"roles\\\";i:1;s:11:\\\"permissions\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"reason\\\";s:1:\\\"d\\\";s:13:\\\"recipientType\\\";s:10:\\\"technician\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:27:\\\"angelo.gaspar@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781665486,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #242)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-17 03:04:53');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(18, '970d0fc2-7254-42f3-b7ed-9b1d347cd6da', 'database', 'default', '{\"uuid\":\"970d0fc2-7254-42f3-b7ed-9b1d347cd6da\",\"displayName\":\"App\\\\Mail\\\\WorkOrderCancellationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderCancellationMail\\\":6:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:9:\\\"createdBy\\\";i:2;s:8:\\\"priority\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:9:\\\"canceller\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:2:{i:0;s:5:\\\"roles\\\";i:1;s:11:\\\"permissions\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"reason\\\";s:1:\\\"k\\\";s:13:\\\"recipientType\\\";s:7:\\\"manager\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:31:\\\"alain.evangelista@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781665811,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #245)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-17 03:10:14'),
(19, '6ffea8a1-9efe-4fab-8e4b-75d824a6519b', 'database', 'default', '{\"uuid\":\"6ffea8a1-9efe-4fab-8e4b-75d824a6519b\",\"displayName\":\"App\\\\Mail\\\\WorkOrderCancellationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderCancellationMail\\\":6:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:9:\\\"createdBy\\\";i:2;s:8:\\\"priority\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:9:\\\"canceller\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:2:{i:0;s:5:\\\"roles\\\";i:1;s:11:\\\"permissions\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"reason\\\";s:1:\\\"k\\\";s:13:\\\"recipientType\\\";s:10:\\\"technician\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:25:\\\"richard.vea@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781665811,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #248)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-17 03:10:16'),
(20, 'f3d4a3a6-1b3f-43ad-b779-9c1b70162fbe', 'database', 'default', '{\"uuid\":\"f3d4a3a6-1b3f-43ad-b779-9c1b70162fbe\",\"displayName\":\"App\\\\Mail\\\\WorkOrderCancellationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderCancellationMail\\\":6:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:20:\\\"App\\\\Models\\\\WorkOrder\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:3:{i:0;s:7:\\\"workers\\\";i:1;s:9:\\\"createdBy\\\";i:2;s:8:\\\"priority\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:9:\\\"canceller\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:2:{i:0;s:5:\\\"roles\\\";i:1;s:11:\\\"permissions\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"reason\\\";s:1:\\\"k\\\";s:13:\\\"recipientType\\\";s:10:\\\"technician\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:27:\\\"angelo.gaspar@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781665811,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #251)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-17 03:10:18');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(21, '80aceba0-00ec-47b8-a796-8644e63dc289', 'database', 'default', '{\"uuid\":\"80aceba0-00ec-47b8-a796-8644e63dc289\",\"displayName\":\"App\\\\Mail\\\\WorkOrderConfirmationMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:34:\\\"App\\\\Mail\\\\WorkOrderConfirmationMail\\\":3:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\RequestorWorkOrder\\\";s:2:\\\"id\\\";i:38;s:9:\\\"relations\\\";a:1:{i:0;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:33:\\\"reinier.intendencia@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781769425,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #226)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-18 07:57:09'),
(22, '60f26212-a396-40d3-b8fd-f6a57b083a56', 'database', 'default', '{\"uuid\":\"60f26212-a396-40d3-b8fd-f6a57b083a56\",\"displayName\":\"App\\\\Mail\\\\WorkOrderActionRequiredMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:36:\\\"App\\\\Mail\\\\WorkOrderActionRequiredMail\\\":4:{s:9:\\\"workOrder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\RequestorWorkOrder\\\";s:2:\\\"id\\\";i:38;s:9:\\\"relations\\\";a:1:{i:0;s:9:\\\"createdBy\\\";}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:9:\\\"recipient\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\AppUser\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:7:\\\"mariadb\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:27:\\\"rich.dionisio@ravago.com.ph\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1781769426,\"delay\":null}', 'Symfony\\Component\\Mailer\\Exception\\TransportException: Connection could not be established with host \"127.0.0.1:1025\": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (No connection could be made because the target machine actively refused it) in C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php:154\nStack trace:\n#0 [internal function]: Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\{closure}(2, \'stream_socket_c...\', \'C:\\\\Users\\\\ryan.m...\', 157)\n#1 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\Stream\\SocketStream.php(157): stream_socket_client(\'127.0.0.1:1025\', 0, \'\', 60.0, 4, Resource id #252)\n#2 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(268): Symfony\\Component\\Mailer\\Transport\\Smtp\\Stream\\SocketStream->initialize()\n#3 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(200): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->start()\n#4 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\AbstractTransport.php(69): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->doSend(Object(Symfony\\Component\\Mailer\\SentMessage))\n#5 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\mailer\\Transport\\Smtp\\SmtpTransport.php(138): Symfony\\Component\\Mailer\\Transport\\AbstractTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#6 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(584): Symfony\\Component\\Mailer\\Transport\\Smtp\\SmtpTransport->send(Object(Symfony\\Component\\Mime\\Email), Object(Symfony\\Component\\Mailer\\DelayedEnvelope))\n#7 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailer.php(331): Illuminate\\Mail\\Mailer->sendSymfonyMessage(Object(Symfony\\Component\\Mime\\Email))\n#8 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(207): Illuminate\\Mail\\Mailer->send(\'emails.work-ord...\', Array, Object(Closure))\n#9 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Mail\\Mailable->Illuminate\\Mail\\{closure}()\n#10 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\Mailable.php(200): Illuminate\\Mail\\Mailable->withLocale(NULL, Object(Closure))\n#11 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Mail\\SendQueuedMailable.php(82): Illuminate\\Mail\\Mailable->send(Object(Illuminate\\Mail\\MailManager))\n#12 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Mail\\SendQueuedMailable->handle(Object(Illuminate\\Mail\\MailManager))\n#13 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#18 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#19 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#20 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Mail\\SendQueuedMailable), false)\n#22 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#23 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Mail\\SendQueuedMailable))\n#24 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Mail\\SendQueuedMailable))\n#26 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#27 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(485): Illuminate\\Queue\\Jobs\\Job->fire()\n#28 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(435): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#29 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(201): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#30 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#31 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#32 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#33 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#34 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#35 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#36 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#37 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#38 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#39 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#40 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#42 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#43 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#44 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 C:\\Users\\ryan.masungsong\\Desktop\\filament\\emms\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#46 {main}', '2026-06-18 07:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `fuel_types`
--

CREATE TABLE `fuel_types` (
  `fuel_id` bigint(20) UNSIGNED NOT NULL,
  `fuel_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

CREATE TABLE `inspections` (
  `ins_id` bigint(20) UNSIGNED NOT NULL,
  `ins_dep_id` bigint(20) UNSIGNED NOT NULL,
  `ins_eqm_id` bigint(20) UNSIGNED NOT NULL,
  `ins_by` bigint(20) UNSIGNED NOT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `ins_submitted_dt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspections`
--

INSERT INTO `inspections` (`ins_id`, `ins_dep_id`, `ins_eqm_id`, `ins_by`, `ins_dt`, `ins_submitted_by`, `ins_submitted_dt`) VALUES
(38, 2, 6, 5, '2026-06-05 14:31:00', 3, '2026-06-05 14:31:10'),
(39, 2, 6, 4, '2026-06-05 14:38:00', 3, '2026-06-05 14:38:48'),
(40, 2, 6, 4, '2026-06-05 16:12:00', 3, '2026-06-05 16:13:17'),
(41, 2, 6, 4, '2026-06-06 01:05:00', 4, '2026-06-06 01:08:55'),
(42, 2, 6, 5, '2026-06-08 00:06:00', 3, '2026-06-08 00:06:29'),
(43, 2, 6, 4, '2026-06-08 00:30:00', 3, '2026-06-08 00:30:54'),
(44, 2, 6, 5, '2026-06-08 10:44:00', 3, '2026-06-08 10:45:16'),
(45, 2, 6, 4, '2026-06-08 10:48:00', 3, '2026-06-08 10:48:31'),
(46, 2, 6, 4, '2026-06-08 10:49:00', 3, '2026-06-08 10:49:31'),
(47, 2, 6, 5, '2026-06-11 11:06:00', 5, '2026-06-11 11:06:17');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_items`
--

CREATE TABLE `inspection_items` (
  `insi_id` bigint(20) UNSIGNED NOT NULL,
  `insi_ins_id` bigint(20) UNSIGNED NOT NULL,
  `insi_task_id` bigint(20) UNSIGNED DEFAULT NULL,
  `insi_status_id` varchar(10) NOT NULL,
  `insi_result` char(1) DEFAULT NULL,
  `insi_cli_name_for_record` varchar(255) NOT NULL,
  `insi_remarks` text DEFAULT NULL,
  `insi_closed_dt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspection_items`
--

INSERT INTO `inspection_items` (`insi_id`, `insi_ins_id`, `insi_task_id`, `insi_status_id`, `insi_result`, `insi_cli_name_for_record`, `insi_remarks`, `insi_closed_dt`) VALUES
(40, 38, NULL, 'cmp', 'P', 'Check Brakes', NULL, '2026-06-05 14:31:10'),
(41, 38, 7, 'pnd', 'F', 'Check Tires', NULL, NULL),
(42, 38, 8, 'cmp', 'N', 'Check if Lubricated', NULL, '2026-06-05 14:31:10'),
(43, 39, NULL, 'cmp', 'P', 'Check Brakes', NULL, NULL),
(44, 39, 7, 'cmp', 'N', 'Check Tires', NULL, NULL),
(45, 39, 8, 'drg', 'F', 'Check if Lubricated', NULL, '2026-06-05 14:53:46'),
(46, 40, NULL, 'cmp', 'P', 'Check Brakes', 'p', '2026-06-05 16:13:17'),
(47, 40, 7, 'inprog', 'F', 'Check Tires', 'failed', NULL),
(48, 40, 8, 'cmp', 'N', 'Check if Lubricated', 'na', '2026-06-05 16:13:17'),
(49, 41, NULL, 'cmp', 'P', 'Check Brakes', 'ok mahigpit', '2026-06-06 01:08:55'),
(50, 41, 7, 'cmp', 'P', 'Check Tires', 'kapal pa', '2026-06-06 01:08:55'),
(51, 41, 8, 'cmp', 'N', 'Check if Lubricated', NULL, '2026-06-06 01:08:55'),
(52, 41, NULL, 'drg', 'F', 'Check Machine', 'may sira', '2026-06-06 01:21:00'),
(53, 42, NULL, 'cmp', 'P', 'Check Brakes', NULL, '2026-06-08 00:06:29'),
(54, 42, 7, 'inprog', 'F', 'Check Tires', NULL, NULL),
(55, 42, 8, 'cmp', 'P', 'Check if Lubricated', NULL, '2026-06-08 00:06:29'),
(56, 42, NULL, 'cmp', 'P', 'Check Machine', NULL, '2026-06-08 00:06:29'),
(57, 43, NULL, 'pnd', 'F', 'Check Brakes', NULL, NULL),
(58, 43, 7, 'pnd', 'F', 'Check Tires', NULL, NULL),
(59, 43, 8, 'pnd', 'F', 'Check if Lubricated', NULL, NULL),
(60, 43, NULL, 'inprog', 'F', 'Check Machine', NULL, NULL),
(61, 44, NULL, 'pnd', 'F', 'Check Brakes', NULL, NULL),
(62, 44, 7, 'pnd', 'F', 'Check Tires', NULL, NULL),
(63, 44, 8, 'pnd', 'F', 'Check if Lubricated', NULL, NULL),
(64, 44, NULL, 'cmp', 'P', 'Check Machine', NULL, '2026-06-08 10:45:16'),
(65, 45, NULL, 'cmp', 'P', 'Check Brakes', NULL, '2026-06-08 10:48:31'),
(66, 45, 7, 'drg', 'F', 'Check Tires', NULL, '2026-06-08 11:00:40'),
(67, 45, 8, 'cmp', 'N', 'Check if Lubricated', NULL, '2026-06-08 10:48:31'),
(68, 45, NULL, 'drg', 'F', 'Check Machine', NULL, '2026-06-08 10:59:22'),
(69, 46, NULL, 'inprog', 'F', 'Check Brakes', NULL, NULL),
(70, 46, 7, 'cmp', 'P', 'Check Tires', NULL, '2026-06-08 10:49:31'),
(71, 46, 8, 'drg', 'F', 'Check if Lubricated', NULL, '2026-06-08 10:57:47'),
(72, 46, NULL, 'cmp', 'N', 'Check Machine', NULL, '2026-06-08 10:49:31'),
(73, 47, 7, 'pnd', 'F', 'Check Tires', NULL, NULL),
(74, 47, 8, 'cmp', 'P', 'Check if Lubricated', NULL, '2026-06-11 11:06:17');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_item_logs`
--

CREATE TABLE `inspection_item_logs` (
  `inil_id` bigint(20) UNSIGNED NOT NULL,
  `inil_insi_id` bigint(20) UNSIGNED NOT NULL,
  `inil_a_id` varchar(10) DEFAULT NULL,
  `inil_status_id` varchar(10) DEFAULT NULL,
  `inil_action_made` varchar(30) NOT NULL,
  `inil_status_log` varchar(40) NOT NULL,
  `inil_remarks` text DEFAULT NULL,
  `inil_wo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `inil_by` bigint(20) UNSIGNED DEFAULT NULL,
  `inil_dt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspection_item_logs`
--

INSERT INTO `inspection_item_logs` (`inil_id`, `inil_insi_id`, `inil_a_id`, `inil_status_id`, `inil_action_made`, `inil_status_log`, `inil_remarks`, `inil_wo_id`, `inil_by`, `inil_dt`) VALUES
(42, 40, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-05 14:31:10'),
(43, 41, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-05 14:31:10'),
(44, 42, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-05 14:31:10'),
(45, 41, 'mwo', 'inprog', 'Made Work Order', 'In-Progress', NULL, 16, 3, '2026-06-05 14:31:50'),
(46, 43, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-05 14:38:48'),
(47, 44, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-05 14:38:48'),
(48, 45, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-05 14:38:48'),
(51, 45, 'drg', 'drg', 'Disregarded', 'Disregarded', 'dfdf', NULL, 3, '2026-06-05 14:53:46'),
(53, 46, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-05 16:13:17'),
(54, 47, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-05 16:13:17'),
(55, 48, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-05 16:13:17'),
(56, 47, 'mwo', 'inprog', 'Made Work Order', 'In-Progress', NULL, 17, 3, '2026-06-05 16:14:25'),
(58, 49, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 4, '2026-06-06 01:08:55'),
(59, 50, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 4, '2026-06-06 01:08:55'),
(60, 51, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 4, '2026-06-06 01:08:55'),
(61, 52, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 4, '2026-06-06 01:08:55'),
(62, 52, 'mwo', 'inprog', 'Made Work Order', 'In-Progress', NULL, 19, 3, '2026-06-06 01:18:22'),
(63, 52, 'upt', 'pnd', 'Updated', 'Pending', 'Cancelled the linked work order and reverted this inspection finding to pending.', 19, 3, '2026-06-06 01:20:16'),
(64, 52, 'drg', 'drg', 'Disregarded', 'Disregarded', 'false', NULL, 3, '2026-06-06 01:21:00'),
(65, 53, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 00:06:29'),
(66, 54, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 00:06:29'),
(67, 55, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 00:06:29'),
(68, 56, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 00:06:29'),
(69, 54, 'mwo', 'inprog', 'Made Work Order', 'In-Progress', NULL, 20, 3, '2026-06-08 00:16:25'),
(70, 57, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 00:30:54'),
(71, 58, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 00:30:54'),
(72, 59, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 00:30:54'),
(73, 60, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 00:30:54'),
(74, 60, 'mwo', 'inprog', 'Made Work Order', 'In-Progress', NULL, 21, 3, '2026-06-08 00:31:28'),
(75, 61, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 10:45:16'),
(76, 62, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 10:45:16'),
(77, 63, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 10:45:16'),
(78, 64, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 10:45:16'),
(79, 65, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 10:48:31'),
(80, 66, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 10:48:31'),
(81, 67, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 10:48:31'),
(82, 68, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 10:48:31'),
(83, 69, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 10:49:31'),
(84, 70, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 10:49:31'),
(85, 71, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 3, '2026-06-08 10:49:31'),
(86, 72, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 3, '2026-06-08 10:49:31'),
(87, 69, 'mwo', 'inprog', 'Made Work Order', 'In-Progress', NULL, 22, 3, '2026-06-08 10:51:09'),
(88, 71, 'drg', 'drg', 'Disregarded', 'Disregarded', 'dffdf', NULL, 3, '2026-06-08 10:57:47'),
(89, 68, 'drg', 'drg', 'Disregarded', 'Disregarded', 'dfdfd', NULL, 3, '2026-06-08 10:59:22'),
(90, 66, 'drg', 'drg', 'Disregarded', 'Disregarded', 'ff', NULL, 3, '2026-06-08 11:00:40'),
(91, 73, 'create', 'pnd', 'Created', 'Pending', NULL, NULL, 5, '2026-06-11 11:06:17'),
(92, 74, 'create', 'cmp', 'Created', 'Completed', NULL, NULL, 5, '2026-06-11 11:06:17'),
(94, 41, 'upt', 'pnd', 'Updated', 'Pending', 'Cancelled the linked work order and reverted this inspection finding to pending.', 16, 3, '2026-06-17 11:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_results`
--

CREATE TABLE `inspection_results` (
  `insr_code` char(1) NOT NULL,
  `insr_name` varchar(10) NOT NULL,
  `insr_color` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspection_results`
--

INSERT INTO `inspection_results` (`insr_code`, `insr_name`, `insr_color`) VALUES
('F', 'Failed', 'danger'),
('N', 'N/A', 'gray'),
('P', 'Passed', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
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
-- Table structure for table `maintenance_tasks`
--

CREATE TABLE `maintenance_tasks` (
  `mt_id` bigint(20) UNSIGNED NOT NULL,
  `mt_batch_id` char(36) DEFAULT NULL,
  `mt_eqm_id` bigint(20) UNSIGNED NOT NULL,
  `mt_eqm_log` varchar(255) DEFAULT NULL,
  `mt_dep_id` bigint(20) UNSIGNED NOT NULL,
  `mt_status_id` varchar(10) NOT NULL,
  `mt_due_dt` datetime DEFAULT NULL,
  `mt_remarks` text DEFAULT NULL,
  `mt_scheduled_dt` datetime NOT NULL,
  `mt_closed_dt` datetime DEFAULT NULL,
  `mt_by` bigint(20) UNSIGNED DEFAULT NULL,
  `mt_dt` datetime NOT NULL,
  `mt_task_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mt_task_log` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_tasks`
--

INSERT INTO `maintenance_tasks` (`mt_id`, `mt_batch_id`, `mt_eqm_id`, `mt_eqm_log`, `mt_dep_id`, `mt_status_id`, `mt_due_dt`, `mt_remarks`, `mt_scheduled_dt`, `mt_closed_dt`, `mt_by`, `mt_dt`, `mt_task_id`, `mt_task_log`) VALUES
(24, 'f031f415-b5d5-45b6-9de1-bfd791748597', 6, '10 Wheeler Truck #2', 2, 'cmp', '2026-06-08 17:01:00', 'System-generated', '2026-05-28 08:00:00', '2026-06-09 14:21:59', NULL, '2026-05-29 16:14:01', 2, 'Change Oil'),
(32, '8accec9d-cc5c-4bcb-b906-66f6facf841e', 6, '10 Wheeler Truck #2', 4, 'pnd', '2026-06-10 17:00:00', 'System-generated', '2026-06-04 08:00:00', NULL, NULL, '2026-06-09 17:00:16', 12, 'Change Oil'),
(34, '8f8ad7fc-4623-4c31-a20f-ba4004112826', 6, '10 Wheeler Truck #2', 4, 'pnd', '2026-06-14 08:00:00', 'System-generated', '2026-06-14 08:00:00', NULL, NULL, '2026-06-15 08:43:17', 14, 'Change Filter');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_task_logs`
--

CREATE TABLE `maintenance_task_logs` (
  `mtl_id` bigint(20) UNSIGNED NOT NULL,
  `mtl_mt_id` bigint(20) UNSIGNED NOT NULL,
  `mtl_status_id` varchar(10) NOT NULL,
  `mtl_due_dt` datetime DEFAULT NULL,
  `mtl_last_act_made` varchar(10) DEFAULT NULL,
  `mtl_wo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mtl_remarks` text DEFAULT NULL,
  `mtl_by` bigint(20) UNSIGNED DEFAULT NULL,
  `mtl_dt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_task_logs`
--

INSERT INTO `maintenance_task_logs` (`mtl_id`, `mtl_mt_id`, `mtl_status_id`, `mtl_due_dt`, `mtl_last_act_made`, `mtl_wo_id`, `mtl_remarks`, `mtl_by`, `mtl_dt`) VALUES
(24, 24, 'pnd', '2026-05-28 08:00:00', 'create', NULL, 'System-generated', NULL, '2026-05-29 16:14:01'),
(25, 24, 'pnd', '2026-05-28 08:00:00', 'rtv', NULL, 'Overdue - updated by system', NULL, '2026-06-01 08:50:09'),
(26, 24, 'snz', '2026-05-31 08:00:00', 'snz', NULL, 'ok', 1, '2026-06-01 08:52:52'),
(27, 24, 'pnd', '2026-05-31 08:00:00', 'rtv', NULL, 'Overdue - updated by system', NULL, '2026-06-01 08:54:28'),
(34, 24, 'inprog', '2026-05-31 08:00:00', 'mwo', 29, NULL, 3, '2026-06-08 14:39:09'),
(35, 24, 'pnd', NULL, 'upt', 29, 'Cancelled the linked work order and reverted this maintenance task finding to pending.', 3, '2026-06-08 14:39:28'),
(36, 24, 'snz', '2026-06-08 17:01:00', 'snz', NULL, 'dfs', 3, '2026-06-08 14:54:20'),
(39, 24, 'cmp', NULL, 'mac', NULL, 'd', 1, '2026-06-09 14:21:59'),
(52, 32, 'pnd', '2026-06-04 08:00:00', 'create', NULL, 'System-generated', NULL, '2026-06-09 17:00:16'),
(53, 32, 'snz', '2026-06-10 17:00:00', 'snz', NULL, 'dc', 7, '2026-06-09 17:00:50'),
(55, 34, 'pnd', '2026-06-14 08:00:00', 'create', NULL, 'System-generated', NULL, '2026-06-15 08:43:17'),
(56, 32, 'pnd', '2026-06-10 17:00:00', 'rtv', NULL, 'Overdue - updated by system', NULL, '2026-06-15 08:43:17'),
(57, 32, 'pnd', '2026-06-10 17:00:00', 'rtv', NULL, 'Overdue - updated by system', NULL, '2026-06-15 08:43:35'),
(58, 32, 'pnd', '2026-06-10 17:00:00', 'rtv', NULL, 'Overdue - updated by system', NULL, '2026-06-15 08:48:45'),
(59, 32, 'pnd', '2026-06-10 17:00:00', 'rtv', NULL, 'Overdue - updated by system', NULL, '2026-06-15 08:52:28'),
(60, 32, 'pnd', '2026-06-10 17:00:00', 'rtv', NULL, 'Overdue - updated by system', NULL, '2026-06-15 16:39:44');

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
(4, '2026_04_24_014352_create_departments_table', 1),
(5, '2026_04_24_014751_create_app_users_table', 1),
(6, '2026_04_24_054941_create_permission_tables', 1),
(7, '2026_04_28_000000_make_app_users_columns_nullable', 1),
(8, '2026_04_29_013450_create_equipment_categories_table', 1),
(9, '2026_04_29_013503_create_equipment_brands_table', 1),
(10, '2026_04_29_013512_create_fuel_types_table', 1),
(11, '2026_04_29_013627_create_equipment_models_table', 1),
(12, '2026_04_29_032933_add_parent_id_to_equipment_categories_table', 1),
(13, '2026_04_30_065151_create_equipment_table', 1),
(14, '2026_04_30_080514_update_equipment_units_table', 1),
(15, '2026_05_04_004308_add_serial_num_and_engine_to_equipment_units_table', 1),
(16, '2026_05_04_023726_create_app_settings_table', 1),
(17, '2026_05_04_111204_add_last_equipment_sync_to_app_settings_table', 1),
(18, '2026_05_04_160217_create_equipment_types_table', 1),
(19, '2026_05_04_163947_add_columns_to_equipment_models_table', 1),
(20, '2026_05_07_085934_create_checklist_usage_types_table', 1),
(21, '2026_05_07_090708_create_checklist_templates_table', 1),
(22, '2026_05_07_095133_create_checklist_items_table', 1),
(23, '2026_05_07_112756_update_cli_clt_id_foreign_cascade', 1),
(24, '2026_05_07_140930_create_equipment_checklist_assignments_table', 1),
(25, '2026_05_08_133208_add_due_dates_to_equipment_checklist_assignments_table', 1),
(26, '2026_05_08_165515_add_dep_is_maintenance_to_departments_table', 1),
(27, '2026_05_11_084344_create_statuses_table', 1),
(28, '2026_05_11_091215_create_maintenance_tasks_table', 1),
(29, '2026_05_11_113253_create_actions_table', 1),
(30, '2026_05_11_114053_create_maintenance_task_logs_table', 1),
(31, '2026_05_11_163418_add_eca_dep_id_to_equipment_checklist_assignments_table', 1),
(32, '2026_05_12_093240_make_mt_by_nullable_in_maintenance_tasks_table', 1),
(33, '2026_05_14_145034_add_batch_uuid_to_maintenance_tasks_table', 1),
(34, '2026_05_14_155539_add_status_color_to_statuses_table', 1),
(35, '2026_05_20_080000_replace_checklist_with_tasks', 1),
(36, '2026_05_20_081000_change_task_last_updated_by_fk_to_app_users', 2),
(37, '2026_05_21_135914_change_ets_due_effectivity_dt_to_date_in_equipment_tasks_schedules', 3),
(39, '2026_05_21_144613_add_ets_last_assigned_by_and_ets_last_assigned_at_to_equipment_tasks_schedules', 4),
(41, '2026_05_21_165239_add_a_icon_to_actions_table', 5),
(44, '2026_05_25_091730_create_priorities_table', 6),
(45, '2026_05_25_091730_create_work_orders_table', 6),
(46, '2026_05_25_091731_create_work_order_assignments_table', 6),
(47, '2026_05_25_091731_create_work_order_log_updates_table', 6),
(48, '2026_05_25_091734_create_work_order_logs_table', 6),
(49, '2026_05_25_095756_add_unique_constraint_to_wo_no_in_work_orders', 7),
(50, '2026_05_25_103132_change_prio_id_to_auto_increment_in_priorities', 8),
(51, '2026_05_25_112239_change_wo_attachments_to_json_in_work_orders', 9),
(54, '2026_05_25_150621_simplify_work_order_assignments_table', 10),
(55, '2026_05_28_131921_create_report_submissions_table', 11),
(56, '2026_05_28_132110_create_worker_reports_table', 11),
(57, '2026_05_28_165847_add_wol_note_to_work_order_logs_table', 12),
(58, '2026_06_01_083557_add_unique_constraint_to_equipment_tasks_schedules_table', 13),
(59, '2026_06_01_105637_update_maintenance_tasks_for_decoupling', 14),
(60, '2026_06_01_145118_update_task_foreign_keys_on_delete', 15),
(61, '2026_06_01_153431_add_unique_constraint_to_equipment_task_checklist_template_table', 16),
(64, '2026_06_03_103421_create_inspection_results_table', 17),
(65, '2026_06_03_103422_create_inspections_table', 17),
(66, '2026_06_03_103449_create_inspection_items_table', 17),
(67, '2026_06_03_113944_remove_action_columns_from_inspection_items_table', 18),
(68, '2026_06_04_092258_add_insi_status_id_to_inspection_items_table', 19),
(69, '2026_06_04_093253_create_inspection_item_logs_table', 20),
(70, '2026_06_04_133432_increase_inil_action_made_length_in_inspection_item_logs_table', 21),
(71, '2026_06_04_141553_add_wo_insi_id_foreign_key_to_work_orders_table', 22),
(75, '2026_06_04_163937_add_inil_wo_no_to_inspection_item_logs_table', 23),
(76, '2026_06_05_000000_add_inil_a_id_and_inil_status_id_to_inspection_item_logs_table', 23),
(77, '2026_06_05_115544_add_insi_closed_dt_to_inspection_items_table', 24),
(79, '2026_06_08_132947_add_mtl_wo_id_to_maintenance_task_logs_table', 25),
(80, '2026_06_10_114907_add_user_avatar_to_app_users_table', 26),
(81, '2026_06_11_095712_create_site_settings_table', 27),
(82, '2026_06_11_104104_create_activity_log_table', 28),
(83, '2026_06_11_104105_add_event_column_to_activity_log_table', 28),
(84, '2026_06_11_104106_add_batch_uuid_column_to_activity_log_table', 28),
(85, '2026_06_15_165100_add_favicon_and_font_family_to_site_settings_table', 29),
(86, '2026_06_16_090043_add_requestor_and_manager_notes_to_work_orders_table', 30),
(87, '2026_06_16_090553_add_approved_status_to_statuses_table', 30),
(88, '2026_06_16_102103_make_wo_desc_nullable_in_work_orders_table', 31),
(91, '2026_06_18_144211_add_display_name_to_roles_table', 32);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\AppUser', 1),
(1, 'App\\Models\\AppUser', 2),
(2, 'App\\Models\\AppUser', 3),
(2, 'App\\Models\\AppUser', 6),
(2, 'App\\Models\\AppUser', 7),
(3, 'App\\Models\\AppUser', 4),
(3, 'App\\Models\\AppUser', 5),
(3, 'App\\Models\\AppUser', 8),
(3, 'App\\Models\\AppUser', 9),
(3, 'App\\Models\\AppUser', 10),
(4, 'App\\Models\\AppUser', 11),
(5, 'App\\Models\\AppUser', 12);

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'ViewAny:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(2, 'View:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(3, 'Create:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(4, 'Update:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(5, 'Delete:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(6, 'DeleteAny:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(7, 'Restore:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(8, 'ForceDelete:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(9, 'ForceDeleteAny:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(10, 'RestoreAny:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(11, 'Replicate:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(12, 'Reorder:AppUserResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(13, 'ViewAny:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(14, 'View:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(15, 'Create:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(16, 'Update:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(17, 'Delete:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(18, 'DeleteAny:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(19, 'Restore:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(20, 'ForceDelete:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(21, 'ForceDeleteAny:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(22, 'RestoreAny:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(23, 'Replicate:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(24, 'Reorder:BrandResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(25, 'ViewAny:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(26, 'View:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(27, 'Create:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(28, 'Update:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(29, 'Delete:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(30, 'DeleteAny:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(31, 'Restore:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(32, 'ForceDelete:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(33, 'ForceDeleteAny:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(34, 'RestoreAny:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(35, 'Replicate:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(36, 'Reorder:CategoryResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(37, 'ViewAny:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(38, 'View:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(39, 'Create:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(40, 'Update:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(41, 'Delete:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(42, 'DeleteAny:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(43, 'Restore:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(44, 'ForceDelete:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(45, 'ForceDeleteAny:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(46, 'RestoreAny:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(47, 'Replicate:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(48, 'Reorder:DepartmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(49, 'ViewAny:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(50, 'View:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(51, 'Create:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(52, 'Update:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(53, 'Delete:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(54, 'DeleteAny:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(55, 'Restore:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(56, 'ForceDelete:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(57, 'ForceDeleteAny:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(58, 'RestoreAny:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(59, 'Replicate:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(60, 'Reorder:EquipmentTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(61, 'ViewAny:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(62, 'View:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(63, 'Create:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(64, 'Update:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(65, 'Delete:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(66, 'DeleteAny:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(67, 'Restore:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(68, 'ForceDelete:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(69, 'ForceDeleteAny:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(70, 'RestoreAny:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(71, 'Replicate:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(72, 'Reorder:EquipmentResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(73, 'ViewAny:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(74, 'View:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(75, 'Create:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(76, 'Update:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(77, 'Delete:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(78, 'DeleteAny:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(79, 'Restore:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(80, 'ForceDelete:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(81, 'ForceDeleteAny:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(82, 'RestoreAny:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(83, 'Replicate:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(84, 'Reorder:FuelTypeResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(85, 'ViewAny:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(86, 'View:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(87, 'Create:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(88, 'Update:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(89, 'Delete:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(90, 'DeleteAny:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(91, 'Restore:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(92, 'ForceDelete:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(93, 'ForceDeleteAny:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(94, 'RestoreAny:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(95, 'Replicate:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(96, 'Reorder:MaintenanceTaskLogResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(97, 'ViewAny:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(98, 'View:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(99, 'Create:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(100, 'Update:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(101, 'Delete:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(102, 'DeleteAny:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(103, 'Restore:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(104, 'ForceDelete:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(105, 'ForceDeleteAny:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(106, 'RestoreAny:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(107, 'Replicate:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(108, 'Reorder:MaintenanceTaskResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(109, 'ViewAny:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(110, 'View:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(111, 'Create:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(112, 'Update:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(113, 'Delete:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(114, 'DeleteAny:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(115, 'Restore:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(116, 'ForceDelete:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(117, 'ForceDeleteAny:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(118, 'RestoreAny:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(119, 'Replicate:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(120, 'Reorder:ModelResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(121, 'ViewAny:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(122, 'View:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(123, 'Create:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(124, 'Update:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(125, 'Delete:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(126, 'DeleteAny:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(127, 'Restore:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(128, 'ForceDelete:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(129, 'ForceDeleteAny:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(130, 'RestoreAny:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(131, 'Replicate:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(132, 'Reorder:RoleResource', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(133, 'View:SapSyncManager', 'web', '2026-05-20 08:13:46', '2026-05-20 08:13:46'),
(134, 'ViewAny:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(135, 'View:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(136, 'Create:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(137, 'Update:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(138, 'Delete:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(139, 'DeleteAny:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(140, 'Restore:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(141, 'ForceDelete:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(142, 'ForceDeleteAny:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(143, 'RestoreAny:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(144, 'Replicate:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(145, 'Reorder:TaskResource', 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(146, 'Sync:EquipmentResource', 'web', '2026-05-22 06:49:46', '2026-05-22 06:49:46'),
(147, 'ViewAny:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(148, 'View:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(149, 'Create:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(150, 'Update:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(151, 'Delete:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(152, 'DeleteAny:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(153, 'Restore:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(154, 'ForceDelete:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(155, 'ForceDeleteAny:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(156, 'RestoreAny:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(157, 'Replicate:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(158, 'Reorder:TechnicianWorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(159, 'ViewAny:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(160, 'View:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(161, 'Create:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(162, 'Update:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(163, 'Delete:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(164, 'DeleteAny:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(165, 'Restore:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(166, 'ForceDelete:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(167, 'ForceDeleteAny:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(168, 'RestoreAny:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(169, 'Replicate:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(170, 'Reorder:WorkOrderResource', 'web', '2026-05-26 05:40:35', '2026-05-26 05:40:35'),
(171, 'AddUpdate:TechnicianWorkOrderResource', 'web', '2026-05-26 06:07:29', '2026-05-26 06:07:29'),
(172, 'AddUpdate:WorkOrderResource', 'web', '2026-05-26 08:37:22', '2026-05-26 08:37:22'),
(173, 'AddReport:TechnicianWorkOrderResource', 'web', '2026-05-28 07:55:06', '2026-05-28 07:55:06'),
(174, 'RequestCompletion:TechnicianWorkOrderResource', 'web', '2026-05-29 01:05:50', '2026-05-29 01:05:50'),
(175, 'AddReport:WorkOrderResource', 'web', '2026-05-29 01:19:30', '2026-05-29 01:19:30'),
(176, 'RejectCompletion:WorkOrderResource', 'web', '2026-05-29 02:59:12', '2026-05-29 02:59:12'),
(177, 'ApproveCompletion:WorkOrderResource', 'web', '2026-05-29 05:40:32', '2026-05-29 05:40:32'),
(178, 'CancelWorkOrder:WorkOrderResource', 'web', '2026-05-29 05:59:55', '2026-05-29 05:59:55'),
(179, 'ViewAny:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(180, 'View:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(181, 'Create:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(182, 'Update:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(183, 'Delete:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(184, 'DeleteAny:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(185, 'Restore:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(186, 'ForceDelete:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(187, 'ForceDeleteAny:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(188, 'RestoreAny:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(189, 'Replicate:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(190, 'Reorder:InspectionResource', 'web', '2026-06-03 08:15:55', '2026-06-03 08:15:55'),
(191, 'ViewAny:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(192, 'View:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(193, 'Create:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(194, 'Update:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(195, 'Delete:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(196, 'DeleteAny:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(197, 'Restore:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(198, 'ForceDelete:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(199, 'ForceDeleteAny:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(200, 'RestoreAny:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(201, 'Replicate:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(202, 'Reorder:InspectionItemResource', 'web', '2026-06-05 08:36:07', '2026-06-05 08:36:07'),
(203, 'View:DashboardStatsOverview', 'web', '2026-06-09 03:05:33', '2026-06-09 03:05:33'),
(204, 'MakeWorkOrderFromInspectionItem:InspectionItemResource', 'web', '2026-06-09 03:45:27', '2026-06-09 03:45:27'),
(205, 'Disregard:InspectionItemResource', 'web', '2026-06-09 03:45:27', '2026-06-09 03:45:27'),
(206, 'MakeWorkOrder:InspectionItemResource', 'web', '2026-06-09 03:50:12', '2026-06-09 03:50:12'),
(207, 'MakeWorkOrder:MaintenanceTaskResource', 'web', '2026-06-09 05:52:26', '2026-06-09 05:52:26'),
(208, 'Snooze:MaintenanceTaskResource', 'web', '2026-06-09 05:52:26', '2026-06-09 05:52:26'),
(209, 'MarkAsComplete:MaintenanceTaskResource', 'web', '2026-06-09 05:52:26', '2026-06-09 05:52:26'),
(210, 'View:SiteSettings', 'web', '2026-06-11 02:03:43', '2026-06-11 02:03:43'),
(211, 'View:ActivityLogPage', 'web', '2026-06-11 02:49:02', '2026-06-11 02:49:02'),
(212, 'ViewAny:RequestorWorkOrderResource', 'web', '2026-06-16 01:43:52', '2026-06-16 01:43:52'),
(213, 'View:RequestorWorkOrderResource', 'web', '2026-06-16 01:43:52', '2026-06-16 01:43:52'),
(214, 'Create:RequestorWorkOrderResource', 'web', '2026-06-16 01:43:52', '2026-06-16 01:43:52'),
(215, 'ApproveWorkOrder:WorkOrderResource', 'web', '2026-06-16 03:04:38', '2026-06-16 03:04:38'),
(216, 'RejectWorkOrder:WorkOrderResource', 'web', '2026-06-16 03:04:38', '2026-06-16 03:04:38'),
(217, 'Super Admin', 'web', '2026-06-18 06:58:12', '2026-06-18 06:58:12'),
(218, 'Requestor', 'web', '2026-06-18 06:58:46', '2026-06-18 06:58:46'),
(219, 'ddd', 'web', '2026-06-18 07:03:58', '2026-06-18 07:03:58'),
(220, 'Cancel:RequestorWorkOrderResource', 'web', '2026-06-18 08:04:31', '2026-06-18 08:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

CREATE TABLE `priorities` (
  `prio_id` int(10) UNSIGNED NOT NULL,
  `prio_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `priorities`
--

INSERT INTO `priorities` (`prio_id`, `prio_name`) VALUES
(1, 'Emergency'),
(2, 'Urgent'),
(3, 'Normal'),
(4, 'Programmed');

-- --------------------------------------------------------

--
-- Table structure for table `report_submissions`
--

CREATE TABLE `report_submissions` (
  `rs_id` bigint(20) UNSIGNED NOT NULL,
  `rs_wo_id` bigint(20) UNSIGNED NOT NULL,
  `rs_work_date` date NOT NULL,
  `rs_submitted_by` bigint(20) UNSIGNED NOT NULL,
  `rs_submitted_dt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_submissions`
--

INSERT INTO `report_submissions` (`rs_id`, `rs_wo_id`, `rs_work_date`, `rs_submitted_by`, `rs_submitted_dt`) VALUES
(10, 5, '2026-05-28', 4, '2026-05-28 15:40:11'),
(11, 5, '2026-05-27', 5, '2026-05-28 16:27:38'),
(16, 5, '2026-05-29', 4, '2026-05-29 09:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', NULL, 'web', '2026-05-20 08:12:12', '2026-05-20 08:12:12'),
(2, 'manager', NULL, 'web', '2026-05-22 06:35:20', '2026-05-22 06:35:20'),
(3, 'technician', NULL, 'web', '2026-05-25 03:14:12', '2026-05-25 03:14:12'),
(4, 'requestor', 'Requestor', 'web', '2026-06-16 00:57:14', '2026-06-18 07:03:04'),
(5, 'asset_admin', NULL, 'web', '2026-06-16 07:29:35', '2026-06-16 07:29:35');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 5),
(14, 5),
(15, 5),
(16, 5),
(17, 5),
(18, 5),
(19, 5),
(20, 5),
(21, 5),
(22, 5),
(23, 5),
(24, 5),
(25, 1),
(25, 2),
(25, 5),
(26, 1),
(26, 2),
(26, 5),
(27, 1),
(27, 5),
(28, 1),
(28, 5),
(29, 1),
(29, 5),
(30, 1),
(30, 5),
(31, 1),
(31, 5),
(32, 1),
(32, 5),
(33, 1),
(33, 5),
(34, 1),
(34, 5),
(35, 1),
(35, 5),
(36, 1),
(36, 5),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(49, 2),
(49, 5),
(50, 1),
(50, 2),
(50, 5),
(51, 1),
(51, 5),
(52, 1),
(52, 5),
(53, 1),
(53, 5),
(54, 1),
(54, 5),
(55, 1),
(55, 5),
(56, 1),
(56, 5),
(57, 1),
(57, 5),
(58, 1),
(58, 5),
(59, 1),
(59, 5),
(60, 1),
(60, 5),
(61, 1),
(61, 2),
(61, 4),
(61, 5),
(62, 1),
(62, 2),
(62, 4),
(62, 5),
(64, 1),
(64, 5),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 5),
(74, 5),
(75, 5),
(76, 5),
(77, 5),
(78, 5),
(79, 5),
(80, 5),
(81, 5),
(82, 5),
(83, 5),
(84, 5),
(85, 1),
(85, 2),
(86, 1),
(86, 2),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(97, 2),
(98, 1),
(98, 2),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(109, 2),
(109, 5),
(110, 1),
(110, 2),
(110, 5),
(111, 1),
(111, 5),
(112, 1),
(112, 5),
(113, 1),
(113, 5),
(114, 1),
(114, 5),
(115, 1),
(115, 5),
(116, 1),
(116, 5),
(117, 1),
(117, 5),
(118, 1),
(118, 5),
(119, 1),
(119, 5),
(120, 1),
(120, 5),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(126, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(134, 2),
(135, 1),
(135, 2),
(136, 1),
(136, 2),
(137, 1),
(137, 2),
(138, 1),
(138, 2),
(139, 1),
(139, 2),
(140, 1),
(140, 2),
(141, 1),
(141, 2),
(142, 1),
(142, 2),
(143, 1),
(143, 2),
(144, 1),
(144, 2),
(145, 1),
(145, 2),
(146, 1),
(147, 1),
(147, 3),
(148, 1),
(148, 3),
(149, 1),
(150, 1),
(151, 1),
(152, 1),
(152, 3),
(153, 1),
(154, 1),
(155, 1),
(156, 1),
(157, 1),
(158, 1),
(159, 1),
(159, 2),
(160, 1),
(160, 2),
(161, 1),
(161, 2),
(163, 1),
(164, 1),
(164, 2),
(165, 1),
(166, 1),
(167, 1),
(168, 1),
(169, 1),
(170, 1),
(171, 1),
(171, 3),
(172, 1),
(172, 2),
(173, 1),
(173, 3),
(174, 1),
(174, 3),
(175, 1),
(176, 2),
(177, 2),
(178, 2),
(179, 1),
(179, 2),
(179, 3),
(180, 1),
(180, 2),
(180, 3),
(181, 1),
(181, 2),
(181, 3),
(182, 1),
(183, 1),
(184, 1),
(185, 1),
(186, 1),
(187, 1),
(188, 1),
(189, 1),
(190, 1),
(191, 1),
(191, 2),
(191, 3),
(192, 1),
(192, 2),
(192, 3),
(193, 1),
(194, 1),
(195, 1),
(196, 1),
(197, 1),
(198, 1),
(199, 1),
(200, 1),
(201, 1),
(202, 1),
(203, 1),
(203, 2),
(203, 3),
(205, 1),
(205, 2),
(206, 1),
(206, 2),
(207, 1),
(207, 2),
(208, 1),
(208, 2),
(209, 1),
(209, 2),
(210, 1),
(211, 1),
(212, 4),
(213, 4),
(214, 4),
(215, 1),
(215, 2),
(216, 1),
(216, 2),
(217, 1),
(218, 4),
(220, 4);

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
('AnNy0nt2Nlm6VqoW2G4o5Vkwb2o1JOk4t9YNFoNx', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6ImpPWVBSdEZ6bUN6bXFwdWR0Smo1ZWNYOWZyYkxLQmVmY1BiR0JtZmIiO3M6MzoidXJsIjthOjA6e31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0NjoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL3JlcXVlc3Rvci13b3JrLW9yZGVycy80MCI7czo1OiJyb3V0ZSI7czo1MToiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLnJlcXVlc3Rvci13b3JrLW9yZGVycy52aWV3Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJmaWxhbWVudCI7YTowOnt9czo2OiJ0YWJsZXMiO2E6MjU6e3M6NDA6IjdkZGQxODg0MWZhMGVjNmMzNjI1NjgyNWQxN2RmYWQyX2NvbHVtbnMiO2E6NDp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjIyOiJlcXVpcG1lbnRVbml0LmVxbV9uYW1lIjtzOjU6ImxhYmVsIjtzOjk6IkVxdWlwbWVudCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6InRhc2sudGFza19uYW1lIjtzOjU6ImxhYmVsIjtzOjQ6IlRhc2siO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6Im10X2R1ZV9kdCI7czo1OiJsYWJlbCI7czo4OiJEdWUgRGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6InN0YXR1cy5zdGF0dXNfdGl0bGUiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiI2YmU0ZWJiNzJlYjI3MmMyZTk1YWNiMTllMWZhZmJhZl9jb2x1bW5zIjthOjY6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoiZXFtX3ByY19jb2RlIjtzOjU6ImxhYmVsIjtzOjg6IlBSQyBDb2RlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo4OiJlcW1fbmFtZSI7czo1OiJsYWJlbCI7czo0OiJOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToibW9kZWwuZXFtbV9uYW1lIjtzOjU6ImxhYmVsIjtzOjU6Ik1vZGVsIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo3OiJlcW1fdmluIjtzOjU6ImxhYmVsIjtzOjM6IlZJTiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6ImVxbV9wbGF0ZV9udW0iO3M6NToibGFiZWwiO3M6NzoiUGxhdGUgIyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6ImVxbV9pc19hY3RpdmUiO3M6NToibGFiZWwiO3M6NjoiQWN0aXZlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiI3ODgwMGQ1OTNjNzgyN2FmNGJhMTVkNGUxYTg4YWExN19jb2x1bW5zIjthOjM6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJ0YXNrX25hbWUiO3M6NToibGFiZWwiO3M6OToiVGFzayBOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyMjoidGFza1VzYWdlVHlwZS50dXRfbmFtZSI7czo1OiJsYWJlbCI7czoxMDoiVXNhZ2UgVHlwZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6ImRlcGFydG1lbnQuZGVwX25hbWUiO3M6NToibGFiZWwiO3M6MTA6IkRlcGFydG1lbnQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6ImUwZDBhMzFiNGQ4YWYxNmFhZjJmMDE2NzZmZDkxMGExX2NvbHVtbnMiO2E6Mzp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjg6ImRlcF9jb2RlIjtzOjU6ImxhYmVsIjtzOjE1OiJEZXBhcnRtZW50IENvZGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjg6ImRlcF9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoiaXNfbWFpbnRlbmFuY2UiO3M6NToibGFiZWwiO3M6MTY6Ik1haW50ZW5hbmNlIERlcHQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6ImYwMGFmZjcyYWNmZDI1ZDBhMmY3NjRmOWM2ZGJhYTI5X2NvbHVtbnMiO2E6Nzp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJ1c2VyX2ZuYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJGaXJzdCBOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoidXNlcl9tbmFtZSI7czo1OiJsYWJlbCI7czoxMToiTWlkZGxlIE5hbWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InVzZXJfbG5hbWUiO3M6NToibGFiZWwiO3M6OToiTGFzdCBOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoidXNlcl9lbWFpbCI7czo1OiJsYWJlbCI7czo1OiJFbWFpbCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6InVzZXJfY29udGFjdF9ubyI7czo1OiJsYWJlbCI7czo3OiJDb250YWN0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJkZXBhcnRtZW50LmRlcF9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoicm9sZXMubmFtZSI7czo1OiJsYWJlbCI7czo1OiJSb2xlcyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiZGM1YTIzOTlhMGU0YzhjZTRlZmNjNTE4ZjE1Yjg3M2VfY29sdW1ucyI7YTo3OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTg6ImVxdWlwbWVudC5lcW1fbmFtZSI7czo1OiJsYWJlbCI7czo5OiJFcXVpcG1lbnQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJkZXBhcnRtZW50LmRlcF9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyMjoiY29uZHVjdGVkQnkudXNlcl9mbmFtZSI7czo1OiJsYWJlbCI7czoxMjoiSW5zcGVjdGVkIGJ5IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJpbnNfZHQiO3M6NToibGFiZWwiO3M6MTU6Ikluc3BlY3Rpb24gRGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MjI6InN1Ym1pdHRlZEJ5LnVzZXJfZm5hbWUiO3M6NToibGFiZWwiO3M6MTI6IlN1Ym1pdHRlZCBieSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6Imluc19zdWJtaXR0ZWRfZHQiO3M6NToibGFiZWwiO3M6MTI6IlN1Ym1pdHRlZCBhdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MjI6Imluc3BlY3Rpb25faXRlbXNfY291bnQiO3M6NToibGFiZWwiO3M6MTE6Ikl0ZW1zIENvdW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiI4MTNjYjU4MmU3M2JjM2EwNWY1NjQ5MjU3YjY5YmY3MV9jb2x1bW5zIjthOjk6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJ3b19ubyI7czo1OiJsYWJlbCI7czo2OiJXTyBOby4iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE4OiJlcXVpcG1lbnQuZXFtX25hbWUiO3M6NToibGFiZWwiO3M6OToiRXF1aXBtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxOToiZGVwYXJ0bWVudC5kZXBfbmFtZSI7czo1OiJsYWJlbCI7czoxMDoiRGVwYXJ0bWVudCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6ODoid29fdGl0bGUiO3M6NToibGFiZWwiO3M6NToiVGl0bGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE4OiJwcmlvcml0eS5wcmlvX25hbWUiO3M6NToibGFiZWwiO3M6ODoiUHJpb3JpdHkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJzdGF0dXMuc3RhdHVzX3RpdGxlIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Nzoid29ya2VycyI7czo1OiJsYWJlbCI7czo3OiJXb3JrZXJzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoid29fY2xvc2VkX2R0IjtzOjU6ImxhYmVsIjtzOjk6IkNsb3NlZCBBdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjg7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6IndvX2NyZWF0ZWRfZHQiO3M6NToibGFiZWwiO3M6MTA6IkNyZWF0ZWQgQXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjZlOTc4NTFjMjlhMjk1NjEzNGZkZGU2NWRmZTcxNmUxX2NvbHVtbnMiO2E6ODp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJzdGF0dXMuc3RhdHVzX3RpdGxlIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTc6Imluc3BlY3Rpb24uaW5zX2lkIjtzOjU6ImxhYmVsIjtzOjEzOiJJbnNwZWN0aW9uIElEIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyOToiaW5zcGVjdGlvbi5lcXVpcG1lbnQuZXFtX25hbWUiO3M6NToibGFiZWwiO3M6OToiRXF1aXBtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyNDoiaW5zaV9jbGlfbmFtZV9mb3JfcmVjb3JkIjtzOjU6ImxhYmVsIjtzOjQ6IlRhc2siO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJpbnNpX3Jlc3VsdCI7czo1OiJsYWJlbCI7czo2OiJSZXN1bHQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJpbnNpX3JlbWFya3MiO3M6NToibGFiZWwiO3M6NzoiUmVtYXJrcyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6Imluc2lfY2xvc2VkX2R0IjtzOjU6ImxhYmVsIjtzOjk6IkNsb3NlZCBhdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjc7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Mjc6Imluc3BlY3Rpb24uaW5zX3N1Ym1pdHRlZF9kdCI7czo1OiJsYWJlbCI7czo5OiJUaW1lc3RhbXAiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjVhNzVhMGYzMWJjYWEyOGU4OTU0YjRkZGFlMTcyOTQ5X2NvbHVtbnMiO2E6MTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6ImVxbXRfbmFtZSI7czo1OiJsYWJlbCI7czo0OiJOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiIzNzU0MDZlYjU2OTQ5YmNjMTliMzY0MjU3NmIwYjZjOV9jb2x1bW5zIjthOjg6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJ3b19ubyI7czo1OiJsYWJlbCI7czo2OiJXTyBOby4iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJkZXBhcnRtZW50LmRlcF9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo4OiJ3b190aXRsZSI7czo1OiJsYWJlbCI7czo1OiJUaXRsZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTg6InByaW9yaXR5LnByaW9fbmFtZSI7czo1OiJsYWJlbCI7czo4OiJQcmlvcml0eSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6InN0YXR1cy5zdGF0dXNfdGl0bGUiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo3OiJ3b3JrZXJzIjtzOjU6ImxhYmVsIjtzOjc6IldvcmtlcnMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJ3b19jbG9zZWRfZHQiO3M6NToibGFiZWwiO3M6OToiQ2xvc2VkIEF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoid29fY3JlYXRlZF9kdCI7czo1OiJsYWJlbCI7czoxMDoiQ3JlYXRlZCBBdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiZWE4ZTVhZmIzZDdmY2QzYTcyYzJjMDBkNmE3NjJiNzZfY29sdW1ucyI7YTo3OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6InN0YXR1cy5zdGF0dXNfdGl0bGUiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoibXRfZXFtX2xvZyI7czo1OiJsYWJlbCI7czo5OiJFcXVpcG1lbnQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJkZXBhcnRtZW50LmRlcF9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToibXRfdGFza19sb2ciO3M6NToibGFiZWwiO3M6NDoiVGFzayI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToibXRfZHVlX2R0IjtzOjU6ImxhYmVsIjtzOjg6IkR1ZSBEYXRlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoibXRfY2xvc2VkX2R0IjtzOjU6ImxhYmVsIjtzOjY6IkNsb3NlZCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NToibXRfZHQiO3M6NToibGFiZWwiO3M6NzoiQ3JlYXRlZCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fX1zOjQwOiI1M2E0M2QxYzYwMmZmZWI3YTA2MTQxNGQwNmU4MjI4ZV9jb2x1bW5zIjthOjc6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJlcW1tX25hbWUiO3M6NToibGFiZWwiO3M6NDoiTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6InR5cGUuZXFtdF9uYW1lIjtzOjU6ImxhYmVsIjtzOjE0OiJFcXVpcG1lbnQgVHlwZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTg6ImNhdGVnb3J5LmVxbWNfbmFtZSI7czo1OiJsYWJlbCI7czo4OiJDYXRlZ29yeSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6ImJyYW5kLmVxbWJfbmFtZSI7czo1OiJsYWJlbCI7czo1OiJCcmFuZCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6ImZ1ZWxfdHlwZS5mdWVsX25hbWUiO3M6NToibGFiZWwiO3M6OToiRnVlbCBUeXBlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyMjoiZXFtbV9tYXhfY2FwYWNpdHlfdG9ucyI7czo1OiJsYWJlbCI7czoxOToiTWF4IENhcGFjaXR5ICh0b25zKSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MjE6ImVxbW1fbWF4X3JlYWNoX21ldGVycyI7czo1OiJsYWJlbCI7czoxODoiTWF4IFJlYWNoIChtZXRlcnMpIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiJlNzJjNzNkMTViZTk2MDQ2NWY3ZTEwYjg2Mzc5NjZiMV9jb2x1bW5zIjthOjQ6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoidGFzay50YXNrX25hbWUiO3M6NToibGFiZWwiO3M6NDoiVGFzayI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6ImRlcGFydG1lbnQuZGVwX25hbWUiO3M6NToibGFiZWwiO3M6MTA6IkRlcGFydG1lbnQiO3M6ODoiaXNIaWRkZW4iO2I6MTtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjIyOiJldHNfZHVlX2VmZmVjdGl2aXR5X2R0IjtzOjU6ImxhYmVsIjtzOjE2OiJFZmZlY3Rpdml0eSBEYXRlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiZXRzX2R1ZV9kdCI7czo1OiJsYWJlbCI7czo4OiJEdWUgRGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiZTgzYjkwYzcxZjUwMGQ4NGI2NmM2MDI0MGJiZDkwYmJfY29sdW1ucyI7YTozOntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6InRhc2sudGFza19uYW1lIjtzOjU6ImxhYmVsIjtzOjQ6IlRhc2siO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE4OiJjcmVhdG9yLnVzZXJfZm5hbWUiO3M6NToibGFiZWwiO3M6ODoiQWRkZWQgQnkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJldGN0X2NyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6ODoiQWRkZWQgQXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjIwNzk5Nzg0YjdkYWU5OGJmOWU2NTFjNDFmNmI0NmViX2NvbHVtbnMiO2E6Nzp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJzdGF0dXMuc3RhdHVzX3RpdGxlIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6ImFjdGlvbi5hX3Bhc3RfdGVuc2UiO3M6NToibGFiZWwiO3M6MTY6Ikxhc3QgQWN0aW9uIE1hZGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJtdGxfcmVtYXJrcyI7czo1OiJsYWJlbCI7czo3OiJSZW1hcmtzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoibXRsX2R1ZV9kdCI7czo1OiJsYWJlbCI7czo4OiJEdWUgRGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6IndvcmtPcmRlci53b19ubyI7czo1OiJsYWJlbCI7czo0OiJXTyAjIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToibG9nQnkuZnVsbF9uYW1lIjtzOjU6ImxhYmVsIjtzOjk6IkxvZ2dlZCBCeSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NjoibXRsX2R0IjtzOjU6ImxhYmVsIjtzOjk6IlRpbWVzdGFtcCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiNmU4MTcyMGNlYWZiYzdiMTUxODQ2ZTU3MjA2M2M0OTBfY29sdW1ucyI7YTo2OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6InN0YXR1cy5zdGF0dXNfdGl0bGUiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNjoiaW5pbF9hY3Rpb25fbWFkZSI7czo1OiJsYWJlbCI7czoxNjoiTGFzdCBBY3Rpb24gTWFkZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6InVzZXIudXNlcl9mbmFtZSI7czo1OiJsYWJlbCI7czo5OiJMb2dnZWQgYnkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJpbmlsX3JlbWFya3MiO3M6NToibGFiZWwiO3M6NzoiUmVtYXJrcyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NzoiaW5pbF9kdCI7czo1OiJsYWJlbCI7czo5OiJUaW1lc3RhbXAiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJ3b3JrT3JkZXIud29fbm8iO3M6NToibGFiZWwiO3M6NDoiV08gIyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiZGRjMWQwOGViZWZhNjUyMjkwM2FiMWYzN2MzY2I4YWNfY29sdW1ucyI7YToyOntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToiZXFtY19uYW1lIjtzOjU6ImxhYmVsIjtzOjQ6Ik5hbWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJwYXJlbnRfcGF0aCI7czo1OiJsYWJlbCI7czo2OiJQYXJlbnQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjQxZWUxMjZmZjU4Yzc0NGY5M2FmMGI0MDg1ZGNkNjBlX2NvbHVtbnMiO2E6ODp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6IndvX25vIjtzOjU6ImxhYmVsIjtzOjY6IldPIE5vLiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTg6ImVxdWlwbWVudC5lcW1fbmFtZSI7czo1OiJsYWJlbCI7czo5OiJFcXVpcG1lbnQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJkZXBhcnRtZW50LmRlcF9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo4OiJ3b190aXRsZSI7czo1OiJsYWJlbCI7czo1OiJUaXRsZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTg6InByaW9yaXR5LnByaW9fbmFtZSI7czo1OiJsYWJlbCI7czo4OiJQcmlvcml0eSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTk6InN0YXR1cy5zdGF0dXNfdGl0bGUiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo3OiJ3b3JrZXJzIjtzOjU6ImxhYmVsIjtzOjc6IldvcmtlcnMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJ3b19jcmVhdGVkX2R0IjtzOjU6ImxhYmVsIjtzOjEwOiJDcmVhdGVkIEF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiI3MWM5ZTgxYjIzNmQ1N2UzNWNiYjIxYWIxZTlhNGM0M19jb2x1bW5zIjthOjU6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo0OiJuYW1lIjtzOjU6ImxhYmVsIjtzOjQ6Ik5hbWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJndWFyZF9uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJHdWFyZCBOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJ0ZWFtLm5hbWUiO3M6NToibGFiZWwiO3M6NDoiVGVhbSI7czo4OiJpc0hpZGRlbiI7YjoxO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTc6InBlcm1pc3Npb25zX2NvdW50IjtzOjU6ImxhYmVsIjtzOjExOiJQZXJtaXNzaW9ucyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTA6IlVwZGF0ZWQgQXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjM1MzM2YTgwODc3ODgzNGY4OTQxNzQ3MTEyYzI2NDNlX2NvbHVtbnMiO2E6NTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6NDoiTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6Imd1YXJkX25hbWUiO3M6NToibGFiZWwiO3M6MTA6Ikd1YXJkIE5hbWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6InRlYW0ubmFtZSI7czo1OiJsYWJlbCI7czo0OiJUZWFtIjtzOjg6ImlzSGlkZGVuIjtiOjE7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNzoicGVybWlzc2lvbnNfY291bnQiO3M6NToibGFiZWwiO3M6MTE6IlBlcm1pc3Npb25zIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoidXBkYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMDoiVXBkYXRlZCBBdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiZGQ4ZGNjYmRjM2ZmYWQ0MjNlMzY4NzQ0ZTRhYTI0OGJfY29sdW1ucyI7YTo1OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTc6ImNhdXNlci51c2VyX2ZuYW1lIjtzOjU6ImxhYmVsIjtzOjQ6IlVzZXIiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJzdWJqZWN0X3R5cGUiO3M6NToibGFiZWwiO3M6MTI6IlN1YmplY3QgVHlwZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NToiZXZlbnQiO3M6NToibGFiZWwiO3M6NToiRXZlbnQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJkZXNjcmlwdGlvbiI7czo1OiJsYWJlbCI7czoxMToiRGVzY3JpcHRpb24iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjQ6IldoZW4iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6Ijk1YWM0Y2EyZDAzNGZmMzZhMWMwN2IxZWE0MzQ4MGM4X2NvbHVtbnMiO2E6MTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6ImVxbWJfbmFtZSI7czo1OiJsYWJlbCI7czo0OiJOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiJmOGRiNjVmZmI2YTZjNzRjY2YyYzM5MmU3ZjNiMjE2NV9jb2x1bW5zIjthOjE6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJmdWVsX25hbWUiO3M6NToibGFiZWwiO3M6OToiRnVlbCBuYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiJlODA5MjFhODNiYWI5NjcwMTQ0OTdjZTgzNzgyZDMwY19jb2x1bW5zIjthOjc6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJ3b19ubyI7czo1OiJsYWJlbCI7czo2OiJXTyBOby4iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE4OiJlcXVpcG1lbnQuZXFtX25hbWUiO3M6NToibGFiZWwiO3M6OToiRXF1aXBtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxOToiZGVwYXJ0bWVudC5kZXBfbmFtZSI7czo1OiJsYWJlbCI7czoxMDoiRGVwYXJ0bWVudCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6ODoid29fdGl0bGUiO3M6NToibGFiZWwiO3M6NToiVGl0bGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE4OiJwcmlvcml0eS5wcmlvX25hbWUiO3M6NToibGFiZWwiO3M6ODoiUHJpb3JpdHkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJzdGF0dXMuc3RhdHVzX3RpdGxlIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6IndvX2NyZWF0ZWRfZHQiO3M6NToibGFiZWwiO3M6MTA6IkNyZWF0ZWQgQXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjE1N2EzZDY3MWViNjI3ZjUzNmQzYTAwN2QwYjY3ODkxX2NvbHVtbnMiO2E6NDp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6IndvbF9hX2xvZyI7czo1OiJsYWJlbCI7czoxMToiTGFzdCBBY3Rpb24iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE0OiJ3b2xfc3RhdHVzX2xvZyI7czo1OiJsYWJlbCI7czo2OiJTdGF0dXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJieS51c2VyX2ZuYW1lIjtzOjU6ImxhYmVsIjtzOjI6IkJ5IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJ3b2xfZHQiO3M6NToibGFiZWwiO3M6MTU6IkRhdGUgJmFtcDsgVGltZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319fXM6MTE6ImltcGVyc29uYXRlIjthOjI6e3M6NToiZ3VhcmQiO3M6Mzoid2ViIjtzOjc6ImJhY2tfdG8iO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcHAtdXNlcnM/dGFiPU9QUyI7fXM6MTU6ImltcGVyc29uYXRlZF9ieSI7aToxO3M6MTg6ImltcGVyc29uYXRvcl9ndWFyZCI7czozOiJ3ZWIiO3M6MjQ6ImltcGVyc29uYXRvcl9ndWFyZF91c2luZyI7czozOiJ3ZWIiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjExO30=', 1781770684);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `site_name` varchar(255) NOT NULL DEFAULT 'EMMS',
  `site_logo` varchar(255) DEFAULT NULL,
  `site_favicon` varchar(255) DEFAULT NULL,
  `site_primary_color` varchar(255) NOT NULL DEFAULT 'amber',
  `site_font_family` varchar(255) NOT NULL DEFAULT 'Inter',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `site_name`, `site_logo`, `site_favicon`, `site_primary_color`, `site_font_family`, `created_at`, `updated_at`) VALUES
(1, 'EMMS', 'site-logo/01KTV1Z8QX5DRHW463070TBXBK.png', 'site-favicon/01KV57VWXG7J07CP30YMV7GXD9.png', 'indigo', 'Poppins', '2026-06-11 01:58:39', '2026-06-16 00:58:48');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `status_id` varchar(10) NOT NULL,
  `status_title` varchar(40) NOT NULL,
  `status_color` varchar(255) NOT NULL DEFAULT 'gray',
  `status_icon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`status_id`, `status_title`, `status_color`, `status_icon`) VALUES
('appr', 'Approved', 'success', ''),
('cmp', 'Completed', 'success', 'heroicon-o-check-circle'),
('cnc', 'Cancelled', 'danger', 'heroicon-o-x-circle'),
('drg', 'Disregarded', 'gray', 'heroicon-o-no-symbol'),
('inprog', 'In-Progress', 'info', 'heroicon-o-play-circle'),
('pca', 'Pending Completion Approval', 'primary', 'heroicon-o-check-badge'),
('pnd', 'Pending', 'warning', 'heroicon-o-clock'),
('rca', 'Rejected Completion Approval', 'danger', ''),
('rej', 'Rejected', 'danger', 'heroicon-o-exclamation-circle'),
('snz', 'Snoozed', 'gray', 'heroicon-o-bell-slash');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `task_dep_id` bigint(20) UNSIGNED NOT NULL,
  `task_tut_id` int(11) NOT NULL,
  `task_created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `task_created_at` datetime NOT NULL,
  `task_last_updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `task_last_updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `task_name`, `task_dep_id`, `task_tut_id`, `task_created_by`, `task_created_at`, `task_last_updated_by`, `task_last_updated_at`) VALUES
(2, 'Change Oil', 2, 2, 3, '2026-05-21 14:57:45', 3, '2026-05-21 14:57:45'),
(6, 'Change Filter', 2, 2, 3, '2026-06-01 16:57:02', 3, '2026-06-01 16:57:02'),
(7, 'Check Tires', 2, 1, 3, '2026-06-04 09:53:34', 3, '2026-06-04 09:53:34'),
(8, 'Check if Lubricated', 2, 1, 3, '2026-06-05 13:21:55', 3, '2026-06-05 13:21:55'),
(11, 'Change Oil', 1, 2, 1, '2026-06-06 01:26:55', 1, '2026-06-06 01:26:55'),
(12, 'Change Oil', 4, 2, 7, '2026-06-09 15:29:13', 7, '2026-06-09 15:29:13'),
(14, 'Change Filter', 4, 2, 7, '2026-06-15 08:25:08', 7, '2026-06-15 08:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `task_usage_types`
--

CREATE TABLE `task_usage_types` (
  `tut_id` int(11) NOT NULL,
  `tut_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_usage_types`
--

INSERT INTO `task_usage_types` (`tut_id`, `tut_name`) VALUES
(1, 'Pre-Operational Inspection'),
(2, 'Scheduled Preventive Maintenance');

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
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '2026-05-20 07:59:40', '$2y$12$yPx04h.cb0sabvAnwLVe3u7I.DdF/Qhb537LC5bQ7pdRjmFO7tn2.', '4dIiCqzf1w', '2026-05-20 07:59:40', '2026-05-20 07:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `worker_reports`
--

CREATE TABLE `worker_reports` (
  `wr_rs_id` bigint(20) UNSIGNED NOT NULL,
  `wr_worker_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `worker_reports`
--

INSERT INTO `worker_reports` (`wr_rs_id`, `wr_worker_id`) VALUES
(10, 4),
(10, 5),
(11, 4),
(11, 5),
(16, 4);

-- --------------------------------------------------------

--
-- Table structure for table `work_orders`
--

CREATE TABLE `work_orders` (
  `wo_id` bigint(20) UNSIGNED NOT NULL,
  `wo_no` varchar(255) NOT NULL,
  `wo_eqm_id` bigint(20) UNSIGNED NOT NULL,
  `wo_dep_id` bigint(20) UNSIGNED NOT NULL,
  `wo_mt_id` bigint(20) UNSIGNED DEFAULT NULL,
  `wo_insi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `wo_title` text NOT NULL,
  `wo_req_desc` text DEFAULT NULL,
  `wo_desc` text DEFAULT NULL,
  `wo_prio_id` int(10) UNSIGNED NOT NULL,
  `wo_status_id` varchar(255) NOT NULL,
  `wo_attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`wo_attachments`)),
  `wo_created_by` bigint(20) UNSIGNED NOT NULL,
  `wo_created_dt` datetime NOT NULL,
  `wo_closed_dt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_orders`
--

INSERT INTO `work_orders` (`wo_id`, `wo_no`, `wo_eqm_id`, `wo_dep_id`, `wo_mt_id`, `wo_insi_id`, `wo_title`, `wo_req_desc`, `wo_desc`, `wo_prio_id`, `wo_status_id`, `wo_attachments`, `wo_created_by`, `wo_created_dt`, `wo_closed_dt`) VALUES
(5, 'WO-MECH-260526001', 6, 2, NULL, NULL, 'try', NULL, 'try', 4, 'cmp', '[\"01KSHKYDRAR8KH152XYCY7W1FM.png\"]', 3, '2026-05-26 15:45:47', '2026-05-29 13:43:12'),
(16, 'WO-MECH-260605001', 6, 2, NULL, 41, 'x', NULL, 'xc', 1, 'cnc', '[]', 3, '2026-06-05 14:31:50', '2026-06-17 11:10:11'),
(17, 'WO-MECH-260605002', 6, 2, NULL, 47, 'asfd', NULL, 'dsf', 1, 'inprog', '[]', 3, '2026-06-05 16:14:25', NULL),
(18, 'WO-MECH-260606001', 6, 2, NULL, NULL, 'Equipment needs fix', NULL, 'bigla nalang huminto', 1, 'cmp', '[\"01KTCAKB6P4K4V8S2BV6HS646S.jpg\",\"01KTCAKB6SZK3MV8FAQ498464N.jpg\"]', 3, '2026-06-06 00:41:57', '2026-06-06 00:56:42'),
(19, 'WO-MECH-260606002', 6, 2, NULL, 52, 'Machine Repair', NULL, 'may sira sa ganito na piyesa', 1, 'cnc', '[]', 3, '2026-06-06 01:18:22', '2026-06-06 01:20:16'),
(20, 'WO-MECH-260608001', 6, 2, NULL, 54, 'a', NULL, 'a', 1, 'inprog', '[]', 3, '2026-06-08 00:16:25', NULL),
(21, 'WO-MECH-260608002', 6, 2, NULL, 60, 'b', NULL, 'b', 3, 'inprog', '[]', 3, '2026-06-08 00:31:28', NULL),
(22, 'WO-MECH-260608003', 6, 2, NULL, 69, 'hh', NULL, 'hyjh', 3, 'inprog', '[]', 3, '2026-06-08 10:51:09', NULL),
(29, 'WO-MECH-260608004', 6, 2, 24, NULL, '56', NULL, '56', 3, 'cnc', '[]', 3, '2026-06-08 14:39:09', '2026-06-08 14:39:28'),
(30, 'WO-MECH-260608005', 6, 2, NULL, NULL, '4545', NULL, '45445', 3, 'inprog', '[]', 3, '2026-06-08 15:42:46', NULL),
(31, 'WO-MECH-260611001', 6, 2, NULL, NULL, '66', NULL, '66', 1, 'inprog', '[]', 3, '2026-06-11 10:55:50', NULL),
(32, 'WO-MECH-260616001', 6, 2, NULL, NULL, 'test', 'test', NULL, 1, 'rej', '[]', 11, '2026-06-16 10:35:41', '2026-06-16 11:11:34'),
(33, 'WO-MECH-260616002', 6, 2, NULL, NULL, 'Voluptatem dignissim', 'Explicabo Qui est s', 'test', 1, 'inprog', '[]', 11, '2026-06-16 11:13:46', NULL),
(34, 'WO-MECH-260616003', 6, 2, NULL, NULL, 'tt', 'tt', NULL, 3, 'rej', '[]', 11, '2026-06-16 13:15:56', '2026-06-16 16:14:07'),
(35, 'WO-MECH-260616004', 6, 2, NULL, NULL, 'te', 'te', NULL, 2, 'rej', '[]', 11, '2026-06-16 13:21:02', '2026-06-16 16:09:41'),
(36, 'WO-MECH-260616005', 6, 2, NULL, NULL, 'ere', 'er', 'ok', 1, 'inprog', '[]', 11, '2026-06-16 13:21:49', NULL),
(37, 'WO-MECH-260616006', 6, 2, NULL, NULL, 'tty', 'tty', NULL, 4, 'rej', '[]', 11, '2026-06-16 14:57:07', '2026-06-16 15:19:41'),
(38, 'WO-ELEC-260618001', 5, 3, NULL, NULL, 'weew', 'wererw', NULL, 1, 'pnd', '[]', 11, '2026-06-18 15:57:05', NULL),
(39, 'WO-ELEC-260618002', 5, 3, NULL, NULL, 'dded', 'd', NULL, 1, 'cnc', '[]', 11, '2026-06-18 15:58:44', '2026-06-18 16:08:49'),
(40, 'WO-ELEC-260618003', 5, 3, NULL, NULL, 'Doloremque nostrud v', 'Aliqua Molestias no', NULL, 1, 'cnc', '[]', 11, '2026-06-18 16:16:53', '2026-06-18 16:17:54');

-- --------------------------------------------------------

--
-- Table structure for table `work_order_assignments`
--

CREATE TABLE `work_order_assignments` (
  `woa_wo_id` bigint(20) UNSIGNED NOT NULL,
  `woa_worker_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_order_assignments`
--

INSERT INTO `work_order_assignments` (`woa_wo_id`, `woa_worker_id`) VALUES
(5, 4),
(5, 5),
(16, 4),
(16, 5),
(17, 4),
(18, 4),
(18, 5),
(19, 5),
(20, 4),
(20, 5),
(21, 4),
(21, 5),
(22, 4),
(29, 4),
(29, 5),
(30, 4),
(30, 5),
(31, 4),
(33, 4),
(33, 5),
(36, 5);

-- --------------------------------------------------------

--
-- Table structure for table `work_order_logs`
--

CREATE TABLE `work_order_logs` (
  `wol_id` bigint(20) UNSIGNED NOT NULL,
  `wol_wo_id` bigint(20) UNSIGNED NOT NULL,
  `wol_a_id` varchar(10) NOT NULL,
  `wol_status_id` varchar(10) NOT NULL,
  `wol_a_log` varchar(255) NOT NULL,
  `wol_note` text DEFAULT NULL,
  `wol_status_log` varchar(255) NOT NULL,
  `wol_by` bigint(20) UNSIGNED NOT NULL,
  `wol_dt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_order_logs`
--

INSERT INTO `work_order_logs` (`wol_id`, `wol_wo_id`, `wol_a_id`, `wol_status_id`, `wol_a_log`, `wol_note`, `wol_status_log`, `wol_by`, `wol_dt`) VALUES
(1, 5, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-05-26 15:45:47'),
(12, 5, 'reqcom', 'pca', 'Requested Completion Approval', NULL, 'Pending Completion Approval', 4, '2026-05-29 09:56:47'),
(19, 5, 'approve', 'cmp', 'Approved', NULL, 'Completed', 3, '2026-05-29 13:43:12'),
(30, 16, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-05 14:31:50'),
(32, 17, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-05 16:14:25'),
(33, 17, 'cancel', 'cnc', 'Cancelled', 'dfgh', 'Cancelled', 3, '2026-06-05 16:18:51'),
(34, 18, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-06 00:41:57'),
(35, 18, 'reqcom', 'pca', 'Requested Completion Approval', 'iujij', 'Pending Completion Approval', 5, '2026-06-06 00:53:32'),
(36, 18, 'approve', 'cmp', 'Approved', 'ok na', 'Completed', 3, '2026-06-06 00:56:42'),
(37, 19, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-06 01:18:22'),
(38, 19, 'cancel', 'cnc', 'Cancelled', 'false ', 'Cancelled', 3, '2026-06-06 01:20:16'),
(39, 20, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-08 00:16:25'),
(40, 21, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-08 00:31:28'),
(41, 22, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-08 10:51:09'),
(48, 29, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-08 14:39:09'),
(49, 29, 'cancel', 'cnc', 'Cancelled', 'dv', 'Cancelled', 3, '2026-06-08 14:39:28'),
(50, 30, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-08 15:42:46'),
(51, 31, 'create', 'inprog', 'Created', NULL, 'In-Progress', 3, '2026-06-11 10:55:50'),
(52, 32, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-16 10:35:41'),
(53, 32, 'reject', 'rej', 'Rejected', 'test', 'Rejected', 3, '2026-06-16 11:11:34'),
(54, 33, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-16 11:13:46'),
(57, 33, 'approve', 'inprog', 'Approved', 'test', 'In-Progress', 3, '2026-06-16 11:27:02'),
(58, 34, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-16 13:15:56'),
(59, 35, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-16 13:21:02'),
(60, 36, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-16 13:21:49'),
(61, 37, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-16 14:57:07'),
(62, 37, 'reject', 'rej', 'Rejected', 'rejected', 'Rejected', 3, '2026-06-16 15:19:41'),
(63, 36, 'approve', 'inprog', 'Approved', 'ok', 'In-Progress', 3, '2026-06-16 15:20:35'),
(64, 35, 'reject', 'rej', 'Rejected', 'ty', 'Rejected', 3, '2026-06-16 16:09:41'),
(65, 34, 'reject', 'rej', 'Rejected', 'wwewew', 'Rejected', 3, '2026-06-16 16:14:07'),
(67, 16, 'cancel', 'cnc', 'Cancelled', 'k', 'Cancelled', 3, '2026-06-17 11:10:11'),
(68, 38, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-18 15:57:05'),
(69, 39, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-18 15:58:44'),
(70, 39, 'cancel', 'cnc', 'Cancelled', 'df', 'Cancelled', 11, '2026-06-18 16:08:49'),
(71, 40, 'create', 'pnd', 'Created', NULL, 'Pending', 11, '2026-06-18 16:16:53'),
(72, 40, 'cancel', 'cnc', 'Cancelled', 'f', 'Cancelled', 11, '2026-06-18 16:17:54');

-- --------------------------------------------------------

--
-- Table structure for table `work_order_log_updates`
--

CREATE TABLE `work_order_log_updates` (
  `wolu_id` bigint(20) UNSIGNED NOT NULL,
  `wolu_wo_id` bigint(20) UNSIGNED NOT NULL,
  `wolu_update_note` text NOT NULL,
  `wolu_attachments` text DEFAULT NULL,
  `wolu_by` bigint(20) UNSIGNED NOT NULL,
  `wolu_dt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_order_log_updates`
--

INSERT INTO `work_order_log_updates` (`wolu_id`, `wolu_wo_id`, `wolu_update_note`, `wolu_attachments`, `wolu_by`, `wolu_dt`) VALUES
(8, 5, 'updates', '[\"01KSHNBXWE3TARX784K6ZZ7FSV.jpeg\",\"01KSHNBXWJYBYJ06KQBN77YC6M.jpeg\"]', 1, '2026-05-26 16:10:38'),
(9, 5, 'ed', '[\"01KSHNJ2DWFFGNCW1W1EZV0HMW.png\"]', 1, '2026-05-26 16:14:00'),
(10, 5, 'dfdf', '[\"01KSHQ379MRCWG74VD9VJPTG04.png\",\"01KSHQ379S2QNF2G6ZCDJGS65V.png\"]', 3, '2026-05-26 16:40:50'),
(11, 5, 'est', '[\"01KSPV6ANHRKN6GENGEG96PHQ4.png\"]', 5, '2026-05-28 16:28:38'),
(12, 5, 'f', '[]', 4, '2026-05-29 09:02:56'),
(13, 18, 'ito piyesa pang ayos', '[\"01KTCB3C5K8Q9P6R2ZNWY99X1R.jpg\"]', 3, '2026-06-06 00:50:42'),
(14, 18, 'ok', '[\"01KTCB67V6KPV50VFK14KR7K5Y.png\",\"01KTCB67V9SQ6Y9PTAB7Z5MNM8.png\"]', 5, '2026-06-06 00:52:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`a_id`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_settings_key_unique` (`key`);

--
-- Indexes for table `app_users`
--
ALTER TABLE `app_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `app_users_user_email_unique` (`user_email`),
  ADD KEY `app_users_user_dep_id_foreign` (`user_dep_id`);

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
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dep_id`);

--
-- Indexes for table `equipment_brands`
--
ALTER TABLE `equipment_brands`
  ADD PRIMARY KEY (`eqmb_id`);

--
-- Indexes for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD PRIMARY KEY (`eqmc_id`),
  ADD KEY `equipment_categories_eqmc_parent_id_foreign` (`eqmc_parent_id`);

--
-- Indexes for table `equipment_models`
--
ALTER TABLE `equipment_models`
  ADD PRIMARY KEY (`eqmm_id`),
  ADD KEY `equipment_models_eqmm_eqmc_id_foreign` (`eqmm_eqmc_id`),
  ADD KEY `equipment_models_eqmm_brand_id_foreign` (`eqmm_brand_id`),
  ADD KEY `equipment_models_eqmm_fuel_type_foreign` (`eqmm_fuel_type`),
  ADD KEY `equipment_models_eqmm_eqmt_id_foreign` (`eqmm_eqmt_id`);

--
-- Indexes for table `equipment_tasks_schedules`
--
ALTER TABLE `equipment_tasks_schedules`
  ADD PRIMARY KEY (`ets_id`),
  ADD UNIQUE KEY `ets_dep_eqm_task_unique` (`ets_dep_id`,`ets_eqm_id`,`ets_task_id`),
  ADD KEY `equipment_tasks_schedules_ets_eqm_id_foreign` (`ets_eqm_id`),
  ADD KEY `equipment_tasks_schedules_ets_assigned_by_foreign` (`ets_assigned_by`),
  ADD KEY `equipment_tasks_schedules_ets_last_assigned_by_foreign` (`ets_last_assigned_by`),
  ADD KEY `equipment_tasks_schedules_ets_task_id_foreign` (`ets_task_id`);

--
-- Indexes for table `equipment_task_checklist_template`
--
ALTER TABLE `equipment_task_checklist_template`
  ADD PRIMARY KEY (`etct_id`),
  ADD UNIQUE KEY `etct_dep_eqm_task_unique` (`etct_dep_id`,`etct_eqm_id`,`etct_task_id`),
  ADD KEY `equipment_task_checklist_template_etct_eqm_id_foreign` (`etct_eqm_id`),
  ADD KEY `equipment_task_checklist_template_etct_task_id_foreign` (`etct_task_id`),
  ADD KEY `equipment_task_checklist_template_etct_created_by_foreign` (`etct_created_by`);

--
-- Indexes for table `equipment_types`
--
ALTER TABLE `equipment_types`
  ADD PRIMARY KEY (`eqmt_id`),
  ADD UNIQUE KEY `equipment_types_eqmt_name_unique` (`eqmt_name`);

--
-- Indexes for table `equipment_units`
--
ALTER TABLE `equipment_units`
  ADD PRIMARY KEY (`eqm_id`),
  ADD UNIQUE KEY `equipment_units_eqm_prc_code_unique` (`eqm_prc_code`),
  ADD UNIQUE KEY `equipment_units_eqm_serial_num_unique` (`eqm_serial_num`),
  ADD KEY `equipment_units_eqm_eqmm_id_foreign` (`eqm_eqmm_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fuel_types`
--
ALTER TABLE `fuel_types`
  ADD PRIMARY KEY (`fuel_id`);

--
-- Indexes for table `inspections`
--
ALTER TABLE `inspections`
  ADD PRIMARY KEY (`ins_id`),
  ADD KEY `inspections_ins_dep_id_foreign` (`ins_dep_id`),
  ADD KEY `inspections_ins_eqm_id_foreign` (`ins_eqm_id`),
  ADD KEY `inspections_ins_by_foreign` (`ins_by`),
  ADD KEY `inspections_ins_submitted_by_foreign` (`ins_submitted_by`);

--
-- Indexes for table `inspection_items`
--
ALTER TABLE `inspection_items`
  ADD PRIMARY KEY (`insi_id`),
  ADD KEY `inspection_items_insi_ins_id_foreign` (`insi_ins_id`),
  ADD KEY `inspection_items_insi_task_id_foreign` (`insi_task_id`),
  ADD KEY `inspection_items_insi_result_foreign` (`insi_result`),
  ADD KEY `inspection_items_insi_status_id_foreign` (`insi_status_id`);

--
-- Indexes for table `inspection_item_logs`
--
ALTER TABLE `inspection_item_logs`
  ADD PRIMARY KEY (`inil_id`),
  ADD KEY `inspection_item_logs_inil_insi_id_foreign` (`inil_insi_id`),
  ADD KEY `inspection_item_logs_inil_by_foreign` (`inil_by`),
  ADD KEY `inspection_item_logs_inil_wo_id_foreign` (`inil_wo_id`),
  ADD KEY `inspection_item_logs_inil_a_id_foreign` (`inil_a_id`),
  ADD KEY `inspection_item_logs_inil_status_id_foreign` (`inil_status_id`);

--
-- Indexes for table `inspection_results`
--
ALTER TABLE `inspection_results`
  ADD PRIMARY KEY (`insr_code`);

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
-- Indexes for table `maintenance_tasks`
--
ALTER TABLE `maintenance_tasks`
  ADD PRIMARY KEY (`mt_id`),
  ADD KEY `maintenance_tasks_mt_eqm_id_foreign` (`mt_eqm_id`),
  ADD KEY `maintenance_tasks_mt_dep_id_foreign` (`mt_dep_id`),
  ADD KEY `maintenance_tasks_mt_status_id_foreign` (`mt_status_id`),
  ADD KEY `maintenance_tasks_mt_by_foreign` (`mt_by`),
  ADD KEY `maintenance_tasks_mt_batch_id_index` (`mt_batch_id`),
  ADD KEY `maintenance_tasks_mt_task_id_foreign` (`mt_task_id`);

--
-- Indexes for table `maintenance_task_logs`
--
ALTER TABLE `maintenance_task_logs`
  ADD PRIMARY KEY (`mtl_id`),
  ADD KEY `maintenance_task_logs_mtl_mt_id_foreign` (`mtl_mt_id`),
  ADD KEY `maintenance_task_logs_mtl_status_id_foreign` (`mtl_status_id`),
  ADD KEY `maintenance_task_logs_mtl_last_act_made_foreign` (`mtl_last_act_made`),
  ADD KEY `maintenance_task_logs_mtl_by_foreign` (`mtl_by`),
  ADD KEY `maintenance_task_logs_mtl_wo_id_foreign` (`mtl_wo_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `priorities`
--
ALTER TABLE `priorities`
  ADD PRIMARY KEY (`prio_id`);

--
-- Indexes for table `report_submissions`
--
ALTER TABLE `report_submissions`
  ADD PRIMARY KEY (`rs_id`),
  ADD KEY `report_submissions_rs_wo_id_foreign` (`rs_wo_id`),
  ADD KEY `report_submissions_rs_submitted_by_foreign` (`rs_submitted_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `tasks_task_dep_id_foreign` (`task_dep_id`),
  ADD KEY `tasks_task_tut_id_foreign` (`task_tut_id`),
  ADD KEY `tasks_task_created_by_foreign` (`task_created_by`),
  ADD KEY `tasks_task_last_updated_by_foreign` (`task_last_updated_by`);

--
-- Indexes for table `task_usage_types`
--
ALTER TABLE `task_usage_types`
  ADD PRIMARY KEY (`tut_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `worker_reports`
--
ALTER TABLE `worker_reports`
  ADD PRIMARY KEY (`wr_rs_id`,`wr_worker_id`),
  ADD KEY `worker_reports_wr_worker_id_foreign` (`wr_worker_id`);

--
-- Indexes for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD PRIMARY KEY (`wo_id`),
  ADD UNIQUE KEY `work_orders_wo_no_unique` (`wo_no`),
  ADD KEY `work_orders_wo_eqm_id_foreign` (`wo_eqm_id`),
  ADD KEY `work_orders_wo_dep_id_foreign` (`wo_dep_id`),
  ADD KEY `work_orders_wo_mt_id_foreign` (`wo_mt_id`),
  ADD KEY `work_orders_wo_prio_id_foreign` (`wo_prio_id`),
  ADD KEY `work_orders_wo_status_id_foreign` (`wo_status_id`),
  ADD KEY `work_orders_wo_created_by_foreign` (`wo_created_by`),
  ADD KEY `work_orders_wo_insi_id_foreign` (`wo_insi_id`);

--
-- Indexes for table `work_order_assignments`
--
ALTER TABLE `work_order_assignments`
  ADD PRIMARY KEY (`woa_wo_id`,`woa_worker_id`),
  ADD KEY `work_order_assignments_woa_worker_id_foreign` (`woa_worker_id`);

--
-- Indexes for table `work_order_logs`
--
ALTER TABLE `work_order_logs`
  ADD PRIMARY KEY (`wol_id`),
  ADD KEY `work_order_logs_wol_wo_id_foreign` (`wol_wo_id`),
  ADD KEY `work_order_logs_wol_a_id_foreign` (`wol_a_id`),
  ADD KEY `work_order_logs_wol_status_id_foreign` (`wol_status_id`),
  ADD KEY `work_order_logs_wol_by_foreign` (`wol_by`);

--
-- Indexes for table `work_order_log_updates`
--
ALTER TABLE `work_order_log_updates`
  ADD PRIMARY KEY (`wolu_id`),
  ADD KEY `work_order_log_updates_wolu_wo_id_foreign` (`wolu_wo_id`),
  ADD KEY `work_order_log_updates_wolu_by_foreign` (`wolu_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `app_users`
--
ALTER TABLE `app_users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dep_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `equipment_brands`
--
ALTER TABLE `equipment_brands`
  MODIFY `eqmb_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  MODIFY `eqmc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipment_models`
--
ALTER TABLE `equipment_models`
  MODIFY `eqmm_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipment_tasks_schedules`
--
ALTER TABLE `equipment_tasks_schedules`
  MODIFY `ets_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `equipment_task_checklist_template`
--
ALTER TABLE `equipment_task_checklist_template`
  MODIFY `etct_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `equipment_types`
--
ALTER TABLE `equipment_types`
  MODIFY `eqmt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `equipment_units`
--
ALTER TABLE `equipment_units`
  MODIFY `eqm_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1506;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `fuel_types`
--
ALTER TABLE `fuel_types`
  MODIFY `fuel_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `ins_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `inspection_items`
--
ALTER TABLE `inspection_items`
  MODIFY `insi_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `inspection_item_logs`
--
ALTER TABLE `inspection_item_logs`
  MODIFY `inil_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `maintenance_tasks`
--
ALTER TABLE `maintenance_tasks`
  MODIFY `mt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `maintenance_task_logs`
--
ALTER TABLE `maintenance_task_logs`
  MODIFY `mtl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT for table `priorities`
--
ALTER TABLE `priorities`
  MODIFY `prio_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `report_submissions`
--
ALTER TABLE `report_submissions`
  MODIFY `rs_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `task_usage_types`
--
ALTER TABLE `task_usage_types`
  MODIFY `tut_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `work_orders`
--
ALTER TABLE `work_orders`
  MODIFY `wo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `work_order_logs`
--
ALTER TABLE `work_order_logs`
  MODIFY `wol_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `work_order_log_updates`
--
ALTER TABLE `work_order_log_updates`
  MODIFY `wolu_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app_users`
--
ALTER TABLE `app_users`
  ADD CONSTRAINT `app_users_user_dep_id_foreign` FOREIGN KEY (`user_dep_id`) REFERENCES `departments` (`dep_id`);

--
-- Constraints for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD CONSTRAINT `equipment_categories_eqmc_parent_id_foreign` FOREIGN KEY (`eqmc_parent_id`) REFERENCES `equipment_categories` (`eqmc_id`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_models`
--
ALTER TABLE `equipment_models`
  ADD CONSTRAINT `equipment_models_eqmm_brand_id_foreign` FOREIGN KEY (`eqmm_brand_id`) REFERENCES `equipment_brands` (`eqmb_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_models_eqmm_eqmc_id_foreign` FOREIGN KEY (`eqmm_eqmc_id`) REFERENCES `equipment_categories` (`eqmc_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_models_eqmm_eqmt_id_foreign` FOREIGN KEY (`eqmm_eqmt_id`) REFERENCES `equipment_types` (`eqmt_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_models_eqmm_fuel_type_foreign` FOREIGN KEY (`eqmm_fuel_type`) REFERENCES `fuel_types` (`fuel_id`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_tasks_schedules`
--
ALTER TABLE `equipment_tasks_schedules`
  ADD CONSTRAINT `equipment_tasks_schedules_ets_assigned_by_foreign` FOREIGN KEY (`ets_assigned_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `equipment_tasks_schedules_ets_dep_id_foreign` FOREIGN KEY (`ets_dep_id`) REFERENCES `departments` (`dep_id`),
  ADD CONSTRAINT `equipment_tasks_schedules_ets_eqm_id_foreign` FOREIGN KEY (`ets_eqm_id`) REFERENCES `equipment_units` (`eqm_id`),
  ADD CONSTRAINT `equipment_tasks_schedules_ets_last_assigned_by_foreign` FOREIGN KEY (`ets_last_assigned_by`) REFERENCES `app_users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_tasks_schedules_ets_task_id_foreign` FOREIGN KEY (`ets_task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment_task_checklist_template`
--
ALTER TABLE `equipment_task_checklist_template`
  ADD CONSTRAINT `equipment_task_checklist_template_etct_created_by_foreign` FOREIGN KEY (`etct_created_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `equipment_task_checklist_template_etct_dep_id_foreign` FOREIGN KEY (`etct_dep_id`) REFERENCES `departments` (`dep_id`),
  ADD CONSTRAINT `equipment_task_checklist_template_etct_eqm_id_foreign` FOREIGN KEY (`etct_eqm_id`) REFERENCES `equipment_units` (`eqm_id`),
  ADD CONSTRAINT `equipment_task_checklist_template_etct_task_id_foreign` FOREIGN KEY (`etct_task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment_units`
--
ALTER TABLE `equipment_units`
  ADD CONSTRAINT `equipment_units_eqm_eqmm_id_foreign` FOREIGN KEY (`eqm_eqmm_id`) REFERENCES `equipment_models` (`eqmm_id`) ON DELETE CASCADE;

--
-- Constraints for table `inspections`
--
ALTER TABLE `inspections`
  ADD CONSTRAINT `inspections_ins_by_foreign` FOREIGN KEY (`ins_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `inspections_ins_dep_id_foreign` FOREIGN KEY (`ins_dep_id`) REFERENCES `departments` (`dep_id`),
  ADD CONSTRAINT `inspections_ins_eqm_id_foreign` FOREIGN KEY (`ins_eqm_id`) REFERENCES `equipment_units` (`eqm_id`),
  ADD CONSTRAINT `inspections_ins_submitted_by_foreign` FOREIGN KEY (`ins_submitted_by`) REFERENCES `app_users` (`user_id`);

--
-- Constraints for table `inspection_items`
--
ALTER TABLE `inspection_items`
  ADD CONSTRAINT `inspection_items_insi_ins_id_foreign` FOREIGN KEY (`insi_ins_id`) REFERENCES `inspections` (`ins_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inspection_items_insi_result_foreign` FOREIGN KEY (`insi_result`) REFERENCES `inspection_results` (`insr_code`),
  ADD CONSTRAINT `inspection_items_insi_status_id_foreign` FOREIGN KEY (`insi_status_id`) REFERENCES `statuses` (`status_id`),
  ADD CONSTRAINT `inspection_items_insi_task_id_foreign` FOREIGN KEY (`insi_task_id`) REFERENCES `tasks` (`task_id`) ON DELETE SET NULL;

--
-- Constraints for table `inspection_item_logs`
--
ALTER TABLE `inspection_item_logs`
  ADD CONSTRAINT `inspection_item_logs_inil_a_id_foreign` FOREIGN KEY (`inil_a_id`) REFERENCES `actions` (`a_id`),
  ADD CONSTRAINT `inspection_item_logs_inil_by_foreign` FOREIGN KEY (`inil_by`) REFERENCES `app_users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inspection_item_logs_inil_insi_id_foreign` FOREIGN KEY (`inil_insi_id`) REFERENCES `inspection_items` (`insi_id`),
  ADD CONSTRAINT `inspection_item_logs_inil_status_id_foreign` FOREIGN KEY (`inil_status_id`) REFERENCES `statuses` (`status_id`),
  ADD CONSTRAINT `inspection_item_logs_inil_wo_id_foreign` FOREIGN KEY (`inil_wo_id`) REFERENCES `work_orders` (`wo_id`);

--
-- Constraints for table `maintenance_tasks`
--
ALTER TABLE `maintenance_tasks`
  ADD CONSTRAINT `maintenance_tasks_mt_by_foreign` FOREIGN KEY (`mt_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `maintenance_tasks_mt_dep_id_foreign` FOREIGN KEY (`mt_dep_id`) REFERENCES `departments` (`dep_id`),
  ADD CONSTRAINT `maintenance_tasks_mt_eqm_id_foreign` FOREIGN KEY (`mt_eqm_id`) REFERENCES `equipment_units` (`eqm_id`),
  ADD CONSTRAINT `maintenance_tasks_mt_status_id_foreign` FOREIGN KEY (`mt_status_id`) REFERENCES `statuses` (`status_id`),
  ADD CONSTRAINT `maintenance_tasks_mt_task_id_foreign` FOREIGN KEY (`mt_task_id`) REFERENCES `tasks` (`task_id`) ON DELETE SET NULL;

--
-- Constraints for table `maintenance_task_logs`
--
ALTER TABLE `maintenance_task_logs`
  ADD CONSTRAINT `maintenance_task_logs_mtl_by_foreign` FOREIGN KEY (`mtl_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `maintenance_task_logs_mtl_last_act_made_foreign` FOREIGN KEY (`mtl_last_act_made`) REFERENCES `actions` (`a_id`),
  ADD CONSTRAINT `maintenance_task_logs_mtl_mt_id_foreign` FOREIGN KEY (`mtl_mt_id`) REFERENCES `maintenance_tasks` (`mt_id`),
  ADD CONSTRAINT `maintenance_task_logs_mtl_status_id_foreign` FOREIGN KEY (`mtl_status_id`) REFERENCES `statuses` (`status_id`),
  ADD CONSTRAINT `maintenance_task_logs_mtl_wo_id_foreign` FOREIGN KEY (`mtl_wo_id`) REFERENCES `work_orders` (`wo_id`) ON DELETE SET NULL;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_submissions`
--
ALTER TABLE `report_submissions`
  ADD CONSTRAINT `report_submissions_rs_submitted_by_foreign` FOREIGN KEY (`rs_submitted_by`) REFERENCES `app_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_submissions_rs_wo_id_foreign` FOREIGN KEY (`rs_wo_id`) REFERENCES `work_orders` (`wo_id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_task_created_by_foreign` FOREIGN KEY (`task_created_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `tasks_task_dep_id_foreign` FOREIGN KEY (`task_dep_id`) REFERENCES `departments` (`dep_id`),
  ADD CONSTRAINT `tasks_task_last_updated_by_foreign` FOREIGN KEY (`task_last_updated_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `tasks_task_tut_id_foreign` FOREIGN KEY (`task_tut_id`) REFERENCES `task_usage_types` (`tut_id`);

--
-- Constraints for table `worker_reports`
--
ALTER TABLE `worker_reports`
  ADD CONSTRAINT `worker_reports_wr_rs_id_foreign` FOREIGN KEY (`wr_rs_id`) REFERENCES `report_submissions` (`rs_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `worker_reports_wr_worker_id_foreign` FOREIGN KEY (`wr_worker_id`) REFERENCES `app_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD CONSTRAINT `work_orders_wo_created_by_foreign` FOREIGN KEY (`wo_created_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `work_orders_wo_dep_id_foreign` FOREIGN KEY (`wo_dep_id`) REFERENCES `departments` (`dep_id`),
  ADD CONSTRAINT `work_orders_wo_eqm_id_foreign` FOREIGN KEY (`wo_eqm_id`) REFERENCES `equipment_units` (`eqm_id`),
  ADD CONSTRAINT `work_orders_wo_insi_id_foreign` FOREIGN KEY (`wo_insi_id`) REFERENCES `inspection_items` (`insi_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `work_orders_wo_mt_id_foreign` FOREIGN KEY (`wo_mt_id`) REFERENCES `maintenance_tasks` (`mt_id`),
  ADD CONSTRAINT `work_orders_wo_prio_id_foreign` FOREIGN KEY (`wo_prio_id`) REFERENCES `priorities` (`prio_id`),
  ADD CONSTRAINT `work_orders_wo_status_id_foreign` FOREIGN KEY (`wo_status_id`) REFERENCES `statuses` (`status_id`);

--
-- Constraints for table `work_order_assignments`
--
ALTER TABLE `work_order_assignments`
  ADD CONSTRAINT `work_order_assignments_woa_wo_id_foreign` FOREIGN KEY (`woa_wo_id`) REFERENCES `work_orders` (`wo_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `work_order_assignments_woa_worker_id_foreign` FOREIGN KEY (`woa_worker_id`) REFERENCES `app_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `work_order_logs`
--
ALTER TABLE `work_order_logs`
  ADD CONSTRAINT `work_order_logs_wol_a_id_foreign` FOREIGN KEY (`wol_a_id`) REFERENCES `actions` (`a_id`),
  ADD CONSTRAINT `work_order_logs_wol_by_foreign` FOREIGN KEY (`wol_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `work_order_logs_wol_status_id_foreign` FOREIGN KEY (`wol_status_id`) REFERENCES `statuses` (`status_id`),
  ADD CONSTRAINT `work_order_logs_wol_wo_id_foreign` FOREIGN KEY (`wol_wo_id`) REFERENCES `work_orders` (`wo_id`);

--
-- Constraints for table `work_order_log_updates`
--
ALTER TABLE `work_order_log_updates`
  ADD CONSTRAINT `work_order_log_updates_wolu_by_foreign` FOREIGN KEY (`wolu_by`) REFERENCES `app_users` (`user_id`),
  ADD CONSTRAINT `work_order_log_updates_wolu_wo_id_foreign` FOREIGN KEY (`wolu_wo_id`) REFERENCES `work_orders` (`wo_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
