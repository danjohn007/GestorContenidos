<?php
/**
 * Página Pública Principal
 */
require_once __DIR__ . '/config/bootstrap.php';

// Si está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect('dashboard.php');
}

$noticiaModel = new Noticia();
$categoriaModel = new Categoria();

// Obtener noticias destacadas
$noticiasDestacadas = $noticiaModel->getDestacadas(3);

// Obtener noticias recientes publicadas
$noticiasRecientes = $noticiaModel->getAll('publicado', null, 1, 6);

// Obtener categorías principales
$categorias = $categoriaModel->getParents(1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Noticias - Querétaro</title>
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
                    <h1 class="text-2xl font-bold text-gray-800">Portal de Noticias Querétaro</h1>
                </div>
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
        <!-- Noticias Destacadas -->
        <?php if (!empty($noticiasDestacadas)): ?>
        <section class="mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Noticias Destacadas
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($noticiasDestacadas as $noticia): ?>
                <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <?php if ($noticia['imagen_destacada']): ?>
                    <img src="<?php echo e($noticia['imagen_destacada']); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                    <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-6xl"></i>
                    </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                <?php echo e($noticia['categoria_nombre']); ?>
                            </span>
                            <span class="ml-auto">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'] ?? $noticia['fecha_creacion'])); ?>
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 hover:text-blue-600">
                            <?php echo e($noticia['titulo']); ?>
                        </h3>
                        <?php if ($noticia['resumen']): ?>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                            <?php echo e($noticia['resumen']); ?>
                        </p>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>
                                <i class="fas fa-eye mr-1"></i>
                                <?php echo number_format($noticia['visitas']); ?> visitas
                            </span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Noticias Recientes -->
        <section>
            <h2 class="text-3xl font-bold text-gray-800 mb-6">
                <i class="fas fa-clock text-blue-600 mr-2"></i>
                Últimas Noticias
            </h2>
            <?php if (empty($noticiasRecientes)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No hay noticias disponibles en este momento</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($noticiasRecientes as $noticia): ?>
                <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <?php if ($noticia['imagen_destacada']): ?>
                    <img src="<?php echo e($noticia['imagen_destacada']); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="w-full h-40 object-cover">
                    <?php else: ?>
                    <div class="w-full h-40 bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-4xl"></i>
                    </div>
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
                            <?php echo e($noticia['titulo']); ?>
                        </h3>
                        <?php if ($noticia['resumen']): ?>
                        <p class="text-gray-600 text-sm line-clamp-2 mb-3">
                            <?php echo e($noticia['resumen']); ?>
                        </p>
                        <?php endif; ?>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-eye mr-1"></i>
                            <?php echo number_format($noticia['visitas']); ?> visitas
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-newspaper mr-2"></i>
                        Portal de Noticias
                    </h3>
                    <p class="text-gray-400">Tu fuente de noticias de Querétaro</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Categorías</h4>
                    <ul class="space-y-2 text-gray-400">
                        <?php foreach (array_slice($categorias, 0, 5) as $cat): ?>
                        <li><a href="<?php echo url('index.php?categoria=' . $cat['id']); ?>" class="hover:text-white"><?php echo e($cat['nombre']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contacto</h4>
                    <p class="text-gray-400">
                        <i class="fas fa-phone mr-2"></i>
                        442-123-4567
                    </p>
                    <p class="text-gray-400 mt-2">
                        <i class="fas fa-envelope mr-2"></i>
                        contacto@portalqueretaro.mx
                    </p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Portal de Noticias Querétaro. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
