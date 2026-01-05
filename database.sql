-- =====================================================
-- Base de Datos: Sistema de Gestión de Contenidos
-- Versión: 1.0
-- Tecnología: MySQL 5.7+
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =====================================================
-- Tabla: usuarios
-- Descripción: Gestión de usuarios del sistema
-- =====================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `intentos_fallidos` int(11) DEFAULT 0,
  `ultimo_acceso` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: roles
-- Descripción: Roles del sistema con permisos
-- =====================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `permisos` text,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: logs_acceso
-- Descripción: Registro de accesos al sistema
-- =====================================================
CREATE TABLE IF NOT EXISTS `logs_acceso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `accion` varchar(50) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `exitoso` tinyint(1) DEFAULT 1,
  `mensaje` text,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: categorias
-- Descripción: Categorías y secciones del portal
-- =====================================================
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descripcion` text,
  `padre_id` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `visible` tinyint(1) DEFAULT 1,
  `editor_responsable_id` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `padre_id` (`padre_id`),
  KEY `editor_responsable_id` (`editor_responsable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: noticias
-- Descripción: Contenido de noticias del portal
-- =====================================================
CREATE TABLE IF NOT EXISTS `noticias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `contenido` longtext NOT NULL,
  `resumen` text,
  `tags` varchar(500) DEFAULT NULL,
  `autor_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `imagen_destacada` varchar(255) DEFAULT NULL,
  `estado` enum('borrador','revision','aprobado','publicado','rechazado','archivado') DEFAULT 'borrador',
  `destacado` tinyint(1) DEFAULT 0,
  `orden_destacado` int(11) DEFAULT 0,
  `visitas` int(11) DEFAULT 0,
  `permitir_comentarios` tinyint(1) DEFAULT 1,
  `fecha_publicacion` datetime DEFAULT NULL,
  `fecha_programada` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modificado_por` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `autor_id` (`autor_id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `estado` (`estado`),
  KEY `fecha_publicacion` (`fecha_publicacion`),
  KEY `destacado` (`destacado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: noticias_versiones
-- Descripción: Versionado de contenido
-- =====================================================
CREATE TABLE IF NOT EXISTS `noticias_versiones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticia_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `contenido` longtext NOT NULL,
  `version` int(11) NOT NULL,
  `modificado_por` int(11) NOT NULL,
  `comentario` text,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `noticia_id` (`noticia_id`),
  KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: multimedia
-- Descripción: Gestión de archivos multimedia
-- =====================================================
CREATE TABLE IF NOT EXISTS `multimedia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `tipo` enum('imagen','video','documento') DEFAULT 'imagen',
  `ruta` varchar(500) NOT NULL,
  `carpeta` varchar(255) DEFAULT 'general',
  `tamanio` int(11) DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text,
  `alt_text` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_subida` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: noticias_multimedia
-- Descripción: Relación noticias-multimedia (galería)
-- =====================================================
CREATE TABLE IF NOT EXISTS `noticias_multimedia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticia_id` int(11) NOT NULL,
  `multimedia_id` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `es_destacada` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `noticia_id` (`noticia_id`),
  KEY `multimedia_id` (`multimedia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: seo_metadata
-- Descripción: Metadatos SEO para noticias
-- =====================================================
CREATE TABLE IF NOT EXISTS `seo_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticia_id` int(11) NOT NULL,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `og_title` varchar(100) DEFAULT NULL,
  `og_description` varchar(200) DEFAULT NULL,
  `og_image` varchar(500) DEFAULT NULL,
  `twitter_card` varchar(50) DEFAULT 'summary_large_image',
  `canonical_url` varchar(500) DEFAULT NULL,
  `noindex` tinyint(1) DEFAULT 0,
  `nofollow` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `noticia_id` (`noticia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: comentarios
-- Descripción: Comentarios de noticias
-- =====================================================
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticia_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contenido` text NOT NULL,
  `ip` varchar(45) NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado','spam') DEFAULT 'pendiente',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `moderado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `noticia_id` (`noticia_id`),
  KEY `estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: banners
-- Descripción: Gestión de publicidad y banners
-- =====================================================
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('imagen','html','script') DEFAULT 'imagen',
  `contenido` text NOT NULL,
  `url_destino` varchar(500) DEFAULT NULL,
  `posicion` varchar(50) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `impresiones` int(11) DEFAULT 0,
  `clics` int(11) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: configuracion
-- Descripción: Configuración general del sistema
-- =====================================================
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text,
  `tipo` varchar(50) DEFAULT 'texto',
  `grupo` varchar(50) DEFAULT 'general',
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: logs_auditoria
-- Descripción: Auditoría de acciones administrativas
-- =====================================================
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `tabla` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `datos_anteriores` text,
  `datos_nuevos` text,
  `ip` varchar(45) NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `modulo` (`modulo`),
  KEY `fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabla: workflow_comentarios
-- Descripción: Comentarios del flujo editorial
-- =====================================================
CREATE TABLE IF NOT EXISTS `workflow_comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticia_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `estado_previo` varchar(20) DEFAULT NULL,
  `estado_nuevo` varchar(20) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `noticia_id` (`noticia_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DATOS DE EJEMPLO - Estado de Querétaro
-- =====================================================

-- Insertar Roles
INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`) VALUES
(1, 'Super Administrador', 'Control total del sistema', '["all"]'),
(2, 'Editor General', 'Gestión completa de contenidos', '["noticias","categorias","multimedia","usuarios.ver"]'),
(3, 'Editor de Sección', 'Gestión de contenidos de su sección', '["noticias.seccion","multimedia"]'),
(4, 'Redactor', 'Creación y edición de borradores', '["noticias.crear","noticias.editar.propias"]'),
(5, 'Colaborador', 'Creación de borradores para revisión', '["noticias.crear.borrador"]'),
(6, 'Administrador Técnico', 'Configuración técnica del sistema', '["configuracion","logs","usuarios"]');

-- Insertar Usuario Administrador por defecto
-- Password: admin123 (debe cambiarse en producción)
INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `email`, `password`, `rol_id`, `activo`) VALUES
(1, 'Administrador', 'Sistema', 'admin@gestorcontenidos.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

-- Insertar Categorías de ejemplo para Querétaro
INSERT INTO `categorias` (`id`, `nombre`, `slug`, `descripcion`, `padre_id`, `orden`, `visible`) VALUES
(1, 'Política', 'politica', 'Noticias políticas de Querétaro', NULL, 1, 1),
(2, 'Economía', 'economia', 'Economía y negocios en el estado', NULL, 2, 1),
(3, 'Seguridad', 'seguridad', 'Seguridad pública y temas relacionados', NULL, 3, 1),
(4, 'Cultura', 'cultura', 'Eventos culturales y artísticos', NULL, 4, 1),
(5, 'Deportes', 'deportes', 'Deportes y actividades físicas', NULL, 5, 1),
(6, 'Turismo', 'turismo', 'Turismo y lugares de interés', NULL, 6, 1),
(7, 'Educación', 'educacion', 'Educación y desarrollo académico', NULL, 7, 1),
(8, 'Salud', 'salud', 'Salud y bienestar', NULL, 8, 1),
(9, 'Santiago de Querétaro', 'santiago-queretaro', 'Capital del estado', 1, 1, 1),
(10, 'Zona Metropolitana', 'zona-metropolitana', 'Noticias de la zona metropolitana', 1, 2, 1),
(11, 'Municipios', 'municipios', 'Noticias de otros municipios', 1, 3, 1);

-- Insertar Configuración inicial
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) VALUES
('nombre_sitio', 'Portal de Noticias Querétaro', 'texto', 'general', 'Nombre del sitio web'),
('email_sistema', 'noreply@gestorcontenidos.mx', 'email', 'general', 'Email principal del sistema'),
('zona_horaria', 'America/Mexico_City', 'texto', 'general', 'Zona horaria del sistema'),
('telefono_contacto', '442-123-4567', 'texto', 'general', 'Teléfono de contacto'),
('direccion', '', 'texto', 'general', 'Dirección de contacto'),
('noticias_por_pagina', '20', 'numero', 'contenido', 'Noticias por página'),
('permitir_registro', '0', 'booleano', 'usuarios', 'Permitir registro público'),
('moderacion_comentarios', '1', 'booleano', 'comentarios', 'Moderación automática de comentarios'),
('google_analytics_id', '', 'texto', 'seo', 'ID de Google Analytics'),
('facebook_url', '', 'texto', 'redes_sociales', 'URL de Facebook'),
('twitter_url', '', 'texto', 'redes_sociales', 'URL de Twitter'),
('instagram_url', '', 'texto', 'redes_sociales', 'URL de Instagram'),
('horario_atencion', 'Lunes a Viernes 9:00 - 18:00', 'texto', 'contacto', 'Horario de atención'),
('color_primario', '#1e40af', 'color', 'diseno', 'Color primario del sistema'),
('color_secundario', '#3b82f6', 'color', 'diseno', 'Color secundario del sistema');

-- Insertar noticia de ejemplo
INSERT INTO `noticias` (`id`, `titulo`, `subtitulo`, `slug`, `contenido`, `resumen`, `autor_id`, `categoria_id`, `estado`, `destacado`, `fecha_publicacion`) VALUES
(1, 'Bienvenido al Sistema de Gestión de Contenidos', 'Tu plataforma profesional para gestionar noticias', 'bienvenido-sistema-gestion-contenidos', '<p>Este es el Sistema Administrativo de Gestión de Contenidos para tu portal de noticias.</p><p>Características principales:</p><ul><li>Gestión completa de usuarios y roles</li><li>Edición avanzada de noticias</li><li>Sistema de categorías jerárquico</li><li>Optimización SEO</li><li>Y mucho más...</li></ul>', 'Sistema profesional de gestión de contenidos para portales de noticias', 1, 1, 'publicado', 1, NOW());

-- =====================================================
-- RELACIONES DE CLAVES FORÁNEAS
-- =====================================================
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT;

ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`padre_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categorias_ibfk_2` FOREIGN KEY (`editor_responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `noticias_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT;

ALTER TABLE `noticias_versiones`
  ADD CONSTRAINT `noticias_versiones_ibfk_1` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE;

ALTER TABLE `multimedia`
  ADD CONSTRAINT `multimedia_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;

ALTER TABLE `noticias_multimedia`
  ADD CONSTRAINT `noticias_multimedia_ibfk_1` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `noticias_multimedia_ibfk_2` FOREIGN KEY (`multimedia_id`) REFERENCES `multimedia` (`id`) ON DELETE CASCADE;

ALTER TABLE `seo_metadata`
  ADD CONSTRAINT `seo_metadata_ibfk_1` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE;

ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE;

ALTER TABLE `logs_acceso`
  ADD CONSTRAINT `logs_acceso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

ALTER TABLE `logs_auditoria`
  ADD CONSTRAINT `logs_auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;

ALTER TABLE `workflow_comentarios`
  ADD CONSTRAINT `workflow_comentarios_ibfk_1` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workflow_comentarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;
