-- =====================================================
-- Mejoras al Portal - Configuración Logo y Slider
-- =====================================================

-- Agregar configuración para modo de logo (imagen o texto)
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'modo_logo' as clave, 'imagen' as valor, 'texto' as tipo, 'general' as grupo, 'Modo de visualización del logo:  imagen o texto' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'modo_logo'
) LIMIT 1;

-- Agregar configuración para tipo de slider
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_tipo' as clave, 'estatico' as valor, 'texto' as tipo, 'general' as grupo, 'Tipo de slider: estatico, noticias, mixto' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_tipo'
) LIMIT 1;

-- Agregar configuración para número de slides en slider
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_cantidad' as clave, '3' as valor, 'numero' as tipo, 'general' as grupo, 'Cantidad de elementos en el slider' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_cantidad'
) LIMIT 1;

-- Agregar configuración para autoplay del slider
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_autoplay' as clave, '1' as valor, 'booleano' as tipo, 'general' as grupo, 'Activar autoplay del slider' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_autoplay'
) LIMIT 1;

-- Agregar configuración para intervalo de autoplay (en segundos)
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_intervalo' as clave, '5000' as valor, 'numero' as tipo, 'general' as grupo, 'Intervalo de autoplay en milisegundos' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_intervalo'
) LIMIT 1;

-- Agregar campo para marcar imágenes de slider en pagina_inicio
-- Verificar si la columna existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = 'pagina_inicio';
SET @columnname = 'imagen_slider';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `pagina_inicio` ADD COLUMN `imagen_slider` VARCHAR(500) DEFAULT NULL AFTER `imagen`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar campo para vincular noticias al slider
SET @columnname = 'noticia_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `pagina_inicio` ADD COLUMN `noticia_id` INT(11) DEFAULT NULL AFTER `imagen_slider`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar índice para noticia_id si no existe
SET @dbname = DATABASE();
SET @tablename = 'pagina_inicio';
SET @indexname = 'noticia_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA. STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `pagina_inicio` ADD KEY `noticia_id` (`noticia_id`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
