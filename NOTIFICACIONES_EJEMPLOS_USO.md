# Ejemplos de Uso — Sistema de Notificaciones (Bloque 5.3)

## Arquitectura

```
┌─────────────────────────────────────────────────────┐
│                      Componentes Vue                 │
│  (NotificationBell.vue, Notificaciones.vue, etc)   │
└──────────────────┬──────────────────────────────────┘
                   │ usa
                   ↓
┌─────────────────────────────────────────────────────┐
│         useNotifications() Composable               │
│  • State UI local (mostrarPanel)                   │
│  • Métodos: obtenerNotificaciones(), marcarLeida() │
│  • Helpers: getIconoPorTipo(), getColorPorTipo()   │
│  • onMounted/onUnmounted: control de ciclo         │
└──────────────────┬──────────────────────────────────┘
                   │ usa
                   ↓
┌─────────────────────────────────────────────────────┐
│      useNotificationsStore() Store Pinia           │
│  State:                                            │
│  • items[]               notificaciones             │
│  • unreadCount          contador no leídas          │
│  • loading              estado de carga             │
│  • pollingActive        polling en marcha           │
│                                                    │
│  Computed:                                         │
│  • totalNotificaciones, noLeidas, leidas, porTipo │
│                                                    │
│  Actions (9):                                      │
│  • fetchUnreadCount()   (polling cada 30s)         │
│  • fetchAll()           (GET paginado)             │
│  • markRead()           (optimistic update)        │
│  • markAllRead()                                   │
│  • deleteNotification()                            │
│  • deleteAllRead()                                 │
│  • startPolling()                                  │
│  • stopPolling()                                   │
│  • reset()                                         │
└──────────────────┬──────────────────────────────────┘
                   │ ejecuta
                   ↓
        ┌──────────────────────┐
        │   API Backend        │
        │ /notificaciones      │
        │ (HTTP REST)          │
        └──────────────────────┘
```

---

## Uso en Componentes

### 1. Usar el Composable (Recomendado para componentes)

```vue
<script setup>
import { useNotifications } from '@/composables/useNotifications'

const {
  notificaciones,       // ref array
  contadorNoLeidas,     // computed (int)
  cargando,             // computed (bool)
  mostrarPanel,         // ref bool
  marcarLeida,          // async (id) => Promise
  obtenerNotificaciones,// async (page, filtro) => Promise
  getIconoPorTipo,      // (tipo) => string
  getColorPorTipo,      // (tipo) => string
} = useNotifications()

// Al montar, inicia polling automático
// Al desmontar, detiene polling
</script>

<template>
  <div>
    <!-- Campana -->
    <button @click="mostrarPanel = !mostrarPanel">
      🔔 {{ contadorNoLeidas }}
    </button>
    
    <!-- Cargar todas las notificaciones -->
    <button @click="obtenerNotificaciones(1, 'todas')">
      Cargar
    </button>

    <!-- Listar -->
    <div v-for="n in notificaciones" :key="n.id">
      <span>{{ getIconoPorTipo(n.tipo) }}</span>
      <p>{{ n.titulo }}</p>
      <button @click="marcarLeida(n.id)">Marcar leída</button>
    </div>
  </div>
</template>
```

---

### 2. Usar el Store directamente (Para lógica compleja)

```vue
<script setup>
import { useNotificationsStore } from '@/stores/notifications'

const store = useNotificationsStore()

// Acceder al estado
console.log(store.items)           // array
console.log(store.unreadCount)     // int
console.log(store.loading)         // bool
console.log(store.pollingActive)   // bool

// Computed
console.log(store.totalNotificaciones)  // int
console.log(store.noLeidas)             // array filtrado
console.log(store.leidas)               // array filtrado
console.log(store.porTipo)              // { examen_publicado: [...], ... }

// Llamar actions
await store.fetchAll(1, 'no-leidas')    // GET /notificaciones
await store.markRead(123)               // POST /notificaciones/123/leer
await store.startPolling(30000)         // Inicia polling
await store.stopPolling()               // Detiene polling

// Limpiar todo
store.reset()
</script>
```

---

## Optimistic Update Explicado

El store implementa **optimistic update** para mejor UX:

