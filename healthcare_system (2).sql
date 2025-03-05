-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 18, 2024 at 09:11 PM
-- Server version: 8.0.39
-- PHP Version: 8.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `healthcare_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_date` datetime NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doctor_note` text COLLATE utf8mb4_general_ci,
  `doctor_note_submitted` tinyint(1) DEFAULT '0',
  `prescription_submitted` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `status`, `created_at`, `department`, `doctor_note`, `doctor_note_submitted`, `prescription_submitted`) VALUES
(1, 1, 1, '2024-09-07 00:00:00', 'Completed', '2024-09-06 18:12:08', NULL, NULL, 0, 0),
(4, 1, 1, '2024-09-08 01:15:00', 'Completed', '2024-09-06 18:42:19', NULL, NULL, 0, 0),
(5, 1, 2, '2024-09-08 01:15:00', 'Completed', '2024-09-06 18:42:37', NULL, NULL, 0, 0),
(6, 1, 1, '2024-09-19 21:37:00', 'Completed', '2024-09-18 16:07:31', NULL, NULL, 0, 0),
(7, 1, 2, '2024-09-19 00:45:00', 'Completed', '2024-09-18 16:10:42', NULL, NULL, 0, 0),
(8, 1, 1, '2024-09-19 21:51:00', 'pending', '2024-09-18 16:21:22', NULL, NULL, 0, 0),
(9, 1, 2, '2024-09-19 22:45:00', 'Completed', '2024-09-18 17:15:15', NULL, NULL, 0, 0),
(10, 1, 2, '2024-09-19 01:10:00', 'Completed', '2024-09-18 17:39:42', NULL, NULL, 0, 0),
(11, 1, 2, '2024-09-20 05:00:00', 'Completed', '2024-09-18 20:20:11', NULL, NULL, 0, 0),
(12, 1, 2, '2024-09-22 05:55:00', 'Completed', '2024-09-18 20:21:34', NULL, NULL, 0, 0),
(13, 1, 2, '2024-09-30 07:00:00', 'Completed', '2024-09-18 20:25:04', NULL, 'sdfgsdfg', 1, 0),
(14, 3, 4, '2024-09-20 06:26:00', 'pending', '2024-09-18 20:53:34', NULL, NULL, 0, 0),
(15, 1, 4, '2024-09-20 06:45:00', 'pending', '2024-09-18 21:10:54', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int NOT NULL,
  `prescription_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `pharmacist_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `prescription_id`, `patient_id`, `pharmacist_id`, `total_amount`, `created_at`) VALUES
(1, 1, 1, 987654, 200.00, '2024-09-18 19:32:53'),
(2, 1, 1, 1234567890, 50.00, '2024-09-18 20:10:05'),
(3, 1, 1, 987654, 20.00, '2024-09-18 20:11:22'),
(4, 1, 1, 987654, 20.00, '2024-09-18 20:12:26'),
(5, 1, 1, 987654, 40.00, '2024-09-18 20:13:02'),
(6, 3, 1, 987654, 60.00, '2024-09-18 21:10:28');

-- --------------------------------------------------------

--
-- Table structure for table `bill_items`
--

CREATE TABLE `bill_items` (
  `id` int NOT NULL,
  `bill_id` int NOT NULL,
  `medication_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill_items`
--

INSERT INTO `bill_items` (`id`, `bill_id`, `medication_name`, `quantity`, `price`) VALUES
(1, 1, 'dsfdsaf', 1, 200.00),
(2, 2, 'gfhdgfdh', 1, 50.00),
(3, 3, 'gfhdgfdh', 1, 20.00),
(4, 4, 'gfhdgfdh', 1, 20.00),
(5, 5, 'adsfsdaf', 2, 20.00),
(6, 6, 'sdaf', 1, 20.00),
(7, 6, 'adsfsdaf', 2, 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `specialty` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hospital_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `full_name`, `department`, `specialty`, `hospital_id`, `created_at`) VALUES
(1, 1, 'doc2', 'Neurology', 'Neurology', NULL, '2024-09-06 18:08:46'),
(2, 2, 'doc1', 'Cardiology', 'brain', NULL, '2024-09-06 18:09:08'),
(3, 1234567891, 'PRATIK KALIDAS CHAVAN', 'Cardiology', 'dsaf', NULL, '2024-09-18 20:50:39'),
(4, 1234567892, 'pratik_c16', 'Cardiology', 'fdsgsdf', NULL, '2024-09-18 20:51:30'),
(5, 1234567893, 'PRATIK KALIDAS CHAVAN', 'Cardiology', 'asdfds', NULL, '2024-09-18 20:52:43');

-- --------------------------------------------------------

--
-- Table structure for table `lab_orders`
--

CREATE TABLE `lab_orders` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `lab_id` int NOT NULL,
  `test_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_histories`
--

CREATE TABLE `medical_histories` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `record` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('appointment','test_result','billing','general') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `medical_history` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `prescription` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `full_name`, `date_of_birth`, `created_at`, `medical_history`, `prescription`, `phone`, `city`) VALUES
(1, 6, 'Pratik Chavan', '2024-09-01', '2024-09-06 18:11:21', '\n\n2024-09-06 - Dr. :\nHe is all fine\n\n2024-09-06 - Dr. :\nHe is ill, he has fever and he has gone crazyy\n\n2024-09-06 - Dr. :\nhe is well\n\n2024-09-18 - Dr. :\ndsafsdaf\n\n2024-09-18 - Dr. :\nfcxvbxcv\n\n2024-09-18 - Dr. :\nsdafdsaf\n\n2024-09-18 - Dr. :\nadsf', NULL, '7558434111', 'Pune'),
(2, 7, 'amisha', '2024-09-02', '2024-09-06 18:11:48', NULL, NULL, '9766860730', 'Nashik'),
(3, 1234567894, 'Pratik Chavan', '2024-09-20', '2024-09-18 20:53:11', NULL, NULL, '+919766860730', 'Pune');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacists`
--

