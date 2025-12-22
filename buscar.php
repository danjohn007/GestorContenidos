<?php
/**
 * Búsqueda de Noticias
 */
require_once __DIR__ . '/config/bootstrap.php';

$noticiaModel = new Noticia();
$categoriaModel = new Categoria();

// Obtener término de búsqueda
$termino = trim($_GET['q'] ?? '');
$page = $_GET['page'] ?? 1;
$perPage = 12;

// Búsqueda
$resultados = [];
$totalResultados = 0;
$totalPages = 0;

if (!empty($termino)) {
    $resultados = $noticiaModel->search($termino, $page, $perPage);
    $totalResultados = $noticiaModel->countSearch($termino);
    $totalPages = ceil($totalResultados / $perPage);
}

// Obtener categorías principales
$categorias = $categoriaModel->getParents(1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda<?php echo !empty($termino) ? ': ' . e($termino) : ''; ?> - Portal de Noticias</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-newspaper text-3xl text-blue-600"></i>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <a href="<?php echo url('index.php'); ?>">Portal de Noticias Querétaro</a>
                    </h1>
                </div>
                
                <!-- Formulario de búsqueda -->
                <form method="GET" action="<?php echo url('buscar.php'); ?>" class="flex items-center">
                    <input type="text" name="q" value="<?php echo e($termino); ?>" 
                           placeholder="Buscar noticias..."
                           class="border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-r-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <a href="<?php echo url('login.php'); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Acceder
                </a>
            </div>
            <!-- Navigation -->
            <nav class="border-t border-gray-200 py-3">
                <ul class="flex space-x-6">
                    <li><a href="<?php echo url('index.php'); ?>" class="text-gray-700 hover:text-blue-600 font-medium">Inicio</a></li>
                    <?php foreach (array_slice($categorias, 0, 6) as $cat): ?>
                    <li><a href="<?php echo url('index.php?categoria=' . $cat['id']); ?>" class="text-gray-700 hover:text-blue-600"><?php echo e($cat['nombre']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-search text-blue-600 mr-2"></i>
                Resultados de búsqueda
            </h2>
            <?php if (!empty($termino)): ?>
            <p class="text-gray-600 mt-2">
                Mostrando resultados para: <strong>"<?php echo e($termino); ?>"</strong>
                <?php if ($totalResultados > 0): ?>
                    (<?php echo number_format($totalResultados); ?> <?php echo $totalResultados === 1 ? 'resultado' : 'resultados'; ?>)
                <?php endif; ?>
            </p>
            <?php endif; ?>
        </div>

        <?php if (empty($termino)): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Ingresa un término de búsqueda para encontrar noticias</p>
            <form method="GET" action="" class="mt-6 max-w-lg mx-auto">
                <div class="flex">
                    <input type="text" name="q" placeholder="¿Qué estás buscando?"
                           class="flex-1 border border-gray-300 rounded-l-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-r-lg hover:bg-blue-700 transition-colors">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
        <?php elseif (empty($resultados)): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg mb-4">No se encontraron resultados para "<?php echo e($termino); ?>"</p>
            <p class="text-gray-400">Intenta con otros términos de búsqueda</p>
        </div>
        <?php else: ?>
        <!-- Resultados -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($resultados as $noticia): ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                <?php if ($noticia['imagen_destacada']): ?>
                <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>">
                    <img src="<?php echo e($noticia['imagen_destacada']); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="w-full h-40 object-cover">
                </a>
                <?php else: ?>
                <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>">
                    <div class="w-full h-40 bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-4xl"></i>
                    </div>
                </a>
                <?php endif; ?>
                <div class="p-4">
                    <div class="flex items-center text-xs text-gray-500 mb-2">
                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded font-medium">
                            <?php echo e($noticia['categoria_nombre']); ?>
                        </span>
                        <span class="ml-auto">
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'] ?? $noticia['fecha_creacion'])); ?>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2 hover:text-blue-600 line-clamp-2">
                        <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>">
                            <?php echo e($noticia['titulo']); ?>
                        </a>
                    </h3>
                    <?php if ($noticia['resumen']): ?>
                    <p class="text-gray-600 text-sm line-clamp-2 mb-3">
                        <?php echo e($noticia['resumen']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($noticia['tags']): ?>
                    <div class="flex flex-wrap gap-1 mb-3">
                        <?php foreach (array_slice(explode(',', $noticia['tags']), 0, 3) as $tag): ?>
                        <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded">
                            #<?php echo e(trim($tag)); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-eye mr-1"></i>
                        <?php echo number_format($noticia['visitas']); ?> visitas
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?q=<?php echo urlencode($termino); ?>&page=<?php echo $i; ?>" 
                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium
                          <?php echo $i === (int)$page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Portal de Noticias Querétaro. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
