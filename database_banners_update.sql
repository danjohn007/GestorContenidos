-- =====================================================
-- Actualizaciones de Base de Datos - Banners Publicitarios
-- Fecha: 2026-01-02
-- Descripción: Agrega secciones para gestión de banners publicitarios
-- =====================================================

-- Insertar datos por defecto para banners verticales (solo si no existen)
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'banner_vertical' as seccion, 'Banner Vertical 1' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 1 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'banner_vertical' AND orden = 1
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'banner_vertical' as seccion, 'Banner Vertical 2' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 2 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'banner_vertical' AND orden = 2
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'banner_vertical' as seccion, 'Banner Vertical 3' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 3 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'banner_vertical' AND orden = 3
) LIMIT 1;

-- Insertar datos por defecto para anuncios de footer (solo si no existen)
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'anuncio_footer' as seccion, 'Anuncio Footer 1' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 1 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'anuncio_footer' AND orden = 1
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'anuncio_footer' as seccion, 'Anuncio Footer 2' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 2 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'anuncio_footer' AND orden = 2
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'anuncio_footer' as seccion, 'Anuncio Footer 3' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 3 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'anuncio_footer' AND orden = 3
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'anuncio_footer' as seccion, 'Anuncio Footer 4' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 4 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'anuncio_footer' AND orden = 4
) LIMIT 1;

-- Insertar datos por defecto para banners intermedios (solo si no existen)
INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'banner_intermedio' as seccion, 'Banner Entre Secciones 1' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 1 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'banner_intermedio' AND orden = 1
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'banner_intermedio' as seccion, 'Banner Entre Secciones 2' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 2 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'banner_intermedio' AND orden = 2
) LIMIT 1;

INSERT INTO `pagina_inicio` (`seccion`, `titulo`, `subtitulo`, `contenido`, `imagen`, `url`, `orden`, `activo`)
SELECT * FROM (SELECT 'banner_intermedio' as seccion, 'Banner Entre Secciones 3' as titulo, 'Publicidad' as subtitulo, '' as contenido, '' as imagen, '#' as url, 3 as orden, 0 as activo) AS tmp
WHERE NOT EXISTS (
    SELECT seccion FROM `pagina_inicio` WHERE seccion = 'banner_intermedio' AND orden = 3
) LIMIT 1;
