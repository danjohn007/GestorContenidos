-- =====================================================
-- Fix Teléfono de Contacto Group
-- =====================================================
-- This script fixes the group for telefono_contacto configuration
-- from 'contacto' to 'general' so it can be properly saved and displayed

-- Update existing telefono_contacto entries to use 'general' group
UPDATE `configuracion` 
SET `grupo` = 'general' 
WHERE `clave` = 'telefono_contacto';

-- If the entry doesn't exist, create it with the correct group
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`)
SELECT 'telefono_contacto', '', 'texto', 'general', 'Teléfono de contacto'
WHERE NOT EXISTS (
    SELECT 1 FROM `configuracion` WHERE `clave` = 'telefono_contacto'
);

-- Update direccion field to also use 'general' group for consistency
UPDATE `configuracion` 
SET `grupo` = 'general' 
WHERE `clave` = 'direccion';

-- If direccion doesn't exist, create it with the correct group
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`)
SELECT 'direccion', '', 'texto', 'general', 'Dirección de contacto'
WHERE NOT EXISTS (
    SELECT 1 FROM `configuracion` WHERE `clave` = 'direccion'
);
