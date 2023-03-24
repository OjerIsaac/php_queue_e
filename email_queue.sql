SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `queue`
--

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

DROP TABLE IF EXISTS `email_queue`;
CREATE TABLE email_queue (
  id INT NOT NULL AUTO_INCREMENT,
  to_address VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('queued', 'dispatched', 'failed') NOT NULL,
  created_at DATETIME NOT NULL,
  dispatched_at DATETIME DEFAULT NULL,
  failed_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------