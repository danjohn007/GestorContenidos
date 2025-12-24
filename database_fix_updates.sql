-- =====================================================
-- Actualizaciones para resolver errores reportados
-- Fecha: 2024-12-24
-- =====================================================

-- 1. Asegurar que existe la columna tags en la tabla noticias
SET @dbname = DATABASE();
SET @tablename = 'noticias';
SET @columnname = 'tags';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(500) DEFAULT NULL AFTER `resumen`")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Asegurar que existen las configuraciones necesarias para el sistema
-- Insertar configuraciones si no existen (usando ON DUPLICATE KEY para evitar errores)
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) VALUES
('tinymce_api_key', '', 'texto', 'general', 'API Key de TinyMCE para editor de texto enriquecido'),
('slogan_sitio', '', 'texto', 'general', 'Slogan del sitio web'),
('descripcion_sitio', '', 'texto', 'general', 'Descripción breve del sitio para SEO'),
('logo_sitio', '', 'texto', 'general', 'Ruta del logo del sitio'),
('direccion', '', 'texto', 'contacto', 'Dirección física'),
('color_primario', '#1e40af', 'color', 'diseno', 'Color primario del sistema'),
('color_secundario', '#3b82f6', 'color', 'diseno', 'Color secundario del sistema'),
('color_acento', '#10b981', 'color', 'diseno', 'Color de acento del sistema'),
('color_texto', '#1f2937', 'color', 'diseno', 'Color principal del texto'),
('color_fondo', '#f3f4f6', 'color', 'diseno', 'Color de fondo del sitio'),
('fuente_principal', 'system-ui', 'texto', 'diseno', 'Fuente principal del sistema'),
('fuente_titulos', 'system-ui', 'texto', 'diseno', 'Fuente para títulos')
ON DUPLICATE KEY UPDATE clave=clave;

-- 3. Verificar integridad de datos: actualizar noticias con tags NULL a cadena vacía
-- Esto previene errores de PHP al trabajar con valores NULL
UPDATE `noticias` SET `tags` = '' WHERE `tags` IS NULL;

-- Confirmación
SELECT 'Actualizaciones aplicadas exitosamente' AS status;
