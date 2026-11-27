-- MemoryWall MySQL schema (MySQL 5.7+/8.x, MariaDB 10.4+)
-- Import this file using phpMyAdmin or: mysql -u root -p < database/schema.sql
CREATE DATABASE IF NOT EXISTS memorywall
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE memorywall;

CREATE TABLE IF NOT EXISTS visitors (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_visitors_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS photos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    visitor_id BIGINT UNSIGNED NOT NULL,
    stored_name VARCHAR(80) NOT NULL,
    original_name VARCHAR(180) NOT NULL,
    mime_type VARCHAR(80) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    consent_at TIMESTAMP NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_photos_stored_name (stored_name),
    KEY idx_photos_status_uploaded (status, uploaded_at, id),
    KEY idx_photos_visitor (visitor_id),
    CONSTRAINT fk_photos_visitor FOREIGN KEY (visitor_id)
        REFERENCES visitors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
