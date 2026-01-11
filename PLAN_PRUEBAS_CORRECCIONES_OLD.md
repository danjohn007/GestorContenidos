# Plan de Pruebas - Correcciones de Noticias e Imágenes

## Preparación

Antes de probar, asegúrate de:
1. Ejecutar el script de migración: `install_updates.php?secret=install123` o `database_fix_tags.sql`
2. Limpiar caché del navegador (Ctrl+F5)
3. Tener al menos una categoría creada en el sistema

## Prueba 1: Crear Noticia Sin Imagen

**Objetivo:** Verificar que se puede crear una noticia básica

**Pasos:**
1. Iniciar sesión en el sistema administrativo
2. Ir a "Noticias" → "Crear Nueva Noticia"
3. Completar los campos:
   - Título: "Noticia de Prueba 1"
   - Categoría: Seleccionar cualquiera
   - Contenido: Escribir al menos un párrafo en el editor Quill
4. Hacer clic en "Guardar Noticia"

**Resultado Esperado:**
- ✅ Mensaje de éxito "Noticia creada exitosamente"
- ✅ Redirección al listado de noticias
- ✅ La nueva noticia aparece en el listado

## Prueba 2: Crear Noticia Con Imagen

**Objetivo:** Verificar que se puede subir y guardar una imagen destacada

**Pasos:**
1. Ir a "Noticias" → "Crear Nueva Noticia"
2. Completar los campos requeridos:
   - Título: "Noticia de Prueba 2 con Imagen"
   - Categoría: Seleccionar cualquiera
   - Contenido: Escribir al menos un párrafo
3. Subir una imagen en "Imagen Destacada" (JPG, PNG o WebP)
4. Hacer clic en "Guardar Noticia"

**Resultado Esperado:**
- ✅ Mensaje de éxito "Noticia creada exitosamente"
- ✅ La noticia aparece en el listado
- ✅ La imagen en miniatura se muestra en el listado del admin
- ✅ La imagen NO requiere recargar la página para visualizarse

## Prueba 3: Crear Noticia Con Tags

**Objetivo:** Verificar que el campo tags funciona correctamente

**Pasos:**
1. Ir a "Noticias" → "Crear Nueva Noticia"
2. Completar los campos:
   - Título: "Noticia de Prueba 3 con Tags"
   - Categoría: Seleccionar cualquiera
   - Contenido: Escribir al menos un párrafo
   - Tags: "prueba, test, noticia"
3. Hacer clic en "Guardar Noticia"

**Resultado Esperado:**
- ✅ Mensaje de éxito
- ✅ La noticia se guarda correctamente
- ✅ Al editar la noticia, los tags aparecen en el campo

## Prueba 4: Visualización en Página Pública (Con Imagen)

**Objetivo:** Verificar que las imágenes se muestran correctamente en la parte pública

**Pasos:**
1. Crear una noticia con imagen destacada y estado "Publicado"
2. Ir a la página pública del sitio (index.php)
3. Verificar que la noticia aparece en "Últimas Noticias" o "Noticias Destacadas"
4. Observar la imagen destacada

**Resultado Esperado:**
- ✅ La imagen se carga inmediatamente (sin necesidad de recargar)
- ✅ La imagen se muestra con el tamaño y proporción correctos
- ✅ No aparece icono de "imagen rota"

## Prueba 5: Visualización en Detalle de Noticia

**Objetivo:** Verificar que las imágenes se muestran en la página de detalle

**Pasos:**
1. Desde la página pública, hacer clic en "Leer más" de una noticia con imagen
2. Observar la página de detalle

**Resultado Esperado:**
- ✅ La imagen destacada se muestra en tamaño grande
- ✅ La imagen se carga sin errores
- ✅ El contenido de la noticia se muestra correctamente formateado

## Prueba 6: Editar Noticia

**Objetivo:** Verificar que se puede editar una noticia existente

**Pasos:**
1. Ir al listado de noticias en el admin
2. Hacer clic en "Editar" en cualquier noticia
3. Modificar el título o contenido
4. Hacer clic en "Guardar Cambios"

**Resultado Esperado:**
- ✅ Los cambios se guardan correctamente
- ✅ No aparecen errores de base de datos
- ✅ Si había imagen, se mantiene

## Prueba 7: Validación de Contenido Vacío

**Objetivo:** Verificar que no se puede crear una noticia sin contenido

**Pasos:**
1. Ir a "Crear Nueva Noticia"
2. Completar solo el título y categoría
3. Dejar el editor de contenido vacío
4. Hacer clic en "Guardar Noticia"

**Resultado Esperado:**
- ✅ Aparece alerta: "Por favor ingresa el contenido de la noticia"
- ✅ El formulario NO se envía
- ✅ Los datos ingresados se mantienen en el formulario

## Prueba 8: Búsqueda de Noticias (Con Imágenes)

**Objetivo:** Verificar que las imágenes se muestran en los resultados de búsqueda

**Pasos:**
1. Ir a la página pública
2. Usar el buscador con cualquier término
3. Observar los resultados con imágenes

**Resultado Esperado:**
- ✅ Las imágenes de los resultados se cargan correctamente
- ✅ No hay errores de carga de imágenes

## Problemas Conocidos y Soluciones

### Problema: "Las imágenes aún no cargan"
**Solución:**
1. Verificar que el directorio `/public/uploads/noticias/` existe y tiene permisos de escritura (755)
2. Limpiar caché del navegador (Ctrl+Shift+Delete)
3. Si usas CDN o proxy, limpiar su caché también

### Problema: "Error al guardar noticia"
**Solución:**
1. Verificar que ejecutaste el script de migración `database_fix_tags.sql`
2. Verificar que el campo `tags` existe en la tabla `noticias`:
   ```sql
   DESCRIBE noticias;
   ```
3. Si no existe, ejecutar manualmente:
   ```sql
   ALTER TABLE noticias ADD COLUMN tags VARCHAR(500) DEFAULT NULL AFTER resumen;
   ```

### Problema: "La imagen se sube pero no se ve"
**Solución:**
1. Verificar permisos del directorio de uploads
2. Verificar que el archivo se guardó correctamente:
   ```bash
   ls -la public/uploads/noticias/
   ```
3. Verificar el valor en la base de datos:
   ```sql
   SELECT id, titulo, imagen_destacada FROM noticias WHERE id = X;
   ```

## Verificación Final

Todas las pruebas deben pasar para confirmar que las correcciones funcionan correctamente.

**Lista de verificación:**
- [ ] Prueba 1: Crear noticia sin imagen
- [ ] Prueba 2: Crear noticia con imagen
- [ ] Prueba 3: Crear noticia con tags
- [ ] Prueba 4: Visualización pública con imagen
- [ ] Prueba 5: Visualización en detalle
- [ ] Prueba 6: Editar noticia
- [ ] Prueba 7: Validación de contenido vacío
- [ ] Prueba 8: Búsqueda con imágenes

Si todas las pruebas pasan: **✅ Sistema funcionando correctamente**
