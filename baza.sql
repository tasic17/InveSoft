-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for invesoft
CREATE DATABASE IF NOT EXISTS `invesoft` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `invesoft`;

-- Dumping structure for table invesoft.kategorije
CREATE TABLE IF NOT EXISTS `kategorije` (
  `kategorijaID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) NOT NULL,
  PRIMARY KEY (`kategorijaID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table invesoft.kategorije: ~5 rows (approximately)
INSERT INTO `kategorije` (`kategorijaID`, `naziv`) VALUES
	(1, 'Mobilni telefoni'),
	(2, 'Laptopovi i računari'),
	(3, 'Televizori i monitori'),
	(4, 'Audio oprema'),
	(5, 'Komponente i dodaci');

-- Dumping structure for table invesoft.korisnici
CREATE TABLE IF NOT EXISTS `korisnici` (
  `korisnikID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ime` varchar(64) NOT NULL,
  `prezime` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`korisnikID`),
  UNIQUE KEY `uq_korisnici_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table invesoft.korisnici: ~14 rows (approximately)
INSERT INTO `korisnici` (`korisnikID`, `ime`, `prezime`, `email`, `password`) VALUES
	(2, 'Anabela', 'Petrović', 'anabela.petrovic@invesoft.com', 'AnaPet#456'),
	(3, 'Marko', 'Nikolić', 'marko.nikolic@invesoft.com', 'MarkoN!789'),
	(4, 'Ivan', 'Popović', 'ivan.popovic@invesoft.com', 'IvaPoP@321'),
	(5, 'Jelena', 'Marković', 'jelena.markovic@invesoft.com', 'JeLeNa%654'),
	(6, 'Stefan', 'Kovačević', 'stefan.kovacevic@invesoft.com', 'SteFaN$876'),
	(7, 'Maja', 'Stojanović', 'maja.stojanovic@invesoft.com', 'MajA#112'),
	(8, 'Nikola', 'Ilić', 'nikola.ilic@invesoft.com', 'NikILiC!344'),
	(9, 'Dragana', 'Simić', 'dragana.simic@invesoft.com', 'DraSim@559'),
	(10, 'Aleksandar', 'Pavlović', 'aleksandar.pavlovic@invesoft.com', 'AleksaP&788'),
	(13, 'Goran', 'Suvacarev', 'goran@gmail.com', '$2y$10$bMosiRSZPlzxRCZyPPTyfujVOQH2rQ3KDlMCHAcWEc0xXNOMDAOQC'),
	(14, 'Luka', 'Tasic', 'admin@invesoft.com', '$2y$10$cG5JgWrQL/Y6F84yekvxAelXoFkxl9PssR/X1FI.PsfdJpv05rScS'),
	(15, 'Milos', 'Vidakovic', 'milos@gmail.com', '$2y$10$ieVASttBfxrKlvPO72hmCud8Ko.RZhORfHTgxLKY43q9ubbzWNW5W'),
	(19, 'Zeljko', 'Grbic', 'zeljko@gmail.com', '$2y$10$2rTZoFrtJ3y5AtpK4odV3.AnLFjysjnLC6KUBMf14VYs1DGTk2W1K'),
	(20, 'Andrija', 'Tasic', 'admin@gmail.com', '$2y$10$OmIHrWYje9o4NjBjV/Wva.kl7HcaIpf0hsaF26SRJdMA6feHk6bRG'),
	(21, 'Mita', 'Tasic', 'mita@gmail.com', '$2y$10$mzVcuhKDOe.EFm3NqEchQ.2n7kmKa2dGFPUqEWgO051n8UeW8J8PW');

-- Dumping structure for table invesoft.korisnik_role
CREATE TABLE IF NOT EXISTS `korisnik_role` (
  `korisnik_rolaID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `korisnikID` int(10) unsigned NOT NULL,
  `rolaID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`korisnik_rolaID`),
  KEY `fk_korisnik_role_korisnikID` (`korisnikID`),
  KEY `fk_korisnik_role_rolaID` (`rolaID`),
  CONSTRAINT `fk_korisnik_role_korisnikID` FOREIGN KEY (`korisnikID`) REFERENCES `korisnici` (`korisnikID`) ON UPDATE CASCADE,
  CONSTRAINT `fk_korisnik_role_rolaID` FOREIGN KEY (`rolaID`) REFERENCES `role` (`rolaID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table invesoft.korisnik_role: ~12 rows (approximately)
INSERT INTO `korisnik_role` (`korisnik_rolaID`, `korisnikID`, `rolaID`) VALUES
	(2, 2, 2),
	(3, 3, 2),
	(4, 4, 2),
	(5, 5, 2),
	(6, 6, 2),
	(7, 7, 2),
	(8, 8, 2),
	(9, 9, 2),
	(10, 10, 2),
	(14, 14, 1),
	(15, 15, 2),
	(20, 20, 1),
	(21, 21, 2);

-- Dumping structure for table invesoft.proizvodi
CREATE TABLE IF NOT EXISTS `proizvodi` (
  `proizvodID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(128) NOT NULL,
  `opis` varchar(255) NOT NULL,
  `cena` decimal(10,2) unsigned NOT NULL,
  `kategorijaID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`proizvodID`),
  KEY `fk_proizvodi_kategorijaID` (`kategorijaID`),
  CONSTRAINT `fk_proizvodi_kategorijaID` FOREIGN KEY (`kategorijaID`) REFERENCES `kategorije` (`kategorijaID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table invesoft.proizvodi: ~50 rows (approximately)
INSERT INTO `proizvodi` (`proizvodID`, `naziv`, `opis`, `cena`, `kategorijaID`) VALUES
	(1, 'iPhone 14', 'Apple pametni telefon sa 128GB memorije', 999.99, 1),
	(2, 'Samsung Galaxy S23', 'Samsung pametni telefon sa 256GB memorije', 899.99, 1),
	(3, 'Xiaomi Redmi Note 12', 'Xiaomi pametni telefon sa 128GB memorije', 299.99, 1),
	(4, 'Google Pixel 7', 'Google pametni telefon sa 128GB memorije', 799.99, 1),
	(5, 'OnePlus 11', 'OnePlus pametni telefon sa 256GB memorije', 699.99, 1),
	(6, 'Huawei P60 Pro', 'Huawei pametni telefon sa 512GB memorije', 1099.99, 1),
	(7, 'Sony Xperia 5', 'Sony pametni telefon sa 256GB memorije', 849.99, 1),
	(8, 'Oppo Find X6', 'Oppo pametni telefon sa 128GB memorije', 749.99, 1),
	(9, 'Vivo X90 Pro', 'Vivo pametni telefon sa 256GB memorije', 699.99, 1),
	(10, 'Motorola Edge 40', 'Motorola pametni telefon sa 128GB memorije', 499.99, 1),
	(11, 'Dell XPS 13', 'Dell ultrabook sa Intel i7 procesorom i 16GB RAM-a', 1299.99, 2),
	(12, 'MacBook Pro 14"', 'Apple laptop sa M2 čipom i 512GB SSD-a', 1999.99, 2),
	(13, 'HP Spectre x360', 'HP konvertibilni laptop sa 16GB RAM-a', 1499.99, 2),
	(14, 'Lenovo ThinkPad X1 Carbon', 'Lenovo poslovni laptop sa 1TB SSD-a', 1699.99, 2),
	(15, 'Asus ROG Zephyrus', 'Gaming laptop sa RTX 3080 grafičkom kartom', 2299.99, 2),
	(16, 'Acer Aspire 5', 'Acer laptop sa Intel i5 procesorom i 256GB SSD-a', 599.99, 2),
	(17, 'MSI Creator Z16', 'MSI laptop za kreativce sa 32GB RAM-a', 2599.99, 2),
	(18, 'Microsoft Surface Laptop 5', 'Microsoft laptop sa 512GB SSD-a', 1399.99, 2),
	(19, 'Razer Blade 15', 'Razer gaming laptop sa RTX 3070 grafičkom kartom', 1999.99, 2),
	(20, 'Huawei MateBook X Pro', 'Huawei laptop sa 16GB RAM-a', 1299.99, 2),
	(21, 'LG OLED C3', 'LG OLED 4K televizor sa HDR podrškom', 1499.99, 3),
	(22, 'Samsung QN90B', 'Samsung QLED televizor sa 120Hz osvežavanjem', 1299.99, 3),
	(23, 'Sony Bravia XR', 'Sony 4K televizor sa Dolby Vision podrškom', 1799.99, 3),
	(24, 'TCL 6-Series', 'TCL 4K televizor sa mini-LED tehnologijom', 899.99, 3),
	(25, 'Vizio M-Series Quantum', 'Vizio televizor sa Quantum Dot tehnologijom', 799.99, 3),
	(26, 'Philips Ambilight OLED', 'Philips OLED televizor sa Ambilight efektima', 1999.99, 3),
	(27, 'Dell UltraSharp U2723QE', 'Dell 27" 4K monitor sa IPS panelom', 699.99, 3),
	(28, 'LG UltraGear 27GP950', 'LG gaming monitor sa 144Hz osvežavanjem', 899.99, 3),
	(29, 'BenQ PD3220U', 'BenQ 32" 4K monitor za dizajnere', 1199.99, 3),
	(30, 'ASUS ProArt PA32UCX', 'ASUS profesionalni 4K monitor', 2399.99, 3),
	(31, 'Sony WH-1000XM5', 'Sony bežične slušalice sa poništavanjem buke', 399.99, 4),
	(32, 'Bose QuietComfort 45', 'Bose slušalice sa poništavanjem buke', 349.99, 4),
	(33, 'JBL Flip 6', 'JBL prenosivi Bluetooth zvučnik', 129.99, 4),
	(34, 'Apple AirPods Pro', 'Apple bežične slušalice sa aktivnim poništavanjem buke', 249.99, 4),
	(35, 'Samsung Galaxy Buds2 Pro', 'Samsung bežične slušalice sa 360 Audio podrškom', 229.99, 4),
	(36, 'Sennheiser HD 660S', 'Sennheiser slušalice za audiofile', 499.99, 4),
	(37, 'Logitech G Pro X', 'Logitech gaming slušalice sa mikrofonom', 129.99, 4),
	(38, 'Sonos One', 'Sonos pametni zvučnik sa Alexa podrškom', 199.99, 4),
	(39, 'Audio-Technica ATH-M50X', 'Audio-Technica profesionalne studijske slušalice', 149.99, 4),
	(40, 'Yamaha HS8', 'Yamaha studijski monitor zvučnik', 349.99, 4),
	(41, 'Intel Core i9-13900K', 'Intel procesor 13. generacije sa 24 jezgra', 599.99, 5),
	(42, 'AMD Ryzen 9 7950X', 'AMD procesor sa 16 jezgara i AM5 socketom', 549.99, 5),
	(43, 'NVIDIA GeForce RTX 4090', 'Najnovija NVIDIA grafička karta', 1599.99, 5),
	(44, 'Corsair Vengeance RGB Pro', '16GB DDR4 RAM sa RGB osvetljenjem', 99.99, 5),
	(45, 'Samsung 980 Pro SSD', 'Samsung NVMe SSD od 1TB', 149.99, 5),
	(46, 'ASUS ROG Strix Z790-E', 'ASUS matična ploča za gaming', 349.99, 5),
	(47, 'Cooler Master Hyper 212', 'Cooler za CPU sa odličnim performansama', 49.99, 5),
	(48, 'Logitech MX Master 3', 'Ergonomski bežični miš', 99.99, 5),
	(49, 'Corsair K95 RGB Platinum', 'Gaming tastatura sa mehaničkim tasterima', 199.99, 5),
	(50, 'NZXT H710i', 'Kućište za PC sa RGB osvetljenjem', 169.99, 5);

-- Dumping structure for table invesoft.promene_zaliha
CREATE TABLE IF NOT EXISTS `promene_zaliha` (
  `promenaID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proizvodID` int(10) unsigned NOT NULL,
  `korisnikID` int(10) unsigned NOT NULL,
  `datum_promene` datetime NOT NULL,
  `tip_promene` enum('Ulaz','Izlaz') NOT NULL,
  `kolicina` int(10) unsigned NOT NULL,
  PRIMARY KEY (`promenaID`),
  KEY `fk_promene_zaliha_proizvodID` (`proizvodID`),
  KEY `fk_promene_zaliha_korisnikID` (`korisnikID`),
  CONSTRAINT `fk_promene_zaliha_korisnikID` FOREIGN KEY (`korisnikID`) REFERENCES `korisnici` (`korisnikID`) ON UPDATE CASCADE,
  CONSTRAINT `fk_promene_zaliha_proizvodID` FOREIGN KEY (`proizvodID`) REFERENCES `proizvodi` (`proizvodID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table invesoft.promene_zaliha: ~33 rows (approximately)
INSERT INTO `promene_zaliha` (`promenaID`, `proizvodID`, `korisnikID`, `datum_promene`, `tip_promene`, `kolicina`) VALUES
	(4, 1, 14, '2024-11-23 14:28:11', 'Izlaz', 1),
	(5, 1, 14, '2024-11-23 14:40:05', 'Ulaz', 10),
	(6, 2, 14, '2024-11-23 14:56:52', 'Ulaz', 100),
	(7, 2, 14, '2024-11-24 17:18:51', 'Ulaz', 200),
	(8, 2, 14, '2024-11-24 17:18:57', 'Izlaz', 150),
	(9, 2, 14, '2024-11-24 17:19:03', 'Ulaz', 20),
	(10, 2, 14, '2024-11-24 17:19:10', 'Izlaz', 50),
	(11, 2, 14, '2024-11-24 17:31:23', 'Ulaz', 20),
	(12, 8, 14, '2024-11-24 17:32:25', 'Ulaz', 100),
	(13, 8, 14, '2024-11-24 17:32:32', 'Izlaz', 40),
	(14, 8, 14, '2024-11-24 17:32:40', 'Ulaz', 20),
	(15, 8, 14, '2024-11-24 17:32:45', 'Ulaz', 80),
	(16, 8, 14, '2024-11-24 17:32:56', 'Izlaz', 50),
	(17, 8, 14, '2024-11-24 17:36:35', 'Izlaz', 40),
	(18, 1, 13, '2024-11-24 17:50:07', 'Ulaz', 10),
	(19, 1, 19, '2024-11-24 22:54:54', 'Ulaz', 80),
	(20, 1, 19, '2024-11-24 22:55:04', 'Izlaz', 30),
	(21, 1, 19, '2024-11-24 22:55:10', 'Ulaz', 50),
	(22, 2, 15, '2024-11-24 22:58:42', 'Ulaz', 20),
	(23, 8, 14, '2024-11-24 23:30:06', 'Ulaz', 100),
	(24, 1, 15, '2024-11-24 23:37:01', 'Ulaz', 30),
	(25, 8, 15, '2024-11-24 23:37:06', 'Ulaz', 30),
	(26, 7, 15, '2024-11-24 23:37:11', 'Ulaz', 30),
	(27, 1, 21, '2024-11-25 11:06:01', 'Ulaz', 10),
	(28, 1, 21, '2024-11-25 11:06:06', 'Izlaz', 10),
	(30, 7, 21, '2024-11-25 11:21:29', 'Ulaz', 125),
	(31, 6, 21, '2024-11-25 11:21:39', 'Ulaz', 60),
	(32, 1, 21, '2024-11-25 11:21:49', 'Izlaz', 50),
	(34, 1, 15, '2024-11-25 19:24:06', 'Ulaz', 10),
	(35, 9, 15, '2024-11-25 19:24:17', 'Izlaz', 15),
	(36, 1, 14, '2024-11-26 15:40:37', 'Ulaz', 100),
	(37, 2, 14, '2024-11-26 15:40:43', 'Ulaz', 100),
	(38, 8, 14, '2024-11-26 15:40:51', 'Ulaz', 100);

-- Dumping structure for table invesoft.role
CREATE TABLE IF NOT EXISTS `role` (
  `rolaID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ime` varchar(64) NOT NULL,
  PRIMARY KEY (`rolaID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table invesoft.role: ~2 rows (approximately)
INSERT INTO `role` (`rolaID`, `ime`) VALUES
	(1, 'Administrator'),
	(2, 'Radnik');

-- Dumping structure for table invesoft.zalihe
CREATE TABLE IF NOT EXISTS `zalihe` (
  `zalihaID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proizvodID` int(10) unsigned NOT NULL,
  `kolicina` int(10) unsigned NOT NULL,
  PRIMARY KEY (`zalihaID`),
  KEY `fk_zalihe_proizvodID` (`proizvodID`),
  CONSTRAINT `fk_zalihe_proizvodID` FOREIGN KEY (`proizvodID`) REFERENCES `proizvodi` (`proizvodID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table invesoft.zalihe: ~50 rows (approximately)
INSERT INTO `zalihe` (`zalihaID`, `proizvodID`, `kolicina`) VALUES
	(1, 1, 290),
	(2, 2, 270),
	(3, 3, 87),
	(4, 4, 84),
	(5, 5, 72),
	(6, 6, 91),
	(7, 7, 180),
	(8, 8, 330),
	(9, 9, 50),
	(10, 10, 82),
	(11, 11, 67),
	(12, 12, 32),
	(13, 13, 71),
	(14, 14, 62),
	(15, 15, 68),
	(16, 16, 84),
	(17, 17, 16),
	(18, 18, 50),
	(19, 19, 75),
	(20, 20, 77),
	(21, 21, 13),
	(22, 22, 86),
	(23, 23, 95),
	(24, 24, 4),
	(25, 25, 76),
	(26, 26, 88),
	(27, 27, 85),
	(28, 28, 8),
	(29, 29, 72),
	(30, 30, 87),
	(31, 31, 78),
	(32, 32, 58),
	(33, 33, 12),
	(34, 34, 24),
	(35, 35, 59),
	(36, 36, 30),
	(37, 37, 36),
	(38, 38, 90),
	(39, 39, 81),
	(40, 40, 45),
	(41, 41, 37),
	(42, 42, 25),
	(43, 43, 31),
	(44, 44, 83),
	(45, 45, 67),
	(46, 46, 57),
	(47, 47, 65),
	(48, 48, 56),
	(49, 49, 50),
	(50, 50, 20);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
