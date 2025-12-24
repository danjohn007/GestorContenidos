-- =====================================================
-- Actualizaciones de Base de Datos
-- =====================================================

-- Agregar campo de tags/palabras clave a noticias
ALTER TABLE `noticias` 
ADD COLUMN `tags` VARCHAR(500) DEFAULT NULL AFTER `resumen`;

-- Crear tabla para gestión de página de inicio
CREATE TABLE IF NOT EXISTS `pagina_inicio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seccion` varchar(50) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `contenido` text,
  `imagen` varchar(500) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `seccion` (`seccion`),
  KEY `orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla para enlaces de redes sociales
CREATE TABLE IF NOT EXISTS `redes_sociales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `icono` varchar(50) NOT NULL,
  `url` varchar(500) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar redes sociales por defecto (Actualizar con URLs reales antes de usar)
-- Desactivadas por defecto - Habilitar después de configurar URLs reales
INSERT INTO `redes_sociales` (`nombre`, `icono`, `url`, `orden`, `activo`) VALUES
('Facebook', 'fab fa-facebook', '', 1, 0),
('Twitter', 'fab fa-twitter', '', 2, 0),
('Instagram', 'fab fa-instagram', '', 3, 0),
('YouTube', 'fab fa-youtube', '', 4, 0);

-- Insertar datos por defecto para slider
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `orden`, `activo`) VALUES
('slider', 'Bienvenido al Portal de Noticias', 'Tu fuente confiable de información', 'Mantente informado con las últimas noticias de tu región', 1, 1),
('slider', 'Noticias de Última Hora', 'Información al momento', 'Cobertura completa de los eventos más importantes', 2, 1),
('slider', 'Contenido de Calidad', 'Periodismo responsable', 'Información verificada y de calidad para ti', 3, 1);

-- Insertar datos por defecto para sección de contacto
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `orden`, `activo`) VALUES
('contacto', '¿Tienes una noticia?', 'Contáctanos', 'Email: contacto@portalqueretaro.mx<br>Tel: 442-123-4567<br>Dirección: Querétaro, México', 1, 1);

-- Insertar datos por defecto para accesos directos
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `url`, `orden`, `activo`) VALUES
('acceso_directo', 'Noticias Destacadas', 'Las más importantes', 'fas fa-star', 'index.php?destacadas=1', 1, 1),
('acceso_directo', 'Última Hora', 'Lo más reciente', 'fas fa-clock', 'index.php?recientes=1', 2, 1),
('acceso_directo', 'Categorías', 'Explora por tema', 'fas fa-th-large', 'categorias.php', 3, 1),
('acceso_directo', 'Multimedia', 'Fotos y videos', 'fas fa-images', 'multimedia.php', 4, 1);

-- Agregar nuevas configuraciones para TinyMCE y otros ajustes
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) VALUES
('tinymce_api_key', '', 'texto', 'general', 'API Key de TinyMCE para editor de texto enriquecido'),
('slogan_sitio', '', 'texto', 'general', 'Slogan del sitio web'),
('descripcion_sitio', '', 'texto', 'general', 'Descripción breve del sitio para SEO'),
('logo_sitio', '', 'texto', 'general', 'Ruta del logo del sitio'),
('direccion', '', 'texto', 'contacto', 'Dirección física'),
('color_acento', '#10b981', 'color', 'diseno', 'Color de acento del sistema'),
('color_texto', '#1f2937', 'color', 'diseno', 'Color principal del texto'),
('color_fondo', '#f3f4f6', 'color', 'diseno', 'Color de fondo del sitio'),
('fuente_principal', 'system-ui', 'texto', 'diseno', 'Fuente principal del sistema'),
('fuente_titulos', 'system-ui', 'texto', 'diseno', 'Fuente para títulos'),
('smtp_host', '', 'texto', 'correo', 'Servidor SMTP'),
('smtp_port', '587', 'texto', 'correo', 'Puerto SMTP'),
('smtp_usuario', '', 'texto', 'correo', 'Usuario SMTP'),
('smtp_password', '', 'texto', 'correo', 'Contraseña SMTP'),
('smtp_seguridad', 'tls', 'texto', 'correo', 'Seguridad SMTP (tls/ssl/none)'),
('email_remitente', '', 'texto', 'correo', 'Email remitente'),
('nombre_remitente', '', 'texto', 'correo', 'Nombre remitente'),
('google_search_console', '', 'texto', 'seo', 'Código de verificación de Google Search Console'),
('facebook_app_id', '', 'texto', 'seo', 'Facebook App ID para compartir'),
('meta_keywords_default', '', 'texto', 'seo', 'Palabras clave por defecto'),
('meta_description_default', '', 'texto', 'seo', 'Descripción meta por defecto')
ON DUPLICATE KEY UPDATE clave=clave;

