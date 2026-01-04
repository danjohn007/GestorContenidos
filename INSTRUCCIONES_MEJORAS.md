# Instrucciones de Instalación - Nuevas Mejoras

## Actualizaciones Implementadas

Este documento describe las nuevas funcionalidades agregadas al sistema:

1. **Animaciones AOS (Animate On Scroll)** - Efectos visuales al hacer scroll
2. **Soporte para Favicon** - Configuración del ícono del sitio web
3. **Configuración de Tamaño de Banners** - Control preciso del tamaño de visualización de banners

---

## 🎬 1. Animaciones AOS

### Descripción
Se han agregado animaciones suaves que se activan cuando los elementos entran en la vista al hacer scroll. Esto mejora la experiencia visual del usuario.

### Elementos Animados
- **Portal Público (index.php)**:
  - Noticias destacadas (fade-up con delay)
  - Noticias recientes (fade-up)
  - Sección lateral de accesos rápidos (fade-left)
  
- **Panel Administrativo (main.php)**:
  - Todas las páginas tienen animaciones sutiles al cargar

### Configuración AOS
Las animaciones se inicializan automáticamente con los siguientes parámetros:
- **Duración**: 800ms (portal público), 600ms (panel admin)
- **Easing**: ease-in-out
- **Once**: true (la animación ocurre solo una vez)
- **Offset**: 100px (portal público), 50px (panel admin)

### Personalización
Para agregar animaciones a nuevos elementos, usa los atributos:
```html
<!-- Animación básica -->
<div data-aos="fade-up">Contenido</div>

<!-- Con retraso -->
<div data-aos="fade-up" data-aos-delay="100">Contenido</div>

<!-- Opciones de animación disponibles -->
data-aos="fade-up"      <!-- Aparece desde abajo -->
data-aos="fade-left"    <!-- Aparece desde la izquierda -->
data-aos="fade-right"   <!-- Aparece desde la derecha -->
data-aos="fade-down"    <!-- Aparece desde arriba -->
data-aos="zoom-in"      <!-- Efecto de zoom -->
```

---

## 🎨 2. Favicon del Sitio

### Descripción
Ahora puedes configurar el favicon (ícono) que aparece en la pestaña del navegador para tu sitio web.

### Cómo Configurar el Favicon

1. **Accede a la Configuración del Sitio**:
   - Panel Admin → Configuración → Datos del Sitio
   - URL: `/configuracion_sitio.php`

2. **Sección "Favicon del Sitio"**:
   - Verás el favicon actual (si existe)
   - Usa el campo "Cargar Favicon" para seleccionar tu nuevo archivo

3. **Formatos Soportados**:
   - `.ico` (formato clásico de favicon)
   - `.png` (recomendado para mejor calidad)
   - `.jpg` / `.jpeg`
   - `.svg` (vectorial, ideal para escalabilidad)

4. **Tamaño Recomendado**:
   - 32x32 píxeles o 16x16 píxeles
   - Para mejores resultados, usa imágenes cuadradas

5. **Guarda los Cambios**:
   - Haz clic en "Guardar Cambios"
   - El favicon se mostrará automáticamente en:
     - Portal público
     - Panel administrativo
     - Todas las páginas del sitio

### Ubicación del Archivo
Los favicons se guardan en: `/public/uploads/config/favicon_[timestamp].[ext]`

### Verificación
- Abre una página del sitio en modo incógnito
- Verifica que el ícono aparezca en la pestaña del navegador
- Puede tomar unos segundos para que el navegador actualice el caché

---

## 📐 3. Configuración de Tamaño de Banners

### Descripción
Control preciso sobre cómo se visualizan los banners en el portal, sin reescalados automáticos.

### Actualización de Base de Datos

**⚠️ IMPORTANTE**: Debes ejecutar la migración SQL antes de usar esta funcionalidad.

#### Opción 1: Ejecución Manual
1. Accede a phpMyAdmin o tu gestor de base de datos
2. Selecciona la base de datos `gestor_contenidos` (o el nombre de tu BD)
3. Ve a la pestaña "SQL"
4. Copia y ejecuta el contenido del archivo: `database_banner_size.sql`

#### Opción 2: Desde la Terminal
```bash
mysql -u usuario -p gestor_contenidos < database_banner_size.sql
```

### Opciones de Tamaño Disponibles

1. **Automático (responsive)** - *Default*
   - Banner se adapta al ancho disponible
   - Mantiene proporciones originales
   - Ideal para diseño responsive

2. **Banner horizontal (1200×400)**
   - Formato panorámico
   - Ideal para banners de encabezado
   - Dimensión fija: máx. 1200px ancho × 400px alto

