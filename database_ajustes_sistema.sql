-- =====================================================
-- Ajustes y Correcciones del Sistema
-- Fecha: 2026-01-06
-- Descripción: Correcciones solicitadas para sidebar, categorías, banners, videos, etc.
-- =====================================================

-- =====================================================
-- 1. Configuración para Sidebar - Accesos Rápidos
-- =====================================================
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
VALUES 
('mostrar_accesos_rapidos', '1', 'boolean', 'general', 'Mostrar bloque de accesos rápidos en el sidebar público'),
('logo_footer', '', 'imagen', 'general', 'Logo específico para mostrar en el footer del sitio')
ON DUPLICATE KEY UPDATE 
`descripcion` = VALUES(`descripcion`);

-- =====================================================
-- 2. Actualizar estructura de banners si es necesario
-- =====================================================
-- Verificar y agregar campo rotativo si no existe
SET @exist_rotativo := (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'banners' 
                        AND column_name = 'rotativo');

SET @sql_rotativo := IF(@exist_rotativo > 0, 
                        'SELECT "La columna rotativo ya existe" AS message',
                        'ALTER TABLE `banners` ADD COLUMN `rotativo` tinyint(1) DEFAULT 0 COMMENT "Banner con múltiples imágenes (carrusel)" AFTER `fecha_fin`');

PREPARE stmt FROM @sql_rotativo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar que existe la estructura actualizada de banners
SET @exist_imagen_url := (SELECT COUNT(*) FROM information_schema.columns 
                          WHERE table_schema = DATABASE() 
                          AND table_name = 'banners' 
                          AND column_name = 'imagen_url');

-- Si no existe imagen_url, actualizar la estructura antigua
SET @sql_banner_structure := IF(@exist_imagen_url = 0, 
    'ALTER TABLE `banners` 
     CHANGE COLUMN `contenido` `imagen_url` varchar(500) DEFAULT NULL COMMENT "URL de la imagen del banner",
     ADD COLUMN `ubicacion` varchar(50) DEFAULT "sidebar" COMMENT "Ubicación del banner" AFTER `url_destino`,
     ADD COLUMN `orientacion` varchar(20) DEFAULT "horizontal" COMMENT "Orientación del banner" AFTER `ubicacion`,
     ADD COLUMN `dispositivo` varchar(20) DEFAULT "todos" COMMENT "Dispositivo de visualización" AFTER `orientacion`,
     ADD COLUMN `tamano_display` varchar(20) DEFAULT "auto" COMMENT "Tamaño de display del banner" AFTER `dispositivo`,
     ADD COLUMN `orden` int(11) DEFAULT 0 COMMENT "Orden de visualización" AFTER `tamano_display`,
     DROP COLUMN `posicion`,
     DROP COLUMN `categoria_id`',
    'SELECT "La estructura de banners ya está actualizada" AS message');

PREPARE stmt FROM @sql_banner_structure;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 3. Crear tabla banner_imagenes si no existe (para carruseles)
-- =====================================================
CREATE TABLE IF NOT EXISTS `banner_imagenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL COMMENT 'ID del banner principal',
  `imagen_url` varchar(500) NOT NULL COMMENT 'Ruta de la imagen',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de aparición en el carrusel',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Si la imagen está activa',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `banner_id` (`banner_id`),
  KEY `orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Imágenes adicionales para banners rotativos';

-- Agregar foreign key si no existe
SET @exist_fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
                  WHERE CONSTRAINT_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'banner_imagenes' 
                  AND CONSTRAINT_NAME = 'fk_banner_imagenes_banner');

SET @sql_fk := IF(@exist_fk > 0,
               'SELECT "Foreign key ya existe" AS message',
               'ALTER TABLE `banner_imagenes` ADD CONSTRAINT `fk_banner_imagenes_banner` FOREIGN KEY (`banner_id`) REFERENCES `banners` (`id`) ON DELETE CASCADE');

PREPARE stmt FROM @sql_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índice compuesto para mejor rendimiento si no existe
SET @exist_idx := (SELECT COUNT(*) FROM information_schema.statistics 
                   WHERE table_schema = DATABASE() 
                   AND table_name = 'banner_imagenes' 
                   AND index_name = 'idx_banner_activo_orden');

SET @sql_idx := IF(@exist_idx > 0,
                'SELECT "Índice ya existe" AS message',
                'CREATE INDEX `idx_banner_activo_orden` ON `banner_imagenes` (`banner_id`, `activo`, `orden`)');

PREPARE stmt FROM @sql_idx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 4. Agregar sección de logo_footer en pagina_inicio
-- =====================================================
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'logo_footer' as seccion, 'Logo del Footer' as titulo, 'Logo específico para el pie de página' as subtitulo, '' as contenido, '' as imagen, '#' as url, 1 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'logo_footer' AND orden = 1
) LIMIT 1;

-- =====================================================
-- 5. Verificar campos de video en noticias
-- =====================================================
-- Estos campos ya deberían existir por database_system_improvements.sql
-- pero lo verificamos por si acaso

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
