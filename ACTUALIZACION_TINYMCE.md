# Actualizaciones del Sistema - Integración TinyMCE y Configuración

## Resumen de Cambios

Este documento describe las mejoras implementadas en el sistema de gestión de contenidos.

## 1. Corrección de Bug en Búsqueda de Noticias ✅

**Archivo:** `app/models/Noticia.php`

**Problema:** Error SQL al buscar noticias: `SQLSTATE[HY093]: Invalid parameter number`

**Solución:** 
- Se corrigieron los métodos `search()` y `countSearch()` para usar parámetros únicos en la consulta SQL
- El problema ocurría porque se usaba el mismo placeholder `:termino` múltiples veces sin vincularlo correctamente
- Ahora se usan placeholders únicos (`:termino1`, `:termino2`, etc.) para cada campo de búsqueda

**Código actualizado:**
```php
// Antes (con error):
WHERE n.titulo LIKE :termino OR n.contenido LIKE :termino ...

// Después (corregido):
WHERE n.titulo LIKE :termino1 OR n.contenido LIKE :termino2 ...
```

## 2. Configuración de TinyMCE ✅

**Archivos creados:**
- `configuracion_sitio.php` - Página de configuración del sitio
- `app/models/Configuracion.php` - Modelo para gestionar configuraciones

**Características:**
- Campo para agregar API Key de TinyMCE
- El API Key se guarda en la base de datos y en `config/config.php`
- Se puede obtener una clave gratuita en: https://www.tiny.cloud/auth/signup/

**Base de datos:**
```sql
INSERT INTO configuracion (clave, valor, tipo, grupo, descripcion) VALUES
('tinymce_api_key', '', 'texto', 'general', 'API Key de TinyMCE para editor de texto enriquecido');
```

## 3. Módulos de Configuración General ✅

Se han desarrollado todos los módulos de la sección "Configuración General":

### 3.1 Datos del Sitio
**Archivo:** `configuracion_sitio.php`

Permite configurar:
- Nombre del sitio
- Slogan
- Descripción para SEO
- Logo del sitio (con carga de imagen)
- Email del sistema
- Teléfono de contacto
- Dirección
- Zona horaria
- **TinyMCE API Key**

### 3.2 Estilos y Colores
**Archivo:** `configuracion_estilos.php`

Permite personalizar:
- Color primario (botones y enlaces)
- Color secundario (acentos)
- Color de acento (elementos destacados)
- Color de texto
- Color de fondo
- Fuente principal
- Fuente para títulos
- Vista previa en tiempo real de los colores

### 3.3 Correo del Sistema
**Archivo:** `configuracion_correo.php`

Permite configurar SMTP:
- Servidor SMTP
- Puerto (587 para TLS, 465 para SSL)
- Usuario SMTP
- Contraseña SMTP
- Seguridad (TLS/SSL/None)
- Email remitente
- Nombre remitente
- Incluye guía para configurar Gmail

### 3.4 Redes Sociales y SEO
**Archivo:** `configuracion_redes_seo.php`

**Redes Sociales:**
- Facebook
- Twitter
- Instagram
- YouTube
- Activar/desactivar cada red
- Configurar URL de perfil

**SEO y Analytics:**
- Google Analytics ID
- Google Search Console (código de verificación)
- Facebook App ID
- Meta Keywords por defecto
- Meta Description por defecto

## 4. Imágenes en Accesos Directos ✅

**Archivos modificados:**
- `pagina_inicio.php` - Formulario actualizado
- `index.php` - Visualización actualizada
- `database_updates.sql` - Campo `imagen` en tabla `pagina_inicio`

**Características:**
- Se agregó un campo de carga de imagen en la sección "Accesos Directos"
- La imagen reemplaza al ícono cuando está presente
- Tamaño recomendado: 128x128px
- Formatos soportados: JPG, PNG, GIF, WEBP
- Si no hay imagen, se usa el ícono Font Awesome

**Implementación en frontend:**
```php
<?php if (!empty($acceso['imagen'])): ?>
    <img src="<?php echo e($acceso['imagen']); ?>" alt="..." class="w-16 h-16 mx-auto object-contain">
<?php else: ?>
    <i class="<?php echo e($acceso['contenido']); ?>"></i>
<?php endif; ?>
```

## 5. Actualizaciones de Base de Datos

**Archivo:** `database_updates.sql`

Ejecutar las siguientes actualizaciones en la base de datos:

