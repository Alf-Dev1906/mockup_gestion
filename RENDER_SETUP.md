# 🚀 Setup Backend en Render.com (CORREGIDO)

El error anterior fue porque Render intentó usar Node.js. Ahora está configurado correctamente con Docker.

---

## ✅ NUEVO SETUP - 5 Pasos

### **PASO 1: Ir a Render.com**

1. Ir a https://render.com
2. Click en **"New"** → **"Web Service"**
3. Si no está conectado GitHub, conéctalo

### **PASO 2: Seleccionar repositorio**

1. Buscar `Alf-Dev1906/mockup_gestion`
2. Click para seleccionar

### **PASO 3: Configurar servicio**

Rellenar así:

```
Name:        mockup-gestion-backend
Environment: Docker (auto-detectado del Dockerfile)
Plan:        Free
Branch:      main
```

### **PASO 4: Agregar variables de entorno**

En "Environment Variables", agregar:

```
APP_NAME        = SGE
APP_ENV         = production
APP_DEBUG       = false
CACHE_DRIVER    = file
SESSION_DRIVER  = cookie
QUEUE_CONNECTION= sync
LOG_CHANNEL     = single
```

### **PASO 5: Crear base de datos MySQL**

1. En Render, ir a **"MySQL"** (lado izquierdo)
2. Click **"New MySQL Database"**
3. Configurar:
   - **Name:** `mockup-gestion-mysql`
   - **Database Name:** `sge_db`
4. Create

Las credenciales de BD se conectarán automáticamente a través del `render.yaml`

---

## 🎯 Resultado

Después de 3-5 minutos verás:

```
✅ Backend listo en: https://mockup-gestion-backend.onrender.com
✅ API disponible en: https://mockup-gestion-backend.onrender.com/api
✅ Base de datos MySQL conectada
```

---

## ✅ Verificar que funciona

1. Ir a `https://mockup-gestion-backend.onrender.com/api/health` 
   (debería devolver JSON)

2. Si ves error 500, ver logs:
   - En Render: Web Service → Logs
   - Buscar el error específico

---

## ❓ Errores comunes

### Error: "Build failed"
**Solución:** Render detectó mal el tipo. Verificar que el Dockerfile está presente en la raíz del repo.

### Error: "Composer not found"
**Solucionado:** Dockerfile ahora lo incluye automáticamente.

### Error: "Connection refused" a BD
**Solución:** Esperar 2 minutos más. La BD tarda en iniciarse.

---

## 🔄 Próximo paso

Una vez el backend esté funcionando:

1. Copiar URL del backend (ej: `https://mockup-gestion-backend.onrender.com`)
2. Ir a [NETLIFY_SETUP.md](./NETLIFY_SETUP.md)
3. Agregar variable `VITE_API_BASE_URL` con esa URL

---

**¿Está funcionando? Avísame y continúo con el frontend en Netlify.**
