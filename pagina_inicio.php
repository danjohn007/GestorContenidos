<?php
/**
 * Gestión de Página de Inicio
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$paginaInicioModel = new PaginaInicio();
$menuItemModel = new MenuItem();
$categoriaModel = new Categoria();
$errors = [];
$success = false;

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $data = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'subtitulo' => trim($_POST['subtitulo'] ?? ''),
        'contenido' => trim($_POST['contenido'] ?? ''),
        'url' => trim($_POST['url'] ?? ''),
        'orden' => (int)($_POST['orden'] ?? 0),
        'activo' => isset($_POST['activo']) ? 1 : 0
    ];
    
    // Manejar imagen si se sube
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/homepage/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validar tamaño (máximo 5MB)
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['imagen']['size'] > $maxFileSize) {
            $errors[] = 'El archivo es demasiado grande. Tamaño máximo: 5MB';
        }
        
        // Validar extensión
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Formato de imagen no permitido';
        } else {
            // Validar MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $errors[] = 'Error al validar el tipo de archivo';
            } else {
                $mimeType = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = 'Tipo de archivo no válido';
                } else {
                    $filename = uniqid('homepage_') . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                        $data['imagen'] = '/public/uploads/homepage/' . $filename;
                    } else {
                        $errors[] = 'Error al subir la imagen';
                    }
                }
            }
        }
    }
    
    if ($paginaInicioModel->update($id, $data)) {
        // Registrar auditoría
        $logModel = new Log();
        $currentUser = getCurrentUser();
        $logModel->registrarAuditoria(
            $currentUser['id'],
            'pagina_inicio',
            'modificar',
            'pagina_inicio',
            $id,
            null,
            $data
        );
        
        setFlash('success', 'Elemento actualizado exitosamente');
        redirect('pagina_inicio.php');
    } else {
        $errors[] = 'Error al actualizar el elemento';
    }
}

// Procesar acciones de menú principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_action'])) {
    $action = $_POST['menu_action'];
    
    if ($action === 'sync') {
        // Sincronizar menú con categorías
        $menuItemModel->syncWithCategories();
        setFlash('success', 'Menú sincronizado exitosamente con las categorías');
        redirect('pagina_inicio.php#menu');
    } elseif ($action === 'toggle' && isset($_POST['menu_id'])) {
        // Alternar estado activo/inactivo
        $menuId = (int)$_POST['menu_id'];
        $menuItem = $menuItemModel->getById($menuId);
        if ($menuItem) {
            $menuItemModel->update($menuId, ['activo' => $menuItem['activo'] ? 0 : 1]);
            setFlash('success', 'Estado del ítem actualizado exitosamente');
        }
        redirect('pagina_inicio.php#menu');
    } elseif ($action === 'update_order' && isset($_POST['menu_id']) && isset($_POST['orden'])) {
        // Actualizar orden
        $menuId = (int)$_POST['menu_id'];
        $orden = (int)$_POST['orden'];
        $menuItemModel->update($menuId, ['orden' => $orden]);
        setFlash('success', 'Orden actualizado exitosamente');
        redirect('pagina_inicio.php#menu');
    }
}

// Obtener elementos por sección
$sliders = $paginaInicioModel->getBySeccion('slider', false);
$accesosDirectos = $paginaInicioModel->getBySeccion('acceso_directo', false);
$accesosLaterales = $paginaInicioModel->getBySeccion('acceso_lateral', false);
$contactos = $paginaInicioModel->getBySeccion('contacto', false);
$bannersVerticales = $paginaInicioModel->getBySeccion('banner_vertical', false);
$anunciosFooter = $paginaInicioModel->getBySeccion('anuncio_footer', false);
$bannersIntermedios = $paginaInicioModel->getBySeccion('banner_intermedio', false);

// Obtener ítems del menú principal
$menuItems = $menuItemModel->getAll();

$title = 'Gestión de Página de Inicio';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-home mr-2 text-purple-600"></i>
            Gestión de Página de Inicio
        </h1>
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

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px flex-wrap">
                <button onclick="showTab('slider')" id="tab-slider"
                        class="tab-button px-6 py-3 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                    <i class="fas fa-images mr-2"></i>
                    Slider Principal
                </button>
                <button onclick="showTab('accesos')" id="tab-accesos"
                        class="tab-button px-6 py-3 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-th-large mr-2"></i>
                    Accesos Directos
                </button>
                <!-- Commented out as per requirements: Sidebar lateral - Banners is now managed via Banners module -->
                <!--
                <button onclick="showTab('bannersvert')" id="tab-bannersvert"
                        class="tab-button px-6 py-3 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-ad mr-2"></i>
                    Sidebar lateral - Banners
                </button>
                -->
                <button onclick="showTab('logofooter')" id="tab-logofooter"
                        class="tab-button px-6 py-3 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-image mr-2"></i>
                    Logo del Footer
                </button>
                <button onclick="showTab('menu')" id="tab-menu"
                        class="tab-button px-6 py-3 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-bars mr-2"></i>
                    Menú Principal
                </button>
                <button onclick="showTab('contacto')" id="tab-contacto"
                        class="tab-button px-6 py-3 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-envelope mr-2"></i>
                    Información de Contacto
                </button>
            </nav>
        </div>

        <!-- Slider Section -->
        <div id="content-slider" class="tab-content p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Slider Principal</h2>
            <p class="text-gray-600 mb-6">Configura los elementos del slider que aparecen en la página principal. Puedes agregar imágenes estáticas o configurar noticias destacadas.</p>
            
            <!-- Configuración del Slider -->
            <form method="POST" action="<?php echo url('configuracion_sitio.php'); ?>" class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-cog mr-2 text-blue-600"></i>
                    Configuración del Slider
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Contenido del Slider
                        </label>
                        <select name="slider_tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php 
                            $configuracionModel = new Configuracion();
                            $sliderTipo = $configuracionModel->get('slider_tipo', 'estatico'); 
                            ?>
                            <option value="estatico" <?php echo $sliderTipo === 'estatico' ? 'selected' : ''; ?>>Solo imágenes estáticas</option>
                            <option value="noticias" <?php echo $sliderTipo === 'noticias' ? 'selected' : ''; ?>>Solo noticias destacadas</option>
                            <option value="mixto" <?php echo $sliderTipo === 'mixto' ? 'selected' : ''; ?>>Combinación de ambos</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Define qué tipo de contenido se mostrará en el slider</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cantidad de Slides
                        </label>
                        <input type="number" name="slider_cantidad" min="1" max="10" 
                               value="<?php echo (int)$configuracionModel->get('slider_cantidad', 3); ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Número máximo de elementos a mostrar</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="slider_autoplay" value="1" 
                                   <?php echo $configuracionModel->get('slider_autoplay', '1') === '1' ? 'checked' : ''; ?>
                                   class="form-checkbox text-blue-600">
                            <span class="ml-2 text-sm font-medium text-gray-700">Activar reproducción automática</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-6">El slider cambiará automáticamente</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Intervalo (milisegundos)
                        </label>
                        <input type="number" name="slider_intervalo" min="1000" max="30000" step="1000"
                               value="<?php echo (int)$configuracionModel->get('slider_intervalo', 5000); ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Tiempo entre cambios automáticos (5000 = 5 segundos)</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Configuración
                    </button>
                </div>
            </form>
            
            <!-- Botón para agregar nuevo slider -->
            <div class="mb-6">
                <button type="button" onclick="mostrarFormularioNuevoSlider()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Agregar Nuevo Elemento
                </button>
            </div>
            
            <!-- Formulario para nuevo slider (oculto por defecto) -->
            <div id="formulario_nuevo_slider" class="border border-green-200 rounded-lg p-4 mb-6 bg-green-50" style="display: none;">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Nuevo Elemento del Slider</h3>
                <form method="POST" action="<?php echo url('pagina_inicio_accion.php'); ?>" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="accion" value="crear_slider">
                    <input type="hidden" name="seccion" value="slider">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                            <input type="text" name="titulo" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo</label>
                            <input type="text" name="subtitulo"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contenido</label>
                        <textarea name="contenido" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Imagen del Slider</label>
                        <input type="file" name="imagen" accept="image/*"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 1920x600px. Formatos: JPG, PNG, WebP. Máximo 5MB.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                            <input type="number" name="orden" value="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div class="flex items-center pt-6">
                            <input type="checkbox" name="activo" id="activo_nuevo" value="1" checked
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="activo_nuevo" class="ml-2 block text-sm text-gray-900">
                                Activo
                            </label>
                        </div>
                        
                        <div class="flex justify-end items-end space-x-2">
                            <button type="button" onclick="ocultarFormularioNuevoSlider()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Crear
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Lista de sliders existentes -->
            <div class="space-y-4">
                <?php foreach ($sliders as $slider): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $slider['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="titulo" value="<?php echo e($slider['titulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo</label>
                                <input type="text" name="subtitulo" value="<?php echo e($slider['subtitulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contenido</label>
                            <textarea name="contenido" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($slider['contenido']); ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagen del Slider</label>
                            <?php if (!empty($slider['imagen'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo e($slider['imagen']); ?>" alt="<?php echo e($slider['titulo']); ?>" class="max-w-md h-32 object-cover rounded">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 1920x600px. Formatos: JPG, PNG, WebP. Máximo 5MB.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="number" name="orden" value="<?php echo $slider['orden']; ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="flex items-center pt-6">
                                <input type="checkbox" name="activo" id="activo_<?php echo $slider['id']; ?>" value="1" <?php echo $slider['activo'] ? 'checked' : ''; ?>
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="activo_<?php echo $slider['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                    Activo
                                </label>
                            </div>
                            
                            <div class="flex justify-end items-end">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Accesos Directos Section -->
        <div id="content-accesos" class="tab-content p-6 hidden">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Accesos Directos</h2>
            <p class="text-gray-600 mb-6">Configura los accesos directos que aparecen en la página principal</p>
            
            <div class="space-y-4">
                <?php foreach ($accesosDirectos as $acceso): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $acceso['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="titulo" value="<?php echo e($acceso['titulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo</label>
                                <input type="text" name="subtitulo" value="<?php echo e($acceso['subtitulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Icono (clase Font Awesome)</label>
                                <input type="text" name="contenido" value="<?php echo e($acceso['contenido']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="fas fa-star">
                                <p class="text-xs text-gray-500 mt-1">Se usará si no hay imagen</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Imagen (reemplaza al ícono)
                            </label>
                            <?php if (!empty($acceso['imagen'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo e($acceso['imagen']); ?>" alt="<?php echo e($acceso['titulo']); ?>" class="h-16 rounded">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 128x128px. Si se sube una imagen, reemplazará al ícono.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                <input type="text" name="url" value="<?php echo e($acceso['url']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="number" name="orden" value="<?php echo $acceso['orden']; ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="flex items-end justify-between">
                                <div class="flex items-center">
                                    <input type="checkbox" name="activo" id="activo_acc_<?php echo $acceso['id']; ?>" value="1" <?php echo $acceso['activo'] ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="activo_acc_<?php echo $acceso['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                        Activo
                                    </label>
                                </div>
                                
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Accesos Laterales Section - HIDDEN FROM UI -->
        <div id="content-laterales" class="tab-content p-6" style="display: none;">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Accesos Laterales</h2>
            <p class="text-gray-600 mb-6">Configura los 3 accesos directos que aparecen en el módulo lateral de la página principal</p>
            
            <div class="space-y-4">
                <?php foreach ($accesosLaterales as $lateral): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $lateral['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="titulo" value="<?php echo e($lateral['titulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo</label>
                                <input type="text" name="subtitulo" value="<?php echo e($lateral['subtitulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Icono (clase Font Awesome)</label>
                                <input type="text" name="contenido" value="<?php echo e($lateral['contenido']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="fas fa-star">
                                <p class="text-xs text-gray-500 mt-1">Se usará si no hay imagen</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Imagen (reemplaza al ícono)
                            </label>
                            <?php if (!empty($lateral['imagen'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo e($lateral['imagen']); ?>" alt="<?php echo e($lateral['titulo']); ?>" class="h-16 rounded">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 128x128px. Si se sube una imagen, reemplazará al ícono.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                <input type="text" name="url" value="<?php echo e($lateral['url']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="number" name="orden" value="<?php echo $lateral['orden']; ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="flex items-end justify-between">
                                <div class="flex items-center">
                                    <input type="checkbox" name="activo" id="activo_lat_<?php echo $lateral['id']; ?>" value="1" <?php echo $lateral['activo'] ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="activo_lat_<?php echo $lateral['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                        Activo
                                    </label>
                                </div>
                                
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Logo del Footer Section -->
        <div id="content-logofooter" class="tab-content p-6 hidden">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Logo del Footer</h2>
            <p class="text-gray-600 mb-6">Configura un logo específico que se mostrará en el pie de página del sitio público. Si no se configura, se mostrará el nombre del sitio.</p>
            
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <form method="POST" action="<?php echo url('configuracion_sitio.php'); ?>" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="setting_type" value="logo_footer">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-image mr-2 text-blue-600"></i>
                            Logo del Footer
                        </label>
                        <?php 
                        $logoFooterActual = $configuracionModel->get('logo_footer', '');
                        if (!empty($logoFooterActual)): 
                        ?>
                        <div class="mb-3 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Logo actual:</p>
                            <img src="<?php echo e(BASE_URL . $logoFooterActual); ?>" alt="Logo Footer" class="h-16 w-auto">
                        </div>
                        <?php endif; ?>
                        
                        <input type="file" name="logo_footer" accept="image/*"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">
                            Formatos: JPG, PNG, GIF, WEBP, SVG. Tamaño recomendado: 200x60px. Máximo 2MB.
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i>
                            Deja vacío si quieres mantener el logo actual o elimínalo desde Configuración del Sitio.
                        </p>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                            El logo se mostrará automáticamente en el footer del sitio público
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Logo
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Menú Principal Section -->
        <div id="content-menu" class="tab-content p-6 hidden">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Gestión de Menú Principal</h2>
            <p class="text-gray-600 mb-4">Administra los ítems del menú principal. Cada ítem representa una categoría y puedes habilitar o deshabilitar los que se muestren en la parte pública.</p>
            
            <div class="mb-6 space-y-3">
                <div>
                    <form method="POST" class="inline">
                        <input type="hidden" name="menu_action" value="sync">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-sync mr-2"></i>
                            Sincronizar con Categorías
                        </button>
                    </form>
                    <a href="<?php echo url('diagnostico_completo.php'); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors inline-block ml-2">
                        <i class="fas fa-stethoscope mr-2"></i>
                        Diagnóstico Completo
                    </a>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Sincronización Automática:</strong> Crea ítems de menú para categorías principales, elimina categorías huérfanas o subcategorías del menú, y actualiza el orden automáticamente.
                    </p>
                </div>
            </div>
            
            <?php if (empty($menuItems)): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-800">
                            No hay ítems de menú configurados. Haz clic en "Sincronizar con Categorías" para crear los ítems automáticamente.
                        </p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Categoría
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Orden
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($menuItems as $menuItem): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo e($menuItem['categoria_nombre']); ?>
                                    </div>
                                    <?php if (!empty($menuItem['categoria_descripcion'])): ?>
                                    <div class="text-sm text-gray-500">
                                        <?php echo e(substr($menuItem['categoria_descripcion'], 0, 50)); ?><?php echo strlen($menuItem['categoria_descripcion']) > 50 ? '...' : ''; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form method="POST" class="flex items-center space-x-2">
                                        <input type="hidden" name="menu_action" value="update_order">
                                        <input type="hidden" name="menu_id" value="<?php echo $menuItem['id']; ?>">
                                        <input type="number" name="orden" value="<?php echo $menuItem['orden']; ?>" 
                                               class="w-20 border border-gray-300 rounded px-2 py-1 text-sm">
                                        <button type="submit" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($menuItem['activo']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Inactivo
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="menu_action" value="toggle">
                                        <input type="hidden" name="menu_id" value="<?php echo $menuItem['id']; ?>">
                                        <button type="submit" class="text-blue-600 hover:text-blue-800 mr-3">
                                            <i class="fas fa-toggle-<?php echo $menuItem['activo'] ? 'on' : 'off'; ?> mr-1"></i>
                                            <?php echo $menuItem['activo'] ? 'Desactivar' : 'Activar'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Contacto Section -->
        <div id="content-contacto" class="tab-content p-6 hidden">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Información de Contacto</h2>
            <p class="text-gray-600 mb-6">Configura la información de contacto que aparece en la página principal</p>
            
            <div class="space-y-4">
                <?php foreach ($contactos as $contacto): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $contacto['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="titulo" value="<?php echo e($contacto['titulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo</label>
                                <input type="text" name="subtitulo" value="<?php echo e($contacto['subtitulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contenido</label>
                            <textarea name="contenido" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($contacto['contenido']); ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Puedes usar HTML básico: &lt;br&gt; para saltos de línea</p>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" name="activo" id="activo_cont_<?php echo $contacto['id']; ?>" value="1" <?php echo $contacto['activo'] ? 'checked' : ''; ?>
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="activo_cont_<?php echo $contacto['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                    Activo
                                </label>
                            </div>
                            
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Banners Verticales Section -->
        <div id="content-bannersvert" class="tab-content p-6 hidden">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Sidebar lateral - Banners</h2>
            <p class="text-gray-600 mb-6">Configura los banners publicitarios que aparecen en el módulo lateral de la página principal</p>
            
            <div class="space-y-4">
                <?php foreach ($bannersVerticales as $banner): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="titulo" value="<?php echo e($banner['titulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL destino</label>
                                <input type="text" name="url" value="<?php echo e($banner['url']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagen del Banner</label>
                            <?php if (!empty($banner['imagen'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo e($banner['imagen']); ?>" alt="<?php echo e($banner['titulo']); ?>" class="max-w-xs rounded">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 300x600px. Formatos: JPG, PNG, GIF, WEBP. Máximo 5MB.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="number" name="orden" value="<?php echo $banner['orden']; ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="flex items-end justify-between col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="activo" id="activo_bvert_<?php echo $banner['id']; ?>" value="1" <?php echo $banner['activo'] ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="activo_bvert_<?php echo $banner['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                        Activo
                                    </label>
                                </div>
                                
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Banners Intermedios Section - HIDDEN FROM UI -->
        <div id="content-bannersinter" class="tab-content p-6" style="display: none;">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Banners Entre Secciones</h2>
            <p class="text-gray-600 mb-6">Configura los banners publicitarios que aparecen entre las secciones de la página principal</p>
            
            <div class="space-y-4">
                <?php foreach ($bannersIntermedios as $banner): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="titulo" value="<?php echo e($banner['titulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL destino</label>
                                <input type="text" name="url" value="<?php echo e($banner['url']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagen del Banner</label>
                            <?php if (!empty($banner['imagen'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo e($banner['imagen']); ?>" alt="<?php echo e($banner['titulo']); ?>" class="max-w-full rounded">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 1200x200px. Formatos: JPG, PNG, GIF, WEBP. Máximo 5MB.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="number" name="orden" value="<?php echo $banner['orden']; ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="flex items-end justify-between col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="activo" id="activo_binter_<?php echo $banner['id']; ?>" value="1" <?php echo $banner['activo'] ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="activo_binter_<?php echo $banner['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                        Activo
                                    </label>
                                </div>
                                
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Anuncios Footer Section - HIDDEN FROM UI -->
        <div id="content-anunciosfoot" class="tab-content p-6" style="display: none;">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Anuncios del Footer</h2>
            <p class="text-gray-600 mb-6">Configura los anuncios que aparecen en un grid de 3-4 espacios antes del footer</p>
            
            <div class="space-y-4">
                <?php foreach ($anunciosFooter as $anuncio): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $anuncio['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="titulo" value="<?php echo e($anuncio['titulo']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL destino</label>
                                <input type="text" name="url" value="<?php echo e($anuncio['url']); ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagen del Anuncio</label>
                            <?php if (!empty($anuncio['imagen'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo e($anuncio['imagen']); ?>" alt="<?php echo e($anuncio['titulo']); ?>" class="max-w-xs rounded">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 300x250px. Formatos: JPG, PNG, GIF, WEBP. Máximo 5MB.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="number" name="orden" value="<?php echo $anuncio['orden']; ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="flex items-end justify-between col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="activo" id="activo_afoot_<?php echo $anuncio['id']; ?>" value="1" <?php echo $anuncio['activo'] ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="activo_afoot_<?php echo $anuncio['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                        Activo
                                    </label>
                                </div>
                                
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

function mostrarFormularioNuevoSlider() {
    document.getElementById('formulario_nuevo_slider').style.display = 'block';
}

function ocultarFormularioNuevoSlider() {
    document.getElementById('formulario_nuevo_slider').style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
