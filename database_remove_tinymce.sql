-- =====================================================
-- Migración: Eliminar configuración de TinyMCE
-- =====================================================
-- Fecha: Diciembre 2024
-- Descripción: Se reemplaza TinyMCE con Quill.js, un editor
--              de texto enriquecido open source que no requiere API key
-- =====================================================

-- Eliminar la configuración de TinyMCE API Key de la tabla de configuración
DELETE FROM configuracion WHERE clave = 'tinymce_api_key';
