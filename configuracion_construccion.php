<?php
/**
 * Configuración del Modo en Construcción
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
        'modo_construccion' => isset($_POST['modo_construccion']) ? '1' : '0',
        'mensaje_construccion' => trim($_POST['mensaje_construccion'] ?? 'Estamos mejorando para ti, disponibles muy pronto'),
        'contacto_construccion' => trim($_POST['contacto_construccion'] ?? '')
    ];
    
    // Guardar valores
    foreach ($valores as $clave => $valor) {
        $tipo = ($clave === 'modo_construccion') ? 'boolean' : 'texto';
        $descripcion = ''; // No description needed for these runtime configs
        $configuracionModel->setOrCreate($clave, $valor, $tipo, 'general', $descripcion);
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
    setFlash('success', 'Configuración de modo construcción actualizada exitosamente');
}

// Obtener valores actuales
$config = $configuracionModel->getByGrupo('general');
$modoConstruccion = ($config['modo_construccion']['valor'] ?? '0') === '1';
$mensajeConstruccion = $config['mensaje_construccion']['valor'] ?? 'Estamos mejorando para ti, disponibles muy pronto';
$contactoConstruccion = $config['contacto_construccion']['valor'] ?? 'Email: contacto@portalqueretaro.mx<br>Tel: 442-123-4567<br>Dirección: Querétaro, México';

$title = 'Configuración de Modo en Construcción';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-hard-hat mr-2 text-orange-600"></i>
            Modo en Construcción
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

    <?php if ($modoConstruccion): ?>
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-orange-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-orange-800">
                    ⚠️ El modo construcción está ACTIVO
                </h3>
                <p class="mt-2 text-sm text-orange-700">
                    El sitio público está mostrando la página de construcción. Los usuarios no autenticados no pueden ver el contenido.
                </p>
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
                            Activa el modo construcción para mostrar una página de mantenimiento en el sitio público.
                            Los administradores autenticados seguirán teniendo acceso al sistema.
                        </p>
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración General</h3>

            <!-- Activar Modo Construcción -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <label class="block text-base font-medium text-gray-700 mb-1">
                            <i class="fas fa-toggle-on mr-2 text-orange-600"></i>
                            Activar Modo en Construcción
                        </label>
                        <p class="text-sm text-gray-600">
                            Cuando está activo, los visitantes verán una página de construcción en lugar del sitio normal
                        </p>
                    </div>
                    <div class="ml-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="modo_construccion" value="1" <?php echo $modoConstruccion ? 'checked' : ''; ?> 
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-orange-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Mensaje de Construcción -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-comment-alt mr-2 text-gray-600"></i>
                    Mensaje de Construcción
                </label>
                <input type="text" name="mensaje_construccion" 
                       value="<?php echo e($mensajeConstruccion); ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="Estamos mejorando para ti, disponibles muy pronto">
                <p class="text-xs text-gray-500 mt-1">
                    Este mensaje se mostrará en la página de construcción
                </p>
            </div>

            <!-- Información de Contacto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-address-card mr-2 text-gray-600"></i>
                    Información de Contacto
                </label>
                <textarea name="contacto_construccion" rows="4" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                          placeholder="Email: contacto@ejemplo.com&#10;Tel: 123-456-7890"><?php echo e($contactoConstruccion); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Puedes usar HTML básico (br, strong, em) para formato. Esta información se mostrará en la página de construcción.
                </p>
            </div>

            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vista Previa</h3>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-12 text-center text-white">
                        <div class="max-w-xl mx-auto">
                            <div class="mb-6">
                                <i class="fas fa-hard-hat text-6xl"></i>
                            </div>
                            <h2 class="text-3xl font-bold mb-4">En Construcción</h2>
                            <p class="text-xl mb-6"><?php echo e($mensajeConstruccion); ?></p>
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                <h3 class="text-xl font-bold mb-2">
                                    <i class="fas fa-envelope mr-2"></i>
                                    Contáctanos
                                </h3>
                                <div class="text-sm">
                                    <?php echo nl2br(e($contactoConstruccion)); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="<?php echo url('configuracion.php'); ?>" 
                   class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition-colors">
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
