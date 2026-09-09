-- Script para verificar usuarios en la base de datos

-- Ver todos los usuarios
SELECT id, name, email, role, created_at 
FROM users 
ORDER BY created_at DESC 
LIMIT 10;

-- Ver estudiantes
SELECT id, nombre, apellido, email, cedula, estatus, matricula
FROM estudiantes
ORDER BY created_at DESC
LIMIT 10;

-- Verificar si hay usuarios sin relacionar
SELECT u.id, u.email, u.role, 
       CASE 
           WHEN e.email IS NOT NULL THEN 'Tiene estudiante'
           ELSE 'Sin estudiante'
       END as estado_estudiante
FROM users u
LEFT JOIN estudiantes e ON u.email = e.email
WHERE u.role = 'estudiante';

-- Limpiar throttle (cache de rate limiting)
-- Esto se hace mejor con: php artisan cache:clear
