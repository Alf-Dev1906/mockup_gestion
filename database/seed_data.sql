-- =============================================================================
--  SISTEMA DE GESTIÓN ESTUDIANTIL (SGE) — FASE 1
--  Datos Masivos de Prueba
--  Genera: 10 carreras, 50 asignaturas, 200 profesores, 60 aulas,
--          10,000 estudiantes, 5 períodos, 500 horarios, 50,000+ inscripciones
--
--  INSTRUCCIONES:
--  1. Asegúrate de haber ejecutado schema.sql primero.
--  2. Ejecutar este script en DBeaver o desde CLI:
--       mysql -u root sge_db < seed_data.sql
--  3. El proceso puede tardar 2-5 minutos según hardware.
-- =============================================================================

USE `sge_db`;

SET FOREIGN_KEY_CHECKS = 0;
SET autocommit = 0;

-- =============================================================================
-- DATOS BASE: Carreras
-- =============================================================================
INSERT INTO `carreras` (`codigo`, `nombre`, `descripcion`, `duracion_semestres`, `creditos_totales`) VALUES
('ING-SIS',  'Ingeniería de Sistemas',           'Formación en desarrollo de software, redes y sistemas de información.',    8, 180),
('ING-CIV',  'Ingeniería Civil',                 'Diseño y construcción de infraestructura civil.',                          10, 210),
('ING-IND',  'Ingeniería Industrial',            'Optimización de procesos productivos y gestión de calidad.',               8, 175),
('ING-MEC',  'Ingeniería Mecánica',              'Diseño y manufactura de sistemas mecánicos y térmicos.',                   8, 180),
('MED-GEN',  'Medicina General',                 'Formación médica integral para el ejercicio clínico y comunitario.',       12, 280),
('ADM-EMP',  'Administración de Empresas',       'Gestión organizacional, finanzas y dirección estratégica.',                8, 165),
('DER-GEN',  'Derecho',                          'Formación jurídica en derecho público, privado e internacional.',          10, 200),
('CON-PUB',  'Contaduría Pública',               'Auditoría, tributación y contabilidad financiera.',                        8, 165),
('PSI-CLN',  'Psicología Clínica',               'Evaluación, diagnóstico y tratamiento de trastornos psicológicos.',        8, 170),
('ARQ-GEN',  'Arquitectura',                     'Diseño arquitectónico, urbanismo y patrimonio cultural.',                  10, 200);

COMMIT;

-- =============================================================================
-- DATOS BASE: Períodos Académicos
-- =============================================================================
INSERT INTO `periodos_academicos` (`codigo`, `nombre`, `fecha_inicio`, `fecha_fin`, `fecha_inicio_inscripciones`, `fecha_fin_inscripciones`, `activo`) VALUES
('2022-I',  'Primer Semestre 2022',   '2022-01-15', '2022-06-30', '2022-01-01', '2022-01-14', 0),
('2022-II', 'Segundo Semestre 2022',  '2022-07-15', '2022-12-15', '2022-07-01', '2022-07-14', 0),
('2023-I',  'Primer Semestre 2023',   '2023-01-15', '2023-06-30', '2023-01-01', '2023-01-14', 0),
('2023-II', 'Segundo Semestre 2023',  '2023-07-15', '2023-12-15', '2023-07-01', '2023-07-14', 0),
('2024-I',  'Primer Semestre 2024',   '2024-01-15', '2024-06-30', '2024-01-01', '2024-01-14', 1);

COMMIT;

