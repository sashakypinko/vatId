-- MySQL dump 10.13  Distrib 8.0.28, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: vat
-- ------------------------------------------------------
-- Server version	8.0.28

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
-- Table structure for table `xst_vat_id_check`
--

DROP TABLE IF EXISTS `xst_vat_id_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xst_vat_id_check` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `UstId_1` varchar(11) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `UstId_2` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Firmenname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Ort` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `PLZ` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Strasse` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lastChange` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `forceReCheck` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xst_vat_id_check`
--

LOCK TABLES `xst_vat_id_check` WRITE;
/*!40000 ALTER TABLE `xst_vat_id_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `xst_vat_id_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xst_vat_id_check_request_logs`
--

DROP TABLE IF EXISTS `xst_vat_id_check_request_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xst_vat_id_check_request_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `UstId_1` varchar(11) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `UstId_2` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Druck` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Erg_PLZ` varchar(1) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Ort` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Datum` date DEFAULT NULL,
  `PLZ` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Erg_Ort` varchar(1) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Uhrzeit` time DEFAULT NULL,
  `Erg_Name` varchar(1) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Gueltig_ab` date DEFAULT NULL,
  `Gueltig_bis` date DEFAULT NULL,
  `Strasse` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Firmenname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Erg_Str` varchar(1) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ErrorCode` int DEFAULT NULL,
  `userID` bigint NOT NULL,
  `lastChange` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `validationJsonResult` text COLLATE utf8mb4_general_ci,
  `validVatId` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `xst_vat_id_check_request_logs_userID_index` (`userID`),
  CONSTRAINT `xst_vat_id_check_request_logs_xst_vat_id_check_id_fk` FOREIGN KEY (`userID`) REFERENCES `xst_vat_id_check` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xst_vat_id_check_request_logs`
--

LOCK TABLES `xst_vat_id_check_request_logs` WRITE;
/*!40000 ALTER TABLE `xst_vat_id_check_request_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `xst_vat_id_check_request_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-03-17 16:09:22
