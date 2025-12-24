<?php
/**
 * Editar Noticia
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$categoriaModel = new Categoria();
$noticiaModel = new Noticia();
$categorias = $categoriaModel->getAll(1);
$errors = [];
$success = false;

// Obtener ID de la noticia
$noticiaId = $_GET['id'] ?? null;

if (!$noticiaId) {
    setFlash('error', 'ID de noticia no especificado');
    redirect('noticias.php');
}

// Obtener datos de la noticia
$noticia = $noticiaModel->getById($noticiaId);

if (!$noticia) {
    setFlash('error', 'Noticia no encontrada');
    redirect('noticias.php');
}

// Verificar permisos (el autor puede editar su propia noticia o usuario con permiso de editar todas)
$currentUser = getCurrentUser();
if ($noticia['autor_id'] != $currentUser['id'] && !hasPermission('noticias.editar') && !hasPermission('all')) {
    setFlash('error', 'No tienes permisos para editar esta noticia');
    redirect('noticias.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $resumen = trim($_POST['resumen'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
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
    $imagen_destacada = $noticia['imagen_destacada'];
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
                // Eliminar imagen anterior si existe
                if ($noticia['imagen_destacada']) {
                    $oldImagePath = __DIR__ . $noticia['imagen_destacada'];
                    // Ensure the path is safe and within the allowed directory
                    if (file_exists($oldImagePath) && strpos(realpath($oldImagePath), realpath(__DIR__)) === 0) {
                        unlink($oldImagePath);
                    }
                }
                $imagen_destacada = '/public/uploads/noticias/' . $filename;
            } else {
                $errors[] = 'Error al subir la imagen';
            }
        } else {
            $errors[] = 'Formato de imagen no permitido';
        }
    }
    
    // Si no hay errores, actualizar noticia
    if (empty($errors)) {
        $data = [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'contenido' => $contenido,
            'resumen' => $resumen,
            'tags' => $tags,
            'categoria_id' => $categoria_id,
            'imagen_destacada' => $imagen_destacada,
            'estado' => $estado,
            'destacado' => $destacado,
            'permitir_comentarios' => $permitir_comentarios,
            'modificado_por' => $currentUser['id']
        ];
        
        $result = $noticiaModel->update($noticiaId, $data);
        if ($result) {
            // Registrar auditoría
            $logModel = new Log();
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'noticias',
                'actualizar',
                'noticias',
                $noticiaId,
                $noticia,
                $data
            );
            
            setFlash('success', 'Noticia actualizada exitosamente');
            redirect('noticias.php');
        } else {
            $errors[] = 'Error al actualizar la noticia. Intente nuevamente.';
        }
    }
}

$title = 'Editar Noticia';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-edit mr-2 text-blue-600"></i>
            Editar Noticia
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
                <input type="text" name="titulo" required value="<?php echo e($_POST['titulo'] ?? $noticia['titulo']); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ingresa el título de la noticia">
            </div>

            <!-- Subtítulo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Subtítulo
                </label>
                <input type="text" name="subtitulo" value="<?php echo e($_POST['subtitulo'] ?? $noticia['subtitulo']); ?>"
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
                    <option value="<?php echo $cat['id']; ?>" 
                        <?php echo (isset($_POST['categoria_id']) ? $_POST['categoria_id'] == $cat['id'] : $noticia['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
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
                          placeholder="Breve resumen de la noticia"><?php echo e($_POST['resumen'] ?? $noticia['resumen']); ?></textarea>
            </div>

            <!-- Tags / Palabras Clave -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Palabras Clave (Tags)
                </label>
                <input type="text" name="tags" value="<?php echo e($_POST['tags'] ?? $noticia['tags']); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Separa las palabras clave con comas (ej: política, economía, salud)">
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle"></i>
                    Las palabras clave ayudan a indexar y buscar las noticias
                </p>
            </div>

            <!-- Contenido -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Contenido <span class="text-red-500">*</span>
                </label>
                <textarea id="contenido" name="contenido" rows="12" required 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Contenido completo de la noticia"><?php echo e($_POST['contenido'] ?? $noticia['contenido']); ?></textarea>
            </div>

            <!-- Imagen Destacada -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen Destacada
                </label>
                <?php if ($noticia['imagen_destacada']): ?>
                <div class="mb-3">
                    <img src="<?php echo e(BASE_URL . $noticia['imagen_destacada']); ?>" alt="Imagen actual" class="h-32 rounded">
                    <p class="text-xs text-gray-500 mt-1">Imagen actual</p>
                </div>
                <?php endif; ?>
                <input type="file" name="imagen_destacada" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Deja vacío para mantener la imagen actual</p>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                </label>
                <select name="estado" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="borrador" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'borrador' : $noticia['estado'] == 'borrador') ? 'selected' : ''; ?>>Borrador</option>
                    <option value="revision" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'revision' : $noticia['estado'] == 'revision') ? 'selected' : ''; ?>>En Revisión</option>
                    <?php if (hasPermission('all') || hasPermission('noticias')): ?>
                    <option value="aprobado" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'aprobado' : $noticia['estado'] == 'aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                    <option value="publicado" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'publicado' : $noticia['estado'] == 'publicado') ? 'selected' : ''; ?>>Publicado</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Opciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="destacado" id="destacado" value="1" 
                        <?php echo (isset($_POST['destacado']) ? ($_POST['destacado'] ? 'checked' : '') : ($noticia['destacado'] ? 'checked' : '')); ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="destacado" class="ml-2 block text-sm text-gray-900">
                        Marcar como destacado
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="permitir_comentarios" id="comentarios" value="1" 
                        <?php echo (isset($_POST['permitir_comentarios']) ? ($_POST['permitir_comentarios'] ? 'checked' : '') : ($noticia['permitir_comentarios'] ? 'checked' : '')); ?>
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
                    Actualizar Noticia
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/<?php echo htmlspecialchars(defined('TINYMCE_API_KEY') ? TINYMCE_API_KEY : 'no-api-key', ENT_QUOTES, 'UTF-8'); ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#contenido',
    height: 500,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
             'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
             'bullist numlist outdent indent | removeformat | link image media | help',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
    language: 'es',
    branding: false,
    promotion: false,
    statusbar: true,
    resize: true,
    init_instance_callback: function (editor) {
        // Forzar que el editor sea editable incluso sin API key válida
        editor.mode.set('design');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
