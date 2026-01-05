-- =====================================================
-- Fix Teléfono de Contacto Group
-- =====================================================
-- This script fixes the group for telefono_contacto configuration
-- from 'contacto' to 'general' so it can be properly saved and displayed
-- 
-- Note: The table has UNIQUE KEY on 'clave', so only one record per key exists.
-- The UPDATE will change any existing record's group to 'general'.
-- The INSERT will only execute if no record exists at all.

-- Update existing telefono_contacto entries to use 'general' group
-- This will affect the record regardless of its current group
UPDATE `configuracion` 
SET `grupo` = 'general' 
WHERE `clave` = 'telefono_contacto';

-- If the entry doesn't exist at all, create it with the correct group
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`)
SELECT 'telefono_contacto', '', 'texto', 'general', 'Teléfono de contacto'
WHERE NOT EXISTS (
    SELECT 1 FROM `configuracion` WHERE `clave` = 'telefono_contacto'
);

-- Update direccion field to also use 'general' group for consistency
UPDATE `configuracion` 
SET `grupo` = 'general' 
WHERE `clave` = 'direccion';

-- If direccion doesn't exist at all, create it with the correct group
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`)
SELECT 'direccion', '', 'texto', 'general', 'Dirección de contacto'
WHERE NOT EXISTS (
    SELECT 1 FROM `configuracion` WHERE `clave` = 'direccion'
);
