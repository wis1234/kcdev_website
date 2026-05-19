-- ================================================================
-- KEMT Center - Schema MySQL
-- Base : u978666307_kemtcenter (Hostinger)
-- Encodage : utf8mb4
-- ================================================================
-- IMPORT : selectionnez la base u978666307_kemtcenter dans
-- phpMyAdmin puis importez ce fichier directement.
-- ================================================================

-- -----------------------------------------------------------------
-- TABLE : contact_messages
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(150)     NOT NULL                    COMMENT 'Nom complet',
    `email`        VARCHAR(200)     NOT NULL                    COMMENT 'Email',
    `organization` VARCHAR(200)     DEFAULT NULL                COMMENT 'Institution ou organisation',
    `phone`        VARCHAR(30)      DEFAULT NULL                COMMENT 'Telephone',
    `subject_type` ENUM(
        'Collaboration','Candidature','Publication',
        'Formation','Partenariat','Autre'
    )                               NOT NULL DEFAULT 'Autre'    COMMENT 'Categorie de la demande',
    `subject`      VARCHAR(250)     NOT NULL                    COMMENT 'Objet du message',
    `message`      TEXT             NOT NULL                    COMMENT 'Corps du message',
    `ip_address`   VARCHAR(45)      DEFAULT NULL                COMMENT 'Adresse IP IPv4 ou IPv6',
    `user_agent`   VARCHAR(255)     DEFAULT NULL                COMMENT 'Navigateur user-agent',
    `status`       ENUM(
        'new','read','replied','archived','spam'
    )                               NOT NULL DEFAULT 'new'      COMMENT 'Statut de traitement',
    `admin_notes`  TEXT             DEFAULT NULL                COMMENT 'Notes internes admin',
    `replied_at`   DATETIME         DEFAULT NULL                COMMENT 'Date de reponse',
    `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_status`       (`status`),
    INDEX `idx_subject_type` (`subject_type`),
    INDEX `idx_created_at`   (`created_at`),
    INDEX `idx_email`        (`email`),
    INDEX `idx_ip`           (`ip_address`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Messages du formulaire de contact KEMT Center';


-- -----------------------------------------------------------------
-- TABLE : newsletter_subscribers
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `email`         VARCHAR(200)    NOT NULL,
    `name`          VARCHAR(150)    DEFAULT NULL,
    `lang`          ENUM('fr','en') NOT NULL DEFAULT 'fr'       COMMENT 'Langue de preference',
    `token`         VARCHAR(64)     NOT NULL                    COMMENT 'Token de desinscription',
    `confirmed`     TINYINT(1)      NOT NULL DEFAULT 0          COMMENT '1 = email confirme',
    `confirmed_at`  DATETIME        DEFAULT NULL,
    `unsubscribed`  TINYINT(1)      NOT NULL DEFAULT 0,
    `ip_address`    VARCHAR(45)     DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email` (`email`),
    INDEX `idx_confirmed` (`confirmed`),
    INDEX `idx_token`     (`token`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Abonnes a la newsletter du KEMT Center';


-- -----------------------------------------------------------------
-- TABLE : job_applications
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `job_applications` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `position`      VARCHAR(200)    NOT NULL                    COMMENT 'Poste vise',
    `name`          VARCHAR(150)    NOT NULL,
    `email`         VARCHAR(200)    NOT NULL,
    `phone`         VARCHAR(30)     DEFAULT NULL,
    `cv_path`       VARCHAR(500)    DEFAULT NULL                COMMENT 'Chemin du CV uploade',
    `cover_letter`  TEXT            DEFAULT NULL,
    `linkedin`      VARCHAR(300)    DEFAULT NULL,
    `status`        ENUM(
        'new','reviewing','interview','rejected','hired'
    )                               NOT NULL DEFAULT 'new',
    `admin_notes`   TEXT            DEFAULT NULL,
    `ip_address`    VARCHAR(45)     DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_status`   (`status`),
    INDEX `idx_position` (`position`),
    INDEX `idx_email`    (`email`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Candidatures recues via la page Carrieres KEMT';


-- -----------------------------------------------------------------
-- TABLE : event_registrations
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `event_registrations` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `event_name`    VARCHAR(250)    NOT NULL                    COMMENT 'Nom de l evenement',
    `event_date`    DATE            DEFAULT NULL,
    `name`          VARCHAR(150)    NOT NULL,
    `email`         VARCHAR(200)    NOT NULL,
    `phone`         VARCHAR(30)     DEFAULT NULL,
    `organization`  VARCHAR(200)    DEFAULT NULL,
    `message`       TEXT            DEFAULT NULL                COMMENT 'Questions ou remarques',
    `status`        ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    `ip_address`    VARCHAR(45)     DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_event`  (`event_name`),
    INDEX `idx_status` (`status`),
    INDEX `idx_email`  (`email`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Inscriptions aux evenements et ateliers KEMT';
