# Plan de Pruebas - Correcciones Enero 2026

## Issue 1: Actualización de Categoría Padre de Subcategoría

### Prueba 1.1: Cambiar categoría padre de una subcategoría sin noticias
**Pasos:**
1. Ir a Categorías
2. Seleccionar una subcategoría que NO tenga noticias asociadas
3. Hacer clic en "Editar"
4. Cambiar el campo "Categoría Padre" a otra categoría principal
5. Guardar cambios

**Resultado esperado:**
✓ La subcategoría debe actualizarse correctamente sin errores
✓ Debe aparecer bajo la nueva categoría padre en el listado

### Prueba 1.2: Prevenir ciclos en jerarquía
**Pasos:**
1. Crear Categoría A (padre)
2. Crear Subcategoría B bajo Categoría A
3. Intentar editar Categoría A y establecer Subcategoría B como su padre

**Resultado esperado:**
✗ Debe mostrar error: "No se puede seleccionar una subcategoría propia como categoría padre"
✓ No debe permitir guardar cambios que creen ciclos

### Prueba 1.3: Validación de auto-referencia
**Pasos:**
1. Editar cualquier categoría
2. Intentar seleccionarse a sí misma como categoría padre

**Resultado esperado:**
✗ Debe mostrar error: "Una categoría no puede ser su propia categoría padre"

---

## Issue 2: Botón "Contáctanos" en Footer

### Prueba 2.1: Verificar funcionalidad del botón
**Pasos:**
1. Ir a la página de inicio (index.php)
2. Desplazarse hasta la sección de contacto antes del footer
3. Hacer clic en el botón "Contáctanos"

**Resultado esperado:**
✓ Debe abrir el cliente de correo predeterminado
✓ El campo "Para:" debe contener el email del sistema configurado
✓ Verificar que el email_sistema esté configurado en Configuración > Sitio

---

## Issue 3: Footer Inconsistente entre Páginas

### Prueba 3.1: Comparar footer en página de inicio vs noticia
**Pasos:**
1. Ir a la página de inicio (index.php)
2. Desplazarse hasta el footer y tomar captura
3. Ir a cualquier noticia (noticia_detalle.php)
4. Desplazarse hasta el footer y comparar

**Resultado esperado:**
✓ Debe tener la MISMA estructura
✓ Debe mostrar el MISMO contenido
✓ Debe usar los MISMOS estilos y colores
✓ Ambos deben mostrar enlace "Aviso Legal" si está configurado

---

## Issue 4: Noticias Destacadas (Solo Imagen) - Carrusel

### Prueba 4.1: Visualización en 4 columnas
**Pasos:**
1. Ir a Destacadas (Imágenes) > Crear
2. Crear 6-8 noticias con Vista: "Carrusel", Ubicación: "Bajo el slider"
3. Ver en página de inicio pública

**Resultado esperado:**
✓ Debe mostrar 4 imágenes por página
✓ Controles prev/next deben aparecer cuando hay más de 4
✓ Indicadores de página deben funcionar correctamente

### Prueba 4.2: Responsive design
**Resultado esperado:**
✓ Desktop: 4 columnas
✓ Móvil: 2 columnas

---

## Issue 5: Programación de Noticias

### Prueba 5.1: Crear y publicar noticia programada
**Pasos:**
1. Crear noticia con estado "Publicado"
2. Establecer fecha programada FUTURA (ej: en 5 minutos)
3. Guardar
4. Verificar que `fecha_publicacion` sea NULL en BD
5. Ejecutar `publicar_programadas.php` después de la fecha

**Resultado esperado:**
✓ Noticia NO aparece en sitio público antes de la fecha
✓ Script publica la noticia automáticamente
✓ `fecha_publicacion` se establece correctamente

### Prueba 5.2: Reprogramar noticia publicada
**Pasos:**
1. Editar noticia ya publicada
2. Establecer fecha programada FUTURA
3. Guardar

**Resultado esperado:**
✓ `fecha_publicacion` se resetea a NULL
✓ Noticia desaparece del sitio público hasta la nueva fecha

---

## Checklist Final

- [ ] Issue 1: Actualización categorías ✓
- [ ] Issue 2: Botón contáctanos ✓
- [ ] Issue 3: Footer unificado ✓
- [ ] Issue 4: Carrusel 4 columnas ✓
- [ ] Issue 5: Publicación programada ✓
- [ ] Sin errores PHP
- [ ] Sin errores JavaScript
- [ ] Responsive funciona
