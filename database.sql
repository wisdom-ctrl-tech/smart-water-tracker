-- ============================================================
-- database.sql
-- Creates the database and the three tables used by the project.
-- Import this file in phpMyAdmin (see the README, Step 3).
-- ============================================================

CREATE DATABASE IF NOT EXISTS `smart_water_tracker`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smart_water_tracker`;

-- ------------------------------------------------------------
-- Table 1: users  (one row per registered account)
-- reset_token is the SHA-256 hash of the password-reset link
-- token (NULL when unused). Saving a new token overwrites the
-- old one, so only the newest reset link works.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(100) NOT NULL,
  `email`            VARCHAR(190) NOT NULL,
  `password`         VARCHAR(255) NOT NULL,
  `daily_goal`       INT UNSIGNED NOT NULL DEFAULT 2500,
  `reset_token`      CHAR(64)     DEFAULT NULL,
  `reset_expires_at` DATETIME     DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`),
  KEY `idx_users_reset_token` (`reset_token`)
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

-- ------------------------------------------------------------
-- Table 3: containers  (the bottles, glasses and mugs a user can scan)
-- Each container has a unique code. The QR label printed from
-- containers.php opens scan.php?code=... and logs its volume.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `containers` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `name`       VARCHAR(60)  NOT NULL,
  `volume_ml`  INT UNSIGNED NOT NULL,
  `code`       VARCHAR(20)  NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_containers_code` (`code`),
  KEY `idx_containers_user` (`user_id`),
  CONSTRAINT `fk_containers_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
