# 🚀 Ejecutar Migraciones y Seeders SIN Shell de Render

Ya que Render requiere plan pago para la Shell, usaremos un script PHP temporal para ejecutar las migraciones y seeders.

## ⚠️ IMPORTANTE
Este script es TEMPORAL y DEBE ser eliminado después de terminar el setup.

---

## Paso 1: Hacer Push de los Cambios

Primero necesitamos subir el nuevo código a GitHub para que Render lo despliegue:

```bash
cd C:\Users\danie\Documents\Gestion_uni
git add .
git commit -m "feat: Add setup.php script for migrations without shell access"
git push origin main
```

**Espera 5-10 minutos** para que Render re-despliegue automáticamente.

---

## Paso 2: Verificar que el Script Funciona

Abre tu navegador y visita:

```
https://gestion-uni-backend.onrender.com/setup.php
```

**Resultado esperado:**
```json
{
  "message": "Script de Setup para Render",
  "endpoints": {
    "migrate": "?action=migrate",
    "seed": "?action=seed&seeder=UserSeeder",
    "verify": "?action=verify"
  },
  "seeders": [...]
}
```

✅ Si ves este JSON, el script está funcionando.

---

## Paso 3: Ejecutar Migraciones

```
https://gestion-uni-backend.onrender.com/setup.php?action=migrate
```

**Resultado esperado:**
```json
{
  "success": true,
  "message": "Migraciones ejecutadas",
  "output": "Migrating: 2014_10_12_000000_create_users_table\nMigrated: ..."
}
```

✅ Si ves `"success": true`, las tablas fueron creadas.

---

## Paso 4: Ejecutar Seeders

Abre estas URLs **UNA POR UNA** en tu navegador (espera a que termine cada una):

### Seeder 1: UserSeeder (5 segundos)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=UserSeeder
```

### Seeder 2: DemoSeeder (8-12 minutos ⏳)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=DemoSeeder
```
**⚠️ IMPORTANTE:** Este toma 8-12 minutos. NO cierres el navegador.

### Seeder 3: LinkDemoUsersSeeder (5 segundos)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=LinkDemoUsersSeeder
```

### Seeder 4: SolicitudesYPagosSeeder (10 segundos)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=SolicitudesYPagosSeeder
```

### Seeder 5: PagosMockupSeeder (15 segundos)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=PagosMockupSeeder
```

### Seeder 6: CalificacionesMockupSeeder (20 segundos)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=CalificacionesMockupSeeder
```

### Seeder 7: ProfesorDemoDataSeeder (30 segundos)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=ProfesorDemoDataSeeder
```

### Seeder 8: EstudianteDemoDataSeeder (30 segundos)
```
https://gestion-uni-backend.onrender.com/setup.php?action=seed&seeder=EstudianteDemoDataSeeder
```

---

## Paso 5: Verificar que Todo Funcionó

```
https://gestion-uni-backend.onrender.com/setup.php?action=verify
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

## Paso 6: ELIMINAR el Script Temporal

**⚠️ MUY IMPORTANTE:** Una vez terminado el setup, DEBES eliminar este script por seguridad.

```bash
cd C:\Users\danie\Documents\Gestion_uni\backend
rm public/setup.php
```

Guarda los cambios y haz push:
```bash
git add .
git commit -m "chore: Remove temporary setup script"
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
