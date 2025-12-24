<?php
/**
 * Configuración de Estilos y Colores
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$configuracionModel = new Configuracion();
$errors = [];
$success = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores = [
        'color_primario' => trim($_POST['color_primario'] ?? '#1e40af'),
        'color_secundario' => trim($_POST['color_secundario'] ?? '#3b82f6'),
        'color_acento' => trim($_POST['color_acento'] ?? '#10b981'),
        'color_texto' => trim($_POST['color_texto'] ?? '#1f2937'),
        'color_fondo' => trim($_POST['color_fondo'] ?? '#f3f4f6'),
        'fuente_principal' => trim($_POST['fuente_principal'] ?? 'system-ui'),
        'fuente_titulos' => trim($_POST['fuente_titulos'] ?? 'system-ui')
    ];
    
    // Guardar valores
    foreach ($valores as $clave => $valor) {
        $tipo = ($clave === 'fuente_principal' || $clave === 'fuente_titulos') ? 'texto' : 'color';
        $configuracionModel->setOrCreate($clave, $valor, $tipo, 'diseno', '');
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
    setFlash('success', 'Configuración de estilos actualizada exitosamente');
}

// Obtener valores actuales
$config = $configuracionModel->getByGrupo('diseno');
if (empty($config)) {
    $config = [];
}

$title = 'Configuración de Estilos';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-palette mr-2 text-pink-600"></i>
            Estilos y Colores
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

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" class="space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800">
                            Personaliza los colores principales del sistema público y administrativo.
                            Los cambios se aplicarán en toda la interfaz.
                        </p>
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 mb-4">Colores del Sistema</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Color Primario
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="color_primario" 
                               value="<?php echo e($config['color_primario']['valor'] ?? '#1e40af'); ?>" 
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               value="<?php echo e($config['color_primario']['valor'] ?? '#1e40af'); ?>" 
                               readonly
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-sm">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Color principal de botones y enlaces</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Color Secundario
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="color_secundario" 
                               value="<?php echo e($config['color_secundario']['valor'] ?? '#3b82f6'); ?>" 
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               value="<?php echo e($config['color_secundario']['valor'] ?? '#3b82f6'); ?>" 
                               readonly
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-sm">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Color secundario para acentos</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Color de Acento
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="color_acento" 
                               value="<?php echo e($config['color_acento']['valor'] ?? '#10b981'); ?>" 
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               value="<?php echo e($config['color_acento']['valor'] ?? '#10b981'); ?>" 
                               readonly
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-sm">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Color para resaltar elementos</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Color de Texto
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="color_texto" 
                               value="<?php echo e($config['color_texto']['valor'] ?? '#1f2937'); ?>" 
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               value="<?php echo e($config['color_texto']['valor'] ?? '#1f2937'); ?>" 
                               readonly
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-sm">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Color principal del texto</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Color de Fondo
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="color_fondo" 
                               value="<?php echo e($config['color_fondo']['valor'] ?? '#f3f4f6'); ?>" 
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               value="<?php echo e($config['color_fondo']['valor'] ?? '#f3f4f6'); ?>" 
                               readonly
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-sm">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Color de fondo del sitio</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tipografía</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fuente Principal
                        </label>
                        <select name="fuente_principal" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="system-ui" <?php echo ($config['fuente_principal']['valor'] ?? 'system-ui') === 'system-ui' ? 'selected' : ''; ?>>
                                System UI (Predeterminada)
                            </option>
                            <option value="Arial, sans-serif" <?php echo ($config['fuente_principal']['valor'] ?? '') === 'Arial, sans-serif' ? 'selected' : ''; ?>>
                                Arial
                            </option>
                            <option value="Helvetica, sans-serif" <?php echo ($config['fuente_principal']['valor'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : ''; ?>>
                                Helvetica
                            </option>
                            <option value="'Segoe UI', sans-serif" <?php echo ($config['fuente_principal']['valor'] ?? '') === "'Segoe UI', sans-serif" ? 'selected' : ''; ?>>
                                Segoe UI
                            </option>
                            <option value="'Roboto', sans-serif" <?php echo ($config['fuente_principal']['valor'] ?? '') === "'Roboto', sans-serif" ? 'selected' : ''; ?>>
                                Roboto
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fuente para Títulos
                        </label>
                        <select name="fuente_titulos" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="system-ui" <?php echo ($config['fuente_titulos']['valor'] ?? 'system-ui') === 'system-ui' ? 'selected' : ''; ?>>
                                System UI (Predeterminada)
                            </option>
                            <option value="Arial, sans-serif" <?php echo ($config['fuente_titulos']['valor'] ?? '') === 'Arial, sans-serif' ? 'selected' : ''; ?>>
                                Arial
                            </option>
                            <option value="Helvetica, sans-serif" <?php echo ($config['fuente_titulos']['valor'] ?? '') === 'Helvetica, sans-serif' ? 'selected' : ''; ?>>
                                Helvetica
                            </option>
                            <option value="Georgia, serif" <?php echo ($config['fuente_titulos']['valor'] ?? '') === 'Georgia, serif' ? 'selected' : ''; ?>>
                                Georgia
                            </option>
                            <option value="'Roboto', sans-serif" <?php echo ($config['fuente_titulos']['valor'] ?? '') === "'Roboto', sans-serif" ? 'selected' : ''; ?>>
                                Roboto
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vista Previa</h3>
                <div class="border border-gray-200 rounded-lg p-6" style="background-color: <?php echo e($config['color_fondo']['valor'] ?? '#f3f4f6'); ?>">
                    <h2 class="text-2xl font-bold mb-3" style="color: <?php echo e($config['color_texto']['valor'] ?? '#1f2937'); ?>; font-family: <?php echo e($config['fuente_titulos']['valor'] ?? 'system-ui'); ?>">
                        Ejemplo de Título
                    </h2>
                    <p class="mb-4" style="color: <?php echo e($config['color_texto']['valor'] ?? '#1f2937'); ?>; font-family: <?php echo e($config['fuente_principal']['valor'] ?? 'system-ui'); ?>">
                        Este es un ejemplo de texto con la configuración actual. Los botones y enlaces usarán los colores seleccionados.
                    </p>
                    <div class="flex space-x-3">
                        <button type="button" class="px-4 py-2 rounded-lg text-white" 
                                style="background-color: <?php echo e($config['color_primario']['valor'] ?? '#1e40af'); ?>">
                            Botón Primario
                        </button>
                        <button type="button" class="px-4 py-2 rounded-lg text-white" 
                                style="background-color: <?php echo e($config['color_secundario']['valor'] ?? '#3b82f6'); ?>">
                            Botón Secundario
                        </button>
                        <button type="button" class="px-4 py-2 rounded-lg text-white" 
                                style="background-color: <?php echo e($config['color_acento']['valor'] ?? '#10b981'); ?>">
                            Botón de Acento
                        </button>
                    </div>
                </div>
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

<script>
// Sincronizar color picker con texto
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    colorInput.addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
