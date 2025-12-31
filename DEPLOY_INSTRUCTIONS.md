# Instrucciones de Despliegue: Rediseño de la Parte Pública

## Resumen
Esta actualización implementa tres características principales:
1. **Módulo lateral de accesos rápidos** - 3 accesos directos configurables en un sidebar
2. **Gestión del menú principal** - Control completo sobre qué categorías aparecen en el menú
3. **Filtrado por categoría mejorado** - Navegación funcional por categorías

## Pre-requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Acceso al panel de administración del sistema
- Permisos de administrador

## Pasos de Despliegue

### 1. Hacer Pull de los Cambios
```bash
git pull origin copilot/redesign-public-section
```

### 2. Actualizar la Base de Datos

**Opción A: Usando el Script de Actualización (Recomendado)**
1. Acceder a: `http://tu-sitio.com/install_updates.php`
2. El script ejecutará automáticamente todas las actualizaciones pendientes

**Opción B: Ejecución Manual**
```bash
mysql -u usuario -p nombre_base_datos < database_updates.sql
```

### 3. Verificar la Instalación
Ejecutar el script de validación:
```bash
php validate_changes.php
```

Deberías ver ✓ en todos los puntos de verificación.

### 4. Configurar el Sistema

#### A. Sincronizar el Menú Principal
1. Iniciar sesión en el panel de administración
2. Ir a: **Página de Inicio > Menú Principal**
3. Hacer clic en el botón **"Sincronizar con Categorías"**
4. Esto creará automáticamente ítems de menú para todas las categorías principales

#### B. Configurar Ítems del Menú
1. En la misma pantalla (**Menú Principal**)
2. Para cada categoría:
   - **Activar/Desactivar**: Hacer clic en el botón de estado
   - **Cambiar Orden**: Modificar el número y guardar
3. Los cambios se reflejarán inmediatamente en la parte pública

#### C. Configurar Accesos Laterales
1. Ir a: **Página de Inicio > Accesos Laterales**
2. Ya hay 3 accesos por defecto, pero puedes personalizarlos:
   - **Título**: Nombre del acceso
   - **Subtítulo**: Descripción breve
   - **Icono**: Clase Font Awesome (ej: `fas fa-star`)
   - **Imagen**: Opcionalmente, subir una imagen (128x128px)
   - **URL**: Destino del enlace
   - **Orden**: 1, 2, o 3
   - **Activo**: Marcar para mostrar
3. Guardar cada acceso

### 5. Probar la Funcionalidad

#### Pruebas en la Parte Pública
1. Acceder a la página principal del sitio
2. Verificar que el módulo lateral aparece con los accesos rápidos
3. Hacer clic en cada ítem del menú superior
4. Confirmar que:
   - Se filtran las noticias por categoría
   - El título cambia a "Noticias de [Categoría]"
   - El ítem del menú se resalta
   - Solo aparecen noticias de esa categoría

#### Pruebas en el Panel de Administración
1. Ir a **Página de Inicio**
2. Verificar que hay 4 pestañas:
   - Slider Principal
   - Accesos Directos
   - **Accesos Laterales** (nueva)
   - **Menú Principal** (nueva)
   - Información de Contacto
3. Probar activar/desactivar ítems del menú
4. Verificar que los cambios se reflejan en la parte pública

## Estructura de Archivos Modificados

```
GestorContenidos/
├── app/
│   └── models/
│       └── MenuItem.php                 [NUEVO]
├── database_updates.sql                 [MODIFICADO]
├── index.php                            [MODIFICADO]
├── pagina_inicio.php                    [MODIFICADO]
├── DOCUMENTACION_CAMBIOS.md            [NUEVO]
├── validate_changes.php                 [NUEVO]
└── DEPLOY_INSTRUCTIONS.md              [NUEVO - Este archivo]
```

## Cambios en la Base de Datos

### Nueva Tabla: `menu_items`
```sql
- id: Identificador único
- categoria_id: Referencia a la categoría
- orden: Orden de aparición en el menú
- activo: Estado del ítem (1=visible, 0=oculto)
- fecha_modificacion: Última actualización
```

### Nueva Sección: `pagina_inicio.acceso_lateral`
```sql
- seccion = 'acceso_lateral'
- 3 registros por defecto
- Campos: titulo, subtitulo, contenido (icono), imagen, url, orden, activo
```

## Compatibilidad y Requisitos

### Navegadores Compatibles
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Versión PHP
- Mínimo: PHP 7.4
- Recomendado: PHP 8.0+
- Probado: PHP 8.3

### Base de Datos
- MySQL 5.7+
- MariaDB 10.2+

## Rollback (En caso de problemas)

Si encuentras problemas críticos, puedes revertir los cambios:

### 1. Revertir Código
```bash
git revert HEAD~2
git push
```

### 2. Revertir Base de Datos
```sql
-- Eliminar tabla menu_items
DROP TABLE IF EXISTS `menu_items`;

-- Eliminar accesos laterales
DELETE FROM `pagina_inicio` WHERE seccion = 'acceso_lateral';
```

### 3. Restaurar Archivos Originales
```bash
git checkout main -- index.php pagina_inicio.php
```

## Solución de Problemas Comunes

### Error: "Table 'menu_items' doesn't exist"
**Solución**: Ejecutar `install_updates.php` o el script SQL manualmente

### Error: "Call to undefined class MenuItem"
**Solución**: 
1. Verificar que `app/models/MenuItem.php` existe
2. Limpiar caché de PHP si es aplicable
3. Reiniciar el servidor web

### Los accesos laterales no aparecen
**Solución**:
1. Verificar que existen registros con `seccion = 'acceso_lateral'`
2. Verificar que están marcados como activos
3. Verificar que hay al menos un registro

### El menú está vacío
**Solución**:
1. Ir a **Menú Principal** y hacer clic en **"Sincronizar con Categorías"**
2. Activar manualmente los ítems que desees mostrar
3. Verificar que existen categorías principales en el sistema

## Verificación Post-Despliegue

### Checklist de Verificación
- [ ] Base de datos actualizada correctamente
- [ ] Tabla `menu_items` creada
- [ ] Accesos laterales en `pagina_inicio`
- [ ] Script de validación ejecutado exitosamente
- [ ] Menú sincronizado con categorías
- [ ] Módulo lateral visible en la parte pública
- [ ] Filtrado por categoría funcionando
- [ ] Pestañas nuevas visibles en panel de administración
- [ ] No hay errores en logs de PHP
- [ ] No hay errores en consola del navegador

### Logs a Revisar
```bash
# Logs de Apache/Nginx
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# Logs de PHP
tail -f /var/log/php/error.log
```

## Soporte

Si encuentras problemas durante el despliegue:

1. **Revisar la documentación**: `DOCUMENTACION_CAMBIOS.md`
2. **Ejecutar validación**: `php validate_changes.php`
3. **Revisar logs**: Verificar logs de PHP y servidor web
4. **Contactar al equipo de desarrollo**: Con detalles del error y logs

## Notas Importantes

- **Backup**: Siempre hacer backup de la base de datos antes de actualizar
- **Testing**: Probar en ambiente de desarrollo/staging primero
- **Cache**: Limpiar caché del navegador para ver los cambios
- **Permisos**: Asegurarse de que el directorio `public/uploads/homepage/` tenga permisos de escritura

## Próximas Actualizaciones Planificadas

1. Paginación para categorías con muchas noticias
2. Búsqueda dentro de categorías específicas
3. Contador de noticias por categoría
4. Breadcrumbs de navegación
5. Menú desplegable con subcategorías
