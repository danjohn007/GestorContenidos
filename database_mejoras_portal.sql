-- =====================================================
-- Mejoras al Portal - Configuración Logo y Slider
-- =====================================================

-- Agregar configuración para modo de logo (imagen o texto)
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'modo_logo' as clave, 'imagen' as valor, 'texto' as tipo, 'general' as grupo, 'Modo de visualización del logo: imagen o texto' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'modo_logo'
) LIMIT 1;

-- Agregar configuración para tipo de slider
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_tipo' as clave, 'estatico' as valor, 'texto' as tipo, 'general' as grupo, 'Tipo de slider: estatico, noticias, mixto' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_tipo'
) LIMIT 1;

-- Agregar configuración para número de slides en slider
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_cantidad' as clave, '3' as valor, 'numero' as tipo, 'general' as grupo, 'Cantidad de elementos en el slider' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_cantidad'
) LIMIT 1;

-- Agregar configuración para autoplay del slider
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_autoplay' as clave, '1' as valor, 'booleano' as tipo, 'general' as grupo, 'Activar autoplay del slider' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_autoplay'
) LIMIT 1;

-- Agregar configuración para intervalo de autoplay (en segundos)
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
SELECT * FROM (SELECT 'slider_intervalo' as clave, '5000' as valor, 'numero' as tipo, 'general' as grupo, 'Intervalo de autoplay en milisegundos' as descripcion) AS tmp
WHERE NOT EXISTS (
    SELECT clave FROM `configuracion` WHERE clave = 'slider_intervalo'
) LIMIT 1;

-- Agregar campo para marcar imágenes de slider en pagina_inicio
ALTER TABLE `pagina_inicio` 
ADD COLUMN IF NOT EXISTS `imagen_slider` VARCHAR(500) DEFAULT NULL AFTER `imagen`;

-- Agregar campo para vincular noticias al slider
ALTER TABLE `pagina_inicio` 
ADD COLUMN IF NOT EXISTS `noticia_id` INT(11) DEFAULT NULL AFTER `imagen_slider`,
ADD KEY IF NOT EXISTS `noticia_id` (`noticia_id`);
