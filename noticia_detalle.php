<?php
/**
 * Detalle de Noticia
 */
require_once __DIR__ . '/config/bootstrap.php';

$noticiaModel = new Noticia();
$categoriaModel = new Categoria();

// Obtener slug de la noticia
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect('index.php');
}

// Obtener noticia
$noticia = $noticiaModel->getBySlug($slug);

if (!$noticia || $noticia['estado'] !== 'publicado') {
    redirect('index.php');
}

// Incrementar contador de visitas
$noticiaModel->incrementVisitas($noticia['id']);

// Obtener categorías principales
$categorias = $categoriaModel->getParents(1);

// Obtener noticias relacionadas de la misma categoría
$noticiasRelacionadas = $noticiaModel->getAll('publicado', $noticia['categoria_id'], 1, 3);
// Filtrar la noticia actual
$noticiasRelacionadas = array_filter($noticiasRelacionadas, function($n) use ($noticia) {
    return $n['id'] !== $noticia['id'];
});

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($noticia['titulo']); ?> - Portal de Noticias</title>
    <meta name="description" content="<?php echo e($noticia['resumen'] ?? substr(strip_tags($noticia['contenido']), 0, 160)); ?>">
    <?php if ($noticia['tags']): ?>
    <meta name="keywords" content="<?php echo e($noticia['tags']); ?>">
    <?php endif; ?>
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
                    <input type="text" name="q" placeholder="Buscar noticias..."
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contenido Principal -->
            <div class="lg:col-span-2">
                <article class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Imagen destacada -->
                    <?php if ($noticia['imagen_destacada']): ?>
                    <img src="<?php echo e($noticia['imagen_destacada']); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="w-full h-96 object-cover">
                    <?php endif; ?>
                    
                    <!-- Contenido -->
                    <div class="p-8">
                        <!-- Categoría y fecha -->
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium">
                                <?php echo e($noticia['categoria_nombre']); ?>
                            </span>
                            <span class="ml-4">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($noticia['fecha_publicacion'] ?? $noticia['fecha_creacion'])); ?>
                            </span>
                            <span class="ml-4">
                                <i class="fas fa-user mr-1"></i>
                                <?php echo e($noticia['autor_nombre'] . ' ' . $noticia['autor_apellidos']); ?>
                            </span>
                        </div>
                        
                        <!-- Título -->
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">
                            <?php echo e($noticia['titulo']); ?>
                        </h1>
                        
                        <!-- Subtítulo -->
                        <?php if ($noticia['subtitulo']): ?>
                        <p class="text-xl text-gray-600 mb-6">
                            <?php echo e($noticia['subtitulo']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <!-- Tags -->
                        <?php if ($noticia['tags']): ?>
                        <div class="flex flex-wrap gap-2 mb-6 pb-6 border-b border-gray-200">
                            <?php foreach (explode(',', $noticia['tags']) as $tag): ?>
                            <a href="<?php echo url('buscar.php?q=' . urlencode(trim($tag))); ?>" 
                               class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm hover:bg-blue-100">
                                <i class="fas fa-tag mr-1 text-xs"></i>
                                <?php echo e(trim($tag)); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Contenido -->
                        <div class="prose max-w-none text-gray-700 leading-relaxed">
                            <?php echo $noticia['contenido']; ?>
                        </div>
                        
                        <!-- Estadísticas -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex items-center text-sm text-gray-500">
                                <span class="mr-6">
                                    <i class="fas fa-eye mr-2"></i>
                                    <?php echo number_format($noticia['visitas'] + 1); ?> visitas
                                </span>
                                <span>
                                    <i class="fas fa-clock mr-2"></i>
                                    Publicado el <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'] ?? $noticia['fecha_creacion'])); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Compartir -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-sm font-medium text-gray-700 mb-3">Compartir:</p>
                            <div class="flex space-x-3">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(url('noticia_detalle.php?slug=' . $noticia['slug'])); ?>" 
                                   target="_blank" 
                                   class="inline-flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(url('noticia_detalle.php?slug=' . $noticia['slug'])); ?>&text=<?php echo urlencode($noticia['titulo']); ?>" 
                                   target="_blank"
                                   class="inline-flex items-center justify-center w-10 h-10 bg-sky-500 text-white rounded-full hover:bg-sky-600">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode($noticia['titulo'] . ' ' . url('noticia_detalle.php?slug=' . $noticia['slug'])); ?>" 
                                   target="_blank"
                                   class="inline-flex items-center justify-center w-10 h-10 bg-green-600 text-white rounded-full hover:bg-green-700">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Noticias Relacionadas -->
                <?php if (!empty($noticiasRelacionadas)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-newspaper text-blue-600 mr-2"></i>
                        Noticias Relacionadas
                    </h3>
                    <div class="space-y-4">
                        <?php foreach (array_slice($noticiasRelacionadas, 0, 3) as $rel): ?>
                        <a href="<?php echo url('noticia_detalle.php?slug=' . $rel['slug']); ?>" class="block group">
                            <div class="flex space-x-3">
                                <?php if ($rel['imagen_destacada']): ?>
                                <img src="<?php echo e($rel['imagen_destacada']); ?>" alt="" class="w-20 h-20 object-cover rounded">
                                <?php else: ?>
                                <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600 line-clamp-2">
                                        <?php echo e($rel['titulo']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('d/m/Y', strtotime($rel['fecha_publicacion'] ?? $rel['fecha_creacion'])); ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Categorías -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-th-large text-blue-600 mr-2"></i>
                        Categorías
                    </h3>
                    <ul class="space-y-2">
                        <?php foreach ($categorias as $cat): ?>
                        <li>
                            <a href="<?php echo url('index.php?categoria=' . $cat['id']); ?>" 
                               class="text-gray-700 hover:text-blue-600 flex items-center justify-between">
                                <span><?php echo e($cat['nombre']); ?></span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
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
