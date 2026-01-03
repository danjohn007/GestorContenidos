-- =====================================================
-- Actualización de Base de Datos - Soporte de Galería para Banners Rotativos
-- Fecha: 2026-01-03
-- Descripción: Añade tabla para múltiples imágenes en banners rotativos
-- =====================================================

-- Crear tabla para almacenar múltiples imágenes de banners rotativos
CREATE TABLE IF NOT EXISTS `banner_imagenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL COMMENT 'ID del banner principal',
  `imagen_url` varchar(500) NOT NULL COMMENT 'Ruta de la imagen',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de aparición en el carrusel',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Si la imagen está activa',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `banner_id` (`banner_id`),
  KEY `orden` (`orden`),
  CONSTRAINT `fk_banner_imagenes_banner` FOREIGN KEY (`banner_id`) REFERENCES `banners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Imágenes adicionales para banners rotativos';

-- Crear índice compuesto para mejor rendimiento
CREATE INDEX `idx_banner_activo_orden` ON `banner_imagenes` (`banner_id`, `activo`, `orden`);
