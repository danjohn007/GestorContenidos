<?php
/**
 * Helper para mostrar banners en el frontend
 * Incluir este archivo donde se necesite mostrar banners
 */

if (!isset($bannerModel)) {
    $bannerModel = new Banner();
}

// Variable global para tracking de banners ya mostrados
if (!isset($GLOBALS['displayed_banners'])) {
    $GLOBALS['displayed_banners'] = [];
}

/**
 * Muestra banners de una ubicación específica
 * @param string $ubicacion - La ubicación del banner (inicio, sidebar, footer, etc.)
 * @param int $limit - Número máximo de banners a mostrar
 * @param string $cssClass - Clase CSS adicional para el contenedor
 * @param bool $random - Si se debe aleatorizar la selección de banners
 */
function displayBanners($ubicacion, $limit = null, $cssClass = '', $random = true) {
    global $bannerModel;
    
    // Obtener todos los banners disponibles para esta ubicación
    $allBanners = $bannerModel->getByUbicacion($ubicacion);
    
    if (empty($allBanners)) {
        return;
    }
    
    // Filtrar banners ya mostrados para evitar repetición
    $availableBanners = array_filter($allBanners, function($banner) {
        return !in_array($banner['id'], $GLOBALS['displayed_banners']);
    });
    
    // Si no hay banners disponibles (todos ya mostrados), resetear y usar todos
    if (empty($availableBanners)) {
        // Resetear tracking para esta ubicación
        $GLOBALS['displayed_banners'] = array_diff(
            $GLOBALS['displayed_banners'], 
            array_map(function($b) { return $b['id']; }, $allBanners)
        );
        $availableBanners = $allBanners;
    }
    
    // Aleatorizar si se solicita
    if ($random && count($availableBanners) > 1) {
        shuffle($availableBanners);
    }
    
    // Limitar cantidad si se especifica
    if ($limit !== null) {
        $availableBanners = array_slice($availableBanners, 0, $limit);
    }
    
    // Mostrar banners
    foreach ($availableBanners as $banner) {
        // Registrar como mostrado
        $GLOBALS['displayed_banners'][] = $banner['id'];
        
        // Determinar si se debe mostrar según el dispositivo
        $deviceClass = '';
        if ($banner['dispositivo'] === 'desktop') {
            $deviceClass = 'hidden md:block';
        } elseif ($banner['dispositivo'] === 'movil') {
            $deviceClass = 'block md:hidden';
        }
        
        // Determinar clase según orientación
        $orientacionClass = $banner['orientacion'] === 'vertical' ? 'banner-vertical' : 'banner-horizontal';
        
        // Sanitize CSS class
        $safeCssClass = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');
        
        echo '<div class="' . $orientacionClass . ' ' . $safeCssClass . ' ' . $deviceClass . '" data-banner-id="' . (int)$banner['id'] . '">';
        
        if (!empty($banner['url_destino'])) {
            echo '<a href="' . e($banner['url_destino']) . '" target="_blank" onclick="return trackBannerClick(this);">';
        }
        
        if (!empty($banner['imagen_url'])) {
            echo '<img src="' . e(BASE_URL . $banner['imagen_url']) . '" alt="' . e($banner['nombre']) . '" class="w-full h-auto" loading="lazy">';
        }
        
        if (!empty($banner['url_destino'])) {
            echo '</a>';
        }
        
        echo '</div>';
    }
}

/**
 * Resetea el tracking de banners mostrados
 * Útil cuando se necesita volver a mostrar banners
 */
function resetDisplayedBanners() {
    $GLOBALS['displayed_banners'] = [];
}

/**
 * Muestra banners rotativos (carrusel) de una ubicación específica
 * @param string $ubicacion - La ubicación del banner
 * @param int $interval - Intervalo de rotación en milisegundos (default: 5000)
 * @param string $cssClass - Clase CSS adicional para el contenedor
 */
