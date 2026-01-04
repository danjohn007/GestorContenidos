-- Migración para agregar campo tamano_display a la tabla banners
-- Fecha: 2026-01-04
-- Descripción: Añade configuración de tamaño de visualización para banners

-- Agregar columna tamano_display a la tabla banners
ALTER TABLE `banners` 
ADD COLUMN `tamano_display` ENUM('auto', 'horizontal', 'cuadrado', 'vertical', 'real') 
DEFAULT 'auto' 
COMMENT 'Tamaño de visualización del banner: auto (responsive), horizontal (1200x400), cuadrado (600x600), vertical (300x600), real (sin escalar)' 
AFTER `dispositivo`;

-- Actualizar banners existentes para usar tamaño automático (default)
UPDATE `banners` SET `tamano_display` = 'auto' WHERE `tamano_display` IS NULL;
