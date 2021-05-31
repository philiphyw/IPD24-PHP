-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3333
-- Generation Time: May 25, 2021 at 10:41 PM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 7.4.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz1slimshout`
--

-- --------------------------------------------------------

--
-- Table structure for table `shouts`
--

CREATE TABLE `shouts` (
  `id` int(11) NOT NULL,
  `authorID` int(11) NOT NULL,
  `message` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `shouts`
--

INSERT INTO `shouts` (`id`, `authorID`, `message`) VALUES
(1, 9, '<p>Hello~~this is my first <strong>shout</strong></p>'),
(2, 9, '<p>1</p>'),
(3, 11, '<p>Hey guys, I\'m candy123~~\'s up</p>'),
(4, 12, '<p>Hey guys. anyone want to play counter-strike:GO?</p>'),
(5, 14, '<p>Hey guys, I\'m Eddie~~~</p>'),
(6, 14, '<p>Hey, it\'s Eddie again, I want to play basketball this afternoon. anyone in?</p>');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `imagePath` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `imagePath`) VALUES
(9, 'addie123', '$2y$10$TsB4aAEKyHWLwJ.3CuV1MexBUrbWaHhkzLe270CeO0at0grdVamWi', 'addie123.png'),
(10, 'betty123', '$2y$10$LpAODjR4xFqsAoi8MBhWzef9H8kit1cb01s9V8k0E8ooGUrykFanK', 'betty123.jpg'),
(11, 'candy123', '$2y$10$wjHnx/SHcAqi1r3UNqOl3.x1bBgSeEktVqGKq90.1FiSU/.jX6UZe', 'candy123.jpg'),
(12, 'zack99999', '$2y$10$tPTLSfzy6KcDZkrZ0YQnMek1O.xICXoAzx8yo3cNVe1EZ76lMi4VK', 'zack99999.png'),
(13, 'delia123', '$2y$10$UM1zTIRlRWcgtOdLaW/i.uV/I1xy8R1jG2Qxtlnvah5CKPKeFASZm', 'delia123.png'),
(14, 'eddie96', '$2y$10$BCHsE47mklT.YxhArdvt2OSY00hs0JIcrhYUX.pMJfC05xe8sxjCS', 'eddie96.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shouts`
--
ALTER TABLE `shouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `authorID` (`authorID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shouts`
--
ALTER TABLE `shouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `shouts`
--
ALTER TABLE `shouts`
  ADD CONSTRAINT `shouts_ibfk_1` FOREIGN KEY (`authorID`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