CREATE TABLE `pharmacists` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacists`
--

INSERT INTO `pharmacists` (`id`, `user_id`, `full_name`, `phone`, `email`) VALUES
(987654, 1234567890, 'Pharmacy', '7558434111', 'pharma@gmail.com'),
(987654321, 987654, 'Pharmacy', '7558434111', 'admin@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `typed_prescription` text COLLATE utf8mb4_general_ci,
  `prescription_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `appointment_id`, `patient_id`, `doctor_id`, `typed_prescription`, `prescription_image`, `created_at`) VALUES
(1, 9, 1, 2, 'gfhdgfdh', NULL, '2024-09-18 17:34:05'),
(2, 11, 1, 2, 'afsd', NULL, '2024-09-18 20:21:09'),
(3, 13, 1, 2, 'fdsgdsfg', NULL, '2024-09-18 20:29:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'doc2', 'doc2@gmail.com', '$2y$10$SfS3GYpRFB3JwfLqVOm.Veod3TRLoQLUqdlBPtybN2ItTyUaiuJ6u', 'doctor', '2024-09-06 18:08:46'),
(2, 'doc1', 'doc1@gmail.com', '$2y$10$YvrRNXRAu5s/lwoDyUG/YOVPKfsSsttYWS3la95wJTvYlVzspkmei', 'doctor', '2024-09-06 18:09:08'),
(3, 'pat1', 'pat1@gmail.com', '$2y$10$KQapnCshL0AB5/x/tPFxBORmIyh4PyyzJgtoqJdLI9M0TEvPrkY/G', 'patient', '2024-09-06 18:09:24'),
(5, 'pat2', 'pat2@gmail.com', '$2y$10$mIWMU5w4lQwujbqbbOivr.tPviMo5rkzjF3MoyidM3PMrpu7gIh3u', 'patient', '2024-09-06 18:10:45'),
(6, 'Pratik Chavan', 'prateek203203@gmail.com', '$2y$10$bX977yzPxnXVcfc2w9kVlO6UD2QEqZJkHnogGK7v9ygDSt8pxLoka', 'patient', '2024-09-06 18:11:21'),
(7, 'amisha', 'amisha@gmail.com', '$2y$10$5GB7e5cay9fTXTuLK9.rHOcDD/fOL/FMfBpov3GSA5e0/KXrALXPC', 'patient', '2024-09-06 18:11:48'),
(987654, 'Pharmacy', 'admin@gmail.com', '$2y$10$pF4Xmr7pVNM43WlfffaAjeY/vmVxJux08nQ9EsNcm96FOWG2CjkI6', 'pharmacist', '2024-09-18 18:24:09'),
(1234567890, 'Pharmacy', 'pharma@gmail.com', '$2y$10$rQ6/mV94CTTI67EsL349PewCkkb3DYsKfIjx1ZmO2iLqkxo0BIh1a', 'pharmacist', '2024-09-18 19:54:14'),
(1234567891, 'PRATIK KALIDAS CHAVAN', 'doc5@gmail.com', '$2y$10$9xXF.IVNT4sYac.Zue/77u4imW4VCRhs1qysERC5j0qe3NldHndP6', 'doctor', '2024-09-18 20:50:39'),
(1234567892, 'pratik_c16', 'chavanpratik412@gmail.com', '$2y$10$EuG6c/W.XuRZ35LtztSDqejWUW3e1ugPH/FaYK4Sw/28CnnewYdam', 'doctor', '2024-09-18 20:51:30'),
(1234567893, 'PRATIK KALIDAS CHAVAN', 'doc7@gmail.com', '$2y$10$uOzF8959vs3mXJAslGP7buMCUOaeE1mdOAJxRA0Rj.sIVzvXGWu8C', 'doctor', '2024-09-18 20:52:43'),
(1234567894, 'Pratik Chavan', 'pat11@gmail.com', '$2y$10$wCxqF2c41Oci9/A.Pr0vCOpJ0VLsfYV9VkTd8z2/vBTJnkjc6G1Mq', 'patient', '2024-09-18 20:53:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_id` (`prescription_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `pharmacist_id` (`pharmacist_id`);

--
-- Indexes for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lab_orders`
--
ALTER TABLE `lab_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `medical_histories`
--
ALTER TABLE `medical_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pharmacists`
--
ALTER TABLE `pharmacists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lab_orders`
--
ALTER TABLE `lab_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_histories`
--
ALTER TABLE `medical_histories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pharmacists`
--
ALTER TABLE `pharmacists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=987654322;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1234567895;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`),
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD CONSTRAINT `bill_items_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lab_orders`
--
ALTER TABLE `lab_orders`
  ADD CONSTRAINT `lab_orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_orders_ibfk_2` FOREIGN KEY (`lab_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_histories`
--
ALTER TABLE `medical_histories`
  ADD CONSTRAINT `medical_histories_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pharmacists`
--
ALTER TABLE `pharmacists`
  ADD CONSTRAINT `pharmacists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
