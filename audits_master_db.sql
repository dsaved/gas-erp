-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2021 at 09:57 PM
-- Server version: 10.4.13-MariaDB
-- PHP Version: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `audits_master_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `create_log` (IN `spAcc_form_id` INT(20), IN `spDebit_amount` DECIMAL(62,2), IN `spDebit_date` DATE, IN `spDescription_from` TEXT, IN `spStatement_id_from` INT(255), IN `spAcc_to_id` INT(20), IN `spCredit_amount` DECIMAL(62,2), IN `spCredit_date` DATE, IN `spDescription_to` TEXT, IN `spStatement_id_to` INT(255), IN `spIntval` INT(255))  BEGIN

SELECT name,acc_num1,acc_num2, bank 
INTO @acc_name1, @acc_num1_from,@acc_num2_from, @bid1 
FROM accounts WHERE id=spAcc_form_id LIMIT 1;
SELECT name INTO  @bname1 FROM banks WHERE id=@bid1 LIMIT 1;

if @acc_num1_from is NULL OR @acc_num1_from='' then
    SET @acc_num1_from = @acc_num2_from;
end if;

SELECT name,acc_num1,acc_num2, bank 
INTO @acc_name2, @acc_num1_to,@acc_num2_to, @bid2 
FROM accounts WHERE id=spAcc_to_id LIMIT 1;
SELECT name INTO  @bname2 FROM banks WHERE id=@bid2 LIMIT 1;

if @acc_num1_to is NULL OR @acc_num1_to='' then
    SET @acc_num1_to = @acc_num2_to;
end if;

INSERT INTO `audits_logs`(`account_name_from`, `account_id_from`, `account_number_from`, `debit_amount`, `debit_date`, `description_from`, `bank_from`, `bank_id_from`, `statement_id_from`, `account_name_to`, `account_id_to`, `account_number_to`, `credit_amount`, `credit_date`, `description_to`, `bank_to`, `bank_id_to`, `statement_id_to`,`intval`)
VALUES (@acc_name1,spAcc_form_id,@acc_num1_from,spDebit_amount,spDebit_date,spDescription_from,@bname1,@bid1,spStatement_id_from,
@acc_name2,spAcc_to_id,@acc_num1_to,spCredit_amount,spCredit_date,spDescription_to,@bname2,@bid2,spStatement_id_to,spIntval);
UPDATE statements SET statements.status='locked',statements.locked_to = spAcc_form_id WHERE statements.id = spStatement_id_to;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `file_export_status_logs` (IN `spID` INT(20), IN `spCurrent` INT(255), IN `spTotal` INT(255), IN `spDescription` TEXT, IN `spStatus` VARCHAR(255), IN `spProcessing` VARCHAR(20))  BEGIN
SET @processStatus = spProcessing;
SET @processStatus = spProcessing;

if @processStatus is NULL OR @processStatus='' then
    SET @processStatus = 'processing';
end if;

UPDATE file_export_status_logs SET current_count=spCurrent, total_account=spTotal, description=spDescription, status=spStatus, processing=@processStatus WHERE id = spID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `flag_transaction` (IN `spAccount_from` INT(20), IN `spOffset_account` VARCHAR(255), IN `spStatement_id` INT(255), IN `spAmount` DECIMAL(62,2), IN `spIntval` INT(255))  NO SQL
BEGIN
INSERT INTO unauthorized_transfers(account_from, offset_account, statement_id, amount,intval)
VALUES(spAccount_from, spOffset_account, spStatement_id, spAmount,spIntval);
UPDATE statements SET statements.status='flagged'WHERE statements.id = spStatement_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `rm_bank_reconcilation` (IN `SPIDS` TEXT)  BEGIN
Update statements set `status`='',`locked_to`=0 WHERE `account_id` IN (SELECT child_account FROM asinged_account WHERE FIND_IN_SET(main_account, SPIDS)) AND FIND_IN_SET(locked_to, SPIDS);
Update statements set `status`='' WHERE FIND_IN_SET(account_id, SPIDS) AND `locked_to`=0;
Update accounts set `reconciled`=0 WHERE FIND_IN_SET(id, SPIDS);
DELETE FROM unauthorized_transfers WHERE FIND_IN_SET(unauthorized_transfers.account_from, SPIDS);
DELETE FROM comments WHERE FIND_IN_SET(account_from, SPIDS);
DELETE FROM audits_logs WHERE FIND_IN_SET(audits_logs.account_id_from, SPIDS);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_file_export` (IN `spID` INT(20), IN `spCurrent` INT(255), IN `spTotal` INT(255), IN `spDescription` TEXT, IN `spStatus` VARCHAR(255), IN `spProcessing` VARCHAR(20))  BEGIN
SET @processStatus = spProcessing;
SET @processStatus = spProcessing;

if @processStatus is NULL OR @processStatus='' then
    SET @processStatus = 'processing';
end if;

UPDATE file_export_status SET current_count=spCurrent, total_account=spTotal, description=spDescription, status=spStatus, processing=@processStatus WHERE id = spID;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(255) NOT NULL,
  `name` text NOT NULL,
  `acc_num1` varchar(15) NOT NULL,
  `acc_num2` varchar(15) NOT NULL,
  `owner` int(255) NOT NULL,
  `bank` int(255) NOT NULL,
  `category` int(255) NOT NULL DEFAULT 0,
  `bank_type` varchar(20) NOT NULL,
  `status` varchar(8) NOT NULL DEFAULT 'Active',
  `date_inactive` datetime DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `reconciled` int(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `acc_num1`, `acc_num2`, `owner`, `bank`, `category`, `bank_type`, `status`, `date_inactive`, `created`, `reconciled`) VALUES
(1, 'TMAIN 2 2016-2020', '1018631479537', '', 1, 1, 0, 'Bank Of Ghana', 'Active', NULL, '2020-09-24 17:11:27', 0),
(2, 'TMAIN 4 2016-2020', 'GHS144130035', '', 1, 4, 0, 'Other Banks', 'Active', NULL, '2020-09-24 17:12:35', 1),
(3, 'STATUTORY  FUND ACCT_2015-2020', '1223434343345', '', 2, 1, 0, 'Bank Of Ghana', 'Active', NULL, '2020-09-24 17:13:16', 0),
(4, 'SPECIAL PROJ. ACCT_ 2015-2020', '27817672671', '', 3, 2, 0, 'Other Banks', 'Active', NULL, '2020-09-24 17:23:15', 0),
(5, 'LOAN REPAYMENT INTEREST-2015-2020 ', '3232234343434', '', 2, 2, 1, 'Other Banks', 'Active', NULL, '2020-09-24 17:24:31', 0),
(6, 'LOAN REPAYEMENT PRINCIPAL 2015-2020', '323432243232', '', 2, 4, 0, 'Other Banks', 'Active', NULL, '2020-09-24 17:25:00', 0),
(7, 'GOODS AND SERVICE 2015-2020', '2343342343423', '', 3, 4, 5, 'Other Banks', 'Active', NULL, '2020-09-24 17:25:24', 0),
(8, 'COMPENSATION OF EMPLOYEES 2015-2020', '1343422212111', '', 3, 1, 5, 'Bank Of Ghana', 'Active', NULL, '2020-09-24 17:25:42', 0),
(9, 'CENTRAL SECURITIES DEPOSITORY 1018531447018', '1232345345675', '', 1, 1, 0, 'Bank Of Ghana', 'Active', NULL, '2020-09-24 17:26:01', 0),
(10, 'CAPITAL EXPENDITURE 2015-2020_', '1232343332345', '', 2, 1, 5, 'Bank Of Ghana', 'Active', NULL, '2020-09-24 17:26:27', 0);

