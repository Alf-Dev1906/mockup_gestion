# 🎨 Setup Frontend en Netlify (Vue.js)

Frontend con Vue 3, Vite y Tailwind CSS.

---

## ✅ 5 Pasos - 5 minutos

### **PASO 1: Ir a Netlify**

1. Ir a https://netlify.com
2. Click **"Add new site"** → **"Import an existing project"**
3. Si no está conectado GitHub, conéctalo

### **PASO 2: Seleccionar repositorio**

1. Buscar `Alf-Dev1906/mockup_gestion`
2. Click para seleccionar
3. Click **"Deploy site"**

### **PASO 3: Configurar build**

Rellenar así:

```
Base directory:     frontend
Build command:      npm run build
Publish directory:  frontend/dist
```

### **PASO 4: Agregar variable de entorno**

**IMPORTANTE:** La URL del backend debe ser la de Render que obtuviste arriba.

En "Environment Variables" (o "Build & Deploy" → "Environment"):

```
Key:   VITE_API_BASE_URL
Value: https://mockup-gestion-backend.onrender.com/api
```

(Reemplaza con tu URL real de Render)

### **PASO 5: Deploy**

1. Click **"Deploy site"**
2. Esperar 2-3 minutos
3. Netlify te dará una URL como:
   ```
   https://mockup-gestion-frontend.netlify.app
   ```

---

## 🎯 Verificación

Una vez deployado:

1. Ir a la URL de Netlify
2. Debería verse la **Landing Page** con:
   - ✅ Carrusel Hero animado
   - ✅ Estadísticas
   - ✅ Botón "Inscríbete Ahora"
   - ✅ Sección de carreras
   - ✅ Noticias

3. Click en **"Inscríbete Ahora"** → debería redirigir a login

---

## 🔐 Login y Prueba

1. En la página de login:
   ```
   Email:    admin@universidad.edu.ve
   Password: Admin2026!
   ```

2. Debería cargar el Dashboard

3. Si ves error de "Cannot GET /", ir a Paso 5 en Netlify y redeploy

---

## ❓ Errores comunes

### Página blanca o error 404
**Causa:** Netlify no está sirviendo el build correcto

**Solución:**
1. En Netlify, ir a "Site settings" → "Build & deploy"
2. Verificar:
   - Build command: `npm run build`
   - Publish directory: `frontend/dist`
3. Click "Trigger deploy"

### Error de conexión al backend
**Causa:** Variable `VITE_API_BASE_URL` no correcta o backend no responde

**Solución:**
1. Abrir consola del navegador (F12)
2. Ver error exacto
3. Verificar que la URL del backend es correcta en Netlify

### Backend responde pero retorna 401/403
**Causa:** Token CORS o autenticación

**Solución:**
- Backend está bien
- Es un problema de sesión
- Hacer login de nuevo

---

## 🔄 ¿Qué sigue?

Ahora tienes:

```
✅ Backend en: https://mockup-gestion-backend.onrender.com
✅ Frontend en: https://mockup-gestion-frontend.netlify.app
✅ Todo funcionando 100%
```

### Credenciales finales para mostrar a partners:

```
🔐 ADMIN
   Email:    admin@universidad.edu.ve
   Password: Admin2026!

🎓 PROFESOR
   Email:    profesor@universidad.edu.ve
   Password: Admin2026!

👨‍🎓 ESTUDIANTE
   Email:    estudiante@universidad.edu.ve
   Password: Admin2026!

📝 SOLICITANTE
   Email:    solicitante@universidad.edu.ve
   Password: Admin2026!
```

---

## 🎉 ¡LISTO!

Tu Sistema de Gestión Universitaria está completamente deployado y listo para mostrar a tus partners.

**URLs finales:**
- 🎨 Frontend: https://mockup-gestion-frontend.netlify.app
- 🔧 API: https://mockup-gestion-backend.onrender.com/api

---

**¿Necesitas ayuda con algo?**
