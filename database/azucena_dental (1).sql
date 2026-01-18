-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2026 at 03:26 PM
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
-- Database: `azucena_dental`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `is_online_appointment` tinyint(1) DEFAULT 0,
  `status_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `seat_id` int(11) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `rescheduled_from` int(11) DEFAULT NULL,
  `consultation_notes` longtext DEFAULT NULL,
  `follow_up_notes` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `appointment_date`, `appointment_time`, `status`, `is_online_appointment`, `status_id`, `created_at`, `seat_id`, `patient_id`, `doctor_id`, `rescheduled_from`, `consultation_notes`, `follow_up_notes`) VALUES
(2, '2026-01-18', '14:30:00', NULL, 1, 1, '2026-01-18 14:23:40', NULL, 1, 3, NULL, NULL, NULL),
(3, '2026-01-18', '10:00:00', NULL, 1, 27, '2026-01-18 14:47:55', NULL, 1, 3, NULL, NULL, NULL),
(4, '2026-01-18', '11:00:00', NULL, 1, 25, '2026-01-18 14:48:25', NULL, 1, 3, NULL, '', ''),
(5, '2026-01-19', '09:00:00', NULL, 1, 1, '2026-01-18 21:08:12', NULL, 2, 3, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_trails`
--

CREATE TABLE `audit_trails` (
  `audit_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `time_stamp` datetime DEFAULT current_timestamp(),
  `data_changed` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup`
--

CREATE TABLE `backup` (
  `backup_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `backup_date` datetime DEFAULT current_timestamp(),
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `billing_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status_id` int(11) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`billing_id`, `appointment_id`, `user_id`, `total_amount`, `created_at`, `status_id`, `processed_by`) VALUES
(1, 3, 4, 41500.00, '2026-01-18 21:15:39', NULL, 4);

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `reason` text DEFAULT NULL,
  `address` text NOT NULL,
  `telephone_no` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `branch_name`, `reason`, `address`, `telephone_no`, `email`, `status`, `created_at`, `user_id`) VALUES
(1, 'Main Branch - Cagayan De Oro', 'Main clinic location', 'Metz Arcade, Barangay 24, Capt. Vicente Roa St, Cagayan De Oro City, 9000, Misamis Oriental, Philippines', '(088) 123-4567', 'main@azucenadental.com', 22, '2026-01-18 11:57:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `check_in_out`
--

CREATE TABLE `check_in_out` (
  `checkIn_id` int(11) NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) NOT NULL,
  `que_number` int(11) DEFAULT NULL,
  `served_time` time DEFAULT NULL,
  `completed_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `history_id` int(11) NOT NULL,
  `patients_id` int(11) NOT NULL,
  `history_type` varchar(100) DEFAULT NULL,
  `history_from` date DEFAULT NULL,
  `history_to` date DEFAULT NULL,
  `detailed_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patients_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `added_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patients_id`, `user_id`, `first_name`, `last_name`, `middle_name`, `username`, `password`, `address`, `date_of_birth`, `gender`, `phone_number`, `email`, `created_at`, `added_by`) VALUES
(1, 5, 'Juan', 'Patient', NULL, 'patient', NULL, NULL, NULL, 'Male', '09123456789', 'patient@clinic.com', '2026-01-18 14:19:51', NULL),
(2, 7, 'Paul Benjie', 'Gonzales', 'Maglupay', 'paulbgonzales', 'paul123', 'Zone 3 Agusan CDO', '1997-08-25', 'Male', '096546511564', 'paul@gmail.com', '2026-01-18 20:55:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `billing_id` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL,
  `prescription_date` date DEFAULT NULL,
  `medicine_name` varchar(100) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `medicine_brand` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `selected_treatment_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`, `description`) VALUES
(1, 'admin', 'System Administrator - Full access to all modules'),
(2, 'doctor', 'Doctor - Manage appointments and patient records'),
(3, 'secretary', 'Secretary - Handle bookings and payments'),
(4, 'patient', 'Patient - Book appointments and view records');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(11) NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `available_from` time DEFAULT NULL,
  `available_to` time DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `status_id`, `branch_id`, `available_from`, `available_to`, `day_of_week`, `user_id`) VALUES
(1, 1, NULL, '09:00:00', '15:00:00', 'Monday', 3),
(2, 1, NULL, '09:00:00', '15:00:00', 'Tuesday', 3),
(3, 1, NULL, '09:00:00', '15:00:00', 'Wednesday', 3),
(4, 1, NULL, '09:00:00', '15:00:00', 'Thursday', 3),
(5, 1, NULL, '09:00:00', '15:00:00', 'Friday', 3);

-- --------------------------------------------------------

--
-- Table structure for table `selected_services`
--

CREATE TABLE `selected_services` (
  `selected_service_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `selected_services`
--

INSERT INTO `selected_services` (`selected_service_id`, `appointment_id`, `notes`, `service_id`) VALUES
(1, 2, NULL, 1),
(2, 3, NULL, 1),
(3, 3, NULL, 9),
(4, 5, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `selected_treatments`
--

CREATE TABLE `selected_treatments` (
  `selected_treatment_id` int(11) NOT NULL,
  `effective_service_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `tooth_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `initial_deposit` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `added_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `initial_deposit`, `description`, `created_at`, `added_by`) VALUES
(1, 'Dental Consultation', 500.00, 'General dental checkup and consultation', '2026-01-18 11:57:55', 1),
(2, 'Teeth Cleaning (Prophylaxis)', 800.00, 'Professional teeth cleaning and polishing', '2026-01-18 11:57:55', 1),
(3, 'Tooth Extraction', 1500.00, 'Simple tooth extraction procedure', '2026-01-18 11:57:55', 1),
(4, 'Tooth Filling (Pasta)', 1200.00, 'Tooth cavity filling with composite', '2026-01-18 11:57:55', 1),
(5, 'Root Canal Treatment', 8000.00, 'Endodontic root canal therapy', '2026-01-18 11:57:55', 1),
(6, 'Dental Crown', 12000.00, 'Tooth crown installation', '2026-01-18 11:57:55', 1),
(7, 'Teeth Whitening', 5000.00, 'Professional teeth bleaching', '2026-01-18 11:57:55', 1),
(8, 'Dental Braces (Metal)', 50000.00, 'Complete orthodontic treatment with metal braces', '2026-01-18 11:57:55', 1),
(9, 'Dental Implant', 40000.00, 'Single tooth implant procedure', '2026-01-18 11:57:55', 1),
(10, 'Oral Surgery', 15000.00, 'Surgical dental procedures', '2026-01-18 11:57:55', 1),
(11, 'Dental Bridge', 18000.00, 'Fixed dental bridge', '2026-01-18 11:57:55', 1),
(12, 'Dentures (Complete Set)', 25000.00, 'Full set of removable dentures', '2026-01-18 11:57:55', 1),
(13, 'Scaling and Root Planing', 2500.00, 'Deep cleaning for gum disease treatment', '2026-01-18 21:47:48', 1),
(14, 'Gum Disease Treatment', 3000.00, 'Treatment for periodontitis and gingivitis', '2026-01-18 21:47:48', 1),
(15, 'Bonded Veneer', 8000.00, 'Cosmetic tooth veneer bonding', '2026-01-18 21:47:48', 1),
(16, 'Ceramic Crown', 15000.00, 'Ceramic tooth crown installation', '2026-01-18 21:47:48', 1),
(17, 'Braces (Ceramic)', 55000.00, 'Ceramic orthodontic braces', '2026-01-18 21:47:48', 1),
(18, 'Clear Aligners (Invisalign)', 60000.00, 'Invisible teeth alignment system', '2026-01-18 21:47:48', 1),
(19, 'Tooth Bonding', 3000.00, 'Composite resin bonding for damaged teeth', '2026-01-18 21:47:48', 1),
(20, 'Wisdom Tooth Extraction', 3500.00, 'Surgical removal of wisdom teeth', '2026-01-18 21:47:48', 1),
(21, 'Dental X-ray', 300.00, 'Digital radiographic imaging', '2026-01-18 21:47:48', 1),
(22, 'Fluoride Treatment', 500.00, 'Protective fluoride application', '2026-01-18 21:47:48', 1),
(23, 'Sealant Application', 600.00, 'Preventive dental sealants', '2026-01-18 21:47:48', 1),
(24, 'Night Guard (Bruxism)', 2000.00, 'Custom-made night guard for teeth grinding', '2026-01-18 21:47:48', 1),
(25, 'Partial Dentures', 15000.00, 'Removable partial dentures', '2026-01-18 21:47:48', 1),
(26, 'Removable Braces', 35000.00, 'Removable orthodontic appliances', '2026-01-18 21:47:48', 1),
(27, 'Teeth Desensitization', 1500.00, 'Treatment for sensitive teeth', '2026-01-18 21:47:48', 1),
(28, 'Bite Correction', 20000.00, 'Occlusal adjustment and correction', '2026-01-18 21:47:48', 1),
(29, 'Scaling and Root Planing', 2500.00, 'Deep cleaning for gum disease treatment', '2026-01-18 21:48:10', 1),
(30, 'Gum Disease Treatment', 3000.00, 'Treatment for periodontitis and gingivitis', '2026-01-18 21:48:10', 1),
(31, 'Bonded Veneer', 8000.00, 'Cosmetic tooth veneer bonding', '2026-01-18 21:48:10', 1),
(32, 'Ceramic Crown', 15000.00, 'Ceramic tooth crown installation', '2026-01-18 21:48:10', 1),
(33, 'Braces (Ceramic)', 55000.00, 'Ceramic orthodontic braces', '2026-01-18 21:48:10', 1),
(34, 'Clear Aligners (Invisalign)', 60000.00, 'Invisible teeth alignment system', '2026-01-18 21:48:10', 1),
(35, 'Tooth Bonding', 3000.00, 'Composite resin bonding for damaged teeth', '2026-01-18 21:48:10', 1),
(36, 'Wisdom Tooth Extraction', 3500.00, 'Surgical removal of wisdom teeth', '2026-01-18 21:48:10', 1),
(37, 'Dental X-ray', 300.00, 'Digital radiographic imaging', '2026-01-18 21:48:10', 1),
(38, 'Fluoride Treatment', 500.00, 'Protective fluoride application', '2026-01-18 21:48:10', 1),
(39, 'Sealant Application', 600.00, 'Preventive dental sealants', '2026-01-18 21:48:10', 1),
(40, 'Night Guard (Bruxism)', 2000.00, 'Custom-made night guard for teeth grinding', '2026-01-18 21:48:10', 1),
(41, 'Partial Dentures', 15000.00, 'Removable partial dentures', '2026-01-18 21:48:10', 1),
(42, 'Removable Braces', 35000.00, 'Removable orthodontic appliances', '2026-01-18 21:48:10', 1),
(43, 'Teeth Desensitization', 1500.00, 'Treatment for sensitive teeth', '2026-01-18 21:48:10', 1),
(44, 'Bite Correction', 20000.00, 'Occlusal adjustment and correction', '2026-01-18 21:48:10', 1);

-- --------------------------------------------------------

--
-- Table structure for table `specialization`
--

CREATE TABLE `specialization` (
  `specialization_id` int(11) NOT NULL,
  `specialization_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialization`
--

INSERT INTO `specialization` (`specialization_id`, `specialization_name`, `description`) VALUES
(1, 'General Dentistry', 'General dental care and checkups'),
(2, 'Orthodontics', 'Braces and teeth alignment'),
(3, 'Endodontics', 'Root canal treatments'),
(4, 'Periodontics', 'Gum disease treatment'),
(5, 'Prosthodontics', 'Dental prosthetics and implants'),
(6, 'Oral Surgery', 'Surgical procedures');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_id` int(11) NOT NULL,
  `status_category` varchar(50) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `status_category`, `status_name`, `description`) VALUES
(1, 'appointment', 'Pending', 'Appointment is pending confirmation'),
(2, 'appointment', 'Confirmed', 'Appointment has been confirmed'),
(3, 'appointment', 'Completed', 'Appointment has been completed'),
(4, 'appointment', 'Cancelled', 'Appointment has been cancelled'),
(5, 'appointment', 'No Show', 'Patient did not show up'),
(6, 'appointment', 'Rescheduled', 'Appointment has been rescheduled'),
(7, 'payment', 'Pending', 'Payment is pending'),
(8, 'payment', 'Partial', 'Partially paid'),
(9, 'payment', 'Paid', 'Fully paid'),
(10, 'payment', 'Refunded', 'Payment refunded'),
(11, 'treatment', 'Planned', 'Treatment is planned'),
(12, 'treatment', 'In Progress', 'Treatment is ongoing'),
(13, 'treatment', 'Completed', 'Treatment completed'),
(14, 'treatment', 'Cancelled', 'Treatment cancelled'),
(15, 'tooth', 'Healthy', 'Tooth is healthy'),
(16, 'tooth', 'Cavity', 'Tooth has cavity'),
(17, 'tooth', 'Filled', 'Tooth has been filled'),
(18, 'tooth', 'Root Canal', 'Root canal treatment'),
(19, 'tooth', 'Crowned', 'Tooth has crown'),
(20, 'tooth', 'Missing', 'Tooth is missing'),
(21, 'tooth', 'Extracted', 'Tooth has been extracted'),
(22, 'general', 'Active', 'Record is active'),
(23, 'general', 'Inactive', 'Record is inactive'),
(24, 'general', 'Deleted', 'Record is deleted'),
(25, '', 'In-Queue', NULL),
(26, '', 'In-Progress', NULL),
(27, '', 'Completed', NULL),
(28, '', 'Pending', NULL),
(29, '', 'Confirmed', NULL),
(30, '', 'Cancelled', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tooth`
--

CREATE TABLE `tooth` (
  `tooth_id` int(11) NOT NULL,
  `tooth_number` varchar(10) DEFAULT NULL,
  `tooth_surface` varchar(50) DEFAULT NULL,
  `upper_lower` varchar(10) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tooth`
--

INSERT INTO `tooth` (`tooth_id`, `tooth_number`, `tooth_surface`, `upper_lower`, `status_id`) VALUES
(1, '1', NULL, 'upper', 15),
(2, '2', NULL, 'upper', 15),
(3, '3', NULL, 'upper', 15),
(4, '4', NULL, 'upper', 15),
(5, '5', NULL, 'upper', 15),
(6, '6', NULL, 'upper', 15),
(7, '7', NULL, 'upper', 15),
(8, '8', NULL, 'upper', 15),
(9, '9', NULL, 'upper', 15),
(10, '10', NULL, 'upper', 15),
(11, '11', NULL, 'upper', 15),
(12, '12', NULL, 'upper', 15),
(13, '13', NULL, 'upper', 15),
(14, '14', NULL, 'upper', 15),
(15, '15', NULL, 'upper', 15),
(16, '16', NULL, 'upper', 15),
(17, '17', NULL, 'lower', 15),
(18, '18', NULL, 'lower', 15),
(19, '19', NULL, 'lower', 15),
(20, '20', NULL, 'lower', 15),
(21, '21', NULL, 'lower', 15),
(22, '22', NULL, 'lower', 15),
(23, '23', NULL, 'lower', 15),
(24, '24', NULL, 'lower', 15),
(25, '25', NULL, 'lower', 15),
(26, '26', NULL, 'lower', 15),
(27, '27', NULL, 'lower', 15),
(28, '28', NULL, 'lower', 15),
(29, '29', NULL, 'lower', 15),
(30, '30', NULL, 'lower', 15),
(31, '31', NULL, 'lower', 15),
(32, '32', NULL, 'lower', 15);

-- --------------------------------------------------------

--
-- Table structure for table `tooth_images`
--

CREATE TABLE `tooth_images` (
  `image_id` int(11) NOT NULL,
  `image_file` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `file_name` varchar(255) DEFAULT NULL,
  `selected_treatment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tooth_records`
--

CREATE TABLE `tooth_records` (
  `tooth_record_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `tooth_number` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tooth_records`
--

INSERT INTO `tooth_records` (`tooth_record_id`, `appointment_id`, `tooth_number`, `status`, `created_at`) VALUES
(1, 3, 11, 'filled', '2026-01-18 07:37:51'),
(5, 4, 3, 'healthy', '2026-01-18 13:44:56'),
(6, 4, 16, 'missing', '2026-01-18 13:44:56'),
(7, 4, 23, 'caries', '2026-01-18 13:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `treatment_id` int(11) NOT NULL,
  `treatment_name` varchar(100) NOT NULL,
  `treatment_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `added_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatments`
--

INSERT INTO `treatments` (`treatment_id`, `treatment_name`, `treatment_type`, `description`, `created_at`, `added_by`) VALUES
(1, 'Cavity Filling', 'Restorative', 'Fill tooth cavity with composite material', '2026-01-18 11:57:55', 1),
(2, 'Root Canal', 'Endodontic', 'Remove infected pulp and seal tooth', '2026-01-18 11:57:55', 1),
(3, 'Tooth Extraction', 'Surgical', 'Remove damaged or problematic tooth', '2026-01-18 11:57:55', 1),
(4, 'Crown Placement', 'Restorative', 'Install dental crown on tooth', '2026-01-18 11:57:55', 1),
(5, 'Scaling', 'Preventive', 'Deep cleaning of teeth and gums', '2026-01-18 11:57:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_access_modules`
--

CREATE TABLE `user_access_modules` (
  `user_module_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `modules` varchar(100) DEFAULT NULL,
  `can_edit` tinyint(1) DEFAULT 0,
  `can_view` tinyint(1) DEFAULT 0,
  `can_deactivate` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `first_name`, `last_name`, `username`, `password`, `date_of_birth`, `gender`, `phone_number`, `email`, `address`, `specialization_id`, `role_id`, `added_by`, `is_active`, `created_at`) VALUES
(1, 'System', 'Administrator', 'admin', 'admin123', '1990-01-01', 'Other', '09123456789', 'admin@azucenadental.com', NULL, NULL, 1, NULL, 1, '2026-01-18 11:57:55'),
(3, 'Dr.', 'Santos', 'doctor', 'doctor123', '1990-01-01', 'Male', '0901111111', 'doctor@azucenadental.com', NULL, NULL, 2, NULL, 1, '2026-01-18 12:51:21'),
(4, 'Maria', 'Secretary', 'secretary', 'secretary123', '1990-01-01', 'Female', '0902222222', 'secretary@azucenadental.com', NULL, NULL, 3, NULL, 1, '2026-01-18 12:51:21'),
(5, 'Juan', 'Patient', 'patient', 'patient123', '1990-01-01', 'Male', '0903333333', 'patient@azucenadental.com', NULL, NULL, 4, NULL, 1, '2026-01-18 12:51:21'),
(7, 'Paul Benjie', 'Gonzales', 'paulbgonzales', 'paul123', '1997-08-25', 'Male', '096546511564', 'paul@gmail.com', 'Zone 3 Agusan CDO', NULL, 4, NULL, 1, '2026-01-18 20:55:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `user_module_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `idx_appointment_date` (`appointment_date`),
  ADD KEY `idx_appointment_patient` (`patient_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `idx_appointment_datetime` (`appointment_date`,`appointment_time`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `audit_trails`
--
ALTER TABLE `audit_trails`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `idx_user_action` (`user_id`,`action`),
  ADD KEY `idx_timestamp` (`time_stamp`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `backup`
--
ALTER TABLE `backup`
  ADD PRIMARY KEY (`backup_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`billing_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`branch_id`),
  ADD KEY `idx_branch_status` (`status`);

--
-- Indexes for table `check_in_out`
--
ALTER TABLE `check_in_out`
  ADD PRIMARY KEY (`checkIn_id`),
  ADD UNIQUE KEY `uk_appointment` (`appointment_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `patients_id` (`patients_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patients_id`),
  ADD KEY `idx_patient_name` (`last_name`,`first_name`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `billing_id` (`billing_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_appointment` (`appointment_id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `selected_treatment_id` (`selected_treatment_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `uk_role_name` (`role_name`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_schedule_user` (`user_id`),
  ADD KEY `idx_schedule_branch` (`branch_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `selected_services`
--
ALTER TABLE `selected_services`
  ADD PRIMARY KEY (`selected_service_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `selected_treatments`
--
ALTER TABLE `selected_treatments`
  ADD PRIMARY KEY (`selected_treatment_id`),
  ADD KEY `effective_service_id` (`effective_service_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `tooth_id` (`tooth_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `specialization`
--
ALTER TABLE `specialization`
  ADD PRIMARY KEY (`specialization_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `idx_status_category` (`status_category`);

--
-- Indexes for table `tooth`
--
ALTER TABLE `tooth`
  ADD PRIMARY KEY (`tooth_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `tooth_images`
--
ALTER TABLE `tooth_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `selected_treatment_id` (`selected_treatment_id`);

--
-- Indexes for table `tooth_records`
--
ALTER TABLE `tooth_records`
  ADD PRIMARY KEY (`tooth_record_id`),
  ADD KEY `idx_appointment` (`appointment_id`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`treatment_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `user_access_modules`
--
ALTER TABLE `user_access_modules`
  ADD PRIMARY KEY (`user_module_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uk_username` (`username`),
  ADD UNIQUE KEY `uk_email` (`email`),
  ADD KEY `idx_role` (`role_id`),
  ADD KEY `idx_specialization` (`specialization_id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`user_module_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `audit_trails`
--
ALTER TABLE `audit_trails`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup`
--
ALTER TABLE `backup`
  MODIFY `backup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `billing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `check_in_out`
--
ALTER TABLE `check_in_out`
  MODIFY `checkIn_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patients_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `selected_services`
--
ALTER TABLE `selected_services`
  MODIFY `selected_service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `selected_treatments`
--
ALTER TABLE `selected_treatments`
  MODIFY `selected_treatment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `specialization`
--
ALTER TABLE `specialization`
  MODIFY `specialization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tooth`
--
ALTER TABLE `tooth`
  MODIFY `tooth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tooth_images`
--
ALTER TABLE `tooth_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tooth_records`
--
ALTER TABLE `tooth_records`
  MODIFY `tooth_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `treatments`
--
ALTER TABLE `treatments`
  MODIFY `treatment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_access_modules`
--
ALTER TABLE `user_access_modules`
  MODIFY `user_module_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `user_module_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patients_id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `user_account` (`user_id`);

--
-- Constraints for table `audit_trails`
--
ALTER TABLE `audit_trails`
  ADD CONSTRAINT `audit_trails_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`),
  ADD CONSTRAINT `audit_trails_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `backup`
--
ALTER TABLE `backup`
  ADD CONSTRAINT `backup_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`);

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `billing_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`),
  ADD CONSTRAINT `billing_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `billing_ibfk_4` FOREIGN KEY (`processed_by`) REFERENCES `user_account` (`user_id`);

--
-- Constraints for table `check_in_out`
--
ALTER TABLE `check_in_out`
  ADD CONSTRAINT `check_in_out_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `check_in_out_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`patients_id`) REFERENCES `patients` (`patients_id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `user_account` (`user_id`),
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`billing_id`) REFERENCES `billing` (`billing_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patients_id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`selected_treatment_id`) REFERENCES `selected_treatments` (`selected_treatment_id`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`),
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`);

--
-- Constraints for table `selected_services`
--
ALTER TABLE `selected_services`
  ADD CONSTRAINT `selected_services_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `selected_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `selected_treatments`
--
ALTER TABLE `selected_treatments`
  ADD CONSTRAINT `selected_treatments_ibfk_1` FOREIGN KEY (`effective_service_id`) REFERENCES `selected_services` (`selected_service_id`),
  ADD CONSTRAINT `selected_treatments_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `selected_treatments_ibfk_3` FOREIGN KEY (`tooth_id`) REFERENCES `tooth` (`tooth_id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `user_account` (`user_id`);

--
-- Constraints for table `tooth`
--
ALTER TABLE `tooth`
  ADD CONSTRAINT `tooth_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`);

--
-- Constraints for table `tooth_images`
--
ALTER TABLE `tooth_images`
  ADD CONSTRAINT `tooth_images_ibfk_1` FOREIGN KEY (`selected_treatment_id`) REFERENCES `selected_treatments` (`selected_treatment_id`);

--
-- Constraints for table `tooth_records`
--
ALTER TABLE `tooth_records`
  ADD CONSTRAINT `tooth_records_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`);

--
-- Constraints for table `treatments`
--
ALTER TABLE `treatments`
  ADD CONSTRAINT `treatments_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `user_account` (`user_id`);

--
-- Constraints for table `user_access_modules`
--
ALTER TABLE `user_access_modules`
  ADD CONSTRAINT `user_access_modules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_account`
--
ALTER TABLE `user_account`
  ADD CONSTRAINT `user_account_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`),
  ADD CONSTRAINT `user_account_ibfk_2` FOREIGN KEY (`specialization_id`) REFERENCES `specialization` (`specialization_id`);

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_logs_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
