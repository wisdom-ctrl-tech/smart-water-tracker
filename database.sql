-- ============================================================
-- database.sql
-- Creates the database and the four tables used by the project.
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

-- ------------------------------------------------------------
-- Table 4: password_resets  (one row per "forgot password" request)
-- The real token is never stored: only its SHA-256 hash, so a copy
-- of this table cannot be used to reset anybody's password.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `token_hash`  CHAR(64)     NOT NULL,
  `expires_at`  DATETIME     NOT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_password_resets_token` (`token_hash`),
  KEY `idx_password_resets_user` (`user_id`),
  CONSTRAINT `fk_password_resets_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
