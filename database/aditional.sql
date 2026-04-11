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


/*Descuentos especiales*/
CREATE TABLE descuentos_especiales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Número único del descuento
    numero_descuento VARCHAR(255) NOT NULL UNIQUE,

    -- Usuario creador
    user_id BIGINT UNSIGNED NOT NULL,

    -- Nuevos campos
    numero_factura VARCHAR(255) NULL,
    numero_orden   VARCHAR(255) NULL,

    -- Datos del cliente
    sede VARCHAR(255) NOT NULL,
    ruc VARCHAR(255) NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    consultor VARCHAR(255) NOT NULL,
    ciudad VARCHAR(255) NOT NULL,

    -- Detalles del descuento
    descuento_especial TEXT NOT NULL,

    -- Tipo de descuento
    tipo ENUM(
        'ANULACION',
        'CORTESIA',
        'DESCUENTO ADICIONAL',
        'DESCUENTO TOTAL',
        'OTROS'
    ) NOT NULL,

    -- Detalles del producto
    marca VARCHAR(255) NOT NULL,
    ar VARCHAR(255) NULL,
    disenos VARCHAR(255) NULL,
    material VARCHAR(255) NULL,
    comentarios TEXT NULL,

    -- Validación
    aplicado ENUM('Pendiente', 'Aprobado', 'Rechazado')
        DEFAULT 'Pendiente',

    aplicado_por BIGINT UNSIGNED NULL,
    aplicado_at TIMESTAMP NULL,
    
    -- Aprobación
    aprobado ENUM('Pendiente', 'Aprobado', 'Rechazado')
        DEFAULT 'Pendiente',

    aprobado_por BIGINT UNSIGNED NULL,
    aprobado_at TIMESTAMP NULL,

    -- Archivos adjuntos
    archivos_adjuntos JSON NULL,

    -- Control habilitación
    habilitado BOOLEAN DEFAULT TRUE,
    motivo_deshabilitacion TEXT NULL,
    deshabilitado_at TIMESTAMP NULL,
    deshabilitado_por BIGINT UNSIGNED NULL,

    -- Rehabilitación
    motivo_rehabilitacion TEXT NULL,
    rehabilitado_at TIMESTAMP NULL,
    rehabilitado_por BIGINT UNSIGNED NULL,

    -- Timestamps y soft deletes
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    -- Índices
    INDEX idx_numero_descuento (numero_descuento),
    INDEX idx_numero_factura (numero_factura),
    INDEX idx_numero_orden (numero_orden),
    INDEX idx_user_id (user_id),
    INDEX idx_sede (sede),
    INDEX idx_aplicado (aplicado),
    INDEX idx_aprobado (aprobado),
    INDEX idx_created_at (created_at),

    -- Foreign Keys
    CONSTRAINT fk_desc_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_desc_aplicado
        FOREIGN KEY (aplicado_por)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_desc_aprobado
        FOREIGN KEY (aprobado_por)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_desc_deshabilitado
        FOREIGN KEY (deshabilitado_por)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_desc_rehabilitado
        FOREIGN KEY (rehabilitado_por)
        REFERENCES users(id)
        ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
/*End Descuentos especiales*/

/* agrega columna para asinar la ventana de desceuntos*/
ALTER TABLE users
ADD COLUMN puede_ver_descuentos_especiales BOOLEAN
DEFAULT FALSE
AFTER puede_ver_ventas_consolidadas;
/* end agrega columna para asinar la ventana de desceuntos*/

/* Groq */

CREATE TABLE ai_interactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(255) NOT NULL,
    user_role VARCHAR(50) NULL,
    module VARCHAR(100) NOT NULL DEFAULT 'general',

    question TEXT NOT NULL,
    context JSON NULL,
    ai_response TEXT NOT NULL,

    response_type ENUM('direct_answer','action','clarification','error')
        NOT NULL DEFAULT 'direct_answer',

    was_helpful TINYINT(1) NULL,
    feedback_comment TEXT NULL,
    action_taken VARCHAR(255) NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    INDEX idx_module (module),
    INDEX idx_user_role (user_role),
    INDEX idx_was_helpful (was_helpful),

    CONSTRAINT fk_ai_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
);


