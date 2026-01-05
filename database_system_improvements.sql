-- =====================================================
-- Actualizaciones del Sistema
-- Mejoras solicitadas: Footer, Aviso Legal, Videos, etc.
-- =====================================================

-- =====================================================
-- 1. Agregar campos de video a la tabla noticias
-- =====================================================
ALTER TABLE `noticias` 
ADD COLUMN `video_url` varchar(500) DEFAULT NULL COMMENT 'URL de video local' AFTER `imagen_destacada`,
ADD COLUMN `video_youtube` varchar(255) DEFAULT NULL COMMENT 'URL o ID de video de YouTube' AFTER `video_url`,
ADD COLUMN `video_thumbnail` varchar(255) DEFAULT NULL COMMENT 'Thumbnail personalizado para el video' AFTER `video_youtube`;

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
ALTER TABLE `noticias` 
ADD KEY IF NOT EXISTS `idx_fecha_programada` (`fecha_programada`, `estado`);
