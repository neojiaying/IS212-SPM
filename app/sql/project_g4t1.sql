-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 07, 2019 at 06:55 AM
-- Server version: 5.7.23
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+08:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_g4t1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(5) NOT NULL,
  `password` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bid`
--

DROP TABLE IF EXISTS `bid`;
CREATE TABLE IF NOT EXISTS `bid` (
  `userid` varchar(128) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `code` varchar(10) NOT NULL,
  `section` varchar(3) NOT NULL,
  `status` varchar(128) DEFAULT NULL,
  `round` int(11) NOT NULL,
  PRIMARY KEY (`userid`,`code`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE IF NOT EXISTS `course` (
  `course` varchar(10) NOT NULL,
  `school` varchar(128) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `examdate` varchar(8) NOT NULL,
  `examstart` varchar(8) NOT NULL,
  `examend` varchar(8) NOT NULL,
  PRIMARY KEY (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `course_completed`
--

DROP TABLE IF EXISTS `course_completed`;
CREATE TABLE IF NOT EXISTS `course_completed` (
  `userid` varchar(128) NOT NULL,
  `code` varchar(10) NOT NULL,
  PRIMARY KEY (`userid`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `minbid`
--

DROP TABLE IF EXISTS `minbid`;
CREATE TABLE IF NOT EXISTS `minbid` (
  `course` varchar(10) NOT NULL,
  `section` varchar(3) NOT NULL,
  `minbid` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prerequisite`
--

DROP TABLE IF EXISTS `prerequisite`;
CREATE TABLE IF NOT EXISTS `prerequisite` (
  `course` varchar(10) NOT NULL,
  `prerequisite` varchar(10) NOT NULL,
  PRIMARY KEY (`course`,`prerequisite`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `round`
--

DROP TABLE IF EXISTS `round`;
CREATE TABLE IF NOT EXISTS `round` (
  `round` int(11) NOT NULL,
  `status` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
CREATE TABLE IF NOT EXISTS `section` (
  `course` varchar(10) NOT NULL,
  `section` varchar(3) NOT NULL,
  `day` int(11) NOT NULL,
  `start` varchar(5) NOT NULL,
  `end` varchar(5) NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `venue` varchar(100) NOT NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`course`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `userid` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `name` varchar(100) NOT NULL,
  `school` varchar(128) NOT NULL,
  `edollar` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vacancy`
--

DROP TABLE IF EXISTS `vacancy`;
CREATE TABLE IF NOT EXISTS `vacancy` (
  `course` varchar(10) NOT NULL,
  `section` varchar(3) NOT NULL,
  `size` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
