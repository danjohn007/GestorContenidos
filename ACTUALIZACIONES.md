# Actualizaciones del Sistema - Instrucciones de Instalación

## Nuevas Funcionalidades Implementadas

Este update incluye las siguientes mejoras:

### 1. **Sistema de Gestión de Página de Inicio**
- Slider principal configurable
- Sección de accesos directos
- Información de contacto personalizable
- Panel de administración completo en `/pagina_inicio.php`

### 2. **Palabras Clave (Tags) en Noticias**
- Campo de tags para mejorar la búsqueda
- Indexación de palabras clave
- Visualización de tags en las noticias

### 3. **Búsqueda de Noticias**
- Buscador global que indexa título, contenido, resumen y tags
- Página de resultados de búsqueda
- Accesible desde la página principal

### 4. **Enlaces a Redes Sociales**
- Gestión dinámica de enlaces a redes sociales
- Barra superior con iconos de redes sociales
- Configurable desde la base de datos

### 5. **Editor de Texto Enriquecido (WYSIWYG)**
- Integración de TinyMCE para crear noticias
- Formato HTML completo (estilos, colores, enlaces, imágenes)
- Vista previa en tiempo real

### 6. **Sistema de Auditoría Completo**
- Registro automático de creación de noticias
- Registro de modificaciones en página de inicio
- Logs accesibles en `/logs.php?tipo=auditoria`

### 7. **Página de Detalle de Noticias**
- Vista completa de cada noticia
- Enlaces desde la página principal
- Contador de visitas
- Noticias relacionadas
- Botones para compartir en redes sociales

### 8. **Corrección de URLs de Multimedia**
- URLs absolutas con detección automática de BASE_URL
- Vista previa correcta de imágenes
- Función de copiar URL mejorada

## Instalación

### Paso 1: Actualizar la Base de Datos

**Opción A - Mediante Script de Instalación (Recomendado)**

1. **IMPORTANTE**: Antes de usar, considera cambiar el secreto en `install_updates.php` línea 12 por un valor más seguro
2. Accede a: `http://tu-dominio.com/install_updates.php?secret=TU_SECRETO_AQUI`
3. El script ejecutará automáticamente todas las actualizaciones necesarias
4. Verás un resumen de los cambios aplicados
5. **CRÍTICO**: Elimina inmediatamente el archivo `install_updates.php` después de usarlo

**Notas de Seguridad**: 
- El script solo funciona desde localhost o con el parámetro secret
- El secreto por defecto es 'install123' - cámbialo antes de usar en producción
- Siempre elimina el archivo después de la instalación

**Opción B - Manualmente mediante phpMyAdmin**

1. Abre phpMyAdmin
2. Selecciona tu base de datos
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido del archivo `database_updates.sql`
5. Ejecuta el script

### Paso 2: Verificar Permisos de Carpetas

Asegúrate de que las siguientes carpetas tengan permisos de escritura (755 o 775):

```bash
chmod -R 755 public/uploads/noticias/
chmod -R 755 public/uploads/multimedia/
chmod -R 755 public/uploads/homepage/
```

### Paso 3: Probar las Nuevas Funcionalidades

1. **Página de Inicio**: Accede al administrador y busca "Página de Inicio" en el menú
2. **Crear Noticia con Tags**: Ve a "Nueva Noticia" y verás el nuevo campo de "Palabras Clave"
3. **Búsqueda**: Usa el buscador en la parte pública del sitio
4. **Ver Detalles**: Haz clic en cualquier noticia para ver su página completa
5. **Auditoría**: Accede a `Logs > Auditoría de Acciones` para ver los registros

## Nuevas Tablas Creadas

- `pagina_inicio`: Gestión de contenido de la página principal
- `redes_sociales`: Enlaces a redes sociales
- Columna `tags` agregada a la tabla `noticias`

## Datos de Prueba Incluidos

El script crea automáticamente:
- 3 slides de ejemplo
- 4 accesos directos
- 1 sección de contacto
- 4 redes sociales (Facebook, Twitter, Instagram, YouTube)

## Configuración Post-Instalación

### Personalizar Página de Inicio

1. Accede a `/pagina_inicio.php` en el administrador
2. Edita los textos del slider
3. Configura los accesos directos según tus necesidades
4. Actualiza la información de contacto

### Configurar Redes Sociales

Las redes sociales se crean desactivadas por defecto con URLs de placeholder. 

**Actualizar URLs reales:**
1. Accede a phpMyAdmin o tu cliente MySQL
2. Edita la tabla `redes_sociales`
3. Actualiza los campos `url` y `activo`:
   ```sql
   UPDATE redes_sociales SET 
     url = 'https://facebook.com/tu-pagina',
     activo = 1
   WHERE nombre = 'Facebook';
   ```
4. Repite para las otras redes sociales que uses

**O crea una interfaz de administración** siguiendo el patrón de `pagina_inicio.php` para gestionar las redes sociales desde el panel admin.

### Editor de Texto

El editor TinyMCE está configurado para usar el CDN público. Para producción, considera:
- Obtener una API key gratuita en https://www.tiny.cloud/
- Reemplazar en `noticia_crear.php` la línea del CDN con tu API key

## Solución de Problemas

### El buscador no encuentra nada
- Verifica que las noticias estén en estado "publicado"
- Asegúrate de que la columna `tags` fue agregada correctamente

### Las imágenes de multimedia no se ven
- Verifica los permisos de la carpeta `public/uploads/`
- Confirma que BASE_URL está correctamente configurado en `config/bootstrap.php`

### Error en auditoría
- Verifica que la tabla `logs_auditoria` existe
- Confirma que el modelo `Log` tiene el método `registrarAuditoria`

### El editor no carga
- Verifica conexión a internet (usa CDN)
- Revisa la consola del navegador por errores de JavaScript

## Seguridad

### Importante: Eliminar archivo de instalación
Después de ejecutar las actualizaciones, elimina o renombra el archivo `install_updates.php` por seguridad:

```bash
rm install_updates.php
# o
mv install_updates.php install_updates.php.bak
```

## Compatibilidad

- PHP 7.4+
- MySQL 5.7+
- Navegadores modernos (Chrome, Firefox, Safari, Edge)

## Soporte

Para reportar problemas o sugerencias, contacta al equipo de desarrollo.

---

**Versión**: 1.1.0  
**Fecha**: Diciembre 2024
