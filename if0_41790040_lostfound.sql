-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql308.infinityfree.com
-- Generation Time: Jun 18, 2026 at 06:00 AM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41790040_lostfound`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `product_id`, `sender_name`, `message`, `created_at`) VALUES
(3, 9, 'Sakib Khan', 'this wallet is main', '2026-05-17 15:42:58'),
(4, 9, 'Sakib Khan', 'CLAIM REQUEST — Name: Sakib Khan | Contact: 010101010101 | Note: im the real owner', '2026-05-20 10:03:38'),
(5, 20, 'mr. WOW', 'oii ami paesi tomar jenis \r\n', '2026-05-20 12:54:02'),
(6, 9, 'mahnoor islam', 'CLAIM REQUEST â€” Name: mahnoor islam | Contact: 01580617125 | Note: ', '2026-05-27 19:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `Id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `question` varchar(255) DEFAULT NULL,
  `answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`Id`, `user_id`, `name`, `description`, `image`, `type`, `location`, `contact`, `question`, `answer`) VALUES
(9, '4', 'Wallet', 'Location: ENT Lab\r\n\r\nA black leather wallet was found inside the ENT Lab classroom after a practical session. The wallet appears to be slightly worn and contains some cash, a student ID card, and a few small personal papers. No clear owner name is visible on the outside. The item was handed over by a student to the lab assistant immediately after discovery. Anyone claiming this wallet must provide proper identification details and describe its contents accurately for verification. For contact, please call 01745-882193 or 01988-334567. The wallet is safely kept in the lab office for further collection. Please collect it within official hours from the ENT Lab supervisor.', 'wallet.jpg', 'Found', '', '', 'how much money ???', '500'),
(10, '5', 'Smartphone', 'Location: Soft1 Lab\r\n\r\nA modern Android smartphone with a black back cover was lost in Soft1 Lab after a programming class. The phone was discovered on a desk, likely left behind by a student in a hurry. It is password locked, and no SIM details are currently visible. The device is in good condition with minor scratches on the screen. It has been submitted to the lab in-charge for safekeeping. The owner is requested to contact with proof of ownership and correct phone details. For verification, call 01823-445901 or 01672-990112. ', 'phone.jpg', 'Lost', 'qw', 'qw', 'qw', 'qw'),
(11, '4', 'book', 'A textbook titled “Data Communication” was found in the Printing Lab near the printer section. The book contains highlighted notes and handwritten markings, indicating it is actively used by a student. It was likely forgotten after completing a printing assignment or class work. The book is in decent condition with a light blue cover. It has been safely kept by the lab supervisor. The owner is advised to collect it soon. For confirmation, contact 01819-556677 or 01790-112233 and provide details about the notes inside the book for verification.', 'book.jpeg', 'Lost', 'Printing Lab', '01213424524', 'there is a name in the front page , Tell me the name ?? ', 'kafi'),
(21, '18', 'ID Card', 'A student ID card was found near the main gate of the campus during morning entry hours. The card belongs to a diploma student and includes a clear photo, but the name is partially faded due to wear. It was collected by the security guard and submitted to the administration office. The card is essential for campus access, so the owner is advised to collect it as soon as possible. Please contact 01912-778899 or 01733-445566 and provide correct student information for verification before receiving the card.', 'WhatsApp Image 2026-05-21 at 5.58.17 PM.jpeg', 'Found', 'Main Gate of Campus', ' 01912-778899 or 01733-445566', 'Roll Number ????', '847963');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(14) NOT NULL,
  `password` varchar(255) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `name`, `email`, `phone`, `password`, `google_id`, `role`, `status`) VALUES
(4, 'Farhan Islam Rafid', 'admin@gmail.com', '01533477264', '$2y$10$dC5QBeszPnIFvVj2qJzo8Obsc3goPQkKRe5D45F3sXKA3QVMuXlvG', '', 'admin', 'Active'),
(5, 'Sakib Khan', 'sakib@gmail.com', '01234387480', '$2y$10$f80UY.iQQAE7iy9jAKYvhuASlUrR76XjwJ5vSz.oR9qghRbddaGN2', '', '', 'Active'),
(7, 'Farhan Islam Rafid', 'rrr@gmail.com', '010100201212', '$2y$10$bs2FIsGRSgitEeBXXpGaHujl0/X0e81BudW4UYjz9p0DST6HIK/B2', '', '', 'Active'),
(8, 'Farhan Islam Rafid', 'rafid@gmail.com', '01533477264', '$2y$10$bK5tYq.1Zv1FBg.i.JxOmulCGbxooI9I919rvTXEJBB3.BCaLcUFi', '', '', 'Active'),
(10, 'Herospath', 'herospath@gmail.com', '01223322322', '$2y$10$ZFgRm7xwvf7hDfd7kjlk..odZ/sJFRut2hYXTH5Q/YYu2acxeubtS', '', '', 'Active'),
(11, 'Herospath', 'herospath@gmail.com', '01223322322', '$2y$10$GgPgUaeGWJXWnqO.0EiNreuYg51hn07MmmIznElGiTjTFQpCnHK86', '', '', 'Active'),
(12, 'Herospath', 'herospath@gmail.com', '01223322322', '$2y$10$YtiXNMdi1fOyUVlvM3qcGem5xfgoNbjuksHlU7pDfVbezH.V2mMwO', '', '', 'Active'),
(13, 'Ruzzel ', 'ruzzelcamara88@gmail.com', '09267524814', '$2y$10$n5REBOmKty924hmJbAdcjOHbdw65kFet./A/44UyBV47NVI5JFtwO', '', '', 'Active'),
(14, 'Sobur ali', 'sobur@gmail.com', '928388', '$2y$10$O/5hQDZe6iv2SIMU.Er/tuQ3SzzWEIDuxlaAErDcWtH2yHKhJ9evK', '', '', 'Active'),
(15, 'Supercalifragilisticexpialidocious ', 'sudosu@gmail.com', '01875022222', '$2y$10$N28Khmil/uUAygp6C1iPdeEVle4B0PpQtfVtYEZm17kPiX2TiDg4C', '', '', 'Active'),
(16, 'Farabi ', 'Farabi@gmail.com', '014533477264', '$2y$10$P5HUBNQ4aBGjuiQYZM.pSejcT0HYI.0z7zq/s0Q6boiK6P0ottlZC', '', '', 'Active'),
(17, 'El Nadi', 'elnadi@gmail.com', '09632594265', '$2y$10$d4IM3CaOVAvayuLPsd6VuOBMDY14jveihYKbZUMv7CsSQX5SeDnEK', '', '', 'Active'),
(18, 'mr. WOW', 'wow@gmail.com', '7777777777', '$2y$10$6sxjomZyqvim4AwKeb1v0eEEd/g5Xu3gGpAy.gafGr9jqgUyQsPAu', '', '', 'Active'),
(20, 'Hill Kafi', 'Kafi@gmail.com', '01554684841', '$2y$10$lhuxUtoeyLSVLDnZYepmbeZdNx4zVgl9XCl1tRwjCrgUDq8dfHefm', '', '', 'Active'),
(21, 'Mr. J ', 'jokar@gmail.com', '777777772377', '$2y$10$vODmEQgKjcqHGk.qZNTf.OyN.f30GJWQJYM2L/1H0lVcHZwlm2twO', '', '', 'Banned');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
