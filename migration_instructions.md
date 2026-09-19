# Migración de XAMPP a Laragon

## ⚠️ IMPORTANTE: Ya tenemos backup de la base de datos
- Backup ubicado en: `C:\xampp\mysql\data\sge_db_backup`

## Paso 1: Descargar Laragon
1. Ve a: https://laragon.org/download/
2. Descarga: **Laragon Full** (incluye PHP 8.x, MySQL 8.x)
3. Instala en: `C:\laragon` (ubicación por defecto)

## Paso 2: Iniciar Laragon
1. Abre Laragon
2. Click en "Start All" (arranca Apache + MySQL automáticamente)
3. Verifica que MySQL esté corriendo (indicador verde)

## Paso 3: Restaurar Base de Datos

### Opción A: Copiar carpeta directamente (MÁS RÁPIDO)
```powershell
# Detener MySQL en Laragon (botón Stop)
# Copiar la carpeta de backup
Copy-Item "C:\xampp\mysql\data\sge_db_backup" -Destination "C:\laragon\data\mysql\sge_db" -Recurse
# Iniciar MySQL en Laragon
```

### Opción B: Import SQL (si Opción A falla)
1. Abre phpMyAdmin de Laragon: http://localhost/phpmyadmin
2. Crea base de datos: `sge_db`
3. Import: Usa el archivo SQL que crearemos

## Paso 4: Actualizar .env del Backend
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sge_db
DB_USERNAME=root
DB_PASSWORD=
```

## Paso 5: Verificar
```bash
cd backend
php artisan db:show
```

---

## Alternativa: Reinstalar XAMPP Limpio

Si prefieres quedarte con XAMPP:

1. **Backup completo**: Ya tenemos `sge_db_backup`
2. **Desinstalar XAMPP**:
   - Panel de Control → Desinstalar programas
   - Eliminar carpeta `C:\xampp` manualmente
3. **Descargar XAMPP nuevo**: https://www.apachefriends.org/
4. **Instalar versión limpia**
5. **Restaurar base de datos**: Copiar `sge_db_backup` a `C:\xampp\mysql\data\sge_db`

---

## ¿Qué prefieres?

- ✅ **Laragon** (recomendado): Más moderno, menos problemas
- ⚠️ **Reinstalar XAMPP**: Si ya conoces bien XAMPP

**Mi recomendación personal: Laragon** 🚀
