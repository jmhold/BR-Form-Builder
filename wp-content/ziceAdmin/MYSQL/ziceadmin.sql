-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- โฮสต์: localhost
-- เวลาในการสร้าง: 
-- รุ่นของเซิร์ฟเวอร์: 5.0.51
-- รุ่นของ PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- ฐานข้อมูล: `ziceadmin`
-- 

-- --------------------------------------------------------

-- 
-- โครงสร้างตาราง `01_albums`
-- 

CREATE TABLE `01_albums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(35) collate utf8_unicode_ci NOT NULL,
  `thumb` int(10) NOT NULL,
  `cnt` int(6) unsigned NOT NULL,
  `dt` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- 
-- dump ตาราง `01_albums`
-- 

INSERT INTO `01_albums` VALUES (1, '2012-04-19', 18, 20, '2012-04-19 10:54:55');
INSERT INTO `01_albums` VALUES (2, 'Thai Food', 25, 8, '2012-04-19 10:57:24');
INSERT INTO `01_albums` VALUES (3, 'wow', 29, 1, '2012-04-19 10:58:25');

-- --------------------------------------------------------

-- 
-- โครงสร้างตาราง `01_pics`
-- 

CREATE TABLE `01_pics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `albumid` int(10) NOT NULL,
  `filename` varchar(255) collate utf8_unicode_ci NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `dt` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `key_position` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `albumid` (`albumid`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

-- 
-- dump ตาราง `01_pics`
-- 

INSERT INTO `01_pics` VALUES (1, 1, 'Z8Q4CF1h5BjskfmWgtKoncyvrHzpD39u-0BasLA98QTjCeHzEiXv7uy3fl1VPcbxo.jpg', '01.jpg', '', '2012-04-19 10:56:18', 1);
INSERT INTO `01_pics` VALUES (2, 1, 'UB6vbEVer3RfkyPJ2KjhW58adHcNDLpm-ciq068KsrfVp2IwXZgB9oTvk3QAH4y5W.jpg', '02.jpg', '', '2012-04-19 10:56:18', 2);
INSERT INTO `01_pics` VALUES (3, 1, 'pGjF4obdKJWSmklHrA7yUsw3MtIuhRce-8dPoALMhKIlVr6Z2gWusYB0Cav5fUzGJ.jpg', '03.jpg', '', '2012-04-19 10:56:19', 3);
INSERT INTO `01_pics` VALUES (4, 1, 'lHW29jx1raVpGEXvZ7PqghesTk4ySt8R-6nRDAlqch4kYiNWKb5GxBIpV9JeMfo3E.jpg', '04.jpg', '', '2012-04-19 10:56:20', 4);
INSERT INTO `01_pics` VALUES (5, 1, 'witcVnMPCpQG8u1U6rm0lfINR3Fk4Tvs-GxjW2ktf8iDz0nJpKXrVNdv4ygLws3Aa.jpg', '05.jpg', '', '2012-04-19 10:56:21', 5);
INSERT INTO `01_pics` VALUES (6, 1, 'jCv13Qcp4LXudUPkKe2F09b8yB5or7WV-vumXaWVBLUYjcGo9Prs1Z672hfACbkiT.jpg', '06.jpg', '', '2012-04-19 10:56:21', 6);
INSERT INTO `01_pics` VALUES (7, 1, 'f3oXw2kdqvBZcJTyAELx6jHtK1QngeIP-qvHVMkQ7fAdwLhS48ts1ryNngGmP0IYa.jpg', '07.jpg', '', '2012-04-19 10:56:22', 7);
INSERT INTO `01_pics` VALUES (8, 1, 'NpDBmuYkFA17gzMnhJVGycS0qQC2teZP-mvYBUWfK0Nks7dZcD51QGMArazj4wox9.jpg', '08.jpg', '', '2012-04-19 10:56:23', 8);
INSERT INTO `01_pics` VALUES (9, 1, 'pj9dkQlIF5Bi3Uy1aK02TC4YbAvcgzN6-5etN2aPAjnD8pGf0ZS9H3FxTv7By6s4Y.jpg', '09.jpg', '', '2012-04-19 10:56:23', 9);
INSERT INTO `01_pics` VALUES (10, 1, 'vL7qyj5QAlD3pBxYbX1sMHztVndfPU6S-VsarnNhQWPAv62mDGXo1e5xS7F8KEJTl.jpg', '10.jpg', '', '2012-04-19 10:56:24', 10);
INSERT INTO `01_pics` VALUES (11, 1, 'zir1PpCcLbmDu8FGnxNEl34WIRHeXZdQ-b9Ax4aR3Ems7FdUIYopwnfckvPGCMiel.jpg', '11.jpg', '', '2012-04-19 10:56:25', 11);
INSERT INTO `01_pics` VALUES (12, 1, 'IaE09UpxulsY7dPTGSoeCrf864ZgyqWw-9uxWCKrcMbdV5Jn3zYatSsmoH7FjT2Ey.jpg', '12.jpg', '', '2012-04-19 10:56:26', 12);
INSERT INTO `01_pics` VALUES (13, 1, 'nG7BRuUTkLz2JD6bC4wi9XKlQ8gMhoFd-Fkq3nHK7WlpXNuCstQmR4S91EcVwALIy.jpg', '13.jpg', '', '2012-04-19 10:56:26', 14);
INSERT INTO `01_pics` VALUES (14, 1, '7FqbgHSVcwvhpo4J1CfxIUNP9DeXB2K8-DsjtlVA1GRy9zQFgM8Eo0BrITWNCaUwu.jpg', '14.jpg', '', '2012-04-19 10:56:27', 15);
INSERT INTO `01_pics` VALUES (15, 1, 'bXf6pKj5c8LNlI7w1dqztWBZ0ErH3k2m-EdiDs5FJnxm4yVY0zB196ZHNA8uPkjRt.jpg', '15.jpg', '', '2012-04-19 10:56:28', 16);
INSERT INTO `01_pics` VALUES (16, 1, 'YdJcvqMgsjDiTaWeFALfPw2UVpxu5yK0-fqoRXzvrk7SaB608QAIx4J1hg9wZDdsF.jpg', '16.jpg', '', '2012-04-19 10:56:28', 17);
INSERT INTO `01_pics` VALUES (17, 1, 'J1IaWtrpT9Hk3X4ZSFoPmuYGw5cRsKB6-lIq8QUNLi7knJzWP9eb4wg5tDvpZsu03.jpg', '17.jpg', '', '2012-04-19 10:56:29', 18);
INSERT INTO `01_pics` VALUES (18, 1, 'niulIwdoPsh1K98fp7mtFQybr0X4GaYj-0t8MQX4nNodCKg6I7bha5xY9rJfi3jZy.jpg', '18.jpg', '', '2012-04-19 10:56:30', 13);
INSERT INTO `01_pics` VALUES (19, 1, 'ECqKabAZoLxjd0vzfgVBnS1tUHQPRiuX-Uig49FZXrR6flJjncpKQSHzMWDABvPqx.jpg', '19.jpg', '', '2012-04-19 10:56:31', 19);
INSERT INTO `01_pics` VALUES (20, 1, '7poBPHz5sgxqbFu0LmUKIwrMDtn8Z9aS-Ckatj5ZgfHoVMcTJAKF0PRWwlndzyI8b.jpg', '20.jpg', '', '2012-04-19 10:56:31', 20);
INSERT INTO `01_pics` VALUES (21, 2, '3elq6PJNAuco9pxYsfwg4V0IzRi7m1bU-INkHtEmPVMbdZef89cJwhxS6Clz1TaAF.jpg', '21.jpg', '', '2012-04-19 10:57:46', 7);
INSERT INTO `01_pics` VALUES (22, 2, 'ukxmIeoAvULtz0ESFKhqMb1QYrG5ysDg-1tUZ9kQSCFEfT5GxcdgXrA27BhsnVIvP.jpg', '22.jpg', '', '2012-04-19 10:57:47', 3);
INSERT INTO `01_pics` VALUES (23, 2, 'ZhC5IJBmRVeLSHlotMAsqYvfdpDFyUWj-I7StlPnDe9Y6QqBGzcmkZgHj4EUrMRix.jpg', '23.jpg', '', '2012-04-19 10:57:48', 2);
INSERT INTO `01_pics` VALUES (24, 2, 'CYfZ4daSRczWr6TepKhPiNAkLu9jFD5t-PvH4ZRlGomqVNdAthfcpKbeQBSwFTgC8.jpg', '24.jpg', '', '2012-04-19 10:57:48', 5);
INSERT INTO `01_pics` VALUES (25, 2, 'kSXEH0lTJeug8B4jLwh3sGpvfZzNDUrK-wF8pgnLCsRiG7obZYT9Uxj2uf5tEeWN3.jpg', '25.jpg', '', '2012-04-19 10:57:49', 4);
INSERT INTO `01_pics` VALUES (26, 2, 'BteYpmfgGHJZ8q4Iz6WkiUVXAdEsQTrS-XLShn01NUa6Ex8A5bdl7qmFvuHKIWj4P.jpg', '26.jpg', '', '2012-04-19 10:57:50', 1);
INSERT INTO `01_pics` VALUES (27, 2, '1Uglb58oC6BycDXYz3QA4LamSPKntWvr-oEb38PB2JQvUfDcAVzTWlqaYSg9715mt.jpg', '27.jpg', '', '2012-04-19 10:57:50', 6);
INSERT INTO `01_pics` VALUES (28, 2, '8Hw1gYyP2TkGQt4aRMVF9fzeusx5jAZl-4Z1eG9MiDhPguYmWaCUTNfVK6yxFnHLB.jpg', '28.jpg', '', '2012-04-19 10:57:51', 8);
INSERT INTO `01_pics` VALUES (29, 3, '4H9CUoh3rYSjxWbs28qMmN7kBtVT6Fnz-14syoTvi79SIPX30FRGUembDBKh6Mwzq.jpg', '30.jpg', '', '2012-04-19 10:58:44', 0);

-- --------------------------------------------------------

-- 
-- โครงสร้างตาราง `01_reload`
-- 

CREATE TABLE `01_reload` (
  `id` int(3) NOT NULL auto_increment,
  `title` char(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `email` varchar(50) NOT NULL,
  `gender` char(1) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

-- 
-- dump ตาราง `01_reload`
-- 

INSERT INTO `01_reload` VALUES (1, 'Mr.', 'pinyo', 'pungfueng', 'zicedemo@gmail.com', '1', '2012-06-08 17:34:31');
INSERT INTO `01_reload` VALUES (2, 'Mr.', 'zicedemo', 'demo', 'zicedemo@gmail.com', '1', '2012-06-08 12:34:33');
INSERT INTO `01_reload` VALUES (24, 'Mrs.', 'Arin', 'include', 'Arin@demo.com', '2', '2012-06-09 20:26:18');

-- --------------------------------------------------------

-- 
-- โครงสร้างตาราง `01_user`
-- 

CREATE TABLE `01_user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- dump ตาราง `01_user`
-- 

INSERT INTO `01_user` VALUES (1, 'admin', 'zice');
