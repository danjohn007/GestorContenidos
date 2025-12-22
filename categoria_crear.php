<?php
/**
 * Crear Categoría
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$categoriaModel = new Categoria();
$categoriasParent = $categoriaModel->getParents();
$errors = [];
$success = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    $nombre = trim($_POST['nombre'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $padre_id = !empty($_POST['padre_id']) ? (int)$_POST['padre_id'] : null;
    $visible = isset($_POST['visible']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre)) {
        $errors[] = 'El nombre es requerido';
    }
    
    // Si no hay errores, crear categoría
    if (empty($errors)) {
        $data = [
            'nombre' => $nombre,
            'slug' => $slug,
            'descripcion' => $descripcion,
            'padre_id' => $padre_id,
            'visible' => $visible,
            'orden' => 0
        ];
        
        $result = $categoriaModel->create($data);
        if ($result) {
            setFlash('success', 'Categoría creada exitosamente');
            redirect('categorias.php');
        } else {
            $errors[] = 'Error al crear la categoría. Intente nuevamente.';
        }
    }
}

$title = 'Crear Categoría';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-folder-plus mr-2 text-purple-600"></i>
            Crear Nueva Categoría
        </h1>
        <a href="<?php echo url('categorias.php'); ?>" class="text-gray-600 hover:text-gray-900">
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
        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" required value="<?php echo e($_POST['nombre'] ?? ''); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Slug (URL amigable)
                </label>
                <input type="text" name="slug" value="<?php echo e($_POST['slug'] ?? ''); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="Se genera automáticamente si se deja vacío">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción
                </label>
                <textarea name="descripcion" rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"><?php echo e($_POST['descripcion'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Categoría Padre
                </label>
                <select name="padre_id" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Ninguna (Categoría principal)</option>
                    <?php foreach ($categoriasParent as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['padre_id']) && $_POST['padre_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo e($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="visible" id="visible" value="1" <?php echo (!isset($_POST['visible']) || isset($_POST['visible'])) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="visible" class="ml-2 block text-sm text-gray-900">
                    Visible en el sitio público
                </label>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('categorias.php'); ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
