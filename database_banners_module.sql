-- =====================================================
-- Actualización de Base de Datos - Módulo de Banners Completo
-- Fecha: 2026-01-03
-- Descripción: Actualiza la tabla banners para soporte completo
-- ADVERTENCIA: Este script eliminará la tabla banners existente
-- Asegúrese de hacer un backup antes de ejecutar en producción
-- =====================================================

-- Respaldar datos existentes si la tabla existe
CREATE TABLE IF NOT EXISTS `banners_backup` LIKE `banners`;
INSERT INTO `banners_backup` SELECT * FROM `banners` WHERE EXISTS (SELECT 1 FROM `banners`);

-- Eliminar tabla existente
DROP TABLE IF EXISTS `banners`;

-- Crear tabla banners con estructura completa
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo del banner',
  `tipo` enum('imagen','html','script') DEFAULT 'imagen' COMMENT 'Tipo de contenido del banner',
  `imagen_url` varchar(500) DEFAULT NULL COMMENT 'Ruta de la imagen del banner',
  `url_destino` varchar(500) DEFAULT NULL COMMENT 'URL a la que redirige el banner',
  `ubicacion` enum('inicio','sidebar','footer','dentro_notas','entre_secciones') NOT NULL COMMENT 'Ubicación donde se mostrará el banner',
  `orientacion` enum('horizontal','vertical') DEFAULT 'horizontal' COMMENT 'Orientación del banner',
  `dispositivo` enum('todos','desktop','movil') DEFAULT 'todos' COMMENT 'En qué dispositivos se muestra',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de aparición',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Si el banner está activo o no',
  `fecha_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio de vigencia (opcional)',
  `fecha_fin` date DEFAULT NULL COMMENT 'Fecha de fin de vigencia (opcional)',
  `rotativo` tinyint(1) DEFAULT 0 COMMENT 'Si el banner es parte de un carrusel rotativo',
  `impresiones` int(11) DEFAULT 0 COMMENT 'Contador de impresiones',
  `clics` int(11) DEFAULT 0 COMMENT 'Contador de clics',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ubicacion` (`ubicacion`),
  KEY `activo` (`activo`),
  KEY `fecha_inicio` (`fecha_inicio`),
  KEY `fecha_fin` (`fecha_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Gestión de banners publicitarios';

-- Insertar algunos banners de ejemplo (opcional - desactivados por defecto)
INSERT INTO `banners` (`nombre`, `tipo`, `imagen_url`, `url_destino`, `ubicacion`, `orientacion`, `dispositivo`, `orden`, `activo`) VALUES
('Banner Ejemplo Sidebar', 'imagen', NULL, '#', 'sidebar', 'vertical', 'todos', 1, 0),
('Banner Ejemplo Footer', 'imagen', NULL, '#', 'footer', 'horizontal', 'todos', 1, 0),
('Banner Ejemplo Inicio', 'imagen', NULL, '#', 'inicio', 'horizontal', 'todos', 1, 0);

-- Nota: Para restaurar datos antiguos si es necesario, ejecute:
-- INSERT INTO `banners` SELECT * FROM `banners_backup`;
