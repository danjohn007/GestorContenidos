# Módulo de Gestión de Banners - Documentación

## Descripción General

El módulo de gestión de banners permite administrar banners publicitarios en diferentes ubicaciones del sitio web, con control completo sobre la visibilidad, dispositivos y estadísticas.

## Características Implementadas

### ✅ Ubicaciones Disponibles

- **Inicio (entre secciones)**: Banners horizontales entre bloques de contenido en la página principal
- **Sidebar lateral derecho**: Banners verticales en la barra lateral
- **Sección inferior (Footer)**: Banners en el pie de página
- **Dentro de notas/artículos**: Banners integrados en el contenido de las noticias
- **Entre títulos o bloques**: Banners intermedios entre secciones

### ✅ Tipos de Banners Soportados

- **Imágenes**: JPG, JPEG, PNG, GIF, WebP
- **Con enlace**: URL externa o interna configurable
- **Orientación**: Horizontal o Vertical
- **Dispositivos**: Todos, Solo Desktop, Solo Móvil

### ✅ Funcionalidades del Admin

El panel de administración incluye:

- ✅ Listar todos los banners con filtros por ubicación y estado
- ✅ Crear nuevo banner con carga de imagen
- ✅ Editar banner existente
- ✅ Eliminar banner
- ✅ Activar/desactivar banners
- ✅ Configurar ubicación del banner
- ✅ Orden de aparición (prioridad)
- ✅ Fechas de inicio y fin (vigencia opcional)
- ✅ Configurar visibilidad por dispositivo (móvil/desktop/todos)
- ✅ Banner rotativo (carrusel) - opción disponible
- ✅ Estadísticas de rendimiento (impresiones, clics, CTR)

## Instalación

### 1. Ejecutar Script SQL

Ejecutar el archivo `database_banners_module.sql` en tu base de datos MySQL:

```bash
mysql -u usuario -p nombre_base_datos < database_banners_module.sql
```

O importar desde phpMyAdmin.

### 2. Verificar Permisos

El módulo de banners requiere el permiso `configuracion` o `all` para acceder desde el panel administrativo.

### 3. Crear Directorio de Uploads

Asegúrate de que el directorio de uploads existe y tiene permisos de escritura:

```bash
mkdir -p public/uploads/banners
chmod 755 public/uploads/banners
```

## Uso

### Acceso al Módulo

1. Inicia sesión en el panel de administración
2. En el menú lateral, haz clic en "Banners"
3. Se mostrará la lista de todos los banners existentes

### Crear un Nuevo Banner

1. Haz clic en "Nuevo Banner"
2. Completa el formulario:
   - **Nombre**: Identificador interno del banner
   - **Ubicación**: Dónde se mostrará el banner
   - **Orientación**: Horizontal o vertical
   - **Dispositivo**: En qué dispositivos se mostrará
   - **Imagen**: Selecciona un archivo de imagen (JPG, PNG, WebP)
   - **URL de Destino**: Opcional - URL a la que redirige el banner
   - **Orden**: Prioridad de aparición (menor = primero)
   - **Fechas**: Opcional - Define vigencia del banner
3. Marca "Banner activo" para que sea visible inmediatamente
4. Haz clic en "Guardar Banner"

### Editar un Banner

1. En la lista de banners, haz clic en el ícono de editar (lápiz)
2. Modifica los campos deseados
3. Para cambiar la imagen, selecciona un nuevo archivo
4. Haz clic en "Actualizar Banner"

### Eliminar un Banner

1. En la lista de banners, haz clic en el ícono de eliminar (papelera)
2. Confirma la eliminación
3. El banner y su imagen se eliminarán permanentemente

### Filtrar Banners

Utiliza los filtros en la parte superior de la lista para:
- Filtrar por ubicación específica
- Filtrar por estado (activo/inactivo)

## Estructura de Archivos

```
├── app/
│   ├── models/
│   │   └── Banner.php                 # Modelo de datos para banners
│   └── helpers/
│       └── banner_helper.php          # Funciones auxiliares para mostrar banners
├── api/
│   └── banner_track.php               # API para tracking de estadísticas
├── banners.php                        # Lista de banners
├── banner_crear.php                   # Formulario de creación
├── banner_editar.php                  # Formulario de edición
├── banner_accion.php                  # Controlador de acciones (eliminar, toggle)
├── database_banners_module.sql        # Script de instalación de DB
└── public/
    └── uploads/
        └── banners/                   # Directorio de imágenes
```

## Integración en el Frontend

Los banners se muestran automáticamente en:

- **index.php**: Página principal (sidebar, entre secciones, footer)
- **noticia_detalle.php**: Dentro del contenido de las noticias

### Uso Programático

Para mostrar banners en otras páginas, incluye el helper y usa:

```php
// Incluir modelo y helper
$bannerModel = new Banner();
require_once __DIR__ . '/app/helpers/banner_helper.php';

// Mostrar banners de una ubicación
displayBanners('sidebar', 3); // Ubicación, límite opcional
```

## Tracking de Estadísticas

El sistema rastrea automáticamente:

- **Impresiones**: Cada vez que un banner se muestra
- **Clics**: Cada vez que un usuario hace clic en el banner
- **CTR**: Porcentaje de clics sobre impresiones

Las estadísticas se actualizan en tiempo real mediante llamadas AJAX al API.

## Consideraciones Técnicas

### Responsividad

- Los banners se adaptan automáticamente al tamaño de pantalla
- Opción de mostrar solo en desktop o móvil
- Clases CSS aplicadas automáticamente según dispositivo

### Rendimiento

- Las imágenes se cargan con `loading="lazy"` para optimizar la carga
- Las estadísticas se rastrean de forma asíncrona sin afectar la experiencia del usuario

### Seguridad

- Validación de tipos de archivo permitidos
- Sanitización de URLs de destino
- Protección contra inyección SQL mediante prepared statements
- Control de permisos en el backend

## Compatibilidad

El módulo mantiene compatibilidad con el sistema anterior de banners (`PaginaInicio`), permitiendo una transición gradual.

## Soporte

Para reportar problemas o solicitar mejoras, contacta al equipo de desarrollo.

---

**Fecha de implementación**: Enero 2026  
**Versión**: 1.0
