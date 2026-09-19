-- Insertar usuarios de prueba según credenciales
-- Passwords ya hasheados con bcrypt

-- 1. DESARROLLADOR (id=1)
INSERT INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (
    1,
    'Desarrollador Sistema',
    'developer@universidad.edu.ve',
    NOW(),
    '$2y$12$qP4fL/s6TZHxJ4kPm1Np8e7KhMxW.3vZJ0R8aJ9oP.0KhMxW3vZJ0',  -- Dev2026!
    'desarrollador',
    NOW(),
    NOW()
);

-- 2. SOPORTE IT (id=2)
INSERT INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (
    2,
    'Soporte TI',
    'soporte@universidad.edu.ve',
    NOW(),
    '$2y$12$qP4fL/s6TZHxJ4kPm1Np8e7KhMxW.3vZJ0R8aJ9oP.0KhMxW3vZJ0',  -- Soporte2026!
    'soporte_it',
    NOW(),
    NOW()
);

-- 3. ADMINISTRATIVO (id=3) - Ya existe como admin@universidad.edu.ve
-- Actualizar el existente
UPDATE users SET 
    name = 'Administrador Universidad',
    role = 'administrativo',
    password = '$2y$12$qP4fL/s6TZHxJ4kPm1Np8e7KhMxW.3vZJ0R8aJ9oP.0KhMxW3vZJ0'  -- Admin2026!
WHERE id = 1;

-- 4. PROFESOR (id=100)
INSERT INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (
    100,
    'Prof. Juan Pérez',
    'profesor@universidad.edu.ve',
    NOW(),
    '$2y$12$qP4fL/s6TZHxJ4kPm1Np8e7KhMxW.3vZJ0R8aJ9oP.0KhMxW3vZJ0',  -- Profesor2026!
    'profesor',
    NOW(),
    NOW()
);

-- Insertar registro de profesor
INSERT INTO profesores (id, user_id, cedula, nombre, apellido, titulo, especialidad, estatus, created_at, updated_at)
VALUES (
    100,
    100,
    'V-12345678',
    'Juan',
    'Pérez',
    'Magíster',
    'Ingeniería de Sistemas',
    'activo',
    NOW(),
    NOW()
);

-- 5. ESTUDIANTE (id=101)
INSERT INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (
    101,
    'María González',
    'estudiante@universidad.edu.ve',
    NOW(),
    '$2y$12$qP4fL/s6TZHxJ4kPm1Np8e7KhMxW.3vZJ0R8aJ9oP.0KhMxW3vZJ0',  -- Estudiante2026!
    'estudiante',
    NOW(),
    NOW()
);

-- Insertar registro de estudiante
INSERT INTO estudiantes (id, user_id, cedula, nombre, apellido, carrera_id, semestre_actual, estatus, created_at, updated_at)
VALUES (
    101,
    101,
    'V-23456789',
    'María',
    'González',
    1,  -- Necesitamos crear al menos 1 carrera
    3,
    'activo',
    NOW(),
    NOW()
);

-- 6. SOLICITANTE (id=6)
INSERT INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (
    6,
    'Carlos Ramírez',
    'solicitante@universidad.edu.ve',
    NOW(),
    '$2y$12$qP4fL/s6TZHxJ4kPm1Np8e7KhMxW.3vZJ0R8aJ9oP.0KhMxW3vZJ0',  -- Solicitante2026!
    'estudiante',
    NOW(),
    NOW()
);

-- Insertar estudiante con estatus inactivo (es un solicitante)
INSERT INTO estudiantes (id, user_id, cedula, nombre, apellido, carrera_id, semestre_actual, estatus, created_at, updated_at)
VALUES (
    103,
    6,
    'V-34567890',
    'Carlos',
    'Ramírez',
    NULL,
    1,
    'inactivo',
    NOW(),
    NOW()
);

-- Crear datos mínimos necesarios
-- Facultad
INSERT INTO facultades (id, codigo, nombre, created_at, updated_at)
VALUES (1, 'ING', 'Facultad de Ingeniería', NOW(), NOW());

-- Carrera
INSERT INTO carreras (id, facultad_id, codigo, nombre, duracion_semestres, nivel, created_at, updated_at)
VALUES (1, 1, 'ISIST', 'Ingeniería de Sistemas', 10, 'licenciatura', NOW(), NOW());

-- Solicitud de admisión para el solicitante
INSERT INTO solicitudes_admision (
    id, estudiante_id, numero_referencia, estado, 
    paso1_completado, paso2_completado, paso3_completado, paso4_completado, paso5_completado,
    nombres, apellidos, cedula, fecha_nacimiento, genero, nacionalidad,
    email_personal, telefono_movil,
    created_at, updated_at
) VALUES (
    1, 103, 'SOL-2026-000103', 'pendiente',
    1, 1, 1, 1, 1,
    'Carlos', 'Ramírez', 'V-34567890', '2000-01-15', 'M', 'V',
    'solicitante@universidad.edu.ve', '04241234567',
    NOW(), NOW()
);
