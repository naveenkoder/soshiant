DROP TABLE IF EXISTS `arioo_top_ten`;
CREATE TABLE IF NOT EXISTS `arioo_top_ten` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `create_time` INT NOT NULL,
    `top_ten` JSON NULL,
    `unique_id` VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

DROP TABLE IF EXISTS `arioo_routes`;
CREATE TABLE IF NOT EXISTS `arioo_routes` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `name` VARCHAR(50) NOT NULL,
    `average` INT DEFAULT 0,
    `unique_id` INT NOT NULL UNIQUE,
    `parent` INT DEFAULT 0,
    `create_time` INT NOT NULL,
    `modified_time` INT NOT NULL,
    `unit` VARCHAR(255) NULL,
    `period` VARCHAR(50) DEFAULT "",
    `alerts` TEXT,
    `is_active` BOOLEAN DEFAULT 1,
    `is_deleted` BOOLEAN DEFAULT 0,
    `is_pendding` BOOLEAN DEFAULT 0,
    PRIMARY KEY (id)
);

DROP TABLE IF EXISTS `arioo_routes_data`;
CREATE TABLE IF NOT EXISTS `arioo_routes_data` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `year` INT NOT NULL, 
    `period` VARCHAR(255) NOT NULL,
    `value` TEXT NOT NULL,
    `more_info` TEXT,
    `create_time` INT DEFAULT 0,
    `unique_id` VARCHAR(255) NOT NULL, 
    `is_real` Boolean NOT NULL, 
    `average_accuracy` FLOAT NOT NULL DEFAULT 0, 
    `confidence_level` FLOAT NOT NULL DEFAULT 0, 
    PRIMARY KEY (id)
);

DROP TABLE IF EXISTS `arioo_route_access`;
CREATE TABLE IF NOT EXISTS `arioo_route_access` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `create_time` INT NOT NULL,
    `modified_time` INT NOT NULL,
    `expire_time` INT DEFAULT NULL,
    `user` INT DEFAULT 0,
    `access` TEXT NULL,
    `is_ban` BOOLEAN DEFAULT 0,
    PRIMARY KEY (id)
);



CREATE TABLE `arioo_users` (
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `user_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_algo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sha256',
  `user_salt` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_admin_algo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sha256',
  `user_admin_salt` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_admin_password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_hide_email` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `user_timezone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Europe/London',
  `user_avatar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_posts` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `user_threads` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_joined` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_lastvisit` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  `user_ip_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '4',
  `user_rights` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_groups` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_level` tinyint(4) NOT NULL DEFAULT '-101',
  `user_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `user_reputation` int(10) UNSIGNED NOT NULL,
  `user_inbox` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `user_outbox` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `user_archive` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `user_pm_email_notify` tinyint(1) NOT NULL DEFAULT '0',
  `user_pm_save_sent` tinyint(1) NOT NULL DEFAULT '0',
  `user_actiontime` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_theme` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default',
  `user_location` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_birthdate` date NOT NULL DEFAULT '1900-01-01',
  `user_skype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_aim` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_icq` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_yahoo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_web` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_sig` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_language` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'English'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

