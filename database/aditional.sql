/*ADICIONAL COLUMNS - TABLE ACUERDOS COMERCIALES*/

ALTER TABLE acuerdos_comerciales
ADD COLUMN habilitado BOOLEAN DEFAULT TRUE AFTER aprobado_at,
ADD COLUMN motivo_deshabilitacion TEXT NULL AFTER habilitado,
ADD COLUMN deshabilitado_at TIMESTAMP NULL AFTER motivo_deshabilitacion,
ADD COLUMN deshabilitado_por BIGINT UNSIGNED NULL AFTER deshabilitado_at,
ADD COLUMN motivo_extension TEXT NULL AFTER deshabilitado_por,
ADD COLUMN extendido_at TIMESTAMP NULL AFTER motivo_extension,
ADD COLUMN extendido_por BIGINT UNSIGNED NULL AFTER extendido_at;

ALTER TABLE acuerdos_comerciales
ADD CONSTRAINT acuerdos_comerciales_deshabilitado_por_foreign
    FOREIGN KEY (deshabilitado_por) REFERENCES users(id);

ALTER TABLE acuerdos_comerciales
ADD CONSTRAINT acuerdos_comerciales_extendido_por_foreign
    FOREIGN KEY (extendido_por) REFERENCES users(id);
/*END ADICIONAL COLUMNS - TABLE ACUERDOS COMERCIALES*/

/*ADICIONAL COLUMNS - TABLE ACUERDOS COMERCIALES DETALLES*/

ALTER TABLE acuerdos_comerciales
ADD COLUMN motivo_rehabilitacion TEXT NULL AFTER motivo_deshabilitacion,
ADD COLUMN rehabilitado_at TIMESTAMP NULL AFTER motivo_rehabilitacion,
ADD COLUMN rehabilitado_por BIGINT UNSIGNED NULL AFTER rehabilitado_at;

ALTER TABLE acuerdos_comerciales
ADD CONSTRAINT acuerdos_comerciales_rehabilitado_por_foreign
    FOREIGN KEY (rehabilitado_por) REFERENCES users(id);

/*END ADICIONAL COLUMNS - TABLE ACUERDOS COMERCIALES DETALLES*/


/*TABLA ACUERDOS COMERCIALES*/
CREATE TABLE `acuerdos_comerciales` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `numero_acuerdo` VARCHAR(255) NOT NULL UNIQUE,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que creó el acuerdo',
    
    -- Datos del cliente
    `sede` VARCHAR(255) NOT NULL,
    `ruc` VARCHAR(255) NOT NULL,
    `razon_social` VARCHAR(255) NOT NULL,
    `consultor` VARCHAR(255) NOT NULL,
    `ciudad` VARCHAR(255) NOT NULL,
    
    -- Detalles del acuerdo
    `acuerdo_comercial` VARCHAR(255) NOT NULL,
    `tipo_promocion` VARCHAR(255) NOT NULL,
    `marca` VARCHAR(255) NOT NULL,
    `ar` VARCHAR(255) NULL,
    `disenos` VARCHAR(255) NULL,
    `material` VARCHAR(255) NULL,
    `comentarios` TEXT NULL,
    
    -- Fechas
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL,
    
    -- Estados y aprobaciones (ENUM)
    `estado` ENUM('Solicitado', 'Vigente', 'Vencido', 'Deshabilitado') NOT NULL DEFAULT 'Solicitado',
    `validado` ENUM('Pendiente', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    `aprobado` ENUM('Pendiente', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    
    -- Usuarios que validan/aprueban
    `validado_por` BIGINT UNSIGNED NULL,
    `validado_at` TIMESTAMP NULL,
    `aprobado_por` BIGINT UNSIGNED NULL,
    `aprobado_at` TIMESTAMP NULL,
    
    -- Archivos adjuntos
    `archivos_adjuntos` JSON NULL,
    
    -- Timestamps y SoftDeletes
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,

    -- Foreign Keys
    CONSTRAINT `acuerdos_comerciales_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),

    CONSTRAINT `acuerdos_comerciales_validado_por_foreign`
        FOREIGN KEY (`validado_por`) REFERENCES `users`(`id`),

    CONSTRAINT `acuerdos_comerciales_aprobado_por_foreign`
        FOREIGN KEY (`aprobado_por`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*END TABLA ACUERDOS COMERCIALES*/