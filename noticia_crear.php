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
    $tags = trim($_POST['tags'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $estado = $_POST['estado'] ?? 'borrador';
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $permitir_comentarios = isset($_POST['permitir_comentarios']) ? 1 : 0;
    $video_youtube = trim($_POST['video_youtube'] ?? '');
    $fecha_programada = !empty($_POST['fecha_programada']) ? $_POST['fecha_programada'] : null;
    
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
    
    // Manejar video local
    $video_url = null;
    if (isset($_FILES['video_local']) && $_FILES['video_local']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/videos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['video_local']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['mp4', 'webm', 'ogg'];
        
        if (in_array($extension, $allowedExtensions)) {
            $filename = 'video_' . bin2hex(random_bytes(16)) . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['video_local']['tmp_name'], $uploadPath)) {
                $video_url = '/public/uploads/videos/' . $filename;
            } else {
                $errors[] = 'Error al subir el video';
            }
        } else {
            $errors[] = 'Formato de video no permitido (solo MP4, WebM, OGG)';
        }
    }
    
    // Manejar thumbnail de video
    $video_thumbnail = null;
    $video_thumbnail_url = trim($_POST['video_thumbnail_url'] ?? '');
    
    // Si se proporciona una URL, usarla primero
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
    
    // Si no hay errores, crear noticia
    if (empty($errors)) {
        $currentUser = getCurrentUser();
        $data = [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'contenido' => $contenido,
            'resumen' => $resumen,
            'tags' => $tags,
            'categoria_id' => $categoria_id,
            'autor_id' => $currentUser['id'],
            'imagen_destacada' => $imagen_destacada,
            'estado' => $estado,
            'destacado' => $destacado,
            'permitir_comentarios' => $permitir_comentarios,
            'video_url' => $video_url,
            'video_youtube' => $video_youtube,
            'video_thumbnail' => $video_thumbnail,
            'video_thumbnail_url' => $video_thumbnail_url,
            'fecha_programada' => $fecha_programada
        ];
        
        $result = $noticiaModel->create($data);
        if ($result) {
            // Registrar auditoría
            $logModel = new Log();
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'noticias',
                'crear',
                'noticias',
                $result,
                null,
                $data
            );
            
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

            <!-- Tags / Palabras Clave -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Palabras Clave (Tags)
                </label>
                <input type="text" name="tags" value="<?php echo e($_POST['tags'] ?? ''); ?>"
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
                <textarea name="contenido" style="display:none;"><?php echo e($_POST['contenido'] ?? ''); ?></textarea>
            </div>

            <!-- Imagen Destacada -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen Destacada
                </label>
                <input type="file" name="imagen_destacada" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Sección de Videos -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-video mr-2 text-purple-600"></i>
                    Contenido de Video (Opcional)
                </h3>
                
                <div class="space-y-4">
                    <!-- Video de YouTube -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            URL de YouTube
                        </label>
                        <input type="text" name="video_youtube" value="<?php echo e($_POST['video_youtube'] ?? ''); ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://www.youtube.com/watch?v=... o ID del video">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i>
                            Pegue la URL completa o solo el ID del video de YouTube
                        </p>
                    </div>

                    <!-- Video Local -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Subir Video Local
                        </label>
                        <input type="file" name="video_local" accept="video/mp4,video/webm,video/ogg"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">
                            Formatos permitidos: MP4, WebM, OGG
                        </p>
                    </div>

                    <!-- Thumbnail de Video -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Imagen de Portada para Video (Thumbnail)
                        </label>
                        
                        <!-- Opción 1: URL -->
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Opción 1: Ingresar URL de la imagen
                            </label>
                            <input type="url" name="video_thumbnail_url" 
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
            </div>

            <!-- Programación de Publicación -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-clock mr-2 text-blue-600"></i>
                    Programación de Publicación
                </h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha y Hora Programada
                    </label>
                    <input type="datetime-local" name="fecha_programada" value="<?php echo e($_POST['fecha_programada'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i>
                        Si especifica una fecha, la noticia se publicará automáticamente en ese momento (debe estar en estado "Publicar")
                    </p>
                </div>
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
                    <input type="checkbox" name="permitir_comentarios" id="comentarios" value="1" <?php echo (empty($_POST) || (isset($_POST['permitir_comentarios']) && $_POST['permitir_comentarios'])) ? 'checked' : ''; ?>
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
var contenidoInicial = <?php echo json_encode($_POST['contenido'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP); ?>;
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
    
    // Validar campos requeridos
    var titulo = document.querySelector('input[name="titulo"]').value.trim();
    var categoriaId = document.querySelector('select[name="categoria_id"]').value;
    var contenidoTexto = quill.getText().trim();
    
    var errores = [];
    
    if (!titulo) {
        errores.push('El título es requerido');
    }
    
    if (!categoriaId) {
        errores.push('Debe seleccionar una categoría');
    }
    
    if (!contenidoTexto || contenidoTexto === '') {
        errores.push('El contenido de la noticia es requerido');
    }
    
    if (errores.length > 0) {
        e.preventDefault();
        alert('Por favor complete los siguientes campos:\n\n- ' + errores.join('\n- '));
        return false;
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
