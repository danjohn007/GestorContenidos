# Guía Visual: Correcciones de Categorías y Slider

## 1. Corrección de Actualización de Categoría Padre

### Flujo Anterior (Con Error)

```
Usuario en formulario de edición
        ↓
Selecciona "Ninguna (Categoría principal)"
        ↓
Formulario envía: padre_id = "" (string vacío)
        ↓
categoria_editar.php procesa:
  $padre_id = !empty($_POST['padre_id']) ? (int)$_POST['padre_id'] : null;
  // Resultado: $padre_id = null ✓
        ↓
Se crea array: $data = ['padre_id' => null]
        ↓
Categoria->update($id, $data) ejecuta:
  if (isset($data['padre_id'])) {  // ❌ PROBLEMA: isset(null) = false
    $fields[] = "padre_id = :padre_id";
  }
        ↓
El campo padre_id NO se incluye en la actualización
        ↓
❌ La categoría NO se actualiza
```

### Flujo Corregido

```
Usuario en formulario de edición
        ↓
Selecciona "Ninguna (Categoría principal)"
        ↓
Formulario envía: padre_id = "" (string vacío)
        ↓
categoria_editar.php procesa:
  $padre_id = !empty($_POST['padre_id']) ? (int)$_POST['padre_id'] : null;
  // Resultado: $padre_id = null ✓
        ↓
Se crea array: $data = ['padre_id' => null]
        ↓
Categoria->update($id, $data) ejecuta:
  if (array_key_exists('padre_id', $data)) {  // ✅ Retorna true
    $fields[] = "padre_id = :padre_id";
    $params['padre_id'] = null;
  }
        ↓
Query SQL: UPDATE categorias SET padre_id = NULL WHERE id = ?
        ↓
✅ La categoría se actualiza correctamente
        ↓
La subcategoría se convierte en categoría principal
```

### Ejemplo Visual de Categorías

```
Antes de la corrección:

Deportes (padre_id: NULL)
├── Fútbol (padre_id: 1)
│   └── Liga MX (padre_id: 2)  ← Queremos convertir en principal
├── Béisbol (padre_id: 1)
└── Basketball (padre_id: 1)

Intentamos: Liga MX → padre_id = NULL
❌ Error: No se puede actualizar

---

Después de la corrección:

Deportes (padre_id: NULL)
├── Fútbol (padre_id: 1)
├── Béisbol (padre_id: 1)
└── Basketball (padre_id: 1)

Liga MX (padre_id: NULL)  ← ✅ Ahora es categoría principal
```

## 2. Corrección del Slider de Noticias Destacadas

### Layout Desktop: 4 Columnas

```
┌─────────────────────────────────────────────────────────────────┐
│                  NOTICIAS DESTACADAS (Solo Imagen)              │
├───────────────┬───────────────┬───────────────┬─────────────────┤
│               │               │               │                 │
│   Imagen 1    │   Imagen 2    │   Imagen 3    │   Imagen 4      │
│               │               │               │                 │
│   240x192     │   240x192     │   240x192     │   240x192       │
│               │               │               │                 │
└───────────────┴───────────────┴───────────────┴─────────────────┘

Cuando hay ≤ 4 imágenes: Sin controles de navegación
```

### Layout con Navegación (> 4 imágenes)

```
         ┌─────────────────────────────────────────────────────┐
         │        NOTICIAS DESTACADAS (Solo Imagen)            │
         ├─────────────────────────────────────────────────────┤
         │                                                     │
    [◀]  │  [Img 1]  [Img 2]  [Img 3]  [Img 4]                │  [▶]
         │                                                     │
         ├─────────────────────────────────────────────────────┤
         │              ● ○ ○                                  │
         │         (Página 1 de 3)                             │
         └─────────────────────────────────────────────────────┘

Ejemplo con 10 imágenes:
- Página 1: Imágenes 1-4
- Página 2: Imágenes 5-8  
- Página 3: Imágenes 9-10

Navegación:
- Botones ◀ ▶ : Prev / Next
- Indicadores ● ○ ○ : Clic directo a página
```

### Layout Mobile: 2 Columnas

```
┌─────────────────────────────┐
│   NOTICIAS DESTACADAS       │
├──────────────┬──────────────┤
│              │              │
│   Imagen 1   │   Imagen 2   │
│              │              │
│   160x192    │   160x192    │
│              │              │
├──────────────┼──────────────┤
│              │              │
│   Imagen 3   │   Imagen 4   │
│              │              │
│   160x192    │   160x192    │
│              │              │
└──────────────┴──────────────┘
         ● ○
```

### Comportamiento Automático

```
displayNoticiasDestacadasGrid($noticias)
        ↓
   ¿Cuántas imágenes?
        ↓
     ┌──┴──┐
     │     │
   ≤ 4   > 4
     │     │
     ↓     ↓
  Grid   Carousel
  Simple  con Nav.
     │     │
     └──┬──┘
        ↓
    Renderizar
```

### Código HTML Generado

#### Con ≤ 4 imágenes:

```html
<div class="noticias-destacadas-grid my-8">
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="noticia-destacada-item">
      <a href="/destino">
        <img src="/imagen1.jpg" class="w-full h-48 object-cover">
      </a>
    </div>
    <!-- ... 3 imágenes más ... -->
  </div>
</div>
```

#### Con > 4 imágenes:

