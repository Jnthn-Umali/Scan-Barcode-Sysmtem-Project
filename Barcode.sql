-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2025 at 01:06 PM
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
-- Database: `fantastic`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `usertype` varchar(20) NOT NULL,
  `userstatus` varchar(20) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblaccounts`
--

INSERT INTO `tblaccounts` (`username`, `password`, `usertype`, `userstatus`, `createdby`, `datecreated`, `email`) VALUES
('123', '1234', 'STUDENT', 'ACTIVE', 'admin', '03/13/2025', '123@g'),
('2200123', '1234', 'STUDENT', 'ACTIVE', 'admin', '03/13/2025', '123@g'),
('2201214', '123', 'STUDENT', 'ACTIVE', 'admin', '03/13/2025', '123@g'),
('54321', '1234', 'STUDENT', 'ACTIVE', 'admin', '03/13/2025', '123@g'),
('admin', '1234', 'ADMINISTRATOR', 'ACTIVE', 'admin', '11/03/2025', 'admin@gmail.om'),
('allyn', '1234', 'SECURITY', 'ACTIVE', 'admin', '03/13/2025', '1234@gmail.om'),
('jona', '123', 'ADMINISTRATOR', 'ACTIVE', 'admin', '03/13/2025', 'jonathanumali21@yahoo.com'),
('title', '1234', 'ADMINISTRATOR', 'ACTIVE', 'admin', '13/03/2025', 'admin@gmail.om');

-- --------------------------------------------------------

--
-- Table structure for table `tbllogs`
--

CREATE TABLE `tbllogs` (
  `datelog` varchar(20) NOT NULL,
  `timelog` varchar(20) NOT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(20) NOT NULL,
  `performedby` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbllogs`
--

INSERT INTO `tbllogs` (`datelog`, `timelog`, `action`, `module`, `performedby`) VALUES
('2025-03-13', '20:00:39', 'Scanned Student - 2200123', 'Barcode Scanner', 'Security');

-- --------------------------------------------------------

--
-- Table structure for table `tblstudents`
--

CREATE TABLE `tblstudents` (
  `studentnumber` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `campus` varchar(50) NOT NULL,
  `yearlevel` varchar(20) NOT NULL,
  `grade` varchar(20) NOT NULL,
  `yearenrolled` varchar(10) NOT NULL,
  `age` varchar(10) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `address` varchar(50) NOT NULL,
  `emailstudent` varchar(50) NOT NULL,
  `emailguardian` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblstudents`
--

INSERT INTO `tblstudents` (`studentnumber`, `name`, `campus`, `yearlevel`, `grade`, `yearenrolled`, `age`, `gender`, `address`, `emailstudent`, `emailguardian`) VALUES
('123', 'me', 'a', 'a', 'a', '123', '123', 'a', '123', '123@g', 'quecolim@gmail.com'),
('2200123', 'jonathan', 'a', 'a', 'a', '02/25/2025', '21', 'b', '123', '123@g', 'quecolim@gmail.com'),
('2201214', '123', 'a', 'a', 'a', '123', '123', 'a', '123', '123@g', '213@g'),
('54321', 'Allyn Ullanday', 'a', 'a', 'a', '21', '12', 'a', '21', '123@g', 'aujsculandayallyn@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `tblstudents`
--
ALTER TABLE `tblstudents`
  ADD PRIMARY KEY (`studentnumber`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
