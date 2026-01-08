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
    
    // First check if image was selected from gallery
    if (!empty($_POST['imagen_destacada_url'])) {
        $imagen_destacada = trim($_POST['imagen_destacada_url']);
    }
    // Otherwise check if file was uploaded
    elseif (isset($_FILES['imagen_destacada']) && $_FILES['imagen_destacada']['error'] === UPLOAD_ERR_OK) {
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
                // Eliminar imagen anterior si existe y no es de la galería multimedia
                if ($noticia['imagen_destacada'] && strpos($noticia['imagen_destacada'], '/public/uploads/noticias/') !== false) {
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
    
    // Manejar thumbnail de video
    $video_thumbnail = null;
    $video_thumbnail_url = trim($_POST['video_thumbnail_url'] ?? '');
    
    // Si se proporciona una URL, validarla
    if (!empty($video_thumbnail_url)) {
        $video_thumbnail_url = filter_var($video_thumbnail_url, FILTER_VALIDATE_URL) ? $video_thumbnail_url : null;
        if (!$video_thumbnail_url) {
            $errors[] = 'La URL del thumbnail de video no es válida';
        }
    }
    
    // Si se sube un archivo, toma prioridad sobre la URL
    if (isset($_FILES['video_thumbnail']) && $_FILES['video_thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/noticias/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['video_thumbnail']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            // Validate MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $errors[] = 'Error al validar el tipo de archivo del thumbnail';
            } else {
                $mimeType = finfo_file($finfo, $_FILES['video_thumbnail']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = 'Tipo de archivo de thumbnail no válido';
                } else {
                    // Delete old thumbnail if exists
                    if (!empty($noticia['video_thumbnail'])) {
                        $oldThumbPath = __DIR__ . $noticia['video_thumbnail'];
                        if (file_exists($oldThumbPath) && is_file($oldThumbPath)) {
                            @unlink($oldThumbPath);
                        }
                    }
                    $filename = 'thumb_' . bin2hex(random_bytes(16)) . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['video_thumbnail']['tmp_name'], $uploadPath)) {
                        $video_thumbnail = '/public/uploads/noticias/' . $filename;
                        // Limpiar URL si se subió archivo
                        $video_thumbnail_url = null;
                    } else {
                        $errors[] = 'Error al subir el thumbnail del video';
                    }
                }
            }
        } else {
            $errors[] = 'Formato de thumbnail no permitido';
        }
    }
    
    // Si no hay errores, actualizar noticia
    if (empty($errors)) {
        $fecha_programada = !empty($_POST['fecha_programada']) ? $_POST['fecha_programada'] : null;
        $video_url = trim($_POST['video_url'] ?? '');
        $video_youtube = trim($_POST['video_youtube'] ?? '');
        
        $data = [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'contenido' => $contenido,
            'resumen' => $resumen,
            'tags' => $tags,
            'categoria_id' => $categoria_id,
            'imagen_destacada' => $imagen_destacada,
            'video_url' => !empty($video_url) ? $video_url : null,
            'video_youtube' => !empty($video_youtube) ? $video_youtube : null,
            'video_thumbnail' => $video_thumbnail !== null ? $video_thumbnail : $noticia['video_thumbnail'],
            'video_thumbnail_url' => $video_thumbnail_url !== null ? $video_thumbnail_url : ($noticia['video_thumbnail_url'] ?? null),
            'estado' => $estado,
            'destacado' => $destacado,
            'permitir_comentarios' => $permitir_comentarios,
            'fecha_programada' => $fecha_programada,
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
                <div id="contenido" style="height: 400px; background: white;"></div>
                <textarea name="contenido" style="display:none;"><?php echo e($_POST['contenido'] ?? $noticia['contenido']); ?></textarea>
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
                <div class="flex items-center space-x-3">
                    <input type="file" name="imagen_destacada" accept="image/*" id="imagen_destacada_file"
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="openMediaGallery('imagen_destacada')"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors whitespace-nowrap">
                        <i class="fas fa-images mr-2"></i>
                        Galería
                    </button>
                </div>
                <input type="hidden" name="imagen_destacada_url" id="imagen_destacada_url">
                <div id="imagen_destacada_preview" class="mt-3 hidden">
                    <p class="text-sm text-gray-600 mb-2">Vista previa de la galería:</p>
                    <img id="imagen_destacada_preview_img" src="" alt="Preview" class="h-32 rounded border border-gray-300">
                    <button type="button" onclick="clearMediaSelection('imagen_destacada')"
                            class="mt-2 text-sm text-red-600 hover:text-red-800">
                        <i class="fas fa-times mr-1"></i>
                        Remover selección
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Deja vacío para mantener la imagen actual o selecciona de la galería</p>
            </div>

            <!-- Videos (YouTube o Local) -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-video mr-2 text-blue-600"></i>
                    Contenido de Video
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Puedes agregar un video de YouTube o subir un video local. El video se mostrará en la noticia.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Video YouTube -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fab fa-youtube text-red-600 mr-1"></i>
                            Video de YouTube
                        </label>
                        <input type="text" name="video_youtube" value="<?php echo e($_POST['video_youtube'] ?? $noticia['video_youtube'] ?? ''); ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="ID o URL de YouTube (ej: dQw4w9WgXcQ)">
                        <p class="text-xs text-gray-500 mt-1">
                            Ingresa el ID del video o la URL completa
                        </p>
                    </div>
                    
                    <!-- Video Local URL -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-file-video mr-1"></i>
                            Video Local (URL)
                        </label>
                        <input type="text" name="video_url" value="<?php echo e($_POST['video_url'] ?? $noticia['video_url'] ?? ''); ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="/public/uploads/videos/mi-video.mp4">
                        <p class="text-xs text-gray-500 mt-1">
                            Ruta del archivo de video subido
                        </p>
                    </div>
                </div>
                
                <!-- Video Thumbnail -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-image mr-1"></i>
                        Imagen de Portada del Video (Thumbnail)
                    </label>
                    
                    <?php if (!empty($noticia['video_thumbnail'])): ?>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Thumbnail Actual (Archivo)</label>
                        <img src="<?php echo e(BASE_URL . $noticia['video_thumbnail']); ?>" alt="Thumbnail actual" class="h-32 border border-gray-300 rounded">
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($noticia['video_thumbnail_url'])): ?>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Thumbnail Actual (URL)</label>
                        <img src="<?php echo e($noticia['video_thumbnail_url']); ?>" alt="Thumbnail actual" class="h-32 border border-gray-300 rounded">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Opción 1: URL -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Opción 1: Ingresar URL de la imagen
                        </label>
                        <input type="url" name="video_thumbnail_url" 
                               value="<?php echo e($_POST['video_thumbnail_url'] ?? $noticia['video_thumbnail_url'] ?? ''); ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://ejemplo.com/imagen.jpg">
                    </div>
                    
                    <!-- Opción 2: Subir archivo -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Opción 2: Subir imagen desde el equipo
                        </label>
                        <input type="file" name="video_thumbnail" accept="image/*"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle"></i>
                        Imagen que se mostrará antes de reproducir el video. Puedes ingresar una URL o subir un archivo (si subes un archivo, este tendrá prioridad).
                    </p>
                </div>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                </label>
                <select name="estado" id="estado-select"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="borrador" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'borrador' : $noticia['estado'] == 'borrador') ? 'selected' : ''; ?>>Borrador</option>
                    <option value="revision" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'revision' : $noticia['estado'] == 'revision') ? 'selected' : ''; ?>>En Revisión</option>
                    <?php if (hasPermission('all') || hasPermission('noticias')): ?>
                    <option value="aprobado" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'aprobado' : $noticia['estado'] == 'aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                    <option value="publicado" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'publicado' : $noticia['estado'] == 'publicado') ? 'selected' : ''; ?>>Publicado</option>
                    <option value="programado" <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'programado' : $noticia['estado'] == 'programado') ? 'selected' : ''; ?>>Programado</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Fecha Programada (solo visible cuando estado es 'programado') -->
            <div id="fecha-programada-container" style="display: <?php echo (isset($_POST['estado']) ? $_POST['estado'] == 'programado' : $noticia['estado'] == 'programado') ? 'block' : 'none'; ?>;">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Fecha y Hora de Publicación
                </label>
                <input type="datetime-local" name="fecha_programada" id="fecha-programada"
                       value="<?php echo e(isset($_POST['fecha_programada']) ? $_POST['fecha_programada'] : ($noticia['fecha_programada'] ? date('Y-m-d\TH:i', strtotime($noticia['fecha_programada'])) : '')); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    La noticia se publicará automáticamente en la fecha y hora indicada
                </p>
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

