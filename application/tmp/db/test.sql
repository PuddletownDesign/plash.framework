-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 10, 2011 at 06:05 AM
-- Server version: 5.1.44
-- PHP Version: 5.2.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ironarm.co`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `id` tinyint(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `text` longtext,
  `description` text,
  `url` varchar(200) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `author` tinyint(3) DEFAULT NULL,
  `tags` varchar(200) DEFAULT NULL,
  `category` tinyint(3) DEFAULT NULL,
  `commentcount` int(4) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `blog`
--


-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `email` varchar(200) NOT NULL,
  `text` longtext NOT NULL,
  `section` tinyint(4) NOT NULL,
  `post` tinyint(5) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `admin` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `post` (`post`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` VALUES(1, 'Brent Schneider', 'brent@puddletowndesign.com', '#THis is a dope comment\r\n\r\nThis is the first comment on the iron arm blog\r\n\r\n* and a little\r\n* bit\r\n* of\r\n*markdown\r\n\r\nand the closing statements of the mcomment', 2, 4, '2011-10-28 21:42:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(90) NOT NULL,
  `text` longtext,
  `description` text,
  `url` varchar(90) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` VALUES(4, 'login', 'Enter your username and password', '', 'login', '2011-11-27 00:06:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `text` text,
  `description` text,
  `url` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` VALUES(1, 'Home Page', 'This is the iron arm home page text', 'iron arm home page description', 'home');
INSERT INTO `sections` VALUES(2, 'Blog', 'This is the iron arm blog', 'THis is the iron arm blog description', 'blog');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` tinyint(5) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `name` varchar(70) NOT NULL,
  `password` varchar(47) NOT NULL,
  `priv` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` VALUES(1, 'brent', 'Brent Schneider', 'b31f3a50f382eb57bb48872dded2d8d69c97cf1b9a975df', 5);
INSERT INTO `users` VALUES(2, 'karli', 'Karolina Cengija', '830ee0120d69e1bf16c538ed95b93879c2d548cb8251551', 5);
