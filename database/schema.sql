-- Minuta Electrónica - Demo de Vigilancia
-- Esquema de Base de Datos MySQL/MariaDB
-- Compatible con PHP 8.2+ y PDO

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLA: users (Usuarios del sistema)
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `role` ENUM('admin', 'supervisor', 'vigilante') DEFAULT 'vigilante',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: posts (Puestos de vigilancia)
-- ============================================
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `location` VARCHAR(200),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: shifts (Turnos de trabajo)
-- ============================================
DROP TABLE IF EXISTS `shifts`;
CREATE TABLE `shifts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME NULL,
    `status` ENUM('open', 'closed', 'in_progress') DEFAULT 'open',
    `observations` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_shifts_post_user` (`post_id`, `user_id`),
    INDEX `idx_shifts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: access_logs (Registro de Ingresos)
-- ============================================
DROP TABLE IF EXISTS `access_logs`;
CREATE TABLE `access_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `shift_id` INT UNSIGNED,
    `visitor_name` VARCHAR(100) NOT NULL,
    `visitor_document` VARCHAR(50),
    `visitor_company` VARCHAR(100),
    `purpose` TEXT,
    `entry_time` DATETIME NOT NULL,
    `exit_time` DATETIME NULL,
    `vehicle_plate` VARCHAR(20),
    `photo_path` VARCHAR(255),
    `status` ENUM('inside', 'exited') DEFAULT 'inside',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE SET NULL,
    INDEX `idx_access_post` (`post_id`),
    INDEX `idx_access_user` (`user_id`),
    INDEX `idx_access_shift` (`shift_id`),
    INDEX `idx_access_entry_time` (`entry_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: incidents (Novedades/Reportes de incidencias)
-- ============================================
DROP TABLE IF EXISTS `incidents`;
CREATE TABLE `incidents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `shift_id` INT UNSIGNED,
    `incident_type` ENUM('security', 'maintenance', 'visitor', 'other') NOT NULL,
    `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `action_taken` TEXT,
    `status` ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    `reported_at` DATETIME NOT NULL,
    `resolved_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE SET NULL,
    INDEX `idx_incidents_post` (`post_id`),
    INDEX `idx_incidents_user` (`user_id`),
    INDEX `idx_incidents_status` (`status`),
    INDEX `idx_incidents_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: documents (Comunicados y Actas)
-- ============================================
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED,
    `user_id` INT UNSIGNED NOT NULL,
    `shift_id` INT UNSIGNED,
    `document_type` ENUM('acta', 'comunicado', 'bitacora', 'other') NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `file_path` VARCHAR(255),
    `digital_signature` VARCHAR(255),
    `hash_integrity` CHAR(64),
    `is_signed` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE SET NULL,
    INDEX `idx_documents_post` (`post_id`),
    INDEX `idx_documents_type` (`document_type`),
    INDEX `idx_documents_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: audit_logs (Trazabilidad de todas las operaciones)
-- Requisito: Obligatoria en toda transacción
-- ============================================
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED,
    `post_id` INT UNSIGNED,
    `action` VARCHAR(50) NOT NULL,
    `table_name` VARCHAR(50) NOT NULL,
    `record_id` INT UNSIGNED,
    `old_values` JSON,
    `new_values` JSON,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE SET NULL,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_table` (`table_name`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- DATOS DE PRUEBA
-- ============================================
INSERT INTO `users` (`username`, `password_hash`, `full_name`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin@minuta.com', 'admin'),
('supervisor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Supervisor Uno', 'supervisor@minuta.com', 'supervisor'),
('vigilante1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vigilante Uno', 'vigilante1@minuta.com', 'vigilante');

INSERT INTO `posts` (`name`, `description`, `location`) VALUES
('Entrada Principal', 'Control de acceso principal del edificio', 'Edificio A - Planta Baja'),
('Parqueadero', 'Control de vehículos y parqueadero', 'Sótano 1'),
('Recepción', 'Puesto de recepción y atención al público', 'Edificio A - Primer Piso');