-- =============================================================================
-- DATOS BASE: Profesores (200 registros via procedimiento)
-- =============================================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS `temp_insert_profesores`$$
CREATE PROCEDURE `temp_insert_profesores`()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_nombres VARCHAR(100);
    DECLARE v_apellidos VARCHAR(100);
    DECLARE v_depto VARCHAR(100);

    WHILE i <= 200 DO
        SET v_nombres = ELT(1 + FLOOR(RAND() * 20),
            'Carlos','María','José','Ana','Luis','Laura','Pedro','Carmen',
            'Jorge','Isabel','Miguel','Patricia','Roberto','Daniela',
            'Fernando','Valentina','Ricardo','Gabriela','Eduardo','Alejandra');

        SET v_apellidos = ELT(1 + FLOOR(RAND() * 30),
            'González','Rodríguez','López','Martínez','García','Hernández',
            'Pérez','Sánchez','Ramírez','Torres','Flores','Rivera','Gómez',
            'Díaz','Reyes','Morales','Cruz','Ortega','Vargas','Castillo',
            'Ramos','Jiménez','Álvarez','Moreno','Muñoz','Rojas','Delgado',
            'Castro','Rubio','Medina');

        SET v_depto = ELT(1 + FLOOR(RAND() * 8),
            'Matemáticas','Física','Química','Ciencias Sociales',
            'Humanidades','Informática','Ingeniería','Ciencias Básicas');

        INSERT IGNORE INTO `profesores` (`cedula`, `nombres`, `apellidos`, `email`, `titulo`, `departamento`)
        VALUES (
            LPAD(i * 13 + 7000000, 8, '0'),
            v_nombres,
            CONCAT(v_apellidos, ' ', ELT(1 + FLOOR(RAND() * 10), 'De La Cruz','Del Valle','Morales','Castro','Rivas','Fuentes','Mendez','Arias','Vega','Blanco')),
            CONCAT('prof', i, '@universidad.edu.ve'),
            ELT(1 + FLOOR(RAND() * 4), 'Licenciado','Especialista','Magister','Doctor'),
            v_depto
        );
        SET i = i + 1;
    END WHILE;
END$$

CALL `temp_insert_profesores`()$$
DROP PROCEDURE `temp_insert_profesores`$$

DELIMITER ;
COMMIT;

-- =============================================================================
-- DATOS BASE: Aulas (60 registros)
-- =============================================================================
DELIMITER $$
DROP PROCEDURE IF EXISTS `temp_insert_aulas`$$
CREATE PROCEDURE `temp_insert_aulas`()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_tipo ENUM('salon','laboratorio','anfiteatro','sala_conferencias');
    DECLARE v_edificio VARCHAR(80);
    DECLARE v_cap SMALLINT;

    WHILE i <= 60 DO
        SET v_tipo = ELT(1 + FLOOR(RAND() * 4), 'salon','laboratorio','anfiteatro','sala_conferencias');
        SET v_edificio = ELT(1 + FLOOR(RAND() * 5), 'Edificio A','Edificio B','Edificio C','Edificio D','Bloque STEM');
        SET v_cap = CASE v_tipo
            WHEN 'salon'              THEN 25 + FLOOR(RAND() * 15)
            WHEN 'laboratorio'        THEN 20 + FLOOR(RAND() * 10)
            WHEN 'anfiteatro'         THEN 80 + FLOOR(RAND() * 120)
            WHEN 'sala_conferencias'  THEN 15 + FLOOR(RAND() * 10)
        END;

        INSERT IGNORE INTO `aulas` (`codigo`, `nombre`, `edificio`, `piso`, `capacidad`, `tipo`)
        VALUES (
            CONCAT('AUL-', LPAD(i, 3, '0')),
            CONCAT('Aula ', LPAD(i, 3, '0')),
            v_edificio,
            1 + FLOOR(RAND() * 4),
            v_cap,
            v_tipo
        );
        SET i = i + 1;
    END WHILE;
END$$

CALL `temp_insert_aulas`()$$
DROP PROCEDURE `temp_insert_aulas`$$

DELIMITER ;
COMMIT;

