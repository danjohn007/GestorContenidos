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
?>
