-- ============================================================
-- MÓDULO: TRACKING MOTORIZADOS — Trimax CRM
-- Ejecutar en orden
-- ============================================================

-- 1. Motorizados
CREATE TABLE IF NOT EXISTS `motorizados` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nombre`            VARCHAR(255) NOT NULL,
    `sede`              VARCHAR(100) NOT NULL,
    `telefono`          VARCHAR(50) NULL,
    `traccar_device_id` BIGINT UNSIGNED NULL UNIQUE,
    `estado`            ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Órdenes de entrega
CREATE TABLE IF NOT EXISTS `ordenes_tracking` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cliente_nombre`   VARCHAR(255) NOT NULL,
    `cliente_telefono` VARCHAR(50) NULL,
    `referencia`       VARCHAR(100) NULL,
    `direccion`        TEXT NOT NULL,
    `latitud`          DECIMAL(10,7) NULL,
    `longitud`         DECIMAL(10,7) NULL,
    `estado`           ENUM('pendiente','en_ruta','entregado','fallido') NOT NULL DEFAULT 'pendiente',
    `sede`             VARCHAR(100) NOT NULL,
    `notas`            TEXT NULL,
    `created_at`       TIMESTAMP NULL,
    `updated_at`       TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Rutas del día
CREATE TABLE IF NOT EXISTS `rutas_tracking` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `motorizado_id`  BIGINT UNSIGNED NOT NULL,
    `fecha`          DATE NOT NULL,
    `estado`         ENUM('pendiente','en_ruta','completado') NOT NULL DEFAULT 'pendiente',
    `notas`          TEXT NULL,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    CONSTRAINT `fk_rutas_motorizado` FOREIGN KEY (`motorizado_id`)
        REFERENCES `motorizados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Paradas de cada ruta
CREATE TABLE IF NOT EXISTS `ruta_paradas` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ruta_id`         BIGINT UNSIGNED NOT NULL,
    `orden_id`        BIGINT UNSIGNED NOT NULL,
    `orden_secuencia` TINYINT UNSIGNED NOT NULL,
    `estado`          ENUM('pendiente','en_camino','completado','fallido') NOT NULL DEFAULT 'pendiente',
    `hora_llegada`    TIMESTAMP NULL,
    `hora_salida`     TIMESTAMP NULL,
    `notas`           TEXT NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    CONSTRAINT `fk_paradas_ruta`  FOREIGN KEY (`ruta_id`)  REFERENCES `rutas_tracking` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_paradas_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_tracking` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Caché de posiciones GPS
CREATE TABLE IF NOT EXISTS `tracking_positions` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `motorizado_id`       BIGINT UNSIGNED NOT NULL,
    `latitud`             DECIMAL(10,7) NOT NULL,
    `longitud`            DECIMAL(10,7) NOT NULL,
    `velocidad`           FLOAT NOT NULL DEFAULT 0,
    `rumbo`               FLOAT NULL,
    `altitud`             FLOAT NULL,
    `traccar_position_id` BIGINT UNSIGNED NULL,
    `registrado_en`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    INDEX `idx_motorizado_fecha` (`motorizado_id`, `registrado_en`),
    CONSTRAINT `fk_positions_motorizado` FOREIGN KEY (`motorizado_id`)
        REFERENCES `motorizados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Token de acceso diario para motorizados (agrega columna a rutas_tracking)
ALTER TABLE `rutas_tracking`
    ADD COLUMN `token_acceso` VARCHAR(64) NULL UNIQUE;

-- 7. Permiso en tabla users
ALTER TABLE `users`
    ADD COLUMN `puede_ver_motorizados` TINYINT(1) NOT NULL DEFAULT 0;
