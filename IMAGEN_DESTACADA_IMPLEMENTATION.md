# Implementación de Noticias Destacadas (Solo Imagen)

## Resumen
Esta implementación cumple con el requerimiento de mostrar noticias destacadas con **4 columnas horizontales en desktop**, mostrando **únicamente la vista previa de la imagen**, y con **controles de navegación prev/next** cuando hay más de 4 imágenes.

## Características Implementadas

### 1. Display en 4 Columnas
- **Desktop**: 4 columnas horizontales (`md:grid-cols-4`)
- **Mobile**: 2 columnas adaptativas (`grid-cols-2`)
- Solo muestra la imagen de vista previa (altura fija de 48 unidades con `h-48 object-cover`)

### 2. Navegación con Controles
Cuando hay **más de 4 imágenes**:
- Botones **Prev/Next** en los laterales
- Indicadores de página en la parte inferior
- Transición suave entre páginas
- Cada página muestra exactamente 4 imágenes

### 3. Ubicaciones Disponibles
Las noticias destacadas pueden mostrarse en 3 ubicaciones diferentes:
- `bajo_slider`: Debajo del slider principal
- `entre_bloques`: Entre bloques de contenido en la página de inicio
- `antes_footer`: Antes del footer

### 4. Tipos de Vista
- **Grid**: Muestra 4 columnas sin controles (cuando hay 4 o menos imágenes)
- **Carousel**: Muestra 4 columnas con controles de navegación (cuando hay más de 4 imágenes)

## Archivos Principales

### 1. Modelo (`app/models/NoticiaDestacadaImagen.php`)
- Maneja todas las operaciones CRUD
- Gestiona ubicaciones y tipos de vista
- Filtra por fechas de vigencia

### 2. Helper (`app/helpers/noticia_destacada_helper.php`)
- `displayNoticiasDestacadasImagenes($ubicacion, $cssClass)`: Función principal para mostrar
- `displayNoticiasDestacadasGrid($noticias, $cssClass)`: Display en grid (4 o menos)
- `displayNoticiasDestacadasCarousel($noticias, $cssClass)`: Display con navegación (más de 4)

### 3. Administración
- `noticia_destacada_crear.php`: Formulario de creación
- `noticia_destacada_editar.php`: Formulario de edición
- `noticias_destacadas.php`: Listado y gestión
- `noticia_destacada_accion.php`: Acciones (toggle, eliminar)

### 4. Base de Datos
- Tabla: `noticias_destacadas_imagenes`
- Script SQL: `database_noticias_destacadas_imagenes.sql`

## Mejoras Implementadas

### 1. Manejo de Errores Mejorado
Se agregó manejo detallado de errores de subida de archivos:
- Errores de tamaño de archivo
- Errores de permisos
- Errores de formato
- Mensajes de error descriptivos en español

### 2. Validación de Imágenes
- Formatos permitidos: JPG, JPEG, PNG, GIF, WebP
- Creación automática del directorio de uploads
- Validación de extensiones
- Nombres únicos para evitar colisiones

### 3. Seguridad
- Validación de paths al eliminar imágenes
- Sanitización de parámetros
- Control de permisos de usuario
- Auditoría de acciones

## Uso en el Frontend

### Mostrar en Index.php
```php
// Incluir el helper
require_once __DIR__ . '/app/helpers/noticia_destacada_helper.php';

// Mostrar en la ubicación deseada
displayNoticiasDestacadasImagenes('bajo_slider');
displayNoticiasDestacadasImagenes('entre_bloques');
displayNoticiasDestacadasImagenes('antes_footer');
```

### Personalización con CSS
```php
// Agregar clases CSS personalizadas
displayNoticiasDestacadasImagenes('bajo_slider', 'mi-clase-personalizada');
```

## Flujo de Trabajo

### Crear Noticia Destacada
1. Ir a "Noticias Destacadas (Solo Imágenes)"
2. Clic en "Nueva Destacada"
3. Completar formulario:
   - Título (para administración)
   - Seleccionar noticia existente O subir imagen manual
   - URL de destino (opcional si se seleccionó noticia)
   - Ubicación
   - Tipo de vista
   - Orden
   - Fechas de vigencia (opcional)
4. Activar/Desactivar
5. Guardar

### Editar/Gestionar
- Toggle activo/inactivo desde el listado
- Editar cualquier campo
- Eliminar (también elimina imagen si no es de noticia)

## Requisitos Técnicos

### Base de Datos
```bash
# Ejecutar antes de usar
mysql -u usuario -p base_datos < database_noticias_destacadas_imagenes.sql
```

### Permisos de Directorio
```bash
# Asegurar permisos de escritura
chmod 755 public/uploads/destacadas/
```

### Dependencias Frontend
- TailwindCSS (para grid y estilos)
- Font Awesome (para iconos)
- JavaScript vanilla (para navegación del carousel)

## Estructura de Navegación del Carousel

### JavaScript
El carousel utiliza JavaScript vanilla sin dependencias externas:
- `changeDestacadaCarouselPage(carouselId, direction)`: Navega entre páginas
- `goToDestacadaCarouselPage(carouselId, index)`: Va a una página específica
- Estado mantenido en objeto `destacadaCarouselPages`

### Controles
- **Botones Prev/Next**: Círculos blancos semi-transparentes en los laterales
- **Indicadores**: Puntos en la parte inferior
- **Transiciones**: Opacidad suave entre páginas

## Compatibilidad
- PHP 7.4+
- MySQL 5.7+
- Navegadores modernos (Chrome, Firefox, Safari, Edge)
- Responsive: Desktop, Tablet, Mobile

## Pruebas Realizadas
- ✅ Creación con imagen manual
- ✅ Creación desde noticia existente
- ✅ Edición y actualización
- ✅ Toggle activo/inactivo
- ✅ Eliminación
- ✅ Display en 4 columnas
- ✅ Navegación con más de 4 imágenes
- ✅ Responsive en mobile
- ✅ Validación de errores

## Notas Importantes
1. Las imágenes solo se eliminan automáticamente si NO provienen de una noticia
2. Las fechas de vigencia son opcionales pero útiles para campañas temporales
3. El orden determina la secuencia de visualización
4. Cada ubicación puede tener múltiples noticias destacadas
5. El tipo de vista (grid/carousel) se aplica por grupo de ubicación
