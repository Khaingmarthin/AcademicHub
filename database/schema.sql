-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: localhost    Database: academichub
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;



--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_years` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year_name` (`year_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,'2025-2026','active','2025-12-01','2026-09-30','2026-07-31 14:02:39','2026-08-02 13:02:36'),(2,'2024-2025','archived','2024-12-01','2025-09-30','2026-07-31 14:02:48','2026-08-02 13:02:36');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'login','User logged in successfully','2026-07-31 03:00:25'),(2,1,'login','User logged in successfully','2026-07-31 13:59:49'),(3,1,'login','User logged in successfully','2026-08-01 03:14:14'),(4,1,'create_announcement','Created announcement: ID 1','2026-08-01 04:08:17'),(5,1,'create_announcement','Created announcement: ID 2','2026-08-01 04:35:52'),(6,1,'update_announcement','Updated announcement: ID 2','2026-08-01 04:36:05'),(7,1,'update_announcement','Updated announcement: ID 2','2026-08-01 04:36:08'),(8,1,'update_announcement','Updated announcement: ID 2','2026-08-01 04:36:09'),(9,1,'update_announcement','Updated announcement: ID 2','2026-08-01 04:36:10'),(10,1,'update_announcement','Updated announcement: ID 2','2026-08-01 04:38:22'),(11,1,'login','User logged in successfully','2026-08-01 05:47:29'),(12,1,'logout','User logged out successfully','2026-08-01 06:29:03'),(13,1,'login','User logged in successfully','2026-08-01 06:32:22'),(14,1,'login','User logged in successfully','2026-08-01 11:15:37'),(15,1,'create_announcement','Created announcement: ID 3','2026-08-01 11:19:43'),(16,1,'login','User logged in successfully','2026-08-02 12:50:14'),(17,1,'academic_year_change','Changed academic year ID 1 status to active','2026-08-02 13:02:36'),(18,1,'login','User logged in successfully','2026-08-02 22:40:57'),(19,1,'create_announcement','Created announcement: ID 4','2026-08-02 22:42:21');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `academic_year_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_urgent` tinyint(1) DEFAULT '0',
  `view_count` int DEFAULT '0',
  `download_count` int DEFAULT '0',
  `publish_date` datetime DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `announcements_academic_year_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `announcements_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `announcements_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,3,NULL,1,'Testing','Testing 12','draft',1,0,0,0,'2026-08-01 00:00:00','2026-08-31 00:00:00','2026-08-01 04:08:17','2026-08-01 04:08:17'),(2,1,1,1,'Testing','Testing','draft',0,0,0,0,'2026-08-01 11:07:56',NULL,'2026-08-01 04:35:52','2026-08-01 04:36:05'),(3,5,1,1,'Final exam','Final exam on September 30','published',0,0,0,0,'2026-07-09 11:18:42',NULL,'2026-08-01 11:19:43','2026-08-01 11:19:43'),(4,3,1,1,'Fresher Welcome','Fresher Welcome on December 25','published',0,1,0,0,'2026-08-01 22:41:21',NULL,'2026-08-02 22:42:21','2026-08-02 22:42:21');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;