```javascript
// En markRead():

// 1. Actualizar localmente (inmediato)
const idx = items.value.findIndex(n => n.id === id)
items.value[idx].leida = true
unreadCount.value--

// 2. API en background
try {
  await api.post(`/notificaciones/${id}/leer`)
  // ✅ Éxito, estado ya reflejado
} catch (error) {
  // 3. Si falla, revertir
  items.value[idx].leida = false
  unreadCount.value++
  throw error
}
```

**Ventajas:**
- ✅ Interface **inmediata** (no espera API)
- ✅ Si API falla, se revierte automáticamente
- ✅ Mejor perceived performance

---

## Polling Automático

### Cómo funciona:

```javascript
// En composable onMounted():
store.startPolling(30000)  // Inicia polling

// En store startPolling():
setInterval(async () => {
  const { data } = await api.get('/notificaciones/no-leidas-count')
  // Actualiza: unreadCount, items (últimas 3)
}, 30000)  // cada 30 segundos
```

### Por qué es **ligero**:

- No obtiene lista completa
- Solo obtiene **contador** + últimas 3 notificaciones
- 1 request pequeño cada 30 segundos
- Actualiza UI en tiempo real

### Iniciar/Detener:

```javascript
// Automático en componentes que usan composable
onMounted(() => store.startPolling())
onUnmounted(() => store.stopPolling())

// Manual si necesitas control
const store = useNotificationsStore()
store.startPolling(15000)   // cambiar intervalo
store.stopPolling()         // detener
store.pollingActive         // check estado
```

---

## Casos de Uso

### Caso 1: Mostrar badge en campana

```vue
<script setup>
import { useNotifications } from '@/composables/useNotifications'

const { contadorNoLeidas } = useNotifications()
// Polling automático inicia aquí
</script>

<template>
  <!-- Badge actualiza automáticamente cada 30s -->
  <div class="badge">{{ contadorNoLeidas }}</div>
</template>

<!-- El polling continúa mientras este componente está montado -->
```

### Caso 2: Cargar todas las notificaciones con paginación

```vue
<script setup>
import { ref } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

const { obtenerNotificaciones, notificaciones, cargando } = useNotifications()

const page = ref(1)
const filtro = ref('todas')

const cargar = async () => {
  const pagination = await obtenerNotificaciones(page.value, filtro.value)
  // notificaciones.value ahora contiene la página
}

const cambiarPagina = async (newPage) => {
  page.value = newPage
  await cargar()
}
</script>

<template>
  <button @click="cargar">Cargar</button>
  <button @click="cambiarPagina(2)">Página 2</button>
  
  <div v-if="cargando">Cargando...</div>
  <div v-else v-for="n in notificaciones" :key="n.id">
    {{ n.titulo }}
  </div>
</template>
```

### Caso 3: Filtrar notificaciones del store

```vue
<script setup>
import { useNotificationsStore } from '@/stores/notifications'

const store = useNotificationsStore()

// Computed derivados del store
const tareasNoLeidas = computed(() => {
  return store.items.filter(n => 
    !n.leida && n.tipo.includes('tarea')
  )
})

const examenesCalificados = computed(() => {
  return store.porTipo['examen_calificado'] || []
})
</script>

<template>
  <div>
    <h2>Tareas sin leer ({{ tareasNoLeidas.length }})</h2>
    <div v-for="t in tareasNoLeidas" :key="t.id">{{ t.titulo }}</div>
    
    <h2>Exámenes calificados</h2>
    <div v-for="e in examenesCalificados" :key="e.id">{{ e.titulo }}</div>
  </div>
</template>
```

### Caso 4: Integrar en Header.vue

```vue
<!-- Header.vue -->
<script setup>
import NotificationBell from '@/components/NotificationBell.vue'
</script>

<template>
  <header class="bg-white shadow">
    <nav class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
      <!-- Logo -->
      <h1>Mi App</h1>
      
      <!-- Campana (polling automático aquí) -->
      <NotificationBell />
      
      <!-- Avatar -->
    </nav>
  </header>
</template>
```

---

## Métodos del Store (Referencia)

### fetchUnreadCount() — Polling

```javascript
// GET /notificaciones/no-leidas-count
// Devuelve: { no_leidas_count, ultimas_notificaciones[] }

const store = useNotificationsStore()
await store.fetchUnreadCount()

// Resultado:
console.log(store.unreadCount)    // 5
console.log(store.items.slice(0,3)) // últimas 3
```

