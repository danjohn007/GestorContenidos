<?php
/**
 * Helper para mostrar noticias destacadas (solo imágenes) en el frontend
 * Incluir este archivo donde se necesite mostrar noticias destacadas
 */

if (!isset($noticiaDestacadaImagenModel)) {
    $noticiaDestacadaImagenModel = new NoticiaDestacadaImagen();
}

/**
 * Muestra noticias destacadas de una ubicación específica
 * @param string $ubicacion - La ubicación (bajo_slider, entre_bloques, antes_footer)
 * @param string $cssClass - Clase CSS adicional para el contenedor
 */
function displayNoticiasDestacadasImagenes($ubicacion, $cssClass = '') {
    global $noticiaDestacadaImagenModel;
    
    // Obtener noticias destacadas activas de esta ubicación
    $noticiasDestacadas = $noticiaDestacadaImagenModel->getByUbicacion($ubicacion);
    
    if (empty($noticiasDestacadas)) {
        return;
    }
    
    // Agrupar por tipo de vista
    $porVista = [];
    foreach ($noticiasDestacadas as $noticia) {
        $vista = $noticia['vista'] ?? 'grid';
        if (!isset($porVista[$vista])) {
            $porVista[$vista] = [];
        }
        $porVista[$vista][] = $noticia;
    }
    
    // Mostrar cada grupo según su vista
    foreach ($porVista as $vista => $noticias) {
        if ($vista === 'carousel') {
            displayNoticiasDestacadasCarousel($noticias, $cssClass);
        } else {
            displayNoticiasDestacadasGrid($noticias, $cssClass);
        }
    }
}

/**
 * Muestra noticias destacadas en formato grid
 * Ahora muestra 4 columnas con controles prev/next cuando hay más de 4 imágenes (igual que carousel)
 */
