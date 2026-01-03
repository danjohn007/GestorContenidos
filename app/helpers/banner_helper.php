<?php
/**
 * Helper para mostrar banners en el frontend
 * Incluir este archivo donde se necesite mostrar banners
 */

if (!isset($bannerModel)) {
    $bannerModel = new Banner();
}

/**
 * Muestra banners de una ubicación específica
 * @param string $ubicacion - La ubicación del banner (inicio, sidebar, footer, etc.)
 * @param int $limit - Número máximo de banners a mostrar
 * @param string $cssClass - Clase CSS adicional para el contenedor
 */
function displayBanners($ubicacion, $limit = null, $cssClass = '') {
    global $bannerModel;
    
    $banners = $bannerModel->getByUbicacion($ubicacion, $limit);
    
    if (empty($banners)) {
        return;
    }
    
    foreach ($banners as $banner) {
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
?>
