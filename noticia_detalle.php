<?php
/**
 * Detalle de Noticia
 */
require_once __DIR__ . '/config/bootstrap.php';

$noticiaModel = new Noticia();
$categoriaModel = new Categoria();
$configuracionModel = new Configuracion();
$bannerModel = new Banner();

// Incluir helper de banners
require_once __DIR__ . '/app/helpers/banner_helper.php';

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

// Obtener configuración del sitio
$configGeneral = $configuracionModel->getByGrupo('general');
$configDiseno = $configuracionModel->getByGrupo('diseno');

// Valores de configuración
$nombreSitio = $configGeneral['nombre_sitio']['valor'] ?? 'Portal de Noticias Querétaro';
$logoSitio = $configGeneral['logo_sitio']['valor'] ?? null;
$colorPrimario = $configDiseno['color_primario']['valor'] ?? '#1e40af';
$colorSecundario = $configDiseno['color_secundario']['valor'] ?? '#3b82f6';
$colorAcento = $configDiseno['color_acento']['valor'] ?? '#10b981';
$colorTexto = $configDiseno['color_texto']['valor'] ?? '#1f2937';
$colorFondo = $configDiseno['color_fondo']['valor'] ?? '#f3f4f6';
$fuentePrincipal = $configDiseno['fuente_principal']['valor'] ?? 'system-ui';
$fuenteTitulos = $configDiseno['fuente_titulos']['valor'] ?? 'system-ui';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo e($noticia['titulo']); ?> - <?php echo e($nombreSitio); ?></title>
    <meta name="description" content="<?php echo e($noticia['resumen'] ?? substr(strip_tags($noticia['contenido']), 0, 160)); ?>">
    <?php if ($noticia['tags']): ?>
    <meta name="keywords" content="<?php echo e($noticia['tags']); ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="<?php echo url('public/js/banner-tracking.js'); ?>"></script>
    <style>
        :root {
            --color-primario: <?php echo e($colorPrimario); ?>;
            --color-secundario: <?php echo e($colorSecundario); ?>;
            --color-acento: <?php echo e($colorAcento); ?>;
            --color-texto: <?php echo e($colorTexto); ?>;
            --color-fondo: <?php echo e($colorFondo); ?>;
        }
        body {
            font-family: <?php echo e($fuentePrincipal); ?>;
            background-color: var(--color-fondo);
            color: var(--color-texto);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: <?php echo e($fuenteTitulos); ?>;
        }
        .btn-primary, .bg-blue-600, .hover\:bg-blue-700:hover, 
        .text-blue-600, .hover\:text-blue-600:hover,
        .bg-blue-100, .text-blue-800 {
            background-color: var(--color-primario) !important;
            color: white !important;
        }
        .text-blue-600 {
            background-color: transparent !important;
            color: var(--color-primario) !important;
        }
        .hover\:text-blue-600:hover {
            background-color: transparent !important;
            color: var(--color-primario) !important;
        }
        .bg-blue-100 {
            background-color: rgba(<?php echo hexToRgbString($colorPrimario); ?>, 0.1) !important;
        }
        .text-blue-800 {
            color: var(--color-primario) !important;
            background-color: transparent !important;
        }
        .btn-primary:hover, .bg-blue-600:hover {
            opacity: 0.9;
        }
        /* Footer background */
        .footer-bg {
            background: linear-gradient(to right, var(--color-primario), var(--color-secundario));
        }
        
        /* Mobile menu styles */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }
        
        .mobile-menu-overlay.active {
            display: block;
        }
        
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100%;
            background: white;
            z-index: 999;
            transition: right 0.3s ease;
            overflow-y: auto;
            box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
        }
        
        .mobile-menu.active {
            right: 0;
        }
        
        .mobile-menu-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mobile-menu-close {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        
        .mobile-menu-items {
            padding: 1rem 0;
        }
        
        .mobile-menu-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #374151;
            text-decoration: none;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .mobile-menu-item:hover {
            background: #f9fafb;
            color: var(--color-primario);
        }
        
        .hamburger-btn {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #374151;
        }
        
        @media (max-width: 768px) {
            .desktop-nav {
                display: none;
            }
            
            .desktop-search {
                display: none;
            }
            
            .desktop-login {
                display: none;
            }
            
            .hamburger-btn {
                display: block;
            }
        }
        
        /* Responsive prose content */
        .prose img {
            max-width: 100%;
            height: auto;
        }
        
        .prose {
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        
        /* Quill alignment styles for content display */
        .prose .ql-align-center, .ql-align-center {
            text-align: center;
        }
        .prose .ql-align-right, .ql-align-right {
            text-align: right;
        }
        .prose .ql-align-justify, .ql-align-justify {
            text-align: justify;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <?php if ($logoSitio): ?>
                    <a href="<?php echo url('index.php'); ?>">
                        <img src="<?php echo e(BASE_URL . $logoSitio); ?>" alt="<?php echo e($nombreSitio); ?>" class="h-10">
                    </a>
                    <?php else: ?>
                    <i class="fas fa-newspaper text-3xl text-blue-600"></i>
                    <?php endif; ?>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">
                        <a href="<?php echo url('index.php'); ?>"><?php echo e($nombreSitio); ?></a>
                    </h1>
                </div>
                
                <!-- Hamburger button for mobile -->
                <button class="hamburger-btn" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Desktop Search and Login -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Formulario de búsqueda -->
                    <form method="GET" action="<?php echo url('buscar.php'); ?>" class="flex items-center desktop-search">
                        <input type="text" name="q" placeholder="Buscar noticias..."
                               class="border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-r-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    
                    <a href="<?php echo url('login.php'); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors desktop-login">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Acceder
                    </a>
                </div>
            </div>
            <!-- Navigation -->
            <nav class="border-t border-gray-200 py-3 desktop-nav">
                <ul class="flex space-x-6 overflow-x-auto">
                    <li><a href="<?php echo url('index.php'); ?>" class="text-gray-700 hover:text-blue-600 font-medium whitespace-nowrap">Inicio</a></li>
                    <?php foreach (array_slice($categorias, 0, 6) as $cat): ?>
                    <li><a href="<?php echo url('index.php?categoria=' . $cat['id']); ?>" class="text-gray-700 hover:text-blue-600 whitespace-nowrap"><?php echo e($cat['nombre']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMobileMenu()"></div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <h3 class="text-lg font-bold text-gray-800">Menú</h3>
            <span class="mobile-menu-close" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </span>
        </div>
        <div class="mobile-menu-items">
            <a href="<?php echo url('index.php'); ?>" class="mobile-menu-item">
                <i class="fas fa-home mr-2"></i> Inicio
            </a>
            <?php foreach ($categorias as $cat): ?>
            <a href="<?php echo url('index.php?categoria=' . $cat['id']); ?>" class="mobile-menu-item">
                <i class="fas fa-folder mr-2"></i> <?php echo e($cat['nombre']); ?>
            </a>
            <?php endforeach; ?>
            <div class="border-t border-gray-200 my-2"></div>
            <a href="<?php echo url('buscar.php'); ?>" class="mobile-menu-item">
                <i class="fas fa-search mr-2"></i> Buscar
            </a>
            <a href="<?php echo url('login.php'); ?>" class="mobile-menu-item">
                <i class="fas fa-sign-in-alt mr-2"></i> Acceder
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contenido Principal -->
            <div class="lg:col-span-2">
                <article class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Imagen destacada -->
                    <?php if ($noticia['imagen_destacada']): ?>
                    <img src="<?php echo e(BASE_URL . $noticia['imagen_destacada']); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="w-full h-96 object-cover">
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
                        <!-- Security: Content sanitized with custom sanitizeHtml() function -->
                        <!-- Removes dangerous attributes and JavaScript pseudo-protocols -->
                        <div class="prose max-w-none text-gray-700 leading-relaxed">
                            <?php echo sanitizeHtml($noticia['contenido']); ?>
                        </div>
                        
                        <!-- Banners dentro de noticias -->
                        <div class="my-8">
                            <?php displayBanners('dentro_notas', 1); ?>
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
                <!-- Banners Sidebar -->
                <div class="mb-6">
                    <?php displayBanners('sidebar', 3); ?>
                </div>
                
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
                                <img src="<?php echo e(BASE_URL . $rel['imagen_destacada']); ?>" alt="" class="w-20 h-20 object-cover rounded" loading="lazy">
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
                
                <!-- Banners Sidebar (más banners) -->
                <div class="mb-6">
                    <?php displayBanners('sidebar', 2); ?>
                </div>
                
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
    <footer class="footer-bg text-white mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="text-center opacity-80">
                <p>&copy; <?php echo date('Y'); ?> Portal de Noticias Querétaro. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    
    <script>
    // Mobile menu toggle
    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        
        mobileMenu.classList.toggle('active');
        mobileMenuOverlay.classList.toggle('active');
        
        // Prevent body scroll when menu is open
        if (mobileMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    // Close mobile menu on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
            
            if (mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
                mobileMenuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });
    </script>
</body>
</html>
