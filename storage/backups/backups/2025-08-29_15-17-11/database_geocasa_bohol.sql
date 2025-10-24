-- Database backup created at 2025-08-29 15:17:11
-- Database: geocasa_bohol

SET FOREIGN_KEY_CHECKS=0;

-- Table structure for `admin_activity_logs`
DROP TABLE IF EXISTS `admin_activity_logs`;
CREATE TABLE `admin_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(255) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_activity_logs_admin_id_created_at_index` (`admin_id`,`created_at`),
  KEY `admin_activity_logs_target_type_target_id_index` (`target_type`,`target_id`),
  CONSTRAINT `admin_activity_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `cache`
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `cache`
INSERT INTO `cache` VALUES
('902ba3cda1883801594b6e1b452790cc53948fda','i:1;','1756479464'),
('902ba3cda1883801594b6e1b452790cc53948fda:timer','i:1756479464;','1756479464'),
('app_metrics','a:6:{s:5:\"users\";a:1:{s:5:\"error\";s:28:\"Unable to fetch user metrics\";}s:10:\"properties\";a:1:{s:5:\"error\";s:32:\"Unable to fetch property metrics\";}s:9:\"inquiries\";a:5:{s:15:\"total_inquiries\";i:5;s:19:\"new_inquiries_today\";i:3;s:17:\"pending_inquiries\";i:0;s:19:\"responded_inquiries\";i:0;s:19:\"inquiries_this_week\";i:5;}s:12:\"transactions\";a:1:{s:5:\"error\";s:35:\"Unable to fetch transaction metrics\";}s:6:\"system\";a:8:{s:11:\"php_version\";s:6:\"8.2.12\";s:15:\"laravel_version\";s:7:\"11.40.0\";s:11:\"environment\";s:5:\"local\";s:10:\"debug_mode\";b:1;s:8:\"timezone\";s:3:\"UTC\";s:12:\"memory_usage\";s:5:\"30 MB\";s:11:\"memory_peak\";s:5:\"30 MB\";s:10:\"disk_usage\";a:4:{s:5:\"total\";s:9:\"102.76 GB\";s:4:\"used\";s:8:\"17.37 GB\";s:4:\"free\";s:8:\"85.39 GB\";s:16:\"usage_percentage\";d:16.9;}}s:11:\"performance\";a:4:{s:25:\"database_response_time_ms\";d:0.52;s:22:\"cache_response_time_ms\";d:0.39;s:24:\"average_response_time_ms\";d:0;s:18:\"slow_queries_count\";i:0;}}','1756480600'),
('c1dfd96eea8cc2b62785275bca38ac261256e278','i:1;','1756479140'),
('c1dfd96eea8cc2b62785275bca38ac261256e278:timer','i:1756479140;','1756479140');

-- Table structure for `cache_locks`
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `clients`
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip_code` varchar(255) DEFAULT NULL,
  `budget_min` decimal(15,2) DEFAULT NULL,
  `budget_max` decimal(15,2) DEFAULT NULL,
  `preferred_location` varchar(255) DEFAULT NULL,
  `preferred_area_min` decimal(10,2) DEFAULT NULL,
  `preferred_area_max` decimal(10,2) DEFAULT NULL,
  `preferred_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_features`)),
  `broker_id` bigint(20) unsigned NOT NULL,
  `source` enum('inquiry','manual','referral','website') NOT NULL DEFAULT 'manual',
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive','converted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_email_unique` (`email`),
  KEY `clients_broker_id_status_index` (`broker_id`,`status`),
  KEY `clients_email_index` (`email`),
  CONSTRAINT `clients_broker_id_foreign` FOREIGN KEY (`broker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `clients`