CREATE TABLE ai_knowledge_base (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    category VARCHAR(100) NOT NULL,
    question_pattern TEXT NOT NULL,
    answer_template TEXT NOT NULL,

    confidence_score DECIMAL(3,2) NOT NULL DEFAULT 0.75,
    usage_count INT NOT NULL DEFAULT 0,
    success_rate DECIMAL(3,2) NOT NULL DEFAULT 1.00,

    last_used_at TIMESTAMP NULL,

    created_from_interactions INT NOT NULL DEFAULT 1,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/*modifcamos la consulta*/

ALTER TABLE ai_interactions MODIFY COLUMN response_type VARCHAR(50) NOT NULL DEFAULT 'direct_answer';


ALTER TABLE `users`
ADD COLUMN `puede_ver_consultar_orden` TINYINT(1) NOT NULL DEFAULT 0 AFTER `puede_ver_descuentos_especiales`,
ADD COLUMN `puede_ver_acuerdos_comerciales` TINYINT(1) NOT NULL DEFAULT 0 AFTER `puede_ver_consultar_orden`,
ADD COLUMN `puede_ver_lead_time` TINYINT(1) NOT NULL DEFAULT 0 AFTER `puede_ver_acuerdos_comerciales`;



/*Agrega columnas para permisos de requerimientos*/
ALTER TABLE users
ADD COLUMN puede_crear_requerimientos TINYINT(1) NOT NULL DEFAULT 0
    AFTER puede_ver_lead_time,
ADD COLUMN puede_gestionar_requerimientos TINYINT(1) NOT NULL DEFAULT 0
    AFTER puede_crear_requerimientos,
ADD COLUMN puede_ver_todos_requerimientos TINYINT(1) NOT NULL DEFAULT 0
    AFTER puede_gestionar_requerimientos;

/*Modifica el estado de requerimientos_personal*/
ALTER TABLE requerimientos_personal
MODIFY COLUMN estado 
    ENUM('Pendiente','En Proceso','Contratado','Cancelado')
    NOT NULL
    DEFAULT 'Pendiente';

/*Modifica el tipo de evento de requerimiento_historial*/
ALTER TABLE requerimiento_historial
MODIFY COLUMN tipo_evento 
    ENUM(
        'creacion',
        'cambio_estado',
        'asignacion_rh',
        'publicacion_oferta',
        'revision_cvs',
        'entrevista_virtual',
        'entrevista_presencial',
        'evaluacion',
        'oferta_candidato',
        'nota',
        'alerta_sla'
    )
    NOT NULL;

/*Tabla de requerimientos de personal*/
CREATE TABLE requerimientos_personal (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    solicitante_id BIGINT UNSIGNED NOT NULL,
    gerencia VARCHAR(255) NOT NULL DEFAULT 'GERENCIA COMERCIAL',
    puesto VARCHAR(255) NOT NULL,
    sede VARCHAR(255) NOT NULL,
    jefe_directo VARCHAR(255) NOT NULL,
    tipo ENUM('Regular', 'Urgente') NOT NULL,
    condiciones_oferta TEXT NULL,
    comentarios TEXT NULL,

    responsable_rh_id BIGINT UNSIGNED NULL,
    responsable_rh_externo VARCHAR(255) NULL,

    estado ENUM('En Proceso', 'Contratado', 'Cancelado') 
        NOT NULL DEFAULT 'En Proceso',

    sla INT NOT NULL DEFAULT 45,

    fecha_solicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_req_solicitante
        FOREIGN KEY (solicitante_id) REFERENCES users(id),

    CONSTRAINT fk_req_responsable_rh
        FOREIGN KEY (responsable_rh_id) REFERENCES users(id)
        ON DELETE SET NULL
);

/*Tabla de historial de requerimientos*/
CREATE TABLE requerimiento_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requerimiento_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    tipo_evento VARCHAR(255) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    estado_anterior VARCHAR(255) NULL,
    estado_nuevo VARCHAR(255) NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_hist_requerimiento
        FOREIGN KEY (requerimiento_id)
        REFERENCES requerimientos_personal(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_hist_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
);

/* Agrega columna para permisos de pendiente de entrega montura*/
ALTER TABLE users ADD COLUMN puede_ver_pendiente_entrega_montura TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_ver_lead_time;

ALTER TABLE users ADD COLUMN puede_ver_venta_clientes TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_ver_pendiente_entrega_montura;

ALTER TABLE users ADD COLUMN puede_ver_ordenes_x_sede TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_ver_venta_clientes;

ALTER TABLE users ADD COLUMN puede_ver_asignacion_bases TINYINT(1) NOT NULL DEFAULT 0 AFTER puede_ver_ordenes_x_sede;

/*Tabla para estadísticas de ordenes por sede*/
CREATE TABLE ordenes_sede_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sede VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    mes SMALLINT UNSIGNED NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    cant INT UNSIGNED NOT NULL DEFAULT 0,
    facturadas INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    UNIQUE KEY uq_sede_fecha (sede, fecha),
    INDEX idx_mes_anio (mes, anio)
);

