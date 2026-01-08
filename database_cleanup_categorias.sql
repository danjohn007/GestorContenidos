-- Script para limpiar categorías inconsistentes
-- Este script identifica y repara problemas con categorías

-- 1. Identificar categorías con padre_id que no existe (categorías huérfanas)
SELECT 
    c.id,
    c.nombre,
    c.padre_id as padre_inexistente
FROM categorias c
LEFT JOIN categorias p ON c.padre_id = p.id
WHERE c.padre_id IS NOT NULL AND p.id IS NULL;

-- 2. Reparar categorías huérfanas (convertirlas en categorías principales)
UPDATE categorias c
LEFT JOIN categorias p ON c.padre_id = p.id
SET c.padre_id = NULL
WHERE c.padre_id IS NOT NULL AND p.id IS NULL;

-- 3. Identificar categorías invisibles que tienen subcategorías visibles
SELECT 
    p.id as padre_id,
    p.nombre as padre_nombre,
    p.visible as padre_visible,
    COUNT(c.id) as subcategorias_visibles
FROM categorias p
INNER JOIN categorias c ON c.padre_id = p.id
WHERE p.visible = 0 AND c.visible = 1
GROUP BY p.id;

-- 4. Identificar categorías duplicadas por nombre
SELECT nombre, COUNT(*) as cantidad
FROM categorias
GROUP BY nombre
HAVING cantidad > 1;
