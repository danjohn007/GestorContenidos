# Actualización: Sistema de Banners Publicitarios y Mejoras de UI

## Fecha: 2026-01-02

## Cambios Implementados

### 1. Banners Publicitarios Verticales (Sidebar)

Se ha agregado un nuevo módulo lateral que gestiona banners publicitarios verticales en la barra lateral del sitio público.

**Características:**
- Ubicación: Barra lateral derecha de la página principal
- Gestión desde: Menú "Gestión de Página de Inicio" → Pestaña "Banners Verticales"
- Cantidad: 3 espacios configurables
- Tamaño recomendado: 300x600px
- Formatos soportados: JPG, PNG, GIF, WEBP
- Cada banner incluye: imagen, URL de destino, orden y estado activo/inactivo

**Cómo usar:**
1. Acceder a la sección "Gestión de Página de Inicio" desde el panel de administración
2. Seleccionar la pestaña "Banners Verticales"
3. Subir la imagen del banner (recomendado 300x600px)
4. Ingresar la URL de destino
5. Activar el banner con el checkbox
6. Guardar cambios

### 2. Menú Superior Sticky y Hamburguesa Móvil

El menú de navegación superior ahora es sticky en dispositivos de escritorio y se convierte en un menú hamburguesa con overlay en dispositivos móviles.

**Características Desktop:**
- Menú fijo en la parte superior al hacer scroll (sticky)
- Siempre visible mientras navegas
- Transición suave con sombra

**Características Móvil:**
- Botón hamburguesa en la esquina superior derecha
- Menú lateral deslizante desde la derecha
- Overlay semi-transparente sobre el contenido
- Incluye todos los items del menú principal
- Opciones adicionales: Buscar y Acceder

**Funcionamiento:**
- Clic en el botón hamburguesa para abrir/cerrar
- Clic en el overlay para cerrar
- Se adapta automáticamente al tamaño de pantalla
- Breakpoint: 768px (tablets y móviles)

### 3. Grid de Anuncios Publicitarios en Footer

Se ha agregado un grid de 3-4 espacios para anuncios publicitarios antes del footer.

**Características:**
- Ubicación: Justo antes del footer de la página principal
- Gestión desde: Menú "Gestión de Página de Inicio" → Pestaña "Anuncios Footer"
- Cantidad: 4 espacios configurables
- Layout: Grid de 2 columnas en móvil, 4 columnas en desktop
- Tamaño recomendado: 300x250px
- Formatos soportados: JPG, PNG, GIF, WEBP

**Cómo usar:**
1. Acceder a "Gestión de Página de Inicio"
2. Seleccionar pestaña "Anuncios Footer"
3. Subir imágenes de los anuncios (300x250px recomendado)
4. Configurar URLs de destino
5. Activar los anuncios que deseas mostrar
6. Guardar cambios

### 4. Imágenes de Noticias Clickeables

Las imágenes destacadas de las noticias ahora son completamente clickeables y redirigen al detalle de la noticia.

**Cambios:**
- Imágenes de noticias destacadas: clickeables
- Imágenes de noticias recientes: clickeables
- Efecto hover: ligera transparencia (90%)
- Cursor: pointer para indicar que es clickeable
- No afecta el comportamiento del título y botón "Leer más"

### 5. Banners Publicitarios Entre Secciones

Se han agregado banners publicitarios horizontales que aparecen entre las diferentes secciones de la página principal.

**Características:**
- Ubicación: Entre "Noticias Destacadas" y "Últimas Noticias"
- Gestión desde: "Gestión de Página de Inicio" → Pestaña "Banners Intermedios"
- Cantidad: 3 espacios configurables
- Tamaño recomendado: 1200x200px (ancho completo)
- Formato responsive: se adapta al ancho del contenedor
- Formatos soportados: JPG, PNG, GIF, WEBP

**Cómo usar:**
1. Acceder a "Gestión de Página de Inicio"
2. Seleccionar pestaña "Banners Intermedios"
3. Subir imagen del banner (1200x200px recomendado)
4. Configurar URL de destino
5. Definir orden de aparición
6. Activar el banner
7. Guardar cambios

