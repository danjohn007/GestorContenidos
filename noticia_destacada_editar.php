<?php
/**
 * Editar Noticia Destacada (Solo Imagen)
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$noticiaDestacadaImagenModel = new NoticiaDestacadaImagen();
$noticiaModel = new Noticia();
$errors = [];

// Obtener ID
$destacadaId = $_GET['id'] ?? null;

if (!$destacadaId) {
    setFlash('error', 'ID no especificado');
    redirect('noticias_destacadas.php');
}

// Obtener datos
$destacada = $noticiaDestacadaImagenModel->getById($destacadaId);

if (!$destacada) {
    setFlash('error', 'Noticia destacada no encontrada');
    redirect('noticias_destacadas.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim(isset($_POST['titulo']) ? $_POST['titulo'] : $destacada['titulo']);
    $url_destino = trim(isset($_POST['url_destino']) ? $_POST['url_destino'] : $destacada['url_destino']);
    $noticia_id = !empty($_POST['noticia_id']) ? (int)$_POST['noticia_id'] : null;
    $ubicacion = $_POST['ubicacion'] ?? 'bajo_slider';
    $vista = $_POST['vista'] ?? 'grid';
    $orden = (int)($_POST['orden'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
    
    // Validaciones
    if (empty($titulo)) {
        $errors[] = 'El título es requerido';
    }
    
    // Manejar imagen
    $imagen_url = $destacada['imagen_url'];
    
    // Si se seleccionó una noticia, usar su imagen
    if ($noticia_id) {
        $noticia = $noticiaModel->getById($noticia_id);
        if ($noticia && $noticia['imagen_destacada']) {
            $imagen_url = $noticia['imagen_destacada'];
            // Si hay noticia, usar su URL
            if (empty($url_destino)) {
                $url_destino = url('noticia_detalle.php?slug=' . $noticia['slug']);
            }
        }
    }
    
    // Si se subió archivo nuevo, reemplazar
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/destacadas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            $filename = uniqid('destacada_') . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                // Eliminar imagen anterior si no es de noticia
                if (!$destacada['noticia_id'] && $destacada['imagen_url']) {
                    $oldImagePath = __DIR__ . $destacada['imagen_url'];
                    if (file_exists($oldImagePath) && strpos(realpath($oldImagePath), realpath(__DIR__)) === 0) {
                        unlink($oldImagePath);
                    }
                }
                $imagen_url = '/public/uploads/destacadas/' . $filename;
            } else {
                $errors[] = 'Error al subir la imagen';
            }
        } else {
            $errors[] = 'Formato de imagen no permitido';
        }
    }
    
    // Si no hay errores, actualizar
    if (empty($errors)) {
        $data = [
            'titulo' => $titulo,
            'imagen_url' => $imagen_url,
            'url_destino' => $url_destino,
            'noticia_id' => $noticia_id,
            'ubicacion' => $ubicacion,
            'vista' => $vista,
            'orden' => $orden,
            'activo' => $activo,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ];
        
        $result = $noticiaDestacadaImagenModel->update($destacadaId, $data);
        if ($result) {
            setFlash('success', 'Noticia destacada actualizada exitosamente');
            redirect('noticias_destacadas.php');
        } else {
            $errors[] = 'Error al actualizar la noticia destacada. Intente nuevamente.';
        }
    }
}

// Obtener noticias publicadas para selector
$noticias = $noticiaModel->getAll('publicado', null, 1, 100);

$title = 'Editar Noticia Destacada';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-plus mr-2 text-blue-600"></i>
            Editar Noticia Destacada (Solo Imagen)
        </h1>
        <a href="<?php echo url('noticias_destacadas.php'); ?>" class="text-gray-600 hover:text-gray-900">
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Título (para administración) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" required value="<?php echo e(isset($_POST['titulo']) ? $_POST['titulo'] : $destacada['titulo']); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Seleccionar Noticia (opcional)
                </label>
                <select name="noticia_id" id="noticia_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Subir imagen manual --</option>
                    <?php foreach ($noticias as $noticia): ?>
                    <option value="<?php echo $noticia['id']; ?>" <?php echo (isset($_POST['noticia_id']) && $_POST['noticia_id'] == $noticia['id']) ? 'selected' : ''; ?>>
                        <?php echo e($noticia['titulo']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Si selecciona una noticia, se usará su imagen destacada y enlace
                </p>
            </div>

            <div id="imagen-manual-container">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen
                </label>
                <input type="file" name="imagen" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    Formatos permitidos: JPG, PNG, GIF, WebP
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    URL de Destino
                </label>
                <input type="url" name="url_destino" value="<?php echo e(isset($_POST['url_destino']) ? $_POST['url_destino'] : $destacada['url_destino']); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="https://...">
                <p class="text-xs text-gray-500 mt-1">
                    Si seleccionó una noticia, esto se llenará automáticamente
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ubicación <span class="text-red-500">*</span>
                    </label>
                    <select name="ubicacion" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach (NoticiaDestacadaImagen::getUbicaciones() as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($_POST['ubicacion']) && $_POST['ubicacion'] === $key) ? 'selected' : ''; ?>>
                            <?php echo e($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Vista <span class="text-red-500">*</span>
                    </label>
                    <select name="vista" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach (NoticiaDestacadaImagen::getVistas() as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($_POST['vista']) && $_POST['vista'] === $key) ? 'selected' : ''; ?>>
                            <?php echo e($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Orden
                    </label>
                    <input type="number" name="orden" value="<?php echo e($_POST['orden'] ?? 0); ?>" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha Inicio (opcional)
                    </label>
                    <input type="date" name="fecha_inicio" value="<?php echo e($_POST['fecha_inicio'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha Fin (opcional)
                    </label>
                    <input type="date" name="fecha_fin" value="<?php echo e($_POST['fecha_fin'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="activo" id="activo" value="1" <?php echo (!isset($_POST['activo']) || $_POST['activo']) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="activo" class="ml-2 block text-sm text-gray-900">
                    Activo (visible en el sitio)
                </label>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('noticias_destacadas.php'); ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Editar Noticia Destacada
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
