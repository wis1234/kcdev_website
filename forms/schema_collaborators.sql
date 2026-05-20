-- ================================================================
-- KEMT Center - Table Collaborateurs
-- Base : u978666307_kemtcenter
-- ================================================================
-- IMPORT : selectionnez la base u978666307_kemtcenter dans
-- phpMyAdmin puis importez ce fichier pour ajouter la table.
-- ================================================================

CREATE TABLE IF NOT EXISTS `collaborators` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(150)    NOT NULL,
    `email`         VARCHAR(200)    NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `role`          ENUM('researcher', 'partner', 'admin') NOT NULL DEFAULT 'researcher',
    `status`        ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `last_login`    DATETIME        DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email` (`email`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Comptes d acces pour l espace membre';
