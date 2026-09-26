-- -----------------------------------------------------
-- YRU Train Tracking System - MySQL Database Dump
-- Generated on 2026-07-02 10:33:05
-- -----------------------------------------------------

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- -----------------------------------------------------
-- Table structure for table `migrations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` INT NOT NULL,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_06_11_000001_create_electric_trains_table', '1'),
('5', '2026_06_11_000002_create_routes_table', '1'),
('6', '2026_06_11_000003_create_train_locations_table', '1'),
('7', '2026_06_11_000004_create_stations_table', '1'),
('8', '2026_06_11_000005_create_schedules_table', '1'),
('9', '2026_06_11_000006_create_maintenances_table', '1'),
('10', '2026_06_11_000007_create_role_permissions_table', '1'),
('11', '2026_06_11_000008_create_travel_histories_table', '1'),
('12', '2026_06_11_000009_create_notifications_table', '1'),
('13', '2026_07_01_145717_add_name_and_email_to_users_table', '2');

-- -----------------------------------------------------
-- Table structure for table `users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `usage_rights` VARCHAR(255) NULL,
  `user_role` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `name` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`user_id`, `username`, `password`, `usage_rights`, `user_role`, `remember_token`, `created_at`, `updated_at`, `name`, `email`) VALUES
('USR-178112167051', '406665014@yru.ac.th', '$2y$12$6NMbj9JsFg2pELyUYuBRAObVsHtqKoeCPwBAr4/M0ZgYNCkMK6bdy', 'active', 'passenger', NULL, '2026-06-10 20:01:10', '2026-06-10 20:01:10', NULL, NULL),
('USR-000001', 'user@yru.ac.th', '$2y$12$DpikFzFp0cBF/dzhiXj05eZnrUQCbajIGR99mrUutMi0xJTZxv9XO', 'active', 'Passenger', 'rXkQ7YapGYPNESIAEUFm24QtHh5dYCnaIoP0fFIO3MzaYtoeDyaKoSLmMGwg', '2026-07-01 07:19:59', '2026-07-01 13:56:56', NULL, NULL),
('USR-000002', 'tracking1@yru.ac.th', '$2y$12$nERy00EhA5amNuNTcfpDFuJzVfwoQvUF4i1HUFv.xxYRS/fudt0li', 'active', 'Driver', NULL, '2026-07-01 07:19:59', '2026-07-01 07:19:59', NULL, NULL),
('USR-000003', 'tracking2@yru.ac.th', '$2y$12$tA3Dy7tTIogj8WY6hklDmu3wYD/LF3dVHxA7jBZfnNQJoD1tfIMqS', 'active', 'Driver', NULL, '2026-07-01 07:19:59', '2026-07-01 07:19:59', NULL, NULL),
('USR-000004', 'executive@yru.ac.th', '$2y$12$RiS9TUkhGHK20OD5cU4CI.CpZKfWtgDtFdH8GN79kL1ngj3lEh1KS', 'active', 'Operator', NULL, '2026-07-01 07:19:59', '2026-07-01 07:19:59', NULL, NULL),
('USR-000005', 'admin@yru.ac.th', '$2y$12$F.R6ZKGZ00VgEKks6.fVNeV8zU3ZaP3uBpU02VQe0b08W0knmRk2q', 'active', 'Administrator', NULL, '2026-07-01 07:19:59', '2026-07-01 07:22:00', NULL, NULL),
('USR001', 'sakeenahuma@gmail.com', '$2y$12$TQHV3PNK.bfWBLhuOmknU.OXoJhDW0cObwqL60zMB4lFhjuH2teE2', 'Active', 'Passenger', 'ZcAem30bfcwhRqxRNS7fDNYuMYsBL4gtglUo9BIPBpVXZ8fA2MhZO5McTz70', '2026-07-01 15:00:23', '2026-07-01 15:00:23', 'สากีน๊ะ อูมา', 'sakeenahuma@gmail.com'),
('USR002', 'fasai@gmail.com', '$2y$12$dvNkK.2lYoTng9Vlbmxvb.3YaqsydCjnHoDpa8vQ7RUAWdqXJH9ve', 'Active', 'Passenger', NULL, '2026-07-02 07:24:02', '2026-07-02 07:24:02', 'นางสาวพิชญา ชุมมิคสา', 'fasai@gmail.com'),
('USR003', 'fasa@gmail.com', '$2y$12$Wzz60vJ9XrXLj07bmltyKOEcaOzFoETj32.JO.3HLkLI9HATJgA4G', 'Active', 'Passenger', 'x3Olk1EvN1DPwRvUSjlfJsHtYC7xIhM15IIcucoPntEJXgF7EKrovcQsB0hO', '2026-07-02 07:25:37', '2026-07-02 07:25:37', 'นางสาวพิชญา ชุมมิคสา', 'fasa@gmail.com');

-- -----------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `sessions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NULL,
  `ip_address` VARCHAR(255) NULL,
  `user_agent` TEXT NULL,
  `payload` TEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `cache`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `cache_locks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `jobs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` INT NOT NULL,
  `queue` VARCHAR(255) NOT NULL,
  `payload` TEXT NOT NULL,
  `attempts` INT NOT NULL,
  `reserved_at` INT NULL,
  `available_at` INT NOT NULL,
  `created_at` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `job_batches`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` TEXT NOT NULL,
  `options` TEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `failed_jobs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` INT NOT NULL,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` TEXT NOT NULL,
  `exception` TEXT NOT NULL,
  `failed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `electric_trains`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `electric_trains`;