-- --------------------------------------------------------

--
-- Table structure for table `accounts_bog`
--

CREATE TABLE `accounts_bog` (
  `id` bigint(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `number` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `account_balance`
--

CREATE TABLE `account_balance` (
  `id` bigint(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `amount` decimal(62,2) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `account_balance`
--

INSERT INTO `account_balance` (`id`, `account_id`, `amount`, `date`) VALUES
(1, 1, '-2171978947.46', '0000-00-00'),
(11, 6, '123417489.72', '2020-09-08'),
(12, 7, '17106539.71', '2020-08-26'),
(13, 8, '118120303.79', '2020-09-08'),
(14, 9, '2103513.89', '2020-08-31'),
(15, 10, '1692512.89', '0009-01-20'),
(16, 3, '40408918.81', '2020-09-01'),
(17, 4, '19249159.10', '2020-09-01'),
(18, 5, '0.00', '0000-00-00'),
(38922, 2, '7783421.81', '2020-08-31');

-- --------------------------------------------------------

--
-- Table structure for table `account_category`
--

CREATE TABLE `account_category` (
  `id` int(255) NOT NULL,
  `name` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `account_category`
--

INSERT INTO `account_category` (`id`, `name`, `created`) VALUES
(1, 'BOG CAT', '2020-09-22 11:10:48'),
(4, 'OTERS CAT', '2020-09-22 11:16:05'),
(5, 'ACCESS CAT', '2020-10-07 07:34:15');

-- --------------------------------------------------------

--
-- Table structure for table `asinged_account`
--

CREATE TABLE `asinged_account` (
  `id` int(255) NOT NULL,
  `main_account` int(255) NOT NULL,
  `child_account` int(255) NOT NULL,
  `bank_type` varchar(15) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `asinged_account`
--

INSERT INTO `asinged_account` (`id`, `main_account`, `child_account`, `bank_type`, `created`) VALUES
(7, 7, 6, 'Other Banks', '2020-11-15 10:06:47'),
(8, 6, 10, 'Other Banks', '2020-11-15 11:53:47'),
(9, 10, 9, 'Bank Of Ghana', '2020-11-18 13:21:55'),
(10, 1, 10, 'Bank Of Ghana', '2020-11-19 15:46:32'),
(11, 1, 9, 'Bank Of Ghana', '2020-11-19 15:46:32'),
(12, 1, 8, 'Bank Of Ghana', '2020-11-19 15:46:32'),
(13, 1, 3, 'Bank Of Ghana', '2020-11-19 15:46:32'),
(14, 2, 10, 'Other Banks', '2020-11-19 15:49:08'),
(15, 2, 9, 'Other Banks', '2020-11-19 15:49:08'),
(16, 2, 8, 'Other Banks', '2020-11-19 15:49:08'),
(17, 2, 7, 'Other Banks', '2020-11-19 15:49:08'),
(18, 2, 6, 'Other Banks', '2020-11-19 15:49:08'),
(19, 2, 5, 'Other Banks', '2020-11-19 15:49:08'),
(20, 2, 4, 'Other Banks', '2020-11-19 15:49:08'),
(21, 2, 3, 'Other Banks', '2020-11-19 15:49:08'),
(22, 2, 1, 'Other Banks', '2020-11-19 15:49:08');

-- --------------------------------------------------------

--
-- Table structure for table `audits_logs`
--

CREATE TABLE `audits_logs` (
  `id` int(20) NOT NULL,
  `account_name_from` varchar(300) NOT NULL,
  `account_id_from` int(20) NOT NULL,
  `account_number_from` varchar(15) NOT NULL,
  `debit_amount` decimal(62,2) NOT NULL,
  `debit_date` date NOT NULL,
  `description_from` text NOT NULL,
  `bank_from` varchar(200) NOT NULL,
  `bank_id_from` int(20) NOT NULL,
  `statement_id_from` int(255) NOT NULL,
  `account_name_to` varchar(300) NOT NULL,
  `account_id_to` int(20) NOT NULL,
  `account_number_to` varchar(15) NOT NULL,
  `credit_amount` decimal(62,2) NOT NULL,
  `credit_date` date NOT NULL,
  `description_to` text NOT NULL,
  `bank_to` varchar(200) NOT NULL,
  `bank_id_to` int(20) NOT NULL,
  `statement_id_to` int(255) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `intval` int(255) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `authentication`
--

CREATE TABLE `authentication` (
  `id` int(255) NOT NULL,
  `auth` text NOT NULL,
  `user_id` int(255) NOT NULL,
  `requests` int(255) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `authentication`
--

INSERT INTO `authentication` (`id`, `auth`, `user_id`, `requests`) VALUES
(1, '0a6b64c97f6a', 6, 178902),
(2, '4edaee7bb5c6', 7, 126),
(3, 'e087cb81383e', 8, 534),
(4, '773368dc7d9f', 9, 164),
(5, '5a5603c22305', 1, 10769),
(6, 'edc73a5f6b19', 3, 0),
(7, 'a1d16aa8a191', 2, 0),
(8, '6f0ce020a73a', 5, 0),
(9, '9393e13482ee', 4, 74);

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` int(255) NOT NULL,
  `name` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `name`, `created`) VALUES
(1, 'BOG', '2020-09-22 11:10:48'),
(2, 'Access Bank', '2020-09-22 11:15:46'),
(4, 'Eco Bank', '2020-09-22 11:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `statement_id` int(11) NOT NULL,
  `user_id` int(20) NOT NULL,
  `account_from` int(20) NOT NULL,
  `type` varchar(50) NOT NULL,
  `reviewedby` varchar(5) NOT NULL,
  `account_name` varchar(300) DEFAULT NULL,
  `account_number` varchar(300) DEFAULT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `file_download`
--

CREATE TABLE `file_download` (
  `id` int(255) NOT NULL,
  `filename` varchar(300) NOT NULL,
  `link` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `file_download`
--

INSERT INTO `file_download` (`id`, `filename`, `link`, `created`) VALUES
(7, 'ukjbhkjh-1605727616', '../api/downloads/docs/ukjbhkjh-1605727616.xlsx', '2020-11-18 19:27:00'),
(8, 'sddadas-1605727720', '../api/downloads/docs/sddadas-1605727720.xlsx', '2020-11-18 19:28:43'),
(9, 'sddcczd-1605727827', '../api/downloads/docs/sddcczd-1605727827.xlsx', '2020-11-18 19:30:31'),
(10, 'sddfsdafs-1605727850', '../api/downloads/docs/sddfsdafs-1605727850.xlsx', '2020-11-18 19:31:06'),
(11, 'sdasfasdfdas-1605728153', '../api/downloads/docs/sdasfasdfdas-1605728153.xlsx', '2020-11-18 19:35:59'),
(12, 'asds-1605728192', '../api/downloads/docs/asds-1605728192.xlsx', '2020-11-18 19:36:51'),
(13, 'AUDI lOG-1605801686', '../api/downloads/docs/AUDI lOG-1605801686.xlsx', '2020-11-19 16:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `file_export_status`
--

CREATE TABLE `file_export_status` (
  `id` int(255) NOT NULL,
  `jobid` varchar(255) NOT NULL,
  `ids` text NOT NULL,
  `path` text NOT NULL,
  `filename` varchar(200) NOT NULL,
  `current_count` int(255) NOT NULL,
  `total_account` int(15) NOT NULL,
  `status` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `processing` varchar(12) NOT NULL DEFAULT 'pending',
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `file_export_status`
--

INSERT INTO `file_export_status` (`id`, `jobid`, `ids`, `path`, `filename`, `current_count`, `total_account`, `status`, `description`, `processing`, `created`) VALUES
(1, 'EXP1605727616', '6', '', 'ukjbhkjh-1605727616', 477, 477, 'completed', 'File ready for download - 19:27:0', 'completed', '2020-11-18 19:26:56'),
(2, 'EXP1605727720', '6', '', 'sddadas-1605727720', 477, 477, 'completed', 'File ready for download - 19:28:43', 'completed', '2020-11-18 19:28:40'),
(3, 'EXP1605727827', '6', '', 'sddcczd-1605727827', 477, 477, 'completed', 'File ready for download - 19:30:31', 'completed', '2020-11-18 19:30:27'),
(4, 'EXP1605727850', '10', '', 'sddfsdafs-1605727850', 3498, 3498, 'completed', 'File ready for download - 19:31:6', 'completed', '2020-11-18 19:30:50'),
(5, 'EXP1605728153', '6', '', 'sdasfasdfdas-1605728153', 477, 477, 'completed', 'File ready for download - 19:35:59', 'completed', '2020-11-18 19:35:53'),
(6, 'EXP1605728192', '6,10', '', 'asds-1605728192', 3975, 3975, 'completed', 'File ready for download - 19:36:51', 'completed', '2020-11-18 19:36:32');

-- --------------------------------------------------------

--
-- Table structure for table `file_export_status_logs`
--

CREATE TABLE `file_export_status_logs` (
  `id` int(255) NOT NULL,
  `jobid` varchar(255) NOT NULL,
  `ids` text NOT NULL,
  `path` text NOT NULL,
  `filename` varchar(200) NOT NULL,
  `current_count` int(255) NOT NULL,
  `total_account` int(15) NOT NULL,
  `status` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `processing` varchar(12) NOT NULL DEFAULT 'pending',
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `file_export_status_logs`
--

INSERT INTO `file_export_status_logs` (`id`, `jobid`, `ids`, `path`, `filename`, `current_count`, `total_account`, `status`, `description`, `processing`, `created`) VALUES
(10, 'EXP1605801686', '2', '', 'AUDI lOG-1605801686', 18, 18, 'completed', 'File ready for download - 16:1:28', 'completed', '2020-11-19 16:01:26');

-- --------------------------------------------------------

--
-- Table structure for table `file_upload_receipt_status`
--

CREATE TABLE `file_upload_receipt_status` (
  `id` int(255) NOT NULL,
  `path` text NOT NULL,
  `account_id` int(50) NOT NULL,
  `status` varchar(300) NOT NULL DEFAULT 'Reading File Content',
  `total` int(255) NOT NULL DEFAULT 0,
  `current` int(255) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `processing` varchar(50) NOT NULL DEFAULT 'pending',
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `file_upload_receipt_status`
--

INSERT INTO `file_upload_receipt_status` (`id`, `path`, `account_id`, `status`, `total`, `current`, `description`, `processing`, `created`) VALUES
(1, 'uploads/docs/20201111OMCs-Receipting-Template79.xlsx', 2, 'completed', 0, 0, 'All done - 21:34:27', 'completed', '2020-11-11 21:30:45'),
(2, 'uploads/docs/20201111OMCs-Receipting-Template98.xlsx', 2, 'completed', 0, 0, 'All done - 21:36:48', 'completed', '2020-11-11 21:36:46'),
(3, 'uploads/docs/20201111OMCs-Receipting-Template48.xlsx', 2, 'completed', 0, 0, 'All done - 21:38:55', 'completed', '2020-11-11 21:38:53'),
(4, 'uploads/docs/20201111OMCs-Receipting-Template55.xlsx', 2, 'completed', 0, 0, 'All done - 21:46:49', 'completed', '2020-11-11 21:46:47'),
(5, 'uploads/docs/20201111OMCs-Receipting-Template94.xlsx', 2, 'completed', 0, 0, 'All done - 21:48:9', 'completed', '2020-11-11 21:48:07'),
(6, 'uploads/docs/20201111OMCs-Receipting-Template40.xlsx', 2, 'completed', 0, 0, 'All done - 21:49:14', 'completed', '2020-11-11 21:49:12'),
(7, 'uploads/docs/20201111OMCs-Receipting-Template71.xlsx', 2, 'completed', 0, 0, 'All done - 21:50:35', 'completed', '2020-11-11 21:50:35'),
(8, 'uploads/docs/20201111OMCs-Receipting-Template50.xlsx', 2, 'completed', 0, 0, 'All done - 21:51:6', 'completed', '2020-11-11 21:51:04'),
(9, 'uploads/docs/20201111OMCs-Receipting-Template18.xlsx', 2, 'error', 7, 1, 'job error: cannot insert record ', 'completed', '2020-11-11 21:53:04'),
(10, 'uploads/docs/20201111OMCs-Receipting-Template53.xlsx', 2, 'completed', 7, 7, 'All done - 21:54:21', 'completed', '2020-11-11 21:54:20'),
(11, 'uploads/docs/20201111OMCs-Receipting-Template61.xlsx', 2, 'completed', 7, 7, 'All done - 21:58:38', 'completed', '2020-11-11 21:58:36'),
(12, 'uploads/docs/20201111OMCs-Receipting-Template95.xlsx', 2, 'completed', 7, 7, 'All done - 22:1:6', 'completed', '2020-11-11 22:01:05'),
(13, 'uploads/docs/20201112OMCs-Receipting-Template0.xlsx', 2, 'completed', 7, 7, 'All done - 10:31:35', 'completed', '2020-11-12 10:31:35'),
(14, 'uploads/docs/20201114OMCs-Receipting-Template68.xlsx', 2, 'completed', 7, 7, 'All done - 9:40:16', 'completed', '2020-11-14 09:39:32'),
(15, 'uploads/docs/20201114OMCs-Receipting-Template93.xlsx', 2, 'completed', 7, 7, 'All done - 9:41:8', 'completed', '2020-11-14 09:41:07'),
(16, 'uploads/docs/20201114OMCs-Receipting-Template89.xlsx', 2, 'completed', 7, 7, 'All done - 9:41:16', 'completed', '2020-11-14 09:41:14'),
(17, 'uploads/docs/20201114OMCs-Receipting-Template22.xlsx', 2, 'completed', 7, 7, 'All done - 9:41:22', 'completed', '2020-11-14 09:41:21'),
(18, 'uploads/docs/20210108OMCs-Receipting-Template71.xlsx', 2, 'Error', 0, 0, 'error occured: proccessing file eror- 11:7:55', 'completed', '2021-01-08 11:07:53'),
(19, 'C:/xampp/htdocs/gas/api/uploads/docs/20210108OMCs-Receipting-Template64.xlsx', 2, 'completed', 7, 7, 'All done - 11:15:41', 'completed', '2021-01-08 11:15:38');

-- --------------------------------------------------------

--
-- Table structure for table `file_upload_status`
--

CREATE TABLE `file_upload_status` (
  `id` int(255) NOT NULL,
  `path` text NOT NULL,
  `account_id` int(50) NOT NULL,
  `status` varchar(300) NOT NULL DEFAULT 'Reading File Content',
  `total` int(255) NOT NULL DEFAULT 0,
  `current` int(255) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `processing` varchar(50) NOT NULL DEFAULT 'pending',
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `file_upload_status`
--

INSERT INTO `file_upload_status` (`id`, `path`, `account_id`, `status`, `total`, `current`, `description`, `processing`, `created`) VALUES
(1, 'uploads/docs/20201114LOAN-REPAYEMENT-PRINCIPAL-2015-202049.xlsx', 6, 'completed', 2873, 2873, 'All done - 19:12:12', 'completed', '2020-11-14 19:09:11'),
(2, 'uploads/docs/20201118CAPITAL-EXPENDITURE-2015-2020_16.xlsx', 10, 'completed', 4239, 4239, 'All done - 13:5:33', 'completed', '2020-11-18 13:05:01'),
(3, 'uploads/docs/20201119CENTRAL-SECURITIES-DEPOSITORY-10185314470186.xlsx', 9, 'completed', 5499, 5499, 'All done - 15:7:58', 'completed', '2020-11-19 15:07:23'),
(4, 'uploads/docs/20201119COMPENSATION-OF-EMPLOYEES-2015-20207.xlsx', 8, 'completed', 6949, 6949, 'All done - 15:9:12', 'completed', '2020-11-19 15:08:31'),
(5, 'uploads/docs/20201119GOODS-AND-SERVICE-2015-20209.xlsx', 7, 'completed', 15802, 15802, 'All done - 15:10:58', 'completed', '2020-11-19 15:09:26'),
(6, 'uploads/docs/20201119LOAN-REPAYMENT-INTEREST-2015-2020-66.xlsx', 5, 'completed', 538, 538, 'All done - 15:11:26', 'completed', '2020-11-19 15:11:19'),
(7, 'uploads/docs/20201119SPECIAL-PROJ.-ACCT_-2015-20207.xlsx', 4, 'completed', 230, 230, 'All done - 15:11:59', 'completed', '2020-11-19 15:11:56'),
(8, 'uploads/docs/20201119STATUTORY--FUND-ACCT_2015-202064.xlsx', 3, 'completed', 358, 358, 'All done - 15:12:57', 'completed', '2020-11-19 15:12:53'),
(9, 'uploads/docs/20201119TMAIN-2-2016-202016.xlsx', 1, 'completed', 2415, 2415, 'All done - 15:13:32', 'completed', '2020-11-19 15:13:17'),
(10, 'uploads/docs/20201119TMAIN-4-2016-202076.xlsx', 2, 'completed', 32613, 32613, 'All done - 15:19:9', 'completed', '2020-11-19 15:15:53'),
(11, 'uploads/docs/20201119CAPITAL-EXPENDITURE-2015-2020_53.xlsx', 10, 'completed', 4239, 4239, 'All done - 15:39:51', 'completed', '2020-11-19 15:39:04'),
(12, 'uploads/docs/20201119CAPITAL-EXPENDITURE-2015-2020_70.xlsx', 10, 'completed', 4238, 4238, 'All done - 15:43:59', 'completed', '2020-11-19 15:43:33'),
(13, 'uploads/docs/20201119LOAN-REPAYMENT-INTEREST-2015-2020-14.xlsx', 5, 'completed', 538, 538, 'All done - 15:45:59', 'completed', '2020-11-19 15:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `omc`
--

CREATE TABLE `omc` (
  `id` int(255) NOT NULL,
  `name` varchar(300) NOT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `location` varchar(300) DEFAULT NULL,
  `region` varchar(300) DEFAULT NULL,
  `district` varchar(300) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `reconciled` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `omc`
--

INSERT INTO `omc` (`id`, `name`, `phone`, `email`, `location`, `region`, `district`, `created`, `reconciled`) VALUES
(2, 'Dsaved', '+233573868', 'dsaved8291@gmail.com', 'Ghana', 'Grater-Accra', '', '2020-11-08 22:11:32', 0);

-- --------------------------------------------------------

--
-- Table structure for table `omc_mapped_account`
--

CREATE TABLE `omc_mapped_account` (
  `id` int(255) NOT NULL,
  `main_account` int(255) NOT NULL,
  `child_account` int(255) NOT NULL,
  `bank_type` varchar(15) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `omc_mapped_account`
--

INSERT INTO `omc_mapped_account` (`id`, `main_account`, `child_account`, `bank_type`, `created`) VALUES
(3, 2, 10, 'omc', '2020-11-14 09:25:03'),
(4, 2, 9, 'omc', '2020-11-14 09:25:03');

-- --------------------------------------------------------

--
-- Table structure for table `omc_receipt`
--

CREATE TABLE `omc_receipt` (
  `id` bigint(255) NOT NULL,
  `omc_id` bigint(255) NOT NULL,
  `bank` varchar(300) NOT NULL,
  `date` date NOT NULL,
  `declaration_number` varchar(300) NOT NULL,
  `receipt_number` varchar(300) NOT NULL,
  `mode_of_payment` varchar(300) NOT NULL,
  `amount` decimal(62,2) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT '',
  `bank_id` bigint(255) NOT NULL DEFAULT 0,
  `created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
PARTITION BY RANGE (`id`)
(
PARTITION p0 VALUES LESS THAN (300000) ENGINE=InnoDB,
PARTITION p1 VALUES LESS THAN (600000) ENGINE=InnoDB,
PARTITION p2 VALUES LESS THAN (900000) ENGINE=InnoDB,
PARTITION p3 VALUES LESS THAN (1200000) ENGINE=InnoDB,
PARTITION p4 VALUES LESS THAN (1500000) ENGINE=InnoDB,
PARTITION p5 VALUES LESS THAN (1800000) ENGINE=InnoDB,
PARTITION p6 VALUES LESS THAN (2100000) ENGINE=InnoDB,
PARTITION p7 VALUES LESS THAN (2400000) ENGINE=InnoDB,
PARTITION p8 VALUES LESS THAN (2700000) ENGINE=InnoDB,
PARTITION p9 VALUES LESS THAN (3000000) ENGINE=InnoDB,
PARTITION p10 VALUES LESS THAN (3300000) ENGINE=InnoDB,
PARTITION p11 VALUES LESS THAN (3600000) ENGINE=InnoDB,
PARTITION p12 VALUES LESS THAN (3900000) ENGINE=InnoDB,
PARTITION p13 VALUES LESS THAN (4200000) ENGINE=InnoDB,
PARTITION p14 VALUES LESS THAN (4500000) ENGINE=InnoDB,
PARTITION p15 VALUES LESS THAN (4800000) ENGINE=InnoDB,
PARTITION p16 VALUES LESS THAN (5100000) ENGINE=InnoDB,
PARTITION p17 VALUES LESS THAN (5400000) ENGINE=InnoDB,
PARTITION p18 VALUES LESS THAN (5700000) ENGINE=InnoDB,
PARTITION p19 VALUES LESS THAN (6000000) ENGINE=InnoDB,
PARTITION p20 VALUES LESS THAN (6300000) ENGINE=InnoDB,
PARTITION p21 VALUES LESS THAN (6600000) ENGINE=InnoDB,
PARTITION p22 VALUES LESS THAN (6900000) ENGINE=InnoDB,
PARTITION p23 VALUES LESS THAN (7200000) ENGINE=InnoDB,
PARTITION p24 VALUES LESS THAN (7500000) ENGINE=InnoDB,
PARTITION p25 VALUES LESS THAN (7800000) ENGINE=InnoDB,
PARTITION p26 VALUES LESS THAN (8200000) ENGINE=InnoDB
);

--
-- Dumping data for table `omc_receipt`
--

INSERT INTO `omc_receipt` (`id`, `omc_id`, `bank`, `date`, `declaration_number`, `receipt_number`, `mode_of_payment`, `amount`, `status`, `bank_id`, `created`) VALUES
(1, 2, 'BANK OF GHANA', '0000-00-00', 'sdfdsdfsfdfd', '902883933', 'BANK DEPOSIT', '3982753.00', '', 0, '2021-01-08'),
(2, 2, 'GCB', '0000-00-00', '3wefsdfsd', '3233192', 'BANK DEPOSIT', '2189291.00', '', 0, '2021-01-08'),
(3, 2, 'BANK OF GHANA', '0000-00-00', 'dffdfdsfsdsf34334', '837923873', 'BANK DEPOSIT', '26537.00', '', 0, '2021-01-08'),
(4, 2, 'GCB', '0000-00-00', '34rwefscdcx', '9038772831', 'TRANSFER', '8302989.00', '', 0, '2021-01-08'),
(5, 2, 'ECO BANK', '0000-00-00', '4343434fasd', '31323313', 'BANK DEPOSIT', '13232753.00', '', 0, '2021-01-08'),
(6, 2, 'GCB', '0000-00-00', 'ff43443rfasad', '212312123', 'TRANSFER', '9287.00', '', 0, '2021-01-08'),
(7, 2, 'BANK OF GHANA', '0000-00-00', '44334rtsdfssr434', '3224454322', 'TRANSFER', '90.00', '', 0, '2021-01-08');

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` int(255) NOT NULL,
  `name` varchar(300) NOT NULL,
  `email` varchar(300) DEFAULT NULL,
  `phone` varchar(12) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `email`, `phone`, `created`) VALUES
(1, 'Direct Tax', 'directtax@site.com', '0573868330', '2020-09-24 17:05:31'),
(2, 'Company A', 'company@site.com', '7382323231', '2020-09-24 17:06:34'),
(3, 'Company B', 'companyb@site.com', '3432214233', '2020-09-24 17:08:42'),
(4, 'Bank of Ghana', '', '', '2020-10-05 11:49:58');

-- --------------------------------------------------------

--
-- Table structure for table `reconcilation_status`
--

CREATE TABLE `reconcilation_status` (
  `id` int(255) NOT NULL,
  `user_id` int(50) NOT NULL,
  `jobid` varchar(255) NOT NULL,
  `bank_type` varchar(20) NOT NULL,
  `account` int(100) NOT NULL,
  `ids` text NOT NULL,
  `intval` int(255) NOT NULL DEFAULT 2,
  `transtype` varchar(100) DEFAULT NULL,
  `reconcile_with` varchar(3) NOT NULL,
  `reconciling_with` text NOT NULL,
  `proccessing_account` int(15) NOT NULL,
  `total_account` int(15) NOT NULL,
  `status` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `start_time` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `processing` varchar(12) NOT NULL DEFAULT 'pending',
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reconcilation_status`
--

INSERT INTO `reconcilation_status` (`id`, `user_id`, `jobid`, `bank_type`, `account`, `ids`, `intval`, `transtype`, `reconcile_with`, `reconciling_with`, `proccessing_account`, `total_account`, `status`, `description`, `start_time`, `end_time`, `processing`, `created`) VALUES
(1, 1, 'RJ1605434817', 'Other Banks', 7, '6', 0, NULL, 'few', '', 0, 0, 'completed', ' reconcilation completed - 10:9:25', '2020-11-15 10:06:57', '2020-11-15 10:09:25', 'completed', '2020-11-15 10:06:57'),
(2, 1, 'RJ1605441232', 'Other Banks', 6, '10', 0, NULL, 'few', '', 1775, 1775, 'completed', ' reconcilation completed - 11:54:16', '2020-11-15 11:53:52', '2020-11-15 11:54:16', 'completed', '2020-11-15 11:53:52'),
(3, 1, 'RJ1605442082', 'Other Banks', 6, '10', 0, NULL, 'few', '', 1775, 1775, 'completed', ' reconcilation completed - 12:8:23', '2020-11-15 12:08:02', '2020-11-15 12:08:23', 'completed', '2020-11-15 12:08:02'),
(4, 1, 'RJ1605553426', 'Other Banks', 6, '10', 0, NULL, 'few', '', 1775, 1775, 'completed', ' reconcilation completed - 19:4:15', '2020-11-16 19:03:46', '2020-11-16 19:04:15', 'completed', '2020-11-16 19:03:46'),
(5, 1, 'RJ1605555298', 'Other Banks', 6, '10', 0, NULL, 'few', '', 1775, 1775, 'completed', ' reconcilation completed - 19:35:23', '2020-11-16 19:34:58', '2020-11-16 19:35:23', 'completed', '2020-11-16 19:34:58'),
(6, 1, 'RJ1605705721', 'Bank Of Ghana', 10, '9', 2, NULL, 'few', '', 0, 0, 'completed', ' reconcilation completed - 13:22:43', '2020-11-18 13:22:01', '2020-11-18 13:22:43', 'completed', '2020-11-18 13:22:01'),
(7, 1, 'RJ1605800801', 'Bank Of Ghana', 1, '10,9,8,3', 1, NULL, 'few', '', 1119, 1119, 'completed', ' reconcilation completed - 15:47:46', '2020-11-19 15:46:41', '2020-11-19 15:47:46', 'completed', '2020-11-19 15:46:41'),
(8, 1, 'RJ1605800955', 'Other Banks', 2, '10,9,8,7,6,5,4,3,1', 1, NULL, 'few', '', 152, 152, 'completed', ' reconcilation completed - 15:50:54', '2020-11-19 15:49:15', '2020-11-19 15:50:54', 'completed', '2020-11-19 15:49:15'),
(9, 1, 'RJ1605805520', 'Other Banks', 2, '10,9,8,7,6,5,4,3,1', 2, NULL, 'few', '', 152, 152, 'completed', ' reconcilation completed - 17:7:6', '2020-11-19 17:05:20', '2020-11-19 17:07:06', 'completed', '2020-11-19 17:05:20');

-- --------------------------------------------------------

--
-- Table structure for table `statements`
--

CREATE TABLE `statements` (
  `id` int(255) NOT NULL,
  `account_id` int(255) NOT NULL,
  `post_date` date NOT NULL,
  `particulars` text NOT NULL,
  `reference` varchar(300) NOT NULL DEFAULT '',
  `value_date` date NOT NULL,
  `debit_amount` decimal(65,2) NOT NULL,
  `credit_amount` decimal(65,2) NOT NULL,
  `balance` decimal(65,2) NOT NULL,
  `offset_acc_no` varchar(15) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT '',
  `locked_to` int(255) NOT NULL DEFAULT 0 COMMENT 'account id that this transaction has been locked to',
  `escalated_to` int(255) DEFAULT NULL COMMENT 'the id of the user the record is escalate to.',
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
PARTITION BY RANGE (`id`)
(
PARTITION p0 VALUES LESS THAN (300000) ENGINE=InnoDB,
PARTITION p1 VALUES LESS THAN (600000) ENGINE=InnoDB,
PARTITION p2 VALUES LESS THAN (900000) ENGINE=InnoDB,
PARTITION p3 VALUES LESS THAN (1200000) ENGINE=InnoDB,
PARTITION p4 VALUES LESS THAN (1500000) ENGINE=InnoDB,
PARTITION p5 VALUES LESS THAN (1800000) ENGINE=InnoDB,
PARTITION p6 VALUES LESS THAN (2100000) ENGINE=InnoDB,
PARTITION p7 VALUES LESS THAN (2400000) ENGINE=InnoDB,
PARTITION p8 VALUES LESS THAN (2700000) ENGINE=InnoDB,
PARTITION p9 VALUES LESS THAN (3000000) ENGINE=InnoDB,
PARTITION p10 VALUES LESS THAN (3300000) ENGINE=InnoDB,
PARTITION p11 VALUES LESS THAN (3600000) ENGINE=InnoDB,
PARTITION p12 VALUES LESS THAN (3900000) ENGINE=InnoDB,
PARTITION p13 VALUES LESS THAN (4200000) ENGINE=InnoDB,
PARTITION p14 VALUES LESS THAN (4500000) ENGINE=InnoDB,
PARTITION p15 VALUES LESS THAN (4800000) ENGINE=InnoDB,
PARTITION p16 VALUES LESS THAN (5100000) ENGINE=InnoDB,
PARTITION p17 VALUES LESS THAN (5400000) ENGINE=InnoDB,
PARTITION p18 VALUES LESS THAN (5700000) ENGINE=InnoDB,
PARTITION p19 VALUES LESS THAN (6000000) ENGINE=InnoDB,
PARTITION p20 VALUES LESS THAN (6300000) ENGINE=InnoDB,
PARTITION p21 VALUES LESS THAN (6600000) ENGINE=InnoDB,
PARTITION p22 VALUES LESS THAN (6900000) ENGINE=InnoDB,
PARTITION p23 VALUES LESS THAN (7200000) ENGINE=InnoDB,
PARTITION p24 VALUES LESS THAN (7500000) ENGINE=InnoDB,
PARTITION p25 VALUES LESS THAN (7700000) ENGINE=InnoDB,
PARTITION p26 VALUES LESS THAN (8000000) ENGINE=InnoDB,
PARTITION p27 VALUES LESS THAN (8300000) ENGINE=InnoDB,
PARTITION p28 VALUES LESS THAN (8600000) ENGINE=InnoDB,
PARTITION p29 VALUES LESS THAN (8900000) ENGINE=InnoDB,
PARTITION p30 VALUES LESS THAN (9200000) ENGINE=InnoDB,
PARTITION p31 VALUES LESS THAN (9500000) ENGINE=InnoDB,
PARTITION p32 VALUES LESS THAN (9800000) ENGINE=InnoDB,
PARTITION p33 VALUES LESS THAN (10100000) ENGINE=InnoDB,
PARTITION p34 VALUES LESS THAN (10400000) ENGINE=InnoDB,
PARTITION p35 VALUES LESS THAN (10700000) ENGINE=InnoDB,
PARTITION p36 VALUES LESS THAN (11000000) ENGINE=InnoDB
);

--
-- Triggers `statements`
--
DELIMITER $$
CREATE TRIGGER `update_account_balance` AFTER INSERT ON `statements` FOR EACH ROW INSERT INTO account_balance(account_id, amount, date) VALUES(new.account_id, new.balance, new.post_date) ON DUPLICATE KEY UPDATE   
amount=new.balance, date=new.post_date
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tax_exemptions`
--

CREATE TABLE `tax_exemptions` (
  `id` int(255) NOT NULL,
  `omc` int(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `tax_product` int(255) NOT NULL,
  `litters` decimal(62,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tax_exemptions`
--

INSERT INTO `tax_exemptions` (`id`, `omc`, `date_from`, `date_to`, `tax_product`, `litters`) VALUES
(1, 2, '2020-11-02', '2020-11-05', 1, '2020000.00'),
(2, 2, '2020-09-01', '2020-09-10', 1, '2020000.00');

-- --------------------------------------------------------

--
-- Table structure for table `tax_schedule`
--

CREATE TABLE `tax_schedule` (
  `id` int(255) NOT NULL,
  `tax_type` int(255) NOT NULL,
  `rate` decimal(62,12) NOT NULL DEFAULT 0.000000000000,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `tax_product` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tax_schedule`
--

INSERT INTO `tax_schedule` (`id`, `tax_type`, `rate`, `date_from`, `date_to`, `tax_product`) VALUES
(6, 2, '0.087100000000', '2017-01-01', '2020-10-31', 2),
(7, 2, '0.540080000000', '2015-01-01', '2016-12-31', 3),
(8, 2, '0.480000000000', '2017-01-01', '2020-01-08', 4),
(9, 5, '0.150000000000', '2014-01-01', '2017-03-03', 1),
(10, 3, '0.029150000000', '2015-01-01', '2017-12-31', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tax_schedule_products`
--

CREATE TABLE `tax_schedule_products` (
  `id` int(255) NOT NULL,
  `name` varchar(300) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tax_schedule_products`
--

INSERT INTO `tax_schedule_products` (`id`, `name`, `description`) VALUES
(1, 'PMS', 'something something new'),
(2, 'KERO', 'KERO'),
(3, 'MGO', 'MGO'),
(4, 'MGO Foreign', 'MGO Foreign'),
(5, 'RFO', 'RFO'),
(6, 'AGO RiG', 'AGO RiG'),
(7, 'AGO Mines', 'AGO Mines'),
(8, 'UNIFIED', 'UNIFIED');

-- --------------------------------------------------------

--
-- Table structure for table `tax_type`
--

CREATE TABLE `tax_type` (
  `id` int(255) NOT NULL,
  `name` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tax_type`
--

INSERT INTO `tax_type` (`id`, `name`) VALUES
(1, ' Energy Dept. Recovery Levy (EDRL)'),
(2, 'Road Fund'),
(3, 'Excise Duty (Foreign)'),
(4, 'Energy Fund'),
(5, 'Special Petroleum Tax (SPT)');

-- --------------------------------------------------------

--
-- Table structure for table `unauthorized_transfers`
--

CREATE TABLE `unauthorized_transfers` (
  `id` int(11) NOT NULL,
  `account_from` int(20) NOT NULL,
  `offset_account` varchar(100) NOT NULL,
  `statement_id` int(50) NOT NULL,
  `amount` decimal(65,2) NOT NULL,
  `org_status` varchar(10) NOT NULL DEFAULT 'pending',
  `bog_status` varchar(11) NOT NULL DEFAULT '',
  `escalated_to_bog` int(11) NOT NULL DEFAULT 0,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `intval` int(255) NOT NULL DEFAULT 2,
  `softdelete` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(255) NOT NULL,
  `username` varchar(300) DEFAULT NULL,
  `fullname` varchar(300) NOT NULL,
  `email` varchar(300) DEFAULT NULL,
  `phone` varchar(12) DEFAULT NULL,
  `photo` text NOT NULL,
  `organization` int(255) DEFAULT NULL,
  `designation` varchar(200) DEFAULT NULL,
  `tin` varchar(100) DEFAULT NULL,
  `location` varchar(300) DEFAULT NULL,
  `region` varchar(300) DEFAULT NULL,
  `district` varchar(300) DEFAULT NULL,
  `user_type` varchar(100) NOT NULL DEFAULT 'system',
  `access_level` varchar(300) DEFAULT NULL,
  `role` int(255) NOT NULL DEFAULT 0,
  `baseurl` text DEFAULT '',
  `password` text NOT NULL,
  `salt` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `phone`, `photo`, `organization`, `designation`, `tin`, `location`, `region`, `district`, `user_type`, `access_level`, `role`, `baseurl`, `password`, `salt`, `created`) VALUES
(1, 'dsaved', 'Daniel Vincent Johnson', 'dsaved8291@gmail.com', '0543848378', 'http://localhost/gas/api/uploads/image/20201106d2c1dfbf5d07f661ec454a151115b91856.png', 0, NULL, NULL, NULL, NULL, NULL, 'system', 'admin', 1, '/', '==QJiRobm/Xe0fscwx2Ty2Lj', '46ca6cf47c3b3d1ba9be1d8994a64c37', '2020-11-05 09:19:39'),
(4, 'helen', 'Helen Gbadero', 'dsaved91@gmail.com', '5438483784', '', 0, NULL, NULL, NULL, NULL, NULL, 'system', 'admin', 6, '/', '==gas8BPL07QWsPvfpT3AJvO', '38561691771ad8b8dc350568e11e608e', '2020-11-05 09:27:30'),
(5, 'mark', 'Oluwaseun Gbadero', 'mark@gmail.com', '0909646441', '', 2, 'Chief', '387728HFY', 'Barrier', 'Grater-Accra', 'Weija', 'omc', 'omc', 0, '/petroleum', '==QYpHQjdzpaazYnGMUfInVO', 'ab1416433452b8b9d3054f1b5871bc9f', '2020-11-06 12:50:00'),
(6, 'mary', 'Mary Johnson', 'mary@gmail.com', '0243848378', '', 0, 'commander', '3232G', 'Accra', 'Grater-Accra', 'dsaved8291@gmail.com', 'bdc', 'bdc', 0, '/petroleum/icoms/declearations', '==QcTAoTeXDdM9SJLgu566QR', 'be5dd3dc561c4e5e78ca18e0ccf530d0', '2020-11-06 13:33:35'),
(7, 'hog', 'wizzy baanty', 'ed8291@gmail.com', '6089577298', '', 4, NULL, NULL, NULL, NULL, NULL, 'bogorg', 'user', 0, '/revenue', '==Ag7FxJS+pmbdCJqLDfqTWZ', 'a56406f9cdde8cea80fc3e58c48380de', '2020-11-13 12:02:08'),
(8, '09096464414', 'Oluwaseun Gbadero', 'dsave91@gmail.com', '0909646404', '', 0, NULL, NULL, NULL, NULL, NULL, 'system', 'admin', 1, '/', '==wT4clVYy1hHCrCRdSizv9q', '6a8656ec1a238b5c88a0156875860944', '2020-11-13 12:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `users_page`
--

CREATE TABLE `users_page` (
  `id` bigint(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `pages` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users_page`
--

INSERT INTO `users_page` (`id`, `user_id`, `pages`) VALUES
(1, 1, 'Home,Revenue,Regimes,Petroleum,Accounts,BDC users,OMC users,System Users,System User Role,Petroleum Home,Tax,Tax Type,Tax Products,Tax Schedule,Exemptions,Compute Tax Schedule,OMCs,Reconcile,SML - NPA Reconcilations,NPA - ICOMS Reconcilations,NPA - NPA Reconcilations,NPA,NPA Reconcilation Reports,NPA Daily Liftings,ICOMS (CEPS),ICOMS Declearations,ICOMS Payments,ICOMS Differences,SML,SML Daily Liftings,Banks,Accounts (Bank),Organizations,Bank Accounts,Bank Account,Account Categories,Audit Logs,Audit Responses,Revenue Bank Accounts,Unauthorized,Unauthorized Bank of Ghana,Unauthorized Other Banks,Unauthorized Hidden Transactions,Accounts,OMC Organization Manage,Assets,Confirmed Assets,Disposed Assets,New Assets,Transfered Assets,Unknown Assets,Projects,GOG COA,Institutes,Funding,Organization,Location,Natural Accounts,Assets Validation,Assets Validations List,Assets Validations Completed,Reports'),
(4, 4, 'Revenue,Regimes,Petroleum,Accounts,BDC users,OMC users,System Users,System User Role,Home,Petroleum Home,Tasks,Set Up Task Type,Task Schedule,Exemptions,Compute Task Schedule,Reconcile,SML - NPA Reconcilations,NPA - ICOMS Reconcilations,NPA - NPA Reconcilations,NPA,NPA Reconcilation Reports,NPA Daily Liftings,ICOMS (CEPS),ICOMS Declearations,ICOMS Payments,ICOMS Differences,SML,SML Daily Liftings'),
(5, 5, ''),
(6, 6, 'ICOMS (CEPS),ICOMS Declearations,ICOMS Payments,ICOMS Differences'),
(7, 7, ''),
(8, 8, 'Home,Revenue,Regimes,Petroleum,Accounts,BDC users,OMC users,System Users,System User Role,Banks,Bank Accounts,Bank Account,Account Categories,Organizations,Compute Tax Schedule,Tax,Tax Type,Tax Products,Tax Schedule,Exemptions,Petroleum Home,OMCs,Reconcile,SML - NPA Reconcilations,NPA - ICOMS Reconcilations,NPA - NPA Reconcilations,NPA,NPA Reconcilation Reports,NPA Daily Liftings,ICOMS (CEPS),ICOMS Declearations,ICOMS Payments,ICOMS Differences,SML,SML Daily Liftings');

-- --------------------------------------------------------

--
-- Table structure for table `users_role`
--

CREATE TABLE `users_role` (
  `id` int(255) NOT NULL,
  `role` varchar(200) NOT NULL,
  `permissions` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users_role`
--

INSERT INTO `users_role` (`id`, `role`, `permissions`) VALUES
(1, 'Admin', 'create,read,update,delete'),
(6, 'Manger', 'read,update'),
(7, 'sasa', 'read');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner` (`owner`,`bank_type`,`status`),
  ADD KEY `acc_num1` (`acc_num1`,`acc_num2`),
  ADD KEY `acc_num1_2` (`acc_num1`),
  ADD KEY `acc_num2` (`acc_num2`),
  ADD KEY `bank` (`bank`),
  ADD KEY `bank_type` (`bank_type`);

--
-- Indexes for table `accounts_bog`
--
ALTER TABLE `accounts_bog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_balance`
--
ALTER TABLE `account_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_id` (`account_id`);

--
-- Indexes for table `account_category`
--
ALTER TABLE `account_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name_INDEX` (`name`(768)),
  ADD KEY `name_id_index` (`id`,`name`(100)),
  ADD KEY `created` (`created`);

--
-- Indexes for table `asinged_account`
--
ALTER TABLE `asinged_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `main_account` (`main_account`),
  ADD KEY `main_account_2` (`main_account`,`child_account`);

--
-- Indexes for table `audits_logs`
--
ALTER TABLE `audits_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `authentication`
--
ALTER TABLE `authentication`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auth_user_id_index` (`auth`(300),`user_id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name_INDEX` (`name`(768)),
  ADD KEY `name_id_index` (`id`,`name`(100)),
  ADD KEY `created` (`created`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `statement_id_INDEX` (`statement_id`) USING BTREE,
  ADD KEY `reference_no` (`reference_no`),
  ADD KEY `account_number` (`account_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `account_from` (`account_from`);

--
-- Indexes for table `file_download`
--
ALTER TABLE `file_download`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_export_status`
--
ALTER TABLE `file_export_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_export_status_logs`
--
ALTER TABLE `file_export_status_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_upload_receipt_status`
--
ALTER TABLE `file_upload_receipt_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_upload_status`
--
ALTER TABLE `file_upload_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `omc`
--
ALTER TABLE `omc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `omc_mapped_account`
--
ALTER TABLE `omc_mapped_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `main_account` (`main_account`),
  ADD KEY `main_account_2` (`main_account`,`child_account`);

--
-- Indexes for table `omc_receipt`
--
ALTER TABLE `omc_receipt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_id` (`bank_id`),
  ADD KEY `omc_id` (`omc_id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_index` (`phone`),
  ADD KEY `emailIndex` (`email`),
  ADD KEY `name_index` (`name`) USING BTREE,
  ADD KEY `login_index_find` (`name`,`phone`,`email`) USING BTREE;

--
-- Indexes for table `reconcilation_status`
--
ALTER TABLE `reconcilation_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statements`
--
ALTER TABLE `statements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escalated_to_INDEX` (`escalated_to`),
  ADD KEY `locked_to_index` (`locked_to`),
  ADD KEY `offset_acc_no_INDEX` (`offset_acc_no`),
  ADD KEY `post_date_INDEX` (`post_date`),
  ADD KEY `acount_id_AND_lockedtoINDEX` (`account_id`,`locked_to`),
  ADD KEY `account_id_INDEX` (`account_id`),
  ADD KEY `post_date_credit_amount_INDEX` (`post_date`,`credit_amount`),
  ADD KEY `acc_debit-offset_INDEX` (`account_id`,`debit_amount`,`offset_acc_no`),
  ADD KEY `status_index` (`status`);

--
-- Indexes for table `tax_exemptions`
--
ALTER TABLE `tax_exemptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tax_schedule`
--
ALTER TABLE `tax_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tax_schedule_products`
--
ALTER TABLE `tax_schedule_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tax_type`
--
ALTER TABLE `tax_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unauthorized_transfers`
--
ALTER TABLE `unauthorized_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `statement_id` (`statement_id`),
  ADD KEY `account_from_index` (`account_from`),
  ADD KEY `offset_acc_no_INDEX` (`offset_account`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_index_find` (`username`,`id`,`phone`,`email`),
  ADD KEY `phone_index` (`phone`),
  ADD KEY `username_index` (`username`),
  ADD KEY `emailIndex` (`email`),
  ADD KEY `access_level` (`access_level`),
  ADD KEY `organization` (`organization`);

--
-- Indexes for table `users_page`
--
ALTER TABLE `users_page`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users_role`
--
ALTER TABLE `users_role`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `accounts_bog`
--
ALTER TABLE `accounts_bog`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2752;

--
-- AUTO_INCREMENT for table `account_balance`
--
ALTER TABLE `account_balance`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80550;

--
-- AUTO_INCREMENT for table `account_category`
--
ALTER TABLE `account_category`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `asinged_account`
--
ALTER TABLE `asinged_account`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `audits_logs`
--
ALTER TABLE `audits_logs`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `authentication`
--
ALTER TABLE `authentication`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `file_download`
--
ALTER TABLE `file_download`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `file_export_status`
--
ALTER TABLE `file_export_status`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `file_export_status_logs`
--
ALTER TABLE `file_export_status_logs`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `file_upload_receipt_status`
--
ALTER TABLE `file_upload_receipt_status`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `file_upload_status`
--
ALTER TABLE `file_upload_status`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `omc`
--
ALTER TABLE `omc`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `omc_mapped_account`
--
ALTER TABLE `omc_mapped_account`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `omc_receipt`
--
ALTER TABLE `omc_receipt`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reconcilation_status`
--
ALTER TABLE `reconcilation_status`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `statements`
--
ALTER TABLE `statements`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax_exemptions`
--
ALTER TABLE `tax_exemptions`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tax_schedule`
--
ALTER TABLE `tax_schedule`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tax_schedule_products`
--
ALTER TABLE `tax_schedule_products`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tax_type`
--
ALTER TABLE `tax_type`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `unauthorized_transfers`
--
ALTER TABLE `unauthorized_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users_page`
--
ALTER TABLE `users_page`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users_role`
--
ALTER TABLE `users_role`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `Reset Monitor Status` ON SCHEDULE EVERY 1 HOUR STARTS '2020-10-02 20:41:04' ON COMPLETION PRESERVE ENABLE DO BEGIN
DELETE FROM file_upload_status WHERE DATE(file_upload_status.created) < DATE_SUB(CURDATE(), INTERVAL 2 DAY);
DELETE FROM reconcilation_status WHERE DATE(reconcilation_status.created) < DATE_SUB(CURDATE(), INTERVAL 2 DAY);
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
