-- Script rápido para crear usuarios de prueba
-- Ejecutar: mysql -u root sge_db < quick_users.sql

-- Desarrollador (id=2)
INSERT IGNORE INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (2, 'Desarrollador Sistema', 'developer@universidad.edu.ve', NOW(), '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q', 'desarrollador', NOW(), NOW());

-- Soporte IT (id=3)
INSERT IGNORE INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (3, 'Soporte TI', 'soporte@universidad.edu.ve', NOW(), '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q', 'soporte_it', NOW(), NOW());

-- Profesor (id=100)
INSERT IGNORE INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (100, 'Prof. Juan Pérez', 'profesor@universidad.edu.ve', NOW(), '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q', 'profesor', NOW(), NOW());

-- Estudiante (id=101)
INSERT IGNORE INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (101, 'María González', 'estudiante@universidad.edu.ve', NOW(), '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q', 'estudiante', NOW(), NOW());

-- Solicitante (id=6)
INSERT IGNORE INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) 
VALUES (6, 'Carlos Ramírez', 'solicitante@universidad.edu.ve', NOW(), '$2y$12$H5aVhJFBtZSk6evls6Z/huCWK1Wq3bWsVY35lRn1dtp2tWrzcpl5q', 'estudiante', NOW(), NOW());

SELECT 'Usuarios creados exitosamente!' AS mensaje;
SELECT id, name, email, role FROM users WHERE id IN (1,2,3,6,100,101);
