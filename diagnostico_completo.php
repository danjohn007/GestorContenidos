<?php
/**
 * Diagnóstico Completo del Sistema
 * Verifica categorías, menú, noticias programadas, y configuración
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$categoriaModel = new Categoria();
$menuItemModel = new MenuItem();
$noticiaModel = new Noticia();
$configuracionModel = new Configuracion();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico del Sistema</title>";
echo "<style>body{font-family:system-ui;padding:2rem;max-width:1200px;margin:0 auto;}";
echo "h1{color:#1e40af;}h2{color:#3b82f6;border-bottom:2px solid #e5e7eb;padding-bottom:0.5rem;margin-top:2rem;}";
echo "table{width:100%;border-collapse:collapse;margin:1rem 0;}";
echo "th,td{padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;}";
echo "th{background:#f3f4f6;font-weight:600;}";
echo ".success{color:#10b981;}.warning{color:#f59e0b;}.error{color:#ef4444;}";
echo ".badge{display:inline-block;padding:0.25rem 0.75rem;border-radius:0.25rem;font-size:0.875rem;}";
echo ".badge-success{background:#d1fae5;color:#065f46;}";
echo ".badge-warning{background:#fef3c7;color:#92400e;}";
echo ".badge-error{background:#fee2e2;color:#991b1b;}";
echo "</style></head><body>";

echo "<h1>🔍 Diagnóstico Completo del Sistema</h1>";
echo "<p><em>Generado: " . date('Y-m-d H:i:s') . "</em></p>";

// 1. Diagnóstico de Categorías
echo "<h2>📁 Categorías y Subcategorías</h2>";
$todasCategorias = $categoriaModel->getAll();
$categoriasParent = $categoriaModel->getParents();

echo "<h3>Categorías Principales (" . count($categoriasParent) . ")</h3>";
echo "<table><thead><tr><th>ID</th><th>Nombre</th><th>Slug</th><th>Visible</th><th>Orden</th><th>Subcategorías</th></tr></thead><tbody>";
foreach ($categoriasParent as $cat) {
    $subcats = $categoriaModel->getChildren($cat['id']);
    $visibleBadge = $cat['visible'] ? '<span class="badge badge-success">Visible</span>' : '<span class="badge badge-warning">Oculta</span>';
    echo "<tr>";
    echo "<td>{$cat['id']}</td>";
    echo "<td><strong>{$cat['nombre']}</strong></td>";
    echo "<td>{$cat['slug']}</td>";
    echo "<td>{$visibleBadge}</td>";
    echo "<td>{$cat['orden']}</td>";
    echo "<td>" . count($subcats) . "</td>";
    echo "</tr>";
    
    // Mostrar subcategorías
    foreach ($subcats as $sub) {
        $subVisibleBadge = $sub['visible'] ? '<span class="badge badge-success">Visible</span>' : '<span class="badge badge-warning">Oculta</span>';
        echo "<tr style='background:#f9fafb;'>";
        echo "<td style='padding-left:2rem;'>{$sub['id']}</td>";
        echo "<td style='padding-left:2rem;'>↳ {$sub['nombre']}</td>";
        echo "<td>{$sub['slug']}</td>";
        echo "<td>{$subVisibleBadge}</td>";
        echo "<td>{$sub['orden']}</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }
}
echo "</tbody></table>";

// 2. Diagnóstico de Menú
echo "<h2>🍔 Ítems del Menú Principal</h2>";
$menuItems = $menuItemModel->getAll();

echo "<h3>Ítems en el Menú (" . count($menuItems) . ")</h3>";
echo "<table><thead><tr><th>ID Menú</th><th>Categoría ID</th><th>Nombre Categoría</th><th>Estado</th><th>Orden</th><th>Diagnóstico</th></tr></thead><tbody>";
foreach ($menuItems as $item) {
    $categoria = $categoriaModel->getById($item['categoria_id']);
    $activoBadge = $item['activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-warning">Inactivo</span>';
    
    $diagnostico = '';
    if (!$categoria) {
        $diagnostico = '<span class="error">❌ Categoría no existe (huérfano)</span>';
    } elseif ($categoria['padre_id'] !== null) {
        $diagnostico = '<span class="warning">⚠️ Es una subcategoría (no debería estar en menú)</span>';
    } elseif (!$categoria['visible']) {
        $diagnostico = '<span class="warning">⚠️ Categoría oculta</span>';
    } else {
        $diagnostico = '<span class="success">✓ OK</span>';
    }
    
    echo "<tr>";
    echo "<td>{$item['id']}</td>";
    echo "<td>{$item['categoria_id']}</td>";
    echo "<td>" . ($categoria ? $categoria['nombre'] : '<em>No existe</em>') . "</td>";
    echo "<td>{$activoBadge}</td>";
    echo "<td>{$item['orden']}</td>";
    echo "<td>{$diagnostico}</td>";
    echo "</tr>";
}
echo "</tbody></table>";

// 3. Buscar categorías sin ítem de menú
echo "<h3>Categorías sin Ítem de Menú</h3>";
$sinMenu = [];
foreach ($categoriasParent as $cat) {
    if (!$menuItemModel->getByCategoriaId($cat['id'])) {
        $sinMenu[] = $cat;
    }
}

if (empty($sinMenu)) {
    echo "<p class='success'>✓ Todas las categorías principales tienen un ítem de menú</p>";
} else {
    echo "<p class='warning'>⚠️ Se encontraron " . count($sinMenu) . " categoría(s) sin ítem de menú:</p>";
    echo "<ul>";
    foreach ($sinMenu as $cat) {
        echo "<li>{$cat['nombre']} (ID: {$cat['id']})</li>";
    }
    echo "</ul>";
}

// 4. Diagnóstico de Noticias Programadas
echo "<h2>📅 Noticias Programadas</h2>";
$db = Database::getInstance()->getConnection();
$query = "SELECT id, titulo, estado, fecha_programada, fecha_publicacion, fecha_creacion 
          FROM noticias 
          WHERE fecha_programada IS NOT NULL 
          ORDER BY fecha_programada ASC";
$stmt = $db->query($query);
$noticiasProgramadas = $stmt->fetchAll();

if (empty($noticiasProgramadas)) {
    echo "<p>No hay noticias con programación</p>";
} else {
    echo "<table><thead><tr><th>ID</th><th>Título</th><th>Estado</th><th>Programada para</th><th>Publicada</th><th>Diagnóstico</th></tr></thead><tbody>";
    foreach ($noticiasProgramadas as $noticia) {
        $ahora = new DateTime();
        $fechaProgramada = new DateTime($noticia['fecha_programada']);
        
        $diagnostico = '';
        if ($noticia['fecha_publicacion']) {
            $diagnostico = '<span class="badge badge-success">Ya publicada</span>';
        } elseif ($fechaProgramada <= $ahora) {
            $diagnostico = '<span class="badge badge-warning">Pendiente de publicación</span>';
        } else {
            $diagnostico = '<span class="badge badge-success">Programada</span>';
        }
        
        echo "<tr>";
        echo "<td>{$noticia['id']}</td>";
        echo "<td>" . substr($noticia['titulo'], 0, 50) . "...</td>";
        echo "<td>{$noticia['estado']}</td>";
        echo "<td>{$noticia['fecha_programada']}</td>";
        echo "<td>" . ($noticia['fecha_publicacion'] ?? '<em>No publicada</em>') . "</td>";
        echo "<td>{$diagnostico}</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
}

// 5. Configuración del Sistema
echo "<h2>⚙️ Configuración Relevante</h2>";
$config = $configuracionModel->getByGrupo('general');

echo "<table><thead><tr><th>Configuración</th><th>Valor</th></tr></thead><tbody>";
echo "<tr><td>Nombre del Sitio</td><td>" . e($config['nombre_sitio']['valor'] ?? 'No configurado') . "</td></tr>";
echo "<tr><td>Logo del Sitio</td><td>" . ($config['logo_sitio']['valor'] ? '✓ Configurado' : '✗ No configurado') . "</td></tr>";
echo "<tr><td>Logo del Footer</td><td>" . ($config['logo_footer']['valor'] ? '✓ Configurado' : '✗ No configurado') . "</td></tr>";
echo "<tr><td>Email del Sistema</td><td>" . e($config['email_sistema']['valor'] ?? 'No configurado') . "</td></tr>";
echo "<tr><td>Mostrar Accesos Rápidos</td><td>" . (($config['mostrar_accesos_rapidos']['valor'] ?? '1') === '1' ? '✓ Sí' : '✗ No') . "</td></tr>";
echo "<tr><td>Zona Horaria</td><td>" . e($config['zona_horaria']['valor'] ?? date_default_timezone_get()) . "</td></tr>";
echo "</tbody></table>";

// 6. Resumen de Problemas
echo "<h2>📋 Resumen de Problemas Detectados</h2>";
$problemas = [];

// Contar huérfanos
$huerfanos = 0;
$subcatsEnMenu = 0;
foreach ($menuItems as $item) {
    $categoria = $categoriaModel->getById($item['categoria_id']);
    if (!$categoria) {
        $huerfanos++;
    } elseif ($categoria['padre_id'] !== null) {
        $subcatsEnMenu++;
    }
}

if ($huerfanos > 0) {
    $problemas[] = "Se encontraron {$huerfanos} ítem(s) de menú huérfano(s) (categoría no existe)";
}
if ($subcatsEnMenu > 0) {
    $problemas[] = "Se encontraron {$subcatsEnMenu} subcategoría(s) en el menú principal";
}
if (!empty($sinMenu)) {
    $problemas[] = count($sinMenu) . " categoría(s) principal(es) sin ítem de menú";
}

if (empty($problemas)) {
    echo "<p class='success' style='font-size:1.25rem;'>✅ ¡No se detectaron problemas! El sistema está sincronizado correctamente.</p>";
} else {
    echo "<ul class='error'>";
    foreach ($problemas as $problema) {
        echo "<li>❌ {$problema}</li>";
    }
    echo "</ul>";
    echo "<p><strong>Acción recomendada:</strong> Ejecuta la <a href='" . url('pagina_inicio.php') . "'>Sincronización del Menú</a> desde Gestión de Página de Inicio.</p>";
}

echo "<hr style='margin:2rem 0;'>";
echo "<p><a href='" . url('pagina_inicio.php') . "' style='color:#1e40af;text-decoration:underline;'>← Volver a Gestión de Página de Inicio</a></p>";
echo "<p><a href='" . url('sync_menu.php') . "' style='color:#1e40af;text-decoration:underline;'>🔧 Ejecutar Sincronización Detallada</a></p>";

echo "</body></html>";
?>