### fetchAll() — Obtener paginado

```javascript
// GET /notificaciones?page=1&filtro=todas&per_page=20

const paginacion = await store.fetchAll(
  1,              // page
  'no-leidas',    // filtro: 'todas' | 'no-leidas' | 'leidas'
  20              // perPage (opcional)
)

console.log(paginacion)
// { current_page, total, per_page, last_page }

console.log(store.items) // array actualizado
```

### markRead() — Marcar como leída

```javascript
// POST /notificaciones/{id}/leer (optimistic update)

try {
  await store.markRead(123)
  // ✅ Actualizada localmente + API
} catch (error) {
  // ❌ Si API falla, se revierte automáticamente
}

console.log(store.unreadCount) // decrementado
```

### markAllRead() — Marcar todas

```javascript
// POST /notificaciones/leer-todas

try {
  await store.markAllRead()
  // ✅ Todas en estado leído
} catch (error) {
  // ❌ Si falla, se recargan desde API
}

console.log(store.unreadCount) // 0
```

### deleteNotification() — Eliminar una

```javascript
// DELETE /notificaciones/{id}

try {
  await store.deleteNotification(123)
  // ✅ Eliminada del state
} catch (error) {
  // ❌ Si falla, se revierte
}

console.log(store.items.find(n => n.id === 123)) // undefined
```

### deleteAllRead() — Eliminar leídas

```javascript
// POST /notificaciones/eliminar-todas

try {
  await store.deleteAllRead()
  // ✅ Todas leídas eliminadas localmente
} catch (error) {
  // ❌ Si falla, se recargan
}
```

### startPolling() — Iniciar polling

```javascript
store.startPolling(30000)  // ms (default: 30s)

console.log(store.pollingActive)  // true

// Cada 30s: GET /notificaciones/no-leidas-count
// Actualiza: store.unreadCount
```

### stopPolling() — Detener polling

```javascript
store.stopPolling()

console.log(store.pollingActive)  // false
```

### reset() — Limpiar todo

```javascript
store.reset()

console.log(store.items)          // []
console.log(store.unreadCount)    // 0
console.log(store.loading)        // false
console.log(store.pollingActive)  // false
```

---

## Testing

### Test: Polling se inicia automáticamente

```javascript
// En componente que usa useNotifications()
import { useNotifications } from '@/composables/useNotifications'
import { useNotificationsStore } from '@/stores/notifications'

const { } = useNotifications()
const store = useNotificationsStore()

// Después de montar
expect(store.pollingActive).toBe(true)

// Después de desmontar
// expect(store.pollingActive).toBe(false)
```

### Test: Optimistic update funciona

```javascript
const store = useNotificationsStore()

// Setup
store.items = [
  { id: 1, leida: false, titulo: 'Test' }
]
store.unreadCount = 1

// Action
store.markRead(1)

// Verificar optimistic (inmediato)
expect(store.items[0].leida).toBe(true)
expect(store.unreadCount).toBe(0)
```

---

## Debugging

### Inspeccionar estado del store

```javascript
const store = useNotificationsStore()
console.log(store.$state)  // Todo el estado

// Desde Vue DevTools → Pinia tab
// Ver store en tiempo real
```

### Logging de polling

```javascript
// En useNotifications.js, agregar:
onMounted(() => {
  console.log('🔔 Polling iniciado')
  store.startPolling(30000)
})

onUnmounted(() => {
  console.log('🔔 Polling detenido')
  store.stopPolling()
})
```

### Network tab (F12)

- Cada 30 segundos: `GET /notificaciones/no-leidas-count`
- Al hacer clic: `POST /notificaciones/{id}/leer`
- Al eliminar: `DELETE /notificaciones/{id}`

---

## Performance

**Memoria:**
- `items[]`: máx 20 notificaciones por fetch
- Polling: 1 request cada 30s (~0.5KB respuesta)

**CPU:**
- Polling: setInterval + API call
- Store: computed reactividad Vue 3 (muy eficiente)
- Optimistic update: O(n) búsqueda en array

**Recomendaciones:**
- ✅ Usar composable en componentes (auto ciclo)
- ✅ Polling cada 30s es buen balance
- ✅ Limpiar notificaciones antiguas (API diaria)
- ❌ No crear múltiples instances del store
