# Flujo de Datos: Teléfono de Contacto

## ANTES (Con Error) ❌

```
┌─────────────────────────────────────────────────────┐
│         Base de Datos Inicial (database.sql)       │
│  telefono_contacto = '442-123-4567'                 │
│  grupo = 'contacto'  ❌                             │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│      configuracion_sitio.php (Admin Panel)          │
│                                                     │
│  POST: telefono_contacto = '555-1234'              │
│       ↓                                             │
│  setOrCreate('telefono_contacto', valor,            │
│              'texto', 'general', '')  ✅            │
│       ↓                                             │
│  CREA NUEVO REGISTRO:                               │
│    telefono_contacto = '555-1234'                   │
│    grupo = 'general'  ✅                            │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│          Base de Datos (Después de Guardar)        │
│                                                     │
│  Registro 1:                                        │
│    telefono_contacto = '442-123-4567'              │
│    grupo = 'contacto'  ❌                           │
│                                                     │
│  Registro 2: (Nuevo)                                │
│    telefono_contacto = '555-1234'                  │
│    grupo = 'general'  ✅                            │
│                                                     │
│  ⚠️ PROBLEMA: Dos registros con misma clave         │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│           index.php (Sitio Público)                 │
│                                                     │
│  $configGeneral = getByGrupo('general')            │
│       ↓                                             │
│  $telefonoContacto = $configGeneral                 │
│                      ['telefono_contacto']          │
│       ↓                                             │
│  PUEDE LEER: '555-1234' ó valor por defecto        │
│  (Depende del registro que encuentre primero)       │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│                 Footer (Sitio Público)              │
│  📞 555-1234  ó  📞 442-123-4567                    │
│  (Inconsistente)  ❌                                │
└─────────────────────────────────────────────────────┘
```

## DESPUÉS (Con Fix) ✅

```
┌─────────────────────────────────────────────────────┐
│         Base de Datos Inicial (database.sql)       │
│  telefono_contacto = '442-123-4567'                 │
│  grupo = 'general'  ✅  [CORREGIDO]                 │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│      configuracion_sitio.php (Admin Panel)          │
│                                                     │
│  POST: telefono_contacto = '555-1234'              │
│       ↓                                             │
│  setOrCreate('telefono_contacto', valor,            │
│              'texto', 'general', '')  ✅            │
│       ↓                                             │
│  ACTUALIZA REGISTRO EXISTENTE:                      │
│    telefono_contacto = '555-1234'                  │
│    grupo = 'general'  ✅                            │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│          Base de Datos (Después de Guardar)        │
│                                                     │
│  Registro único:  ✅                                │
│    telefono_contacto = '555-1234'                  │
│    grupo = 'general'  ✅                            │
│                                                     │
│  ✅ CORRECTO: Un solo registro                      │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│           index.php (Sitio Público)                 │
│                                                     │
│  $configGeneral = getByGrupo('general')            │
│       ↓                                             │
│  $telefonoContacto = $configGeneral                 │
│                      ['telefono_contacto']          │
│       ↓                                             │
│  LEE CORRECTAMENTE: '555-1234'  ✅                 │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│                 Footer (Sitio Público)              │
│  📞 555-1234  ✅                                     │
│  (Consistente y actualizado)                        │
└─────────────────────────────────────────────────────┘
```

## Explicación del Problema

### Causa Raíz
El método `setOrCreate()` en `Configuracion.php` verifica si existe un registro con la misma `clave`:

```php
public function setOrCreate($clave, $valor, $tipo = 'texto', $grupo = 'general', $descripcion = null) {
    // Verificar si existe
    $existing = $this->get($clave);  // Solo busca por CLAVE, no por GRUPO
    
    if ($existing !== null) {
        return $this->set($clave, $valor);  // Actualiza el existente
    }
    
    // Si no existe, crea nuevo
    // ...
}
```

**Problema:** `get()` busca por clave SIN considerar el grupo:
```php
public function get($clave, $default = null) {
    $query = "SELECT valor FROM {$this->table} WHERE clave = :clave";
    // ⚠️ No filtra por grupo!
}
```

Dado que la tabla tiene UNIQUE KEY en `clave`, solo puede existir un registro por clave. Sin embargo:
- Si existe `telefono_contacto` con grupo='contacto', `get()` lo encuentra y `setOrCreate()` lo actualiza
- Pero `getByGrupo('general')` usado en index.php NO lo encuentra porque busca específicamente por grupo='general'
- Resultado: El valor se guarda pero no se muestra en el sitio público
- La solución correcta es mantener TODOS los campos de configuración del sitio en el mismo grupo para consistencia

## Solución Implementada

### 1. Estandarización de Grupos
Todos los campos de "Información de Contacto" ahora usan el grupo `'general'`:
- ✅ nombre_sitio → general
- ✅ email_sistema → general  
- ✅ telefono_contacto → general ← CORREGIDO
- ✅ direccion → general ← AÑADIDO
- ✅ zona_horaria → general

### 2. Migración para Instalaciones Existentes
Script `database_fix_telefono_contacto.sql` actualiza el grupo:
```sql
UPDATE `configuracion` 
SET `grupo` = 'general' 
WHERE `clave` = 'telefono_contacto';
```

## Beneficios del Fix

✅ Consistencia en el código
✅ Un solo registro por configuración
✅ Actualizaciones funcionan correctamente
✅ El sitio público muestra el valor correcto
✅ Sin cambios en la lógica de negocio
✅ Solución mínima y precisa
