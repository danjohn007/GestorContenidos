<?php
/**
 * Script de Sincronización de Menú y Categorías
 * Corrige inconsistencias entre las categorías del administrador y el menú público
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$categoriaModel = new Categoria();
$menuItemModel = new MenuItem();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Sincronización de Menú</title>";
echo "<style>body{font-family:system-ui;padding:2rem;max-width:800px;margin:0 auto;}";
echo "h1{color:#1e40af;}ul{line-height:1.8;}.success{color:#10b981;}.warning{color:#f59e0b;}.error{color:#ef4444;}</style></head><body>";

echo "<h1>🔄 Sincronización de Menú y Categorías</h1>";

// 1. Listar todas las categorías principales
echo "<h2>📂 Categorías Principales en la Base de Datos</h2>";
$categoriasParent = $categoriaModel->getParents();
echo "<ul>";
foreach ($categoriasParent as $cat) {
    echo "<li><strong>{$cat['nombre']}</strong> (ID: {$cat['id']}, Visible: " . ($cat['visible'] ? 'Sí' : 'No') . ")";
    
    // Listar subcategorías
    $subcats = $categoriaModel->getChildren($cat['id']);
    if (!empty($subcats)) {
        echo "<ul>";
        foreach ($subcats as $sub) {
            echo "<li>{$sub['nombre']} (ID: {$sub['id']}, Visible: " . ($sub['visible'] ? 'Sí' : 'No') . ")</li>";
        }
        echo "</ul>";
    }
    echo "</li>";
}
echo "</ul>";

// 2. Listar ítems del menú actual
echo "<h2>📋 Ítems del Menú Actual</h2>";
$menuItems = $menuItemModel->getAllWithSubcategories();
echo "<ul>";
foreach ($menuItems as $item) {
    echo "<li><strong>{$item['categoria_nombre']}</strong> (Activo: " . ($item['activo'] ? 'Sí' : 'No') . ", Orden: {$item['orden']})";
    
    if (!empty($item['subcategorias'])) {
        echo "<ul>";
        foreach ($item['subcategorias'] as $sub) {
            echo "<li>{$sub['nombre']}</li>";
        }
        echo "</ul>";
    }
    echo "</li>";
}
echo "</ul>";

// 3. Sincronizar menú con categorías
echo "<h2>🔧 Sincronizando...</h2>";
echo "<ul>";

foreach ($categoriasParent as $cat) {
    $existeMenu = $menuItemModel->getByCategoriaId($cat['id']);
    
    if (!$existeMenu && $cat['visible']) {
        // Crear ítem de menú para esta categoría
        $result = $menuItemModel->create([
            'categoria_id' => $cat['id'],
            'orden' => $cat['orden'] ?? 0,
            'activo' => 1
        ]);
        
        if ($result) {
            echo "<li class='success'>✅ Creado ítem de menú para categoría: <strong>{$cat['nombre']}</strong></li>";
        } else {
            echo "<li class='error'>❌ Error al crear ítem para: <strong>{$cat['nombre']}</strong></li>";
        }
    } elseif ($existeMenu && !$cat['visible']) {
        // Desactivar ítem de menú si la categoría está oculta
        $result = $menuItemModel->update($existeMenu['id'], ['activo' => 0]);
        if ($result) {
            echo "<li class='warning'>⚠️ Desactivado ítem de menú para categoría oculta: <strong>{$cat['nombre']}</strong></li>";
        }
    } elseif ($existeMenu) {
        echo "<li>ℹ️ Ítem de menú ya existe para: <strong>{$cat['nombre']}</strong></li>";
    }
}

echo "</ul>";

// 4. Verificar ítems de menú huérfanos (sin categoría válida)
echo "<h2>🔍 Verificando Ítems Huérfanos</h2>";
$allMenuItems = $menuItemModel->getAll();
$huerfanos = false;

echo "<ul>";
foreach ($allMenuItems as $item) {
    $categoria = $categoriaModel->getById($item['categoria_id']);
    
    if (!$categoria) {
        echo "<li class='error'>❌ Ítem de menú #{$item['id']} referencia categoría inexistente (ID: {$item['categoria_id']})</li>";
        $huerfanos = true;
    }
}

if (!$huerfanos) {
    echo "<li class='success'>✅ No se encontraron ítems huérfanos</li>";
}
echo "</ul>";

echo "<h2>✅ Sincronización Completada</h2>";
echo "<p><a href='".url('configuracion.php')."' style='color:#1e40af;text-decoration:underline;'>← Volver a Configuración</a></p>";
echo "<p><a href='".url('index.php?preview=1')."' style='color:#1e40af;text-decoration:underline;' target='_blank'>🔗 Ver Sitio Público</a></p>";

echo "</body></html>";
?>
