<?php
/**
 * Gestión de Multimedia
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$multimediaModel = new Multimedia();
$errors = [];
$success = false;

// Procesar subida de archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $archivo = $_FILES['archivo'];
    $carpeta = trim($_POST['carpeta'] ?? 'general');
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $alt_text = trim($_POST['alt_text'] ?? '');
    
    if ($archivo['error'] === UPLOAD_ERR_OK) {
        $nombreOriginal = $archivo['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $tamanio = $archivo['size'];
        
        // Determinar tipo de archivo
        $tiposImagen = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $tiposVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        $tiposDocumento = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
        
        if (in_array($extension, $tiposImagen)) {
            $tipo = 'imagen';
        } elseif (in_array($extension, $tiposVideo)) {
            $tipo = 'video';
        } elseif (in_array($extension, $tiposDocumento)) {
            $tipo = 'documento';
        } else {
            $errors[] = 'Tipo de archivo no permitido';
            $tipo = null;
        }
        
        // Validar tamaño (10MB máximo)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($tamanio > $maxSize) {
            $errors[] = 'El archivo es demasiado grande. Tamaño máximo: 10MB';
        }
        
        // Si no hay errores, subir archivo
        if (empty($errors) && $tipo) {
            $uploadDir = __DIR__ . '/public/uploads/multimedia/' . $carpeta . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $nombreArchivo = uniqid('media_') . '.' . $extension;
            $rutaCompleta = $uploadDir . $nombreArchivo;
            $rutaRelativa = '/public/uploads/multimedia/' . $carpeta . '/' . $nombreArchivo;
            
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                // Guardar en base de datos
                $currentUser = getCurrentUser();
                $data = [
                    'nombre' => $nombreArchivo,
                    'nombre_original' => $nombreOriginal,
                    'tipo' => $tipo,
                    'ruta' => $rutaRelativa,
                    'carpeta' => $carpeta,
                    'tamanio' => $tamanio,
                    'extension' => $extension,
                    'titulo' => $titulo ?: $nombreOriginal,
                    'descripcion' => $descripcion,
                    'alt_text' => $alt_text ?: $titulo,
                    'usuario_id' => $currentUser['id']
                ];
                
                if ($multimediaModel->create($data)) {
                    setFlash('success', 'Archivo subido exitosamente');
                    redirect('multimedia.php');
                } else {
                    $errors[] = 'Error al guardar el archivo en la base de datos';
                    unlink($rutaCompleta); // Eliminar archivo si falla el registro
                }
            } else {
                $errors[] = 'Error al subir el archivo';
            }
        }
    } else {
        $errors[] = 'Error al cargar el archivo: ' . $archivo['error'];
    }
}

// Eliminar archivo
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($multimediaModel->delete($id)) {
        setFlash('success', 'Archivo eliminado exitosamente');
        redirect('multimedia.php');
    } else {
        setFlash('error', 'Error al eliminar el archivo');
    }
}

// Obtener filtros
$tipo = $_GET['tipo'] ?? null;
$carpeta = $_GET['carpeta'] ?? null;
$page = $_GET['page'] ?? 1;
$perPage = 12;

// Obtener archivos
$archivos = $multimediaModel->getAll($tipo, $carpeta, $page, $perPage);
$totalArchivos = $multimediaModel->count($tipo, $carpeta);
$totalPages = ceil($totalArchivos / $perPage);

// Obtener carpetas disponibles
$carpetas = $multimediaModel->getCarpetas();

$title = 'Gestión de Multimedia';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-images mr-2 text-green-600"></i>
            Gestión de Multimedia
        </h1>
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
            <i class="fas fa-upload mr-2"></i>
            Subir Archivo
        </button>
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

    <?php if ($flashSuccess = getFlash('success')): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-800"><?php echo e($flashSuccess); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Archivo</label>
                <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Todos</option>
                    <option value="imagen" <?php echo $tipo === 'imagen' ? 'selected' : ''; ?>>Imágenes</option>
                    <option value="video" <?php echo $tipo === 'video' ? 'selected' : ''; ?>>Videos</option>
                    <option value="documento" <?php echo $tipo === 'documento' ? 'selected' : ''; ?>>Documentos</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Carpeta</label>
                <select name="carpeta" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Todas</option>
                    <?php foreach ($carpetas as $carp): ?>
                    <option value="<?php echo e($carp); ?>" <?php echo $carpeta === $carp ? 'selected' : ''; ?>>
                        <?php echo e($carp); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Grid de Archivos -->
    <div class="bg-white rounded-lg shadow p-6">
        <?php if (empty($archivos)): ?>
        <div class="text-center py-12">
            <i class="fas fa-images text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No hay archivos multimedia</p>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                    class="mt-4 text-green-600 hover:text-green-800">
                Subir el primer archivo
            </button>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($archivos as $archivo): ?>
            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Previsualización -->
                <div class="relative h-40 bg-gray-100 flex items-center justify-center">
                    <?php if ($archivo['tipo'] === 'imagen'): ?>
                    <img src="<?php echo e($archivo['ruta']); ?>" alt="<?php echo e($archivo['alt_text']); ?>" 
                         class="w-full h-full object-cover">
                    <?php elseif ($archivo['tipo'] === 'video'): ?>
                    <i class="fas fa-video text-6xl text-gray-400"></i>
                    <?php else: ?>
                    <i class="fas fa-file text-6xl text-gray-400"></i>
                    <?php endif; ?>
                    
                    <!-- Badge de tipo -->
                    <span class="absolute top-2 right-2 px-2 py-1 rounded text-xs font-medium <?php 
                        echo match($archivo['tipo']) {
                            'imagen' => 'bg-blue-100 text-blue-800',
                            'video' => 'bg-purple-100 text-purple-800',
                            'documento' => 'bg-green-100 text-green-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    ?>">
                        <?php echo ucfirst($archivo['tipo']); ?>
                    </span>
                </div>
                
                <!-- Información -->
                <div class="p-3">
                    <p class="text-sm font-medium text-gray-900 truncate" title="<?php echo e($archivo['titulo']); ?>">
                        <?php echo e($archivo['titulo']); ?>
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        <?php echo e($archivo['nombre_original']); ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-folder mr-1"></i><?php echo e($archivo['carpeta']); ?> • 
                        <?php echo round($archivo['tamanio'] / 1024, 2); ?> KB
                    </p>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-calendar mr-1"></i>
                        <?php echo date('d/m/Y', strtotime($archivo['fecha_subida'])); ?>
                    </p>
                    
                    <!-- Acciones -->
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-200">
                        <button onclick="copyToClipboard('<?php echo e($archivo['ruta']); ?>')" 
                                class="text-blue-600 hover:text-blue-800 text-xs">
                            <i class="fas fa-copy mr-1"></i>Copiar URL
                        </button>
                        <a href="<?php echo url('multimedia.php?delete=' . $archivo['id']); ?>" 
                           onclick="return confirm('¿Eliminar este archivo?')" 
                           class="text-red-600 hover:text-red-800 text-xs">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&tipo=<?php echo $tipo; ?>&carpeta=<?php echo $carpeta; ?>" 
                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium
                          <?php echo $i === (int)$page ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Información -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Características del Sistema de Multimedia
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Carga de imágenes (JPG, PNG, GIF, WebP, SVG)</li>
                        <li>Carga de videos (MP4, AVI, MOV, WebM)</li>
                        <li>Carga de documentos (PDF, DOC, XLS, PPT, ZIP)</li>
                        <li>Organización por carpetas</li>
                        <li>Gestión de metadatos (título, descripción, ALT)</li>
                        <li>Tamaño máximo por archivo: 10MB</li>
                        <li>Reutilización en múltiples noticias</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Subida -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-upload mr-2 text-green-600"></i>
                Subir Archivo
            </h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" 
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Archivo <span class="text-red-500">*</span>
                </label>
                <input type="file" name="archivo" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                <p class="text-xs text-gray-500 mt-1">Máximo 10MB. Formatos: imágenes, videos, documentos</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Carpeta
                </label>
                <input type="text" name="carpeta" value="general"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Nombre de la carpeta">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Título
                </label>
                <input type="text" name="titulo"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Título descriptivo del archivo">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción
                </label>
                <textarea name="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                          placeholder="Descripción del archivo"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Texto ALT (para imágenes)
                </label>
                <input type="text" name="alt_text"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Texto alternativo para accesibilidad">
            </div>
            
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <button type="button" 
                        onclick="document.getElementById('uploadModal').classList.add('hidden')"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-upload mr-2"></i>
                    Subir Archivo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyToClipboard(text) {
    const fullUrl = window.location.origin + text;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(fullUrl).then(() => {
            alert('URL copiada al portapapeles');
        }).catch((err) => {
            // Fallback para navegadores antiguos
            fallbackCopyToClipboard(fullUrl);
        });
    } else {
        fallbackCopyToClipboard(fullUrl);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "0";
    textArea.style.left = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        alert('URL copiada al portapapeles');
    } catch (err) {
        alert('Error al copiar la URL');
    }
    document.body.removeChild(textArea);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>

