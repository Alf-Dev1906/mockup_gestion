# ⚡ QUICK START - Deploy en 15 minutos

## 🔗 Repositorio
```
https://github.com/Alf-Dev1906/mockup_gestion
```

---

## 📱 PASO 1: Deploy Backend en Render (7 minutos)

1. Ir a https://render.com
2. Click **"New"** → **"Web Service"**
3. Conectar GitHub → Seleccionar `mockup_gestion`
4. Llenar:
   - **Name:** `mockup-gestion-backend`
   - **Runtime:** PHP
   - **Build:** `composer install && php artisan migrate:fresh --seed`
   - **Start:** `php -S 0.0.0.0:10000 -t public`
5. Click **"Create Web Service"**

**Esperar 5 minutos a que deployed** ✅

---

## 🎨 PASO 2: Deploy Frontend en Netlify (5 minutos)

1. Ir a https://netlify.com
2. Click **"Add new site"** → **"Import an existing project"**
3. Conectar GitHub
4. Seleccionar `mockup_gestion`
5. Llenar:
   - **Base:** `frontend`
   - **Build:** `npm run build`
   - **Publish:** `frontend/dist`
6. Agregar variable:
   - **Key:** `VITE_API_BASE_URL`
   - **Value:** (URL del backend de Render)
7. Click **"Deploy"**

**Esperar 2 minutos** ✅

---

## 🎯 RESULTADO FINAL

Después de 12 minutos tendrás:

```
✅ Backend:  https://mockup-gestion-backend.onrender.com
✅ Frontend: https://mockup-gestion-frontend.netlify.app
✅ API:      https://mockup-gestion-backend.onrender.com/api
```

---

## 🔐 Credenciales

```
Email:    admin@universidad.edu.ve
Password: Admin2026!

(O profesor/estudiante/solicitante con mismo password)
```

---

## ⚙️ Si algo no funciona

### El backend no levanta
- Verificar que MySQL se conectó (Render auto-crea la BD)
- Ver logs en Render → "Logs"

### Frontend no conecta al backend
- Verificar `VITE_API_BASE_URL` en Netlify = URL correcta del backend
- Redeploy en Netlify

### Landing page no se ve
- Esperar 2 minutos después de deploy en Netlify
- Hacer refresh con Ctrl+Shift+R (limpiar caché)

---

## 📚 Más Información

- [README_DEPLOYMENT.md](./README_DEPLOYMENT.md) - Guía completa
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Documentación detallada

---

**¡Listo para mostrar a tus partners! 🚀**