<!-- Quill.js Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
/* Quill alignment styles for editor */
.ql-editor .ql-align-center {
    text-align: center;
}
.ql-editor .ql-align-right {
    text-align: right;
}
.ql-editor .ql-align-justify {
    text-align: justify;
}
/* Ensure alignment persists in saved content */
.ql-align-center {
    text-align: center;
}
.ql-align-right {
    text-align: right;
}
.ql-align-justify {
    text-align: justify;
}
</style>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
// Inicializar Quill.js
var quill = new Quill('#contenido', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'font': [] }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'align': [] }],
            ['blockquote', 'code-block'],
            ['link', 'image', 'video'],
            ['clean']
        ]
    },
    placeholder: 'Escribe el contenido de la noticia aquí...'
});

// Cargar contenido inicial si existe
var contenidoInicial = <?php echo json_encode($_POST['contenido'] ?? $noticia['contenido'], JSON_HEX_TAG | JSON_HEX_AMP); ?>;
if (contenidoInicial) {
    quill.root.innerHTML = contenidoInicial;
}

// Cachear referencia al campo de contenido para mejor rendimiento
var contenidoField = document.querySelector('textarea[name="contenido"]');
var form = document.querySelector('form');

// Al enviar el formulario, copiar el contenido de Quill al textarea oculto
form.addEventListener('submit', function(e) {
    // Asegurarse de que el contenido se copie antes de enviar
    contenidoField.value = quill.root.innerHTML;
    
    // Validar que haya contenido
    var contenidoTexto = quill.getText().trim();
    if (!contenidoTexto || contenidoTexto === '') {
        e.preventDefault();
        alert('Por favor ingresa el contenido de la noticia');
        return false;
    }
});

