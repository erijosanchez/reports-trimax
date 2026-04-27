-- ============================================================
-- MÓDULO: CONTROL DE RUTAS GPS — Trimax CRM
-- Ejecutar en MySQL
-- ============================================================

-- ------------------------------------------------------------
-- Estructura de la tabla `motorizados`
-- ------------------------------------------------------------
CREATE TABLE motorizados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    sede VARCHAR(255) NOT NULL,
    telefono VARCHAR(255) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- ------------------------------------------------------------
-- GPS Rutas
-- ------------------------------------------------------------
CREATE TABLE gps_rutas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    motorizado_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    started_at TIMESTAMP NULL DEFAULT NULL,
    ended_at TIMESTAMP NULL DEFAULT NULL,
    distance_km DECIMAL(10,3) DEFAULT 0,
    polyline JSON NULL,
    status ENUM('pendiente', 'activa', 'completada') DEFAULT 'pendiente',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_gps_rutas_motorizado
        FOREIGN KEY (motorizado_id)
        REFERENCES motorizados(id)
        ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- GPS Posiciones
-- ------------------------------------------------------------
CREATE TABLE gps_posiciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    motorizado_id BIGINT UNSIGNED NOT NULL,
    ruta_id BIGINT UNSIGNED NOT NULL,
    latitud DECIMAL(10,7) NOT NULL,
    longitud DECIMAL(10,7) NOT NULL,
    velocidad FLOAT DEFAULT 0,
    precicion FLOAT NULL,
    capturado_en TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_gps_posiciones_motorizado
        FOREIGN KEY (motorizado_id)
        REFERENCES motorizados(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_gps_posiciones_ruta
        FOREIGN KEY (ruta_id)
        REFERENCES gps_rutas(id)
        ON DELETE CASCADE,

    INDEX idx_motorizado_capturado (motorizado_id, capturado_en),
    INDEX idx_ruta (ruta_id)
);

-- ------------------------------------------------------------
-- entregas
-- ------------------------------------------------------------
CREATE TABLE entregas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    motorizado_id BIGINT UNSIGNED NOT NULL,
    ruta_id BIGINT UNSIGNED NOT NULL,
    cliente_nombre VARCHAR(255) NOT NULL,
    cliente_telefono VARCHAR(255) NULL,
    referencia VARCHAR(255) NULL,
    direccion TEXT NOT NULL,
    latitud DECIMAL(10,7) NULL,
    longitud DECIMAL(10,7) NULL,
    orden_secuencia INT NOT NULL,
    estado ENUM('pendiente', 'completado', 'fallido') DEFAULT 'pendiente',
    entrega_latitud DECIMAL(10,7) NULL,
    entrega_longitud DECIMAL(10,7) NULL,
    entregado_en TIMESTAMP NULL DEFAULT NULL,
    notas TEXT NULL,
    sede VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_entregas_motorizado
        FOREIGN KEY (motorizado_id)
        REFERENCES motorizados(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_entregas_ruta
        FOREIGN KEY (ruta_id)
        REFERENCES gps_rutas(id)
        ON DELETE CASCADE
);