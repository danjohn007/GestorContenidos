<?php
/**
 * Dashboard Principal
 */
require_once __DIR__ . '/config/bootstrap.php';

// Requerir autenticación
requireAuth();

$usuarioModel = new Usuario();
$noticiaModel = new Noticia();
$categoriaModel = new Categoria();

// Obtener estadísticas
$totalNoticias = $noticiaModel->count();
$noticiasPublicadas = $noticiaModel->count('publicado');
$noticiasBorrador = $noticiaModel->count('borrador');
$noticiasRevision = $noticiaModel->count('revision');

$totalCategorias = count($categoriaModel->getAll());
$totalUsuarios = count($usuarioModel->getAll(1));

// Obtener noticias recientes
$noticiasRecientes = $noticiaModel->getAll(null, null, 1, 10);

// Obtener noticias más leídas
$noticiasMasLeidas = $noticiaModel->getMasLeidas(5);

$title = 'Dashboard';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <div class="text-sm text-gray-600">
            <i class="fas fa-calendar-alt mr-2"></i>
            <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Noticias -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <i class="fas fa-newspaper text-white text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Noticias
                        </dt>
                        <dd class="text-3xl font-semibold text-gray-900">
                            <?php echo $totalNoticias; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Noticias Publicadas -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <i class="fas fa-check-circle text-white text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Publicadas
                        </dt>
                        <dd class="text-3xl font-semibold text-gray-900">
                            <?php echo $noticiasPublicadas; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Borradores -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <i class="fas fa-edit text-white text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Borradores
                        </dt>
                        <dd class="text-3xl font-semibold text-gray-900">
                            <?php echo $noticiasBorrador; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- En Revisión -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                    <i class="fas fa-clock text-white text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            En Revisión
                        </dt>
                        <dd class="text-3xl font-semibold text-gray-900">
                            <?php echo $noticiasRevision; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas adicionales -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <i class="fas fa-folder text-white text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Categorías
                        </dt>
                        <dd class="text-3xl font-semibold text-gray-900">
                            <?php echo $totalCategorias; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Usuarios Activos
                        </dt>
                        <dd class="text-3xl font-semibold text-gray-900">
                            <?php echo $totalUsuarios; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Noticias Recientes y Más Leídas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Noticias Recientes -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-clock mr-2 text-blue-500"></i>
                    Noticias Recientes
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php if (empty($noticiasRecientes)): ?>
                        <p class="text-gray-500 text-center py-4">No hay noticias recientes</p>
                    <?php else: ?>
                        <?php foreach ($noticiasRecientes as $noticia): ?>
                        <div class="flex items-start space-x-3 pb-3 border-b border-gray-100">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    <?php 
                                    echo match($noticia['estado']) {
                                        'publicado' => 'bg-green-100 text-green-800',
                                        'borrador' => 'bg-yellow-100 text-yellow-800',
                                        'revision' => 'bg-orange-100 text-orange-800',
                                        'aprobado' => 'bg-blue-100 text-blue-800',
                                        'rechazado' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>
                                ">
                                    <?php echo ucfirst($noticia['estado']); ?>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?php echo e($noticia['titulo']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($noticia['categoria_nombre']); ?> • 
                                    <?php echo date('d/m/Y H:i', strtotime($noticia['fecha_creacion'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($noticiasRecientes)): ?>
                <div class="mt-4 text-center">
                    <a href="<?php echo url('noticias.php'); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Ver todas las noticias <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Noticias Más Leídas -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-fire mr-2 text-red-500"></i>
                    Noticias Más Leídas
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php if (empty($noticiasMasLeidas)): ?>
                        <p class="text-gray-500 text-center py-4">No hay noticias publicadas</p>
                    <?php else: ?>
                        <?php foreach ($noticiasMasLeidas as $index => $noticia): ?>
                        <div class="flex items-start space-x-3 pb-3 border-b border-gray-100">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                                    <?php echo $index + 1; ?>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?php echo e($noticia['titulo']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-eye mr-1"></i>
                                    <?php echo number_format($noticia['visitas']); ?> visitas
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                Acciones Rápidas
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php if (hasPermission('noticias.crear') || hasPermission('all')): ?>
                <a href="<?php echo url('noticia_crear.php'); ?>" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <i class="fas fa-plus-circle text-3xl text-blue-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Nueva Noticia</span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('categorias') || hasPermission('all')): ?>
                <a href="<?php echo url('categoria_crear.php'); ?>" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                    <i class="fas fa-folder-plus text-3xl text-green-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Nueva Categoría</span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('usuarios') || hasPermission('all')): ?>
                <a href="<?php echo url('usuario_crear.php'); ?>" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
                    <i class="fas fa-user-plus text-3xl text-purple-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Nuevo Usuario</span>
                </a>
                <?php endif; ?>
                
                <a href="<?php echo url('index.php'); ?>" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors">
                    <i class="fas fa-chart-line text-3xl text-orange-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Ver Estadísticas</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