```html
<div class="noticias-destacadas-carousel my-8" id="carousel-123">
  <div class="relative overflow-hidden">
    <!-- Página 1 -->
    <div class="carousel-page opacity-100 block" data-page="0">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="noticia-destacada-item">...</div>
        <!-- ... 4 imágenes ... -->
      </div>
    </div>
    
    <!-- Página 2 -->
    <div class="carousel-page opacity-0 hidden" data-page="1">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="noticia-destacada-item">...</div>
        <!-- ... 4 imágenes más ... -->
      </div>
    </div>
    
    <!-- Controles -->
    <button onclick="changeDestacadaCarouselPage('carousel-123', -1)">
      <i class="fas fa-chevron-left"></i>
    </button>
    <button onclick="changeDestacadaCarouselPage('carousel-123', 1)">
      <i class="fas fa-chevron-right"></i>
    </button>
    
    <!-- Indicadores -->
    <div class="flex justify-center mt-4 space-x-2">
      <button class="w-3 h-3 rounded-full bg-blue-600"></button>
      <button class="w-3 h-3 rounded-full bg-gray-300"></button>
    </div>
  </div>
</div>
```

## Ubicaciones en el Sitio Público

```
┌─────────────────────────────────────────────────────┐
│                    HEADER                           │
├─────────────────────────────────────────────────────┤
│                 SLIDER PRINCIPAL                    │
├─────────────────────────────────────────────────────┤
│                                                     │
│   [NOTICIAS DESTACADAS - bajo_slider]  ← Ubicación 1│
│   [Img] [Img] [Img] [Img]                          │
│                                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│   NOTICIAS DESTACADAS (Contenido)                  │
│                                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│   [NOTICIAS DESTACADAS - entre_bloques] ← Ubicación 2│
│   [Img] [Img] [Img] [Img]                          │
│                                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│   ÚLTIMAS NOTICIAS                                  │
│                                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│   [NOTICIAS DESTACADAS - antes_footer] ← Ubicación 3│
│   [Img] [Img] [Img] [Img]                          │
│                                                     │
├─────────────────────────────────────────────────────┤
│                    FOOTER                           │
└─────────────────────────────────────────────────────┘
```

## Comparación: Antes vs Después

### Antes de las Correcciones

```
CATEGORÍAS:
❌ No se puede cambiar padre_id a NULL
❌ Error al convertir subcategoría en principal
❌ Bloqueo en la gestión de categorías

SLIDER:
❌ Todas las imágenes visibles a la vez
❌ Sin control de navegación
❌ Páginas muy largas con muchas imágenes
❌ Layout inconsistente (3 columnas → 4 columnas)
```

### Después de las Correcciones

```
CATEGORÍAS:
✅ Se puede cambiar padre_id a NULL
✅ Conversión fluida de subcategoría a principal
✅ Gestión de categorías sin restricciones incorrectas

SLIDER:
✅ Máximo 4 imágenes visibles por página
✅ Controles prev/next automáticos
✅ Páginas limpias y organizadas
✅ Layout consistente (4 columnas en desktop)
✅ Indicadores de página
✅ Navegación intuitiva
```

## Casos de Uso Resueltos

### Caso 1: Editor de Contenido

```
Escenario: Reorganizar estructura de categorías

1. El editor tiene: Deportes > Fútbol > Liga MX
2. Decide que "Liga MX" debe ser categoría principal
3. Edita "Liga MX"
4. Cambia "Categoría Padre" a "Ninguna"
5. Guarda cambios

Antes: ❌ Error "No se puede actualizar"
Ahora:  ✅ Se actualiza correctamente
```

### Caso 2: Administrador de Sitio

```
Escenario: Mostrar promociones especiales

1. Crea 8 imágenes destacadas para promociones
2. Las configura en ubicación "bajo_slider"
3. Publica cambios
4. Visitantes ven en el sitio:
   - Página 1: 4 primeras promociones
   - Botones < > para navegar
   - Página 2: 4 promociones restantes

Antes: ❌ Las 8 imágenes apiladas verticalmente
Ahora:  ✅ 4 imágenes por página con navegación
```

## Notas Técnicas

### isset() vs array_key_exists()

```php
$data = ['key' => null];

// isset() - Problema
isset($data['key']);           // false ❌
isset($data['nonexistent']);   // false

// array_key_exists() - Solución
array_key_exists('key', $data);           // true ✅
array_key_exists('nonexistent', $data);   // false
```

### Responsive Breakpoints

```css
/* Tailwind CSS Grid Clases */

grid-cols-2      /* Mobile: 2 columnas (siempre) */
md:grid-cols-4   /* Desktop: 4 columnas (≥768px) */

/* Antes usaba: */
grid-cols-2 md:grid-cols-3 lg:grid-cols-4
/* 2 → 3 → 4 columnas (inconsistente) */

/* Ahora usa: */
grid-cols-2 md:grid-cols-4
/* 2 → 4 columnas (directo y claro) */
```

## Impacto en la Base de Datos

### Categorías

```sql
-- Antes (con error):
-- No se ejecuta esta query:
UPDATE categorias SET padre_id = NULL WHERE id = 14;

-- Después (corregido):
-- Se ejecuta correctamente:
UPDATE categorias SET padre_id = NULL WHERE id = 14;
-- La subcategoría se convierte en categoría principal ✓
```

### Noticias Destacadas

```
Tabla: noticias_destacadas_imagenes

No hay cambios en la estructura
Solo mejora en la visualización frontend
Los datos existentes funcionan automáticamente con la nueva lógica
```

---

**Resumen**: Todas las correcciones mantienen compatibilidad total con el sistema existente mientras resuelven los problemas reportados de forma elegante y eficiente.
