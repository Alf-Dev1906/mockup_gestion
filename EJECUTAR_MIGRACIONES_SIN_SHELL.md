# 🚀 Ejecutar Migraciones y Seeders SIN Shell de Render

Ya que Render requiere plan pago para la Shell, usaremos endpoints temporales para ejecutar las migraciones y seeders.

## ⚠️ IMPORTANTE
Estos endpoints son TEMPORALES y DEBEN ser eliminados después de terminar el setup.

---

## Paso 1: Hacer Push de los Cambios

Primero necesitamos subir el nuevo código a GitHub para que Render lo despliegue:

```bash
cd C:\Users\danie\Documents\Gestion_uni
git add .
git commit -m "feat: Add temporary setup endpoints for migrations without shell access"
git push origin main
```

**Espera 5-10 minutos** para que Render re-despliegue automáticamente con los nuevos endpoints.

---

## Paso 2: Ejecutar Migraciones

Abre tu navegador y visita esta URL (reemplaza con tu URL de Render):

```
https://gestion-uni-backend.onrender.com/api/setup/migrate
```

**Resultado esperado:**
```json
{
  "success": true,
  "message": "Migraciones ejecutadas exitosamente",
  "output": "Migrating: 2014_10_12_000000_create_users_table\nMigrated: 2014_10_12_000000_create_users_table (85.12ms)\n..."
}
```

✅ Si ves `"success": true`, las tablas fueron creadas.

---

## Paso 3: Ejecutar Seeders

Abre estas URLs **UNA POR UNA** en tu navegador (espera a que termine cada una):

### Seeder 1: UserSeeder (5 segundos)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/UserSeeder
```

### Seeder 2: DemoSeeder (8-12 minutos ⏳)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/DemoSeeder
```
**⚠️ IMPORTANTE:** Este toma 8-12 minutos. NO cierres el navegador. La página dirá "Cargando..." hasta que termine.

### Seeder 3: LinkDemoUsersSeeder (5 segundos)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/LinkDemoUsersSeeder
```

### Seeder 4: SolicitudesYPagosSeeder (10 segundos)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/SolicitudesYPagosSeeder
```

### Seeder 5: PagosMockupSeeder (15 segundos)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/PagosMockupSeeder
```

### Seeder 6: CalificacionesMockupSeeder (20 segundos)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/CalificacionesMockupSeeder
```

### Seeder 7: ProfesorDemoDataSeeder (30 segundos)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/ProfesorDemoDataSeeder
```

### Seeder 8: EstudianteDemoDataSeeder (30 segundos)
```
https://gestion-uni-backend.onrender.com/api/setup/seed/EstudianteDemoDataSeeder
```

---

## Paso 4: Verificar que Todo Funcionó

Abre esta URL:
```
https://gestion-uni-backend.onrender.com/api/setup/verify
```

**Resultado esperado:**
```json
{
  "success": true,
  "stats": {
    "users": 6,
    "estudiantes": 96002,
    "profesores": 5001,
    "inscripciones": 138000,
    "horarios": 15000
  }
}
```

✅ Si los números coinciden, ¡todo está perfecto!

---

## Paso 5: ELIMINAR los Endpoints Temporales

**⚠️ MUY IMPORTANTE:** Una vez terminado el setup, DEBES eliminar estos endpoints por seguridad.

```bash
cd C:\Users\danie\Documents\Gestion_uni\backend
rm routes/setup.php
```

Luego edita `routes/api.php` y elimina esta línea:
```php
// ⚠️ RUTAS TEMPORALES DE SETUP - BORRAR DESPUÉS DE MIGRACIONES
require __DIR__ . '/setup.php';
```

Guarda los cambios y haz push:
```bash
git add .
git commit -m "chore: Remove temporary setup endpoints"
git push origin main
```

---

## 🎉 ¡Listo!

Tu backend en Render ya tiene:
- ✅ Base de datos PostgreSQL configurada
- ✅ Todas las tablas creadas
- ✅ 257,796 registros de demostración
- ✅ 6 usuarios demo listos para probar

**Siguiente paso:** Configurar el frontend en Netlify

---

## 🆘 Troubleshooting

### Error: "SQLSTATE[08006] Could not connect"
- Verifica las credenciales de PostgreSQL en las variables de entorno de Render
- Asegúrate de que la región del Web Service y PostgreSQL sean la misma

### Error: "Maximum execution time exceeded"
- Es normal en DemoSeeder si tarda >5 minutos
- Simplemente vuelve a ejecutar el mismo seeder, continuará desde donde quedó

### Error 500 en algún seeder
- Abre la URL de nuevo
- Si persiste, ve a Render → Logs y busca el error específico
