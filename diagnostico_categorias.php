<?php
/**
 * Script de diagnóstico para categorías y menú
 * Ayuda a identificar problemas de sincronización
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$categoriaModel = new Categoria();
$menuItemModel = new MenuItem();

echo "<h1>Diagnóstico de Categorías y Menú</h1>";

// 1. Verificar todas las categorías
echo "<h2>1. Todas las Categorías en la Base de Datos</h2>";
$todasCategorias = $categoriaModel->getAll();
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Slug</th><th>Padre ID</th><th>Visible</th><th>Orden</th></tr>";
foreach ($todasCategorias as $cat) {
    echo "<tr>";
    echo "<td>{$cat['id']}</td>";
    echo "<td>{$cat['nombre']}</td>";
    echo "<td>{$cat['slug']}</td>";
    echo "<td>" . ($cat['padre_id'] ?? 'NULL') . "</td>";
    echo "<td>" . ($cat['visible'] ? 'Sí' : 'No') . "</td>";
    echo "<td>{$cat['orden']}</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Verificar categorías principales (sin padre)
echo "<h2>2. Categorías Principales (padre_id IS NULL)</h2>";
$principales = $categoriaModel->getParents();
echo "<pre>";
print_r($principales);
echo "</pre>";

// 3. Verificar categorías principales visibles
echo "<h2>3. Categorías Principales Visibles (visible = 1)</h2>";
$principalesVisibles = $categoriaModel->getParents(1);
echo "<pre>";
print_r($principalesVisibles);
echo "</pre>";

// 4. Ver ítems del menú
echo "<h2>4. Ítems del Menú</h2>";
$menuItems = $menuItemModel->getAll();
echo "<pre>";
print_r($menuItems);
echo "</pre>";

// 5. Ver menú con subcategorías
echo "<h2>5. Menú con Subcategorías (como aparece en frontend)</h2>";
$menuConSubs = $menuItemModel->getAllWithSubcategories(1);
echo "<pre>";
print_r($menuConSubs);
echo "</pre>";

// 6. Verificar categorías huérfanas (padre_id apunta a categoría inexistente)
echo "<h2>6. Categorías Huérfanas (padre inexistente)</h2>";
$db = Database::getInstance()->getConnection();
$query = "SELECT c.* FROM categorias c 
          WHERE c.padre_id IS NOT NULL 
          AND c.padre_id NOT IN (SELECT id FROM categorias)";
$stmt = $db->query($query);
$huerfanas = $stmt->fetchAll();
if (empty($huerfanas)) {
    echo "<p>No hay categorías huérfanas.</p>";
} else {
    echo "<pre>";
    print_r($huerfanas);
    echo "</pre>";
}

// 7. Verificar menú items apuntando a categorías inexistentes
echo "<h2>7. Ítems de Menú sin Categoría Válida</h2>";
$query = "SELECT mi.* FROM menu_items mi 
          WHERE mi.categoria_id NOT IN (SELECT id FROM categorias)";
$stmt = $db->query($query);
$menuInvalidos = $stmt->fetchAll();
if (empty($menuInvalidos)) {
    echo "<p>Todos los ítems de menú tienen categorías válidas.</p>";
} else {
    echo "<pre>";
    print_r($menuInvalidos);
    echo "</pre>";
}

echo "<h2>8. Árbol de Categorías (estructura jerárquica)</h2>";
$arbol = $categoriaModel->getTree(1);
echo "<pre>";
print_r($arbol);
echo "</pre>";
?>
