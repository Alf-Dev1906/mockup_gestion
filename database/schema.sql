-- =============================================================================
--  SISTEMA DE GESTIÓN ESTUDIANTIL (SGE) — FASE 1
--  Schema Relacional Optimizado para Alta Concurrencia
--  Motor: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
--  Compatible con: MySQL 8.0+ / MariaDB 10.6+
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------------------------------
-- BASE DE DATOS
-- -----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `sge_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `sge_db`;

-- =============================================================================
-- TABLA: carreras
-- Programas académicos ofrecidos por la institución.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `carreras` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo`        VARCHAR(10)     NOT NULL,
    `nombre`        VARCHAR(150)    NOT NULL,
    `descripcion`   TEXT            NULL,
    `duracion_semestres` TINYINT UNSIGNED NOT NULL DEFAULT 8 COMMENT 'Duración en semestres',
    `creditos_totales`   SMALLINT UNSIGNED NOT NULL DEFAULT 180,
    `activa`        TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_carreras_codigo` (`codigo`),
    KEY `idx_carreras_activa` (`activa`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Programas académicos de la institución';

-- =============================================================================
-- TABLA: periodos_academicos
-- Semestres o trimestres académicos.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `periodos_academicos` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo`        VARCHAR(20)     NOT NULL COMMENT 'Ej: 2024-I, 2024-II',
    `nombre`        VARCHAR(100)    NOT NULL,
    `fecha_inicio`  DATE            NOT NULL,
    `fecha_fin`     DATE            NOT NULL,
    `fecha_inicio_inscripciones` DATE NOT NULL,
    `fecha_fin_inscripciones`    DATE NOT NULL,
    `activo`        TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_periodos_codigo` (`codigo`),
    KEY `idx_periodos_activo` (`activo`),
    KEY `idx_periodos_fechas` (`fecha_inicio`, `fecha_fin`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Períodos académicos (semestres/trimestres)';

-- =============================================================================
-- TABLA: profesores
-- Cuerpo docente de la institución.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `profesores` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cedula`        VARCHAR(20)     NOT NULL,
    `nombres`       VARCHAR(100)    NOT NULL,
    `apellidos`     VARCHAR(100)    NOT NULL,
    `email`         VARCHAR(150)    NOT NULL,
    `telefono`      VARCHAR(20)     NULL,
    `titulo`        VARCHAR(200)    NULL COMMENT 'Título académico más alto',
    `departamento`  VARCHAR(100)    NULL,
    `activo`        TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_profesores_cedula` (`cedula`),
    UNIQUE KEY `uq_profesores_email` (`email`),
    KEY `idx_profesores_activo` (`activo`),
    KEY `idx_profesores_apellidos` (`apellidos`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cuerpo docente de la institución';

-- =============================================================================
-- TABLA: aulas
-- Espacios físicos disponibles para clases.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `aulas` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo`        VARCHAR(20)     NOT NULL,
    `nombre`        VARCHAR(100)    NOT NULL,
    `edificio`      VARCHAR(80)     NULL,
    `piso`          TINYINT         NULL,
    `capacidad`     SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    `tipo`          ENUM('salon','laboratorio','anfiteatro','sala_conferencias') NOT NULL DEFAULT 'salon',
    `disponible`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_aulas_codigo` (`codigo`),
    KEY `idx_aulas_disponible` (`disponible`),
    KEY `idx_aulas_tipo` (`tipo`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Espacios físicos para clases';

-- =============================================================================
-- TABLA: asignaturas
-- Materias o cursos del pensum académico.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `asignaturas` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `carrera_id`    BIGINT UNSIGNED NOT NULL,
    `codigo`        VARCHAR(15)     NOT NULL,
    `nombre`        VARCHAR(200)    NOT NULL,
    `creditos`      TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `horas_teoria`  TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `horas_practica` TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `semestre`      TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Semestre del pensum en que se cursa',
    `activa`        TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_asignaturas_codigo` (`codigo`),
    KEY `idx_asignaturas_carrera` (`carrera_id`),
    KEY `idx_asignaturas_semestre` (`semestre`),
    KEY `idx_asignaturas_activa` (`activa`),

    CONSTRAINT `fk_asignaturas_carrera`
        FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Materias del pensum académico';

