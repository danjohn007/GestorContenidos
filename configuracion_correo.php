<?php
/**
 * Configuración de Correo del Sistema
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
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => trim($_POST['smtp_port'] ?? '587'),
        'smtp_usuario' => trim($_POST['smtp_usuario'] ?? ''),
        'smtp_password' => trim($_POST['smtp_password'] ?? ''),
        'smtp_seguridad' => trim($_POST['smtp_seguridad'] ?? 'tls'),
        'email_remitente' => trim($_POST['email_remitente'] ?? ''),
        'nombre_remitente' => trim($_POST['nombre_remitente'] ?? '')
    ];
    
    // Validaciones
    if (!empty($valores['email_remitente']) && !filter_var($valores['email_remitente'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email remitente no es válido';
    }
    
    if (empty($errors)) {
        // Guardar valores
        foreach ($valores as $clave => $valor) {
            $configuracionModel->setOrCreate($clave, $valor, 'texto', 'correo', '');
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
        setFlash('success', 'Configuración de correo actualizada exitosamente');
    }
}

// Obtener valores actuales
$config = $configuracionModel->getByGrupo('correo');

$title = 'Configuración de Correo';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-envelope mr-2 text-green-600"></i>
            Correo del Sistema
        </h1>
        <a href="<?php echo url('configuracion.php'); ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Se encontraron errores:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                            Configura el servidor SMTP para envío de notificaciones automáticas del sistema.
                            Se recomienda usar servicios como Gmail, SendGrid o Mailgun.
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Remitente</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email Remitente
                        </label>
                        <input type="email" name="email_remitente" 
                               value="<?php echo e($config['email_remitente']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="noreply@ejemplo.com">
                        <p class="text-xs text-gray-500 mt-1">Email que aparecerá como remitente</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre Remitente
                        </label>
                        <input type="text" name="nombre_remitente" 
                               value="<?php echo e($config['nombre_remitente']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Portal de Noticias">
                        <p class="text-xs text-gray-500 mt-1">Nombre que aparecerá como remitente</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración SMTP</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Servidor SMTP
                        </label>
                        <input type="text" name="smtp_host" 
                               value="<?php echo e($config['smtp_host']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="smtp.gmail.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Puerto
                        </label>
                        <input type="number" name="smtp_port" 
                               value="<?php echo e($config['smtp_port']['valor'] ?? '587'); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="587">
                        <p class="text-xs text-gray-500 mt-1">Comúnmente: 587 (TLS) o 465 (SSL)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Usuario SMTP
                        </label>
                        <input type="text" name="smtp_usuario" 
                               value="<?php echo e($config['smtp_usuario']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="tu-email@gmail.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña SMTP
                        </label>
                        <input type="password" name="smtp_password" 
                               value="<?php echo e($config['smtp_password']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="••••••••">
                        <p class="text-xs text-gray-500 mt-1">Para Gmail, usa una contraseña de aplicación</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seguridad
                    </label>
                    <select name="smtp_seguridad" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="tls" <?php echo ($config['smtp_seguridad']['valor'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>
                            TLS (Recomendado)
                        </option>
                        <option value="ssl" <?php echo ($config['smtp_seguridad']['valor'] ?? '') === 'ssl' ? 'selected' : ''; ?>>
                            SSL
                        </option>
                        <option value="none" <?php echo ($config['smtp_seguridad']['valor'] ?? '') === 'none' ? 'selected' : ''; ?>>
                            Sin cifrado
                        </option>
                    </select>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3 text-sm text-yellow-800">
                        <p class="font-medium mb-2">Consejos para Gmail:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Habilita el acceso de aplicaciones menos seguras o usa una contraseña de aplicación</li>
                            <li>Servidor: smtp.gmail.com, Puerto: 587, Seguridad: TLS</li>
                            <li>Visita: <a href="https://myaccount.google.com/apppasswords" target="_blank" class="underline">https://myaccount.google.com/apppasswords</a></li>
                        </ul>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
