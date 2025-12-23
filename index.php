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
$paginaInicioModel = new PaginaInicio();
$redesSocialesModel = new RedesSociales();

// Obtener noticias destacadas
$noticiasDestacadas = $noticiaModel->getDestacadas(3);

// Obtener noticias recientes publicadas
$noticiasRecientes = $noticiaModel->getAll('publicado', null, 1, 6);

// Obtener categorías principales
$categorias = $categoriaModel->getParents(1);

// Obtener contenido de página de inicio
$slider = $paginaInicioModel->getBySeccion('slider');
$accesoDirecto = $paginaInicioModel->getBySeccion('acceso_directo');
$contacto = $paginaInicioModel->getBySeccion('contacto');

// Obtener redes sociales
$redesSociales = $redesSocialesModel->getAll();

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
            <!-- Top Bar - Social Media -->
            <div class="flex justify-between items-center py-2 text-sm border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Síguenos:</span>
                    <?php foreach ($redesSociales as $red): ?>
                    <a href="<?php echo e($red['url']); ?>" target="_blank" class="text-gray-600 hover:text-blue-600 transition-colors" title="<?php echo e($red['nombre']); ?>">
                        <i class="<?php echo e($red['icono']); ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="text-gray-600">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php 
                    // Spanish month and day names for fallback
                    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                    
                    // Check if intl extension is available
                    if (extension_loaded('intl')) {
                        try {
                            $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
                            $formatter->setPattern('EEEE, dd \'de\' MMMM \'de\' yyyy');
                            echo $formatter->format(new DateTime());
                        } catch (Exception $e) {
                            // Fallback if locale is not available
                            $fecha = new DateTime();
                            echo $dias[$fecha->format('w')] . ', ' . $fecha->format('d') . ' de ' . $meses[$fecha->format('n') - 1] . ' de ' . $fecha->format('Y');
                        }
                    } else {
                        // Fallback if intl extension is not available
                        $fecha = new DateTime();
                        echo $dias[$fecha->format('w')] . ', ' . $fecha->format('d') . ' de ' . $meses[$fecha->format('n') - 1] . ' de ' . $fecha->format('Y');
                    }
                    ?>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-newspaper text-3xl text-blue-600"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Portal de Noticias Querétaro</h1>
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
        <!-- Slider Principal -->
        <?php if (!empty($slider)): ?>
        <section class="mb-12">
            <div class="relative bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-xl overflow-hidden">
                <div class="relative z-10 px-8 py-16 md:px-16 md:py-20">
                    <?php $currentSlide = $slider[0]; ?>
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        <?php echo e($currentSlide['titulo']); ?>
                    </h2>
                    <p class="text-xl text-blue-100 mb-2">
                        <?php echo e($currentSlide['subtitulo']); ?>
                    </p>
                    <p class="text-lg text-blue-50 mb-6">
                        <?php echo e($currentSlide['contenido']); ?>
                    </p>
                    <a href="<?php echo url('buscar.php'); ?>" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                        Explorar Noticias <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
                <div class="absolute top-0 right-0 bottom-0 w-1/3 bg-gradient-to-l from-blue-900/50 to-transparent"></div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Accesos Directos -->
        <?php if (!empty($accesoDirecto)): ?>
        <section class="mb-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($accesoDirecto as $acceso): ?>
                <a href="<?php echo url($acceso['url']); ?>" class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-xl transition-shadow group">
                    <div class="text-blue-600 text-4xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="<?php echo e($acceso['contenido']); ?>"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        <?php echo e($acceso['titulo']); ?>
                    </h3>
                    <p class="text-sm text-gray-600">
                        <?php echo e($acceso['subtitulo']); ?>
                    </p>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

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
                            <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>">
                                <?php echo e($noticia['titulo']); ?>
                            </a>
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
                            <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                Leer más <i class="fas fa-arrow-right ml-1"></i>
                            </a>
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
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>
                                <i class="fas fa-eye mr-1"></i>
                                <?php echo number_format($noticia['visitas']); ?> visitas
                            </span>
                            <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                Leer más <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <!-- Sección de Contacto -->
        <?php if (!empty($contacto)): ?>
        <section class="mb-12">
            <?php $infoContacto = $contacto[0]; ?>
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-lg shadow-xl p-8 text-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h2 class="text-3xl font-bold mb-2">
                            <i class="fas fa-envelope text-blue-400 mr-2"></i>
                            <?php echo e($infoContacto['titulo']); ?>
                        </h2>
                        <p class="text-xl text-gray-300 mb-4">
                            <?php echo e($infoContacto['subtitulo']); ?>
                        </p>
                        <div class="text-gray-300 space-y-2">
                            <?php echo sanitizeSimpleHtml($infoContacto['contenido']); ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="mailto:contacto@portalqueretaro.mx" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Contáctanos
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
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
