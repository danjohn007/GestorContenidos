-- =====================================================
-- Actualización de Base de Datos - Noticias Destacadas (Solo Imágenes)
-- Fecha: 2026-01-10
-- Descripción: Módulo de noticias destacadas visuales (solo imágenes)
-- =====================================================

-- Crear tabla para noticias destacadas visuales
CREATE TABLE IF NOT EXISTS `noticias_destacadas_imagenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL COMMENT 'Título descriptivo para administración',
  `imagen_url` varchar(500) NOT NULL COMMENT 'Ruta de la imagen',
  `url_destino` varchar(500) DEFAULT NULL COMMENT 'URL a la que redirige (puede ser una noticia)',
  `noticia_id` int(11) DEFAULT NULL COMMENT 'ID de noticia relacionada (opcional)',
  `ubicacion` enum('bajo_slider','entre_bloques','antes_footer') DEFAULT 'bajo_slider' COMMENT 'Ubicación donde se mostrará',
  `vista` enum('grid','carousel') DEFAULT 'grid' COMMENT 'Tipo de vista (grid o carrusel)',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de aparición',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Si está activo o no',
  `fecha_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio de vigencia (opcional)',
  `fecha_fin` date DEFAULT NULL COMMENT 'Fecha de fin de vigencia (opcional)',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `noticia_id` (`noticia_id`),
  KEY `ubicacion` (`ubicacion`),
  KEY `activo` (`activo`),
  KEY `orden` (`orden`),
  CONSTRAINT `fk_noticias_destacadas_noticia` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Noticias destacadas visuales (solo imágenes)';

-- Crear índice compuesto para mejor rendimiento
CREATE INDEX `idx_ubicacion_activo_orden` ON `noticias_destacadas_imagenes` (`ubicacion`, `activo`, `orden`);

-- Insertar algunos ejemplos desactivados
INSERT INTO `noticias_destacadas_imagenes` (`titulo`, `imagen_url`, `url_destino`, `ubicacion`, `vista`, `orden`, `activo`) VALUES
('Destacada Ejemplo 1', '', '#', 'bajo_slider', 'grid', 1, 0),
('Destacada Ejemplo 2', '', '#', 'bajo_slider', 'grid', 2, 0),
('Destacada Ejemplo 3', '', '#', 'entre_bloques', 'grid', 1, 0);
