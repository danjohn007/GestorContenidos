<?php
/**
 * Editar Banner
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$bannerModel = new Banner();
$errors = [];
$success = false;

// Obtener ID del banner
$bannerId = $_GET['id'] ?? null;

if (!$bannerId) {
    setFlash('error', 'ID de banner no especificado');
    redirect('banners.php');
}

// Obtener datos del banner
$banner = $bannerModel->getById($bannerId);

if (!$banner) {
    setFlash('error', 'Banner no encontrado');
    redirect('banners.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    $nombre = trim($_POST['nombre'] ?? '');
    $ubicacion = $_POST['ubicacion'] ?? '';
    $orientacion = $_POST['orientacion'] ?? 'horizontal';
    $dispositivo = $_POST['dispositivo'] ?? 'todos';
    $url_destino = trim($_POST['url_destino'] ?? '');
    $orden = (int)($_POST['orden'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $rotativo = isset($_POST['rotativo']) ? 1 : 0;
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
    
    // Validaciones
    if (empty($nombre)) {
        $errors[] = 'El nombre es requerido';
    }
    if (empty($ubicacion)) {
        $errors[] = 'Debe seleccionar una ubicación';
    }
    
    // Validar fechas
    if ($fecha_inicio && $fecha_fin && strtotime($fecha_inicio) > strtotime($fecha_fin)) {
        $errors[] = 'La fecha de inicio no puede ser posterior a la fecha de fin';
    }
    
    // Manejar imagen (opcional en edición)
    $imagen_url = $banner['imagen_url'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/banners/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            $filename = uniqid('banner_') . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                // Eliminar imagen anterior
                if ($banner['imagen_url']) {
                    $oldImagePath = __DIR__ . $banner['imagen_url'];
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                $imagen_url = '/public/uploads/banners/' . $filename;
            } else {
                $errors[] = 'Error al subir la nueva imagen';
            }
        } else {
            $errors[] = 'Solo se permiten imágenes JPG, JPEG, PNG, GIF o WebP';
        }
    }
    
    // Si no hay errores, actualizar banner
    if (empty($errors)) {
        $data = [
            'nombre' => $nombre,
            'imagen_url' => $imagen_url,
            'url_destino' => $url_destino,
            'ubicacion' => $ubicacion,
            'orientacion' => $orientacion,
            'dispositivo' => $dispositivo,
            'orden' => $orden,
            'activo' => $activo,
            'rotativo' => $rotativo,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ];
        
        $result = $bannerModel->update($bannerId, $data);
        if ($result) {
            setFlash('success', 'Banner actualizado exitosamente');
            redirect('banners.php');
        } else {
            $errors[] = 'Error al actualizar el banner. Intente nuevamente.';
        }
    }
}

$title = 'Editar Banner';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-edit mr-2 text-indigo-600"></i>
            Editar Banner
        </h1>
        <a href="<?php echo url('banners.php'); ?>" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver al listado
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    Se encontraron los siguientes errores:
                </h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre del Banner <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" required value="<?php echo e($banner['nombre']); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Ej: Banner Promocional Verano">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ubicación <span class="text-red-500">*</span>
                    </label>
                    <select name="ubicacion" required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccione una ubicación</option>
                        <?php foreach (Banner::getUbicaciones() as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($banner['ubicacion'] === $key) ? 'selected' : ''; ?>>
                            <?php echo e($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Orientación
                    </label>
                    <select name="orientacion" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach (Banner::getOrientaciones() as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($banner['orientacion'] === $key) ? 'selected' : ''; ?>>
                            <?php echo e($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Dispositivo
                    </label>
                    <select name="dispositivo" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach (Banner::getDispositivos() as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($banner['dispositivo'] === $key) ? 'selected' : ''; ?>>
                            <?php echo e($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Orden de Aparición
                    </label>
                    <input type="number" name="orden" min="0" value="<?php echo e($banner['orden']); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primero</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen Actual
                </label>
                <?php if ($banner['imagen_url']): ?>
                <div class="mb-2">
                    <img src="<?php echo e(BASE_URL . $banner['imagen_url']); ?>" 
                         alt="<?php echo e($banner['nombre']); ?>" 
                         class="max-h-48 rounded border">
                </div>
                <?php endif; ?>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cambiar Imagen (Opcional)
                </label>
                <input type="file" name="imagen" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPG, PNG, GIF, WebP. Dejar vacío para mantener la imagen actual.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    URL de Destino (Opcional)
                </label>
                <input type="url" name="url_destino" value="<?php echo e($banner['url_destino'] ?? ''); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="https://ejemplo.com">
                <p class="mt-1 text-xs text-gray-500">URL a la que redirige al hacer clic en el banner</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Inicio (Opcional)
                    </label>
                    <input type="date" name="fecha_inicio" value="<?php echo e($banner['fecha_inicio'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Fin (Opcional)
                    </label>
                    <input type="date" name="fecha_fin" value="<?php echo e($banner['fecha_fin'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Mostrar estadísticas -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Estadísticas</h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Impresiones:</span>
                        <span class="font-semibold ml-2"><?php echo number_format($banner['impresiones']); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Clics:</span>
                        <span class="font-semibold ml-2"><?php echo number_format($banner['clics']); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500">CTR:</span>
                        <span class="font-semibold ml-2">
                            <?php 
                            $ctr = $banner['impresiones'] > 0 ? ($banner['clics'] / $banner['impresiones'] * 100) : 0;
                            echo number_format($ctr, 2);
                            ?>%
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t pt-4">
                <div class="flex items-center">
                    <input type="checkbox" name="activo" id="activo" value="1" <?php echo $banner['activo'] ? 'checked' : ''; ?>
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="activo" class="ml-2 block text-sm text-gray-900">
                        Banner activo (visible en el sitio)
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="rotativo" id="rotativo" value="1" <?php echo $banner['rotativo'] ? 'checked' : ''; ?>
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="rotativo" class="ml-2 block text-sm text-gray-900">
                        Banner rotativo (parte de un carrusel)
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('banners.php'); ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Banner
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