-- =============================================================================
-- DATOS BASE: Asignaturas (5 por semestre x 10 carreras = ~50)
-- =============================================================================
DELIMITER $$
DROP PROCEDURE IF EXISTS `temp_insert_asignaturas`$$
CREATE PROCEDURE `temp_insert_asignaturas`()
BEGIN
    DECLARE car_id BIGINT;
    DECLARE sem    INT;
    DECLARE idx    INT;
    DECLARE suffix VARCHAR(20);

    -- Nombres base de asignaturas por área
    DECLARE n1 VARCHAR(80);
    DECLARE n2 VARCHAR(80);
    DECLARE n3 VARCHAR(80);
    DECLARE n4 VARCHAR(80);
    DECLARE n5 VARCHAR(80);

    SELECT id INTO car_id FROM carreras WHERE codigo = 'ING-SIS';
    SET sem = 1;
    WHILE sem <= 8 DO
        SET idx = (car_id * 100) + sem;
        INSERT IGNORE INTO `asignaturas` (`carrera_id`,`codigo`,`nombre`,`creditos`,`horas_teoria`,`horas_practica`,`semestre`) VALUES
        (car_id, CONCAT('SIS-',idx,'A'), CONCAT('Fundamentos de Programación ',sem), 4, 2, 4, sem),
        (car_id, CONCAT('SIS-',idx,'B'), CONCAT('Estructuras de Datos ',sem),         3, 2, 2, sem),
        (car_id, CONCAT('SIS-',idx,'C'), CONCAT('Bases de Datos ',sem),               3, 2, 2, sem),
        (car_id, CONCAT('SIS-',idx,'D'), CONCAT('Redes de Computadoras ',sem),        3, 3, 0, sem),
        (car_id, CONCAT('SIS-',idx,'E'), CONCAT('Cálculo Aplicado ',sem),             3, 3, 0, sem);
        SET sem = sem + 1;
    END WHILE;

    SELECT id INTO car_id FROM carreras WHERE codigo = 'ADM-EMP';
    SET sem = 1;
    WHILE sem <= 8 DO
        SET idx = (car_id * 100) + sem;
        INSERT IGNORE INTO `asignaturas` (`carrera_id`,`codigo`,`nombre`,`creditos`,`horas_teoria`,`horas_practica`,`semestre`) VALUES
        (car_id, CONCAT('ADM-',idx,'A'), CONCAT('Principios de Administración ',sem), 3, 3, 0, sem),
        (car_id, CONCAT('ADM-',idx,'B'), CONCAT('Contabilidad General ',sem),          3, 2, 2, sem),
        (car_id, CONCAT('ADM-',idx,'C'), CONCAT('Economía ',sem),                      3, 3, 0, sem),
        (car_id, CONCAT('ADM-',idx,'D'), CONCAT('Estadística Empresarial ',sem),        3, 2, 2, sem),
        (car_id, CONCAT('ADM-',idx,'E'), CONCAT('Derecho Mercantil ',sem),              3, 3, 0, sem);
        SET sem = sem + 1;
    END WHILE;

    -- Asignaturas genéricas para las 8 carreras restantes
    SET car_id = 1;
    WHILE car_id <= 10 DO
        IF car_id NOT IN (
            (SELECT id FROM carreras WHERE codigo = 'ING-SIS'),
            (SELECT id FROM carreras WHERE codigo = 'ADM-EMP')
        ) THEN
            SET sem = 1;
            WHILE sem <= 5 DO
                SET idx = (car_id * 100) + sem;
                INSERT IGNORE INTO `asignaturas` (`carrera_id`,`codigo`,`nombre`,`creditos`,`horas_teoria`,`horas_practica`,`semestre`) VALUES
                (car_id, CONCAT('GEN-',idx,'A'), CONCAT('Asignatura Básica ',sem,' - Carr.',car_id), 3, 2, 2, sem),
                (car_id, CONCAT('GEN-',idx,'B'), CONCAT('Asignatura Técnica ',sem,' - Carr.',car_id), 3, 3, 0, sem),
                (car_id, CONCAT('GEN-',idx,'C'), CONCAT('Seminario ',sem,' - Carr.',car_id),          2, 2, 0, sem);
                SET sem = sem + 1;
            END WHILE;
        END IF;
        SET car_id = car_id + 1;
    END WHILE;
END$$

CALL `temp_insert_asignaturas`()$$
DROP PROCEDURE `temp_insert_asignaturas`$$

DELIMITER ;
COMMIT;

