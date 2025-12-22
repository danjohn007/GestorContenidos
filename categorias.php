<?php
/**
 * Listado de Categorías
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$categoriaModel = new Categoria();

// Obtener árbol de categorías
$categorias = $categoriaModel->getTree();

$title = 'Gestión de Categorías';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-folder mr-2 text-purple-600"></i>
            Gestión de Categorías
        </h1>
        <?php if (hasPermission('categorias') || hasPermission('all')): ?>
        <a href="<?php echo url('categoria_crear.php'); ?>" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nueva Categoría
        </a>
        <?php endif; ?>
    </div>

    <!-- Lista de Categorías -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($categorias)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No hay categorías creadas</p>
            <?php if (hasPermission('categorias') || hasPermission('all')): ?>
            <a href="<?php echo url('categoria_crear.php'); ?>" class="inline-block mt-4 text-purple-600 hover:text-purple-800">
                Crear la primera categoría
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="p-6 space-y-4">
            <?php foreach ($categorias as $categoria): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-folder text-2xl text-purple-500"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?php echo e($categoria['nombre']); ?>
                                <?php if (!$categoria['visible']): ?>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-eye-slash mr-1"></i> Oculto
                                </span>
                                <?php endif; ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                Slug: <?php echo e($categoria['slug']); ?>
                                <?php if ($categoria['descripcion']): ?>
                                <span class="ml-2">• <?php echo e($categoria['descripcion']); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">
                            <?php 
                            $count = $categoriaModel->countNoticias($categoria['id']); 
                            echo $count;
                            ?> noticia<?php echo $count !== 1 ? 's' : ''; ?>
                        </span>
                        <a href="<?php echo url('categoria_editar.php?id=' . $categoria['id']); ?>" 
                           class="text-blue-600 hover:text-blue-900 px-2 py-1">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Subcategorías -->
                <?php if (!empty($categoria['children'])): ?>
                <div class="ml-12 mt-3 space-y-2">
                    <?php foreach ($categoria['children'] as $subcategoria): ?>
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-folder text-purple-400"></i>
                            <span class="text-sm font-medium text-gray-900">
                                <?php echo e($subcategoria['nombre']); ?>
                            </span>
                            <span class="text-xs text-gray-500">
                                (<?php echo e($subcategoria['slug']); ?>)
                            </span>
                            <?php if (!$subcategoria['visible']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                <i class="fas fa-eye-slash mr-1"></i> Oculto
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">
                                <?php 
                                $subcount = $categoriaModel->countNoticias($subcategoria['id']); 
                                echo $subcount;
                                ?> noticia<?php echo $subcount !== 1 ? 's' : ''; ?>
                            </span>
                            <a href="<?php echo url('categoria_editar.php?id=' . $subcategoria['id']); ?>" 
                               class="text-blue-600 hover:text-blue-900 px-2 py-1">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Información adicional -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Sobre las Categorías
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Las categorías organizan el contenido del portal</li>
                        <li>Puedes crear subcategorías para una mejor organización</li>
                        <li>Las categorías ocultas no se mostrarán en el sitio público</li>
                        <li>El slug se usa para crear URLs amigables</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