function displayNoticiasDestacadasGrid($noticias, $cssClass = '') {
    if (empty($noticias)) {
        return;
    }
    
    $totalNoticias = count($noticias);
    
    // Si hay 4 o menos, mostrar en grid simple sin controles
    if ($totalNoticias <= 4) {
        $safeCssClass = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');
        
        echo '<div class="noticias-destacadas-grid my-8 ' . $safeCssClass . '">';
        echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-4">';
        
        foreach ($noticias as $noticia) {
            $url = !empty($noticia['url_destino']) ? $noticia['url_destino'] : '#';
            $titulo = e($noticia['titulo']);
            
            echo '<div class="noticia-destacada-item overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow">';
            echo '<a href="' . e($url) . '" class="block">';
            
            if (!empty($noticia['imagen_url'])) {
                echo '<img src="' . e(BASE_URL . $noticia['imagen_url']) . '" alt="' . $titulo . '" class="w-full h-48 object-cover hover:opacity-90 transition-opacity">';
            } else {
                echo '<div class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">';
                echo '<i class="fas fa-image text-white text-4xl"></i>';
                echo '</div>';
            }
            
            echo '</a>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    } else {
        // Si hay más de 4, usar el sistema de carousel para mantener consistencia
        displayNoticiasDestacadasCarousel($noticias, $cssClass);
    }
}

/**
 * Muestra noticias destacadas en formato carousel
 * Ahora muestra 4 columnas con controles prev/next cuando hay más de 4 imágenes
 */
function displayNoticiasDestacadasCarousel($noticias, $cssClass = '') {
    if (empty($noticias)) {
        return;
    }
    
    $carouselId = 'noticias-destacadas-carousel-' . uniqid();
    $safeCssClass = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');
    $totalNoticias = count($noticias);
    $noticiasPerPage = 4; // Mostrar 4 columnas
    $totalPages = ceil($totalNoticias / $noticiasPerPage);
    
    echo '<div class="noticias-destacadas-carousel my-8 ' . $safeCssClass . '" id="' . $carouselId . '">';
    echo '<div class="relative overflow-hidden">';
    
    // Contenedor de páginas
    echo '<div class="carousel-pages relative">';
    
    // Dividir noticias en páginas de 4
    for ($page = 0; $page < $totalPages; $page++) {
        $noticiasEnPagina = array_slice($noticias, $page * $noticiasPerPage, $noticiasPerPage);
        $isActive = $page === 0;
        
        echo '<div class="carousel-page transition-opacity duration-500 ' . ($isActive ? 'opacity-100 block' : 'opacity-0 hidden') . '" data-page="' . $page . '">';
        echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-4">';
        
        foreach ($noticiasEnPagina as $noticia) {
            $url = !empty($noticia['url_destino']) ? $noticia['url_destino'] : '#';
            $titulo = e($noticia['titulo']);
            
            echo '<div class="noticia-destacada-item overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow">';
            echo '<a href="' . e($url) . '" class="block">';
            
            if (!empty($noticia['imagen_url'])) {
                echo '<img src="' . e(BASE_URL . $noticia['imagen_url']) . '" alt="' . $titulo . '" class="w-full h-48 object-cover hover:opacity-90 transition-opacity">';
            } else {
                echo '<div class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">';
                echo '<i class="fas fa-image text-white text-4xl"></i>';
                echo '</div>';
            }
            
            echo '</a>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Controles de navegación si hay más de 4 imágenes
    if ($totalPages > 1) {
        echo '<button onclick="changeDestacadaCarouselPage(\'' . $carouselId . '\', -1)" class="absolute left-2 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-all">';
        echo '<i class="fas fa-chevron-left"></i>';
        echo '</button>';
        echo '<button onclick="changeDestacadaCarouselPage(\'' . $carouselId . '\', 1)" class="absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-all">';
        echo '<i class="fas fa-chevron-right"></i>';
        echo '</button>';
        
        // Indicadores de página
        echo '<div class="flex justify-center mt-4 space-x-2">';
        for ($i = 0; $i < $totalPages; $i++) {
            $isActive = $i === 0;
            echo '<button onclick="goToDestacadaCarouselPage(\'' . $carouselId . '\', ' . $i . ')" class="carousel-page-indicator w-3 h-3 rounded-full transition-all ' . ($isActive ? 'bg-blue-600' : 'bg-gray-300') . '" data-page-indicator="' . $i . '"></button>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // JavaScript para el carousel (solo si hay múltiples páginas)
    if ($totalPages > 1) {
        echo '<script>';
        echo 'if (typeof changeDestacadaCarouselPage === "undefined") {';
        echo '  let destacadaCarouselPages = {};';
        echo '  function changeDestacadaCarouselPage(carouselId, direction) {';
        echo '    if (!destacadaCarouselPages[carouselId]) destacadaCarouselPages[carouselId] = 0;';
        echo '    const carousel = document.getElementById(carouselId);';
        echo '    const pages = carousel.querySelectorAll(".carousel-page");';
        echo '    const indicators = carousel.querySelectorAll(".carousel-page-indicator");';
        echo '    const totalPages = pages.length;';
        echo '    ';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.remove("opacity-100", "block");';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.add("opacity-0", "hidden");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.remove("bg-blue-600");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.add("bg-gray-300");';
        echo '    ';
        echo '    destacadaCarouselPages[carouselId] = (destacadaCarouselPages[carouselId] + direction + totalPages) % totalPages;';
        echo '    ';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.remove("opacity-0", "hidden");';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.add("opacity-100", "block");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.remove("bg-gray-300");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.add("bg-blue-600");';
        echo '  }';
        echo '  ';
        echo '  function goToDestacadaCarouselPage(carouselId, index) {';
        echo '    if (!destacadaCarouselPages[carouselId]) destacadaCarouselPages[carouselId] = 0;';
        echo '    const carousel = document.getElementById(carouselId);';
        echo '    const pages = carousel.querySelectorAll(".carousel-page");';
        echo '    const indicators = carousel.querySelectorAll(".carousel-page-indicator");';
        echo '    ';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.remove("opacity-100", "block");';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.add("opacity-0", "hidden");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.remove("bg-blue-600");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.add("bg-gray-300");';
        echo '    ';
        echo '    destacadaCarouselPages[carouselId] = index;';
        echo '    ';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.remove("opacity-0", "hidden");';
        echo '    pages[destacadaCarouselPages[carouselId]].classList.add("opacity-100", "block");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.remove("bg-gray-300");';
        echo '    indicators[destacadaCarouselPages[carouselId]].classList.add("bg-blue-600");';
        echo '  }';
        echo '}';
        echo '</script>';
    }
}
?>