/*Tabla para histórico de ordenes*/

CREATE TABLE ordenes_historico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    descripcion_sede VARCHAR(150) NULL,
    numero_orden VARCHAR(50) NULL,
    ruc VARCHAR(20) NULL,
    cliente VARCHAR(200) NULL,
    diseno VARCHAR(200) NULL,
    descripcion_producto VARCHAR(300) NULL,
    importe DECIMAL(12,2) NULL,
    orden_compra VARCHAR(100) NULL,
    fecha_orden DATE NULL,
    hora_orden VARCHAR(20) NULL,
    tipo_orden VARCHAR(100) NULL,
    nombre_usuario VARCHAR(150) NULL,
    estado_orden VARCHAR(50) NULL,
    ubicacion_orden VARCHAR(150) NULL,
    descripcion_tallado VARCHAR(300) NULL,
    tratamiento VARCHAR(200) NULL,
    lead_time SMALLINT UNSIGNED NULL,

    mes SMALLINT UNSIGNED NULL,
    anio SMALLINT UNSIGNED NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY uq_numero_orden (numero_orden),
    INDEX idx_mes_anio (mes, anio),
    INDEX idx_fecha_orden (fecha_orden),
    INDEX idx_sede (descripcion_sede),
    INDEX idx_estado (estado_orden),
    INDEX idx_usuario (nombre_usuario)
);

/*tabla para la asignacion de bases */
CREATE TABLE asignacion_bases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    fecha_asignacion DATE NOT NULL,
    numero_orden VARCHAR(20) NOT NULL,
    codigo_pt VARCHAR(50) NULL,
    producto_pt VARCHAR(255) NULL,
    id_catalogo_base VARCHAR(20) NULL,
    descripcion_base VARCHAR(255) NULL,
    cantidad TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ojo CHAR(1) NULL, -- D o I
    estado_asignacion CHAR(1) NOT NULL, -- R o N
    descripcion_art VARCHAR(255) NULL,
    precio DECIMAL(10,4) NULL,
    mes TINYINT UNSIGNED NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY uq_asignacion_bases (numero_orden, ojo, fecha_asignacion, id_catalogo_base),

    INDEX idx_numero_orden (numero_orden),
    INDEX idx_id_catalogo_base (id_catalogo_base),
    INDEX idx_estado_asignacion (estado_asignacion),
    INDEX idx_anio (anio),
    INDEX idx_fecha_estado (fecha_asignacion, estado_asignacion),
    INDEX idx_anio_mes_estado (anio, mes, estado_asignacion)
);

