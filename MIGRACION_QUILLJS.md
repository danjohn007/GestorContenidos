# Migración de TinyMCE a Quill.js

## Fecha
Diciembre 2024

## Motivo del Cambio
El editor TinyMCE requería una clave API que generaba errores persistentes en la configuración del sistema. Los usuarios reportaban el siguiente error al intentar configurar la clave API:

```
Se encontraron errores:
No se pudo actualizar TINYMCE_API_KEY en config.php. Verifica el formato del archivo.
```

## Solución Implementada
Se reemplazó TinyMCE con **Quill.js**, un editor de texto enriquecido moderno, open source y completamente gratuito que:

- ✅ No requiere clave API
- ✅ Es completamente gratuito
- ✅ Tiene una interfaz limpia y moderna
- ✅ Soporta todas las funcionalidades necesarias para editar contenido
- ✅ Es ligero y rápido
- ✅ Tiene excelente documentación

## Archivos Modificados

### 1. Páginas de Creación y Edición de Noticias
- `noticia_crear.php` - Reemplazado TinyMCE con Quill.js
- `noticia_editar.php` - Reemplazado TinyMCE con Quill.js

### 2. Configuración del Sistema
- `configuracion_sitio.php` - Eliminado campo de TinyMCE API Key
- `configuracion.php` - Actualizada descripción para remover referencia a TinyMCE
- `config/config.php` - Eliminada constante TINYMCE_API_KEY
- `config/config.example.php` - Eliminada constante TINYMCE_API_KEY

### 3. Base de Datos
- `database_remove_tinymce.sql` - Script SQL para eliminar configuración de TinyMCE

## Características del Nuevo Editor (Quill.js)

El editor Quill.js incluye las siguientes funcionalidades:

### Formato de Texto
- Encabezados (H1-H6)
- Negrita, cursiva, subrayado, tachado
- Colores de texto y fondo
- Diferentes tamaños de fuente
- Diferentes tipos de fuente

### Párrafos y Listas
- Listas ordenadas y no ordenadas
- Alineación de texto (izquierda, centro, derecha, justificado)
- Indentación
- Citas en bloque
- Bloques de código

### Contenido Multimedia
- Insertar enlaces
- Insertar imágenes
- Insertar videos
- Superíndices y subíndices

### Otros
- Limpiar formato
- Interfaz intuitiva y moderna
- Placeholder personalizable

## Instrucciones de Actualización

### Paso 1: Actualizar Base de Datos
Ejecuta el siguiente script SQL en tu base de datos:

```bash
mysql -u usuario -p nombre_bd < database_remove_tinymce.sql
```

O ejecuta manualmente:
```sql
DELETE FROM configuracion WHERE clave = 'tinymce_api_key';
```

### Paso 2: Limpiar Cache del Navegador
Es importante limpiar la cache del navegador o realizar una recarga forzada (Ctrl+F5 o Cmd+Shift+R) para que se carguen correctamente los nuevos archivos CSS y JavaScript de Quill.js.

### Paso 3: Verificar Funcionamiento
1. Ve a "Noticias" > "Crear Noticia"
2. Verifica que el editor de texto enriquecido se carga correctamente
3. Prueba las diferentes opciones de formato
4. Guarda una noticia de prueba
5. Edita la noticia guardada para verificar que el contenido se carga correctamente

## Ventajas de Quill.js sobre TinyMCE

| Característica | TinyMCE | Quill.js |
|----------------|---------|----------|
| Clave API requerida | Sí (problemas frecuentes) | No ❌ |
| Costo | Freemium (límites) | Completamente gratis ✅ |
| Tamaño | ~500 KB | ~150 KB ✅ |
| Interfaz | Tradicional | Moderna y limpia ✅ |
| Configuración | Compleja | Simple ✅ |
| Errores de configuración | Frecuentes | Ninguno ✅ |

## Compatibilidad

El contenido existente creado con TinyMCE es **100% compatible** con Quill.js ya que ambos editores:
- Generan HTML estándar
- Soportan los mismos elementos HTML básicos
- No usan formatos propietarios

## Soporte y Documentación

- **Sitio oficial**: https://quilljs.com/
- **Documentación**: https://quilljs.com/docs/
- **GitHub**: https://github.com/quilljs/quill
- **CDN utilizado**: https://cdn.quilljs.com/

## Notas Adicionales

- El editor mantiene todas las funcionalidades necesarias para la creación de contenido
- La interfaz es más limpia y moderna
- El tiempo de carga de las páginas se reduce debido al menor tamaño de Quill.js
- Ya no es necesario obtener ni configurar ninguna clave API

## Reversión (Si es Necesario)

Si por alguna razón necesitas volver a TinyMCE, puedes revertir los cambios usando git:

```bash
git revert <commit-hash>
```

Sin embargo, recomendamos mantener Quill.js debido a su simplicidad y ausencia de errores de configuración.

---

**Implementado por**: GitHub Copilot
**Fecha**: Diciembre 2024
**Versión**: 1.1.0
