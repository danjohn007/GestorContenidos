<?php
/**
 * Crear Banner
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$bannerModel = new Banner();
$bannerImagenModel = new BannerImagen();
$errors = [];
$success = false;

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
    
    // Manejar imagen principal (solo si no es rotativo o no hay imágenes adicionales)
    $imagen_url = null;
    $imagenesAdicionales = [];
    
    if ($rotativo && isset($_FILES['imagenes_adicionales']) && !empty($_FILES['imagenes_adicionales']['name'][0])) {
        // Banner rotativo con imágenes adicionales
        $uploadDir = __DIR__ . '/public/uploads/banners/';
        
        // Crear directorio con manejo de errores
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $errors[] = 'Error al crear el directorio de uploads. Verifique los permisos.';
            }
        }
        
        if (empty($errors)) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            foreach ($_FILES['imagenes_adicionales']['name'] as $index => $fileName) {
                if ($_FILES['imagenes_adicionales']['error'][$index] === UPLOAD_ERR_OK) {
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    if (in_array($extension, $allowedExtensions)) {
                        $filename = 'banner_' . bin2hex(random_bytes(16)) . '.' . $extension;
                        $uploadPath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($_FILES['imagenes_adicionales']['tmp_name'][$index], $uploadPath)) {
                            $imagenesAdicionales[] = [
                                'url' => '/public/uploads/banners/' . $filename,
                                'orden' => isset($_POST['imagenes_orden'][$index]) ? (int)$_POST['imagenes_orden'][$index] : $index
                            ];
                        } else {
                            $errors[] = 'Error al subir la imagen ' . ($index + 1);
                        }
                    } else {
                        $errors[] = 'Solo se permiten imágenes JPG, JPEG, PNG, GIF o WebP';
                    }
                }
            }
            
            // Para banner rotativo, usar la primera imagen como principal
            if (!empty($imagenesAdicionales)) {
                $imagen_url = $imagenesAdicionales[0]['url'];
            }
        }
        
        if (empty($imagenesAdicionales) && empty($errors)) {
            $errors[] = 'Debe agregar al menos una imagen para el banner rotativo';
        }
    } else {
        // Banner simple con una sola imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/public/uploads/banners/';
            
            // Crear directorio con manejo de errores
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors[] = 'Error al crear el directorio de uploads. Verifique los permisos.';
                }
            }
            
            if (empty($errors)) {
                $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($extension, $allowedExtensions)) {
                    $filename = 'banner_' . bin2hex(random_bytes(16)) . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                        $imagen_url = '/public/uploads/banners/' . $filename;
                    } else {
                        $errors[] = 'Error al subir la imagen';
                    }
                } else {
                    $errors[] = 'Solo se permiten imágenes JPG, JPEG, PNG, GIF o WebP';
                }
            }
        } else {
            $errors[] = 'Debe seleccionar una imagen para el banner';
        }
    }
    
    // Si no hay errores, crear banner
    if (empty($errors)) {
        $data = [
            'nombre' => $nombre,
            'tipo' => 'imagen',
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
        
        $bannerId = $bannerModel->create($data);
        if ($bannerId) {
            // Si es rotativo y hay imágenes adicionales, guardarlas
            if ($rotativo && !empty($imagenesAdicionales)) {
                foreach ($imagenesAdicionales as $imagenData) {
                    $bannerImagenModel->create([
                        'banner_id' => $bannerId,
                        'imagen_url' => $imagenData['url'],
                        'orden' => $imagenData['orden'],
                        'activo' => 1
                    ]);
                }
            }
            
            setFlash('success', 'Banner creado exitosamente' . ($rotativo && count($imagenesAdicionales) > 1 ? ' con ' . count($imagenesAdicionales) . ' imágenes' : ''));
            redirect('banners.php');
        } else {
            $errors[] = 'Error al crear el banner. Intente nuevamente.';
        }
    }
}

$title = 'Crear Banner';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
            Crear Nuevo Banner
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
                    <input type="text" name="nombre" required value="<?php echo e($_POST['nombre'] ?? ''); ?>"
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
                        <option value="<?php echo $key; ?>" <?php echo (isset($_POST['ubicacion']) && $_POST['ubicacion'] === $key) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $key; ?>" <?php echo (isset($_POST['orientacion']) && $_POST['orientacion'] === $key) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $key; ?>" <?php echo (isset($_POST['dispositivo']) && $_POST['dispositivo'] === $key) ? 'selected' : ''; ?>>
                            <?php echo e($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Orden de Aparición
                    </label>
                    <input type="number" name="orden" min="0" value="<?php echo e($_POST['orden'] ?? '0'); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primero</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen del Banner <span class="text-red-500">*</span>
                </label>
                <input type="file" name="imagen" id="imagen_principal" accept="image/*" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPG, PNG, GIF, WebP</p>
            </div>
            
            <!-- Sección de imágenes adicionales para banner rotativo -->
            <div id="imagenes_adicionales_container" style="display: none;">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">
                        <i class="fas fa-images mr-2"></i>
                        Imágenes Adicionales para Carrusel
                    </h3>
                    <p class="text-xs text-blue-700 mb-3">
                        Cuando activas "Banner rotativo", puedes agregar múltiples imágenes que se mostrarán en un carrusel.
                    </p>
                    <button type="button" onclick="agregarImagenAdicional()" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Agregar Imagen
                    </button>
                </div>
                
                <div id="lista_imagenes_adicionales" class="space-y-3">
                    <!-- Las imágenes adicionales se agregarán aquí dinámicamente -->
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    URL de Destino (Opcional)
                </label>
                <input type="url" name="url_destino" value="<?php echo e($_POST['url_destino'] ?? ''); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="https://ejemplo.com">
                <p class="mt-1 text-xs text-gray-500">URL a la que redirige al hacer clic en el banner</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Inicio (Opcional)
                    </label>
                    <input type="date" name="fecha_inicio" value="<?php echo e($_POST['fecha_inicio'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Fin (Opcional)
                    </label>
                    <input type="date" name="fecha_fin" value="<?php echo e($_POST['fecha_fin'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="space-y-3 border-t pt-4">
                <div class="flex items-center">
                    <input type="checkbox" name="activo" id="activo" value="1" <?php echo (empty($_POST) || isset($_POST['activo'])) ? 'checked' : ''; ?>
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="activo" class="ml-2 block text-sm text-gray-900">
                        Banner activo (visible en el sitio)
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="rotativo" id="rotativo" value="1" <?php echo isset($_POST['rotativo']) ? 'checked' : ''; ?>
                           onclick="toggleImagenesAdicionales()"
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
                    Guardar Banner
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let contadorImagenes = 0;

function toggleImagenesAdicionales() {
    const checkbox = document.getElementById('rotativo');
    const container = document.getElementById('imagenes_adicionales_container');
    const imagenPrincipal = document.getElementById('imagen_principal');
    
    if (checkbox.checked) {
        container.style.display = 'block';
        imagenPrincipal.required = false;
    } else {
        container.style.display = 'none';
        imagenPrincipal.required = true;
        // Limpiar imágenes adicionales
        document.getElementById('lista_imagenes_adicionales').innerHTML = '';
        contadorImagenes = 0;
    }
}

function agregarImagenAdicional() {
    contadorImagenes++;
    const lista = document.getElementById('lista_imagenes_adicionales');
    
    const div = document.createElement('div');
    div.className = 'border border-gray-300 rounded-lg p-4 bg-white';
    div.id = `imagen_adicional_${contadorImagenes}`;
    
    div.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-gray-700">Imagen ${contadorImagenes}</h4>
            <button type="button" onclick="eliminarImagenAdicional(${contadorImagenes})" 
                    class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <input type="file" name="imagenes_adicionales[]" accept="image/*" required
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <input type="hidden" name="imagenes_orden[]" value="${contadorImagenes}">
    `;
    
    lista.appendChild(div);
}

function eliminarImagenAdicional(id) {
    const elemento = document.getElementById(`imagen_adicional_${id}`);
    if (elemento) {
        elemento.remove();
    }
}

// Inicializar estado al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    toggleImagenesAdicionales();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