/** tabla de reportes de cobranzas|*/
CREATE TABLE `reportes_cobranza` (                                                                                                                                                                                       
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,                                                                                                                                                           `user_id`               BIGINT UNSIGNED NOT NULL,
    `sede`                  VARCHAR(100) NOT NULL,                                                                                                                                                                         
    `semana_numero`         SMALLINT UNSIGNED NOT NULL,     
    `anio`                  SMALLINT UNSIGNED NOT NULL,
    `semana_inicio`         DATE NULL,
    `semana_fin`            DATE NULL,
    `fecha_limite`          DATETIME NULL,
    `fecha_envio_original`  DATETIME NULL,
    `fecha_ultimo_envio`    DATETIME NULL,
    `archivos`              JSON NULL,
    `notas`                 TEXT NULL,
    `kpi_porcentaje`        DECIMAL(5,2) NULL,
    `editado_tarde`         TINYINT(1) NOT NULL DEFAULT 0,
    `estado`                ENUM('pendiente','en_tiempo','con_atraso','no_enviado') NOT NULL DEFAULT 'pendiente',
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_cobranza_sede_semana` (`sede`, `semana_numero`, `anio`),
    INDEX `idx_anio_semana` (`anio`, `semana_numero`),
    INDEX `idx_sede` (`sede`),
    CONSTRAINT `fk_cobranza_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/*tabla de reportes de caja chica */
 CREATE TABLE `reportes_caja_chica` (                                                                                                                                                                                     
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,                                                                                                                                                           `user_id`               BIGINT UNSIGNED NOT NULL,                                                                                                                                                                      
    `sede`                  VARCHAR(100) NOT NULL,                                                                                                                                                                             `semana_numero`         SMALLINT UNSIGNED NOT NULL,                                                                                                                                                                        `anio`                  SMALLINT UNSIGNED NOT NULL,                                                                                                                                                                    
    `semana_inicio`         DATE NULL,
    `semana_fin`            DATE NULL,
    `fecha_limite`          DATETIME NULL,
    `fecha_envio_original`  DATETIME NULL,
    `fecha_ultimo_envio`    DATETIME NULL,
    `archivos`              JSON NULL,
    `notas`                 TEXT NULL,
    `kpi_porcentaje`        DECIMAL(5,2) NULL,
    `editado_tarde`         TINYINT(1) NOT NULL DEFAULT 0,
    `estado`                ENUM('pendiente','en_tiempo','con_atraso','no_enviado') NOT NULL DEFAULT 'pendiente',
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_cajachica_sede_semana` (`sede`, `semana_numero`, `anio`),
    INDEX `idx_anio_semana` (`anio`, `semana_numero`),
    INDEX `idx_sede` (`sede`),
    CONSTRAINT `fk_cajachica_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*tabla comentarios de reportes*/
CREATE TABLE reportes_comentarios (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,                                                                                                                                                      user_id BIGINT UNSIGNED NOT NULL,
      sede VARCHAR(255) NOT NULL,                                                                                                                                                                            
      semana_numero SMALLINT UNSIGNED NOT NULL,
      anio SMALLINT UNSIGNED NOT NULL,
      semana_inicio DATE NULL,
      semana_fin DATE NULL,
      fecha_limite TIMESTAMP NULL,
      fecha_envio_original TIMESTAMP NULL,
      fecha_ultimo_envio TIMESTAMP NULL,
      archivos JSON NULL,
      notas TEXT NULL,
      kpi_porcentaje DECIMAL(5,2) NULL,
      editado_tarde TINYINT(1) NOT NULL DEFAULT 0,
      estado VARCHAR(255) NOT NULL DEFAULT 'pendiente',
      created_at TIMESTAMP NULL,
      updated_at TIMESTAMP NULL,
      UNIQUE KEY reportes_comentarios_sede_semana_anio (sede, semana_numero, anio),
      INDEX idx_semana_anio (semana_numero, anio),
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  );

/*PERMISOS PARSA VER EL MODULO DE PROSUCTIVIDAD */
ALTER TABLE users
ADD COLUMN puede_ver_productividad_sedes TINYINT(1) NOT NULL DEFAULT 0;


/* ── RRHH FORMULARIO: campos para llenado automático del PDF RH-PR-03-FO-01 ── */

-- Nuevos campos en requerimientos_personal (sección 1 + 2 del formulario + RRHH + firmas)
ALTER TABLE requerimientos_personal
  ADD COLUMN supervisa_a           VARCHAR(255) NULL AFTER jefe_directo,
  ADD COLUMN num_vacantes          TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER supervisa_a,
  ADD COLUMN info_confidencial     TINYINT(1) NOT NULL DEFAULT 0 AFTER num_vacantes,
  ADD COLUMN tipo_vacante          ENUM('vacante','reemplazo','posicion_nueva') NULL AFTER tipo,
  ADD COLUMN permanencia           ENUM('temporal','permanente') NULL AFTER tipo_vacante,
  ADD COLUMN disponibilidad_viaje  TINYINT(1) NOT NULL DEFAULT 0 AFTER permanencia,
  ADD COLUMN jornada               ENUM('tiempo_parcial','tiempo_completo') NULL AFTER disponibilidad_viaje,
  ADD COLUMN motivo                TEXT NULL AFTER comentarios,
  ADD COLUMN candidatos            JSON NULL AFTER motivo,
  ADD COLUMN herramientas          JSON NULL AFTER candidatos,
  -- Sección 3 (RRHH llena)
  ADD COLUMN fecha_estimada_contratacion DATE NULL AFTER herramientas,
  ADD COLUMN tipo_contrato         VARCHAR(255) NULL AFTER fecha_estimada_contratacion,
  ADD COLUMN duracion_contrato     VARCHAR(255) NULL AFTER tipo_contrato,
  ADD COLUMN remuneracion_prevista DECIMAL(10,2) NULL AFTER duracion_contrato,
  ADD COLUMN horario_trabajo       VARCHAR(255) NULL AFTER remuneracion_prevista,
  ADD COLUMN beneficios            TEXT NULL AFTER horario_trabajo,
  -- Firmas digitales (base64 capturadas al momento de firmar)
  ADD COLUMN firma_solicitante_data   MEDIUMTEXT NULL AFTER beneficios,
  ADD COLUMN firma_solicitante_at     DATETIME NULL AFTER firma_solicitante_data,
  ADD COLUMN firma_solicitante_nombre VARCHAR(255) NULL AFTER firma_solicitante_at,
  ADD COLUMN firma_rrhh_data          MEDIUMTEXT NULL AFTER firma_solicitante_nombre,
  ADD COLUMN firma_rrhh_at            DATETIME NULL AFTER firma_rrhh_data,
  ADD COLUMN firma_rrhh_nombre        VARCHAR(255) NULL AFTER firma_rrhh_at,
  ADD COLUMN firma_gerente_data       MEDIUMTEXT NULL AFTER firma_rrhh_nombre,
  ADD COLUMN firma_gerente_at         DATETIME NULL AFTER firma_gerente_data,
  ADD COLUMN firma_gerente_nombre     VARCHAR(255) NULL AFTER firma_gerente_at;

-- Campos en users: cargo para PDF, firma personal, flag gerente general
ALTER TABLE users
  ADD COLUMN cargo              VARCHAR(255) NULL AFTER name,
  ADD COLUMN firma_imagen       MEDIUMTEXT NULL AFTER cargo,
  ADD COLUMN es_gerente_general TINYINT(1) NOT NULL DEFAULT 0 AFTER firma_imagen;

-- Para marcar al Gerente General (ejecutar una sola vez con el ID correcto):
-- UPDATE users SET es_gerente_general = 1 WHERE id = ?;

/* ── FIN RRHH FORMULARIO ── */


/*modificaciones a las tablas de rrhh*/
ALTER TABLE requerimientos_personal                                                                       -- Sección 1 (solicitante)
    ADD COLUMN supervisa_a           VARCHAR(255) NULL AFTER jefe_directo,                                  ADD COLUMN num_vacantes          TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER supervisa_a,             
    ADD COLUMN info_confidencial     TINYINT(1) NOT NULL DEFAULT 0 AFTER num_vacantes,
    -- Sección 2 (solicitante)
    ADD COLUMN tipo_vacante          ENUM('vacante','reemplazo','posicion_nueva') NULL AFTER tipo,      
    ADD COLUMN permanencia           ENUM('temporal','permanente') NULL AFTER tipo_vacante,
    ADD COLUMN disponibilidad_viaje  TINYINT(1) NOT NULL DEFAULT 0 AFTER permanencia,
    ADD COLUMN jornada               ENUM('tiempo_parcial','tiempo_completo') NULL AFTER
  disponibilidad_viaje,
    ADD COLUMN motivo                TEXT NULL AFTER comentarios,
    ADD COLUMN candidatos            JSON NULL AFTER motivo,
    ADD COLUMN herramientas          JSON NULL AFTER candidatos,
    -- Sección 3 (RRHH llena)
    ADD COLUMN fecha_estimada_contratacion DATE NULL AFTER herramientas,
    ADD COLUMN tipo_contrato         VARCHAR(255) NULL AFTER fecha_estimada_contratacion,
    ADD COLUMN duracion_contrato     VARCHAR(255) NULL AFTER tipo_contrato,
    ADD COLUMN remuneracion_prevista DECIMAL(10,2) NULL AFTER duracion_contrato,
    ADD COLUMN horario_trabajo       VARCHAR(255) NULL AFTER remuneracion_prevista,
    ADD COLUMN beneficios            TEXT NULL AFTER horario_trabajo,
    -- Firmas
    ADD COLUMN firma_solicitante_data   MEDIUMTEXT NULL AFTER beneficios,
    ADD COLUMN firma_solicitante_at     DATETIME NULL AFTER firma_solicitante_data,
    ADD COLUMN firma_solicitante_nombre VARCHAR(255) NULL AFTER firma_solicitante_at,
    ADD COLUMN firma_rrhh_data          MEDIUMTEXT NULL AFTER firma_solicitante_nombre,
    ADD COLUMN firma_rrhh_at            DATETIME NULL AFTER firma_rrhh_data,
    ADD COLUMN firma_rrhh_nombre        VARCHAR(255) NULL AFTER firma_rrhh_at,
    ADD COLUMN firma_gerente_data       MEDIUMTEXT NULL AFTER firma_rrhh_nombre,
    ADD COLUMN firma_gerente_at         DATETIME NULL AFTER firma_gerente_data,
    ADD COLUMN firma_gerente_nombre     VARCHAR(255) NULL AFTER firma_gerente_at;
/*end modificaciones a las tablas de rrhh*/

/*campos adicionales para ver el cargo dentro de la empresa */
ALTER TABLE users
    ADD COLUMN cargo              VARCHAR(255) NULL AFTER name,
    ADD COLUMN firma_imagen       MEDIUMTEXT NULL AFTER cargo,
    ADD COLUMN es_gerente_general TINYINT(1) NOT NULL DEFAULT 0 AFTER firma_imagen;

/* Eliminar unique key de asignacion_bases
   El sheet tiene filas legítimas con mismo (orden, ojo, fecha, catalogo) pero
   distinto estado (N=nueva, R=reemplazada). La estrategia TRUNCATE+INSERT no
   necesita unique key. */
ALTER TABLE asignacion_bases DROP INDEX uq_asignacion_bases;

