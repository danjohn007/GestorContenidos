# Guía Visual de Cambios en la UI

## Vista de la Página Principal

### Layout Anterior
```
+----------------------------------------------------------+
|                        HEADER                             |
|  Logo | Buscador | Acceder                               |
+----------------------------------------------------------+
| Inicio | Nacional | Política | Economía | ... (6 cats)  |
+----------------------------------------------------------+
|                                                           |
|                     SLIDER PRINCIPAL                      |
|                                                           |
+----------------------------------------------------------+
|  [★]         [⏰]         [▦]         [🖼]              |
| Destacadas | Última Hora | Categorías | Multimedia      |
+----------------------------------------------------------+
|                                                           |
|              ★ NOTICIAS DESTACADAS (3 columnas)          |
|   [Noticia 1]    [Noticia 2]    [Noticia 3]             |
|                                                           |
+----------------------------------------------------------+
|                                                           |
|              ⏰ ÚLTIMAS NOTICIAS (3 columnas)            |
|   [Noticia 1]    [Noticia 2]    [Noticia 3]             |
|   [Noticia 4]    [Noticia 5]    [Noticia 6]             |
|                                                           |
+----------------------------------------------------------+
|                      FOOTER                               |
+----------------------------------------------------------+
```

### Layout Nuevo (Con Cambios)
```
+----------------------------------------------------------+
|                        HEADER                             |
|  Logo | Buscador | Acceder                               |
+----------------------------------------------------------+
| Inicio | [Menú Dinámico desde menu_items - solo activos] |
| Nacional | Política | Deportes | Cultura (configurable) |
+----------------------------------------------------------+
|                                                           |
|                SLIDER PRINCIPAL (si no hay filtro)        |
|                                                           |
+----------------------------------------------------------+
|  [★]         [⏰]         [▦]         [🖼]              |
| Destacadas | Última Hora | Categorías | Multimedia      |
+----------------------------------------------------------+
|                                                           |
|  CONTENIDO PRINCIPAL        |  ╔═══════════════════╗   |
|  (2/3 del ancho)            |  ║ ACCESOS RÁPIDOS   ║   |
|                             |  ║ (1/3 del ancho)   ║   |
|  ★ NOTICIAS DESTACADAS      |  ╠═══════════════════╣   |
|  (si no hay filtro)         |  ║ [★] Noticias      ║   |
|  [Noticia 1] [Noticia 2]    |  ║     Destacadas     ║   |
|                             |  ║     Las más...     ║   |
|  ⏰ ÚLTIMAS NOTICIAS /      |  ╠═══════════════════╣   |
|  📁 NOTICIAS DE [CAT]       |  ║ [⏰] Última Hora  ║   |
|  (según filtro)             |  ║     Lo más...     ║   |
|  [Noticia 1] [Noticia 2]    |  ║                   ║   |
|  [Noticia 3] [Noticia 4]    |  ╠═══════════════════╣   |
|  [Noticia 5] [Noticia 6]    |  ║ [▦] Categorías    ║   |
|                             |  ║     Explora...    ║   |
|                             |  ╚═══════════════════╝   |
|                             |  ╔═══════════════════╗   |
|                             |  ║ CATEGORÍAS        ║   |
|                             |  ║ 📁 Nacional       ║   |
|                             |  ║ 📁 Política       ║   |
|                             |  ║ 📁 Deportes       ║   |
|                             |  ║ 📁 Cultura        ║   |
|                             |  ╚═══════════════════╝   |
+-----------------------------+-------------------------+
|                      FOOTER                               |
+----------------------------------------------------------+
```

## Panel de Administración - Página de Inicio

### Vista Anterior (3 pestañas)
```
+----------------------------------------------------------+
| 🏠 Gestión de Página de Inicio                           |
+----------------------------------------------------------+
| [🖼 Slider] [▦ Accesos Directos] [✉ Contacto]           |
+----------------------------------------------------------+
|                                                           |
|  Contenido de la pestaña activa                          |
|                                                           |
+----------------------------------------------------------+
```

### Vista Nueva (5 pestañas)
```
+----------------------------------------------------------+
| 🏠 Gestión de Página de Inicio                           |
+----------------------------------------------------------+
| [🖼 Slider] [▦ Accesos] [📱 Laterales] [☰ Menú] [✉ Contacto] |
+----------------------------------------------------------+
|                                                           |
|  Contenido de la pestaña activa                          |
|                                                           |
+----------------------------------------------------------+
```

### Pestaña "Accesos Laterales" (Nueva)
```
+----------------------------------------------------------+
| 📱 Accesos Laterales                                      |
| Configura los 3 accesos directos del módulo lateral      |
+----------------------------------------------------------+
| ┌──────────────────────────────────────────────────────┐ |
| │ Título: [Noticias Destacadas         ]               │ |
| │ Subtítulo: [Las más importantes      ]               │ |
| │ Icono: [fas fa-star                  ]               │ |
| │ Imagen: [📁 Seleccionar archivo    ] (128x128px)     │ |
| │ URL: [index.php?destacadas=1         ]               │ |
| │ Orden: [1]  ☑ Activo                                 │ |
| │                                    [💾 Guardar]       │ |
| └──────────────────────────────────────────────────────┘ |
| ┌──────────────────────────────────────────────────────┐ |
| │ Título: [Última Hora                 ]               │ |
| │ ... (similar al anterior)                             │ |
| └──────────────────────────────────────────────────────┘ |
| ┌──────────────────────────────────────────────────────┐ |
| │ Título: [Categorías                  ]               │ |
| │ ... (similar al anterior)                             │ |
| └──────────────────────────────────────────────────────┘ |
+----------------------------------------------------------+
```

