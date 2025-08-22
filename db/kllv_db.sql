CREATE DATABASE  IF NOT EXISTS `kllv_db` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `kllv_db`;
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
INSERT INTO `admin` VALUES ('admin_01','後台管理員','test123','2025-08-19 08:54:34',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'梨山林輕健行','桃園市中壢區復興路46號9樓',2,'https://i.ibb.co/CstY0s1x/events-01-mt.png','<p>為了讓大家走出戶外，感受大自然的魅力，我們特別規劃了「梨山林輕健行」的活動，帶領各位走進梨山的迷人山林，享受清新空氣與壯麗景色。這條路線適合各年齡層的參加者，無論是初學者還是有健行經驗的朋友，都能輕鬆參與，感受步行中的寧靜與放鬆。</p><p>活動將在專業領隊的帶領下，穿越風景如畫的林間小徑，途中將設有數個休息點，並介紹當地的自然景觀與生態知識。參加者除了能提升健康體能，還能與社區居民一起享受親近大自然的樂趣，建立彼此間的聯繫與友誼。</p><p>我們鼓勵大家穿著舒適的運動鞋，攜帶水壺、輕便餐點以及防曬用品，一起來放鬆心情，度過愉快的一天。快來報名參加，一同踏上這段美麗的梨山林輕健行吧！</p>',400,NULL,200,'2025-10-01 00:00:00','2025-10-02 00:00:00','2025-09-25','2025-08-16',0),(2,'梨花秘境之旅','梨花谷觀光果園',1,'https://i.ibb.co/rGbhhh97/events-02-flower.png','<p>春天來臨，梨花盛開，讓我們一起走進「梨花秘境之旅」，探索隱藏在梨山深處的美麗景點。這次活動將帶領大家走訪梨花樹海，漫遊在白色花海中，感受清新怡人的春日氣息。無論是喜愛攝影、熱愛大自然的朋友，或是單純想放鬆心情的參與者，都能在這條精心規劃的路線中找到樂趣。\n  在專業導遊的帶領下，我們將沿途介紹梨花的品種與生長環境，並深入了解當地的自然生態。活動設有輕鬆步道，適合各年齡層的朋友參加，還能在途中享受休息站提供的小食與熱茶，與其他社區居民共同度過愉快時光。\n帶著親友一起，穿上舒適的運動鞋，攜帶相機捕捉梨花的美麗，來一場與大自然親密接觸的旅行吧！快來報名參加，感受春天的美好。</p>',500,NULL,100,'2025-09-15 09:00:00','2025-09-15 16:00:00','2025-09-01','2025-08-21',1),(3,'瑜珈派對','里民活動中心',2,'https://i.ibb.co/d0hLJ3nD/events-03-yoga.png','<p>快來加入我們的「瑜珈派對」，讓身心靈在輕鬆愉快的氛圍中獲得全方位的放鬆與舒展！這場活動特別規劃為一個輕鬆、有趣又具挑戰的瑜珈體驗，無論你是瑜珈新手或是有一定基礎的愛好者，都能在專業指導老師的帶領下，感受瑜珈動作帶來的舒緩效果。</p><p>活動當天，我們將透過一系列的動作練習，搭配輕柔的音樂與放鬆技巧，幫助參加者釋放壓力，增強身體柔韌性，同時提高專注力。瑜珈的每一個呼吸與動作，不僅能提升身體健康，還能在活動中建立起與其他社區朋友的連結與互動。</p><p>來一場集體的瑜珈派對吧！穿上輕便舒適的運動服，帶上瑜珈墊與愉快的心情，讓我們一起在這場健康派對中，釋放壓力、強健體魄，並開心交流。</p>',200,NULL,60,'2025-09-05 10:00:00','2025-09-05 15:00:00','2025-08-15','2025-08-22',1),(4,'端午包粽樂','里民活動中心',3,'https://i.ibb.co/PGTtdZvy/events-04-dumpling.png','<p>端午節將至，讓我們一起來參加「端午包粽樂」活動，體驗這個充滿傳統味道的節日！本次活動將帶領大家親手包粽子，學習傳統粽子包製技巧，並與社區居民共同分享這份節日的美好。</p><p>在專業指導老師的帶領下，您將學習如何選擇優質的糯米、餡料及粽葉，並了解不同種類的粽子製作方法。不僅如此，我們還將為大家準備豐富的活動內容，讓每位參加者都能體驗到親手包粽的樂趣，並品嚐自己製作的美味粽子。</p><p>這是一個適合全家大小參加的活動，不論您是粽子高手還是第一次動手，都能在輕鬆的氛圍中學習和交流，感受傳統文化的魅力。快來和我們一起過一個溫馨又有趣的端午節吧！穿上舒適的服裝，帶著歡笑和好心情，一同享受包粽的樂趣！</p>',200,NULL,60,'2025-09-05 09:00:00','2025-09-05 12:00:00','2025-08-15','2025-08-22',1),(5,'Emerald畫展','里民藝文中心展覽廳心',3,'https://i.ibb.co/Gv4KWCps/events-05-paint.png','<p>誠摯邀請您來參加「Emerald畫展」，一起沉浸在色彩與創意的藝術世界中！這場畫展將展示一系列充滿活力的作品，以「Emerald」為主題，呈現出自然、和諧與美麗的視覺體驗。每一幅作品都蘊含著深厚的情感與藝術家獨特的視角，展現了從大自然中汲取靈感的無限創造力。</p><p>本次畫展適合所有藝術愛好者與家庭參觀，無論您是對藝術有濃厚興趣，還是單純想來感受藝術氛圍的朋友，都能在這裡找到屬於自己的藝術共鳴。我們還安排了藝術家講座與現場互動，您將有機會近距離了解每幅作品背後的創作故事。</p><p>快來一起感受色彩交織的魅力，與其他社區朋友共享這份視覺與心靈的盛宴！我們期待您的蒞臨，一同探索「Emerald畫展」中的無限可能。</p>',0,NULL,200,'2025-09-05 09:00:00','2025-09-05 18:00:00','2025-08-15','2025-08-23',1),(6,'空瀧馬拉松','活動中心前廣場',2,'https://i.ibb.co/tP1rqgwW/events-06-run.png','<p>第一屆空瀧馬拉松始於1980年，以「讓我們像夥伴一樣一起奔跑」為口號，今年是第41屆。</p><p>本次活動的目的並非競賽，而是讓所有參賽者享受跑步的樂趣，在壯麗的山景和清新的空氣中，挑戰自我，迎接健康的生活！這場馬拉松活動將帶您穿越空瀧的美麗景點，沿途經過郁郁蔥蔥的森林、清澈的溪流和壯觀的山脈，讓您在跑步的同時，沉浸在大自然的美景中，感受運動帶來的無窮樂趣。</p><p>無論您是資深馬拉松選手，還是初次挑戰的跑者，這場活動都將是您難忘的體驗。活動當天，除了賽道挑戰，我們還準備了豐富的獎勳與活動，讓大家在競賽之餘，感受社區的凝聚力與友善氛圍。</p><p>快來報名參加「空瀧馬拉松」，與親友一起在清新空氣中奔跑，挑戰極限，迎接屬於你的完賽時刻！</p>',600,NULL,200,'2025-09-05 06:00:00','2025-09-05 13:00:00','2025-08-15','2025-08-23',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_regs`
--

LOCK TABLES `events_regs` WRITE;
/*!40000 ALTER TABLE `events_regs` DISABLE KEYS */;
INSERT INTO `events_regs` VALUES (1,1,'user_account_001',2,800,1,NULL,'2025-08-21 09:56:54',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_regs_plist`
--

LOCK TABLES `events_regs_plist` WRITE;
/*!40000 ALTER TABLE `events_regs_plist` DISABLE KEYS */;
INSERT INTO `events_regs_plist` VALUES (1,1,'user_account_001','0987654321','H1212345678','1980-02-28 00:00:00','Arik·Mayaw','0988456789','家屬'),(2,1,'user_account_002','0988456789','A223456789','2005-05-10 00:00:00','Komod·Mayaw','0987654321','家屬');
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'地震防災演練地點宣布',5,NULL,'<p>親愛的里民您好：</p><p><br></p><p>台灣位於地震帶，日常防災意識不可少。本里配合防災政策，將於 7 月 11 日（五）上午舉行地震防災演練，模擬地震發生時的應變行動，提升里民防震應變能力。</p><p><br></p><p>演練地點分為三處：文昌公園廣場（主要集合點）、恐龍國小操場（學童避難點）、與里活動中心（模擬收容處所）。當日上午 9 點準時開始，請參與里民於 8:45 前就定位，配合演練人員指示。</p><p><br></p><p>本次演練將模擬 6 級地震情境，涵蓋「地震發生、緊急避難、集合清點、避難安置」完整流程。參與者將實地演練趴下掩護、疏散逃生與災後通報，並學習簡單止血與包紮技巧。演練過程中將有消防與民防人員指導，請勿驚慌。</p><p><br></p><p>演練對象包含本里里民、學校師生與鄰里志工，無需報名，歡迎踴躍參加。參與者將可獲得小禮物與防災小手冊，作為學習與紀念。</p><p><br></p><p>平日的準備就是面對災難時最大的保障。透過實際操作，了解災害當下該怎麼做，是保護自己與家人的第一步！敬請里民多多配合。</p>','2025-07-01',2),(2,'近期詐騙案件頻傳，提醒大家勿輕信陌生電話',1,NULL,'<p>親愛的里民您好：</p><p><br></p><p>近期本區接獲多起詐騙通報，多數手法為假冒政府、電信業者或快遞公司來電，要求民眾操作提款機或提供個資。</p><p><br></p><p>里辦公室特別提醒，接獲陌生來電時請提高警覺，切勿隨意透露身分證號、銀行帳號等個人資訊。</p><p><br></p><p>詐騙集團常以「帳號異常需重新認證」、「涉及某某案件需配合調查」、「包裹未領退件需確認」等名義誘導受害者操作提款機或轉帳，請牢記：任何要求轉帳、操作提款機的來電都是詐騙。</p><p><br></p><p>如遇可疑電話，建議立即掛斷，並撥打 165 反詐騙專線或 110 報警查證。若不慎提供資料，也請立刻通知銀行凍結帳戶，減少損失。</p><p><br></p><p>此外，我們將於近期舉辦反詐騙宣導講座，邀請警方到場解說最新詐騙手法與應對技巧，詳細時間與地點將另行公布，歡迎里民報名參加。</p><p><br></p><p>請大家保持警覺，也提醒身邊的長輩與孩子，小心陌生來電與簡訊。我們一起建立安全社區，守護彼此財產與資訊安全！</p>','2025-07-02',2),(3,'暑期育兒津貼開放申請，家長請把握時間',3,NULL,'<p>親愛的里民您好：</p><p><br></p><p>本里配合市府政策，自 8 月 1 日起開放申請「暑期育兒津貼」，協助有孩童的家庭分擔照顧成本，提升親子生活品質。</p><p><br></p><p>補助對象為設籍本里，且家中有 3 至 12 歲學童之家長。每位符合資格的學童最高可申請新台幣 3,000 元補助金，申請者可依實際支出情況，用於安親班、才藝課程、暑期營隊或其他與暑期照護有關之用途，補助款項將依核定結果核發。</p><p><br></p><p>我們鼓勵家長多加利用此補助資源，不僅能在經濟上獲得實質協助，也能讓孩子的假期過得更加健康、有趣又充實。比起長時間待在家裡看電視或滑手機，安排適當的學習活動不但能維持學習習慣，也有助於社交發展與身心健康。</p><p><br></p><p>申請時間自 8 月 1 日至 8 月 31 日止。家長可攜帶戶口名簿、身分證明與學童相關證明文件，至里辦公室現場辦理，或透過線上申請表單提交資料，程序簡便、免排隊，讓您輕鬆完成補助申請。</p><p><br></p><p>請注意，所有申請資料將由專人審核，請確保資料正確完整並於期限內提交，以免影響補助資格。補助款項將統一於 9 月中旬匯入申請人指定帳戶。</p><p><br></p><p>有任何問題歡迎洽詢里辦公室服務窗口。</p>','2025-07-03',2),(4,'丹娜絲颱風即將來臨，里民請做好準備',5,NULL,'<p>親愛的里民您好：</p><p><br></p><p>中央氣象署已針對丹娜絲颱風發布海上與陸上警報，研判可能在本週末對台灣造成影響。為確保社區安全，請里民及早做好防颱準備。</p><p><br></p><p>請里民確認自家陽台、窗邊有無鬆動或堆放雜物，應立即加固或移除，以免強風來襲造成危險。地下室與屋頂排水系統亦應提前清理，避免淹水。</p><p><br></p><p>此外，請事先備妥三日份糧食、乾淨飲水、電池、行動電源與基本藥品。若家中有長者或行動不便者，也請特別注意他們的安全與照護需求。</p><p><br></p><p>里辦公室將依據氣象變化進行防災通報與必要協助，也請密切關注氣象局與本所發布的最新資訊。</p><p><br></p><p>我們呼籲里民彼此互助，共同守護家園。</p>','2025-07-05',2),(5,'端午粽子吃不完，免費粽子大放送！',2,NULL,'<p>親愛的里民您好：</p><p><br></p><p>端午節過後家家戶戶粽子滿滿，為了避免浪費、也讓更多人感受節慶溫暖，本里將舉辦「免費粽子大放送」活動，邀請有需要的里民前來領取，一起把這份節慶的美味分享出去！</p><p><br></p><p>此次活動由社區志工與熱心商家合作，募集了超過 500 顆冷凍真空粽，包含傳統肉粽、素粽與花生粽等多種口味，皆由合格食品業者製作並標示有效期限，衛生安全有保障。活動將於 7 月 12 日（六）上午 10 點至中午 12 點，在里活動中心一樓開放領取。</p><p><br></p><p>每位里民最多可領取 3 顆粽子，數量有限，送完為止。領取時請出示身分證明文件（如健保卡、身分證），以利登記與分送。不論是長輩、上班族還是學生，只要設籍本里、想吃粽子，都歡迎來領！</p><p><br></p><p>除了粽子發送，現場也會提供加熱服務，讓想現場品嘗的里民可以熱騰騰地享用；還有志工泡茶區與閒聊角落，讓社區里民可以一邊吃粽一邊拉近彼此距離，增進鄰里感情。</p><p><br></p><p>我們相信，每一顆粽子都是一份心意。如果您家中粽子吃不完，也歡迎提前聯繫里辦公室，我們將安排冷凍保存與後續發送，讓食物不被浪費，愛心持續流轉。歡迎里民踴躍參加！</p>','2025-07-06',2),(6,'恐龍公園步道整修即將展開，預定 7 月底完工',4,NULL,'<p>親愛的里民您好：</p><p><br></p><p>恐龍公園是本里里民假日散步與親子活動的重要場所，但近年來步道因長期使用與天候影響，已出現部分鋪面破損與積水情形，影響行走安全。</p><p><br></p><p>為了改善公園內部設施、提供更安全舒適的環境，恐龍公園即將進行步道整修作業。</p><p><br></p><p>本次整修將進行全面性的步道重鋪，包含改善排水系統、強化鋪面平整度，同時也會加強周邊照明設備的安全檢查。</p><p><br></p><p>工程預計於 7 月 10 日正式動工，期間部分區域將設置圍籬封閉，暫不對外開放。預定在 7 月底前完成，屆時恐龍公園將以煥然一新的樣貌重新迎接大家！</p><p><br></p><p>提醒家長與孩童近期遊園時請注意安全，避開施工區域。若需前往運動或休憩，可先改至鄰近的光復公園替代使用。</p><p><br></p><p>施工期間造成不便，請多多包涵。</p>','2025-07-08',2),(7,'社區夜間照明將升級為 LED，預計 8 月中完成',4,NULL,'<p>親愛的里民您好：</p><p><br></p><p>為提升社區夜間行走安全與節能效率，本里將於近期展開夜間照明系統升級工程。現有老舊路燈將全面更換為高效能 LED 燈具，預計於 9 月初前全數完成。</p><p><br></p><p>此次汰換範圍包括主要街道、巷弄轉角與公園周邊等重點區域，總計超過 120 盞路燈將進行更新。新式 LED 燈具具備亮度提升、省電與壽命長等優勢，有助於降低長期電力支出。</p><p><br></p><p>工程預定於 8 月初開工，施工時間以白天為主，預計不會影響里民夜間使用。但部分地區可能有短暫燈光調整，請里民注意安全並配合施工人員指引。</p><p><br></p><p>除提升照明效能外，此次也會同步檢視燈具位置與角度，以改善過去照明死角問題，讓里民夜間出行更安心。完工後夜晚街道將更加明亮。</p><p><br></p><p>感謝里民支持與配合，若施工期間有任何意見或建議，歡迎向里辦公室反映。一起為更安全、宜居的社區努力！</p>','2025-07-12',2),(8,'老舊家電汰換補助，節能又省電',3,NULL,'<p>親愛的里民您好：</p><p><br></p><p>為推動節能減碳與永續生活，自本月起本里里民可申請「老舊家電汰換補助」，只要購買符合節能標章的新家電並完成登記，即可獲得最高 2,000 元補助。</p><p><br></p><p>補助家電品項包含電冰箱、冷氣機、洗衣機與除濕機等，需購買經濟部認證節能標章產品，並於購買後 30 日內完成登記與申請。每戶限申請 1 次，數量有限，申請完為止。</p><p><br></p><p>申請人需具設籍本里身份，並提供購買發票影本、產品證明文件與舊家電回收證明。為避免人數過多，建議透過線上平台先行登記再至里辦確認資料。</p><p><br></p><p>使用高耗能舊電器除了增加電費支出，也有潛在安全風險。把握政府補助汰換時機，讓家中用電更安心、更省錢，也為環保盡一份力！</p><p><br></p><p>若不確定家電型號是否符合補助資格，可上「節能補助資訊網」查詢或向里辦洽詢。</p>','2025-07-18',2),(9,'中元普渡交通管制路段通知',1,NULL,'<p>親愛的里民您好：</p><p><br></p><p>農曆七月即將到來，本區每年固定舉辦中元普渡儀式，今年活動預定於 8 月 18 日（星期日）下午 4 點在文德廣場登場，屆時將有祭品擺設、誦經儀式與社區聯誼。</p><p><br></p><p>為了活動順利進行，當日下午 2 點起，文昌路（德和街至德安街路段）將實施交通管制，車輛禁止通行，預計至晚間 6 點結束。請民眾提前規劃動線，避開管制時間。</p><p><br></p><p>參加普渡者請自行攜帶供品與香品，並遵守現場指引人員安排，祭拜完畢後請記得將垃圾分類帶走，共同維護環境整潔。建議提早到場，可避免車位不足或人潮擁擠。</p><p><br></p><p>現場備有遮陽棚、茶水站與簡易醫療服務，若攜帶長者參與請注意其身體狀況。活動當天如遇雨天，則順延至 8 月 20 日（星期二）同時段舉行。</p><p><br></p><p>歡迎里民踴躍參與，一同祈求風調雨順、里民平安。</p>','2025-07-20',2),(10,'第 41 屆空龍馬拉松報名開始',2,NULL,'<p>親愛的里民您好：</p><p><br></p><p>今年第 41 屆萬眾矚目的空瀧馬拉松，活動將於 8 月 3 日（星期日）清晨盛大登場！邀請您一同參與這項意義非凡的傳統。</p><p><br></p><p>本屆賽道延續往年傳統，串聯社區特色與自然景觀，沿途可欣賞空瀧地區豐富的綠意與河岸風光。途中設有多個補給站與志工應援點，歡迎您在奔跑的同時放慢腳步、看看沿路的美景。</p><p><br></p><p>本次馬拉松規劃三種組別：3 公里親子休閒組、10 公里健康組與 21 公里挑戰組。無論您是熱愛長跑的健將，還是想與家人共享運動樂趣的新手，都能找到適合自己的距離。</p><p><br></p><p>參與者皆可獲得專屬完賽禮包，內含紀念 T 恤、補給點心與參賽證書；順利完賽者還能獲得完賽獎狀，而各組名次前幾名則可獲頒精美獎牌與特別禮品，兼具榮耀與紀念價值。</p><p><br></p><p>即日起開放報名，截止日為 7 月 27 日。可至官網線上報名或親洽里辦公室登記。</p><p><br></p><p>無論您是第一次參加或已連續多年報名，都誠摯邀請您今年再次加入我們的行列，讓這場社區傳統延續下去，共同在奔跑中創造更多珍貴的回憶！</p>','2025-07-21',2),(11,'敬老愛心卡新制上路',1,NULL,'<p>親愛的里民您好：</p><p><br></p><p>桃園市政府自 8 月 1 日起調整「敬老愛心卡」使用方式，相關制度如下：</p><p><br></p><p>1. 使用範圍擴大</p><p><br></p><p>敬老卡與愛心卡除原本可搭乘公車、捷運、公路客運外，新增支援共享單車（YouBike）與部分超商小額支付，讓使用更加靈活。</p><p><br></p><p>2. 點數制度更新</p><p><br></p><p>每月固定點數仍為 480 點（相當於 480 元），未使用完畢之點數 將自次月起累積至 6 個月內有效，請妥善規劃使用。</p><p><br></p><p>3. 持卡條件不變</p><p><br></p><p>年滿 65 歲長者或設籍本市之身心障礙者仍可申辦。若您尚未辦卡，可攜帶身分證與健保卡至里辦公室協助辦理。</p><p><br></p><p>如對新制度有任何疑問，歡迎洽詢本里辦公室或撥打市政服務專線 1999 轉接社會局。</p><p><br></p><p>請里民留意變動資訊，並提醒身邊長輩一同享有便利服務！</p>','2025-07-31',2);
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
  `profile_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '/uploads/avatars/default_avatar.png',
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
INSERT INTO `users` VALUES ('user_account_001','Komod·Mayaw','測試','test123456','/uploads/avatars/default_avatar.png','0987654321','test1@gmail.com','H1212345678','1980-02-28','N',101,0,1,0,'2025-08-18 16:34:57'),('user_account_002','Arik·Mayaw','小艾','test223456','/uploads/avatars/default_avatar.png','0988456789','test2@gmail.com','A223456789','2005-05-10','M',101,1,1,0,'2025-08-19 08:47:13');
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
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_households`
--

LOCK TABLES `users_households` WRITE;
/*!40000 ALTER TABLE `users_households` DISABLE KEYS */;
INSERT INTO `users_households` VALUES (101,'桃園市中壢區空瀧浪里快樂街1479號8樓-3','user_account_001',1);
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

-- Dump completed on 2025-08-23  2:41:49
