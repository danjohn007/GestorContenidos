<?php
/**
 * Página Pública Principal
 */
require_once __DIR__ . '/config/bootstrap.php';

// Si está autenticado, redirigir al dashboard (a menos que se solicite ver el sitio público)
$verSitioPublico = isset($_GET['preview']) && $_GET['preview'] === '1';
if (isAuthenticated() && !$verSitioPublico) {
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
$menuItems = $menuItemModel->getAllWithSubcategories(1);

// Obtener configuración del slider
$sliderTipo = $configuracionModel->get('slider_tipo', 'estatico');
$sliderCantidad = (int)$configuracionModel->get('slider_cantidad', 3);
$sliderAutoplay = $configuracionModel->get('slider_autoplay', '1') === '1';
$sliderIntervalo = (int)$configuracionModel->get('slider_intervalo', 5000);

// Obtener contenido de página de inicio
$slider = $paginaInicioModel->getBySeccion('slider');

// Si el slider está configurado para mostrar noticias o mixto, obtener noticias destacadas
$sliderNoticias = [];
if ($sliderTipo === 'noticias' || $sliderTipo === 'mixto') {
    $sliderNoticias = $noticiaModel->getDestacadas($sliderCantidad);
}

// Combinar contenido según el tipo de slider
$sliderItems = [];
if ($sliderTipo === 'noticias') {
    $sliderItems = $sliderNoticias;
} elseif ($sliderTipo === 'mixto') {
    // Mezclar slider estático con noticias
    $staticItems = array_slice($slider, 0, max(1, floor($sliderCantidad / 2)));
    $newsItems = array_slice($sliderNoticias, 0, max(1, ceil($sliderCantidad / 2)));
    $sliderItems = array_merge($staticItems, $newsItems);
    shuffle($sliderItems);
    $sliderItems = array_slice($sliderItems, 0, $sliderCantidad);
} else {
    // Estático: usar solo contenido de pagina_inicio
    $sliderItems = array_slice($slider, 0, $sliderCantidad);
}

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
$modoLogo = $configGeneral['modo_logo']['valor'] ?? 'imagen';
$tamanoLogo = $configGeneral['tamano_logo']['valor'] ?? 'h-10';
$colorPrimario = $configDiseno['color_primario']['valor'] ?? '#1e40af';
$colorSecundario = $configDiseno['color_secundario']['valor'] ?? '#3b82f6';
$colorAcento = $configDiseno['color_acento']['valor'] ?? '#10b981';
$colorTexto = $configDiseno['color_texto']['valor'] ?? '#1f2937';
$colorFondo = $configDiseno['color_fondo']['valor'] ?? '#f3f4f6';
$fuentePrincipal = $configDiseno['fuente_principal']['valor'] ?? 'system-ui';
$fuenteTitulos = $configDiseno['fuente_titulos']['valor'] ?? 'system-ui';
$sloganSitio = !empty($configGeneral['slogan_sitio']['valor']) ? $configGeneral['slogan_sitio']['valor'] : 'Tu fuente de noticias de Querétaro';
$descripcionSitio = $configGeneral['descripcion_sitio']['valor'] ?? '';
$emailSistema = !empty($configGeneral['email_sistema']['valor']) ? $configGeneral['email_sistema']['valor'] : 'contacto@portalqueretaro.mx';
$telefonoContacto = !empty($configGeneral['telefono_contacto']['valor']) ? $configGeneral['telefono_contacto']['valor'] : '442-123-4567';
$direccion = $configGeneral['direccion']['valor'] ?? '';
$textoFooter = $configGeneral['texto_footer']['valor'] ?? '&copy; ' . date('Y') . ' ' . $nombreSitio . '. Todos los derechos reservados.';
$avisoLegal = $configGeneral['aviso_legal']['valor'] ?? '';
$mostrarAvisoLegal = ($configGeneral['mostrar_aviso_legal']['valor'] ?? '1') === '1';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo e($nombreSitio); ?></title>
    <?php 
    // Cargar favicon si está configurado
    $faviconSitio = $configGeneral['favicon_sitio']['valor'] ?? null;
    if ($faviconSitio): 
        // Determinar tipo MIME según extensión
        $faviconExt = strtolower(pathinfo($faviconSitio, PATHINFO_EXTENSION));
        $faviconType = 'image/x-icon'; // default
        if ($faviconExt === 'png') {
            $faviconType = 'image/png';
        } elseif ($faviconExt === 'jpg' || $faviconExt === 'jpeg') {
            $faviconType = 'image/jpeg';
        } elseif ($faviconExt === 'svg') {
            $faviconType = 'image/svg+xml';
        }
    ?>
    <link rel="icon" type="<?php echo $faviconType; ?>" href="<?php echo e(BASE_URL . $faviconSitio); ?>">
    <link rel="shortcut icon" type="<?php echo $faviconType; ?>" href="<?php echo e(BASE_URL . $faviconSitio); ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS - Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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
            
            .slider-container {
                height: 300px !important;
            }
            
            .slider-slide h2 {
                font-size: 1.5rem !important;
            }
            
            .slider-slide p {
                font-size: 0.875rem !important;
            }
        }
        
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        
        /* Slider responsive styles */
        .slider-container {
            position: relative;
            overflow: hidden;
        }
        
        .slider-slide {
            transition: opacity 0.5s ease-in-out;
        }
        
        /* Sidebar sticky - increased height for full ad display without scroll */
        .sidebar-sticky {
            position: sticky;
            top: 80px;
            /* Removed max-height and overflow-y to show full ads without internal scroll */
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
        
        /* Banner size classes */
        .banner-size-horizontal {
            max-width: 1200px;
            max-height: 400px;
        }
        
        .banner-size-cuadrado {
            max-width: 600px;
            max-height: 600px;
        }
        
        .banner-size-vertical {
            max-width: 300px;
            max-height: 600px;
        }
        
        .banner-size-real {
            /* No constraints - natural size */
        }
        
        .banner-size-auto {
            /* Responsive - no constraints */
        }
    </style>
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
                    <span id="fecha-hora-header">
                    <?php 
                    // Function to format date in Spanish
                    $formatearFechaEspanol = function() {
                        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                        $fecha = new DateTime();
                        return $dias[$fecha->format('w')] . ', ' . $fecha->format('d') . ' de ' . $meses[$fecha->format('n') - 1] . ' de ' . $fecha->format('Y') . ' - ' . $fecha->format('H:i:s');
                    };
                    
                    // Try to use IntlDateFormatter if available
                    if (extension_loaded('intl')) {
                        try {
                            $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
                            $formatter->setPattern('EEEE, dd \'de\' MMMM \'de\' yyyy');
                            echo $formatter->format(new DateTime());
                            echo ' - ' . (new DateTime())->format('H:i:s');
                        } catch (Exception $e) {
                            // Fallback if locale is not available
                            echo $formatearFechaEspanol();
                        }
                    } else {
                        // Fallback if intl extension is not available
                        echo $formatearFechaEspanol();
                    }
                    ?>
                    </span>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <?php if ($modoLogo === 'imagen' && $logoSitio): ?>
                    <a href="<?php echo url('index.php'); ?>">
                        <img src="<?php echo e(BASE_URL . $logoSitio); ?>" alt="<?php echo e($nombreSitio); ?>" class="<?php echo e($tamanoLogo); ?>">
                    </a>
                    <?php elseif ($modoLogo === 'texto' || !$logoSitio): ?>
                    <i class="fas fa-newspaper text-3xl text-blue-600"></i>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <a href="<?php echo url('index.php'); ?>"><?php echo e($nombreSitio); ?></a>
                    </h1>
                    <?php endif; ?>
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
                </div>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="border-t border-gray-200 py-3 desktop-nav">
                <ul class="flex space-x-6">
                    <li><a href="<?php echo url('index.php'); ?>" class="text-gray-700 hover:text-blue-600 font-medium <?php echo !$categoriaSeleccionada ? 'text-blue-600' : ''; ?>">Inicio</a></li>
                    <?php foreach ($menuItems as $menuItem): ?>
                    <li class="relative group">
                        <a href="<?php echo url('index.php?categoria=' . $menuItem['categoria_id']); ?>" 
                           class="text-gray-700 hover:text-blue-600 <?php echo $categoriaSeleccionada == $menuItem['categoria_id'] ? 'text-blue-600 font-medium' : ''; ?>">
                            <?php echo e($menuItem['categoria_nombre']); ?>
                        </a>
                        <?php if (!empty($menuItem['subcategorias'])): ?>
                        <!-- Submenu -->
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="py-2">
                                <?php foreach ($menuItem['subcategorias'] as $subcat): ?>
                                <a href="<?php echo url('index.php?categoria=' . $subcat['id']); ?>" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <?php echo e($subcat['nombre']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </li>
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
            <a href="<?php echo url('index.php?categoria=' . $menuItem['categoria_id']); ?>" 
               class="mobile-menu-item <?php echo $categoriaSeleccionada == $menuItem['categoria_id'] ? 'active' : ''; ?>">
                <i class="fas fa-folder mr-2"></i> <?php echo e($menuItem['categoria_nombre']); ?>
            </a>
            <?php if (!empty($menuItem['subcategorias'])): ?>
                <?php foreach ($menuItem['subcategorias'] as $subcat): ?>
                <a href="<?php echo url('index.php?categoria=' . $subcat['id']); ?>" 
                   class="mobile-menu-item pl-8 text-sm <?php echo $categoriaSeleccionada == $subcat['id'] ? 'active' : ''; ?>">
                    <i class="fas fa-angle-right mr-2"></i> <?php echo e($subcat['nombre']); ?>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php endforeach; ?>
            <div class="border-t border-gray-200 my-2"></div>
            <a href="<?php echo url('buscar.php'); ?>" class="mobile-menu-item">
                <i class="fas fa-search mr-2"></i> Buscar
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Slider Principal -->
        <?php if (!empty($sliderItems) && $mostrarContenidoPaginaPrincipal): ?>
        <section class="mb-12">
            <div class="relative rounded-lg shadow-xl overflow-hidden" id="mainSlider">
                <!-- Slides Container -->
                <div class="slider-container relative" style="height: 400px;">
                    <?php foreach ($sliderItems as $index => $slide): ?>
                    <?php
                    // Determinar si es una noticia o contenido estático
                    // Las noticias tienen campos específicos como 'autor_id' y 'categoria_id'
                    $esNoticia = isset($slide['categoria_id']) && isset($slide['slug']);
                    $titulo = $esNoticia ? $slide['titulo'] : $slide['titulo'];
                    $subtitulo = $esNoticia ? ($slide['subtitulo'] ?? '') : ($slide['subtitulo'] ?? '');
                    $contenido = $esNoticia ? ($slide['resumen'] ?? strip_tags(substr($slide['contenido'], 0, 150))) : ($slide['contenido'] ?? '');
                    $imagen = $esNoticia ? ($slide['imagen_destacada'] ?? null) : ($slide['imagen'] ?? $slide['imagen_slider'] ?? null);
                    $enlace = $esNoticia ? url('noticia_detalle.php?slug=' . $slide['slug']) : ($slide['url'] ?? 'javascript:void(0)');
                    ?>
                    <div class="slider-slide absolute inset-0 transition-opacity duration-500 <?php echo $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>" data-slide="<?php echo $index; ?>">
                        <?php if ($imagen): ?>
                        <!-- Slide con imagen -->
                        <div class="relative w-full h-full">
                            <img src="<?php echo e($esNoticia ? BASE_URL . $imagen : (strpos($imagen, 'http') === 0 ? $imagen : BASE_URL . $imagen)); ?>" 
                                 alt="<?php echo e($titulo); ?>" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/30"></div>
                            <div class="absolute inset-0 flex items-center">
                                <div class="container mx-auto px-8 md:px-16">
                                    <div class="max-w-2xl">
                                        <h2 class="text-3xl md:text-5xl font-bold text-white mb-3">
                                            <?php echo e($titulo); ?>
                                        </h2>
                                        <?php if ($subtitulo): ?>
                                        <p class="text-xl text-white/90 mb-2">
                                            <?php echo e($subtitulo); ?>
                                        </p>
                                        <?php endif; ?>
                                        <?php if ($contenido): ?>
                                        <p class="text-lg text-white/80 mb-6 line-clamp-2">
                                            <?php echo e($contenido); ?>
                                        </p>
                                        <?php endif; ?>
                                        <a href="<?php echo e($enlace); ?>" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                                            <?php echo $esNoticia ? 'Leer Noticia' : 'Explorar'; ?> <i class="fas fa-arrow-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Slide sin imagen (con gradiente) -->
                        <div class="relative w-full h-full bg-gradient-to-r from-blue-600 to-blue-800">
                            <div class="absolute inset-0 flex items-center">
                                <div class="container mx-auto px-8 md:px-16">
                                    <div class="max-w-2xl">
                                        <h2 class="text-3xl md:text-5xl font-bold text-white mb-3">
                                            <?php echo e($titulo); ?>
                                        </h2>
                                        <?php if ($subtitulo): ?>
                                        <p class="text-xl text-blue-100 mb-2">
                                            <?php echo e($subtitulo); ?>
                                        </p>
                                        <?php endif; ?>
                                        <?php if ($contenido): ?>
                                        <p class="text-lg text-blue-50 mb-6">
                                            <?php echo e($contenido); ?>
                                        </p>
                                        <?php endif; ?>
                                        <a href="<?php echo e($enlace); ?>" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                                            <?php echo $esNoticia ? 'Leer Noticia' : 'Explorar'; ?> <i class="fas fa-arrow-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-0 right-0 bottom-0 w-1/3 bg-gradient-to-l from-blue-900/50 to-transparent"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Navigation Arrows -->
                <?php if (count($sliderItems) > 1): ?>
                <button onclick="changeSlide(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 w-12 h-12 rounded-full flex items-center justify-center shadow-lg transition-all">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button onclick="changeSlide(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 w-12 h-12 rounded-full flex items-center justify-center shadow-lg transition-all">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Slide Indicators -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex space-x-2">
                    <?php foreach ($sliderItems as $index => $slide): ?>
                    <button onclick="goToSlide(<?php echo $index; ?>)" 
                            class="slider-indicator w-3 h-3 rounded-full transition-all <?php echo $index === 0 ? 'bg-white' : 'bg-white/50'; ?>" 
                            data-indicator="<?php echo $index; ?>">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <script>
            let currentSlide = 0;
            const totalSlides = <?php echo count($sliderItems); ?>;
            const autoplay = <?php echo $sliderAutoplay ? 'true' : 'false'; ?>;
            const interval = <?php echo $sliderIntervalo; ?>;
            let autoplayTimer = null;
            
            function showSlide(index) {
                const slides = document.querySelectorAll('.slider-slide');
                const indicators = document.querySelectorAll('.slider-indicator');
                
                slides.forEach((slide, i) => {
                    if (i === index) {
                        slide.classList.remove('opacity-0', 'z-0');
                        slide.classList.add('opacity-100', 'z-10');
                    } else {
                        slide.classList.remove('opacity-100', 'z-10');
                        slide.classList.add('opacity-0', 'z-0');
                    }
                });
                
                indicators.forEach((indicator, i) => {
                    if (i === index) {
                        indicator.classList.remove('bg-white/50');
                        indicator.classList.add('bg-white');
                    } else {
                        indicator.classList.remove('bg-white');
                        indicator.classList.add('bg-white/50');
                    }
                });
            }
            
            function changeSlide(direction) {
                currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
                showSlide(currentSlide);
                resetAutoplay();
            }
            
            function goToSlide(index) {
                currentSlide = index;
                showSlide(currentSlide);
                resetAutoplay();
            }
            
            function nextSlide() {
                currentSlide = (currentSlide + 1) % totalSlides;
                showSlide(currentSlide);
            }
            
            function resetAutoplay() {
                if (autoplay) {
                    clearInterval(autoplayTimer);
                    autoplayTimer = setInterval(nextSlide, interval);
                }
            }
            
            // Start autoplay if enabled
            if (autoplay && totalSlides > 1) {
                autoplayTimer = setInterval(nextSlide, interval);
            }
            
            // Pause autoplay on hover
            document.getElementById('mainSlider')?.addEventListener('mouseenter', function() {
                if (autoplay) clearInterval(autoplayTimer);
            });
            
            document.getElementById('mainSlider')?.addEventListener('mouseleave', function() {
                if (autoplay) autoplayTimer = setInterval(nextSlide, interval);
            });
            </script>
        </section>
        <?php endif; ?>

        <!-- Layout de dos columnas: Contenido principal y Accesos Laterales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna Principal -->
            <div class="lg:col-span-2">
                <!-- Noticias Destacadas -->
                <?php if (!empty($noticiasDestacadas) && !$recientesFilter): ?>
                <section class="mb-12" data-aos="fade-up">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Noticias Destacadas
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($noticiasDestacadas as $noticia): ?>
                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="100">
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
                <section data-aos="fade-up">
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
                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="50">
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
                
                <!-- Noticias por Categoría - Mayor visibilidad -->
                <?php if ($mostrarContenidoPaginaPrincipal): ?>
                    <?php 
                    // Obtener las primeras 4 categorías activas del menú
                    $categoriasParaMostrar = array_slice($menuItems, 0, 4);
                    foreach ($categoriasParaMostrar as $categoriaMenu): 
                        $noticiasPorCategoria = $noticiaModel->getAll('publicado', $categoriaMenu['categoria_id'], 1, 4);
                        if (!empty($noticiasPorCategoria)):
                    ?>
                    <section class="mt-12">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-3xl font-bold text-gray-800">
                                <i class="fas fa-folder text-blue-600 mr-2"></i>
                                <?php echo e($categoriaMenu['categoria_nombre']); ?>
                            </h2>
                            <a href="<?php echo url('index.php?categoria=' . $categoriaMenu['categoria_id']); ?>" 
                               class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                Ver todas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($noticiasPorCategoria as $noticia): ?>
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
                        
                        <!-- Banner entre categorías -->
                        <?php displayBanners('entre_secciones', 1); ?>
                    </section>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                <?php endif; ?>
            </div>

            <!-- Columna Lateral - Accesos Laterales y Banners Verticales -->
            <div class="lg:col-span-1">
                <div class="sidebar-sticky">
                    <?php if (!empty($accesoLateral)): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6" data-aos="fade-left">
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
                        <?php echo e($nombreSitio); ?>
                    </h3>
                    <p class="opacity-80"><?php echo e($sloganSitio); ?></p>
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
                        <?php echo e($telefonoContacto); ?>
                    </p>
                    <p class="opacity-80 mt-2">
                        <i class="fas fa-envelope mr-2"></i>
                        <?php echo e($emailSistema); ?>
                    </p>
                </div>
            </div>
            <div class="border-t border-white/20 mt-8 pt-8 text-center">
                <div class="opacity-80 mb-4">
                    <?php echo nl2br(e($textoFooter)); ?>
                </div>
                <?php if ($mostrarAvisoLegal && !empty($avisoLegal)): ?>
                <div class="mt-2">
                    <a href="<?php echo url('aviso-legal.php'); ?>" class="text-white hover:text-blue-200 underline text-sm">
                        Aviso Legal
                    </a>
                </div>
                <?php endif; ?>
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
    
    // Update clock in header
    function actualizarReloj() {
        const ahora = new Date();
        const dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        
        const diaSemana = dias[ahora.getDay()];
        const dia = ahora.getDate();
        const mes = meses[ahora.getMonth()];
        const anio = ahora.getFullYear();
        
        const horas = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        
        const textoFecha = `${diaSemana}, ${dia} de ${mes} de ${anio} - ${horas}:${minutos}:${segundos}`;
        
        const elementoFecha = document.getElementById('fecha-hora-header');
        if (elementoFecha) {
            elementoFecha.textContent = textoFecha;
        }
    }
    
    // Actualizar el reloj cada segundo
    setInterval(actualizarReloj, 1000);
    // Actualizar inmediatamente al cargar
    actualizarReloj();
    
    // Inicializar AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });
    </script>
</body>
</html>
