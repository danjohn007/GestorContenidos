<?php
/**
 * Gestión de Banners
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$bannerModel = new Banner();

// Obtener filtros
$filtroUbicacion = $_GET['ubicacion'] ?? null;
$filtroActivo = isset($_GET['activo']) ? (int)$_GET['activo'] : null;

// Obtener banners
$banners = $bannerModel->getAll($filtroActivo, $filtroUbicacion);

$title = 'Gestión de Banners';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-ad mr-2 text-indigo-600"></i>
            Gestión de Banners
        </h1>
        <?php if (hasPermission('configuracion') || hasPermission('all')): ?>
        <a href="<?php echo url('banner_crear.php'); ?>" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Banner
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ubicación
                </label>
                <select name="ubicacion" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todas las ubicaciones</option>
                    <?php foreach (Banner::getUbicaciones() as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo ($filtroUbicacion === $key) ? 'selected' : ''; ?>>
                        <?php echo e($label); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                </label>
                <select name="activo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="1" <?php echo ($filtroActivo === 1) ? 'selected' : ''; ?>>Activos</option>
                    <option value="0" <?php echo ($filtroActivo === 0) ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
                <a href="<?php echo url('banners.php'); ?>" class="ml-2 text-gray-600 hover:text-gray-900 px-4 py-2">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Banners -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($banners)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-ad text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No hay banners creados</p>
            <?php if (hasPermission('configuracion') || hasPermission('all')): ?>
            <a href="<?php echo url('banner_crear.php'); ?>" class="inline-block mt-4 text-indigo-600 hover:text-indigo-800">
                Crear el primer banner
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Banner
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ubicación
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Orientación
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dispositivo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vigencia
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estadísticas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($banners as $banner): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <?php if ($banner['imagen_url']): ?>
                                <img src="<?php echo e(BASE_URL . $banner['imagen_url']); ?>" 
                                     alt="<?php echo e($banner['nombre']); ?>" 
                                     class="h-12 w-20 object-cover rounded mr-3">
                                <?php else: ?>
                                <div class="h-12 w-20 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo e($banner['nombre']); ?>
                                    </div>
                                    <?php if ($banner['url_destino']): ?>
                                    <div class="text-xs text-gray-500 truncate max-w-xs">
                                        <i class="fas fa-link mr-1"></i>
                                        <?php echo e($banner['url_destino']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo e(Banner::getUbicaciones()[$banner['ubicacion']] ?? $banner['ubicacion']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e(Banner::getOrientaciones()[$banner['orientacion']] ?? $banner['orientacion']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($banner['dispositivo'] === 'desktop'): ?>
                                <i class="fas fa-desktop text-gray-600"></i> Desktop
                            <?php elseif ($banner['dispositivo'] === 'movil'): ?>
                                <i class="fas fa-mobile-alt text-gray-600"></i> Móvil
                            <?php else: ?>
                                <i class="fas fa-devices text-gray-600"></i> Todos
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($banner['fecha_inicio'] || $banner['fecha_fin']): ?>
                                <?php if ($banner['fecha_inicio']): ?>
                                    Desde: <?php echo date('d/m/Y', strtotime($banner['fecha_inicio'])); ?><br>
                                <?php endif; ?>
                                <?php if ($banner['fecha_fin']): ?>
                                    Hasta: <?php echo date('d/m/Y', strtotime($banner['fecha_fin'])); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">Sin límite</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="text-xs">
                                <i class="fas fa-eye text-blue-500"></i> <?php echo number_format($banner['impresiones']); ?><br>
                                <i class="fas fa-mouse-pointer text-green-500"></i> <?php echo number_format($banner['clics']); ?>
                                <?php 
                                $ctr = $banner['impresiones'] > 0 ? ($banner['clics'] / $banner['impresiones'] * 100) : 0;
                                ?>
                                <span class="text-gray-400">(<?php echo number_format($ctr, 2); ?>% CTR)</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($banner['activo']): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Activo
                            </span>
                            <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Inactivo
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="<?php echo url('banner_editar.php?id=' . $banner['id']); ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmarEliminacion(<?php echo $banner['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                    Sobre los Banners
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Los banners se pueden colocar en diferentes ubicaciones del sitio</li>
                        <li>Puedes configurar fechas de inicio y fin para controlar su vigencia</li>
                        <li>Los banners se pueden mostrar solo en desktop, solo en móvil o en ambos</li>
                        <li>Las estadísticas de impresiones y clics te ayudan a medir su efectividad</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este banner? Esta acción no se puede deshacer.')) {
        window.location.href = '<?php echo url("banner_accion.php?accion=eliminar&id="); ?>' + id;
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