-- =============================================================================
-- TABLA: prerequisitos
-- Grafo dirigido de prerequisitos entre asignaturas.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `prerequisitos` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asignatura_id`     BIGINT UNSIGNED NOT NULL COMMENT 'Asignatura que TIENE prerequisito',
    `prerequisito_id`   BIGINT UNSIGNED NOT NULL COMMENT 'Asignatura que ES prerequisito',
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_prerequisitos` (`asignatura_id`, `prerequisito_id`),
    KEY `idx_prerequisitos_asignatura` (`asignatura_id`),
    KEY `idx_prerequisitos_prereq` (`prerequisito_id`),

    CONSTRAINT `fk_prereq_asignatura`
        FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_prereq_prerequisito`
        FOREIGN KEY (`prerequisito_id`) REFERENCES `asignaturas` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Grafo de prerequisitos entre asignaturas';

-- =============================================================================
-- TABLA: estudiantes
-- Información personal y académica de los estudiantes.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `estudiantes` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `carrera_id`        BIGINT UNSIGNED NOT NULL,
    `cedula`            VARCHAR(20)     NOT NULL,
    `numero_expediente` VARCHAR(20)     NOT NULL,
    `nombres`           VARCHAR(100)    NOT NULL,
    `apellidos`         VARCHAR(100)    NOT NULL,
    `email`             VARCHAR(150)    NOT NULL,
    `email_institucional` VARCHAR(150)  NULL,
    `telefono`          VARCHAR(20)     NULL,
    `fecha_nacimiento`  DATE            NOT NULL,
    `genero`            ENUM('M','F','O') NOT NULL DEFAULT 'M',
    `direccion`         TEXT            NULL,
    `semestre_actual`   TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `creditos_aprobados` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `indice_academico`  DECIMAL(4,2)    NOT NULL DEFAULT 0.00 COMMENT 'Índice académico acumulado (0.00-20.00)',
    `estado`            ENUM('activo','inactivo','graduado','retirado','sancionado') NOT NULL DEFAULT 'activo',
    `fecha_ingreso`     DATE            NOT NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_estudiantes_cedula` (`cedula`),
    UNIQUE KEY `uq_estudiantes_expediente` (`numero_expediente`),
    UNIQUE KEY `uq_estudiantes_email_inst` (`email_institucional`),
    KEY `idx_estudiantes_carrera` (`carrera_id`),
    KEY `idx_estudiantes_estado` (`estado`),
    KEY `idx_estudiantes_semestre` (`semestre_actual`),
    KEY `idx_estudiantes_apellidos` (`apellidos`),
    KEY `idx_estudiantes_indice` (`indice_academico`),
    -- Índice compuesto para reportes por carrera y estado (alta consulta)
    KEY `idx_estudiantes_carrera_estado` (`carrera_id`, `estado`),

    CONSTRAINT `fk_estudiantes_carrera`
        FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Estudiantes registrados en la institución';

