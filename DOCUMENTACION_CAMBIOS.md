# Documentación de Cambios: Rediseño de la Parte Pública

## Resumen de Cambios

Este documento describe las actualizaciones realizadas al sistema para implementar el rediseño de la parte pública según los requerimientos especificados.

## Nuevas Características

### 1. Módulo Lateral de Accesos Rápidos

Se ha agregado un módulo lateral en la parte pública que muestra hasta 3 accesos directos configurables.

**Características:**
- Ubicado en el lado derecho de la página principal
- Diseño responsive que se adapta a diferentes tamaños de pantalla
- Soporte para iconos Font Awesome o imágenes personalizadas
- Enlaces configurables

**Gestión:**
Los accesos laterales se gestionan desde el panel de administración en:
`Página de Inicio > Accesos Laterales`

### 2. Gestión de Menú Principal

Se ha implementado un sistema completo de gestión del menú principal superior.

**Características:**
- Cada ítem del menú representa una categoría del sistema
- Los ítems se pueden activar/desactivar individualmente
- Orden configurable de los ítems
- Sincronización automática con las categorías principales

**Gestión:**
El menú principal se gestiona desde:
`Página de Inicio > Menú Principal`

### 3. Filtrado por Categoría

Se ha corregido y mejorado el filtrado de noticias por categoría.

**Características:**
- Al hacer clic en un ítem del menú, se filtran las noticias de esa categoría
- El título de la sección cambia para indicar la categoría activa
- El ítem activo se resalta en el menú
- Solo se muestran noticias de la categoría seleccionada

## Archivos Modificados

### 1. `app/models/MenuItem.php` (NUEVO)
Modelo para gestionar los ítems del menú principal.

**Métodos principales:**
- `getAll($activo = null)`: Obtiene todos los ítems del menú
- `syncWithCategories()`: Sincroniza el menú con las categorías
- `update($id, $data)`: Actualiza un ítem del menú

### 2. `index.php`
Página principal del sitio público.

**Cambios:**
- Agregado filtrado por categoría mediante parámetro `?categoria=ID`
- Implementado layout de dos columnas (principal + lateral)
- Agregado módulo lateral de accesos rápidos
- Menú superior ahora carga solo ítems activos desde menu_items
- Sección de categorías en el sidebar

### 3. `pagina_inicio.php`
Panel de administración de la página de inicio.

**Cambios:**
- Agregada pestaña "Accesos Laterales" para gestionar accesos del módulo lateral
- Agregada pestaña "Menú Principal" para gestionar ítems del menú
- Funcionalidad para sincronizar menú con categorías
- Funcionalidad para activar/desactivar ítems del menú
- Funcionalidad para cambiar el orden de los ítems

### 4. `database_updates.sql`
Script de actualizaciones de base de datos.

**Cambios:**
- Tabla `menu_items` para gestionar ítems del menú principal
- Datos por defecto para `acceso_lateral` (3 accesos iniciales)
- Relación entre menu_items y categorías

## Estructura de Base de Datos

### Tabla: `menu_items`
```sql
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `orden` (`orden`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
)
```

### Tabla: `pagina_inicio` (actualizada)
Nueva sección: `acceso_lateral`
- Almacena hasta 3 accesos directos para el módulo lateral
- Campos: titulo, subtitulo, contenido (icono), imagen, url, orden, activo

## Guía de Uso

### Para Administradores

#### 1. Actualizar la Base de Datos
Después de desplegar los cambios, ejecutar:
```
http://tu-sitio.com/install_updates.php
```
O ejecutar manualmente el script `database_updates.sql` en la base de datos.

#### 2. Configurar Accesos Laterales
1. Ir a: `Panel de Administración > Página de Inicio`
2. Seleccionar la pestaña "Accesos Laterales"
3. Configurar hasta 3 accesos con:
   - Título
   - Subtítulo
   - Icono (Font Awesome) o Imagen
   - URL de destino
   - Orden
   - Estado (Activo/Inactivo)
4. Guardar los cambios

#### 3. Gestionar Menú Principal
1. Ir a: `Panel de Administración > Página de Inicio`
2. Seleccionar la pestaña "Menú Principal"
3. Hacer clic en "Sincronizar con Categorías" si es la primera vez
4. Para cada ítem:
   - Cambiar el orden si es necesario
   - Activar/Desactivar según se requiera
5. Los cambios se aplican inmediatamente

### Para Usuarios Finales

#### Navegación por Categorías
1. Hacer clic en cualquier ítem del menú superior
2. Se mostrarán solo las noticias de esa categoría
3. El título cambiará a "Noticias de [Categoría]"
4. El ítem del menú se resaltará

#### Uso de Accesos Rápidos
1. En el lado derecho de la página, ver el módulo "Accesos Rápidos"
2. Hacer clic en cualquiera de los 3 accesos para ir directamente a la sección
3. También están disponibles las categorías en el sidebar

## Características Técnicas

### Responsive Design
- Layout de dos columnas en desktop
- Una columna en móviles
- El módulo lateral se muestra debajo del contenido principal en móviles

### Performance
- Los ítems del menú se cargan solo una vez por página
- Filtrado eficiente por categoría a nivel de base de datos
- CSS optimizado con Tailwind

### Seguridad
- Validación de parámetros de URL
- Sanitización de salida HTML
- Protección contra SQL injection mediante prepared statements

## Compatibilidad

### Navegadores Soportados
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Versión de PHP
- Requiere PHP 7.4 o superior
- Probado en PHP 8.0+

## Solución de Problemas

### Los ítems del menú no aparecen
1. Verificar que la tabla `menu_items` existe
2. Ejecutar la sincronización desde "Menú Principal"
3. Verificar que hay categorías principales creadas
4. Verificar que los ítems están activos

### El módulo lateral no se muestra
1. Verificar que existen accesos laterales configurados
2. Verificar que están marcados como activos
3. Verificar que la sección es 'acceso_lateral' en la base de datos

### El filtrado por categoría no funciona
1. Verificar que la categoría existe
2. Verificar que hay noticias publicadas en esa categoría
3. Verificar la URL tiene el formato correcto: `?categoria=ID`

## Próximas Mejoras Sugeridas

1. Paginación para noticias filtradas por categoría
2. Breadcrumbs para indicar la navegación
3. Contador de noticias por categoría
4. Búsqueda dentro de una categoría específica
5. Subcategorías en el menú desplegable

## Contacto y Soporte

Para soporte adicional o reportar problemas, contactar al equipo de desarrollo.
