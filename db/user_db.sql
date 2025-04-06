-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 02:45 AM
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
-- Database: `user_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `application_requests`
--

CREATE TABLE `application_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_purpose` varchar(100) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_age` int(3) NOT NULL,
  `client_gender` varchar(20) NOT NULL,
  `client_civil_status` varchar(50) NOT NULL,
  `client_birthday` date NOT NULL,
  `client_birthplace` varchar(255) NOT NULL,
  `client_education` varchar(100) NOT NULL,
  `client_address` text NOT NULL,
  `application_date` date NOT NULL,
  `request_type` varchar(100) NOT NULL,
  `patient_name` varchar(255) DEFAULT NULL,
  `relation_to_patient` varchar(50) DEFAULT NULL,
  `relation_other` varchar(100) DEFAULT NULL,
  `patient_birthday` date DEFAULT NULL,
  `patient_age` int(3) DEFAULT NULL,
  `patient_gender` varchar(20) DEFAULT NULL,
  `patient_civil_status` varchar(50) DEFAULT NULL,
  `patient_birthplace` varchar(255) DEFAULT NULL,
  `patient_education` varchar(100) DEFAULT NULL,
  `patient_occupation` varchar(255) DEFAULT NULL,
  `patient_religion` varchar(100) DEFAULT NULL,
  `patient_address` text DEFAULT NULL,
  `same_as_client_address` tinyint(1) DEFAULT 0,
  `amount` decimal(10,2) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `id_type` varchar(100) DEFAULT NULL,
  `id_file_path` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `viewed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_requests`
--