-- =============================================================================
-- TABLA: horarios
-- Secciones de clase: combinación de asignatura + profesor + aula + periodo.
-- Alta concurrencia: múltiples estudiantes leen y escriben inscripciones.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `horarios` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asignatura_id`     BIGINT UNSIGNED NOT NULL,
    `profesor_id`       BIGINT UNSIGNED NOT NULL,
    `aula_id`           BIGINT UNSIGNED NOT NULL,
    `periodo_id`        BIGINT UNSIGNED NOT NULL,
    `codigo_seccion`    VARCHAR(20)     NOT NULL,
    `cupo_maximo`       SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    `cupo_disponible`   SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Decrementado atómicamente en inscripción',
    `dia_semana`        SET('L','M','X','J','V','S') NOT NULL COMMENT 'L=Lunes,M=Martes,X=Miercoles,J=Jueves,V=Viernes,S=Sabado',
    `hora_inicio`       TIME            NOT NULL,
    `hora_fin`          TIME            NOT NULL,
    `activo`            TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_horarios_seccion_periodo` (`codigo_seccion`, `periodo_id`),
    KEY `idx_horarios_asignatura` (`asignatura_id`),
    KEY `idx_horarios_profesor` (`profesor_id`),
    KEY `idx_horarios_aula` (`aula_id`),
    KEY `idx_horarios_periodo` (`periodo_id`),
    -- Índice compuesto crítico para búsquedas de disponibilidad
    KEY `idx_horarios_periodo_activo` (`periodo_id`, `activo`, `cupo_disponible`),
    -- Índice compuesto para detección de conflictos de horario
    KEY `idx_horarios_aula_periodo_dia` (`aula_id`, `periodo_id`, `dia_semana`),
    KEY `idx_horarios_profesor_periodo` (`profesor_id`, `periodo_id`),

    CONSTRAINT `fk_horarios_asignatura`
        FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_horarios_profesor`
        FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_horarios_aula`
        FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_horarios_periodo`
        FOREIGN KEY (`periodo_id`) REFERENCES `periodos_academicos` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Secciones de clase por período académico';

-- =============================================================================
-- TABLA: inscripciones
-- Relación estudiante-horario-período.
-- NÚCLEO de alta concurrencia: constraint UNIQUE previene inscripciones duplicadas
-- bajo condiciones de carrera (race conditions).
-- =============================================================================
CREATE TABLE IF NOT EXISTS `inscripciones` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `estudiante_id`     BIGINT UNSIGNED NOT NULL,
    `horario_id`        BIGINT UNSIGNED NOT NULL,
    `periodo_id`        BIGINT UNSIGNED NOT NULL,
    `estado`            ENUM('inscrito','retirado','aprobado','reprobado') NOT NULL DEFAULT 'inscrito',
    `fecha_inscripcion` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_retiro`      TIMESTAMP       NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    -- UNIQUE CONSTRAINT clave: previene inscripción duplicada incluso en alta concurrencia
    UNIQUE KEY `uq_inscripcion_estudiante_horario_periodo` (`estudiante_id`, `horario_id`, `periodo_id`),
    KEY `idx_inscripciones_estudiante_periodo` (`estudiante_id`, `periodo_id`),
    KEY `idx_inscripciones_horario` (`horario_id`),
    KEY `idx_inscripciones_periodo` (`periodo_id`),
    KEY `idx_inscripciones_estado` (`estado`),
    -- Índice compuesto para reportes de carga académica por período
    KEY `idx_inscripciones_periodo_estado` (`periodo_id`, `estado`),

    CONSTRAINT `fk_inscripciones_estudiante`
        FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_inscripciones_horario`
        FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_inscripciones_periodo`
        FOREIGN KEY (`periodo_id`) REFERENCES `periodos_academicos` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Inscripciones de estudiantes a secciones de clase';

