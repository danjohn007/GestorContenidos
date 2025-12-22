<?php
/**
 * Crear Noticia (Placeholder)
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$categoriaModel = new Categoria();
$categorias = $categoriaModel->getAll(1);

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

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
            <!-- Título -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titulo" required 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ingresa el título de la noticia">
            </div>

            <!-- Subtítulo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Subtítulo
                </label>
                <input type="text" name="subtitulo" 
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
                    <option value="<?php echo $cat['id']; ?>">
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
                          placeholder="Breve resumen de la noticia"></textarea>
            </div>

            <!-- Contenido -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Contenido <span class="text-red-500">*</span>
                </label>
                <textarea name="contenido" rows="12" required 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Contenido completo de la noticia"></textarea>
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
                    <option value="borrador">Borrador</option>
                    <option value="revision">En Revisión</option>
                    <?php if (hasPermission('all') || hasPermission('noticias')): ?>
                    <option value="publicado">Publicar</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Opciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="destacado" id="destacado" value="1"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="destacado" class="ml-2 block text-sm text-gray-900">
                        Marcar como destacado
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="permitir_comentarios" id="comentarios" value="1" checked
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

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                    Formulario de Demostración
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Este es un formulario de demostración. La funcionalidad completa de crear/editar noticias puede implementarse conectando este formulario con el modelo Noticia.php</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
