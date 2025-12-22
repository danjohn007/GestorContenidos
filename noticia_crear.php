<?php
/**
 * Crear Noticia
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$categoriaModel = new Categoria();
$noticiaModel = new Noticia();
$categorias = $categoriaModel->getAll(1);
$errors = [];
$success = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $resumen = trim($_POST['resumen'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $estado = $_POST['estado'] ?? 'borrador';
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $permitir_comentarios = isset($_POST['permitir_comentarios']) ? 1 : 0;
    
    // Validaciones
    if (empty($titulo)) {
        $errors[] = 'El título es requerido';
    }
    if (empty($contenido)) {
        $errors[] = 'El contenido es requerido';
    }
    if (empty($categoria_id)) {
        $errors[] = 'Debe seleccionar una categoría';
    }
    
    // Manejar imagen destacada
    $imagen_destacada = null;
    if (isset($_FILES['imagen_destacada']) && $_FILES['imagen_destacada']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/noticias/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['imagen_destacada']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            $filename = uniqid('noticia_') . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagen_destacada']['tmp_name'], $uploadPath)) {
                $imagen_destacada = '/public/uploads/noticias/' . $filename;
            } else {
                $errors[] = 'Error al subir la imagen';
            }
        } else {
            $errors[] = 'Formato de imagen no permitido';
        }
    }
    
    // Si no hay errores, crear noticia
    if (empty($errors)) {
        $currentUser = getCurrentUser();
        $data = [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'contenido' => $contenido,
            'resumen' => $resumen,
            'categoria_id' => $categoria_id,
            'autor_id' => $currentUser['id'],
            'imagen_destacada' => $imagen_destacada,
            'estado' => $estado,
            'destacado' => $destacado,
            'permitir_comentarios' => $permitir_comentarios
        ];
        
        $result = $noticiaModel->create($data);
        if ($result) {
            setFlash('success', 'Noticia creada exitosamente');
            redirect('noticias.php');
        } else {
            $errors[] = 'Error al crear la noticia. Intente nuevamente.';
        }
    }
}

$title = 'Crear Noticia';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
            Crear Nueva Noticia
        </h1>
        <a href="<?php echo url('noticias.php'); ?>" class="text-gray-600 hover:text-gray-900">
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
            <!-- Título -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" required value="<?php echo e($_POST['titulo'] ?? ''); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ingresa el título de la noticia">
            </div>

            <!-- Subtítulo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Subtítulo
                </label>
                <input type="text" name="subtitulo" value="<?php echo e($_POST['subtitulo'] ?? ''); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Subtítulo o bajada">
            </div>

            <!-- Categoría -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Categoría <span class="text-red-500">*</span>
                </label>
                <select name="categoria_id" required 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo e($cat['nombre']); ?>
                        <?php if ($cat['padre_id']): ?> (Subcategoría)<?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Resumen -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Resumen
                </label>
                <textarea name="resumen" rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Breve resumen de la noticia"><?php echo e($_POST['resumen'] ?? ''); ?></textarea>
            </div>

            <!-- Contenido -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Contenido <span class="text-red-500">*</span>
                </label>
                <textarea name="contenido" rows="12" required 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Contenido completo de la noticia"><?php echo e($_POST['contenido'] ?? ''); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle"></i>
                    Editor WYSIWYG disponible en la versión completa
                </p>
            </div>

            <!-- Imagen Destacada -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen Destacada
                </label>
                <input type="file" name="imagen_destacada" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                </label>
                <select name="estado" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="borrador" <?php echo (isset($_POST['estado']) && $_POST['estado'] == 'borrador') ? 'selected' : ''; ?>>Borrador</option>
                    <option value="revision" <?php echo (isset($_POST['estado']) && $_POST['estado'] == 'revision') ? 'selected' : ''; ?>>En Revisión</option>
                    <?php if (hasPermission('all') || hasPermission('noticias')): ?>
                    <option value="publicado" <?php echo (isset($_POST['estado']) && $_POST['estado'] == 'publicado') ? 'selected' : ''; ?>>Publicar</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Opciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="destacado" id="destacado" value="1" <?php echo (isset($_POST['destacado'])) ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="destacado" class="ml-2 block text-sm text-gray-900">
                        Marcar como destacado
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="permitir_comentarios" id="comentarios" value="1" <?php echo (!isset($_POST['permitir_comentarios']) || isset($_POST['permitir_comentarios'])) ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="comentarios" class="ml-2 block text-sm text-gray-900">
                        Permitir comentarios
                    </label>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('noticias.php'); ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Noticia
                </button>
            </div>
        </form>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Información
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>El sistema guardará la noticia en la base de datos. Puedes subir una imagen destacada en formato JPG, PNG, GIF o WebP.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
