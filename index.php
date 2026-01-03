<?php
/**
 * Página Pública Principal
 */
require_once __DIR__ . '/config/bootstrap.php';

// Si está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect('dashboard.php');
}

// Verificar modo en construcción
$configuracionModel = new Configuracion();
$modoConstruccion = $configuracionModel->get('modo_construccion', '0');
if ($modoConstruccion === '1' && !isAuthenticated()) {
    include __DIR__ . '/construccion.php';
    exit;
}

$noticiaModel = new Noticia();
$categoriaModel = new Categoria();
$paginaInicioModel = new PaginaInicio();
$redesSocialesModel = new RedesSociales();
$menuItemModel = new MenuItem();
$bannerModel = new Banner();

// Incluir helper de banners
require_once __DIR__ . '/app/helpers/banner_helper.php';

// Obtener categoría seleccionada del parámetro URL
$categoriaSeleccionada = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$destacadasFilter = isset($_GET['destacadas']) ? true : false;
$recientesFilter = isset($_GET['recientes']) ? true : false;

// Obtener noticias destacadas (si no hay categoría seleccionada o si se pide destacadas)
$noticiasDestacadas = (!$categoriaSeleccionada || $destacadasFilter) ? $noticiaModel->getDestacadas(6) : [];

// Obtener noticias recientes publicadas (filtradas por categoría si está seleccionada)
// Si se solicita destacadas o recientes, mostrar más resultados
$limit = ($destacadasFilter || $recientesFilter) ? 12 : 6;
$noticiasRecientes = $noticiaModel->getAll('publicado', $categoriaSeleccionada, 1, $limit);

// Obtener categorías principales
$categorias = $categoriaModel->getParents(1);

// Obtener ítems del menú principal (solo activos)
$menuItems = $menuItemModel->getAll(1);

// Obtener contenido de página de inicio
$slider = $paginaInicioModel->getBySeccion('slider');
$accesoDirecto = $paginaInicioModel->getBySeccion('acceso_directo');
$accesoLateral = $paginaInicioModel->getBySeccion('acceso_lateral');
$contacto = $paginaInicioModel->getBySeccion('contacto');
$bannersVerticales = $paginaInicioModel->getBySeccion('banner_vertical');
$anunciosFooter = $paginaInicioModel->getBySeccion('anuncio_footer');
$bannersIntermedios = $paginaInicioModel->getBySeccion('banner_intermedio');

// Variable helper para determinar si se muestra contenido de página principal
$mostrarContenidoPaginaPrincipal = !$categoriaSeleccionada && !$destacadasFilter && !$recientesFilter;