INSERT INTO `clients` VALUES
('1','Roberto Fernandez','roberto@example.com','+63-917-123-4567','123 Rizal Street','Tagbilaran City','Bohol','6300','5000000.00','25000000.00','Panglao','1000.00','5000.00',NULL,'2','inquiry',NULL,'active','2025-08-27 07:19:40','2025-08-27 07:19:40'),
('2','Elena Villanueva','elena@example.com','+63-918-987-6543',NULL,NULL,NULL,NULL,'10000000.00','50000000.00','Carmen',NULL,NULL,NULL,'3','manual',NULL,'active','2025-08-27 07:19:40','2025-08-27 07:19:40'),
('3','Mariella Doreen L. Canete','canetemariella888@gmail.com','09283940392',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-27 14:43:57','2025-08-27 14:43:57'),
('4','Marie','dfskak@gmail.com','09858483732',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-28 14:45:13','2025-08-28 14:45:13'),
('5','makmdfa','djnfanj@gmail.com','08969584930',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 02:58:42','2025-08-29 02:58:42'),
('6','kl;sdfkl','jdsja@EXAMPLE.COM','09847382913',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 03:05:48','2025-08-29 03:05:48'),
('7','fgdg','fsda@fmda.com','09837483847',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 06:07:38','2025-08-29 06:07:38'),
('8','kmdsaf','dfak@gmail.com','09874859384',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 06:35:41','2025-08-29 06:35:41'),
('9','kmkdf','mkdsfm@kds.com','09847845485',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 06:39:52','2025-08-29 06:39:52'),
('10','mncx','mvxzn@mddd.clm','09097059650654',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 06:42:27','2025-08-29 06:42:27'),
('11','kja','kfdasnk@mdf.com','03421',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 06:46:43','2025-08-29 06:46:43'),
('12','dnfsa','jfan@gmail.com','09938402942',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 07:14:26','2025-08-29 07:14:26'),
('13','dfajkfa','mdsam@gmail.com','079584949644',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 07:23:34','2025-08-29 07:23:34'),
('14','jsdfjaf','jndfnjas@gmail.com','09937439482',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 07:25:32','2025-08-29 07:25:32'),
('15','jkhsdf','jknsfdaj@fkmdsa.com','098674953945',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 07:28:27','2025-08-29 07:28:27'),
('16','fgd','sdfkmam@gma.com','0896975606943',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 07:35:18','2025-08-29 07:35:18'),
('17','jksfda','nlfsan@fms.com','09937475867',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 07:41:10','2025-08-29 07:41:10'),
('18',',dsfa','sdfans@fd.com','09827384754',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 14:19:50','2025-08-29 14:19:50'),
('19','sdfkaf','kdsm@sdf.com','092832749232',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 14:20:50','2025-08-29 14:20:50'),
('20','uyt','msdfn2@md.com','093483241',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 14:31:55','2025-08-29 14:31:55'),
('21','dmsaf','fmsam@fdsaf.com','94530542',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'6','manual',NULL,'active','2025-08-29 14:51:20','2025-08-29 14:51:20'),
('22','Thess Busalanan','thessy@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2','manual',NULL,'active','2025-08-29 14:56:16','2025-08-29 14:56:16');

-- Table structure for `conversations`
DROP TABLE IF EXISTS `conversations`;
CREATE TABLE `conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('inquiry','transaction','general') NOT NULL DEFAULT 'general',
  `inquiry_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `participants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`participants`)),
  `last_message_at` timestamp NULL DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_transaction_id_foreign` (`transaction_id`),
  KEY `conversations_inquiry_id_transaction_id_index` (`inquiry_id`,`transaction_id`),
  KEY `conversations_last_message_at_index` (`last_message_at`),
  CONSTRAINT `conversations_inquiry_id_foreign` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `failed_jobs`
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inquiries`
DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `inquiry_type` enum('general','viewing','purchase','information') NOT NULL DEFAULT 'general',
  `property_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('new','contacted','scheduled','completed','closed') NOT NULL DEFAULT 'new',
  `contacted_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `broker_notes` text DEFAULT NULL,
  `broker_response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inquiries_client_id_foreign` (`client_id`),
  KEY `inquiries_property_id_status_index` (`property_id`,`status`),
  KEY `inquiries_email_created_at_index` (`email`,`created_at`),
  KEY `inquiries_status_index` (`status`),
  CONSTRAINT `inquiries_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inquiries_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `inquiries`
INSERT INTO `inquiries` VALUES
('1','Roberto Fernandez','roberto@example.com','+63-917-123-4567','I am interested in this beachfront property for a resort development. Can we schedule a site visit?','viewing','1','1','contacted','2025-08-25 07:19:40',NULL,NULL,NULL,NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40'),
('2','Michael Johnson','michael@example.com','+1-555-0123','What are the development restrictions for this commercial lot? Is it suitable for a hotel?','information','3',NULL,'new',NULL,NULL,NULL,NULL,NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40'),
('21','uyt','msdfn2@md.com','093483241','I\'m interested in fsdkkfsdmkfska. Please provide more information about this property.','general','7','20','new',NULL,NULL,NULL,NULL,NULL,'2025-08-29 14:31:55','2025-08-29 14:31:55'),
('22','dmsaf','fmsam@fdsaf.com','94530542','I\'m interested in fsdkkfsdmkfska. Please provide more information about this property.','general','7','21','new',NULL,NULL,NULL,NULL,NULL,'2025-08-29 14:51:20','2025-08-29 14:51:20'),
('23','thess busalanan','thessy@gmail.com','09838439242','I\'m interested in learning more about this property. Please provide additional details.','general','7','22','new',NULL,NULL,NULL,NULL,NULL,'2025-08-29 14:56:44','2025-08-29 14:56:44');

-- Table structure for `job_batches`
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `jobs`
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `messages`
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `content` text NOT NULL,
  `type` enum('text','file','system') NOT NULL DEFAULT 'text',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  KEY `messages_sender_id_created_at_index` (`sender_id`,`created_at`),
  KEY `messages_read_at_index` (`read_at`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `migrations`
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `migrations`
INSERT INTO `migrations` VALUES
('1','0001_01_01_000000_create_users_table','1'),
('2','0001_01_01_000001_create_cache_table','1'),
('3','0001_01_01_000002_create_jobs_table','1'),
('4','2025_07_30_134439_add_role_and_approval_to_users_table','1'),
('5','2025_07_30_140136_create_clients_table','1'),
('6','2025_07_30_140137_create_properties_table','1'),
('7','2025_07_30_140426_create_inquiries_table','1'),
('8','2025_07_30_140517_create_transactions_table','1'),
('9','2025_07_30_140553_create_seller_requests_table','1'),
('10','2025_07_30_144555_create_notifications_table','1'),
('11','2025_08_07_095454_add_credentials_to_users_table','1'),
('12','2025_08_16_092449_add_deleted_at_to_seller_requests_table','1'),
('13','2025_08_16_100329_add_suspension_fields_to_users_table','1'),
('14','2025_08_16_100558_create_admin_activity_logs_table','1'),
('15','2025_08_20_114451_add_gis_virtual_tour_to_properties_table','1'),
('16','2025_01_18_000001_create_conversations_table','2'),
('17','2025_01_18_000002_create_messages_table','2'),
('18','2025_08_29_021827_create_notification_preferences_table','3');

-- Table structure for `notification_preferences`
DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE `notification_preferences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `email_new_inquiry` tinyint(1) NOT NULL DEFAULT 1,
  `email_transaction_updates` tinyint(1) NOT NULL DEFAULT 1,
  `email_messages` tinyint(1) NOT NULL DEFAULT 1,
  `email_seller_requests` tinyint(1) NOT NULL DEFAULT 1,
  `email_frequency` enum('immediate','hourly','daily','weekly') NOT NULL DEFAULT 'immediate',
  `phone_number` varchar(255) DEFAULT NULL,
  `sms_urgent_inquiries` tinyint(1) NOT NULL DEFAULT 0,
  `sms_transaction_milestones` tinyint(1) NOT NULL DEFAULT 0,
  `browser_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `quiet_hours_start` time NOT NULL DEFAULT '22:00:00',
  `quiet_hours_end` time NOT NULL DEFAULT '08:00:00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `notification_preferences`
INSERT INTO `notification_preferences` VALUES
('1','1','1','1','1','1','immediate',NULL,'0','0','0','22:00:00','08:00:00','2025-08-29 02:46:22','2025-08-29 02:46:22'),
('2','6','1','1','1','1','immediate',NULL,'0','0','0','22:00:00','08:00:00','2025-08-29 02:48:46','2025-08-29 02:48:46'),
('3','7','1','1','1','1','immediate',NULL,'0','0','0','22:00:00','08:00:00','2025-08-29 14:56:18','2025-08-29 14:56:18');

-- Table structure for `notifications`
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `notifications`
INSERT INTO `notifications` VALUES
('09651312-34cc-4a77-b551-2340e3b9f27e','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":13,\"property_id\":7,\"client_name\":\"dnfsa\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('13bd062f-e061-45b0-a1ce-5058aabb79ed','App\\Notifications\\BrokerApprovalNotification','App\\Models\\User','6','{\"type\":\"broker_approval\",\"approved\":true,\"message\":\"Your broker application has been approved!\"}','2025-08-29 07:24:54','2025-08-29 07:18:55','2025-08-29 07:24:54'),
('19dc6c97-bbee-47a4-b5c2-106b96fc3e15','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":7,\"property_id\":7,\"client_name\":\"kl;sdfkl\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('28d193d8-4442-45f6-90a1-fc53238759ea','App\\Notifications\\NewInquiryNotification','App\\Models\\User','1','{\"type\":\"new_inquiry\",\"inquiry_id\":1,\"property_id\":1,\"client_name\":\"Roberto Fernandez\",\"property_title\":\"Prime Beachfront Lot in Panglao Island\",\"message\":\"New inquiry received for Prime Beachfront Lot in Panglao Island\"}',NULL,'2025-08-29 14:48:32','2025-08-29 14:48:32'),
('2b99a543-dc66-417c-946a-065e2e3dbfcc','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":3,\"property_id\":7,\"client_name\":\"Mariella Doreen L. Canete\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('309234c0-00af-47d2-9acb-19a673ed48a9','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":6,\"property_id\":7,\"client_name\":\"makmdfa\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('3b89e5f3-6e54-42ad-95c9-9940d232517d','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":16,\"property_id\":7,\"client_name\":\"jkhsdf\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:35:00','2025-08-29 07:28:28','2025-08-29 07:35:00'),
('3fd7d4d9-183c-4d41-9ea0-1b86dc58b9c7','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":10,\"property_id\":7,\"client_name\":\"kmkdf\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('55eeed08-baf4-41ae-aa16-f5e5e1a81626','App\\Notifications\\NewInquiryNotification','App\\Models\\User','1','{\"type\":\"new_inquiry\",\"inquiry_id\":1,\"property_id\":1,\"client_name\":\"Roberto Fernandez\",\"property_title\":\"Prime Beachfront Lot in Panglao Island\",\"message\":\"New inquiry received for Prime Beachfront Lot in Panglao Island\"}',NULL,'2025-08-29 14:44:19','2025-08-29 14:44:19'),
('58b29c47-6449-47ec-94ff-35b106532fd2','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":19,\"property_id\":7,\"client_name\":\",dsfa\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 14:21:09','2025-08-29 14:19:52','2025-08-29 14:21:09'),
('71f9e4a2-e9a4-419a-b4a5-835abbe240e2','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":14,\"property_id\":7,\"client_name\":\"dfajkfa\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:23:36','2025-08-29 07:24:54'),
('74323f92-f4f7-4635-8165-f3213feaa988','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":4,\"property_id\":7,\"client_name\":\"Marie\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('74a663fc-2252-4bc6-b5be-4b51bf9a9725','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":18,\"property_id\":7,\"client_name\":\"jksfda\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 14:21:09','2025-08-29 07:41:12','2025-08-29 14:21:09'),
('8429788a-9d30-449f-be96-d7a3e660e5ee','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":21,\"property_id\":7,\"client_name\":\"uyt\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}',NULL,'2025-08-29 14:31:57','2025-08-29 14:31:57'),
('89155760-a0d1-401f-ae96-207bdf7452da','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":22,\"property_id\":7,\"client_name\":\"dmsaf\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 15:15:57','2025-08-29 14:51:21','2025-08-29 15:15:57'),
('96025aa8-2edf-4eea-8744-74a66d5fdada','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":15,\"property_id\":7,\"client_name\":\"jsdfjaf\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:28:03','2025-08-29 07:25:33','2025-08-29 07:28:03'),
('a3aeb069-c34f-4c9e-81e5-da17b7939788','App\\Notifications\\NewInquiryNotification','App\\Models\\User','1','{\"type\":\"new_inquiry\",\"inquiry_id\":1,\"property_id\":1,\"client_name\":\"Roberto Fernandez\",\"property_title\":\"Prime Beachfront Lot in Panglao Island\",\"message\":\"New inquiry received for Prime Beachfront Lot in Panglao Island\"}',NULL,'2025-08-29 14:49:41','2025-08-29 14:49:41'),
('c7b3082a-8313-4501-b19b-076de728c38b','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":17,\"property_id\":7,\"client_name\":\"fgd\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 14:21:09','2025-08-29 07:35:18','2025-08-29 14:21:09'),
('cd43d9d9-6231-4c2d-ba9a-0703674c9850','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":11,\"property_id\":7,\"client_name\":\"mncx\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('d2a087f8-f9fa-48c8-bf7f-3fa6f7fe4755','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":20,\"property_id\":7,\"client_name\":\"sdfkaf\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 14:21:09','2025-08-29 14:20:53','2025-08-29 14:21:09'),
('d2e269d7-e09d-4b23-a211-769e2dcfaed2','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":23,\"property_id\":7,\"client_name\":\"Thess Busalanan\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 14:58:04','2025-08-29 14:56:47','2025-08-29 14:58:04'),
('e4931cee-66e4-4943-8a94-1e529b32e646','App\\Notifications\\NewInquiryNotification','App\\Models\\User','1','{\"type\":\"new_inquiry\",\"inquiry_id\":1,\"property_id\":1,\"client_name\":\"Roberto Fernandez\",\"property_title\":\"Prime Beachfront Lot in Panglao Island\",\"message\":\"New inquiry received for Prime Beachfront Lot in Panglao Island\"}',NULL,'2025-08-29 14:45:40','2025-08-29 14:45:40'),
('e4aba377-420b-4de0-8f63-c092e112d991','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":8,\"property_id\":7,\"client_name\":\"fgdg\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('e7ddfbe5-3dae-4218-8485-92bd8c107898','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":12,\"property_id\":7,\"client_name\":\"kja\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54'),
('e9ed6c2a-d495-4d1e-a539-f74b6a606a5c','App\\Notifications\\NewInquiryNotification','App\\Models\\User','6','{\"type\":\"new_inquiry\",\"inquiry_id\":9,\"property_id\":7,\"client_name\":\"kmdsaf\",\"property_title\":\"fsdkkfsdmkfska\",\"message\":\"New inquiry received for fsdkkfsdmkfska\"}','2025-08-29 07:24:54','2025-08-29 07:18:56','2025-08-29 07:24:54');

-- Table structure for `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `properties`
DROP TABLE IF EXISTS `properties`;
CREATE TABLE `properties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('residential_lot','agricultural_land','commercial_lot','industrial_lot','beachfront','mountain_view','rice_field','coconut_plantation','subdivision_lot','titled_land','tax_declared') NOT NULL,
  `status` enum('available','reserved','sold','under_negotiation','off_market') NOT NULL DEFAULT 'available',
  `price_per_sqm` decimal(10,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `address` varchar(255) NOT NULL,
  `municipality` varchar(255) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `lot_area_sqm` decimal(12,2) NOT NULL,
  `lot_area_hectares` decimal(8,4) DEFAULT NULL,
  `title_type` enum('titled','tax_declared','mother_title','cct') DEFAULT NULL,
  `title_number` varchar(255) DEFAULT NULL,
  `tax_declaration_number` varchar(255) DEFAULT NULL,
  `coordinates_lat` decimal(10,8) DEFAULT NULL,
  `coordinates_lng` decimal(11,8) DEFAULT NULL,
  `gis_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gis_data`)),
  `road_access` tinyint(1) NOT NULL DEFAULT 0,
  `water_source` tinyint(1) NOT NULL DEFAULT 0,
  `electricity_available` tinyint(1) NOT NULL DEFAULT 0,
  `internet_available` tinyint(1) NOT NULL DEFAULT 0,
  `nearby_landmarks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nearby_landmarks`)),
  `zoning_classification` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `virtual_tour_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`virtual_tour_images`)),
  `has_virtual_tour` tinyint(1) NOT NULL DEFAULT 0,
  `tour_hotspots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tour_hotspots`)),
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `broker_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `properties_slug_unique` (`slug`),
  KEY `properties_broker_id_foreign` (`broker_id`),
  KEY `properties_client_id_foreign` (`client_id`),
  KEY `properties_municipality_status_index` (`municipality`,`status`),
  KEY `properties_type_status_index` (`type`,`status`),
  KEY `properties_price_per_sqm_lot_area_sqm_index` (`price_per_sqm`,`lot_area_sqm`),
  KEY `properties_coordinates_lat_coordinates_lng_index` (`coordinates_lat`,`coordinates_lng`),
  CONSTRAINT `properties_broker_id_foreign` FOREIGN KEY (`broker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `properties_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `properties`
INSERT INTO `properties` VALUES
('1','Prime Beachfront Lot in Panglao Island','prime-beachfront-lot-panglao-3HCypY','Stunning 2,500 sqm beachfront property with white sand beach access. Perfect for resort development or luxury residence. Clear title, with road access and utilities nearby.','beachfront','available','8500.00','21250000.00','Alona Beach Road, Tawala','Panglao','Tawala','2500.00','0.2500','titled','TCT-12345',NULL,'9.53300000','123.85300000','{\"elevation\":2.5,\"soil_type\":\"sandy_loam\",\"flood_zone\":\"none\",\"environmental_clearance\":\"approved\",\"coastal_setback\":20,\"tide_level\":\"high_tide_safe\"}','1','1','1','1','[\"Alona Beach\",\"Panglao Airport\",\"Bohol Bee Farm\"]','Tourism Zone','[\"properties\\/images\\/beachfront1.jpg\",\"properties\\/images\\/beachfront2.jpg\",\"properties\\/images\\/beachfront3.jpg\"]','[\"beachfront_360_entrance.jpg\",\"beachfront_360_beach_view.jpg\",\"beachfront_360_property_center.jpg\",\"beachfront_360_sunset_view.jpg\"]','1','[{\"id\":1,\"image_index\":0,\"x_position\":45.2,\"y_position\":30.8,\"title\":\"Beach Access Point\",\"description\":\"Direct access to pristine white sand beach\",\"icon\":\"beach\"},{\"id\":2,\"image_index\":1,\"x_position\":60.5,\"y_position\":25.3,\"title\":\"Sunset Viewing Area\",\"description\":\"Perfect spot for watching spectacular sunsets\",\"icon\":\"sunset\"},{\"id\":3,\"image_index\":2,\"x_position\":35.7,\"y_position\":40.1,\"title\":\"Utility Connection Point\",\"description\":\"Water and electricity connections available\",\"icon\":\"utilities\"}]','[\"title_beachfront.pdf\"]','1','2',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL),
('2','Agricultural Land in Carmen - Rice Field','agricultural-land-carmen-3JWSoE','Productive 3-hectare rice field in Carmen, Bohol. Ideal for agricultural investment or development. With irrigation system and farm-to-market road access.','rice_field','available','450.00','13500000.00','Sitio Malubog, Poblacion','Carmen','Poblacion','30000.00','3.0000','tax_declared',NULL,'TD-2024-001','9.81670000','124.01670000',NULL,'1','1','0','0','[\"Carmen Public Market\",\"Chocolate Hills\",\"Mahogany Forest\"]','Agricultural','[\"ricefield1.jpg\",\"ricefield2.jpg\"]',NULL,'0',NULL,'[\"tax_declaration.pdf\"]','0','3',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL),
('3','Commercial Lot in Tagbilaran City Center','commercial-lot-tagbilaran-KmIeK1','Strategic 800 sqm commercial lot in the heart of Tagbilaran City. Perfect for business establishment, shopping center, or mixed-use development.','commercial_lot','available','25000.00','20000000.00','CPG Avenue, Poblacion 1','Tagbilaran City','Poblacion 1','800.00','0.0800','titled','TCT-67890',NULL,'9.64960000','123.85470000','{\"elevation\":15.2,\"soil_type\":\"clay_loam\",\"flood_zone\":\"low_risk\",\"zoning_compliance\":\"commercial_approved\",\"building_height_limit\":25,\"parking_requirement\":\"1_per_30sqm\"}','1','1','1','1','[\"Tagbilaran City Hall\",\"Island City Mall\",\"Bohol Quality Mall\"]','Commercial','[\"properties\\/images\\/commercial1.jpg\"]','[\"commercial_360_street_view.jpg\",\"commercial_360_lot_center.jpg\",\"commercial_360_city_view.jpg\"]','1','[{\"id\":1,\"image_index\":0,\"x_position\":50,\"y_position\":35,\"title\":\"Main Street Frontage\",\"description\":\"20-meter frontage on busy CPG Avenue\",\"icon\":\"road\"},{\"id\":2,\"image_index\":1,\"x_position\":40.3,\"y_position\":45.8,\"title\":\"Development Area\",\"description\":\"Optimal space for commercial building construction\",\"icon\":\"building\"}]','[\"title_commercial.pdf\",\"zoning_cert.pdf\"]','1','2',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL),
('4','Mountain View Residential Lot in Loboc','mountain-view-loboc-VMzOHi','Peaceful 1,200 sqm residential lot with stunning mountain views in Loboc. Perfect for building your dream home away from the city noise.','mountain_view','reserved','1800.00','2160000.00','Barangay Camayaan Hills','Loboc','Camayaan','1200.00','0.1200','mother_title','OCT-11111',NULL,'9.63330000','124.03330000',NULL,'1','0','1','0','[\"Loboc River\",\"Loboc Church\",\"Busay Falls\"]','Residential','[\"mountain1.jpg\",\"mountain2.jpg\"]',NULL,'0',NULL,NULL,'0','3',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL),
('5','Coconut Plantation in Ubay','coconut-plantation-ubay-1rsi9o','Productive 5-hectare coconut plantation with mature coconut trees. Generating steady income from copra production.','coconut_plantation','available','350.00','17500000.00','Sitio Kawayan, Poblacion','Ubay','Poblacion','50000.00','5.0000','titled','TCT-55555',NULL,'10.05000000','124.48330000',NULL,'1','1','0','0','[\"Ubay Port\",\"Ubay Church\",\"Kawasan Falls\"]','Agricultural','[\"coconut1.jpg\"]',NULL,'0',NULL,'[\"title_coconut.pdf\"]','0','2',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL),
('6','klsmfdklfa','klsmfdklfa-4hZ885','gdfagfad','residential_lot','available','5000.00','5000000.00','jsfakfasf','Clarin','Candajec','1000.00','0.1000','tax_declared','532fg',NULL,'80.00000000','80.00000000',NULL,'0','1','1','0','[\"gfsdlkgkdsg\"]','nfdgks','\"[]\"','[\"properties\\/virtual-tours\\/UN15dUYMEWUOw9WdUmeGDnzBI8GTSiFOmacNsTIV.jpg\"]','1',NULL,NULL,'0','6',NULL,'2025-08-27 07:23:00','2025-08-27 14:24:24','2025-08-27 14:24:24'),
('7','fsdkkfsdmkfska','fsdkkfsdmkfska-gC9e5x','sdfkafsakf','beachfront','available','5000.00','5000000.00','kjsdkfakfklsa','Carmen','Poblacion Norte','1000.00','0.1000','titled','1c1-12345',NULL,'80.00000000','80.00000000',NULL,'0','1','1','0',NULL,'jlfsdaf','[\"properties\\/images\\/JTBSoWLvp0ewSS0l3Efw7UuLJOXi0QB19PFrFUhB.jpg\"]',NULL,'0',NULL,NULL,'0','6',NULL,'2025-08-27 12:52:12','2025-08-27 12:52:12',NULL);

-- Table structure for `seller_requests`
DROP TABLE IF EXISTS `seller_requests`;
CREATE TABLE `seller_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_name` varchar(255) NOT NULL,
  `seller_email` varchar(255) NOT NULL,
  `seller_phone` varchar(255) DEFAULT NULL,
  `seller_address` text DEFAULT NULL,
  `property_title` varchar(255) NOT NULL,
  `property_description` text NOT NULL,
  `asking_price` decimal(15,2) NOT NULL,
  `property_area` decimal(10,2) NOT NULL,
  `area_unit` varchar(255) NOT NULL DEFAULT 'acres',
  `property_location` varchar(255) NOT NULL,
  `property_address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip_code` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `property_type` enum('residential','commercial','agricultural','industrial','recreational') NOT NULL DEFAULT 'residential',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `uploaded_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`uploaded_images`)),
  `status` enum('pending','under_review','approved','rejected','listed') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `assigned_broker_id` bigint(20) unsigned DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `property_id` bigint(20) unsigned DEFAULT NULL,
  `listed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `seller_requests_property_id_foreign` (`property_id`),
  KEY `seller_requests_status_created_at_index` (`status`,`created_at`),
  KEY `seller_requests_assigned_broker_id_status_index` (`assigned_broker_id`,`status`),
  KEY `seller_requests_seller_email_index` (`seller_email`),
  CONSTRAINT `seller_requests_assigned_broker_id_foreign` FOREIGN KEY (`assigned_broker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_requests_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `seller_requests`
INSERT INTO `seller_requests` VALUES
('1','Carmen Rodriguez','carmen@example.com','+63-919-555-0123',NULL,'Subdivision Lot In Dauis','500 sqm residential lot in a gated subdivision near Panglao bridge.','3500000.00','500.00','sqm','Dauis','Villa Esperanza Subdivision','Dauis','Bohol','6339',NULL,NULL,'residential','[\"gated_community\",\"near_airport\",\"utilities_available\"]',NULL,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL);

-- Table structure for `sessions`
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `sessions`
INSERT INTO `sessions` VALUES
('03m5NjdD3rO8q5Yf7zPKuWwynvfi0KqwxoRxtNVf',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.22621.5624','YToyOntzOjY6Il90b2tlbiI7czo0MDoiWGRsdEZ2N3BuME1ubWxZTEpTVTZtZG9IYTFVOXF2VXNBTERndGlkaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==','1756477821'),
('7A7lMJN4zBPqjHpEx73BbLihXUzJ6RIpXoAVQvyX',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.22621.5624','YTozOntzOjY6Il90b2tlbiI7czo0MDoibWtDQXdMSXd5MXU2Qm42T2JzaFRjRExzZUF1dDVNaFBvYUo1NEQxeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9waW5nIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==','1756480291'),
('dWZpsCGNaBCWgqAWh6rFOA4l0cWNbYGBTwSHKwmu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.22621.5624','YTozOntzOjY6Il90b2tlbiI7czo0MDoib0VsMm95S3hEajlOTmVUeGtFTU9jaDI3Vk1XRlJzNjEzY2FlNWhUUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9oZWFsdGgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19','1756480282'),
('JQboOxXkuvtshpSJ4TdiCaZy3i4xFo4LGKoSWMDU',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.22621.5624','YTozOntzOjY6Il90b2tlbiI7czo0MDoiR2xRMXhEbUhWdUlmY1VBc245UDY3MHRpQUtlWG52amlaY2VtMkUzRiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9oZWFsdGgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19','1756480132'),
('KMmB7Vdqy4lBK3dUld5Fr734XmyZOH6FKuwFreUu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.22621.5624','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUE3MFRDellZOHVtQzR2UVc2cWFhN1FEeldKMDQ0QkRqTDk0azF1aiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9oZWFsdGgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19','1756480242'),
('PZWVEqKw4bMq8NPNZP5UKm1bSkYGd8qyvWakTwL9','6','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUW00SEJCaDNHeXRITzdmVXdmU2VqUkJPNVJBMmt4eDdRZTVwVkVqaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9icm9rZXIvY2xpZW50cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjY7fQ==','1756479241'),
('qRljGlV2YuJ3IXqtrnsfJvv5wmavUTzEN7efJLVe','6','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNkczMHJCcXc0YndZbmNUOUM1VHZURjlqT09SbGJqVFpibHdwT3czdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Jyb2tlci9jbGllbnRzP3BhZ2U9MSI7fX0=','1756480559'),
('tqFnFKdJTdPkGfpH2rFDv1mngzsP0UwsgAmobMRY',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Trae/1.100.3 Chrome/132.0.6834.210 Electron/34.5.1 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidFlFMXM0bmtObXJlT012a3pKRVlCN3M3RWZyMmVLWWU2clYwcDM5WiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=','1756480235');

-- Table structure for `transactions`
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `broker_id` bigint(20) unsigned NOT NULL,
  `inquiry_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_number` varchar(255) NOT NULL,
  `offered_price` decimal(15,2) NOT NULL,
  `final_price` decimal(15,2) DEFAULT NULL,
  `commission_rate` decimal(5,4) NOT NULL DEFAULT 0.0600,
  `commission_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('inquiry','initial_contact','property_viewing','offer_made','negotiation','offer_accepted','contract_signed','due_diligence','financing','closing_preparation','finalized','cancelled') NOT NULL DEFAULT 'inquiry',
  `inquiry_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `first_contact_date` timestamp NULL DEFAULT NULL,
  `viewing_date` timestamp NULL DEFAULT NULL,
  `offer_date` timestamp NULL DEFAULT NULL,
  `acceptance_date` timestamp NULL DEFAULT NULL,
  `contract_date` timestamp NULL DEFAULT NULL,
  `closing_date` timestamp NULL DEFAULT NULL,
  `finalized_date` timestamp NULL DEFAULT NULL,
  `broker_notes` text DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `status_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`status_history`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_number_unique` (`transaction_number`),
  KEY `transactions_inquiry_id_foreign` (`inquiry_id`),
  KEY `transactions_broker_id_status_index` (`broker_id`,`status`),
  KEY `transactions_property_id_status_index` (`property_id`,`status`),
  KEY `transactions_client_id_status_index` (`client_id`,`status`),
  KEY `transactions_status_index` (`status`),
  KEY `transactions_finalized_date_index` (`finalized_date`),
  CONSTRAINT `transactions_broker_id_foreign` FOREIGN KEY (`broker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_inquiry_id_foreign` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `transactions`
INSERT INTO `transactions` VALUES
('1','4','1','3','1','TXN-SQ72L73P','2000000.00','2160000.00','0.0500','108000.00','finalized','2025-07-28 07:19:40','2025-07-30 07:19:40','2025-08-02 07:19:40','2025-08-07 07:19:40','2025-08-09 07:19:40','2025-08-12 07:19:40','2025-08-22 07:19:40','2025-08-24 07:19:40','Client loved the mountain view. Quick decision maker.',NULL,NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40');

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','broker','client') NOT NULL DEFAULT 'client',
  `prc_id` varchar(255) DEFAULT NULL,
  `prc_id_file` varchar(255) DEFAULT NULL,
  `business_permit` varchar(255) DEFAULT NULL,
  `business_permit_file` varchar(255) DEFAULT NULL,
  `additional_documents` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `application_status` enum('pending','under_review','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspended_until` timestamp NULL DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `suspended_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_approved_by_foreign` (`approved_by`),
  KEY `users_suspended_by_foreign` (`suspended_by`),
  CONSTRAINT `users_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_suspended_by_foreign` FOREIGN KEY (`suspended_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `users`
INSERT INTO `users` VALUES
('1','GeoCasa Admin','admin@geocasabohol.com','admin',NULL,NULL,NULL,NULL,NULL,'1','pending',NULL,NULL,NULL,NULL,NULL,'2025-08-27 07:19:39','$2y$12$ndivQsXPWzZf.bUsV8kVeeCyZQVpxhxOt22JCt7mmtwy5Rkb8w28e',NULL,'2025-08-27 07:19:39','2025-08-27 07:19:39',NULL,NULL,NULL,NULL),
('2','Maria Santos','maria@geocasabohol.com','broker',NULL,NULL,NULL,NULL,NULL,'1','pending',NULL,NULL,NULL,'2025-08-27 07:19:39','1','2025-08-27 07:19:39','$2y$12$yAlCCj2YsdGOidCKmK/hf.hbqi9/MfFA51kVadP53UVW7tdGvijGi',NULL,'2025-08-27 07:19:39','2025-08-27 07:19:39',NULL,NULL,NULL,NULL),
('3','Juan Dela Cruz','juan@geocasabohol.com','broker',NULL,NULL,NULL,NULL,NULL,'1','pending',NULL,NULL,NULL,'2025-08-27 07:19:40','1','2025-08-27 07:19:40','$2y$12$hCcTu57awOTysAgFGJaBXeroyawLHaJNw8Nxi6WfTw3jaszZCpxVi',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL,NULL,NULL,NULL),
('4','Pedro Reyes','pedro@geocasabohol.com','broker',NULL,NULL,NULL,NULL,NULL,'0','pending',NULL,NULL,NULL,NULL,NULL,'2025-08-27 07:19:40','$2y$12$qZ8LdhvEB73k8ZLtbqZk9uXxKyj3brqLsBYPj6qPNUHVXBg5CDji.',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL,NULL,NULL,NULL),
('5','Anna Garcia','anna@example.com','client',NULL,NULL,NULL,NULL,NULL,'1','pending',NULL,NULL,NULL,NULL,NULL,'2025-08-27 07:19:40','$2y$12$8y6MsMz.gZeDOHcu4CNc.es/JGTrFMmN5/PVvJRlH4y6iXxrG/V2G',NULL,'2025-08-27 07:19:40','2025-08-27 07:19:40',NULL,NULL,NULL,NULL),
('6','testbroker','testbroker@example.com','broker','1232435','credentials/prc/gi3E0K7hhrTO0khQhnFtcLt146jJ7G4B2ylKlGb4.jpg',NULL,NULL,NULL,'1','approved',NULL,'2025-08-27 07:20:48','2025-08-27 07:21:10','2025-08-27 07:21:10','1',NULL,'$2y$12$oeNMcGeb6F/eN4FS7DGRVuvFkfAzEARC49KIHF5zky4ZS2lIMvInO',NULL,'2025-08-27 07:20:48','2025-08-27 07:21:10',NULL,NULL,NULL,NULL),
('7','Thess Busalanan','thessy@gmail.com','client',NULL,NULL,NULL,NULL,NULL,'1','approved',NULL,NULL,NULL,'2025-08-29 14:56:15',NULL,NULL,'$2y$12$Et4epcC/gjbMrDfnZIMJ2elcLGdgnLUgOoYo.kdnNK6d61imubs7W',NULL,'2025-08-29 14:56:15','2025-08-29 14:56:15',NULL,NULL,NULL,NULL);

SET FOREIGN_KEY_CHECKS=1;