-- =============================================================================
-- DATOS BASE: Horarios (una sección por asignatura por período activo)
-- =============================================================================
DELIMITER $$
DROP PROCEDURE IF EXISTS `temp_insert_horarios`$$
CREATE PROCEDURE `temp_insert_horarios`()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_asig_id BIGINT;
    DECLARE v_sec  INT DEFAULT 1;
    DECLARE v_per  BIGINT;
    DECLARE v_prof BIGINT;
    DECLARE v_aula BIGINT;
    DECLARE v_dias SET('L','M','X','J','V','S');
    DECLARE v_hora_ini TIME;

    DECLARE cur_asig CURSOR FOR SELECT id FROM asignaturas WHERE activa = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    SELECT id INTO v_per FROM periodos_academicos WHERE activo = 1 LIMIT 1;

    OPEN cur_asig;
    read_loop: LOOP
        FETCH cur_asig INTO v_asig_id;
        IF done THEN LEAVE read_loop; END IF;

        SET v_prof = 1 + FLOOR(RAND() * 200);
        SET v_aula = 1 + FLOOR(RAND() * 60);
        SET v_dias = ELT(1 + FLOOR(RAND() * 4), 'L,X','M,J','L,M,X','M,X,J');
        SET v_hora_ini = MAKETIME(6 + FLOOR(RAND() * 13), ELT(1+FLOOR(RAND()*2),'0','30'), 0);

        INSERT IGNORE INTO `horarios`
            (`asignatura_id`,`profesor_id`,`aula_id`,`periodo_id`,`codigo_seccion`,
             `cupo_maximo`,`cupo_disponible`,`dia_semana`,`hora_inicio`,`hora_fin`)
        VALUES
            (v_asig_id, v_prof, v_aula, v_per,
             CONCAT('SEC-', LPAD(v_sec, 5, '0')),
             30, 30, v_dias,
             v_hora_ini, ADDTIME(v_hora_ini, '01:30:00'));

        -- Segunda sección de la misma asignatura (50% de probabilidad)
        IF RAND() > 0.5 THEN
            SET v_prof = 1 + FLOOR(RAND() * 200);
            SET v_aula = 1 + FLOOR(RAND() * 60);
            SET v_dias = ELT(1 + FLOOR(RAND() * 4), 'L,X','M,J','L,M,X','M,X,J');
            SET v_hora_ini = MAKETIME(6 + FLOOR(RAND() * 13), ELT(1+FLOOR(RAND()*2),'0','30'), 0);

            INSERT IGNORE INTO `horarios`
                (`asignatura_id`,`profesor_id`,`aula_id`,`periodo_id`,`codigo_seccion`,
                 `cupo_maximo`,`cupo_disponible`,`dia_semana`,`hora_inicio`,`hora_fin`)
            VALUES
                (v_asig_id, v_prof, v_aula, v_per,
                 CONCAT('SEC-', LPAD(v_sec + 1000, 5, '0')),
                 30, 30, v_dias,
                 v_hora_ini, ADDTIME(v_hora_ini, '01:30:00'));
        END IF;

        SET v_sec = v_sec + 1;
    END LOOP;
    CLOSE cur_asig;
END$$

CALL `temp_insert_horarios`()$$
DROP PROCEDURE `temp_insert_horarios`$$

DELIMITER ;
COMMIT;

