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

/* USUARIOS DE MARKETING */
CREATE TABLE users_marketing (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    location VARCHAR(255) NULL,
    unique_token VARCHAR(32) UNIQUE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

-- Índices
CREATE INDEX idx_users_marketing_unique_token ON users_marketing(unique_token);
CREATE INDEX idx_users_marketing_role ON users_marketing(role);
CREATE INDEX idx_users_marketing_location ON users_marketing(location);
CREATE INDEX idx_users_marketing_is_active ON users_marketing(is_active);


/* END USUARIOS DE MARKETING */

/* ENCUESTAS*/
CREATE TABLE surveys (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    client_name VARCHAR(255) NULL,
    experience_rating SMALLINT NOT NULL,
    service_quality_rating SMALLINT NOT NULL,
    comments TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    CONSTRAINT fk_surveys_user_id 
        FOREIGN KEY (user_id) 
        REFERENCES users_marketing(id) 
        ON DELETE CASCADE,
        
    PRIMARY KEY (id)
);


-- Índices para mejorar el rendimiento
CREATE INDEX idx_surveys_user_id ON surveys(user_id);
CREATE INDEX idx_surveys_experience_rating ON surveys(experience_rating);
CREATE INDEX idx_surveys_service_quality_rating ON surveys(service_quality_rating);
CREATE INDEX idx_surveys_created_at ON surveys(created_at);
/* END ENCUESTAS */


/*Asistente*/
CREATE TABLE knowledge_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(100),
    pregunta TEXT,
    respuesta TEXT,
    keywords TEXT,
    ejemplos TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- database/migrations/xxxx_create_ventas_table.php
CREATE TABLE ventas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    tipo_documento VARCHAR(50),
    nro_documento VARCHAR(100),
    nro_orden_fabricacion VARCHAR(100),
    ruc_dni VARCHAR(20),
    razon_social VARCHAR(255),
    tipo_cliente VARCHAR(100),
    motorizado VARCHAR(100),
    sede VARCHAR(100),
    zona VARCHAR(100),
    cod_producto VARCHAR(50),
    descripcion TEXT,
    importe DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    importe_global DECIMAL(12, 2),
    cantidad INT,
    anio INT,
    mes INT,
    tallado VARCHAR(100),
    marca VARCHAR(100),
    diseno VARCHAR(100),
    material VARCHAR(100),
    tipo_fotocromatico VARCHAR(100),
    color VARCHAR(100),
    tipo_articulo VARCHAR(100),
    tipo_articulo2 VARCHAR(100),
    tipo_tributo VARCHAR(100),
    doc_referencia_nc VARCHAR(100),
    motivo_nc TEXT,
    observacion_nc TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_fecha (fecha),
    INDEX idx_cliente (ruc_dni),
    INDEX idx_sede (sede),
    INDEX idx_producto (cod_producto),
    INDEX idx_marca (marca),
    INDEX idx_anio_mes (anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
ADD COLUMN puede_ver_ventas_consolidadas TINYINT(1) NOT NULL DEFAULT 0
AFTER sede;