CREATE TABLE `attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `announcement_id` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `uploaded_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  KEY `announcement_id` (`announcement_id`),
  KEY `uploaded_by` (`uploaded_by`),

  CONSTRAINT `attachments_announcement_fk`
    FOREIGN KEY (`announcement_id`)
    REFERENCES `announcements` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `attachments_user_fk`
    FOREIGN KEY (`uploaded_by`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookmarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `announcement_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `announcement_id` (`announcement_id`),
  CONSTRAINT `bookmarks_announcement_fk` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bookmarks_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks`
--

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '#2563EB',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'folder',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'General','','2026-07-31 14:03:51','2026-08-01 11:34:04','#2563eb','folder'),(3,'Event','','2026-07-31 14:04:42','2026-08-01 11:44:55','#22c55e','folder'),(5,'Exam','','2026-07-31 14:09:22','2026-08-01 07:31:50','#f59e0b','folder');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classrooms`
--

DROP TABLE IF EXISTS `classrooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classrooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `academic_year_id` int NOT NULL,
  `major_id` int DEFAULT NULL,
  `year_level` int DEFAULT NULL,
  `section` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT  NULL,
  `classroom_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classroom_unique` (`academic_year_id`,`major_id`,`year_level`,`section`),
  KEY major_id (`major_id`),
  CONSTRAINT classrooms_major_fk FOREIGN KEY (`major_id`) REFERENCES majors(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
   KEY academic_year_id (`academic_year_id`),
  CONSTRAINT classrooms_academic_year_fk FOREIGN KEY (`academic_year_id`) REFERENCES academic_years(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classrooms`
--

LOCK TABLES `classrooms` WRITE;
/*!40000 ALTER TABLE `classrooms` DISABLE KEYS */;
INSERT INTO `classrooms` VALUES (1,1,1,1,'A','First Year (A)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(2,1,1,1,'B','First Year (B)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(3,1,1,1,'C','First Year (C)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(4,1,2,2,'CS (A)','Second Year CS (A)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(5,1,2,2,'CS (B)','Second Year CS (B)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(6,1,3,2,'CT','Second Year CT','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(7,1,2,3,'CS (A)','Third Year CS (A)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(8,1,2,3,'CS (B)','Third Year CS (B)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(9,1,3,3,'CT','Third Year CT','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(10,1,2,4,'CS (A)','Fourth Year CS (A)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(11,1,2,4,'CS (B)','Fourth Year CS (B)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(12,1,3,4,'CT','Fourth Year CT','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(13,1,2,5,'CS (A)','Fifth Year CS (A)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(14,1,2,5,'CS (B)','Fifth Year CS (B)','Active','2026-08-02 13:22:57','2026-08-02 13:22:57'),(15,1,3,5,'CT','Fifth Year CT','Active','2026-08-02 13:22:57','2026-08-02 13:22:57');
/*!40000 ALTER TABLE `classrooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `announcement_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `announcement_id` (`announcement_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_announcement_fk` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `faculty_id` int NOT NULL,
  `course_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `major_id` int DEFAULT NULL,
  `year_level` int DEFAULT NULL,
  `semester` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `course_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`),
  UNIQUE KEY `course_name` (`course_name`),
  KEY `faculty_id` (`faculty_id`),
  KEY `major_id` (`major_id`),
  CONSTRAINT courses_faculty_fk   FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT courses_major_fk FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,6,'Myanmar','','First Year','First Semester',3,'Active','M-1201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(2,6,'English Proficiency II','','First Year','First Semester',3,'Active','E-1201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(3,5,'Physics','','First Year','First Semester',3,'Active','P-1201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(4,7,'Discrete Mathematics','','First Year','First Semester',3,'Active','CST-1241',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(5,1,'Programming Logic and Design (Programming in C++)','','First Year','First Semester',4,'Active','CST-1212',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(6,1,'Database Fundamentals','','First Year','First Semester',4,'Active','CST-1223',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(7,3,'Digital Logic Design','','First Year','First Semester',4,'Active','CST-1234',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(8,6,'English Proficiency IV','Computer Science','Second Year','First Semester',3,'Active','E-2201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(9,7,'Differential Equations and Numerical Analysis','Computer Science','Second Year','First Semester',3,'Active','CST-2241',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(10,1,'Artificial Intelligence','Computer Science','Second Year','First Semester',4,'Active','CST-2212',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(11,1,'Operating Systems','Computer Science','Second Year','First Semester',4,'Active','CST-2213',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(12,1,'Software Analysis and Design','Computer Science','Second Year','First Semester',4,'Active','CST-2224',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(13,1,'Data Communication and Networking','Computer Science','Second Year','First Semester',4,'Active','CST-2235',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(14,1,'Web Technology (JavaScript)','Computer Science','Second Year','First Semester',4,'Active','CS-2256',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(21,3,'Circuits and Electronics','Computer Technology','Second Year','First Semester',4,'Active','CT-2236',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(22,1,'Third Year CS Course','Computer Science','Third Year','First Semester',4,'Active','CS-3201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(23,3,'Third Year CT Course','Computer Technology','Third Year','First Semester',4,'Active','CT-3201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(24,1,'Fourth Year CS Course','Computer Science','Fourth Year','First Semester',4,'Active','CS-4201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(25,3,'Fourth Year CT Course','Computer Technology','Fourth Year','First Semester',4,'Active','CT-4201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(26,1,'Fifth Year CS Course','Computer Science','Fifth Year','First Semester',4,'Active','CS-5201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08'),(27,3,'Fifth Year CT Course','Computer Technology','Fifth Year','First Semester',4,'Active','CT-5201',NULL,'2026-08-02 13:05:08','2026-08-02 13:05:08');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faculties`
--

DROP TABLE IF EXISTS `faculties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faculties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `faculty_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faculty_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `vision` text COLLATE utf8mb4_unicode_ci,
  `mission` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_name` (`faculty_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faculties`
--

LOCK TABLES `faculties` WRITE;
/*!40000 ALTER TABLE `faculties` DISABLE KEYS */;
INSERT INTO `faculties` VALUES (1,'Faculty of Computer Science (FCS)','FCS','','The Faculty of Computer Science is dedicated to advancing computer science education and research that creates real-world impact. It develops future IT professionals, innovators and leaders.','Provide quality education, research, innovation, practical learning, internships and modern computing education.','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(2,'Faculty of Information Science (FIS)','FIS','','Produce qualified computer scientists through student-centered education emphasizing practical learning, tutorials, projects and professional skills.','Produce qualified computer scientists through student-centered education emphasizing practical learning, tutorials, projects and professional skills.','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(3,'Faculty of Computer Science and Technology (FCST)','FCST','','Quality Education for Change, Peace and Progress and Innovative Education for a Knowledge, Pioneering and Global Society.','Provide holistic education that contributes to sustainable national development.','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(4,'Information Technology Support and Management (ITSM)','ITSM','','Provide innovative education that prepares globally competitive graduates and supports sustainable development.','Provide innovative education that prepares globally competitive graduates and supports sustainable development.','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(5,'Department of Natural Science (Physics)','PHYSICS','','','','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(6,'Department of Natural Language (Myanmar & English)','LANGUAGE','','','','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(7,'Faculty of Computing (Mathematics)','MATH','','','','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(8,'Finance Department','FINANCE','The Finance Department is responsible for managing the university\'s financial resources efficiently and transparently. It oversees budgeting, accounting, procurement, and financial administration to ensure the smooth operation of academic and administrative activities. The department supports students, faculty, and staff by maintaining sound financial practices and contributing to the sustainable development of the university.','','','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(9,'Student Affairs Department','STUDENT_AFFAIRS','The Student Affairs Department is committed to supporting students throughout their academic journey by promoting their personal, academic, and social development. The department manages student registration, welfare services, extracurricular activities, scholarships, and campus events while fostering a safe, inclusive, and disciplined learning environment. It serves as a bridge between students and the university administration to enhance the overall student experience.','','','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(10,'Library','LIBRARY','The University Library provides students, faculty members, and researchers with access to quality academic resources and learning facilities. It offers a wide collection of books, journals, reference materials, and digital resources to support teaching, learning, and research. The library encourages lifelong learning, independent study, and academic excellence by creating a quiet, resourceful, and welcoming environment for the university community.','','','Active','2026-08-02 13:05:08','2026-08-02 13:05:08'),(11,'Administration Department','ADMIN','The Administration Department is responsible for ensuring the efficient operation and effective management of the university\'s administrative services. It supports teaching, research, and student affairs by providing quality administrative assistance, maintaining transparent policies, and coordinating essential university operations. Through a service-oriented approach, the department works closely with students, faculty, staff, parents, and other stakeholders to create a well-organized, supportive, and productive academic environment.','','','Active','2026-08-02 13:05:08','2026-08-02 13:05:08');
/*!40000 ALTER TABLE `faculties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `majors`
--

DROP TABLE IF EXISTS `majors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `majors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `major_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `major_name` (`major_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `majors`
--

LOCK TABLES `majors` WRITE;
/*!40000 ALTER TABLE `majors` DISABLE KEYS */;
INSERT INTO `majors` VALUES (1,5,'Common','First Year Foundation','2026-08-02 13:22:57','2026-08-02 13:37:05'),(2,5,'Computer Science','CS Major','2026-08-02 13:22:57','2026-08-02 13:22:57'),(3,5,'Computer Technology','CT Major','2026-08-02 13:22:57','2026-08-02 13:22:57');
/*!40000 ALTER TABLE `majors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_settings`
--

DROP TABLE IF EXISTS `notification_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `general_enabled` tinyint(1) DEFAULT '1',
  `event_enabled` tinyint(1) DEFAULT '1',
  `exam_enabled` tinyint(1) DEFAULT '1',
  `urgent_enabled` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `notification_settings_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_settings`
--

LOCK TABLES `notification_settings` WRITE;
/*!40000 ALTER TABLE `notification_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `announcement_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `announcement_id` (`announcement_id`),
  CONSTRAINT `notifications_announcement_fk` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

-- Table structure for table `timetables`
--

DROP TABLE IF EXISTS `timetables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timetables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `classroom_id` int NOT NULL,
  `academic_year_id` int DEFAULT NULL,
  `year_level` int DEFAULT NULL,
  `semester` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE(classroom_id, academic_year_id, semester), 
  KEY `classroom_id` (`classroom_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `timetables_ay_fk` (`academic_year_id`),
  CONSTRAINT `timetables_ay_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `timetables_classroom_fk` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `timetables_uploaded_by_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timetables`
--

LOCK TABLES `timetables` WRITE;
/*!40000 ALTER TABLE `timetables` DISABLE KEYS */;
INSERT INTO timetables VALUES
(
2,
2,
1,
1,
'First Semester',
'First Semester Timetable',
'assets/uploads/timetables/timetable_cls_2_1_first.jpg',
1,
NOW(),
NOW()
);

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `classroom_id` int DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `classroom_id` (`classroom_id`),
  CONSTRAINT `users_classroom_fk` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'Admin1','admin1@ucsmtla.edu.mm','$2y$10$/bXJjDOglMzDmvkIqrUoxO4yIhKiWY6uX0NAAAezgco0v2jZn5sya','admin','','2026-07-30 13:23:05','2026-08-01 07:06:46',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-03  5:13:02