### Nuevas configuraciones:
```sql
INSERT INTO configuracion (clave, valor, tipo, grupo, descripcion) VALUES
-- TinyMCE
('tinymce_api_key', '', 'texto', 'general', 'API Key de TinyMCE'),

-- Sitio
('slogan_sitio', '', 'texto', 'general', 'Slogan del sitio web'),
('descripcion_sitio', '', 'texto', 'general', 'Descripción breve del sitio para SEO'),
('logo_sitio', '', 'texto', 'general', 'Ruta del logo del sitio'),
('direccion', '', 'texto', 'contacto', 'Dirección física'),

-- Diseño
('color_acento', '#10b981', 'color', 'diseno', 'Color de acento del sistema'),
('color_texto', '#1f2937', 'color', 'diseno', 'Color principal del texto'),
('color_fondo', '#f3f4f6', 'color', 'diseno', 'Color de fondo del sitio'),
('fuente_principal', 'system-ui', 'texto', 'diseno', 'Fuente principal'),
('fuente_titulos', 'system-ui', 'texto', 'diseno', 'Fuente para títulos'),

-- Correo
('smtp_host', '', 'texto', 'correo', 'Servidor SMTP'),
('smtp_port', '587', 'texto', 'correo', 'Puerto SMTP'),
('smtp_usuario', '', 'texto', 'correo', 'Usuario SMTP'),
('smtp_password', '', 'texto', 'correo', 'Contraseña SMTP'),
('smtp_seguridad', 'tls', 'texto', 'correo', 'Seguridad SMTP'),
('email_remitente', '', 'texto', 'correo', 'Email remitente'),
('nombre_remitente', '', 'texto', 'correo', 'Nombre remitente'),

-- SEO
('google_search_console', '', 'texto', 'seo', 'Código de verificación Google Search Console'),
('facebook_app_id', '', 'texto', 'seo', 'Facebook App ID'),
('meta_keywords_default', '', 'texto', 'seo', 'Palabras clave por defecto'),
('meta_description_default', '', 'texto', 'seo', 'Descripción meta por defecto')
ON DUPLICATE KEY UPDATE clave=clave;
```

## Instrucciones de Instalación

1. **Hacer backup de la base de datos**
   ```bash
   mysqldump -u usuario -p nombre_bd > backup_antes_actualizacion.sql
   ```

2. **Ejecutar el script de actualización**
   ```bash
   mysql -u usuario -p nombre_bd < database_updates.sql
   ```

3. **Verificar que las tablas se crearon correctamente**
   - `pagina_inicio` debe tener el campo `imagen`
   - `configuracion` debe tener todos los nuevos registros

4. **Actualizar archivos PHP**
   - Copiar todos los archivos modificados al servidor
   - Verificar permisos de carpetas de uploads: `public/uploads/config/` y `public/uploads/homepage/`

5. **Configurar TinyMCE**
   - Ir a Configuración > Datos del Sitio
   - Obtener API Key gratuita en: https://www.tiny.cloud/auth/signup/
   - Ingresar la API Key en el campo correspondiente
   - Guardar cambios

6. **Probar funcionalidad**
   - Búsqueda de noticias en el sitio público
   - Edición de noticias con TinyMCE
   - Configuración de estilos y colores
   - Carga de imágenes en accesos directos

## Archivos Modificados

### Modelos:
- ✅ `app/models/Noticia.php` - Fix búsqueda
- ✅ `app/models/Configuracion.php` - NUEVO
- ✅ `app/models/PaginaInicio.php` - Sin cambios (ya existía)
- ✅ `app/models/RedesSociales.php` - Sin cambios (ya existía)

### Vistas/Páginas:
- ✅ `configuracion.php` - Enlaces actualizados
- ✅ `configuracion_sitio.php` - NUEVO
- ✅ `configuracion_estilos.php` - NUEVO
- ✅ `configuracion_correo.php` - NUEVO
- ✅ `configuracion_redes_seo.php` - NUEVO
- ✅ `pagina_inicio.php` - Campo imagen en accesos directos
- ✅ `index.php` - Mostrar imágenes en accesos directos

### Base de datos:
- ✅ `database_updates.sql` - Todas las actualizaciones SQL

## Mejoras Futuras Sugeridas

1. **Validación de imágenes mejorada**
   - Redimensionamiento automático de imágenes cargadas
   - Compresión de imágenes para optimizar rendimiento

2. **Personalización de colores en tiempo real**
   - Preview dinámico al cambiar colores
   - Generador de paletas de colores

3. **Configuración avanzada de TinyMCE**
   - Personalizar plugins y botones disponibles
   - Configurar tamaños de fuente permitidos

4. **Testing automatizado**
   - Tests unitarios para búsqueda
   - Tests de integración para configuración

## Soporte

Para cualquier problema o pregunta sobre estas actualizaciones:
- Revisar logs en: `logs/` (si están configurados)
- Verificar permisos de archivos y carpetas
- Comprobar configuración de base de datos en `config/config.php`

---

**Fecha de actualización:** Diciembre 2024
**Versión:** 1.1.0