3. **Banner cuadrado (600×600)**
   - Formato 1:1
   - Perfecto para anuncios cuadrados
   - Dimensión fija: máx. 600px × 600px

4. **Banner vertical / sidebar (300×600)**
   - Formato vertical
   - Ideal para sidebar lateral
   - Dimensión fija: máx. 300px ancho × 600px alto

5. **Tamaño real de la imagen (sin escalar)**
   - Muestra la imagen en su tamaño original
   - Sin reescalado
   - El banner no será responsive

### Cómo Configurar el Tamaño de un Banner

#### Al Crear un Banner Nuevo
1. Ve a: Panel Admin → Banners → Crear Nuevo Banner
2. Completa los campos requeridos (nombre, ubicación, imagen, etc.)
3. En el campo **"Tamaño de Visualización"**:
   - Selecciona el tamaño deseado del menú desplegable
   - Por defecto está en "Automático (responsive)"
4. Guarda el banner

#### Al Editar un Banner Existente
1. Ve a: Panel Admin → Banners
2. Haz clic en "Editar" en el banner que deseas modificar
3. Busca el campo **"Tamaño de Visualización"**
4. Cambia al tamaño deseado
5. Guarda los cambios

### Recomendaciones

- **Para banners de encabezado**: Usa "Banner horizontal (1200×400)"
- **Para sidebar derecho**: Usa "Banner vertical / sidebar (300×600)"
- **Para anuncios en grid**: Usa "Banner cuadrado (600×600)"
- **Para banners de footer**: Usa "Automático (responsive)"
- **Para logotipos o imágenes específicas**: Usa "Tamaño real de la imagen"

### Comportamiento Visual

El sistema aplica automáticamente:
- CSS `max-width` y `max-height` según el tamaño seleccionado
- Clase `object-cover` para mantener proporciones sin deformar
- Clase `object-contain` para tamaño real sin recortar

### Verificación
1. Crea o edita un banner con un tamaño específico
2. Visita el portal público donde se muestra el banner
3. Verifica que el banner se muestre en el tamaño configurado

---

## 🧪 Pruebas Recomendadas

### Prueba 1: Animaciones AOS
1. Visita el portal público: `index.php`
2. Haz scroll lentamente hacia abajo
3. Observa las animaciones en las noticias y secciones

### Prueba 2: Favicon
1. Sube un favicon desde la configuración del sitio
2. Abre el portal en una nueva pestaña de incógnito
3. Verifica que el ícono aparezca en la pestaña del navegador

### Prueba 3: Tamaños de Banner
1. Ejecuta la migración SQL `database_banner_size.sql`
2. Crea 3 banners con diferentes tamaños:
   - Uno horizontal (1200×400)
   - Uno cuadrado (600×600)
   - Uno vertical (300×600)
3. Visita el portal y verifica que se muestren en los tamaños correctos

---

## 📋 Checklist de Implementación

- [ ] Verificar que las librerías AOS se carguen correctamente
- [ ] Probar animaciones en el portal público
- [ ] Subir y verificar el favicon
- [ ] Ejecutar la migración SQL `database_banner_size.sql`
- [ ] Crear banners de prueba con diferentes tamaños
- [ ] Verificar visualización de banners en el portal
- [ ] Probar responsive en diferentes dispositivos
- [ ] Limpiar caché del navegador si es necesario

---

## 🐛 Solución de Problemas

### Animaciones AOS no funcionan
- Verifica que la librería se cargue en la consola del navegador
- Comprueba que no haya errores JavaScript en la consola
- Asegúrate de que `AOS.init()` se llame después de cargar la librería

### Favicon no se muestra
- Limpia el caché del navegador (Ctrl+Shift+Delete)
- Verifica la ruta del archivo en el código fuente de la página
- Asegúrate de que el archivo se haya subido correctamente a `/public/uploads/config/`

### Error en migración de banners
- Si obtienes error "column already exists", la migración ya se ejecutó
- Verifica que tienes permisos para modificar la base de datos
- Revisa los logs de MySQL para más detalles

### Banner no respeta el tamaño configurado
- Verifica que la columna `tamano_display` exista en la tabla `banners`
- Limpia el caché del navegador
- Revisa el código fuente HTML y verifica los estilos aplicados

---

## 📞 Soporte

Si encuentras algún problema durante la implementación:
1. Revisa los logs del sistema
2. Verifica la consola del navegador (F12)
3. Consulta la documentación completa en el repositorio

---

**Fecha de última actualización**: 2026-01-04
**Versión del sistema**: 1.0.0
