-- ============================================================
-- MÓDULO: CONTROL DE RUTAS GPS — Trimax CRM
-- Ejecutar en MySQL
-- ============================================================

-- Columna de token GPS en motorizados (ejecutar una vez)
ALTER TABLE `motorizados`
    ADD COLUMN IF NOT EXISTS `token_gps` VARCHAR(64) NULL UNIQUE;

-- ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `rutas_gps` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `motorizado_id` BIGINT UNSIGNED NOT NULL,
    `started_at`    TIMESTAMP NOT NULL,
    `ended_at`      TIMESTAMP NULL,
    `distance_km`   DECIMAL(10,3) NULL,
    `polyline`      JSON NULL,
    `status`        ENUM('active','completed') NOT NULL DEFAULT 'active',
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    INDEX `idx_rutas_gps_motorizado`   (`motorizado_id`),
    INDEX `idx_rutas_gps_started`      (`started_at`),
    INDEX `idx_rutas_gps_status`       (`status`),
    CONSTRAINT `fk_rutas_gps_motorizado`
        FOREIGN KEY (`motorizado_id`) REFERENCES `motorizados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