function displayCarouselBanners($ubicacion, $interval = 5000, $cssClass = '') {
    global $bannerModel;
    
    // Obtener banners rotativos de esta ubicación
    $banners = $bannerModel->getByUbicacion($ubicacion);
    
    if (empty($banners)) {
        return;
    }
    
    // Filtrar solo los banners marcados como rotativos o si hay múltiples imágenes
    $bannerImagenModel = new BannerImagen();
    $carouselBanners = [];
    
    foreach ($banners as $banner) {
        if ($banner['rotativo']) {
            // Obtener imágenes adicionales del banner
            $imagenes = $bannerImagenModel->getByBannerId($banner['id']);
            
            if (!empty($imagenes)) {
                // Banner con múltiples imágenes
                $banner['imagenes'] = $imagenes;
                $carouselBanners[] = $banner;
            } elseif (!empty($banner['imagen_url'])) {
                // Banner con imagen única
                $banner['imagenes'] = [['imagen_url' => $banner['imagen_url']]];
                $carouselBanners[] = $banner;
            }
        }
    }
    
    if (empty($carouselBanners)) {
        // Si no hay banners rotativos, mostrar banners normales
        displayBanners($ubicacion, null, $cssClass, true);
        return;
    }
    
    // Generar ID único para el carrusel
    $carouselId = 'banner-carousel-' . uniqid();
    
    // Registrar banners como mostrados
    foreach ($carouselBanners as $banner) {
        $GLOBALS['displayed_banners'][] = $banner['id'];
    }
    
    $safeCssClass = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');
    
    echo '<div class="banner-carousel-container ' . $safeCssClass . '" id="' . $carouselId . '">';
    
    // Mostrar cada banner del carrusel
    foreach ($carouselBanners as $bannerIndex => $banner) {
        $deviceClass = '';
        if ($banner['dispositivo'] === 'desktop') {
            $deviceClass = 'hidden md:block';
        } elseif ($banner['dispositivo'] === 'movil') {
            $deviceClass = 'block md:hidden';
        }
        
        $orientacionClass = $banner['orientacion'] === 'vertical' ? 'banner-vertical' : 'banner-horizontal';
        
        // Si el banner tiene múltiples imágenes, crear un carrusel interno
        if (count($banner['imagenes']) > 1) {
            echo '<div class="banner-carousel-item ' . $orientacionClass . ' ' . $deviceClass . '" data-banner-id="' . (int)$banner['id'] . '" style="' . ($bannerIndex === 0 ? 'display:block;' : 'display:none;') . '">';
            echo '<div class="relative overflow-hidden rounded-lg">';
            
            foreach ($banner['imagenes'] as $imgIndex => $imagen) {
                echo '<div class="carousel-slide" style="' . ($imgIndex === 0 ? 'display:block;' : 'display:none;') . '">';
                
                if (!empty($banner['url_destino'])) {
                    echo '<a href="' . e($banner['url_destino']) . '" target="_blank" onclick="return trackBannerClick(this);">';
                }
                
                echo '<img src="' . e(BASE_URL . $imagen['imagen_url']) . '" alt="' . e($banner['nombre']) . '" class="w-full h-auto" loading="lazy">';
                
                if (!empty($banner['url_destino'])) {
                    echo '</a>';
                }
                
                echo '</div>';
            }
            
            // Controles de navegación si hay múltiples imágenes
            if (count($banner['imagenes']) > 1) {
                echo '<button class="carousel-prev absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center shadow-lg z-10" onclick="carouselNavigate(this, -1)"><i class="fas fa-chevron-left text-sm"></i></button>';
                echo '<button class="carousel-next absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center shadow-lg z-10" onclick="carouselNavigate(this, 1)"><i class="fas fa-chevron-right text-sm"></i></button>';
                
                // Indicadores
                echo '<div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex space-x-2 z-10">';
                foreach ($banner['imagenes'] as $imgIndex => $imagen) {
                    echo '<button class="carousel-indicator w-2 h-2 rounded-full transition-all ' . ($imgIndex === 0 ? 'bg-white' : 'bg-white/50') . '" onclick="carouselGoTo(this, ' . $imgIndex . ')"></button>';
                }
                echo '</div>';
            }
            
            echo '</div>';
            echo '</div>';
        } else {
            // Banner con imagen única
            echo '<div class="banner-carousel-item ' . $orientacionClass . ' ' . $deviceClass . '" data-banner-id="' . (int)$banner['id'] . '" style="' . ($bannerIndex === 0 ? 'display:block;' : 'display:none;') . '">';
            
            if (!empty($banner['url_destino'])) {
                echo '<a href="' . e($banner['url_destino']) . '" target="_blank" onclick="return trackBannerClick(this);">';
            }
            
            if (!empty($banner['imagenes'][0]['imagen_url'])) {
                echo '<img src="' . e(BASE_URL . $banner['imagenes'][0]['imagen_url']) . '" alt="' . e($banner['nombre']) . '" class="w-full h-auto" loading="lazy">';
            }
            
            if (!empty($banner['url_destino'])) {
                echo '</a>';
            }
            
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    // JavaScript para manejar el carrusel
    if (count($carouselBanners) > 1) {
        echo '<script>';
        echo '(function() {';
        echo '  let currentBanner = 0;';
        echo '  const totalBanners = ' . count($carouselBanners) . ';';
        echo '  const interval = ' . (int)$interval . ';';
        echo '  const carouselId = "' . $carouselId . '";';
        echo '  ';
        echo '  function rotateBanners() {';
        echo '    const items = document.querySelectorAll("#" + carouselId + " .banner-carousel-item");';
        echo '    items[currentBanner].style.display = "none";';
        echo '    currentBanner = (currentBanner + 1) % totalBanners;';
        echo '    items[currentBanner].style.display = "block";';
        echo '  }';
        echo '  ';
        echo '  if (totalBanners > 1) {';
        echo '    setInterval(rotateBanners, interval);';
        echo '  }';
        echo '})();';
        echo '</script>';
    }
}

/**
 * Navegación del carrusel de imágenes interno
 */
if (!function_exists('carouselNavigateScript')) {
    function carouselNavigateScript() {
        echo '<script>';
        echo 'function carouselNavigate(button, direction) {';
        echo '  const container = button.closest(".banner-carousel-item");';
        echo '  const slides = container.querySelectorAll(".carousel-slide");';
        echo '  const indicators = container.querySelectorAll(".carousel-indicator");';
        echo '  let current = 0;';
        echo '  slides.forEach((slide, idx) => {';
        echo '    if (slide.style.display === "block") current = idx;';
        echo '  });';
        echo '  slides[current].style.display = "none";';
        echo '  indicators[current].classList.remove("bg-white");';
        echo '  indicators[current].classList.add("bg-white/50");';
        echo '  current = (current + direction + slides.length) % slides.length;';
        echo '  slides[current].style.display = "block";';
        echo '  indicators[current].classList.remove("bg-white/50");';
        echo '  indicators[current].classList.add("bg-white");';
        echo '}';
        echo 'function carouselGoTo(button, index) {';
        echo '  const container = button.closest(".banner-carousel-item");';
        echo '  const slides = container.querySelectorAll(".carousel-slide");';
        echo '  const indicators = container.querySelectorAll(".carousel-indicator");';
        echo '  slides.forEach((slide, idx) => {';
        echo '    slide.style.display = idx === index ? "block" : "none";';
        echo '  });';
        echo '  indicators.forEach((ind, idx) => {';
        echo '    if (idx === index) {';
        echo '      ind.classList.remove("bg-white/50");';
        echo '      ind.classList.add("bg-white");';
        echo '    } else {';
        echo '      ind.classList.remove("bg-white");';
        echo '      ind.classList.add("bg-white/50");';
        echo '    }';
        echo '  });';
        echo '}';
        echo '</script>';
    }
    carouselNavigateScript();
}
?>