CREATE TABLE `electric_trains` (
  `skytrain_code` VARCHAR(10) NOT NULL,
  `car_number` VARCHAR(255) NOT NULL,
  `electric_train_type` VARCHAR(255) NOT NULL,
  `car_status` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`skytrain_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `routes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `routes`;
CREATE TABLE `routes` (
  `route_code` VARCHAR(10) NOT NULL,
  `route_name` VARCHAR(255) NOT NULL,
  `route_details` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`route_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `train_locations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `train_locations`;
CREATE TABLE `train_locations` (
  `position_code` INT NOT NULL,
  `skytrain_code` VARCHAR(10) NOT NULL,
  `latitude` DECIMAL(10, 6) NOT NULL,
  `longitude` DECIMAL(10, 6) NOT NULL,
  `recorded_time` DATETIME NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`position_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `stations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `stations`;
CREATE TABLE `stations` (
  `parking_spot_code` VARCHAR(255) NOT NULL,
  `route_code` VARCHAR(10) NOT NULL,
  `parking_spot_name` VARCHAR(255) NOT NULL,
  `order_of_parking_spots` INT NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`parking_spot_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `schedules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `timetable_code` VARCHAR(255) NOT NULL,
  `skytrain_code` VARCHAR(10) NOT NULL,
  `driver_id` VARCHAR(10) NOT NULL,
  `route_code` VARCHAR(10) NOT NULL,
  `departure_time` TIME NOT NULL,
  `arrival_time` TIME NOT NULL,
  `date` DATE NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`timetable_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `maintenances`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `maintenances`;
CREATE TABLE `maintenances` (
  `maintenance_code` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `skytrain_code` VARCHAR(10) NOT NULL,
  `repair_details` VARCHAR(255) NOT NULL,
  `repair_notification_date` DATE NOT NULL,
  `repair_start_date` DATE NULL,
  `date_of_repair_completion` DATE NULL,
  `repair_status` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`maintenance_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `role_permissions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` INT NOT NULL,
  `role` VARCHAR(255) NOT NULL,
  `permission` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `travel_histories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `travel_histories`;
CREATE TABLE `travel_histories` (
  `id` INT NOT NULL,
  `skytrain_code` VARCHAR(10) NOT NULL,
  `driver_id` VARCHAR(10) NULL,
  `route_code` VARCHAR(10) NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NULL,
  `distance_km` DECIMAL(8, 2) NOT NULL DEFAULT '0',
  `travel_status` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `notifications`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
