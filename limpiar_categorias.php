<?php
/**
 * Script de Limpieza de Categorías
 * Corrige categorías huérfanas y problemas de sincronización
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$categoriaModel = new Categoria();
$db = Database::getInstance()->getConnection();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Limpieza de Categorías</title>";
echo "<style>body{font-family:system-ui;padding:2rem;max-width:800px;margin:0 auto;}";
echo "h1{color:#1e40af;}ul{line-height:1.8;}.success{color:#10b981;}.warning{color:#f59e0b;}.error{color:#ef4444;}</style></head><body>";

echo "<h1>🧹 Limpieza de Categorías</h1>";

$fixed = [];
$issues = [];

// 1. Verificar categorías huérfanas (padre_id apunta a categoría inexistente)
echo "<h2>1️⃣ Buscando Categorías Huérfanas</h2>";
$query = "SELECT c.* FROM categorias c 
          WHERE c.padre_id IS NOT NULL 
          AND c.padre_id NOT IN (SELECT id FROM categorias)";
$stmt = $db->query($query);
$huerfanas = $stmt->fetchAll();

if (empty($huerfanas)) {
    echo "<p class='success'>✅ No se encontraron categorías huérfanas.</p>";
} else {
    echo "<p class='warning'>⚠️ Se encontraron " . count($huerfanas) . " categoría(s) huérfana(s):</p>";
    echo "<ul>";
    foreach ($huerfanas as $cat) {
        echo "<li>{$cat['nombre']} (ID: {$cat['id']}, padre_id inválido: {$cat['padre_id']})</li>";
        $issues[] = "Categoría huérfana: {$cat['nombre']} (ID: {$cat['id']})";
    }
    echo "</ul>";
    
    // Opción de reparación con POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix']) && $_POST['fix'] == 'orphans') {
        echo "<h3>Reparando categorías huérfanas...</h3>";
        foreach ($huerfanas as $cat) {
            $query = "UPDATE categorias SET padre_id = NULL WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute(['id' => $cat['id']]);
            echo "<p class='success'>✅ Categoría '{$cat['nombre']}' convertida en categoría principal</p>";
            $fixed[] = "Categoría '{$cat['nombre']}' reparada";
        }
    } else {
        echo "<form method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='fix' value='orphans'>";
        echo "<button type='submit' style='background:#10b981;color:white;padding:0.5rem 1rem;border-radius:0.5rem;text-decoration:none;display:inline-block;border:none;cursor:pointer;'>🔧 Reparar Huérfanas</button>";
        echo "</form>";
    }
}

// 2. Verificar categorías con visible=0 pero que tienen subcategorías visibles
echo "<h2>2️⃣ Verificando Visibilidad de Categorías Padre</h2>";
$query = "SELECT DISTINCT p.id, p.nombre, p.visible 
          FROM categorias p
          INNER JOIN categorias c ON c.padre_id = p.id
          WHERE p.visible = 0 AND c.visible = 1";
$stmt = $db->query($query);
$padresOcultos = $stmt->fetchAll();

if (empty($padresOcultos)) {
    echo "<p class='success'>✅ No hay inconsistencias de visibilidad.</p>";
} else {
    echo "<p class='warning'>⚠️ Se encontraron " . count($padresOcultos) . " categoría(s) padre oculta(s) con subcategorías visibles:</p>";
    echo "<ul>";
    foreach ($padresOcultos as $padre) {
        echo "<li>{$padre['nombre']} (ID: {$padre['id']})</li>";
        $issues[] = "Categoría padre oculta con hijos visibles: {$padre['nombre']}";
    }
    echo "</ul>";
    
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix']) && $_POST['fix'] == 'visibility') {
        echo "<h3>Corrigiendo visibilidad...</h3>";
        foreach ($padresOcultos as $padre) {
            // Opción 1: Ocultar subcategorías
            $query = "UPDATE categorias SET visible = 0 WHERE padre_id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute(['id' => $padre['id']]);
            echo "<p class='success'>✅ Ocultadas subcategorías de '{$padre['nombre']}'</p>";
            $fixed[] = "Subcategorías de '{$padre['nombre']}' ocultadas";
        }
    } else {
        echo "<form method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='fix' value='visibility'>";
        echo "<button type='submit' style='background:#10b981;color:white;padding:0.5rem 1rem;border-radius:0.5rem;text-decoration:none;display:inline-block;border:none;cursor:pointer;'>🔧 Ocultar Subcategorías</button>";
        echo "</form>";
    }
}

// 3. Verificar categorías duplicadas (mismo nombre y padre)
echo "<h2>3️⃣ Buscando Categorías Duplicadas</h2>";
$query = "SELECT nombre, COALESCE(padre_id, 0) as padre_id, COUNT(*) as count
          FROM categorias
          GROUP BY nombre, COALESCE(padre_id, 0)
          HAVING count > 1";
$stmt = $db->query($query);
$duplicadas = $stmt->fetchAll();

if (empty($duplicadas)) {
    echo "<p class='success'>✅ No se encontraron categorías duplicadas.</p>";
} else {
    echo "<p class='warning'>⚠️ Se encontraron " . count($duplicadas) . " grupo(s) de categorías duplicadas:</p>";
    echo "<ul>";
    foreach ($duplicadas as $dup) {
        echo "<li>'{$dup['nombre']}' (aparece {$dup['count']} veces)</li>";
        $issues[] = "Categorías duplicadas: {$dup['nombre']}";
    }
    echo "</ul>";
    echo "<p class='warning'>⚠️ La reparación automática de duplicados requiere revisión manual.</p>";
}

// 4. Resumen
echo "<h2>📊 Resumen</h2>";
if (empty($issues)) {
    echo "<p class='success' style='font-size:1.2rem;'><strong>✅ ¡La base de datos de categorías está limpia!</strong></p>";
} else {
    echo "<p class='error'>Se encontraron " . count($issues) . " problema(s):</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>{$issue}</li>";
    }
    echo "</ul>";
}

if (!empty($fixed)) {
    echo "<h3 class='success'>Reparaciones aplicadas:</h3>";
    echo "<ul>";
    foreach ($fixed as $fix) {
        echo "<li class='success'>✅ {$fix}</li>";
    }
    echo "</ul>";
}

echo "<hr style='margin:2rem 0;'>";
echo "<p><a href='".url('sync_menu.php')."' style='background:#1e40af;color:white;padding:0.5rem 1rem;border-radius:0.5rem;text-decoration:none;display:inline-block;'>🔄 Sincronizar Menú</a></p>";
echo "<p><a href='".url('diagnostico_categorias.php')."' style='background:#6b7280;color:white;padding:0.5rem 1rem;border-radius:0.5rem;text-decoration:none;display:inline-block;margin-top:0.5rem;'>🔍 Ver Diagnóstico Detallado</a></p>";
echo "<p><a href='".url('categorias.php')."' style='color:#1e40af;text-decoration:underline;'>← Volver a Gestión de Categorías</a></p>";

echo "</body></html>";
?>