INSERT INTO `application_requests` (`id`, `user_id`, `request_purpose`, `client_name`, `client_age`, `client_gender`, `client_civil_status`, `client_birthday`, `client_birthplace`, `client_education`, `client_address`, `application_date`, `request_type`, `patient_name`, `relation_to_patient`, `relation_other`, `patient_birthday`, `patient_age`, `patient_gender`, `patient_civil_status`, `patient_birthplace`, `patient_education`, `patient_occupation`, `patient_religion`, `patient_address`, `same_as_client_address`, `amount`, `diagnosis`, `id_type`, `id_file_path`, `status`, `created_at`, `updated_at`, `viewed`) VALUES
(3, 13, 'Medical Assistance', 'Juan Tamad', 21, 'Male', 'Widowed', '2025-03-03', 'Dine', 'Vocational', 'Bulaklakan Gloria Oriental Mindoro', '2025-03-29', 'Medical Assistance', 'Lebron', 'Parent', '', '2025-03-05', 35, 'Male', 'Married', 'Dine', 'College', 'N/A', 'Catholic', 'Bulaklakan Gloria', 0, 30000.00, 'N/A', '', '', 'Pending', '2025-03-29 05:02:59', '2025-04-04 08:26:26', 1),
(4, 10, 'Financial Assistance', 'Lebron James', 21, 'Male', 'Single', '2025-03-13', 'Purok tres', 'College', 'Bulaklakan Gloria Oriental Mindoro', '2025-03-29', 'Financial Assistance', 'Lebron', 'Parent', '', '2025-03-18', 35, 'Male', 'Married', 'Dine', 'Post Graduate', 'N/A', 'Catholic', 'Bulaklakan Gloria', 0, 5000.00, 'N/A', '', '', 'Approved', '2025-03-29 07:46:05', '2025-04-01 01:03:29', 0),
(5, 10, 'Medical Assistance', 'Steph Curry', 37, 'Male', 'Married', '2025-03-11', 'Purok tres', 'College', 'Bulaklakan Gloria Oriental Mindoro', '2025-04-01', 'Medical Assistance', 'awdada', 'Child', '', '2025-03-26', 23, 'Male', 'Single', 'Dine', 'College', 'N/A', 'Catholic', 'Bulaklakan Gloria', 0, 10000.00, 'N/A', '', '', 'Pending', '2025-04-01 01:14:34', '2025-04-04 07:17:59', 0),
(6, 13, 'Financial Assistance', 'Alex', 21, 'Male', 'Single', '2025-04-04', 'Purok tres', 'College', 'Bulaklakan Gloria Oriental Mindoro', '2025-04-23', 'Financial Assistance', 'gegeg', 'Parent', '', '2025-04-08', 32, 'Male', 'Married', 'Dine', 'Post Graduate', 'N/A', 'Catholic', 'Bulaklakan Gloria', 0, 75000.00, 'N/A', '', '', 'Pending', '2025-04-04 08:23:25', '2025-04-04 08:26:13', 0),
(7, 14, 'Financial Assistance', 'Alex', 34, 'Male', 'Single', '2025-04-03', 'Purok tres', 'College', 'Bulaklakan Gloria Oriental Mindoro', '2025-04-04', 'Financial Assistance', 'Alex', 'Self', '', '2025-04-03', 32, 'Male', 'Single', 'Dine', 'College', 'N/A', 'Catholic', 'Bulaklakan Gloria', 0, 0.00, '', '', '', 'Success', '2025-04-04 08:51:11', '2025-04-04 09:17:03', 0),
(8, 15, 'Financial Assistance', 'Gerald Villaruel', 21, 'Male', 'Single', '2025-03-31', 'Purok tres', 'College', 'Malamig Gloria Oriental Mindoro', '2025-04-01', 'Financial Assistance', 'Gerald', 'Self', '', '2025-03-30', 21, 'Male', 'Single', 'purok tres', 'College', 'N/A', 'Catholic', '', 1, 10000.00, 'N/A', '', '', 'Pending', '2025-04-04 09:09:23', '2025-04-04 09:09:23', 0),
(9, 16, 'Financial Assistance', 'Alex', 21, 'Male', 'Single', '2025-04-01', 'Purok tres', 'College', 'Bulaklakan Gloria Oriental Mindoro', '2025-04-05', 'Financial Assistance', 'Alex', 'Self', '', '2025-04-01', 21, 'Male', 'Single', 'purok tres', 'College', 'N/A', 'Catholic', 'Bulaklakan Gloria', 0, 7500.00, 'N/A', '', '', 'Success', '2025-04-05 00:37:34', '2025-04-05 00:44:02', 0);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact` varchar(25) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `address`, `contact`, `email`) VALUES
(1, 'gerald', 'doon', '321331', 'geraldvillaruel166@gmail.com'),
(2, 'Juan Tamad', 'Gegeg', '09525244109', 'akosijuan@gmail.com'),
(3, 'Lebron James', 'Bulaklakan Gloria Oriental Mindoro', '09525244109', 'Lebron@gmail.com'),
(5, 'Lebron James', 'Bulaklakan Gloria Oriental Mindoro', '09525244109', 'Lebron@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notification_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'upcoming',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `title`, `description`, `category`, `date`, `time`, `location`, `image`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 'try', 'fwfwdaw', 'Health', '2025-04-10', '', 'brgy kawit hall', NULL, 'upcoming', 10, '2025-03-29 06:57:34', '2025-03-29 06:57:34'),
(9, 'Disaster Risk', 'gegege', 'Disaster Response', '2025-03-29', '', 'brgy bulaklakan hall', NULL, 'completed', 10, '2025-03-29 07:23:50', '2025-03-29 07:24:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_form`
--

CREATE TABLE `user_form` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_form`
--

INSERT INTO `user_form` (`id`, `name`, `email`, `password`, `user_type`) VALUES
(10, 'doms', 'doms@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'user'),
(13, 'Alexander Lamboloto', 'alex@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'user'),
(14, 'Alexander Siasat', 'alexandersiasatmain@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'user'),
(15, 'Gerald Villaruel', 'geraldvillaruel12345@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'user'),
(16, 'Alex', 'lekzandergaming@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_requests`
--
ALTER TABLE `application_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_form`
--
ALTER TABLE `user_form`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_requests`
--
ALTER TABLE `application_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_form`
--
ALTER TABLE `user_form`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_requests`
--
ALTER TABLE `application_requests`
  ADD CONSTRAINT `application_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_form` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user_form` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
