<?php
/**
 * Gestión de Página de Inicio
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$paginaInicioModel = new PaginaInicio();
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
        
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            $filename = uniqid('homepage_') . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                $data['imagen'] = '/public/uploads/homepage/' . $filename;
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

// Obtener elementos por sección
$sliders = $paginaInicioModel->getBySeccion('slider', false);
$accesosDirectos = $paginaInicioModel->getBySeccion('acceso_directo', false);
$contactos = $paginaInicioModel->getBySeccion('contacto', false);

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
            <nav class="flex -mb-px">
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
            <p class="text-gray-600 mb-6">Configura los elementos del slider que aparecen en la página principal</p>
            
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
                            </div>
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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
