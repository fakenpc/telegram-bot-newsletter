SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `field`;
CREATE TABLE `field` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8mb4_unicode_520_ci,
  `value` text COLLATE utf8mb4_unicode_520_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `newsletter_category_id` int(11) DEFAULT NULL,
  `name` text COLLATE utf8mb4_unicode_520_ci,
  `description` text COLLATE utf8mb4_unicode_520_ci,
  `sending_timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `disabling_timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `sended` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `newsletter_category`;
CREATE TABLE `newsletter_category` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8mb4_unicode_520_ci,
  `description` text COLLATE utf8mb4_unicode_520_ci,
  `allow_trial` tinyint(1) NOT NULL DEFAULT '1',
  `trial_duration` int(11) NOT NULL DEFAULT '864000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `newsletter_sended`;
CREATE TABLE `newsletter_sended` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) DEFAULT NULL,
  `newsletter_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `subscriber`;
CREATE TABLE `subscriber` (
  `id` int(11) NOT NULL,
  `newsletter_category_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `chat_id` bigint(20) NOT NULL,
  `start_timestamp` int(11) DEFAULT NULL,
  `end_timestamp` int(11) DEFAULT NULL,
  `paid` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `subscription`;
CREATE TABLE `subscription` (
  `id` int(11) NOT NULL,
  `newsletter_category_id` int(11) NOT NULL DEFAULT '0',
  `name` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `duration` int(11) NOT NULL DEFAULT '1',
  `price` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `trial`;
CREATE TABLE `trial` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `newsletter_category_id` int(11) NOT NULL DEFAULT '0',
  `used` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


ALTER TABLE `field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `newsletter_category`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `newsletter_sended`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subscriber`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subscription`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `trial`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `field`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `newsletter_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `newsletter_sended`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `subscriber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `subscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `trial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
