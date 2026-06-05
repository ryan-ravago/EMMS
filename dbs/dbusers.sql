-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 10:58 AM
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
-- Database: `dbusers`
--

-- --------------------------------------------------------

--
-- Table structure for table `app`
--

CREATE TABLE `app` (
  `appId` varchar(5) NOT NULL,
  `description` varchar(45) DEFAULT NULL,
  `appName` varchar(45) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `app`
--

INSERT INTO `app` (`appId`, `description`, `appName`, `active`) VALUES
('A-INV', 'ASC INVENTORY', 'ASC INVENTORY', 1),
('A-VM', 'ASC VESSEL MONITORING', 'ASC VESSEL MONITORING', 1),
('EBCA', 'EBC - AR', 'EBC-AR', 1),
('EBCAD', 'EBC - ALLDASH', 'EBC-ALLDASH', 1),
('EBCB', 'EBC - BILLING', 'EBC-BILLING', 1),
('EBCE', 'EBC - ETR', 'EBC-ETR', 1),
('EBCO', 'EBC - OPERATION', 'EBC-OPS', 1),
('EBCS', 'EBC - SALES', 'EBC-SALES', 1),
('EBCX', 'EBC - EXEC', 'EBC-EXEC', 1),
('P-LIQ', 'PURCHASING LIQUIDATION', 'PURCHASING LIQUIDATION', 1),
('PURJO', 'PURCHASING JOB ORDER', 'PURCHASING JOB ORDER', 1),
('SENGR', 'ENGINEER', 'SEO-ENGINEERING', 1),
('SOPS', 'OPERATIONS', 'SEO-OPERATIONS', 1),
('SSLS', 'SALES', 'SEO-SALES', 1),
('STRAN', 'TRANSPORT', 'SEO-TRANSPORT', 1);

-- --------------------------------------------------------

--
-- Table structure for table `appusr`
--

CREATE TABLE `appusr` (
  `appusrId` int(11) NOT NULL,
  `appId` varchar(5) DEFAULT NULL,
  `gUserName` varchar(64) DEFAULT NULL,
  `isActive` tinyint(4) DEFAULT NULL,
  `priviledgeCode` varchar(6) DEFAULT NULL,
  `dtCreated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appusr`
--

INSERT INTO `appusr` (`appusrId`, `appId`, `gUserName`, `isActive`, `priviledgeCode`, `dtCreated`) VALUES
(1, 'SOPS', 'angelo.gaspar@ravago.com.ph', 1, 'MGR', NULL),
(2, 'SSLS', 'test.sales', 1, 'MGR', NULL),
(3, 'SENGR', 'test.engr', 1, 'MGR', NULL),
(4, 'SOPS', 'test.ops', 1, 'MGR', NULL),
(5, 'SSLS', 'jackie.emata@ravago.com.ph', 0, 'MGR', NULL),
(6, 'SSLS', 'jamie.lopez@ravago.com.ph', 1, NULL, NULL),
(7, 'SSLS', 'yula.corpuz@ravago.com.ph', 1, NULL, NULL),
(8, 'SSLS', 'camille.serrano@ravago.com.ph', 1, NULL, NULL),
(9, 'SSLS', 'erlyn.delsocorro@ravago.com.ph', 1, NULL, NULL),
(10, 'SSLS', 'mira.balatucan@ravago.com.ph', 1, NULL, NULL),
(11, 'SSLS', 'joana.miranda@ravago.com.ph', 1, NULL, NULL),
(12, 'SSLS', 'christianne.manangan@ravago.com.ph', 1, NULL, NULL),
(13, 'SENGR', 'bernard.imbornal@ravago.com.ph', 1, 'MGR', NULL),
(14, 'SENGR', 'ruel.como@ravago.com.ph', 1, 'MGR', NULL),
(15, 'SENGR', 'arnold.sanpedro@ravago.com.ph', 1, NULL, NULL),
(16, 'SENGR', 'dave.canchela@ravago.com.ph', 1, NULL, NULL),
(17, 'SENGR', 'jake.dy@ravago.com.ph', 1, NULL, NULL),
(18, 'SENGR', 'pinky.tablizo@ravago.com.ph', 1, NULL, NULL),
(19, 'SENGR', 'ericka.delacruz@ravago.com.ph', 1, NULL, NULL),
(20, 'SENGR', 'rachel.toquia@ravago.com.ph', 1, 'MGR', NULL),
(21, 'SENGR', 'morris.carreon@ravago.com.ph', 1, NULL, NULL),
(22, 'SENGR', 'gutzier.gutierrez@ravago.com.ph', 1, NULL, NULL),
(23, 'SOPS', 'jayrica.gamba@ravago.com.ph', 1, 'MGR', NULL),
(24, 'SOPS', 'rufina.alli@ravago.com.ph', 1, 'MGR', NULL),
(25, 'SOPS', 'test.dcor', 1, 'DCOR', NULL),
(26, 'SSLS', 'may.avergonzado@ravago.com.ph', 1, NULL, NULL),
(27, 'SENGR', 'miguel.villaflor@ravago.com.ph', 1, NULL, NULL),
(28, 'SENGR', 'nicko.ornedo@ravago.com.ph', 1, NULL, NULL),
(29, 'SENGR', 'jerico.valena@ravago.com.ph', 1, NULL, NULL),
(30, 'SOPS', 'test.dcon', 1, 'DCON', NULL),
(31, 'SOPS', 'vanessa.bareng@ravago.com.ph', 1, 'MGR', NULL),
(32, 'STRAN', 'kerstine.ambito@ravago.com.ph', 1, 'MGR', NULL),
(33, 'SOPS', 'jeneah.subalisid@ravago.com.ph', 0, 'DCOR', NULL),
(34, 'SOPS', 'gleicel.jabajab@liberty', 1, 'MGR', NULL),
(35, 'STRAN', 'test.transport', 1, NULL, NULL),
(36, 'STRAN', 'bea.jacobe@ravago.com.ph', 1, 'MGR', NULL),
(37, 'STRAN', 'shallene.rogon@ravago.com.ph', 1, 'DCON', NULL),
(38, 'STRAN', 'pocholo.fajardo@liberty', 1, 'DCOR', NULL),
(39, 'STRAN', 'joyceann.parra@liberty', 1, 'DCOR', NULL),
(40, 'SENGR', 'edger.morales@ravago.com.ph', 1, NULL, NULL),
(41, 'EBCO', 'test.ops', 1, 'MGR', NULL),
(42, 'EBCE', 'test.etr', 1, 'MGR', '2024-08-01 00:00:00'),
(43, 'SENGR', 'meynard.sioson@ravago.com.ph', 1, NULL, NULL),
(44, 'EBCS', 'test.sales', 1, 'MGR', NULL),
(45, 'EBCA', 'test.ar', 1, NULL, NULL),
(46, 'SOPS', 'jeneah.subalisid@ravago.com.ph', 0, 'DCOR', NULL),
(47, 'EBCX', 'test.exec', 1, 'MGR', NULL),
(48, 'SENGR', 'mary.reyes@ravago.com.ph', 1, NULL, NULL),
(49, 'SENGR', 'irwin.samulde@ravago.com.ph', 1, NULL, NULL),
(50, 'SENGR', 'jofel.mapisa@ravago.com.ph', 1, NULL, NULL),
(51, 'EBCO', 'angelo.gaspar@ravago.com.ph', 1, NULL, NULL),
(52, 'EBCE', 'racquel.teope@ravago.com.ph', 1, 'MGR', NULL),
(53, 'EBCE', 'joyce.jimenez@ravago.com.ph', 0, NULL, NULL),
(54, 'EBCE', 'snooky.flores@ravago.com.ph', 0, 'MGR', NULL),
(55, 'EBCA', 'alyssa.carino@ravago.com.ph', 1, NULL, NULL),
(56, 'EBCE', 'joana.dres@ravago.com.ph', 1, 'MGR', NULL),
(57, 'EBCX', 'valerie.sy@ravago.com.ph', 0, NULL, NULL),
(58, 'EBCX', 'yolly.yap@ravago.com.ph', 1, NULL, NULL),
(59, 'EBCE', 'princes.navarro@ravago.com.ph', 1, NULL, NULL),
(60, 'EBCE', 'kemberly.orbiso@ravago.com.ph', 0, NULL, NULL),
(61, 'EBCE', 'betty.penalosa@ravago.com.ph', 0, NULL, NULL),
(69, 'SENGR', 'prince.delosreyes@ravago.com.ph', 1, NULL, '2024-11-27 00:00:00'),
(72, 'SENGR', 'joseph.florendo@ravago.com.ph', 1, NULL, '2025-02-04 00:00:00'),
(92, 'EBCE', 'sean.bautista@ravago.com.ph', 1, NULL, '2025-03-07 00:00:00'),
(93, 'EBCE', 'razel.cadiz@ravago.com.ph', 1, NULL, NULL),
(94, 'EBCA', 'jennelyn.torres@ravago.com.ph', 1, NULL, NULL),
(95, 'EBCB', 'joyce.jimenez@ravago.com.ph', 1, NULL, NULL),
(109, 'PURJO', 'test.sales', 1, 'STAFF', '2025-03-17 03:51:53'),
(110, 'PURJO', 'gleicel.jabajab@liberty', 1, 'HEAD', '2025-03-17 12:04:23'),
(111, 'PURJO', 'ryan094', 1, 'STAFF', '2025-03-18 03:27:43'),
(112, 'PURJO', 'ken', 1, 'STAFF', '2025-03-18 06:15:15'),
(113, 'PURJO', 'gelo', 1, 'STAFF', '2025-03-18 07:27:22'),
(114, 'PURJO', 'ern', 1, 'HEAD', '2025-03-21 03:16:12'),
(115, 'PURJO', 'testpurad', 1, 'PURAD', '2025-03-27 03:22:47'),
(116, 'PURJO', 'dia', 1, 'PURAD', '2025-03-31 06:28:28'),
(117, 'PURJO', 'rub', 1, 'STAFF', '2025-03-31 06:28:42'),
(120, 'PURJO', 'chad', 1, 'STAFF', '2025-05-09 03:14:26'),
(121, 'PURJO', 'marv', 1, 'STAFF', '2025-05-09 03:51:11'),
(122, 'PURJO', 'karl', 1, 'STAFF', '2025-05-09 03:51:22'),
(123, 'PURJO', 'nat', 1, 'STAFF', '2025-05-09 03:51:36'),
(124, 'PURJO', 'teststaff', 1, 'STAFF', '2025-05-09 08:07:36'),
(125, 'PURJO', 'testexecom', 1, 'EXECOM', '2025-05-13 01:07:01'),
(126, 'EBCE', 'kc.ellima@ravago.com.ph', 0, NULL, '2025-05-15 00:00:00'),
(127, 'EBCE', 'kemberly.orbiso@ravago.com.ph', 0, NULL, '2025-05-15 00:00:00'),
(128, 'EBCE', 'jhemalou.olendan@ravago.com.ph', 1, NULL, '2025-05-15 00:00:00'),
(129, 'EBCO', 'jeneah.subalisid@ravago.com.ph', 0, NULL, '2025-05-30 00:00:00'),
(130, 'EBCO', 'shallene.rogon@ravago.com.ph', 1, NULL, '2025-05-30 00:00:00'),
(131, 'EBCB', 'janet.sanchez@ravago.com.ph', 1, 'MGR', '2025-06-03 00:00:00'),
(132, 'EBCB', 'test.billing', 1, 'MGR', '2025-06-03 00:00:00'),
(134, 'EBCS', 'jackie.emata@ravago.com.ph', 0, 'MGR', NULL),
(135, 'EBCS', 'jamie.lopez@ravago.com.ph', 1, NULL, NULL),
(136, 'EBCS', 'yula.corpuz@ravago.com.ph', 1, NULL, NULL),
(137, 'EBCS', 'camille.serrano@ravago.com.ph', 1, NULL, NULL),
(138, 'EBCS', 'erlyn.delsocorro@ravago.com.ph', 1, NULL, NULL),
(139, 'EBCS', 'mira.balatucan@ravago.com.ph', 1, NULL, NULL),
(140, 'EBCS', 'joana.miranda@ravago.com.ph', 1, NULL, NULL),
(141, 'EBCS', 'christianne.manangan@ravago.com.ph', 1, NULL, NULL),
(142, 'EBCS', 'may.avergonzado@ravago.com.ph', 1, NULL, NULL),
(143, 'EBCX', 'valerie.sy@ravago.com.ph', 1, 'MGR', NULL),
(144, 'EBCO', 'bea.jacobe@ravago.com.ph', 1, NULL, NULL),
(145, 'EBCE', 'joanamarie.leona@ravago.com.ph', 1, 'STAFF', '2025-07-24 00:00:00'),
(146, 'EBCO', 'operations@ravago.com.ph', 1, NULL, '2025-08-08 00:00:00'),
(147, 'EBCA', 'jheyzyel.delgado@ravago.com.ph', 1, 'STAFF', '2025-09-01 00:00:00'),
(4144, 'EBCA', 'love.sarino@ravago.com.ph', 1, NULL, NULL),
(4145, 'PURJO', 'testhead', 1, 'HEAD', '2025-06-11 06:47:47'),
(4146, 'PURJO', 'chr', 1, 'STAFF', '2025-06-30 06:19:03'),
(4147, 'PURJO', 'chri', 1, 'STAFF', '2025-06-30 08:32:55'),
(4148, 'PURJO', 'sar', 1, 'EXECOM', '2025-06-30 08:35:42'),
(4149, 'PURJO', 'rog2', 1, 'EXECOM', '2025-06-30 08:36:03'),
(4150, 'PURJO', 'rus', 1, 'EXECOM', '2025-06-30 08:36:13'),
(4151, 'PURJO', 'deb', 1, 'EXECOM', '2025-06-30 08:36:24'),
(4152, 'PURJO', 'val', 1, 'EXECOM', '2025-06-30 08:36:33'),
(4153, 'PURJO', 'rog', 1, 'EXECOM', '2025-06-30 08:36:46'),
(4154, 'PURJO', 'edi', 1, 'EXECOM', '2025-06-30 08:36:54'),
(4155, 'PURJO', 'rae', 1, 'EXECOM', '2025-06-30 08:37:11'),
(4156, 'PURJO', 'reg1', 1, 'EXECOM', '2025-06-30 08:37:24'),
(4157, 'PURJO', 'ris', 1, 'EXECOM', '2025-06-30 08:37:32'),
(4158, 'PURJO', 'eun', 1, 'EXECOM', '2025-06-30 08:38:08'),
(4159, 'PURJO', 'rob', 1, 'EXECOM', '2025-06-30 08:38:21'),
(4160, 'PURJO', 'roy', 1, 'EXECOM', '2025-06-30 08:38:34'),
(4161, 'PURJO', 'ber', 1, 'HEAD', '2025-06-30 08:52:51'),
(4162, 'PURJO', 'yol', 1, 'HEAD', '2025-06-30 16:55:14'),
(4163, 'PURJO', 'glo', 1, 'HEAD', '2025-06-30 16:58:32'),
(4164, 'PURJO', 'mar3', 1, 'HEAD', '2025-06-30 17:00:05'),
(4165, 'PURJO', 'jes2', 1, 'HEAD', '2025-07-01 08:36:54'),
(4166, 'PURJO', 'sno', 1, 'HEAD', '2025-07-01 08:37:08'),
(4167, 'PURJO', 'jan3', 1, 'HEAD', '2025-07-01 08:37:20'),
(4168, 'PURJO', 'rac1', 1, 'HEAD', '2025-07-01 08:37:28'),
(4169, 'PURJO', 'jan1', 1, 'HEAD', '2025-07-01 08:37:40'),
(4170, 'PURJO', 'ale', 1, 'HEAD', '2025-07-01 08:37:56'),
(4171, 'PURJO', 'dan', 1, 'HEAD', '2025-07-01 08:38:15'),
(4172, 'PURJO', 'dai', 1, 'HEAD', '2025-07-01 08:38:33'),
(4173, 'PURJO', 'may2', 1, 'HEAD', '2025-07-01 08:38:44'),
(4174, 'PURJO', 'jes1', 1, 'HEAD', '2025-07-01 08:38:56'),
(4175, 'PURJO', 'ric2', 1, 'HEAD', '2025-07-01 08:39:06'),
(4176, 'PURJO', 'rue', 1, 'HEAD', '2025-07-01 08:39:23'),
(4177, 'PURJO', 'apr', 1, 'HEAD', '2025-07-01 08:39:29'),
(4178, 'PURJO', 'jai', 1, 'HEAD', '2025-07-01 08:39:42'),
(4179, 'PURJO', 'ara', 1, 'HEAD', '2025-07-01 08:39:52'),
(4180, 'PURJO', 'jea', 1, 'HEAD', '2025-07-01 08:40:05'),
(4181, 'PURJO', 'rol', 1, 'HEAD', '2025-07-01 08:48:34'),
(4182, 'PURJO', 'mer', 1, 'HEAD', '2025-07-01 08:48:58'),
(4183, 'PURJO', 'car', 1, 'HEAD', '2025-07-01 08:49:09'),
(4184, 'PURJO', 'nei1', 1, 'HEAD', '2025-07-01 08:49:29'),
(4185, 'PURJO', 'joh2', 1, 'HEAD', '2025-07-01 08:49:37'),
(4186, 'PURJO', 'mar5', 1, 'HEAD', '2025-07-01 08:49:49'),
(4187, 'PURJO', 'ker', 1, 'HEAD', '2025-07-01 08:49:59'),
(4188, 'PURJO', 'van', 1, 'HEAD', '2025-07-01 08:50:18'),
(4189, 'PURJO', 'erl', 1, 'HEAD', '2025-07-01 08:50:32'),
(4190, 'PURJO', 'mic2', 1, 'HEAD', '2025-07-01 08:51:09'),
(4191, 'PURJO', 'ian', 1, 'HEAD', '2025-07-01 08:51:18'),
(4192, 'PURJO', 'mei', 1, 'HEAD', '2025-07-01 08:51:27'),
(4193, 'PURJO', 'mel', 1, 'HEAD', '2025-07-01 08:51:38'),
(4194, 'PURJO', 'jac', 1, 'HEAD', '2025-07-01 08:51:47'),
(4195, 'PURJO', 'yul', 1, 'HEAD', '2025-07-01 08:52:00'),
(4196, 'PURJO', 'and', 1, 'HEAD', '2025-07-01 08:52:11'),
(4197, 'PURJO', 'abe', 1, 'STAFF', '2025-07-01 08:56:29'),
(4198, 'PURJO', 'acc', 1, 'STAFF', '2025-07-01 08:57:36'),
(4199, 'PURJO', 'acc1', 1, 'STAFF', '2025-07-01 08:59:04'),
(4200, 'PURJO', 'acc2', 1, 'STAFF', '2025-07-01 08:59:13'),
(4201, 'PURJO', 'acc3', 1, 'STAFF', '2025-07-01 08:59:26'),
(4202, 'PURJO', 'aim', 1, 'STAFF', '2025-07-01 08:59:47'),
(4203, 'PURJO', 'ala', 1, 'STAFF', '2025-07-01 08:59:56'),
(4204, 'PURJO', 'ald', 1, 'STAFF', '2025-07-01 09:00:13'),
(4205, 'PURJO', 'ale1', 1, 'STAFF', '2025-07-01 09:00:27'),
(4206, 'PURJO', 'ale2', 1, 'STAFF', '2025-07-01 09:00:35'),
(4207, 'PURJO', 'alm', 1, 'STAFF', '2025-07-01 09:02:10'),
(4208, 'PURJO', 'aly', 1, 'STAFF', '2025-07-01 09:02:18'),
(4209, 'PURJO', 'ana', 1, 'STAFF', '2025-07-01 09:03:16'),
(4210, 'PURJO', 'ang', 1, 'STAFF', '2025-07-01 09:03:39'),
(4211, 'PURJO', 'ann', 1, 'STAFF', '2025-07-01 09:03:48'),
(4212, 'PURJO', 'arn', 1, 'STAFF', '2025-07-01 09:04:08'),
(4213, 'PURJO', 'bea', 1, 'STAFF', '2025-07-01 09:04:19'),
(4214, 'PURJO', 'bet', 1, 'STAFF', '2025-07-01 09:04:32'),
(4215, 'PURJO', 'bil', 1, 'STAFF', '2025-07-01 09:04:49'),
(4216, 'PURJO', 'bis', 1, 'STAFF', '2025-07-01 09:05:03'),
(4217, 'PURJO', 'cam', 1, 'STAFF', '2025-07-01 09:05:11'),
(4218, 'PURJO', 'car1', 1, 'STAFF', '2025-07-01 09:05:31'),
(4219, 'PURJO', 'cat', 1, 'STAFF', '2025-07-01 09:05:52'),
(4220, 'PURJO', 'chr1', 1, 'STAFF', '2025-07-01 09:06:24'),
(4221, 'PURJO', 'cre', 1, 'STAFF', '2025-07-01 09:06:43'),
(4222, 'PURJO', 'cyr', 1, 'STAFF', '2025-07-01 09:06:50'),
(4223, 'PURJO', 'dan1', 1, 'STAFF', '2025-07-01 09:07:07'),
(4224, 'PURJO', 'dan2', 1, 'STAFF', '2025-07-01 09:07:15'),
(4225, 'PURJO', 'dav', 1, 'STAFF', '2025-07-01 09:07:36'),
(4226, 'PURJO', 'dav1', 1, 'STAFF', '2025-07-01 09:07:46'),
(4227, 'PURJO', 'dav2', 1, 'STAFF', '2025-07-01 09:07:55'),
(4228, 'PURJO', 'dha', 1, 'STAFF', '2025-07-01 09:08:12'),
(4229, 'PURJO', 'ruby', 1, 'PURAD', '2025-07-01 09:10:34'),
(4230, 'PURJO', 'dio', 1, 'STAFF', '2025-07-01 09:10:51'),
(4231, 'PURJO', 'edd', 1, 'STAFF', '2025-07-01 09:11:01'),
(4232, 'PURJO', 'edg', 1, 'STAFF', '2025-07-01 09:11:10'),
(4233, 'PURJO', 'ele', 1, 'STAFF', '2025-07-01 09:11:25'),
(4234, 'PURJO', 'ell', 1, 'STAFF', '2025-07-01 09:11:31'),
(4235, 'PURJO', 'emi', 1, 'STAFF', '2025-07-01 09:11:38'),
(4236, 'PURJO', 'emm', 1, 'STAFF', '2025-07-01 09:11:43'),
(4237, 'PURJO', 'eri', 1, 'STAFF', '2025-07-01 09:11:55'),
(4238, 'PURJO', 'eri1', 1, 'STAFF', '2025-07-01 09:12:09'),
(4239, 'PURJO', 'erl1', 1, 'STAFF', '2025-07-01 09:12:20'),
(4240, 'PURJO', 'erw', 1, 'STAFF', '2025-07-01 09:12:45'),
(4241, 'PURJO', 'fay', 1, 'STAFF', '2025-07-01 09:12:57'),
(4242, 'PURJO', 'bel', 1, 'STAFF', '2025-07-01 09:13:06'),
(4243, 'PURJO', 'fre', 1, 'STAFF', '2025-07-01 09:13:21'),
(4244, 'PURJO', 'ger', 1, 'STAFF', '2025-07-01 09:13:41'),
(4245, 'PURJO', 'gin', 1, 'STAFF', '2025-07-01 09:13:52'),
(4246, 'PURJO', 'gle', 1, 'STAFF', '2025-07-01 09:14:07'),
(4247, 'PURJO', 'god', 1, 'STAFF', '2025-07-01 09:14:17'),
(4248, 'PURJO', 'gut', 1, 'STAFF', '2025-07-01 09:14:25'),
(4249, 'PURJO', 'hra', 1, 'STAFF', '2025-07-01 09:14:38'),
(4250, 'PURJO', 'hrd', 1, 'STAFF', '2025-07-01 09:14:48'),
(4251, 'PURJO', 'hr-', 1, 'STAFF', '2025-07-01 09:14:58'),
(4252, 'PURJO', 'hrc', 1, 'STAFF', '2025-07-01 09:15:07'),
(4253, 'PURJO', 'hra1', 1, 'STAFF', '2025-07-01 09:15:19'),
(4254, 'PURJO', 'hrr', 1, 'STAFF', '2025-07-01 09:15:28'),
(4255, 'PURJO', 'hrr1', 1, 'STAFF', '2025-07-01 09:15:35'),
(4256, 'PURJO', 'inv', 1, 'STAFF', '2025-07-01 09:15:49'),
(4257, 'PURJO', 'irw', 1, 'STAFF', '2025-07-01 09:15:56'),
(4258, 'PURJO', 'ita', 1, 'STAFF', '2025-07-01 09:16:05'),
(4259, 'PURJO', 'its', 1, 'STAFF', '2025-07-01 09:16:12'),
(4260, 'PURJO', 'its1', 1, 'STAFF', '2025-07-01 09:16:23'),
(4261, 'PURJO', 'jak', 1, 'STAFF', '2025-07-01 09:16:48'),
(4262, 'PURJO', 'jam', 1, 'STAFF', '2025-07-01 09:16:57'),
(4263, 'PURJO', 'jan', 1, 'STAFF', '2025-07-01 09:17:03'),
(4264, 'PURJO', 'jan2', 1, 'STAFF', '2025-07-01 09:17:30'),
(4265, 'PURJO', 'jan4', 1, 'STAFF', '2025-07-01 09:17:44'),
(4266, 'PURJO', 'jan5', 1, 'STAFF', '2025-07-01 09:17:52'),
(4267, 'PURJO', 'jay', 1, 'STAFF', '2025-07-01 09:18:10'),
(4268, 'PURJO', 'jaz', 1, 'STAFF', '2025-07-01 09:18:20'),
(4269, 'PURJO', 'jea1', 1, 'STAFF', '2025-07-01 09:18:38'),
(4270, 'PURJO', 'jef', 1, 'STAFF', '2025-07-01 09:18:53'),
(4271, 'PURJO', 'jef1', 1, 'STAFF', '2025-07-01 09:19:00'),
(4272, 'PURJO', 'jen', 1, 'STAFF', '2025-07-01 09:19:07'),
(4273, 'PURJO', 'jen1', 1, 'STAFF', '2025-07-01 09:19:17'),
(4274, 'PURJO', 'jen2', 1, 'STAFF', '2025-07-01 09:19:24'),
(4275, 'PURJO', 'jer', 1, 'STAFF', '2025-07-01 09:19:32'),
(4276, 'PURJO', 'jer1', 1, 'STAFF', '2025-07-01 09:19:42'),
(4277, 'PURJO', 'jes', 1, 'STAFF', '2025-07-01 09:20:01'),
(4278, 'PURJO', 'jes3', 1, 'STAFF', '2025-07-01 09:20:37'),
(4279, 'PURJO', 'joa', 1, 'STAFF', '2025-07-01 09:20:47'),
(4280, 'PURJO', 'joa1', 1, 'STAFF', '2025-07-01 09:20:53'),
(4281, 'PURJO', 'joe', 1, 'STAFF', '2025-07-01 09:21:00'),
(4282, 'PURJO', 'joh', 1, 'STAFF', '2025-07-01 09:21:12'),
(4283, 'PURJO', 'joh1', 1, 'STAFF', '2025-07-01 09:21:29'),
(4284, 'PURJO', 'jol', 1, 'STAFF', '2025-07-01 09:21:43'),
(4285, 'PURJO', 'jon', 1, 'STAFF', '2025-07-01 09:21:55'),
(4286, 'PURJO', 'joy', 1, 'STAFF', '2025-07-01 09:22:04'),
(4287, 'PURJO', 'joy1', 1, 'STAFF', '2025-07-01 09:22:33'),
(4288, 'PURJO', 'jun', 1, 'STAFF', '2025-07-01 09:22:53'),
(4289, 'PURJO', 'jus', 1, 'STAFF', '2025-07-01 09:23:02'),
(4290, 'PURJO', 'kar', 1, 'STAFF', '2025-07-01 09:23:18'),
(4291, 'PURJO', 'kat', 1, 'STAFF', '2025-07-01 09:23:25'),
(4292, 'PURJO', 'kat1', 1, 'STAFF', '2025-07-01 09:23:35'),
(4293, 'PURJO', 'kei', 1, 'STAFF', '2025-07-01 09:23:51'),
(4294, 'PURJO', 'kem', 1, 'STAFF', '2025-07-01 09:24:02'),
(4295, 'PURJO', 'kenn', 1, 'STAFF', '2025-07-01 09:26:26'),
(4296, 'PURJO', 'kie', 1, 'STAFF', '2025-07-01 09:28:12'),
(4297, 'PURJO', 'kim', 1, 'STAFF', '2025-07-01 09:28:19'),
(4298, 'PURJO', 'kri', 1, 'STAFF', '2025-07-01 09:28:37'),
(4299, 'PURJO', 'kri1', 1, 'STAFF', '2025-07-01 09:29:00'),
(4300, 'PURJO', 'lea', 1, 'STAFF', '2025-07-01 09:29:19'),
(4301, 'PURJO', 'len', 1, 'STAFF', '2025-07-01 09:29:26'),
(4302, 'PURJO', 'leo', 1, 'STAFF', '2025-07-01 09:29:32'),
(4303, 'PURJO', 'liq', 1, 'STAFF', '2025-07-01 09:29:43'),
(4304, 'PURJO', 'joyceann.parra@liberty', 1, 'STAFF', '2025-07-01 09:29:58'),
(4305, 'PURJO', 'lol', 1, 'STAFF', '2025-07-01 09:30:12'),
(4306, 'PURJO', 'lov', 1, 'STAFF', '2025-07-01 09:30:19'),
(4307, 'PURJO', 'lyn', 1, 'STAFF', '2025-07-01 09:30:40'),
(4308, 'PURJO', 'lys', 1, 'STAFF', '2025-07-01 09:30:49'),
(4309, 'PURJO', 'mac', 1, 'STAFF', '2025-07-01 09:31:18'),
(4310, 'PURJO', 'mai', 1, 'STAFF', '2025-07-01 09:31:24'),
(4311, 'PURJO', 'mar', 1, 'STAFF', '2025-07-01 09:31:41'),
(4312, 'PURJO', 'mar1', 1, 'STAFF', '2025-07-01 09:31:49'),
(4313, 'PURJO', 'mar2', 1, 'STAFF', '2025-07-01 09:31:56'),
(4314, 'PURJO', 'mar4', 1, 'STAFF', '2025-07-01 09:32:15'),
(4315, 'PURJO', 'mar6', 1, 'STAFF', '2025-07-01 09:32:24'),
(4316, 'PURJO', 'mar7', 1, 'STAFF', '2025-07-01 09:32:31'),
(4317, 'PURJO', 'mar8', 1, 'STAFF', '2025-07-01 09:32:43'),
(4318, 'PURJO', 'mar9', 1, 'STAFF', '2025-07-01 09:32:53'),
(4319, 'PURJO', 'mar10', 1, 'STAFF', '2025-07-01 09:33:14'),
(4320, 'PURJO', 'mar11', 1, 'STAFF', '2025-07-01 09:33:21'),
(4321, 'PURJO', 'may', 1, 'STAFF', '2025-07-01 09:33:32'),
(4322, 'PURJO', 'may1', 1, 'STAFF', '2025-07-01 09:33:43'),
(4323, 'PURJO', 'mey', 1, 'STAFF', '2025-07-01 09:34:22'),
(4324, 'PURJO', 'mic', 1, 'STAFF', '2025-07-01 09:34:32'),
(4325, 'PURJO', 'mic1', 1, 'STAFF', '2025-07-01 09:34:45'),
(4326, 'PURJO', 'mig', 1, 'STAFF', '2025-07-01 09:34:55'),
(4327, 'PURJO', 'mik', 1, 'STAFF', '2025-07-01 09:35:03'),
(4328, 'PURJO', 'mir', 1, 'STAFF', '2025-07-01 09:35:10'),
(4329, 'PURJO', 'mor', 1, 'STAFF', '2025-07-01 09:35:14'),
(4330, 'PURJO', 'nas', 1, 'STAFF', '2025-07-01 09:35:20'),
(4331, 'PURJO', 'nei', 1, 'STAFF', '2025-07-01 09:35:35'),
(4332, 'PURJO', 'nic', 1, 'STAFF', '2025-07-01 09:36:16'),
(4333, 'PURJO', 'nol', 1, 'STAFF', '2025-07-01 09:36:30'),
(4334, 'PURJO', 'nor', 1, 'STAFF', '2025-07-01 09:36:34'),
(4335, 'PURJO', 'ope', 1, 'STAFF', '2025-07-01 09:36:51'),
(4336, 'PURJO', 'ope1', 1, 'STAFF', '2025-07-01 09:37:05'),
(4337, 'PURJO', 'ope2', 1, 'STAFF', '2025-07-01 09:37:13'),
(4338, 'PURJO', 'pay', 1, 'STAFF', '2025-07-01 09:37:42'),
(4339, 'PURJO', 'pay1', 1, 'STAFF', '2025-07-01 09:37:58'),
(4340, 'PURJO', 'pay2', 1, 'STAFF', '2025-07-01 09:38:12'),
(4341, 'PURJO', 'pay3', 1, 'STAFF', '2025-07-01 09:38:25'),
(4342, 'PURJO', 'pay4', 1, 'STAFF', '2025-07-01 09:38:35'),
(4343, 'PURJO', 'pay5', 1, 'STAFF', '2025-07-01 09:38:45'),
(4344, 'PURJO', 'pay6', 1, 'STAFF', '2025-07-01 09:38:57'),
(4345, 'PURJO', 'pay7', 1, 'STAFF', '2025-07-01 09:39:04'),
(4346, 'PURJO', 'pin', 1, 'STAFF', '2025-07-01 09:39:12'),
(4347, 'PURJO', 'poc', 1, 'STAFF', '2025-07-01 09:39:20'),
(4348, 'PURJO', 'pre', 1, 'STAFF', '2025-07-01 09:39:33'),
(4349, 'PURJO', 'pre1', 1, 'STAFF', '2025-07-01 09:39:46'),
(4350, 'PURJO', 'pre2', 1, 'STAFF', '2025-07-01 09:39:53'),
(4351, 'PURJO', 'pri', 1, 'STAFF', '2025-07-01 09:40:05'),
(4352, 'PURJO', 'pri1', 1, 'STAFF', '2025-07-01 09:40:18'),
(4353, 'PURJO', 'pri2', 1, 'STAFF', '2025-07-01 09:40:25'),
(4354, 'PURJO', 'pur', 1, 'STAFF', '2025-07-01 09:40:56'),
(4355, 'PURJO', 'qeh', 1, 'STAFF', '2025-07-01 09:41:05'),
(4356, 'PURJO', 'qua', 1, 'STAFF', '2025-07-01 09:41:12'),
(4357, 'PURJO', 'rac', 1, 'STAFF', '2025-07-01 09:41:18'),
(4358, 'PURJO', 'ran', 1, 'STAFF', '2025-07-01 09:41:53'),
(4359, 'PURJO', 'raz', 1, 'STAFF', '2025-07-01 09:42:00'),
(4360, 'PURJO', 'reg', 1, 'STAFF', '2025-07-01 09:42:18'),
(4361, 'PURJO', 'rep', 1, 'STAFF', '2025-07-01 09:42:30'),
(4362, 'PURJO', 'rex', 1, 'STAFF', '2025-07-01 09:42:38'),
(4363, 'PURJO', 'rey', 1, 'STAFF', '2025-07-01 09:42:43'),
(4364, 'PURJO', 'ric', 1, 'STAFF', '2025-07-01 09:42:51'),
(4365, 'PURJO', 'ric1', 1, 'STAFF', '2025-07-01 09:43:44'),
(4366, 'PURJO', 'ric3', 1, 'STAFF', '2025-07-01 09:44:03'),
(4367, 'PURJO', 'ric4', 1, 'STAFF', '2025-07-01 09:44:11'),
(4368, 'PURJO', 'rog1', 1, 'STAFF', '2025-07-01 09:44:36'),
(4369, 'PURJO', 'ron', 1, 'STAFF', '2025-07-01 09:45:09'),
(4370, 'PURJO', 'roq', 1, 'STAFF', '2025-07-01 09:45:23'),
(4371, 'PURJO', 'ros', 1, 'STAFF', '2025-07-01 09:45:31'),
(4372, 'PURJO', 'ros1', 1, 'STAFF', '2025-07-01 09:45:40'),
(4373, 'PURJO', 'rox', 1, 'STAFF', '2025-07-01 09:45:52'),
(4374, 'PURJO', 'ruf', 1, 'STAFF', '2025-07-01 09:46:25'),
(4375, 'PURJO', 'rya', 1, 'STAFF', '2025-07-01 09:46:39'),
(4376, 'PURJO', 'rya1', 1, 'STAFF', '2025-07-01 09:46:44'),
(4377, 'PURJO', 'saf', 1, 'STAFF', '2025-07-01 09:47:13'),
(4378, 'PURJO', 'sal', 1, 'STAFF', '2025-07-01 09:47:24'),
(4379, 'PURJO', 'sal1', 1, 'STAFF', '2025-07-01 09:47:33'),
(4380, 'PURJO', 'sal2', 1, 'STAFF', '2025-07-01 09:47:45'),
(4381, 'PURJO', 'sal3', 1, 'STAFF', '2025-07-01 09:47:56'),
(4382, 'PURJO', 'sal4', 1, 'STAFF', '2025-07-01 09:48:07'),
(4383, 'PURJO', 'sal5', 1, 'STAFF', '2025-07-01 09:48:23'),
(4384, 'PURJO', 'san', 1, 'STAFF', '2025-07-01 09:48:43'),
(4385, 'PURJO', 'sea', 1, 'STAFF', '2025-07-01 09:48:54'),
(4386, 'PURJO', 'sha', 1, 'STAFF', '2025-07-01 09:49:02'),
(4387, 'PURJO', 'she', 1, 'STAFF', '2025-07-01 09:49:11'),
(4388, 'PURJO', 'she1', 1, 'STAFF', '2025-07-01 09:49:42'),
(4389, 'PURJO', 'shi', 1, 'STAFF', '2025-07-01 09:49:49'),
(4390, 'PURJO', 'shi1', 1, 'STAFF', '2025-07-01 09:49:57'),
(4391, 'PURJO', 'ste', 1, 'STAFF', '2025-07-01 09:50:09'),
(4392, 'PURJO', 'the', 1, 'STAFF', '2025-07-01 09:50:21'),
(4393, 'PURJO', 'tim', 1, 'STAFF', '2025-07-01 09:50:44'),
(4394, 'PURJO', 'tim1', 1, 'STAFF', '2025-07-01 09:50:53'),
(4395, 'PURJO', 'tim2', 1, 'STAFF', '2025-07-01 09:51:04'),
(4396, 'PURJO', 'tim3', 1, 'STAFF', '2025-07-01 09:51:25'),
(4397, 'PURJO', 'tri', 1, 'STAFF', '2025-07-01 09:51:37'),
(4398, 'PURJO', 'tri1', 1, 'STAFF', '2025-07-01 09:51:45'),
(4399, 'PURJO', 'uti', 1, 'STAFF', '2025-07-01 09:51:52'),
(4400, 'PURJO', 'uti1', 1, 'STAFF', '2025-07-01 09:51:57'),
(4401, 'PURJO', 'vin', 1, 'STAFF', '2025-07-01 09:53:01'),
(4402, 'PURJO', 'wil', 1, 'STAFF', '2025-07-01 09:53:08'),
(4403, 'PURJO', 'wil1', 1, 'STAFF', '2025-07-01 09:53:16'),
(4404, 'PURJO', 'ily', 1, 'STAFF', '2025-07-01 09:53:49'),
(4405, 'PURJO', 'ang1', 1, 'STAFF', '2025-07-01 09:53:59'),
(4406, 'PURJO', 'kri2', 1, 'STAFF', '2025-07-01 09:54:07'),
(4407, 'PURJO', 'wen', 1, 'STAFF', '2025-07-01 09:54:14'),
(4408, 'PURJO', 'jof', 1, 'STAFF', '2025-07-01 09:54:19'),
(4409, 'PURJO', 'tac', 1, 'STAFF', '2025-07-01 09:54:44'),
(4410, 'PURJO', 'pay8', 1, 'STAFF', '2025-07-01 09:54:54'),
(4411, 'PURJO', 'jul', 1, 'STAFF', '2025-07-01 09:55:02'),
(4412, 'PURJO', 'van1', 1, 'STAFF', '2025-07-01 09:55:25'),
(4413, 'PURJO', 'cri', 1, 'STAFF', '2025-07-01 09:55:33'),
(4414, 'EBCO', 'etr.requestor', 1, NULL, '2025-08-26 00:00:00'),
(4415, 'EBCAD', 'alldash', 1, NULL, '2025-09-22 00:00:00'),
(4416, 'PURJO', 'annalou', 1, 'STAFF', '2025-09-24 15:31:15'),
(4417, 'SENGR', 'kirklexsus.bancolo@smartrigs.com.ph', 1, 'STAFF', '2025-11-26 08:39:00'),
(4418, 'SENGR', 'jecebel.geroche@smartrigs.com.ph', 1, 'STAFF', '2025-11-26 08:39:00'),
(4419, 'SENGR', 'jefe.agapinan@ravago.com.ph', 0, 'STAFF', '2025-11-26 16:54:00'),
(4420, 'SENGR', 'jecebel.geroche@ravago.com.ph', 1, 'STAFF', '2026-01-07 00:00:00'),
(4421, 'EBCA', 'nina.lagera@ravago.com.ph', 1, NULL, '2026-01-12 00:00:00'),
(4422, 'SENGR', 'jonnel.gonzales@ravago.com.ph', 1, 'STAFF', '2026-02-05 00:00:00'),
(4423, 'EBCA', 'geramie.fedilino@ravago.com.ph', 1, 'STAFF', '2026-02-19 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `prvgs`
--

CREATE TABLE `prvgs` (
  `priviledgeCode` varchar(6) NOT NULL,
  `description` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `prvgs`
--

INSERT INTO `prvgs` (`priviledgeCode`, `description`) VALUES
('ADMIN', 'admin'),
('BASIC', 'basic'),
('DCON', 'document controller'),
('DCOR', 'dispatch coordinator'),
('EXECOM', 'EXECOMM'),
('FORMER', 'Former Employee'),
('GUEST', 'guest'),
('HEAD', 'Head'),
('MGR', 'manager'),
('PURAD', 'Purchasing Admin'),
('STAFF', 'Staff');

-- --------------------------------------------------------

--
-- Stand-in structure for view `purjo_users_view`
-- (See below for the actual view)
--
CREATE TABLE `purjo_users_view` (
`userId` varchar(8)
,`roleId` varchar(3)
,`email` varchar(45)
,`name` varchar(45)
,`userName` varchar(45)
,`userPassword` text
,`usr_isActive` tinyint(4)
,`usr_dtCreated` datetime
,`reset_token_hash` varchar(64)
,`reset_token_expires_at` datetime
,`imgPath` varchar(255)
,`orisoft_user_id` int(11)
,`appusrId` int(11)
,`appId` varchar(5)
,`gUserName` varchar(64)
,`appusr_isActive` tinyint(4)
,`priviledgeCode` varchar(6)
,`appusr_dtCreated` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `roleId` varchar(3) NOT NULL,
  `description` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`roleId`, `description`) VALUES
('ADM', 'admin'),
('GST', 'guest');

-- --------------------------------------------------------

--
-- Table structure for table `salesrep`
--

CREATE TABLE `salesrep` (
  `repId` int(11) NOT NULL,
  `email` varchar(65) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `name` varchar(25) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `dtLastUpdate` datetime DEFAULT NULL,
  `dtEncoder` varchar(65) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `settingsId` int(11) NOT NULL,
  `smtpHost` varchar(45) DEFAULT NULL,
  `smtpAuth` tinyint(4) DEFAULT NULL,
  `smtpUsername` varchar(45) DEFAULT NULL,
  `smtpPassword` varchar(45) DEFAULT NULL,
  `smtpPort` int(11) DEFAULT NULL,
  `setFromAddress` varchar(45) DEFAULT NULL,
  `setFromName` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`settingsId`, `smtpHost`, `smtpAuth`, `smtpUsername`, `smtpPassword`, `smtpPort`, `setFromAddress`, `setFromName`) VALUES
(0, 'smtp.gmail.com', 1, 'angelo.gaspar@ravago.com.ph', 'Angel009273775465', 465, 'from@example.com', 'SEO');

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `idtest` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usr`
--

CREATE TABLE `usr` (
  `userId` varchar(8) NOT NULL,
  `roleId` varchar(3) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `userName` varchar(45) DEFAULT NULL,
  `userPassword` text DEFAULT NULL,
  `isActive` tinyint(4) DEFAULT NULL,
  `dtCreated` datetime DEFAULT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `imgPath` varchar(255) DEFAULT NULL,
  `orisoft_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `usr`
--

INSERT INTO `usr` (`userId`, `roleId`, `email`, `name`, `userName`, `userPassword`, `isActive`, `dtCreated`, `reset_token_hash`, `reset_token_expires_at`, `imgPath`, `orisoft_user_id`) VALUES
('1', 'ADM', 'angelo.gaspar@ravago.com.ph', 'Angelo Gaspar', 'gelo', '$2a$12$NgA1E0.zKeX/7uVZkUks.OEwRtPKvr0wMdqXqNi46TLRoFhGMAxQ6', 1, '2023-06-30 00:00:00', NULL, NULL, NULL, NULL),
('ALL-1', NULL, 'gaspar21angelo@gmail.com', 'test.sales', 'test.sales', '$2y$10$GzKqaW7znAWjw.zhSIgBHOqtHxaOxj8dT8u.lYOdUIUH3lkJbhaUa', 1, '2023-11-15 11:34:59', NULL, NULL, NULL, NULL),
('ALL-10', NULL, 'logistics.assistant.lspi@ravago.com.ph', 'Joyce Ann Parra', 'joyceann.parra@liberty', NULL, 1, '2024-04-03 09:58:20', '66de97b4c16242551c7f606a7eaf4b9bf76a32433483c84969e4943856400aa6', '2024-04-04 02:56:26', NULL, NULL),
('ALL-100', NULL, 'jane.mendoza@asianshipping.com.ph', 'Jane Mendoza', 'jan1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-101', NULL, 'janerose.davela@asianshipping.com.ph', 'Janerose Davela', 'jan2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-102', NULL, 'janet.sanchez@ravago.com.ph', 'Janet Sanchez', 'jan3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-103', NULL, 'janica.murillo@ravago.com.ph', 'Janica Murillo', 'jan4', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-104', NULL, 'janice.santos@ravago.com.ph', 'Janice Santos', 'jan5', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-105', NULL, 'jayson.panahon@ravago.com.ph', 'Jayson Panahon', 'jay', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-106', NULL, 'jazzmine.clemente@ravago.com.ph', 'Jazzmine Clemente', 'jaz', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-107', NULL, 'jean.omayao@asianshipping.com.ph', 'Jean Omayao', 'jea', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-108', NULL, 'jeancyn.bendalian@ravago.com.ph', 'Jeancyn Bendalian', 'jea1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-109', NULL, 'jefferson.geronimo@ravago.com.ph', 'Jefferson Geronimo', 'jef', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-11', NULL, NULL, 'test.etr', 'test.etr', '$2y$10$GzKqaW7znAWjw.zhSIgBHOqtHxaOxj8dT8u.lYOdUIUH3lkJbhaUa', 1, '2024-04-03 09:57:40', NULL, NULL, NULL, NULL),
('ALL-110', NULL, 'jeffrey.delmundo@ravago.com.ph', 'Jeffrey Delmundo', 'jef1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-111', NULL, 'jenalyn.pelayo@asianslipway.com', 'Jenalyn Pelayo', 'jen', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-112', NULL, 'jeneah.subalisid@ravago.com.ph', 'Jeneah Subalisid', 'jen1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-113', NULL, 'jennelyn.torres@ravago.com.ph', 'Jennelyn Torres', 'jen2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-114', NULL, 'jerdy.ismael@asianshipping.com.ph', 'Jerdy Ismael', 'jer', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-115', NULL, 'jerico.valena@ravago.com.ph', 'Jerico Valena', 'jer1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-116', NULL, 'jessa.lagman@ravago.com.ph', 'Jessa Lagman', 'jes', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-117', NULL, 'jessica.din@ravago.com.ph', 'Jessica Din', 'jes1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-118', NULL, 'jester.lozano@ravago.com.ph', 'Jester Lozano', 'jes2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-119', NULL, 'jestoney.casas@asianslipway.com', 'Jestoney Casas', 'jes3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-12', NULL, NULL, 'test.ops', 'test.opss', '$2y$10$qvuY71TIUz65QXgm7gDE.O6dn8CtgGE85wyxwvw1cpRBxJMgt9DAm', 1, '2024-04-03 09:57:40', NULL, NULL, NULL, NULL),
('ALL-120', NULL, 'joana.dres@ravago.com.ph', 'Joana Dres', 'joa', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-121', NULL, 'joana.miranda@ravago.com.ph', 'Joana Miranda', 'joa1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-122', NULL, 'joel.napoles@asianshipping.com.ph', 'Joel Napoles', 'joe', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-123', NULL, 'john.martinez@asianslipway.com', 'John Martinez', 'joh', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-124', NULL, 'john.somera@asianshipping.com.ph', 'John Somera', 'joh1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-125', NULL, 'johnny.rodriguez@asianshipping.com.ph', 'Johnny Rodriguez', 'joh2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-126', NULL, 'jolivic.villaluz@asianshipping.com.ph', 'Jolivic Villaluz', 'jol', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-127', NULL, 'jonna.prudenciado@asianshipping.com.ph', 'Jonna Prudenciado', 'jon', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-128', NULL, 'joy.madrid@asianslipway.com', 'Joy Madrid', 'joy', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-129', NULL, 'joyce.jimenez@ravago.com.ph', 'Joyce Jimenez', 'joy1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-13', NULL, 'operations@ravago.com.ph', 'Gleicel Jabajab', 'gleicel.jabajab@liberty', '$2y$10$FW0gX.VMmMlndONboZ0jtuAar7/Zt4N3As3l9ab8Inj29vWDNywmG', 1, '2024-05-17 10:12:37', NULL, NULL, NULL, NULL),
('ALL-130', NULL, 'june.sahagun@ravago.com.ph', 'June Sahagun', 'jun', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-131', NULL, 'justin.gambito@asianslipway.com', 'Justin Gambito', 'jus', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-132', NULL, 'karen.taytayon@asianslipway.com', 'Karen Taytayon', 'kar', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-133', NULL, 'kathlene.lopez@asianshipping.com.ph', 'Kathlene Lopez', 'kat', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-134', NULL, 'katrin.umagtam@ravago.com.ph', 'Katrin Umagtam', 'kat1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-135', NULL, 'keisha.acol@ravago.com.ph', 'Keisha Acol', 'kei', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-136', NULL, 'kemberly.orbiso@ravago.com.ph', 'Kemberly Orbiso', 'kem', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-137', NULL, 'kenneth.basay@ravago.com.ph', 'Kenneth Basay', 'ken', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-138', NULL, 'kerstine.ambito@ravago.com.ph', 'Kerstine Ambito', 'ker', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-139', NULL, 'kiervin.labor@ravago.com.ph', 'Kiervin Labor', 'kie', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-14', NULL, NULL, 'test.ar', 'test.ar', '$2y$10$GzKqaW7znAWjw.zhSIgBHOqtHxaOxj8dT8u.lYOdUIUH3lkJbhaUa', 1, '2024-06-03 00:00:00', NULL, NULL, NULL, NULL),
('ALL-140', NULL, 'kimberly.smith@smartrigs.com.ph', 'Kimberly Smith', 'kim', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-141', NULL, 'kristine.ramos@ravago.com.ph', 'Kristine Ramos', 'kri', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-142', NULL, 'krizza.mahilum@asianshipping.com.ph', 'Krizza Mahilum', 'kri1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-143', NULL, 'lea.caballero@asianshipping.com.ph', 'Lea Caballero', 'lea', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-144', NULL, 'leny.baylon@ravago.com.ph', 'Leny Baylon', 'len', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-145', NULL, 'leonamy@asianshipping.com.ph', 'Leonamy', 'leo', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-146', NULL, 'liquidation.staff.lspi@smartrigs.com.ph', 'Liquidation Staff Lspi', 'liq', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-147', NULL, 'lolit.ventura@asianshipping.com.ph', 'Lolit Ventura', 'lol', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-148', NULL, 'love.sarino@ravago.com.ph', 'Love Sarino', 'lov', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-149', NULL, 'lyndon.estrada@ravago.com.ph', 'Lyndon Estrada', 'lyn', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-15', 'ADM', NULL, 'text.exec', 'test.exec', '$2y$10$qvuY71TIUz65QXgm7gDE.O6dn8CtgGE85wyxwvw1cpRBxJMgt9DAm', 1, '2024-04-03 09:57:40', NULL, NULL, NULL, NULL),
('ALL-150', NULL, 'lysa.biana@liftrite.com.ph', 'Lysa Biana', 'lys', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-151', NULL, 'machinery.assistant.ojt@ravago.com.ph', 'Machinery Assistant Ojt', 'mac', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-152', NULL, 'maintenance@ravago.com.ph', 'Maintenance', 'mai', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-153', NULL, 'marcelo.guillena@smartrigs.com.ph', 'Marcelo Guillena', 'mar', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-154', NULL, 'maricar.padlan@ravago.com.ph', 'Maricar Padlan', 'mar1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-155', NULL, 'maricar.regoroso@asianshipping.com.ph', 'Maricar Regoroso', 'mar2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-156', NULL, 'marilyn.garabiles@ravago.com.ph', 'Marilyn Garabiles', 'mar3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-157', NULL, 'marjorie.tuyogon@smartrigs.com.ph', 'Marjorie Tuyogon', 'mar4', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-158', NULL, 'mark.emata@asianslipway.com', 'Mark Emata', 'mar5', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-159', NULL, 'mark.salvador@asianshipping.com.ph', 'Mark Salvador', 'mar6', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-16', NULL, NULL, 'test.billing', 'test.billing', '$2y$10$qvuY71TIUz65QXgm7gDE.O6dn8CtgGE85wyxwvw1cpRBxJMgt9DAm', 1, '2025-06-03 00:00:00', NULL, NULL, NULL, NULL),
('ALL-160', NULL, 'marvie.paez@asianshipping.com.ph', 'Marvie Paez', 'mar7', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-161', NULL, 'marvin.villamayor@ravago.com.ph', 'Marvin Villamayor', 'mar8', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-162', NULL, 'mary.reyes@ravago.com.ph', 'Mary Reyes', 'mar9', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-163', NULL, 'marystephanie.sangaspar@asianslipway.com', 'Marystephanie Sangaspar', 'mar10', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-164', NULL, 'maryjane.gayona@ravago.com.ph', 'Maryjane Gayona', 'mar11', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-165', NULL, 'may.avergonzado@ravago.com.ph', 'May Avergonzado', 'may', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-166', NULL, 'maybelyn.caples@asianshipping.com.ph', 'Maybelyn Caples', 'may1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-167', NULL, 'mayko.miranda@ravago.com.ph', 'Mayko Miranda', 'may2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-168', NULL, 'mei.dionisio@asianshipping.com.ph', 'Mei Dionisio', 'mei', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-169', NULL, 'melchor.fronteras@ravago.com.ph', 'Melchor Fronteras', 'mel', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-17', NULL, 'christine.picardal@asianshipping.com.ph', 'Christine', 'chri', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-170', NULL, 'merly.alcantara@asianshipping.com.ph', 'Merly Alcantara', 'mer', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-171', NULL, 'meynard.sioson@ravago.com.ph', 'Meynard Sioson', 'mey', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-172', NULL, 'michael.escote@ravago.com.ph', 'Michael Escote', 'mic', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-173', NULL, 'michael.floresca@asianshipping.com.ph', 'Michael Floresca', 'mic1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-174', NULL, 'michelle.yap@ravago.com.ph', 'Michelle Yap', 'mic2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-175', NULL, 'miguel.villaflor@ravago.com.ph', 'Miguel Villaflor', 'mig', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-176', NULL, 'mikaella.miranda@ravago.com.ph', 'Mikaella Miranda', 'mik', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-177', NULL, 'mira.balatucan@ravago.com.ph', 'Mira Balatucan', 'mir', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-178', NULL, 'morris.carreon@ravago.com.ph', 'Morris Carreon', 'mor', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-179', NULL, 'nasrelhan.mecarte@asianslipway.com', 'Nasrelhan Mecarte', 'nas', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-18', NULL, 'abegel.payongayong@ravago.com.ph', 'Abegel Payongayong', 'abe', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-180', NULL, 'neil.quiling@smartrigs.com.ph', 'Neil Quiling', 'nei', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-181', NULL, 'neil.marquez@smartrigs.com.ph', 'Neil Marquez', 'nei1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-182', NULL, 'nicko.ornedo@ravago.com.ph', 'Nicko Ornedo', 'nic', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-183', NULL, 'nole.gonowon@ravago.com.ph', 'Nole Gonowon', 'nol', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-184', NULL, 'norvien.nicolas@ravago.com.ph', 'Norvien Nicolas', 'nor', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-185', NULL, 'operations.assistant.cdo.lspi@smartrigs.com.p', 'Operations Assistant Cdo Lspi', 'ope', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-186', NULL, 'operations.assistant.ilo.lspi@smartrigs.com.p', 'Operations Assistant Ilo Lspi', 'ope1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-187', NULL, 'operations.assistant.lspi@smartrigs.com.ph', 'Operations Assistant Lspi', 'ope2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-188', NULL, 'payroll@smartrigs.com.ph', 'Payroll', 'pay', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-189', NULL, 'payroll@allislandsbarge.com.ph', 'Payroll', 'pay1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-19', NULL, 'accounting@asianshipping.com.ph', 'Accounting', 'acc', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-190', NULL, 'payroll@liftrite.com.ph', 'Payroll', 'pay2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-191', NULL, 'payroll@asianshipping.com.ph', 'Payroll', 'pay3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-192', NULL, 'payroll@landingcraft.com.ph', 'Payroll', 'pay4', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-193', NULL, 'payroll@canlubanggateway.com.ph', 'Payroll', 'pay5', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-194', NULL, 'payroll@asianslipway.com', 'Payroll', 'pay6', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-195', NULL, 'payroll@ravago.com.ph', 'Payroll', 'pay7', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-196', NULL, 'pinky.tablizo@ravago.com.ph', 'Pinky Tablizo', 'pin', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-197', NULL, 'pocholo.fajardo@ravago.com.ph', 'Pocholo Fajardo', 'poc', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-198', NULL, 'preventive.assistant@ravago.com.ph', 'Preventive Assistant', 'pre', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-199', NULL, 'preventive.assistant.cdo.lspi@smartrigs.com.p', 'Preventive Assistant Cdo Lspi', 'pre1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-2', NULL, 'gaspar22angelo@gmail.com', 'test.engr', 'test.engr', '$2y$10$qvuY71TIUz65QXgm7gDE.O6dn8CtgGE85wyxwvw1cpRBxJMgt9DAm', 1, '2023-11-15 11:50:11', NULL, NULL, NULL, NULL),
('ALL-20', NULL, 'accounting.assistant.lspi@smartrigs.com.ph', 'Accounting Assistant Lspi', 'acc1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-200', NULL, 'preventive.assistant.lspi@smartrigs.com.ph', 'Preventive Assistant Lspi', 'pre2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-201', NULL, 'princes.navarro@ravago.com.ph', 'Princes Navarro', 'pri', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-202', NULL, 'princese.santos@asianshipping.com.ph', 'Princese Santos', 'pri1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-203', NULL, 'princess.bade@smartrigs.com.ph', 'Princess Bade', 'pri2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-204', NULL, 'purchaser@ravago.com.ph', 'Purchaser', 'pur', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-205', NULL, 'qehs.assistant.lspi@ravago.com.ph', 'Qehs Assistant Lspi', 'qeh', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-206', NULL, 'qualityehs@ravago.com.ph', 'Qualityehs', 'qua', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-207', NULL, 'rachel.toquia@ravago.com.ph', 'Rachel Toquia', 'rac', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-208', NULL, 'racquel.teope@ravago.com.ph', 'Racquel Teope', 'rac1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-209', NULL, 'raenell.sy@asianshipping.com.ph', 'Raenell Sy', 'rae', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-21', NULL, 'accounting@ravago.com.ph', 'Accounting', 'acc2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-210', NULL, 'ranul.amora@ravago.com.ph', 'Ranul Amora', 'ran', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-211', NULL, 'razel.cadiz@ravago.com.ph', 'Razel Cadiz', 'raz', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-212', NULL, 'regel.mollena@smartrigs.com.ph', 'Regel Mollena', 'reg', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-213', NULL, 'regine.sy@asianshipping.com.ph', 'Regine Sy', 'reg1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-214', NULL, 'repair.assistant.lspi@smartrigs.com.ph', 'Repair Assistant Lspi', 'rep', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-215', NULL, 'rexel.jaboyanon@asianslipway.com', 'Rexel Jaboyanon', 'rex', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-216', NULL, 'reysie.mangahas@asianshipping.com.ph', 'Reysie Mangahas', 'rey', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-217', NULL, 'richard.marasigan@asianshipping.com.ph', 'Richard Marasigan', 'ric', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-218', NULL, 'richelle.saludar@asianshipping.com.ph', 'Richelle Saludar', 'ric1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-219', NULL, 'rich.dionisio@ravago.com.ph', 'Rich Dionisio', 'ric2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-22', NULL, 'accounts.payable@asianshipping.com.ph', 'Accounts Payable', 'acc3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-220', NULL, 'ricky.moraca@liftrite.com.ph', 'Ricky Moraca', 'ric3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-221', NULL, 'ricky.ballad@ravago.com.ph', 'Ricky Ballad', 'ric4', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-222', NULL, 'rissa.sy@asianshipping.com.ph', 'Rissa Sy', 'ris', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-223', NULL, 'robinson.sy@asianshipping.com.ph', 'Robinson Sy', 'rob', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-224', NULL, 'rogelio.sy@ravago.com.ph', 'Rogelio Sy', 'rog', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-225', NULL, 'rogelio.almonina@ravago.com.ph', 'Rogelio Almonina', 'rog1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-226', NULL, 'roger.sy@ravago.com.ph', 'Roger Sy', 'rog2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-227', NULL, 'rolito.delvalle@asianshipping.com.ph', 'Rolito Delvalle', 'rol', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-228', NULL, 'ronilo.teriote@asianshipping.com.ph', 'Ronilo Teriote', 'ron', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-229', NULL, 'roque.alvarado@ravago.com.ph', 'Roque Alvarado', 'roq', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-23', NULL, 'aimsrfid@ravago.com.ph', 'Aimsrfid', 'aim', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-230', NULL, 'roselyn.cacayurin@asianshipping.com.ph', 'Roselyn Cacayurin', 'ros', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-231', NULL, 'rossette.axalan@asianshipping.com.ph', 'Rossette Axalan', 'ros1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-232', NULL, 'roxanne.derder@liftrite.com.ph', 'Roxanne Derder', 'rox', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-233', NULL, 'royce.sy@asianshipping.com.ph', 'Royce Sy', 'roy', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-234', NULL, 'rubylyn.lovina@asianshipping.com.ph', 'Rubylyn Lovina', 'rub', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-235', NULL, 'ruel.como@ravago.com.ph', 'Ruel Como', 'rue', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-236', NULL, 'rufina.alli@ravago.com.ph', 'Rufina Alli', 'ruf', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-237', NULL, 'russell.sy@ravago.com.ph', 'Russell Sy', 'rus', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-238', NULL, 'ryan.francisco@asianshipping.com.ph', 'Ryan Francisco', 'rya', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-239', NULL, 'ryan.agawin@ravago.com.ph', 'Ryan Agawin', 'rya1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-24', NULL, 'alain.evangelista@ravago.com.ph', 'Alainee Evangelista', 'ala', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-240', NULL, 'safety.officer.lspi@smartrigs.com.ph', 'Safety Officer Lspi', 'saf', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-241', NULL, 'sales.cdo@smartrigs.com.ph', 'Sales Cdo', 'sal', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-242', NULL, 'sales@asianshipping.com.ph', 'Sales', 'sal1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-243', NULL, 'sales.assistant.cdo.lspi@smartrigs.com.ph', 'Sales Assistant Cdo Lspi', 'sal2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-244', NULL, 'sales@ravago.com.ph', 'Sales', 'sal3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-245', NULL, 'sales.assistant.lspi@smartrigs.com.ph', 'Sales Assistant Lspi', 'sal4', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-246', NULL, 'sales.assistant.ilo.lspi@smartrigs.com.ph', 'Sales Assistant Ilo Lspi', 'sal5', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-247', NULL, 'sandra.nace@asianshipping.com.ph', 'Sandra Nace', 'san', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-248', NULL, 'sarah.chua@ravago.com.ph', 'Sarah Chua', 'sar', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-249', NULL, 'sean.bautista@ravago.com.ph', 'Sean Bautista', 'sea', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-25', NULL, 'aldrien.quinones@ravago.com.ph', 'Aldrien Quinones', 'ald', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-250', NULL, 'shallene.rogon@ravago.com.ph', 'Shallene Rogon', 'sha', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-251', NULL, 'shella.tubay@smartrigs.com.ph', 'Shella Tubay', 'she', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-252', NULL, 'sheryl.adalin@asianslipway.com', 'Sheryl Adalin', 'she1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-253', NULL, 'shiela.rama@ravago.com.ph', 'Shiela Rama', 'shi', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-254', NULL, 'shiella.flores@ravago.com.ph', 'Shiella Flores', 'shi1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-255', NULL, 'snooky.flores@ravago.com.ph', 'Snooky Flores', 'sno', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-256', NULL, 'stella.coliflores@asianshipping.com.ph', 'Stella Coliflores', 'ste', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-257', NULL, 'theresa.soria@asianshipping.com.ph', 'Theresa Soria', 'the', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-258', NULL, 'timekeeper.assistant.ilo.lspi@smartrigs.com.p', 'Timekeeper Assistant Ilo Lspi', 'tim', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-259', NULL, 'timekeeper@ravago.com.ph', 'Timekeeper', 'tim1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-26', NULL, 'aleli.batiles@asianshipping.com.ph', 'Aleli Batiles', 'ale', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-260', NULL, 'timekeeper.assistant.cdo.lspi@smartrigs.com.p', 'Timekeeper Assistant Cdo Lspi', 'tim2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-261', NULL, 'timekeeping.staff.lspi@smartrigs.com.ph', 'Timekeeping Staff Lspi', 'tim3', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-262', NULL, 'tricia.feliciano@ravago.com.ph', 'Tricia Feliciano', 'tri', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-263', NULL, 'trixia.cabigao@asianshipping.com.ph', 'Trixia Cabigao', 'tri1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-264', NULL, 'utilities@asianshipping.com.ph', 'Utilities', 'uti', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-265', NULL, 'utilities@ravago.com.ph', 'Utilities', 'uti1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-266', NULL, 'valerie.sy@ravago.com.ph', 'Valerie Sy', 'val', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-267', NULL, 'vanessa.bareng@ravago.com.ph', 'Vanessa Bareng', 'van', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-268', NULL, 'vincent.singzon@smartrigs.com.ph', 'Vincent Singzon', 'vin', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-269', NULL, 'will.lazo@asianslipway.com', 'Will Lazo', 'wil', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-27', NULL, 'alexander.madera@ravago.com.ph', 'Alexander Madera', 'ale1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-270', NULL, 'wilner.delacruz@asianshipping.com.ph', 'Wilner Delacruz', 'wil1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-271', NULL, 'yolly.yap@ravago.com.ph', 'Yolly Yap', 'yol', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-272', NULL, 'yula.corpuz@ravago.com.ph', 'Yula Corpuz', 'yul', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-273', NULL, 'ilyn.mata@ravago.com.ph', 'Ilyn Mata', 'ily', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-274', NULL, 'angel.bantilan@ravago.com.ph', 'Angel Bantilan', 'ang1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-275', NULL, 'kristine.guevarra@ravago.com.ph', 'Kristine Guevarra', 'kri2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-276', NULL, 'wenny.gabriel@asianslipway.com', 'Wenny Gabriel', 'wen', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-277', NULL, 'jofel.mapisa@ravago.com.ph', 'Jofel Mapisa', 'jof', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-278', NULL, 'elloisa.mirafuentes@asianshipping.com.ph', 'Elloisa Mirafuentes', 'ell1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-279', NULL, 'tachmina.lumagbas@asianshipping.com.ph', 'Tachmina Lumagbas', 'tac', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-28', NULL, 'alexis.imperial@ravago.com.ph', 'Alexis Imperial', 'ale2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-280', NULL, 'payroll.admin@asianshipping.com.ph', 'Payroll Admin', 'pay8', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-281', NULL, 'julius.ereno@ravago.com.ph', 'Julius Ereno', 'jul', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-282', NULL, 'andy.maligmat@smartrigs.com.ph', 'Andy Maligmat', 'and', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-283', NULL, 'vanessa.panuncillo@smartrigs.com.ph', 'Vanessa Panuncillo', 'van1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-284', NULL, 'cristal.visoc@smartrigs.com.ph', 'Cristal Visoc', 'cri', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-285', NULL, 'alldash', 'ALL DASH', 'alldash', '$2y$10$GzKqaW7znAWjw.zhSIgBHOqtHxaOxj8dT8u.lYOdUIUH3lkJbhaUa', 1, '2025-09-22 00:00:00', NULL, NULL, NULL, NULL),
('ALL-29', NULL, 'alma.bontia@smartrigs.com.ph', 'Alma Bontia', 'alm', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-30', NULL, 'alyssa.carino@ravago.com.ph', 'Alyssa Carino', 'aly', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-31', NULL, 'analyn.abono@ravago.com.ph', 'Analyn Abono', 'ana', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-32', NULL, 'angieny.reyes@ravago.com.ph', 'Angieny Reyes', 'ang', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-33', NULL, 'annaliza.marcelino@asianslipway.com', 'Annaliza Marcelino', 'ann', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-34', NULL, 'april.carlos@ravago.com.ph', 'April Carlos', 'apr', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-35', NULL, 'arah.saldo@ravago.com.ph', 'Arah Saldo', 'ara', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-36', NULL, 'arnold.sanpedro@ravago.com.ph', 'Arnold Sanpedro', 'arn', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-37', NULL, 'bea.jacobe@ravago.com.ph', 'Bea Jacobe', 'bea', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-38', NULL, 'bernard.imbornal@ravago.com.ph', 'Bernard Imbornal', 'ber', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-39', NULL, 'betty.penalosa@ravago.com.ph', 'Betty Penalosa', 'bet', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-4', NULL, 'gaspar23angelo@gmail.com', 'test.ops', 'test.ops', '$2y$10$8OJzr/rZEKQHyl8RBmE/Y.BBnbg5tbs5dW3Qen5CGn.UZcAla5NI2', 1, '2023-11-15 13:27:51', NULL, NULL, NULL, NULL),
('ALL-40', NULL, 'billing.collection@asianshipping.com.ph', 'Billing Collection', 'bil', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-41', NULL, 'bismark.valenzuela@asianslipway.com', 'Bismark Valenzuela', 'bis', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-42', NULL, 'camille.serrano@ravago.com.ph', 'Camille Serrano', 'cam', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-43', NULL, 'carol.nunez@asianshipping.com.ph', 'Carol Nunez', 'car', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-44', NULL, 'caryl.yosores@asianshipping.com.ph', 'Caryl Yosores', 'car1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-45', NULL, 'catherine.aligato@smartrigs.com.ph', 'Catherine Aligato', 'cat', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-46', NULL, 'chris.santiago@asianslipway.com', 'Chris Santiago', 'chr', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-47', NULL, 'christianne.manangan@ravago.com.ph', 'Christianne Manangan', 'chr1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-48', NULL, 'crewing@asianshipping.com.ph', 'Crewing', 'cre', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-49', NULL, 'cyril.andrade@ravago.com.ph', 'Cyril Andrade', 'cyr', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-5', 'ADM', 'gaspar24angelo@gmail.com', 'test.dcor', 'test.dcor', '$2y$10$8OJzr/rZEKQHyl8RBmE/Y.BBnbg5tbs5dW3Qen5CGn.UZcAla5NI2', 1, '2023-11-15 13:27:51', NULL, NULL, NULL, NULL),
('ALL-50', NULL, 'daisy.sulat@ravago.com.ph', 'Daisy Sulat', 'dai', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-51', NULL, 'dancel.balentong@asianshipping.com.ph', 'Dancel Balentong', 'dan', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-52', NULL, 'danessa.alvarez@asianshipping.com.ph', 'Danessa Alvarez', 'dan1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-53', NULL, 'daniel.ligot@ravago.com.ph', 'Daniel Ligot', 'dan2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-54', NULL, 'dave.canchela@ravago.com.ph', 'Dave Canchela', 'dav', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-55', NULL, 'dave.carriedo@asianshipping.com.ph', 'Dave Carriedo', 'dav1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-56', NULL, 'dave.leyson@asianshipping.com.ph', 'Dave Leyson', 'dav2', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-57', NULL, 'debbie.sy@ravago.com.ph', 'Debbie Sy', 'deb', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-58', NULL, 'dhanniel.serrano@ravago.com.ph', 'Dhanniel Serrano', 'dha', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-59', NULL, 'dionisio.reyes@ravago.com.ph', 'Dionisio Reyes', 'dio', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-6', NULL, 'gaspar25angelo@gmail.com', 'test.dcon', 'test.dcon', '$2y$10$8OJzr/rZEKQHyl8RBmE/Y.BBnbg5tbs5dW3Qen5CGn.UZcAla5NI2', 1, '2023-11-15 13:27:51', NULL, NULL, NULL, NULL),
('ALL-60', NULL, 'eddref.felix@ravago.com.ph', 'Eddref Felix', 'edd', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-61', NULL, 'edger.morales@ravago.com.ph', 'Edger Morales', 'edg', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-62', NULL, 'edison.sy@asianshipping.com.ph', 'Edison Sy', 'edi', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-63', NULL, 'eleonor.arieta@ravago.com.ph', 'Eleonor Arieta', 'ele', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-64', NULL, 'ellahmae.sisbino@ravago.com.ph', 'Ellahmae Sisbino', 'ell', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-65', NULL, 'emily.aragao@ravago.com.ph', 'Emily Aragao', 'emi', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-66', NULL, 'emmanuelking.david@ravago.com.ph', 'Emmanuelking David', 'emm', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-67', NULL, 'ericka.delacruz@ravago.com.ph', 'Ericka Delacruz', 'eri', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-68', NULL, 'ericka.batulan@asianshipping.com.ph', 'Ericka Batulan', 'eri1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-69', NULL, 'erlyn.marcos@ravago.com.ph', 'Erlyn Marcos', 'erl', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-70', NULL, 'erlyn.delsocorro@ravago.com.ph', 'Erlyn Delsocorro', 'erl1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-71', NULL, 'erwin.delfin@ravago.com.ph', 'Erwin Delfin', 'erw', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-72', NULL, 'eunice.yu@asianshipping.com.ph', 'Eunice Yu', 'eun', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-73', NULL, 'faye.solomon@asianshipping.com.ph', 'Faye Solomon', 'fay', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-74', NULL, 'belle.lontok@ravago.com.ph', 'Belle Lontok', 'bel', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-75', NULL, 'fretzie.camorro@asianshipping.com.ph', 'Fretzie Camorro', 'fre', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-76', NULL, 'geralyn.caraballe@smartrigs.com.ph', 'Geralyn Caraballe', 'ger', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-77', NULL, 'ginien.bernal@asianshipping.com.ph', 'Ginien Bernal', 'gin', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-78', NULL, 'gleicel.jabajab@ravago.com.ph', 'Gleicel Jabajab', 'gle', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-79', NULL, 'glory.guarte@ravago.com.ph', 'Glory Guarte', 'glo', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-8', 'ADM', '', 'test.transport', 'test.transport', '$2y$10$GzKqaW7znAWjw.zhSIgBHOqtHxaOxj8dT8u.lYOdUIUH3lkJbhaUa', 1, '2024-03-18 11:13:09', NULL, NULL, NULL, NULL),
('ALL-80', NULL, 'godpray.abella@asianshipping.com.ph', 'Godpray Abella', 'god', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-81', NULL, 'gutzier.gutierrez@ravago.com.ph', 'Gutzier Gutierrez', 'gut', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-82', NULL, 'hr.assistant@smartrigs.com.ph', 'Hr Assistant', 'hra', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-83', NULL, 'hr.documentation@ravago.com.ph', 'Hr Documentation', 'hrd', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-84', NULL, 'hr-internalcomm@ravago.com.ph', 'Hr-Internalcomm', 'hr-', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-85', NULL, 'hr.coordinator.ojt@ravago.com.ph', 'Hr Coordinator Ojt', 'hrc', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-86', NULL, 'hr.admin@ravago.com.ph', 'Hr Admin', 'hra1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-87', NULL, 'hr.recruitment@ravago.com.ph', 'Hr Recruitment', 'hrr', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-88', NULL, 'hr.recruitment@smartrigs.com.ph', 'Hr Recruitment', 'hrr1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-89', NULL, 'ian.albay@ravago.com.ph', 'Ian Albay', 'ian', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-9', NULL, 'logistics.assistant.lspi1@ravago.com.ph', 'Pocholo Fajardo', 'pocholo.fajardo@liberty', '$2y$10$tSLPYNlcSCfphCFkSHB9ae756vrdG8ch/IsbL3XRp2bNrStxvgg1.', 1, '2024-04-03 09:57:40', NULL, NULL, NULL, NULL),
('ALL-90', NULL, 'inventory.assistant.lspi@asianshipping.com.ph', 'Inventory Assistant Lspi', 'inv', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-91', NULL, 'irwin.samulde@ravago.com.ph', 'Irwin Samulde', 'irw', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-92', NULL, 'it.admin@ravago.com.ph', 'It Admin', 'ita', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-93', NULL, 'itservice.desk@ravago.com.ph', 'Itservice Desk', 'its', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-94', NULL, 'itsupport.assistant.lspi@ravago.com.ph', 'Itsupport Assistant Lspi', 'its1', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-95', NULL, 'jackie.emata@ravago.com.ph', 'Jackie Emata', 'jac', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-96', NULL, 'jaira.tenolete@ravago.com.ph', 'Jaira Tenolete', 'jai', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-97', NULL, 'jake.dy@ravago.com.ph', 'Jake Dy', 'jak', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-98', NULL, 'jamie.lopez@ravago.com.ph', 'Jamie Lopez', 'jam', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('ALL-99', NULL, 'jana.agusila@asianshipping.com.ph', 'Jana Agusila', 'jan', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('DUMMY-1', NULL, 'etr.requestor', 'etr.requestor', 'etr.requestor', '$2y$10$GzKqaW7znAWjw.zhSIgBHOqtHxaOxj8dT8u.lYOdUIUH3lkJbhaUa', 1, '2024-04-03 09:57:40', NULL, NULL, NULL, NULL),
('PUR-1', NULL, 'ryan.masungsong@ravago.com.ph', 'Ryan Albert Masungsong', 'ryan094', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, '2024-07-03 09:57:40', 'dIPqRN0c9kvNU42MAGfgkkKngIWscwDkgUfFw2diQZ33Rou6MXMfdRg445el', NULL, NULL, NULL);
INSERT INTO `usr` (`userId`, `roleId`, `email`, `name`, `userName`, `userPassword`, `isActive`, `dtCreated`, `reset_token_hash`, `reset_token_expires_at`, `imgPath`, `orisoft_user_id`) VALUES
('PUR-10', NULL, 'ernest.hernan@ravago.com.ph', 'Ernest Hernan', 'ern', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-11', NULL, 'ruby.cervantes@ravago.com.ph', 'Ruby Cervantes', 'ruby', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-12', NULL, 'diana.soriano@ravago.com.ph', 'Diana Soriano', 'dia', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-13', NULL, 'richard.vea@ravago.com.ph', 'Richard Vea', 'chad', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-14', NULL, 'marvin.pangilinan@ravago.com.ph', 'Marvin Pangilinan', 'marv', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-15', NULL, 'nathaniel.chung@ravago.com.ph', 'Nathaniel Chung', 'nat', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-16', NULL, 'karl.cabato@ravago.com.ph', 'Karl Cabato', 'karl', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-17', NULL, 'anna.tuonan@ravago.com.ph', 'Anna Luisa Tuonan', 'annalou', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-5', NULL, 'test.execo@g.co', 'Test Execom', 'testexecom', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-6', NULL, 'test.staff@g.co', 'Test Staff', 'teststaff', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-7', NULL, 'test.purad@g.co', 'Test Purad', 'testpurad', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-8', NULL, 'test.head@g.co', 'Test Head', 'testhead', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL),
('PUR-9', NULL, 'kennery.villacaol@ravago.com.ph', 'Kennery Villacaol', 'kenn', '$2y$10$IUJTX0sQ2TPUdDluAl0J7unDzyTtOfWIMXUf5G8UW3ehOseG3AkoS', 1, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usrcl`
--

CREATE TABLE `usrcl` (
  `lineNo` int(11) NOT NULL,
  `userName` varchar(45) DEFAULT NULL,
  `remarks` varchar(150) DEFAULT NULL,
  `dtUpdated` datetime DEFAULT NULL,
  `encoder` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `usrcl`
--

INSERT INTO `usrcl` (`lineNo`, `userName`, `remarks`, `dtUpdated`, `encoder`) VALUES
(30, 'angelo.gaspar@ravago.com.ph', 'user id : angelo.gaspar@ravago.com.ph \r\n        priviledge changed to : GUEST', '2024-05-17 16:31:21', NULL),
(31, 'angelo.gaspar@ravago.com.ph', 'user id : angelo.gaspar@ravago.com.ph \r\n        priviledge changed to : GUEST', '2024-05-17 16:42:43', NULL),
(32, 'test.ops', 'user id : angelo.gaspar@ravago.com.ph \r\n        priviledge changed to : MGR', '2024-05-20 10:37:00', NULL),
(33, 'test.ops', 'user id : angelo.gaspar@ravago.com.ph \r\n        priviledge changed to : GUEST', '2024-05-20 10:42:54', NULL),
(34, 'test.ops', 'user id : angelo.gaspar@ravago.com.ph \r\n        priviledge changed to : MGR', '2024-05-20 10:43:13', NULL),
(35, 'test.engr', 'user id : test.engr \r\n        priviledge changed to : BASIC', '2024-05-20 11:01:54', NULL),
(36, 'test.engr', 'user id : test.engr \r\n        priviledge changed to : MGR', '2024-05-20 11:02:04', NULL),
(37, 'rufina.alli@ravago.com.ph', 'user id : gleicel.jabajab@liberty \r\n        priviledge changed to : MGR', '2024-05-20 13:43:34', NULL),
(38, 'bernard.imbornal@ravago.com.ph', 'user id : rachel.toquia@ravago.com.ph \r\n        priviledge changed to : MGR', '2024-08-16 13:48:43', NULL),
(45, 'joyce.jimenez@ravago.com.ph', 'EBCE , 1 ,  , ', '2024-09-09 11:53:58', 'test.etr'),
(46, 'joyce.jimenez@ravago.com.ph', 'EBCE , 1 , MGR , ', '2024-09-11 08:45:53', 'test.etr'),
(47, 'joyce.jimenez@ravago.com.ph', 'EBCE , 1 ,  , ', '2024-09-11 08:46:01', 'test.etr'),
(48, 'racquel.teope@ravago.com.ph', 'EBCE , 1 , MGR , ', '2024-09-11 11:52:50', 'test.etr'),
(49, 'snooky.flores@ravago.com.ph', 'EBCE , 1 , MGR , ', '2025-05-21 11:54:13', 'test.etr'),
(50, 'kc.ellima@ravago.com.ph', 'EBCE , 0 ,  , ', '2025-07-03 09:12:50', 'test.etr');

-- --------------------------------------------------------

--
-- Table structure for table `usrprvg`
--

CREATE TABLE `usrprvg` (
  `lineNo` int(11) NOT NULL,
  `gUserName` varchar(64) NOT NULL,
  `priviledgeCode` varchar(5) NOT NULL,
  `appId` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `usrprvg`
--

INSERT INTO `usrprvg` (`lineNo`, `gUserName`, `priviledgeCode`, `appId`) VALUES
(1, 'angelo.gaspar@ravago.com.ph', 'MGR', 'SOPS'),
(2, 'test.sales', 'MGR', 'SSLS'),
(3, 'test.engr', 'MGR', 'SENGR'),
(4, 'test.ops', 'MGR', 'SOPS'),
(5, 'jayrica.gamba@ravago.com.ph', 'MGR', 'SOPS'),
(6, 'jackie.emata@ravago.com.ph', 'MGR', 'SSLS'),
(7, 'bernard.imbornal@ravago.com.ph', 'MGR', 'SENGR'),
(8, 'rufina.alli@ravago.com.ph', 'MGR', 'SOPS'),
(9, 'test.dcor', 'DCOR', 'SOPS'),
(10, 'ruel.como@ravago.com.ph', 'MGR', 'SENGR'),
(11, 'test.dcon', 'DCON', 'SOPS'),
(12, 'vanessa.bareng@ravago.com.ph', 'MGR', 'SOPS'),
(13, 'kerstine.ambito@ravago.com.ph', 'MGR', 'STRAN'),
(14, 'jeneah.subalisid@ravago.com.ph', 'DCOR', 'SOPS'),
(15, 'gleicel.jabajab@liberty', 'DCOR', 'SOPS'),
(16, 'test.transport', 'MGR', 'STRAN'),
(17, 'bea.jacobe@ravago.com.ph', 'MGR', 'STRAN'),
(18, 'shallene.rogon@ravago.com.ph', 'DCON', 'STRAN'),
(19, 'pocholo.fajardo@liberty', 'DCOR', 'STRAN'),
(20, 'joyceann.parra@liberty', 'DCOR', 'STRAN');

-- --------------------------------------------------------

--
-- Structure for view `purjo_users_view`
--
DROP TABLE IF EXISTS `purjo_users_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbadmin`@`%` SQL SECURITY DEFINER VIEW `purjo_users_view`  AS SELECT `usr`.`userId` AS `userId`, `usr`.`roleId` AS `roleId`, `usr`.`email` AS `email`, `usr`.`name` AS `name`, `usr`.`userName` AS `userName`, `usr`.`userPassword` AS `userPassword`, `usr`.`isActive` AS `usr_isActive`, `usr`.`dtCreated` AS `usr_dtCreated`, `usr`.`reset_token_hash` AS `reset_token_hash`, `usr`.`reset_token_expires_at` AS `reset_token_expires_at`, `usr`.`imgPath` AS `imgPath`, `usr`.`orisoft_user_id` AS `orisoft_user_id`, `appusr`.`appusrId` AS `appusrId`, `appusr`.`appId` AS `appId`, `appusr`.`gUserName` AS `gUserName`, `appusr`.`isActive` AS `appusr_isActive`, `appusr`.`priviledgeCode` AS `priviledgeCode`, `appusr`.`dtCreated` AS `appusr_dtCreated` FROM (`usr` join `appusr` on(`appusr`.`gUserName` = `usr`.`userName`)) WHERE `appusr`.`appId` = 'PURJO' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app`
--
ALTER TABLE `app`
  ADD PRIMARY KEY (`appId`);

--
-- Indexes for table `appusr`
--
ALTER TABLE `appusr`
  ADD PRIMARY KEY (`appusrId`),
  ADD KEY `fkAPPUSRappid_idx` (`appId`),
  ADD KEY `fkAPPSURpriviledgecode_idx` (`priviledgeCode`);

--
-- Indexes for table `prvgs`
--
ALTER TABLE `prvgs`
  ADD PRIMARY KEY (`priviledgeCode`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`roleId`);

--
-- Indexes for table `salesrep`
--
ALTER TABLE `salesrep`
  ADD PRIMARY KEY (`repId`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`settingsId`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`idtest`);

--
-- Indexes for table `usr`
--
ALTER TABLE `usr`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `userName_UNIQUE` (`userName`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`),
  ADD KEY `fkUSRroleid_idx` (`roleId`);

--
-- Indexes for table `usrcl`
--
ALTER TABLE `usrcl`
  ADD PRIMARY KEY (`lineNo`);

--
-- Indexes for table `usrprvg`
--
ALTER TABLE `usrprvg`
  ADD PRIMARY KEY (`lineNo`),
  ADD KEY `fkUSRPRVpriviledgecode_idx` (`priviledgeCode`),
  ADD KEY `fkUSRPRVappid_idx` (`appId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appusr`
--
ALTER TABLE `appusr`
  MODIFY `appusrId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4424;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `idtest` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usrcl`
--
ALTER TABLE `usrcl`
  MODIFY `lineNo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `usrprvg`
--
ALTER TABLE `usrprvg`
  MODIFY `lineNo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `usr`
--
ALTER TABLE `usr`
  ADD CONSTRAINT `fkUSRroleid` FOREIGN KEY (`roleId`) REFERENCES `role` (`roleId`);

--
-- Constraints for table `usrprvg`
--
ALTER TABLE `usrprvg`
  ADD CONSTRAINT `fkUSRPRVappid` FOREIGN KEY (`appId`) REFERENCES `app` (`appId`),
  ADD CONSTRAINT `fkUSRPRVpriviledgecode` FOREIGN KEY (`priviledgeCode`) REFERENCES `prvgs` (`priviledgeCode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