-- =============================================================================
-- TABLA: calificaciones
-- Notas por evaluación. Permite múltiples parciales con peso porcentual.
-- =============================================================================
CREATE TABLE IF NOT EXISTS `calificaciones` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `inscripcion_id`    BIGINT UNSIGNED NOT NULL,
    `tipo_evaluacion`   ENUM('parcial_1','parcial_2','parcial_3','final','practica','trabajo','otro') NOT NULL,
    `descripcion`       VARCHAR(200)    NULL,
    `nota`              DECIMAL(5,2)    NOT NULL COMMENT 'Nota obtenida (0.00-20.00)',
    `nota_maxima`       DECIMAL(5,2)    NOT NULL DEFAULT 20.00,
    `peso_porcentual`   DECIMAL(5,2)    NOT NULL DEFAULT 25.00 COMMENT 'Peso en el cálculo final (%)',
    `fecha_evaluacion`  DATE            NOT NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_calificaciones_inscripcion` (`inscripcion_id`),
    KEY `idx_calificaciones_tipo` (`tipo_evaluacion`),
    -- Índice compuesto para cálculo de promedio por inscripción
    KEY `idx_calificaciones_inscripcion_tipo` (`inscripcion_id`, `tipo_evaluacion`),

    CONSTRAINT `fk_calificaciones_inscripcion`
        FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Calificaciones por evaluación de cada inscripción';

-- =============================================================================
-- VIEWS útiles para reportes
-- =============================================================================

-- Vista: Carga académica actual por estudiante
CREATE OR REPLACE VIEW `v_carga_academica` AS
SELECT
    e.id             AS estudiante_id,
    CONCAT(e.apellidos, ', ', e.nombres) AS estudiante,
    e.numero_expediente,
    c.nombre         AS carrera,
    p.nombre         AS periodo,
    COUNT(i.id)      AS materias_inscritas,
    SUM(a.creditos)  AS creditos_inscritos
FROM `estudiantes` e
JOIN `carreras` c         ON c.id = e.carrera_id
JOIN `inscripciones` i    ON i.estudiante_id = e.id AND i.estado = 'inscrito'
JOIN `horarios` h         ON h.id = i.horario_id
JOIN `asignaturas` a      ON a.id = h.asignatura_id
JOIN `periodos_academicos` p ON p.id = i.periodo_id
WHERE p.activo = 1
GROUP BY e.id, e.apellidos, e.nombres, e.numero_expediente, c.nombre, p.nombre;

-- Vista: Disponibilidad de cupos por horario
CREATE OR REPLACE VIEW `v_disponibilidad_horarios` AS
SELECT
    h.id             AS horario_id,
    h.codigo_seccion,
    a.codigo         AS asignatura_codigo,
    a.nombre         AS asignatura,
    CONCAT(pr.apellidos, ', ', pr.nombres) AS profesor,
    au.codigo        AS aula,
    au.capacidad,
    h.cupo_maximo,
    h.cupo_disponible,
    (h.cupo_maximo - h.cupo_disponible) AS inscritos,
    p.nombre         AS periodo,
    h.dia_semana,
    h.hora_inicio,
    h.hora_fin
FROM `horarios` h
JOIN `asignaturas` a            ON a.id = h.asignatura_id
JOIN `profesores` pr            ON pr.id = h.profesor_id
JOIN `aulas` au                 ON au.id = h.aula_id
JOIN `periodos_academicos` p    ON p.id = h.periodo_id
WHERE h.activo = 1;

-- Vista: Historial académico completo por estudiante
CREATE OR REPLACE VIEW `v_historial_academico` AS
SELECT
    e.id             AS estudiante_id,
    CONCAT(e.apellidos, ', ', e.nombres) AS estudiante,
    e.numero_expediente,
    p.codigo         AS periodo_codigo,
    p.nombre         AS periodo,
    a.codigo         AS asignatura_codigo,
    a.nombre         AS asignatura,
    a.creditos,
    i.estado         AS estado_inscripcion,
    ROUND(
        SUM(cal.nota * cal.peso_porcentual / 100), 2
    ) AS nota_definitiva
FROM `estudiantes` e
JOIN `inscripciones` i          ON i.estudiante_id = e.id
JOIN `horarios` h               ON h.id = i.horario_id
JOIN `asignaturas` a            ON a.id = h.asignatura_id
JOIN `periodos_academicos` p    ON p.id = i.periodo_id
LEFT JOIN `calificaciones` cal  ON cal.inscripcion_id = i.id
GROUP BY e.id, e.apellidos, e.nombres, e.numero_expediente,
         p.codigo, p.nombre, a.codigo, a.nombre, a.creditos, i.estado;

-- =============================================================================
-- STORED PROCEDURES para alta concurrencia
-- =============================================================================
DELIMITER $$

-- Procedimiento: Inscripción atómica con control de cupos
-- Maneja race conditions mediante SELECT ... FOR UPDATE + transacción.
CREATE PROCEDURE IF NOT EXISTS `sp_inscribir_estudiante`(
    IN  p_estudiante_id BIGINT UNSIGNED,
    IN  p_horario_id    BIGINT UNSIGNED,
    IN  p_periodo_id    BIGINT UNSIGNED,
    OUT p_resultado     VARCHAR(50),
    OUT p_mensaje       VARCHAR(200)
)
BEGIN
    DECLARE v_cupo INT DEFAULT 0;
    DECLARE v_ya_inscrito INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado = 'ERROR';
        SET p_mensaje = 'Error interno durante la inscripción';
    END;

    START TRANSACTION;

    -- Bloquear fila del horario para prevenir race condition
    SELECT cupo_disponible INTO v_cupo
    FROM `horarios`
    WHERE id = p_horario_id AND activo = 1
    FOR UPDATE;

    -- Verificar si ya está inscrito en ese horario-período
    SELECT COUNT(*) INTO v_ya_inscrito
    FROM `inscripciones`
    WHERE estudiante_id = p_estudiante_id
      AND horario_id    = p_horario_id
      AND periodo_id    = p_periodo_id
      AND estado        = 'inscrito';

    IF v_ya_inscrito > 0 THEN
        ROLLBACK;
        SET p_resultado = 'DUPLICADO';
        SET p_mensaje   = 'El estudiante ya está inscrito en esta sección';
    ELSEIF v_cupo <= 0 OR v_cupo IS NULL THEN
        ROLLBACK;
        SET p_resultado = 'SIN_CUPO';
        SET p_mensaje   = 'No hay cupos disponibles en esta sección';
    ELSE
        -- Insertar inscripción
        INSERT INTO `inscripciones` (estudiante_id, horario_id, periodo_id, estado)
        VALUES (p_estudiante_id, p_horario_id, p_periodo_id, 'inscrito');

        -- Decrementar cupo atómicamente
        UPDATE `horarios`
        SET cupo_disponible = cupo_disponible - 1
        WHERE id = p_horario_id;

        COMMIT;
        SET p_resultado = 'OK';
        SET p_mensaje   = 'Inscripción realizada correctamente';
    END IF;
END$$

-- Procedimiento: Retiro de materia
CREATE PROCEDURE IF NOT EXISTS `sp_retirar_materia`(
    IN  p_inscripcion_id BIGINT UNSIGNED,
    OUT p_resultado      VARCHAR(50),
    OUT p_mensaje        VARCHAR(200)
)
BEGIN
    DECLARE v_horario_id BIGINT UNSIGNED;
    DECLARE v_estado     VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado = 'ERROR';
        SET p_mensaje = 'Error interno durante el retiro';
    END;

    START TRANSACTION;

    SELECT horario_id, estado INTO v_horario_id, v_estado
    FROM `inscripciones`
    WHERE id = p_inscripcion_id
    FOR UPDATE;

    IF v_estado != 'inscrito' THEN
        ROLLBACK;
        SET p_resultado = 'ESTADO_INVALIDO';
        SET p_mensaje   = CONCAT('No se puede retirar una inscripción en estado: ', v_estado);
    ELSE
        UPDATE `inscripciones`
        SET estado = 'retirado', fecha_retiro = NOW()
        WHERE id = p_inscripcion_id;

        UPDATE `horarios`
        SET cupo_disponible = cupo_disponible + 1
        WHERE id = v_horario_id;

        COMMIT;
        SET p_resultado = 'OK';
        SET p_mensaje   = 'Retiro realizado correctamente';
    END IF;
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- FIN DEL SCHEMA — sge_db
-- =============================================================================