-- =============================================================================
-- DATOS MASIVOS: Estudiantes (10,000 registros)
-- =============================================================================
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_generar_estudiantes`$$
CREATE PROCEDURE `sp_generar_estudiantes`()
BEGIN
    DECLARE i        INT DEFAULT 1;
    DECLARE v_carrera BIGINT;
    DECLARE v_nombres VARCHAR(100);
    DECLARE v_apellidos VARCHAR(100);
    DECLARE v_sem    TINYINT;
    DECLARE v_estado ENUM('activo','inactivo','graduado','retirado','sancionado');
    DECLARE v_indice DECIMAL(4,2);
    DECLARE v_fecha_nac DATE;
    DECLARE v_fecha_ing DATE;
    DECLARE v_genero CHAR(1);
    DECLARE v_carr_count BIGINT;

    SELECT COUNT(*) INTO v_carr_count FROM carreras;

    WHILE i <= 10000 DO
        SET v_carrera = 1 + FLOOR(RAND() * v_carr_count);
        SET v_sem     = 1 + FLOOR(RAND() * 8);
        SET v_indice  = ROUND(5 + RAND() * 15, 2);
        SET v_genero  = ELT(1 + FLOOR(RAND() * 2), 'M', 'F');
        SET v_fecha_nac = DATE_SUB(CURDATE(), INTERVAL (18 + FLOOR(RAND() * 10)) YEAR);
        SET v_fecha_ing = DATE_SUB(CURDATE(), INTERVAL (FLOOR(RAND() * 4)) YEAR);

        SET v_estado = CASE
            WHEN RAND() < 0.80 THEN 'activo'
            WHEN RAND() < 0.10 THEN 'inactivo'
            WHEN RAND() < 0.05 THEN 'graduado'
            WHEN RAND() < 0.03 THEN 'retirado'
            ELSE                    'sancionado'
        END;

        SET v_nombres = ELT(1 + FLOOR(RAND() * 25),
            'Carlos','María','José','Ana','Luis','Laura','Pedro','Carmen',
            'Jorge','Isabel','Miguel','Patricia','Roberto','Daniela',
            'Fernando','Valentina','Ricardo','Gabriela','Eduardo','Alejandra',
            'Andrés','Sofía','Diego','Natalia','Sergio');

        IF v_genero = 'F' THEN
            SET v_nombres = ELT(1 + FLOOR(RAND() * 12),
                'María','Laura','Carmen','Isabel','Patricia','Daniela',
                'Valentina','Gabriela','Alejandra','Sofía','Natalia','Ana');
        END IF;

        SET v_apellidos = CONCAT(
            ELT(1 + FLOOR(RAND() * 30),
                'González','Rodríguez','López','Martínez','García','Hernández',
                'Pérez','Sánchez','Ramírez','Torres','Flores','Rivera','Gómez',
                'Díaz','Reyes','Morales','Cruz','Ortega','Vargas','Castillo',
                'Ramos','Jiménez','Álvarez','Moreno','Muñoz','Rojas','Delgado',
                'Castro','Rubio','Medina'),
            ' ',
            ELT(1 + FLOOR(RAND() * 20),
                'De León','Fuentes','Arias','Vega','Blanco','Mendoza','Acosta',
                'Salazar','Herrera','Ibáñez','Santana','Guzmán','Paredes',
                'Velásquez','Aguilar','Molina','Montoya','Bermúdez','Peña','Padilla')
        );

        INSERT IGNORE INTO `estudiantes`
            (`carrera_id`,`cedula`,`numero_expediente`,`nombres`,`apellidos`,
             `email`,`email_institucional`,`telefono`,`fecha_nacimiento`,`genero`,
             `semestre_actual`,`creditos_aprobados`,`indice_academico`,
             `estado`,`fecha_ingreso`)
        VALUES (
            v_carrera,
            LPAD(10000000 + i, 8, '0'),
            CONCAT('EXP-', LPAD(2020000 + i, 8, '0')),
            v_nombres,
            v_apellidos,
            CONCAT('estudiante', i, '@gmail.com'),
            CONCAT('exp', LPAD(2020000 + i, 8, '0'), '@universidad.edu.ve'),
            CONCAT('0412-', LPAD(FLOOR(1000000 + RAND() * 8999999), 7, '0')),
            v_fecha_nac,
            v_genero,
            v_sem,
            (v_sem - 1) * 18 + FLOOR(RAND() * 18),
            v_indice,
            v_estado,
            v_fecha_ing
        );

        -- Commit cada 500 registros para no saturar el buffer
        IF i MOD 500 = 0 THEN
            COMMIT;
        END IF;

        SET i = i + 1;
    END WHILE;
    COMMIT;
END$$

CALL `sp_generar_estudiantes`()$$
DROP PROCEDURE `sp_generar_estudiantes`$$

DELIMITER ;

-- =============================================================================
-- DATOS MASIVOS: Inscripciones + Calificaciones (50,000+ registros)
-- =============================================================================
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_generar_inscripciones`$$
CREATE PROCEDURE `sp_generar_inscripciones`()
BEGIN
    DECLARE done       INT DEFAULT 0;
    DECLARE v_est_id   BIGINT;
    DECLARE v_est_estado VARCHAR(20);
    DECLARE v_per_id   BIGINT;
    DECLARE v_hor_id   BIGINT;
    DECLARE v_insc_id  BIGINT;
    DECLARE v_materias INT;
    DECLARE v_mat      INT;
    DECLARE v_nota     DECIMAL(5,2);
    DECLARE v_estado_insc ENUM('inscrito','retirado','aprobado','reprobado');
    DECLARE v_count    INT DEFAULT 0;

    -- Cursor: solo activos y con historial (activo/inactivo)
    DECLARE cur_est CURSOR FOR
        SELECT id, estado FROM estudiantes
        WHERE estado IN ('activo','inactivo','graduado')
        LIMIT 5000;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    SELECT id INTO v_per_id FROM periodos_academicos WHERE activo = 1 LIMIT 1;

    OPEN cur_est;
    est_loop: LOOP
        FETCH cur_est INTO v_est_id, v_est_estado;
        IF done THEN LEAVE est_loop; END IF;

        -- Cada estudiante activo se inscribe en 3-6 materias
        SET v_materias = 3 + FLOOR(RAND() * 4);
        SET v_mat = 0;

        WHILE v_mat < v_materias DO
            -- Horario aleatorio del período activo
            SELECT id INTO v_hor_id
            FROM horarios
            WHERE periodo_id = v_per_id AND activo = 1 AND cupo_disponible > 0
            ORDER BY RAND()
            LIMIT 1;

            IF v_hor_id IS NOT NULL THEN
                -- Estado de inscripción según estado del estudiante
                SET v_estado_insc = CASE
                    WHEN v_est_estado = 'activo'   THEN 'inscrito'
                    WHEN v_est_estado = 'inactivo' THEN ELT(1+FLOOR(RAND()*2),'aprobado','reprobado')
                    WHEN v_est_estado = 'graduado' THEN 'aprobado'
                    ELSE 'retirado'
                END;

                INSERT IGNORE INTO `inscripciones`
                    (`estudiante_id`,`horario_id`,`periodo_id`,`estado`)
                VALUES
                    (v_est_id, v_hor_id, v_per_id, v_estado_insc);

                SET v_insc_id = LAST_INSERT_ID();

                -- Actualizar cupo
                IF v_insc_id > 0 THEN
                    UPDATE horarios SET cupo_disponible = cupo_disponible - 1
                    WHERE id = v_hor_id AND cupo_disponible > 0;

                    -- Generar calificaciones si no está inscrito actualmente
                    IF v_estado_insc IN ('aprobado','reprobado') THEN
                        INSERT IGNORE INTO `calificaciones`
                            (`inscripcion_id`,`tipo_evaluacion`,`nota`,`nota_maxima`,`peso_porcentual`,`fecha_evaluacion`)
                        VALUES
                            (v_insc_id,'parcial_1', ROUND(8 + RAND()*12, 2), 20, 25, DATE_SUB(CURDATE(), INTERVAL 90 DAY)),
                            (v_insc_id,'parcial_2', ROUND(8 + RAND()*12, 2), 20, 25, DATE_SUB(CURDATE(), INTERVAL 60 DAY)),
                            (v_insc_id,'final',     ROUND(8 + RAND()*12, 2), 20, 40, DATE_SUB(CURDATE(), INTERVAL 30 DAY)),
                            (v_insc_id,'practica',  ROUND(8 + RAND()*12, 2), 20, 10, DATE_SUB(CURDATE(), INTERVAL 45 DAY));
                    END IF;
                END IF;
            END IF;

            SET v_mat = v_mat + 1;
            SET v_hor_id = NULL;
        END WHILE;

        SET v_count = v_count + 1;
        IF v_count MOD 200 = 0 THEN
            COMMIT;
        END IF;

    END LOOP;
    CLOSE cur_est;
    COMMIT;
END$$

CALL `sp_generar_inscripciones`()$$
DROP PROCEDURE `sp_generar_inscripciones`$$

DELIMITER ;

-- =============================================================================
-- LIMPIEZA DE PROCEDIMIENTOS TEMPORALES
-- =============================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- Verificación de volumen generado
SELECT 'carreras'           AS tabla, COUNT(*) AS registros FROM carreras
UNION ALL SELECT 'periodos_academicos', COUNT(*) FROM periodos_academicos
UNION ALL SELECT 'profesores',          COUNT(*) FROM profesores
UNION ALL SELECT 'aulas',               COUNT(*) FROM aulas
UNION ALL SELECT 'asignaturas',         COUNT(*) FROM asignaturas
UNION ALL SELECT 'horarios',            COUNT(*) FROM horarios
UNION ALL SELECT 'estudiantes',         COUNT(*) FROM estudiantes
UNION ALL SELECT 'inscripciones',       COUNT(*) FROM inscripciones
UNION ALL SELECT 'calificaciones',      COUNT(*) FROM calificaciones;

-- =============================================================================
-- FIN DE SEED DATA
-- =============================================================================
