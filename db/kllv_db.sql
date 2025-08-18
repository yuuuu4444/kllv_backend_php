-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: kllv_db
-- ------------------------------------------------------
-- Server version	5.7.24

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `admin_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fullname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community_comments`
--

DROP TABLE IF EXISTS `community_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_comments` (
  `comments_no` int(11) NOT NULL AUTO_INCREMENT,
  `post_no` int(11) NOT NULL,
  `content` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `commented_at` datetime NOT NULL,
  PRIMARY KEY (`comments_no`),
  KEY `post_no_idx` (`post_no`),
  KEY `user_id_idx` (`author_id`),
  CONSTRAINT `fk_community_comments_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_community_comments_post_no` FOREIGN KEY (`post_no`) REFERENCES `community_posts` (`post_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community_comments`
--

LOCK TABLES `community_comments` WRITE;
/*!40000 ALTER TABLE `community_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `community_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community_comments_reports`
--

DROP TABLE IF EXISTS `community_comments_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_comments_reports` (
  `report_no` int(11) NOT NULL AUTO_INCREMENT,
  `comment_no` int(11) NOT NULL,
  `category_no` int(11) NOT NULL,
  `reporter_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `reported_at` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`report_no`),
  KEY `user_id_idx` (`reporter_id`),
  KEY `comment_no_idx` (`comment_no`),
  KEY `fk_community_comments_reports_category_no_idx` (`category_no`),
  CONSTRAINT `fk_community_comments_reports_category_no` FOREIGN KEY (`category_no`) REFERENCES `community_posts_reports_categories` (`category_no`),
  CONSTRAINT `fk_community_comments_reports_comment_no` FOREIGN KEY (`comment_no`) REFERENCES `community_comments` (`comments_no`),
  CONSTRAINT `fk_community_comments_reports_reporter_id` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community_comments_reports`
--

LOCK TABLES `community_comments_reports` WRITE;
/*!40000 ALTER TABLE `community_comments_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `community_comments_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community_posts`
--

DROP TABLE IF EXISTS `community_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_posts` (
  `post_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `category_no` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default image',
  `content` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `posted_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`post_no`),
  KEY `user_id_idx` (`author_id`),
  KEY `category_id_idx` (`category_no`),
  CONSTRAINT `fk_community_posts_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_community_posts_category_no` FOREIGN KEY (`category_no`) REFERENCES `community_posts_categories` (`category_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community_posts`
--

LOCK TABLES `community_posts` WRITE;
/*!40000 ALTER TABLE `community_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `community_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community_posts_categories`
--

DROP TABLE IF EXISTS `community_posts_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_posts_categories` (
  `category_no` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_no`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community_posts_categories`
--

LOCK TABLES `community_posts_categories` WRITE;
/*!40000 ALTER TABLE `community_posts_categories` DISABLE KEYS */;
INSERT INTO `community_posts_categories` VALUES (1,'舊物交換'),(2,'生活抱怨'),(3,'揪團來買');
/*!40000 ALTER TABLE `community_posts_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community_posts_images`
--

DROP TABLE IF EXISTS `community_posts_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_posts_images` (
  `image_no` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `post_no` int(11) NOT NULL,
  PRIMARY KEY (`image_no`),
  KEY `post_no_idx` (`post_no`),
  CONSTRAINT `fk_community_posts_images_post_no` FOREIGN KEY (`post_no`) REFERENCES `community_posts` (`post_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community_posts_images`
--

LOCK TABLES `community_posts_images` WRITE;
/*!40000 ALTER TABLE `community_posts_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `community_posts_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community_posts_reports`
--

DROP TABLE IF EXISTS `community_posts_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_posts_reports` (
  `report_no` int(11) NOT NULL AUTO_INCREMENT,
  `post_no` int(11) NOT NULL,
  `category_no` int(11) NOT NULL,
  `reporter_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `reported_at` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`report_no`),
  KEY `user_id_idx` (`reporter_id`),
  KEY `post_no_idx` (`post_no`),
  KEY `fk_community_posts_reports_category_no_idx` (`category_no`),
  CONSTRAINT `fk_community_posts_reports_category_no` FOREIGN KEY (`category_no`) REFERENCES `community_posts_reports_categories` (`category_no`),
  CONSTRAINT `fk_community_posts_reports_post_no` FOREIGN KEY (`post_no`) REFERENCES `community_posts` (`post_no`),
  CONSTRAINT `fk_community_posts_reports_reporter_id` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community_posts_reports`
--

LOCK TABLES `community_posts_reports` WRITE;
/*!40000 ALTER TABLE `community_posts_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `community_posts_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `community_posts_reports_categories`
--

DROP TABLE IF EXISTS `community_posts_reports_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_posts_reports_categories` (
  `category_no` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `community_posts_reports_categories`
--

LOCK TABLES `community_posts_reports_categories` WRITE;
/*!40000 ALTER TABLE `community_posts_reports_categories` DISABLE KEYS */;
INSERT INTO `community_posts_reports_categories` VALUES (1,'仇恨言論'),(2,'暴力內容'),(3,'詐騙、不實資訊'),(4,'自我傷害、自殺'),(5,'霸凌、騷擾');
/*!40000 ALTER TABLE `community_posts_reports_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `event_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_no` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `fee_per_person` int(11) DEFAULT NULL,
  `p_count` int(11) DEFAULT NULL,
  `p_limit` int(11) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `reg_deadline` date NOT NULL,
  `created_at` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_no`),
  KEY `category_no_idx` (`category_no`),
  CONSTRAINT `fk_events_category_no` FOREIGN KEY (`category_no`) REFERENCES `events_categories` (`category_no`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'梨山林輕健行_test','桃園市中壢區復興路46號9樓',2,'https://i.imgur.com/abc.jpg','為了讓大家走出戶外，感受大自然的魅力，我們特別規劃了「梨山林輕健行」活動，帶領各位走進梨山的迷人山林，享受清新空氣與壯麗景色。這條路線適合各年齡層的參加者，無論是初學者還是有健行經驗的朋友，都能輕鬆參與，感受步行中的寧靜與放鬆。\\n活動將在專業領隊的帶領下，穿越風景如畫的林間小徑，途中將設有數個休息點，並介紹當地的自然景觀與生態知識。參加者除了能提升健康體能，還能與社區居民一起享受親近大自然的樂趣，建立彼此間的聯繫與友誼。\\n我們鼓勵大家穿著舒適的運動鞋，攜帶水壺、輕便餐點以及防曬用品，一起來放鬆心情，度過愉快的一天。快來報名參加，一同踏上這段美麗的梨山林輕健行吧！',400,NULL,200,'2025-10-01 00:00:00','2025-10-02 00:00:00','2025-09-25','2025-08-16',0);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_categories`
--

DROP TABLE IF EXISTS `events_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_categories` (
  `category_no` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_no`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_categories`
--

LOCK TABLES `events_categories` WRITE;
/*!40000 ALTER TABLE `events_categories` DISABLE KEYS */;
INSERT INTO `events_categories` VALUES (1,'旅遊'),(2,'健康'),(3,'藝文'),(4,'其他');
/*!40000 ALTER TABLE `events_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_regs`
--

DROP TABLE IF EXISTS `events_regs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_regs` (
  `reg_no` int(11) NOT NULL AUTO_INCREMENT,
  `event_no` int(11) NOT NULL,
  `participant_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `p_total` int(11) NOT NULL,
  `fee_total` int(11) DEFAULT NULL,
  `payment_no` int(11) DEFAULT NULL,
  `cancel_reason_no` int(11) DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reg_no`),
  KEY `event_no_idx` (`event_no`),
  KEY `user_id_idx` (`participant_id`),
  KEY `payment_no_idx` (`payment_no`),
  KEY `cancel_reason_no_idx` (`cancel_reason_no`),
  CONSTRAINT `fk_events_regs_cancel_reason_no` FOREIGN KEY (`cancel_reason_no`) REFERENCES `events_regs_cancel_reasons` (`reason_no`),
  CONSTRAINT `fk_events_regs_event_no` FOREIGN KEY (`event_no`) REFERENCES `events` (`event_no`),
  CONSTRAINT `fk_events_regs_participant_id` FOREIGN KEY (`participant_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_events_regs_payment_no` FOREIGN KEY (`payment_no`) REFERENCES `events_regs_payments` (`payment_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_regs`
--

LOCK TABLES `events_regs` WRITE;
/*!40000 ALTER TABLE `events_regs` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_regs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_regs_cancel_reasons`
--

DROP TABLE IF EXISTS `events_regs_cancel_reasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_regs_cancel_reasons` (
  `reason_no` int(11) NOT NULL AUTO_INCREMENT,
  `reason_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`reason_no`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_regs_cancel_reasons`
--

LOCK TABLES `events_regs_cancel_reasons` WRITE;
/*!40000 ALTER TABLE `events_regs_cancel_reasons` DISABLE KEYS */;
INSERT INTO `events_regs_cancel_reasons` VALUES (1,'報錯活動 / 重複報名'),(2,'疫情 / 健康安全考量'),(3,'家庭因素'),(4,'工作安排'),(5,'對活動內容不感興趣了'),(6,'其他原因');
/*!40000 ALTER TABLE `events_regs_cancel_reasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_regs_payments`
--

DROP TABLE IF EXISTS `events_regs_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_regs_payments` (
  `payment_no` int(11) NOT NULL AUTO_INCREMENT,
  `payment_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`payment_no`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_regs_payments`
--

LOCK TABLES `events_regs_payments` WRITE;
/*!40000 ALTER TABLE `events_regs_payments` DISABLE KEYS */;
INSERT INTO `events_regs_payments` VALUES (1,'信用卡'),(2,'銀行轉帳'),(3,'現金繳費');
/*!40000 ALTER TABLE `events_regs_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_regs_plist`
--

DROP TABLE IF EXISTS `events_regs_plist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_regs_plist` (
  `plist_no` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` int(11) NOT NULL,
  `participant_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `birth_date` datetime NOT NULL,
  `econtact_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `econtact_phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `econtact_relation` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`plist_no`),
  KEY `order_no_idx` (`reg_no`),
  KEY `fk_events_regs_plists_participant_idx` (`participant_id`),
  CONSTRAINT `fk_events_regs_plists_participant_id` FOREIGN KEY (`participant_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_events_regs_plists_reg_no` FOREIGN KEY (`reg_no`) REFERENCES `events_regs` (`reg_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_regs_plist`
--

LOCK TABLES `events_regs_plist` WRITE;
/*!40000 ALTER TABLE `events_regs_plist` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_regs_plist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `news_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `category_no` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `published_at` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_no`),
  KEY `category_no_idx` (`category_no`),
  CONSTRAINT `fk_news_category_no` FOREIGN KEY (`category_no`) REFERENCES `news_categories` (`category_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_categories`
--

DROP TABLE IF EXISTS `news_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_categories` (
  `category_no` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_categories`
--

LOCK TABLES `news_categories` WRITE;
/*!40000 ALTER TABLE `news_categories` DISABLE KEYS */;
INSERT INTO `news_categories` VALUES (1,'公告'),(2,'活動'),(3,'補助'),(4,'施工'),(5,'防災');
/*!40000 ALTER TABLE `news_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repair`
--

DROP TABLE IF EXISTS `repair`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repair` (
  `repair_no` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_no` int(11) NOT NULL,
  `description` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `reporter_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `reported_at` date NOT NULL,
  `reply_content` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resolved_at` date DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`repair_no`),
  KEY `category_no_idx` (`category_no`),
  KEY `user_id_idx` (`reporter_id`),
  CONSTRAINT `fk_repair_category_no` FOREIGN KEY (`category_no`) REFERENCES `repair_categories` (`category_no`),
  CONSTRAINT `fk_repair_reporter_id` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repair`
--

LOCK TABLES `repair` WRITE;
/*!40000 ALTER TABLE `repair` DISABLE KEYS */;
/*!40000 ALTER TABLE `repair` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repair_categories`
--

DROP TABLE IF EXISTS `repair_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repair_categories` (
  `category_no` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_no`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repair_categories`
--

LOCK TABLES `repair_categories` WRITE;
/*!40000 ALTER TABLE `repair_categories` DISABLE KEYS */;
INSERT INTO `repair_categories` VALUES (1,'路燈損壞'),(2,'公共設施損壞'),(3,'道路坑洞'),(4,'其他');
/*!40000 ALTER TABLE `repair_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repair_images`
--

DROP TABLE IF EXISTS `repair_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repair_images` (
  `image_no` int(11) NOT NULL AUTO_INCREMENT,
  `repair_no` int(11) NOT NULL,
  `image_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`image_no`),
  KEY `repair_no_idx` (`repair_no`),
  CONSTRAINT `fk_repair_images_repair_no` FOREIGN KEY (`repair_no`) REFERENCES `repair` (`repair_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repair_images`
--

LOCK TABLES `repair_images` WRITE;
/*!40000 ALTER TABLE `repair_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `repair_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fullname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profile_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default image',
  `phone_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `household_no` int(11) DEFAULT NULL,
  `role_type` tinyint(4) DEFAULT '0',
  `is_active` tinyint(4) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `household_code_idx` (`household_no`),
  CONSTRAINT `fk_users_household_no` FOREIGN KEY (`household_no`) REFERENCES `users_households` (`household_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_households`
--

DROP TABLE IF EXISTS `users_households`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_households` (
  `household_no` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creator_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`household_no`),
  KEY `user_id_idx` (`creator_id`),
  CONSTRAINT `fk_users_households_creator_id` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_households`
--

LOCK TABLES `users_households` WRITE;
/*!40000 ALTER TABLE `users_households` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_households` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `village_chief`
--

DROP TABLE IF EXISTS `village_chief`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `village_chief` (
  `chief_id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profile_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default image',
  `introduction` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`chief_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `village_chief`
--

LOCK TABLES `village_chief` WRITE;
/*!40000 ALTER TABLE `village_chief` DISABLE KEYS */;
/*!40000 ALTER TABLE `village_chief` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-17 15:09:10

/*以下自行新增的假資料指令 */;