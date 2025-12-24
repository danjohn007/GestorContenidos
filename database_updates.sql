-- =====================================================
-- Actualizaciones de Base de Datos
-- =====================================================

-- Agregar campo de tags/palabras clave a noticias (solo si no existe)
SET @dbname = DATABASE();
SET @tablename = 'noticias';
SET @columnname = 'tags';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(500) DEFAULT NULL AFTER `resumen`")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

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

-- Insertar redes sociales por defecto (solo si no existen)
INSERT INTO `redes_sociales` (`nombre`, `icono`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'Facebook' as nombre, 'fab fa-facebook' as icono, '' as url, 1 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM `redes_sociales` WHERE nombre = 'Facebook'
) LIMIT 1;

INSERT INTO `redes_sociales` (`nombre`, `icono`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'Twitter' as nombre, 'fab fa-twitter' as icono, '' as url, 2 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM `redes_sociales` WHERE nombre = 'Twitter'
) LIMIT 1;

INSERT INTO `redes_sociales` (`nombre`, `icono`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'Instagram' as nombre, 'fab fa-instagram' as icono, '' as url, 3 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM `redes_sociales` WHERE nombre = 'Instagram'
) LIMIT 1;

INSERT INTO `redes_sociales` (`nombre`, `icono`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'YouTube' as nombre, 'fab fa-youtube' as icono, '' as url, 4 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM `redes_sociales` WHERE nombre = 'YouTube'
) LIMIT 1;

-- Insertar datos por defecto para slider (solo si no existen)
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `orden`, `activo`)
SELECT * FROM (SELECT 'slider' as seccion, 'Bienvenido al Portal de Noticias' as titulo, 'Tu fuente confiable de información' as subtitulo, 'Mantente informado con las últimas noticias de tu región' as contenido, 1 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'slider' AND orden = 1
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `orden`, `activo`)
SELECT * FROM (SELECT 'slider' as seccion, 'Noticias de Última Hora' as titulo, 'Información al momento' as subtitulo, 'Cobertura completa de los eventos más importantes' as contenido, 2 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'slider' AND orden = 2
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `orden`, `activo`)
SELECT * FROM (SELECT 'slider' as seccion, 'Contenido de Calidad' as titulo, 'Periodismo responsable' as subtitulo, 'Información verificada y de calidad para ti' as contenido, 3 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'slider' AND orden = 3
) LIMIT 1;

-- Insertar datos por defecto para sección de contacto (solo si no existe)
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `orden`, `activo`)
SELECT * FROM (SELECT 'contacto' as seccion, '¿Tienes una noticia?' as titulo, 'Contáctanos' as subtitulo, 'Email: contacto@portalqueretaro.mx<br>Tel: 442-123-4567<br>Dirección: Querétaro, México' as contenido, 1 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'contacto'
) LIMIT 1;

-- Insertar datos por defecto para accesos directos (solo si no existen)
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'acceso_directo' as seccion, 'Noticias Destacadas' as titulo, 'Las más importantes' as subtitulo, 'fas fa-star' as contenido, 'index.php?destacadas=1' as url, 1 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'acceso_directo' AND orden = 1
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'acceso_directo' as seccion, 'Última Hora' as titulo, 'Lo más reciente' as subtitulo, 'fas fa-clock' as contenido, 'index.php? recientes=1' as url, 2 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'acceso_directo' AND orden = 2
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'acceso_directo' as seccion, 'Categorías' as titulo, 'Explora por tema' as subtitulo, 'fas fa-th-large' as contenido, 'categorias.php' as url, 3 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'acceso_directo' AND orden = 3
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'acceso_directo' as seccion, 'Multimedia' as titulo, 'Fotos y videos' as subtitulo, 'fas fa-images' as contenido, 'multimedia.php' as url, 4 as orden, 1 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'acceso_directo' AND orden = 4
) LIMIT 1;

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