// Obtener redes sociales
$redesSociales = $redesSocialesModel->getAll();

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
    <title><?php echo e($nombreSitio); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .from-blue-600 {
            --tw-gradient-from: var(--color-primario) !important;
        }
        .to-blue-800 {
            --tw-gradient-to: var(--color-secundario) !important;
        }
        .text-blue-100, .text-blue-50 {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .btn-primary:hover, .bg-blue-600:hover {
            opacity: 0.9;
        }
        .text-primary {
            color: var(--color-primario);
        }
        .bg-primary {
            background-color: var(--color-primario);
        }
        /* Footer and contact section backgrounds */
        .footer-bg {
            background: linear-gradient(to right, var(--color-primario), var(--color-secundario));
        }
        .contact-bg {
            background: linear-gradient(135deg, 
                rgba(<?php echo hexToRgbString($colorPrimario); ?>, 0.9), 
                rgba(<?php echo hexToRgbString($colorSecundario); ?>, 0.9)
            );
        }
        
        /* Sticky header */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
        
        .mobile-menu-item.active {
            background: #eff6ff;
            color: var(--color-primario);
            font-weight: 600;
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
            
            .hamburger-btn {
                display: block;
            }
        }
        
        /* Sidebar sticky with limit */
        .sidebar-sticky {
            position: sticky;
            top: 80px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
        }
        
        /* Banner styles */
        .banner-vertical {
            margin-bottom: 1.5rem;
            overflow: hidden;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .banner-vertical:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .banner-vertical img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .banner-intermedio {
            margin: 2rem 0;
            overflow: hidden;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .banner-intermedio img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .banner-horizontal {
            margin: 1.5rem 0;
            overflow: hidden;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .banner-horizontal img {
            width: 100%;
            height: auto;
            display: block;
        }
    </style>
    <script>
        // Banner tracking functions
        function trackBannerImpression(bannerId) {
            fetch('<?php echo url("api/banner_track.php"); ?>?action=impression&id=' + bannerId)
                .catch(err => console.error('Error tracking impression:', err));
        }
        
        function trackBannerClick(bannerId) {
            fetch('<?php echo url("api/banner_track.php"); ?>?action=click&id=' + bannerId)
                .catch(err => console.error('Error tracking click:', err));
            return true;
        }
    </script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md sticky-header">
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
                <div class="text-gray-600 hidden md:block">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php 
                    // Function to format date in Spanish
                    $formatearFechaEspanol = function() {
                        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                        $fecha = new DateTime();
                        return $dias[$fecha->format('w')] . ', ' . $fecha->format('d') . ' de ' . $meses[$fecha->format('n') - 1] . ' de ' . $fecha->format('Y');
                    };
                    
                    // Try to use IntlDateFormatter if available
                    if (extension_loaded('intl')) {
                        try {
                            $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
                            $formatter->setPattern('EEEE, dd \'de\' MMMM \'de\' yyyy');
                            echo $formatter->format(new DateTime());
                        } catch (Exception $e) {
                            // Fallback if locale is not available
                            echo $formatearFechaEspanol();
                        }
                    } else {
                        // Fallback if intl extension is not available
                        echo $formatearFechaEspanol();
                    }
                    ?>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <?php if ($logoSitio): ?>
                    <img src="<?php echo e(BASE_URL . $logoSitio); ?>" alt="<?php echo e($nombreSitio); ?>" class="h-10">
                    <?php else: ?>
                    <i class="fas fa-newspaper text-3xl text-blue-600"></i>
                    <?php endif; ?>
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo e($nombreSitio); ?></h1>
                </div>
                
                <!-- Hamburger button for mobile -->
                <button class="hamburger-btn" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Desktop Search and Login -->
                <div class="hidden md:flex items-center space-x-4">
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
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="border-t border-gray-200 py-3 desktop-nav">
                <ul class="flex space-x-6">
                    <li><a href="<?php echo url('index.php'); ?>" class="text-gray-700 hover:text-blue-600 font-medium <?php echo !$categoriaSeleccionada ? 'text-blue-600' : ''; ?>">Inicio</a></li>
                    <?php foreach ($menuItems as $menuItem): ?>
                    <li><a href="<?php echo url('index.php?categoria=' . $menuItem['categoria_id']); ?>" class="text-gray-700 hover:text-blue-600 <?php echo $categoriaSeleccionada == $menuItem['categoria_id'] ? 'text-blue-600 font-medium' : ''; ?>"><?php echo e($menuItem['categoria_nombre']); ?></a></li>
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
            <a href="<?php echo url('index.php'); ?>" class="mobile-menu-item <?php echo !$categoriaSeleccionada ? 'active' : ''; ?>">
                <i class="fas fa-home mr-2"></i> Inicio
            </a>
            <?php foreach ($menuItems as $menuItem): ?>
            <a href="<?php echo url('index.php?categoria=' . $menuItem['categoria_id']); ?>" class="mobile-menu-item <?php echo $categoriaSeleccionada == $menuItem['categoria_id'] ? 'active' : ''; ?>">
                <i class="fas fa-folder mr-2"></i> <?php echo e($menuItem['categoria_nombre']); ?>
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
        <!-- Slider Principal -->
        <?php if (!empty($slider) && $mostrarContenidoPaginaPrincipal): ?>
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
        <?php if (!empty($accesoDirecto) && $mostrarContenidoPaginaPrincipal): ?>
        <section class="mb-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($accesoDirecto as $acceso): ?>
                <a href="<?php echo url($acceso['url']); ?>" class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-xl transition-shadow group">
                    <div class="text-blue-600 text-4xl mb-3 group-hover:scale-110 transition-transform">
                        <?php if (!empty($acceso['imagen'])): ?>
                            <img src="<?php echo e($acceso['imagen']); ?>" alt="<?php echo e($acceso['titulo']); ?>" class="w-16 h-16 mx-auto object-contain">
                        <?php else: ?>
                            <i class="<?php echo e($acceso['contenido']); ?>"></i>
                        <?php endif; ?>
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

        <!-- Layout de dos columnas: Contenido principal y Accesos Laterales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna Principal -->
            <div class="lg:col-span-2">
                <!-- Noticias Destacadas -->
                <?php if (!empty($noticiasDestacadas) && !$recientesFilter): ?>
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Noticias Destacadas
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($noticiasDestacadas as $noticia): ?>
                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                            <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>">
                                <?php if ($noticia['imagen_destacada']): ?>
                                <img src="<?php echo e(BASE_URL . $noticia['imagen_destacada']); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="w-full h-48 object-cover hover:opacity-90 transition-opacity cursor-pointer">
                                <?php else: ?>
                                <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center hover:opacity-90 transition-opacity cursor-pointer">
                                    <i class="fas fa-newspaper text-white text-6xl"></i>
                                </div>
                                <?php endif; ?>
                            </a>
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
                
                <!-- Banner Intermedio desde sistema de gestión -->
                <?php if ($mostrarContenidoPaginaPrincipal): ?>
                    <?php displayBanners('entre_secciones', 1); ?>
                <?php endif; ?>
                
                <!-- Banner Intermedio antiguo (PaginaInicio) - Mantener compatibilidad -->
                <?php if (!empty($bannersIntermedios) && isset($bannersIntermedios[0]) && $bannersIntermedios[0]['activo'] && !empty($bannersIntermedios[0]['imagen']) && $mostrarContenidoPaginaPrincipal): ?>
                <div class="banner-intermedio">
                    <a href="<?php echo e($bannersIntermedios[0]['url'] ?? '#'); ?>" target="_blank">
                        <img src="<?php echo e($bannersIntermedios[0]['imagen']); ?>" alt="<?php echo e($bannersIntermedios[0]['titulo']); ?>">
                    </a>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Noticias Recientes -->
                <section>
                    <?php if ($categoriaSeleccionada): ?>
                    <?php 
                    $categoriaActual = $categoriaModel->getById($categoriaSeleccionada);
                    ?>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-folder-open text-blue-600 mr-2"></i>
                        Noticias de <?php echo e($categoriaActual['nombre']); ?>
                    </h2>
                    <?php elseif ($destacadasFilter): ?>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Todas las Noticias Destacadas
                    </h2>
                    <?php elseif ($recientesFilter): ?>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                        Noticias de Última Hora
                    </h2>
                    <?php else: ?>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                        Últimas Noticias
                    </h2>
                    <?php endif; ?>
                    <?php if (empty($noticiasRecientes)): ?>
                    <div class="bg-white rounded-lg shadow-md p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No hay noticias disponibles en este momento</p>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($noticiasRecientes as $noticia): ?>
                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                            <?php if ($noticia['imagen_destacada']): ?>
                            <a href="<?php echo url('noticia_detalle.php?slug=' . $noticia['slug']); ?>">
                                <img src="<?php echo e(BASE_URL . $noticia['imagen_destacada']); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="w-full h-40 object-cover">
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
            </div>

            <!-- Columna Lateral - Accesos Laterales y Banners Verticales -->
            <div class="lg:col-span-1">
                <div class="sidebar-sticky">
                    <?php if (!empty($accesoLateral)): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">
                            <i class="fas fa-th-list text-blue-600 mr-2"></i>
                            Accesos Rápidos
                        </h3>
                        <div class="space-y-4">
                            <?php foreach (array_slice($accesoLateral, 0, 3) as $lateral): ?>
                            <a href="<?php echo url($lateral['url']); ?>" class="block bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 hover:from-blue-100 hover:to-blue-200 transition-all group">
                                <div class="flex items-center">
                                    <div class="text-blue-600 text-3xl mr-4 group-hover:scale-110 transition-transform">
                                        <?php if (!empty($lateral['imagen'])): ?>
                                            <img src="<?php echo e($lateral['imagen']); ?>" alt="<?php echo e($lateral['titulo']); ?>" class="w-12 h-12 object-contain">
                                        <?php else: ?>
                                            <i class="<?php echo e($lateral['contenido']); ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-800 group-hover:text-blue-600">
                                            <?php echo e($lateral['titulo']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo e($lateral['subtitulo']); ?>
                                        </p>
                                    </div>
                                    <div class="text-blue-600">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Banners Verticales desde el sistema de gestión -->
                    <?php displayBanners('sidebar', 3); ?>
                    
                    <!-- Banners Verticales antiguos (PaginaInicio) - Mantener compatibilidad -->
                    <?php if (!empty($bannersVerticales)): ?>
                        <?php foreach ($bannersVerticales as $banner): ?>
                            <?php if ($banner['activo'] && !empty($banner['imagen'])): ?>
                            <div class="banner-vertical">
                                <a href="<?php echo e($banner['url'] ?? '#'); ?>" target="_blank">
                                    <img src="<?php echo e($banner['imagen']); ?>" alt="<?php echo e($banner['titulo']); ?>">
                                </a>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Categorías (en el sidebar también) -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-6" id="categorias">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-th text-blue-600 mr-2"></i>
                        Categorías
                    </h3>
                    <div class="space-y-2">
                        <?php foreach ($menuItems as $menuItem): ?>
                        <a href="<?php echo url('index.php?categoria=' . $menuItem['categoria_id']); ?>" 
                           class="block px-4 py-2 rounded hover:bg-blue-50 transition-colors <?php echo $categoriaSeleccionada == $menuItem['categoria_id'] ? 'bg-blue-100 text-blue-600 font-medium' : 'text-gray-700'; ?>">
                            <i class="fas fa-folder mr-2"></i>
                            <?php echo e($menuItem['categoria_nombre']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Contacto -->
        <?php if (!empty($contacto) && $mostrarContenidoPaginaPrincipal): ?>
        <section class="mt-12">
            <?php $infoContacto = $contacto[0]; ?>
            <div class="contact-bg rounded-lg shadow-xl p-8 text-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h2 class="text-3xl font-bold mb-2">
                            <i class="fas fa-envelope mr-2"></i>
                            <?php echo e($infoContacto['titulo']); ?>
                        </h2>
                        <p class="text-xl mb-4 opacity-90">
                            <?php echo e($infoContacto['subtitulo']); ?>
                        </p>
                        <div class="space-y-2 opacity-90">
                            <?php echo sanitizeSimpleHtml($infoContacto['contenido']); ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="mailto:contacto@portalqueretaro.mx" class="inline-block bg-white px-8 py-4 rounded-lg font-semibold hover:bg-opacity-90 transition-colors" style="color: var(--color-primario);">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Contáctanos
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Banners Footer desde sistema de gestión -->
        <?php if ($mostrarContenidoPaginaPrincipal): ?>
        <section class="mt-12">
            <?php displayBanners('footer', 4); ?>
        </section>
        <?php endif; ?>
        
        <!-- Grid de Anuncios Footer antiguos (PaginaInicio) - Mantener compatibilidad -->
        <?php 
        $anunciosFooterActivos = array_filter($anunciosFooter, function($anuncio) {
            return $anuncio['activo'] && !empty($anuncio['imagen']);
        });
        ?>
        <?php if (!empty($anunciosFooterActivos) && $mostrarContenidoPaginaPrincipal): ?>
        <section class="mt-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($anunciosFooterActivos as $anuncio): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <a href="<?php echo e($anuncio['url'] ?? '#'); ?>" target="_blank">
                        <img src="<?php echo e($anuncio['imagen']); ?>" alt="<?php echo e($anuncio['titulo']); ?>" class="w-full h-auto">
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer-bg text-white mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-newspaper mr-2"></i>
                        Portal de Noticias
                    </h3>
                    <p class="opacity-80">Tu fuente de noticias de Querétaro</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Categorías</h4>
                    <ul class="space-y-2 opacity-80">
                        <?php foreach (array_slice($menuItems, 0, 5) as $menuItem): ?>
                        <li><a href="<?php echo url('index.php?categoria=' . $menuItem['categoria_id']); ?>" class="hover:opacity-100"><?php echo e($menuItem['categoria_nombre']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contacto</h4>
                    <p class="opacity-80">
                        <i class="fas fa-phone mr-2"></i>
                        442-123-4567
                    </p>
                    <p class="opacity-80 mt-2">
                        <i class="fas fa-envelope mr-2"></i>
                        contacto@portalqueretaro.mx
                    </p>
                </div>
            </div>
            <div class="border-t border-white/20 mt-8 pt-8 text-center opacity-80">
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
