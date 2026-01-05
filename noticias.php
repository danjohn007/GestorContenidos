<?php
/**
 * Listado de Noticias
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$noticiaModel = new Noticia();
$categoriaModel = new Categoria();

// Obtener filtros
$estado = $_GET['estado'] ?? null;
$categoriaId = $_GET['categoria'] ?? null;

// Convertir strings vacíos a null
if ($estado === '' || $estado === 'Todos') {
    $estado = null;
}
if ($categoriaId === '' || $categoriaId === 'Todas') {
    $categoriaId = null;
}

$page = $_GET['page'] ?? 1;
$perPage = 20;

// Obtener noticias
$noticias = $noticiaModel->getAll($estado, $categoriaId, $page, $perPage);
$totalNoticias = $noticiaModel->count($estado, $categoriaId);
$totalPages = ceil($totalNoticias / $perPage);

// Obtener categorías para filtro
$categorias = $categoriaModel->getAll(1);

$title = 'Gestión de Noticias';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-newspaper mr-2 text-blue-600"></i>
            Gestión de Noticias
        </h1>
        <?php if (hasPermission('noticias.crear') || hasPermission('all')): ?>
        <a href="<?php echo url('noticia_crear.php'); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nueva Noticia
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="borrador" <?php echo $estado === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="revision" <?php echo $estado === 'revision' ? 'selected' : ''; ?>>En Revisión</option>
                    <option value="aprobado" <?php echo $estado === 'aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                    <option value="publicado" <?php echo $estado === 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                    <option value="rechazado" <?php echo $estado === 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                    <option value="archivado" <?php echo $estado === 'archivado' ? 'selected' : ''; ?>>Archivado</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                <select name="categoria" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoriaId == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo e($cat['nombre']); ?>
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

    <!-- Lista de Noticias -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($noticias)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No se encontraron noticias</p>
            <?php if (hasPermission('noticias.crear') || hasPermission('all')): ?>
            <a href="<?php echo url('noticia_crear.php'); ?>" class="inline-block mt-4 text-blue-600 hover:text-blue-800">
                Crear la primera noticia
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Título
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Categoría
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Autor
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Visitas
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($noticias as $noticia): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <?php if ($noticia['imagen_destacada']): ?>
                            <img src="<?php echo e(BASE_URL . $noticia['imagen_destacada']); ?>" alt="" class="h-10 w-10 rounded object-cover mr-3">
                            <?php else: ?>
                            <div class="h-10 w-10 rounded bg-gray-200 flex items-center justify-center mr-3">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo e($noticia['titulo']); ?>
                                </div>
                                <?php if ($noticia['destacado']): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-star mr-1"></i> Destacado
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900"><?php echo e($noticia['categoria_nombre']); ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900"><?php echo e($noticia['autor_nombre'] . ' ' . $noticia['autor_apellidos']); ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?php 
                            echo match($noticia['estado']) {
                                'publicado' => 'bg-green-100 text-green-800',
                                'borrador' => 'bg-yellow-100 text-yellow-800',
                                'revision' => 'bg-orange-100 text-orange-800',
                                'aprobado' => 'bg-blue-100 text-blue-800',
                                'rechazado' => 'bg-red-100 text-red-800',
                                'archivado' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            ?>
                        ">
                            <?php echo ucfirst($noticia['estado']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo date('d/m/Y', strtotime($noticia['fecha_creacion'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <i class="fas fa-eye mr-1"></i>
                        <?php echo number_format($noticia['visitas']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?php echo url('noticia_editar.php?id=' . $noticia['id']); ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($noticia['estado'] === 'publicado'): ?>
                        <a href="<?php echo url('noticia_accion.php?accion=suspender&id=' . $noticia['id']); ?>" 
                           onclick="return confirm('¿Deseas suspender esta noticia?')" 
                           class="text-orange-600 hover:text-orange-900 mr-3" title="Suspender">
                            <i class="fas fa-pause-circle"></i>
                        </a>
                        <?php elseif ($noticia['estado'] === 'archivado'): ?>
                        <a href="<?php echo url('noticia_accion.php?accion=vigencia&id=' . $noticia['id']); ?>" 
                           onclick="return confirm('¿Deseas activar la vigencia de esta noticia?')" 
                           class="text-green-600 hover:text-green-900 mr-3" title="Dar Vigencia">
                            <i class="fas fa-check-circle"></i>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo url('noticia_eliminar.php?id=' . $noticia['id']); ?>" 
                           onclick="return confirm('¿Estás seguro de eliminar esta noticia?')" 
                           class="text-red-600 hover:text-red-900" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&estado=<?php echo $estado; ?>&categoria=<?php echo $categoriaId; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Anterior
                </a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&estado=<?php echo $estado; ?>&categoria=<?php echo $categoriaId; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Siguiente
                </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium"><?php echo (($page - 1) * $perPage) + 1; ?></span>
                        a
                        <span class="font-medium"><?php echo min($page * $perPage, $totalNoticias); ?></span>
                        de
                        <span class="font-medium"><?php echo $totalNoticias; ?></span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&estado=<?php echo $estado; ?>&categoria=<?php echo $categoriaId; ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium
                                  <?php echo $i === (int)$page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
