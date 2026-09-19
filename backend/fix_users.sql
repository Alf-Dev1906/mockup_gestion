-- Arreglar passwords y crear registros faltantes
-- Password: mismo para todos (ya hasheado con bcrypt)

-- 1. Actualizar password del admin
UPDATE users SET password = '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q' WHERE id = 1;

-- 2. Actualizar passwords de estudiante y solicitante
UPDATE users SET password = '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q' WHERE id = 101;
UPDATE users SET password = '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q' WHERE id = 6;
UPDATE users SET password = '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q' WHERE id = 100;

-- 3. Crear registro de profesor (con todos los campos requeridos)
INSERT IGNORE INTO profesores (
    id, user_id, cedula, nombre, apellido, email, 
    codigo_empleado, facultad_id, especialidad, estatus, 
    created_at, updated_at
) VALUES (
    100, 100, 'V-12345678', 'Juan', 'Pérez', 'profesor@universidad.edu.ve',
    'PROF-100', 1, 'Ingeniería de Sistemas', 'activo',
    NOW(), NOW()
);

-- 4. Crear registro de estudiante activo
INSERT IGNORE INTO estudiantes (
    id, user_id, cedula, nombre, apellido, 
    carrera_id, semestre_actual, estatus, 
    created_at, updated_at
) VALUES (
    101, 101, 'V-23456789', 'María', 'González',
    1, 3, 'activo',
    NOW(), NOW()
);

-- 5. Crear registro de estudiante solicitante (inactivo)
INSERT IGNORE INTO estudiantes (
    id, user_id, cedula, nombre, apellido, 
    carrera_id, semestre_actual, estatus, 
    created_at, updated_at
) VALUES (
    103, 6, 'V-34567890', 'Carlos', 'Ramírez',
    NULL, 1, 'inactivo',
    NOW(), NOW()
);

-- 6. Crear solicitud de admisión para el solicitante
INSERT IGNORE INTO solicitudes_admision (
    id, estudiante_id, numero_referencia, estado,
    fecha_nacimiento, genero, telefono,
    created_at, updated_at
) VALUES (
    1, 103, 'SOL-2026-000103', 'pendiente',
    '2000-01-15', 'M', '04241234567',
    NOW(), NOW()
);

SELECT 'Usuarios actualizados!' AS mensaje;
SELECT u.id, u.name, u.email, u.role, 
       CASE WHEN p.id IS NOT NULL THEN 'SÍ' ELSE 'NO' END as tiene_profesor,
       CASE WHEN e.id IS NOT NULL THEN 'SÍ' ELSE 'NO' END as tiene_estudiante
FROM users u
LEFT JOIN profesores p ON u.id = p.user_id
LEFT JOIN estudiantes e ON u.id = e.user_id
WHERE u.id IN (1,2,3,6,100,101)
ORDER BY u.id;
