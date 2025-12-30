-- =====================================================
-- Agregar Modo en Construcción
-- =====================================================

-- Insertar configuración para modo en construcción
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
VALUES ('modo_construccion', '0', 'boolean', 'general', 'Activar o desactivar el modo en construcción del sitio público')
ON DUPLICATE KEY UPDATE `clave` = `clave`;

-- Insertar configuración para mensaje de construcción
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
VALUES ('mensaje_construccion', 'Estamos mejorando para ti, disponibles muy pronto', 'texto', 'general', 'Mensaje a mostrar cuando el sitio está en construcción')
ON DUPLICATE KEY UPDATE `clave` = `clave`;

-- Insertar configuración para información de contacto en construcción
INSERT INTO `configuracion` (`clave`, `valor`, `tipo`, `grupo`, `descripcion`) 
VALUES (
    'contacto_construccion', 
    'Email: contacto@portalqueretaro.mx<br>Tel: 442-123-4567<br>Dirección: Querétaro, México', 
    'texto', 
    'general', 
    'Información de contacto a mostrar en modo construcción'
)
ON DUPLICATE KEY UPDATE `clave` = `clave`;
