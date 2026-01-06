-- =====================================================
-- Actualizaciones del Sistema
-- Mejoras solicitadas: Footer, Aviso Legal, Videos, etc. 
-- =====================================================

-- =====================================================
-- 1. Agregar campos de video a la tabla noticias
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

-- =====================================================
-- 2. Agregar configuraciones para footer y aviso legal
-- =====================================================
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
VALUES 
('texto_footer', '©️ 2026 La Cruda Verdad. Todos los derechos reservados.\n(y los izquierdos, bajo sospecha)', 'textarea', 'general', 'Texto del pie de página del sitio'),
('aviso_legal', '', 'textarea', 'general', 'Contenido de la página de Aviso Legal'),
('mostrar_aviso_legal', '1', 'boolean', 'general', 'Mostrar enlace a Aviso Legal en el footer')
ON DUPLICATE KEY UPDATE 
`descripcion` = VALUES(`descripcion`);

-- =====================================================
-- 3. Verificar índice para fecha_programada
-- =====================================================
-- Agregar índice si no existe para optimizar consultas de noticias programadas
SET @exist_index := (SELECT COUNT(*) FROM information_schema.statistics 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'noticias' 
                     AND index_name = 'idx_fecha_programada');

SET @sql_index := IF(@exist_index > 0, 
                    'SELECT "El índice idx_fecha_programada ya existe" AS message', 
                    'ALTER TABLE `noticias` ADD KEY `idx_fecha_programada` (`fecha_programada`, `estado`)');

PREPARE stmt FROM @sql_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
