<?php
/**
 * Configuración de Datos del Sitio
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
        'nombre_sitio' => trim($_POST['nombre_sitio'] ?? ''),
        'slogan_sitio' => trim($_POST['slogan_sitio'] ?? ''),
        'descripcion_sitio' => trim($_POST['descripcion_sitio'] ?? ''),
        'email_sistema' => trim($_POST['email_sistema'] ?? ''),
        'telefono_contacto' => trim($_POST['telefono_contacto'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'zona_horaria' => trim($_POST['zona_horaria'] ?? 'America/Mexico_City')
    ];
    
    // Validaciones
    if (empty($valores['nombre_sitio'])) {
        $errors[] = 'El nombre del sitio es requerido';
    }
    
    if (!empty($valores['email_sistema']) && !filter_var($valores['email_sistema'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email del sistema no es válido';
    }
    
    // Manejar logo si se sube
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/config/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Formato de logo no permitido';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $errors[] = 'Error al validar el tipo de archivo';
            } else {
                $mimeType = finfo_file($finfo, $_FILES['logo']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = 'Tipo de archivo no válido';
                } else {
                    // Delete old logo if exists
                    if (!empty($config['logo_sitio']['valor'])) {
                        $oldLogoPath = __DIR__ . $config['logo_sitio']['valor'];
                        if (file_exists($oldLogoPath) && is_file($oldLogoPath)) {
                            @unlink($oldLogoPath);
                        }
                    }
                    
                    $filename = 'logo_' . time() . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                        $valores['logo_sitio'] = '/public/uploads/config/' . $filename;
                    } else {
                        $errors[] = 'Error al subir el logo';
                    }
                }
            }
        }
    }
    
    if (empty($errors)) {
        // Guardar valores
        foreach ($valores as $clave => $valor) {
            $configuracionModel->setOrCreate($clave, $valor, 'texto', 'general', '');
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
        setFlash('success', 'Configuración actualizada exitosamente');
    }
}

// Obtener valores actuales
$config = $configuracionModel->getByGrupo('general');

$title = 'Configuración del Sitio';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-globe mr-2 text-blue-600"></i>
            Datos del Sitio
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
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Sitio <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre_sitio" 
                           value="<?php echo e($config['nombre_sitio']['valor'] ?? 'Portal de Noticias'); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Slogan
                    </label>
                    <input type="text" name="slogan_sitio" 
                           value="<?php echo e($config['slogan_sitio']['valor'] ?? ''); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción del Sitio
                </label>
                <textarea name="descripcion_sitio" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($config['descripcion_sitio']['valor'] ?? ''); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Breve descripción para SEO (160 caracteres máximo recomendado)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Logo del Sitio
                </label>
                <?php if (!empty($config['logo_sitio']['valor'])): ?>
                <div class="mb-3">
                    <img src="<?php echo e(BASE_URL . $config['logo_sitio']['valor'] . '?v=' . time()); ?>" alt="Logo actual" class="h-16" loading="eager">
                    <p class="text-xs text-gray-500 mt-1">Logo actual</p>
                </div>
                <?php endif; ?>
                <input type="file" name="logo" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, GIF, SVG, WEBP</p>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Contacto</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email del Sistema
                        </label>
                        <input type="email" name="email_sistema" 
                               value="<?php echo e($config['email_sistema']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Teléfono de Contacto
                        </label>
                        <input type="text" name="telefono_contacto" 
                               value="<?php echo e($config['telefono_contacto']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección
                    </label>
                    <input type="text" name="direccion" 
                           value="<?php echo e($config['direccion']['valor'] ?? ''); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración Técnica</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Zona Horaria
                        </label>
                        <select name="zona_horaria" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="America/Mexico_City" <?php echo ($config['zona_horaria']['valor'] ?? '') === 'America/Mexico_City' ? 'selected' : ''; ?>>
                                Ciudad de México
                            </option>
                            <option value="America/Cancun" <?php echo ($config['zona_horaria']['valor'] ?? '') === 'America/Cancun' ? 'selected' : ''; ?>>
                                Cancún
                            </option>
                            <option value="America/Tijuana" <?php echo ($config['zona_horaria']['valor'] ?? '') === 'America/Tijuana' ? 'selected' : ''; ?>>
                                Tijuana
                            </option>
                        </select>
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
