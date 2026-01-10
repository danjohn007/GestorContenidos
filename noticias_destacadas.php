<?php
/**
 * Gestión de Noticias Destacadas (Solo Imágenes)
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$noticiaDestacadaImagenModel = new NoticiaDestacadaImagen();

// Obtener filtros
$ubicacion = $_GET['ubicacion'] ?? null;

// Convertir strings vacíos a null
if ($ubicacion === '' || $ubicacion === 'Todas') {
    $ubicacion = null;
}

// Obtener noticias destacadas
$noticiasDestacadas = $noticiaDestacadaImagenModel->getAll(null, $ubicacion);

// Verificar si la tabla existe (si getAll retorna array vacío y no hay filtros, podría ser que la tabla no existe)
$tablaMissing = false;
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'noticias_destacadas_imagenes'");
    $tablaMissing = ($stmt->rowCount() === 0);
} catch (Exception $e) {
    // Ignorar errores de verificación
}

$title = 'Noticias Destacadas (Imágenes)';
ob_start();
?>

<div class="space-y-6">
    <?php if ($tablaMissing): ?>
    <!-- Alerta de tabla faltante -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    Tabla de base de datos no encontrada
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>La tabla <code>noticias_destacadas_imagenes</code> no existe en la base de datos.</p>
                    <p class="mt-2">Por favor ejecute el siguiente script SQL para crear la tabla:</p>
                    <code class="block mt-2 p-2 bg-red-100 rounded">
                        mysql -u usuario -p base_datos &lt; database_noticias_destacadas_imagenes.sql
                    </code>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-images mr-2 text-blue-600"></i>
            Noticias Destacadas (Solo Imágenes)
        </h1>
        <?php if (hasPermission('noticias') || hasPermission('all')): ?>
        <a href="<?php echo url('noticia_destacada_crear.php'); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nueva Destacada
        </a>
        <?php endif; ?>
    </div>

    <!-- Descripción -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Las noticias destacadas (solo imágenes) son módulos visuales que se muestran en diferentes ubicaciones del sitio.
                    Puede configurar donde aparecen, si se muestran en grid o carrusel, y su orden de visualización.
                </p>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                <select name="ubicacion" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    <?php foreach (NoticiaDestacadaImagen::getUbicaciones() as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $ubicacion === $key ? 'selected' : ''; ?>>
                        <?php echo e($label); ?>
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

    <!-- Lista de Noticias Destacadas -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($noticiasDestacadas)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No se encontraron noticias destacadas</p>
            <?php if (hasPermission('noticias') || hasPermission('all')): ?>
            <a href="<?php echo url('noticia_destacada_crear.php'); ?>" class="inline-block mt-4 text-blue-600 hover:text-blue-800">
                Crear la primera noticia destacada
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Imagen
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Título
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ubicación
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Vista
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Orden
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
                <?php foreach ($noticiasDestacadas as $destacada): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <?php if ($destacada['imagen_url']): ?>
                        <img src="<?php echo e(BASE_URL . $destacada['imagen_url']); ?>" alt="" class="h-12 w-12 rounded object-cover">
                        <?php else: ?>
                        <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo e($destacada['titulo']); ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">
                            <?php echo e(NoticiaDestacadaImagen::getUbicaciones()[$destacada['ubicacion']] ?? $destacada['ubicacion']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $destacada['vista'] === 'carousel' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                            <?php echo e(NoticiaDestacadaImagen::getVistas()[$destacada['vista']] ?? $destacada['vista']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo $destacada['orden']; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $destacada['activo'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $destacada['activo'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?php echo url('noticia_destacada_editar.php?id=' . $destacada['id']); ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo url('noticia_destacada_accion.php?accion=toggle&id=' . $destacada['id']); ?>" 
                           class="text-orange-600 hover:text-orange-900 mr-3" title="<?php echo $destacada['activo'] ? 'Desactivar' : 'Activar'; ?>">
                            <i class="fas fa-toggle-<?php echo $destacada['activo'] ? 'on' : 'off'; ?>"></i>
                        </a>
                        <a href="<?php echo url('noticia_destacada_accion.php?accion=eliminar&id=' . $destacada['id']); ?>" 
                           onclick="return confirm('¿Estás seguro de que deseas eliminar esta noticia destacada?')" 
                           class="text-red-600 hover:text-red-900" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