### Pestaña "Menú Principal" (Nueva)
```
+----------------------------------------------------------+
| ☰ Gestión de Menú Principal                              |
| Administra los ítems del menú superior                    |
+----------------------------------------------------------+
| [🔄 Sincronizar con Categorías]                          |
+----------------------------------------------------------+
| Categoría          | Orden    | Estado   | Acciones     |
+----------------------------------------------------------+
| Nacional           | [1] [💾] | ✅ Activo | [⚡ Desact.] |
| Política           | [2] [💾] | ✅ Activo | [⚡ Desact.] |
| Economía           | [3] [💾] | ⚫ Inact. | [⚡ Activar] |
| Seguridad          | [4] [💾] | ✅ Activo | [⚡ Desact.] |
| Cultura            | [5] [💾] | ✅ Activo | [⚡ Desact.] |
| Deportes           | [6] [💾] | ⚫ Inact. | [⚡ Activar] |
+----------------------------------------------------------+
```

## Flujo de Navegación por Categoría

### 1. Usuario en Página Principal
```
Usuario ve:
┌─────────────────────────────────────┐
│ Inicio | Nacional | Política | ...  │  ← Menú dinámico
└─────────────────────────────────────┘
```

### 2. Usuario hace clic en "Política"
```
URL cambia a: index.php?categoria=2
```

### 3. Página se recarga mostrando:
```
┌─────────────────────────────────────┐
│ Inicio | Nacional | [Política] | ... │  ← "Política" resaltado
└─────────────────────────────────────┘

Contenido muestra:
📁 Noticias de Política              ← Título dinámico
  Solo noticias de categoría "Política"
  
[Noticia Política 1]
[Noticia Política 2]
[Noticia Política 3]
...
```

## Flujo de Configuración del Sistema

### Paso 1: Sincronizar Menú
```
Admin → Página de Inicio → Menú Principal
        ↓
    [🔄 Sincronizar]
        ↓
Sistema crea ítems para todas las categorías principales
        ↓
    Todos activos por defecto
```

### Paso 2: Personalizar Menú
```
Admin selecciona qué mostrar:
✅ Nacional   → Aparece en menú público
✅ Política   → Aparece en menú público
⚫ Economía   → NO aparece (desactivado)
✅ Deportes   → Aparece en menú público
```

### Paso 3: Configurar Accesos Laterales
```
Admin → Página de Inicio → Accesos Laterales
        ↓
Configura 3 accesos:
1. Noticias Destacadas → index.php?destacadas=1
2. Última Hora → index.php?recientes=1
3. Categorías → #categorias
        ↓
    [💾 Guardar cada uno]
```

## Responsive Design

### Desktop (> 1024px)
```
┌─────────────────────────────────────────────┐
│  CONTENIDO (66%)    │  SIDEBAR (33%)        │
│  [Noticias]         │  [Accesos Rápidos]    │
│  [Lista]            │  [Categorías]         │
└─────────────────────────────────────────────┘
```

### Tablet (768px - 1024px)
```
┌─────────────────────────────────────────────┐
│  CONTENIDO (100%)                           │
│  [Noticias en 2 columnas]                   │
├─────────────────────────────────────────────┤
│  SIDEBAR (100%)                             │
│  [Accesos Rápidos]                          │
│  [Categorías]                               │
└─────────────────────────────────────────────┘
```

### Móvil (< 768px)
```
┌───────────────────┐
│  CONTENIDO        │
│  [Noticia]        │
│  [Noticia]        │
├───────────────────┤
│  SIDEBAR          │
│  [Accesos]        │
│  [Categorías]     │
└───────────────────┘
```

## Diferencias Clave

### Antes vs Después

| Característica | Antes | Después |
|---------------|-------|---------|
| Menú Superior | Primeras 6 categorías fijas | Dinámico, configurable |
| Filtrado | No funcionaba | Funciona correctamente |
| Accesos | Solo 4 en home | 4 en home + 3 lateral |
| Layout | Una columna | Dos columnas (contenido + sidebar) |
| Gestión | No había control | Control total desde admin |
| Categorías | Siempre visibles todas | Solo activas aparecen |
| Responsive | Básico | Mejorado con sidebar |

## Estados del Sistema

### Estado 1: Sin Categoría Seleccionada (Home)
- ✅ Slider visible
- ✅ Accesos directos superiores (4 items)
- ✅ Noticias destacadas
- ✅ Últimas noticias (6 items)
- ✅ Módulo lateral
- ✅ Sección de contacto

### Estado 2: Categoría Seleccionada
- ❌ Slider oculto
- ❌ Accesos directos superiores ocultos
- ❌ Noticias destacadas ocultas
- ✅ Título "Noticias de [Categoría]"
- ✅ Noticias filtradas de la categoría
- ✅ Módulo lateral
- ❌ Sección de contacto oculta

## Iconografía Utilizada

- 🏠 Home / Inicio
- ⭐ / ★ Destacadas
- ⏰ / ⏱️ Última Hora
- 📁 / 📂 Categorías
- 🖼️ / 🎨 Multimedia
- ✉️ / 📧 Contacto
- ☰ Menú
- 🔄 Sincronizar
- ⚡ Activar/Desactivar
- 💾 Guardar
- 👁️ Visitas
- 📅 Fecha