### 6. Corrección de Accesos Rápidos (Sticky Overlap)

Se ha corregido el problema del módulo lateral de accesos rápidos que se emparejaba con el contenido al hacer scroll.

**Mejoras:**
- Comportamiento sticky limitado con `max-height`
- No se superpone con el footer
- Scroll interno si el contenido es muy largo
- Top offset ajustado: 80px desde la parte superior
- Contenedor wrapper `sidebar-sticky` para mejor control

**CSS aplicado:**
```css
.sidebar-sticky {
    position: sticky;
    top: 80px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}
```

## Instrucciones de Instalación

### 1. Aplicar Actualización de Base de Datos

Ejecutar el siguiente script SQL en tu base de datos:

```bash
mysql -u [usuario] -p [nombre_base_datos] < database_banners_update.sql
```

O ejecutar manualmente desde phpMyAdmin, MySQL Workbench, o tu cliente de base de datos preferido.

### 2. Verificar Archivos Actualizados

Los siguientes archivos han sido modificados:
- `index.php` - Página pública principal
- `pagina_inicio.php` - Panel de administración de página de inicio
- `database_banners_update.sql` - Script de actualización de base de datos (NUEVO)

### 3. Configurar Permisos de Directorio

Asegurarse de que el directorio de uploads tenga permisos de escritura:

```bash
chmod 755 public/uploads/homepage/
```

### 4. Verificar Funcionamiento

1. Acceder al panel de administración
2. Ir a "Gestión de Página de Inicio"
3. Verificar que aparezcan las nuevas pestañas:
   - Banners Verticales
   - Banners Intermedios
   - Anuncios Footer
4. Subir imágenes de prueba en cada sección
5. Activar los elementos
6. Revisar el sitio público para verificar que todo funciona correctamente

## Estructura de la Base de Datos

### Nuevas Secciones en `pagina_inicio`

El script SQL agrega los siguientes registros por defecto:

**banner_vertical:**
- 3 espacios configurables
- `seccion = 'banner_vertical'`
- `orden` de 1 a 3

**banner_intermedio:**
- 3 espacios configurables
- `seccion = 'banner_intermedio'`
- `orden` de 1 a 3

**anuncio_footer:**
- 4 espacios configurables
- `seccion = 'anuncio_footer'`
- `orden` de 1 a 4

Cada registro incluye los campos:
- `id`: Identificador único
- `seccion`: Tipo de banner/anuncio
- `titulo`: Título descriptivo
- `subtitulo`: Subtítulo (opcional)
- `contenido`: Contenido adicional (opcional)
- `imagen`: Ruta de la imagen
- `url`: URL de destino
- `orden`: Orden de visualización
- `activo`: Estado (0 = inactivo, 1 = activo)

## Tamaños Recomendados de Imágenes

| Tipo de Banner | Tamaño Recomendado | Ubicación |
|----------------|-------------------|-----------|
| Banner Vertical | 300x600px | Sidebar derecho |
| Banner Intermedio | 1200x200px | Entre secciones |
| Anuncio Footer | 300x250px | Grid antes del footer |

## Notas de Compatibilidad

- **Navegadores:** Chrome, Firefox, Safari, Edge (últimas versiones)
- **Dispositivos móviles:** Responsive design completo
- **Breakpoint móvil:** 768px
- **PHP:** 7.4 o superior
- **MySQL:** 5.7 o superior

## Funcionalidad Preservada

La actualización NO afecta:
- Sistema de noticias existente
- Categorías y taxonomías
- Usuarios y permisos
- Slider principal
- Accesos directos originales
- Accesos laterales originales
- Sección de contacto
- Footer
- Cualquier otra funcionalidad existente

## Soporte y Problemas

Si encuentras algún problema después de la actualización:

1. Verificar que el script SQL se ejecutó correctamente
2. Revisar permisos de directorios
3. Limpiar caché del navegador
4. Verificar logs de errores PHP
5. Consultar la consola del navegador para errores JavaScript

## Capturas de Pantalla

Las capturas de pantalla de los cambios se encuentran disponibles en:
- Menú móvil hamburguesa
- Banners verticales en sidebar
- Grid de anuncios footer
- Banners intermedios entre secciones
