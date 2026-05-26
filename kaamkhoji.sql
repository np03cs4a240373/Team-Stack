-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 06:54 PM
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
-- Database: `kaamkhoji`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('applied','pending','reviewed','shortlisted','interview','accepted','rejected','withdrawn') NOT NULL DEFAULT 'applied',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resume_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `seeker_id`, `cover_letter`, `status`, `applied_at`, `resume_path`) VALUES
(1, 4, 3, 'asdfasdf', 'accepted', '2026-05-17 08:10:30', 'uploads/resumes/resume_3_4_1779005430.pdf'),
(2, 5, 3, 'fafadada', 'applied', '2026-05-18 05:52:45', NULL),
(3, 1, 3, '5839359##$##$%', 'applied', '2026-05-19 06:54:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_profiles`
--

CREATE TABLE `company_profiles` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `company` varchar(150) NOT NULL,
  `location` varchar(100) NOT NULL,
  `type` enum('full-time','part-time','remote','contract','internship') NOT NULL DEFAULT 'full-time',
  `experience_level` enum('any','entry','mid','senior') NOT NULL DEFAULT 'any',
  `salary` varchar(100) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `status` enum('active','closed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `salary_min` int(11) DEFAULT NULL,
  `salary_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `employer_id`, `title`, `company`, `location`, `type`, `experience_level`, `salary`, `deadline`, `description`, `requirements`, `status`, `created_at`, `is_deleted`, `deleted_at`, `salary_min`, `salary_max`) VALUES
(1, 2, 'PHP Developer', 'Tech Corp', 'Kathmandu', 'full-time', 'any', 'Rs. 40,000 - 60,000', NULL, 'We are looking for an experienced PHP developer to join our team. You will build and maintain web applications.', 'PHP, MySQL, HTML, CSS, JavaScript. 1+ year experience.', 'active', '2026-05-17 07:36:53', 0, NULL, NULL, NULL),
(2, 2, 'UI/UX Designer', 'Tech Corp', 'Remote', 'remote', 'any', 'Rs. 30,000 - 50,000', NULL, 'Looking for a creative UI/UX designer to create beautiful user interfaces for our products.', 'Figma, Adobe XD, basic HTML/CSS. Portfolio required.', 'active', '2026-05-17 07:36:53', 0, NULL, NULL, NULL),
(3, 2, 'Backend Intern', 'Tech Corp', 'Lalitpur', 'internship', 'any', 'Rs. 10,000 - 15,000', NULL, 'Internship opportunity for fresh graduates. Learn real-world backend development.', 'Python or PHP basics. Currently enrolled in CS or IT program.', 'active', '2026-05-17 07:36:53', 0, NULL, NULL, NULL),
(4, 2, 'Video Editor', 'BuluRed', 'Balkhu', 'part-time', 'mid', '40000 - 50000', '2026-05-18', 'We are looking for a skilled Video Editor to join BuluRed in Balkhu.\r\n\r\nAs a Video Editor, you will play a key role in our team, contributing to projects and initiatives that drive our organization forward. This is an excellent opportunity for a motivated professional to grow their career in a dynamic environment.\r\n\r\nKey Responsibilities:\r\n• Perform core duties related to the Video Editor role\r\n• Collaborate with cross-functional teams to achieve goals\r\n• Contribute ideas and solutions to improve processes\r\n• Maintain high standards of quality in all deliverables\r\n• Report progress and updates to the management team', '• Relevant educational background or equivalent experience\r\n• Proven experience in a similar Video Editor role\r\n• Strong communication and teamwork skills\r\n• Ability to work independently and meet deadlines\r\n• Proficiency in relevant tools and technologies\r\n• Positive attitude and eagerness to learn', 'active', '2026-05-17 08:07:48', 0, NULL, NULL, NULL),
(5, 2, 'english teacher', 'school', 'Kathmandu', 'full-time', 'mid', 'Rs. 40,000 – 50,000', '2026-05-20', 'We are looking for a skilled english teacher to join school in Kathmandu.\r\n\r\nAs a english teacher, you will play a key role in our team, contributing to projects and initiatives that drive our organization forward. This is an excellent opportunity for a motivated professional to grow their career in a dynamic environment.\r\n\r\nKey Responsibilities:\r\n• Perform core duties related to the english teacher role\r\n• Collaborate with cross-functional teams to achieve goals\r\n• Contribute ideas and solutions to improve processes\r\n• Maintain high standards of quality in all deliverables\r\n• Report progress and updates to the management team', '• Relevant educational background or equivalent experience\r\n• Proven experience in a similar english teacher role\r\n• Strong communication and teamwork skills\r\n• Ability to work independently and meet deadlines\r\n• Proficiency in relevant tools and technologies\r\n• Positive attitude and eagerness to learn', 'active', '2026-05-18 05:50:17', 0, NULL, 40000, 50000);

-- --------------------------------------------------------

--
-- Table structure for table `jobseeker_profiles`
--

CREATE TABLE `jobseeker_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skills` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cv_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(1, 'nijal.mhrzn@gmail.com', 'ff1da8e88fa9411ba5e3585ae57ed5659b018688d8c399cb7cbe98d0dc5daaf4', '2026-05-18 05:13:39', '2026-05-18 02:13:39');

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saved_jobs`
--

INSERT INTO `saved_jobs` (`id`, `seeker_id`, `job_id`, `saved_at`) VALUES
(1, 3, 3, '2026-05-17 08:02:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('seeker','employer','admin') NOT NULL DEFAULT 'seeker',
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `location`, `bio`, `is_active`, `created_at`) VALUES
(1, 'Admin', 'admin@kaamkhoji.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '', '', '', 1, '2026-05-17 07:36:53'),
(2, 'Tech Corp HR', 'employer@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', '9776105454', 'Kathmandu', 'fafafafad', 1, '2026-05-17 07:36:53'),
(3, 'Ram Sharma', 'seeker@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', '', 'Kathmandu', '', 1, '2026-05-17 07:36:53'),
(4, 'test', 'test@gmail.com', '$2y$10$6u5Re658mOTExAPpMpwsiu5u85j/F6KzG9/QLoaMQse5RPbvBEq.C', 'seeker', NULL, NULL, NULL, 1, '2026-05-18 02:07:03'),
(5, 'nijal', 'nijal.mhrzn@gmail.com', '$2y$10$1yPYjClp3/b7NDmj.6a.i.ZkDG1fRD0YG0IJAt6CGgD/TCUbeagj.', 'seeker', '', '', '', 1, '2026-05-18 02:13:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_application` (`job_id`,`seeker_id`),
  ADD KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employer_id` (`employer_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- Indexes for table `jobseeker_profiles`
--
ALTER TABLE `jobseeker_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_saved` (`seeker_id`,`job_id`),
  ADD KEY `job_id` (`job_id`);

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
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `company_profiles`
--
ALTER TABLE `company_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobseeker_profiles`
--
ALTER TABLE `jobseeker_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`seeker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD CONSTRAINT `company_profiles_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobseeker_profiles`
--
ALTER TABLE `jobseeker_profiles`
  ADD CONSTRAINT `jobseeker_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `saved_jobs_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_jobs_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
