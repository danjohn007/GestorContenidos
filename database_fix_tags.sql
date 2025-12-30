-- =====================================================
-- Corrección: Agregar campo tags a tabla noticias
-- Este script corrige el problema donde el campo tags
-- no existe en instalaciones antiguas
-- =====================================================

-- Agregar campo tags a noticias si no existe
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
  "SELECT 'El campo tags ya existe en la tabla noticias' as mensaje",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(500) DEFAULT NULL AFTER `resumen`")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
