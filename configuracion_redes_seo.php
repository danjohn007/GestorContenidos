<?php
/**
 * Configuración de Redes Sociales y SEO
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$configuracionModel = new Configuracion();
$redesSocialesModel = new RedesSociales();
$errors = [];
$success = false;

// Procesar formulario de SEO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo']) && $_POST['tipo'] === 'seo') {
    $valores = [
        'google_analytics_id' => trim($_POST['google_analytics_id'] ?? ''),
        'google_search_console' => trim($_POST['google_search_console'] ?? ''),
        'facebook_app_id' => trim($_POST['facebook_app_id'] ?? ''),
        'meta_keywords_default' => trim($_POST['meta_keywords_default'] ?? ''),
        'meta_description_default' => trim($_POST['meta_description_default'] ?? '')
    ];
    
    // Guardar valores
    foreach ($valores as $clave => $valor) {
        $configuracionModel->setOrCreate($clave, $valor, 'texto', 'seo', '');
    }
    
    // Registrar auditoría
    $logModel = new Log();
    $currentUser = getCurrentUser();
    $logModel->registrarAuditoria(
        $currentUser['id'],
        'configuracion',
        'actualizar',
        'configuracion',
        0,
        null,
        $valores
    );
    
    $success = true;
    setFlash('success', 'Configuración de SEO actualizada exitosamente');
}

// Procesar formulario de redes sociales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo']) && $_POST['tipo'] === 'redes') {
    $id = (int)$_POST['id'];
    $data = [
        'url' => trim($_POST['url'] ?? ''),
        'activo' => isset($_POST['activo']) ? 1 : 0
    ];
    
    if ($redesSocialesModel->update($id, $data)) {
        // Registrar auditoría
        $logModel = new Log();
        $currentUser = getCurrentUser();
        $logModel->registrarAuditoria(
            $currentUser['id'],
            'redes_sociales',
            'actualizar',
            'redes_sociales',
            $id,
            null,
            $data
        );
        
        $success = true;
        setFlash('success', 'Red social actualizada exitosamente');
    }
}

// Obtener valores actuales
$config_seo = $configuracionModel->getByGrupo('seo');
$redes_sociales = $redesSocialesModel->getAll(false);

$title = 'Redes Sociales y SEO';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-share-alt mr-2 text-indigo-600"></i>
            Redes Sociales y SEO
        </h1>
        <a href="<?php echo url('configuracion.php'); ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver
        </a>
    </div>

    <?php if ($success || ($flashSuccess = getFlash('success'))): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-800"><?php echo e($flashSuccess ?? 'Configuración guardada exitosamente'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Redes Sociales -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
            <i class="fas fa-share-alt mr-2 text-indigo-600"></i>
            Enlaces de Redes Sociales
        </h2>
        <p class="text-gray-600 mb-6">Configura los enlaces a tus perfiles en redes sociales</p>
        
        <div class="space-y-4">
            <?php foreach ($redes_sociales as $red): ?>
            <form method="POST" class="border border-gray-200 rounded-lg p-4">
                <input type="hidden" name="tipo" value="redes">
                <input type="hidden" name="id" value="<?php echo $red['id']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="<?php echo e($red['icono']); ?> mr-2"></i>
                            <?php echo e($red['nombre']); ?>
                        </label>
                        <input type="url" name="url" 
                               value="<?php echo e($red['url']); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://<?php echo strtolower($red['nombre']); ?>.com/tu-perfil">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="activo" id="activo_<?php echo $red['id']; ?>" value="1" 
                               <?php echo $red['activo'] ? 'checked' : ''; ?>
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="activo_<?php echo $red['id']; ?>" class="ml-2 block text-sm text-gray-900">
                            Activo
                        </label>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SEO y Analytics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
            <i class="fas fa-search mr-2 text-yellow-600"></i>
            SEO y Analytics
        </h2>
        <p class="text-gray-600 mb-6">Configura herramientas de análisis y optimización para motores de búsqueda</p>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="tipo" value="seo">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Google Analytics ID
                    </label>
                    <input type="text" name="google_analytics_id" 
                           value="<?php echo e($config_seo['google_analytics_id']['valor'] ?? ''); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="G-XXXXXXXXXX o UA-XXXXXXXXX-X">
                    <p class="text-xs text-gray-500 mt-1">
                        <a href="https://analytics.google.com" target="_blank" class="text-blue-600 hover:text-blue-800">
                            Obtener ID de Google Analytics
                        </a>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Google Search Console
                    </label>
                    <input type="text" name="google_search_console" 
                           value="<?php echo e($config_seo['google_search_console']['valor'] ?? ''); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="código de verificación">
                    <p class="text-xs text-gray-500 mt-1">Código de verificación HTML</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Facebook App ID
                </label>
                <input type="text" name="facebook_app_id" 
                       value="<?php echo e($config_seo['facebook_app_id']['valor'] ?? ''); ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="1234567890">
                <p class="text-xs text-gray-500 mt-1">Para compartir en Facebook con metadatos enriquecidos</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Meta Keywords por Defecto
                </label>
                <input type="text" name="meta_keywords_default" 
                       value="<?php echo e($config_seo['meta_keywords_default']['valor'] ?? ''); ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="noticias, querétaro, portal">
                <p class="text-xs text-gray-500 mt-1">Palabras clave separadas por comas</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Meta Description por Defecto
                </label>
                <textarea name="meta_description_default" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Descripción breve del sitio (160 caracteres máximo)"><?php echo e($config_seo['meta_description_default']['valor'] ?? ''); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Descripción que aparecerá en los resultados de búsqueda</p>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="<?php echo url('configuracion.php'); ?>" 
                   class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
