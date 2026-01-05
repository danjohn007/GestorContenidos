# Fix: Teléfono de Contacto - Guardar y Mostrar

## Problema Identificado

El teléfono de contacto no se guardaba correctamente y no se reflejaba en la parte pública del sitio.

### Causa Raíz

El campo `telefono_contacto` estaba configurado en la base de datos con `grupo = 'contacto'`, pero el código en `configuracion_sitio.php` lo guardaba con `grupo = 'general'`, y en `index.php` se recuperaba desde el grupo `general`. Esta inconsistencia causaba que:

1. Al guardar, se creaba/actualizaba un registro con grupo 'general'
2. Al leer desde la base de datos inicial, se buscaba en grupo 'general' pero existía en 'contacto'
3. El valor no se mostraba en el sitio público

## Solución Aplicada

### 1. Actualización de Base de Datos (`database.sql`)

Se movió `telefono_contacto` del grupo `contacto` al grupo `general` y se agregó el campo `direccion`:

```sql
-- Antes:
('telefono_contacto', '442-123-4567', 'texto', 'contacto', 'Teléfono de contacto'),

-- Después:
('telefono_contacto', '442-123-4567', 'texto', 'general', 'Teléfono de contacto'),
('direccion', '', 'texto', 'general', 'Dirección de contacto'),
```

### 2. Script de Migración (`database_fix_telefono_contacto.sql`)

Se creó un script de migración para instalaciones existentes que actualiza el grupo de los campos:

```sql
-- Actualizar telefono_contacto existente
UPDATE `configuracion` 
SET `grupo` = 'general' 
WHERE `clave` = 'telefono_contacto';

-- Actualizar direccion existente  
UPDATE `configuracion` 
SET `grupo` = 'general' 
WHERE `clave` = 'direccion';
```

## Archivos Modificados

1. **database.sql** - Base de datos inicial actualizada
2. **database_fix_telefono_contacto.sql** - Nuevo script de migración

## Archivos que NO Requieren Cambios

Los siguientes archivos ya estaban correctos y NO necesitaron modificación:

- ✅ `configuracion_sitio.php` - Ya guardaba con grupo 'general'
- ✅ `index.php` - Ya leía desde grupo 'general'

## Instrucciones de Instalación

### Para Instalaciones Nuevas

No se requiere ninguna acción adicional. La base de datos `database.sql` ya contiene la configuración correcta.

### Para Instalaciones Existentes

Ejecutar el script de migración:

```bash
mysql -u usuario -p nombre_base_datos < database_fix_telefono_contacto.sql
```

O desde phpMyAdmin:
1. Seleccionar la base de datos
2. Ir a la pestaña "SQL"
3. Pegar el contenido de `database_fix_telefono_contacto.sql`
4. Ejecutar

## Pruebas Manuales

### 1. Verificar Guardado del Teléfono

1. Iniciar sesión como administrador
2. Ir a **Configuración** → **Datos del Sitio**
3. En "Información de Contacto", ingresar un teléfono (ej: 442-123-4567)
4. Hacer clic en "Guardar Cambios"
5. Verificar mensaje de éxito: "Configuración actualizada exitosamente"
6. Recargar la página
7. ✅ Verificar que el teléfono ingresado se muestra en el campo

### 2. Verificar Visualización en Sitio Público

1. Abrir el sitio público (frontend)
2. Desplazarse hasta el footer (pie de página)
3. Buscar la sección "Contacto"
4. ✅ Verificar que el teléfono se muestra correctamente con el formato:
   ```
   📞 442-123-4567
   ```

### 3. Verificar Campo Dirección

1. En **Configuración** → **Datos del Sitio**
2. Ingresar una dirección (ej: "Av. Principal 123, Querétaro")
3. Guardar cambios
4. ✅ Verificar que se guarda correctamente

### 4. Verificación en Base de Datos

Ejecutar la siguiente consulta para verificar que los datos están en el grupo correcto:

```sql
SELECT clave, valor, grupo 
FROM configuracion 
WHERE clave IN ('telefono_contacto', 'direccion', 'email_sistema');
```

**Resultado esperado:**
```
| clave              | valor                  | grupo   |
|--------------------|------------------------|---------|
| telefono_contacto  | 442-123-4567          | general |
| direccion          | Av. Principal 123...  | general |
| email_sistema      | contacto@sitio.mx     | general |
```

## Ubicaciones en el Código

### Guardado del Teléfono
- **Archivo:** `configuracion_sitio.php`
- **Líneas:** 20-21 (recepción POST), 151 (guardado)

### Visualización en Sitio Público
- **Archivo:** `index.php`
- **Líneas:** 95 (carga config), 113 (asignación variable), 1112 (display en footer)

## Compatibilidad

✅ Esta solución es compatible con:
- Nuevas instalaciones
- Instalaciones existentes (con script de migración)
- No rompe ninguna funcionalidad existente
- Mantiene el mismo flujo de trabajo

## Notas Técnicas

- El cambio es **minimal**: solo se ajustó el grupo en la base de datos
- No se modificó lógica de negocio
- No se agregaron nuevas dependencias
- La funcionalidad del resto del sistema permanece intacta
