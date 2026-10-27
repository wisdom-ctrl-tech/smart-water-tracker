-- ============================================================
-- database.sql
-- Creates the database and the two tables used by the project.
-- Import this file in phpMyAdmin (see the README, Step 3).
-- ============================================================

CREATE DATABASE IF NOT EXISTS `smart_water_tracker`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smart_water_tracker`;

-- ------------------------------------------------------------
-- Table 1: users  (one row per registered account)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `email`       VARCHAR(190) NOT NULL,
  `password`    VARCHAR(255) NOT NULL,
  `daily_goal`  INT UNSIGNED NOT NULL DEFAULT 2500,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table 2: water_logs  (one row per glass of water)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `water_logs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `amount_ml`  INT UNSIGNED NOT NULL,
  `logged_at`  DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_water_logs_user_date` (`user_id`, `logged_at`),
  CONSTRAINT `fk_water_logs_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