// Toggle fecha programada field based on estado
document.getElementById('estado-select').addEventListener('change', function() {
    var fechaProgramadaContainer = document.getElementById('fecha-programada-container');
    if (this.value === 'programado') {
        fechaProgramadaContainer.style.display = 'block';
    } else {
        fechaProgramadaContainer.style.display = 'none';
    }
});

// Media Gallery Modal Functions
var currentMediaField = null;

function openMediaGallery(fieldName) {
    currentMediaField = fieldName;
    document.getElementById('mediaGalleryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loadMediaGallery();
}

function closeMediaGallery() {
    document.getElementById('mediaGalleryModal').classList.add('hidden');
    document.body.style.overflow = '';
    currentMediaField = null;
}

function loadMediaGallery(page = 1) {
    const tipo = 'imagen'; // For now, only images
    const galleryContainer = document.getElementById('mediaGalleryContainer');
    
    galleryContainer.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i></div>';
    
    fetch(`<?php echo url('api/multimedia_list.php'); ?>?tipo=${tipo}&page=${page}&perPage=12`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                let html = '<div class="grid grid-cols-3 gap-4">';
                data.data.forEach(media => {
                    html += `
                        <div class="relative group cursor-pointer border-2 border-transparent hover:border-blue-500 rounded-lg overflow-hidden" onclick="selectMedia('${media.ruta}', '${media.titulo}')">
                            <img src="<?php echo BASE_URL; ?>${media.ruta}" alt="${media.titulo || ''}" class="w-full h-32 object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-3xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <p class="text-white text-xs truncate">${media.titulo || media.nombre_original}</p>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                // Pagination
                if (data.totalPages > 1) {
                    html += '<div class="mt-6 flex justify-center space-x-2">';
                    for (let i = 1; i <= data.totalPages; i++) {
                        const active = i === page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300';
                        html += `<button type="button" onclick="loadMediaGallery(${i})" class="${active} px-3 py-1 rounded">${i}</button>`;
                    }
                    html += '</div>';
                }
                
                galleryContainer.innerHTML = html;
            } else {
                galleryContainer.innerHTML = '<div class="text-center py-8 text-gray-500">No hay imágenes disponibles en la galería</div>';
            }
        })
        .catch(error => {
            console.error('Error loading media:', error);
            galleryContainer.innerHTML = '<div class="text-center py-8 text-red-500">Error al cargar la galería</div>';
        });
}

function selectMedia(ruta, titulo) {
    if (!currentMediaField) return;
    
    // Set hidden field with URL
    document.getElementById(currentMediaField + '_url').value = ruta;
    
    // Show preview
    const preview = document.getElementById(currentMediaField + '_preview');
    const previewImg = document.getElementById(currentMediaField + '_preview_img');
    
    preview.classList.remove('hidden');
    previewImg.src = '<?php echo BASE_URL; ?>' + ruta;
    previewImg.alt = titulo;
    
    // Clear file input if user selected from gallery
    const fileInput = document.getElementById(currentMediaField + '_file');
    if (fileInput) {
        fileInput.value = '';
    }
    
    closeMediaGallery();
}

function clearMediaSelection(fieldName) {
    document.getElementById(fieldName + '_url').value = '';
    document.getElementById(fieldName + '_preview').classList.add('hidden');
    document.getElementById(fieldName + '_file').value = '';
}
</script>

<!-- Media Gallery Modal -->
<div id="mediaGalleryModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeMediaGallery()">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-images mr-2 text-purple-600"></i>
                        Galería de Multimedia
                    </h3>
                    <button type="button" onclick="closeMediaGallery()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="mediaGalleryContainer" class="max-h-96 overflow-y-auto">
                    <!-- Gallery will be loaded here -->
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeMediaGallery()" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
