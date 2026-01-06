-- =====================================================
-- Correcciones de Sincronización de Categorías y Mejoras
-- Fecha: Enero 2026
-- =====================================================

-- =====================================================
-- 1. Agregar configuración de logo del footer
-- =====================================================
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
VALUES 
('logo_footer', '', 'texto', 'general', 'Logo que se muestra en el pie de página del sitio')
ON DUPLICATE KEY UPDATE 
`descripcion` = VALUES(`descripcion`);

-- =====================================================
-- 2. Verificar configuración de accesos rápidos
-- =====================================================
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
VALUES 
('mostrar_accesos_rapidos', '1', 'boolean', 'general', 'Mostrar bloque de accesos rápidos en el sidebar público')
ON DUPLICATE KEY UPDATE 
`descripcion` = VALUES(`descripcion`);

-- =====================================================
-- 3. Campos de video en tabla noticias (verificar existencia)
-- =====================================================
-- Verificar y agregar video_url si no existe
SET @exist_video_url := (SELECT COUNT(*) FROM information_schema.columns 
                         WHERE table_schema = DATABASE() 
                         AND table_name = 'noticias' 
                         AND column_name = 'video_url');

SET @sql_video_url := IF(@exist_video_url > 0, 
                         'SELECT "La columna video_url ya existe" AS message',
                         'ALTER TABLE `noticias` ADD COLUMN `video_url` varchar(500) DEFAULT NULL COMMENT "URL de video local" AFTER `imagen_destacada`');

PREPARE stmt FROM @sql_video_url;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y agregar video_youtube si no existe
SET @exist_video_youtube := (SELECT COUNT(*) FROM information_schema.columns 
                             WHERE table_schema = DATABASE() 
                             AND table_name = 'noticias' 
                             AND column_name = 'video_youtube');

SET @sql_video_youtube := IF(@exist_video_youtube > 0, 
                             'SELECT "La columna video_youtube ya existe" AS message',
                             'ALTER TABLE `noticias` ADD COLUMN `video_youtube` varchar(255) DEFAULT NULL COMMENT "URL o ID de video de YouTube" AFTER `video_url`');

PREPARE stmt FROM @sql_video_youtube;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y agregar video_thumbnail si no existe
SET @exist_video_thumbnail := (SELECT COUNT(*) FROM information_schema.columns 
                               WHERE table_schema = DATABASE() 
                               AND table_name = 'noticias' 
                               AND column_name = 'video_thumbnail');

SET @sql_video_thumbnail := IF(@exist_video_thumbnail > 0, 
                               'SELECT "La columna video_thumbnail ya existe" AS message',
                               'ALTER TABLE `noticias` ADD COLUMN `video_thumbnail` varchar(255) DEFAULT NULL COMMENT "Thumbnail personalizado para el video" AFTER `video_youtube`');

PREPARE stmt FROM @sql_video_thumbnail;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y agregar video_thumbnail_url si no existe (para permitir URL externa)
SET @exist_video_thumbnail_url := (SELECT COUNT(*) FROM information_schema.columns 
                                  WHERE table_schema = DATABASE() 
                                  AND table_name = 'noticias' 
                                  AND column_name = 'video_thumbnail_url');

SET @sql_video_thumbnail_url := IF(@exist_video_thumbnail_url > 0, 
                                  'SELECT "La columna video_thumbnail_url ya existe" AS message',
                                  'ALTER TABLE `noticias` ADD COLUMN `video_thumbnail_url` varchar(500) DEFAULT NULL COMMENT "URL externa del thumbnail de video" AFTER `video_thumbnail`');

PREPARE stmt FROM @sql_video_thumbnail_url;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- FINALIZADO
-- =====================================================
