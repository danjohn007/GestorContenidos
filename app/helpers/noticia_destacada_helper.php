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
 */
function displayNoticiasDestacadasGrid($noticias, $cssClass = '') {
    if (empty($noticias)) {
        return;
    }
    
    $safeCssClass = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');
    
    echo '<div class="noticias-destacadas-grid my-8 ' . $safeCssClass . '">';
    echo '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
    
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
}

/**
 * Muestra noticias destacadas en formato carousel
 */
function displayNoticiasDestacadasCarousel($noticias, $cssClass = '') {
    if (empty($noticias)) {
        return;
    }
    
    $carouselId = 'noticias-destacadas-carousel-' . uniqid();
    $safeCssClass = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');
    
    echo '<div class="noticias-destacadas-carousel my-8 ' . $safeCssClass . '" id="' . $carouselId . '">';
    echo '<div class="relative overflow-hidden rounded-lg shadow-xl">';
    
    // Contenedor de slides
    echo '<div class="carousel-slides relative" style="height: 300px;">';
    
    foreach ($noticias as $index => $noticia) {
        $url = !empty($noticia['url_destino']) ? $noticia['url_destino'] : '#';
        $titulo = e($noticia['titulo']);
        $isActive = $index === 0;
        
        echo '<div class="carousel-slide absolute inset-0 transition-opacity duration-500 ' . ($isActive ? 'opacity-100 z-10' : 'opacity-0 z-0') . '" data-slide="' . $index . '">';
        echo '<a href="' . e($url) . '" class="block w-full h-full">';
        
        if (!empty($noticia['imagen_url'])) {
            echo '<img src="' . e(BASE_URL . $noticia['imagen_url']) . '" alt="' . $titulo . '" class="w-full h-full object-cover">';
        } else {
            echo '<div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">';
            echo '<i class="fas fa-image text-white text-6xl"></i>';
            echo '</div>';
        }
        
        echo '</a>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Controles de navegación si hay múltiples imágenes
    if (count($noticias) > 1) {
        echo '<button onclick="changeDestacadaSlide(\'' . $carouselId . '\', -1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-all">';
        echo '<i class="fas fa-chevron-left"></i>';
        echo '</button>';
        echo '<button onclick="changeDestacadaSlide(\'' . $carouselId . '\', 1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-all">';
        echo '<i class="fas fa-chevron-right"></i>';
        echo '</button>';
        
        // Indicadores
        echo '<div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex space-x-2">';
        foreach ($noticias as $index => $noticia) {
            $isActive = $index === 0;
            echo '<button onclick="goToDestacadaSlide(\'' . $carouselId . '\', ' . $index . ')" class="carousel-indicator w-3 h-3 rounded-full transition-all ' . ($isActive ? 'bg-white' : 'bg-white/50') . '" data-indicator="' . $index . '"></button>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // JavaScript para el carousel (solo si hay múltiples slides)
    if (count($noticias) > 1) {
        echo '<script>';
        echo 'if (typeof changeDestacadaSlide === "undefined") {';
        echo '  let destacadaSlides = {};';
        echo '  function changeDestacadaSlide(carouselId, direction) {';
        echo '    if (!destacadaSlides[carouselId]) destacadaSlides[carouselId] = 0;';
        echo '    const carousel = document.getElementById(carouselId);';
        echo '    const slides = carousel.querySelectorAll(".carousel-slide");';
        echo '    const indicators = carousel.querySelectorAll(".carousel-indicator");';
        echo '    const totalSlides = slides.length;';
        echo '    ';
        echo '    slides[destacadaSlides[carouselId]].classList.remove("opacity-100", "z-10");';
        echo '    slides[destacadaSlides[carouselId]].classList.add("opacity-0", "z-0");';
        echo '    indicators[destacadaSlides[carouselId]].classList.remove("bg-white");';
        echo '    indicators[destacadaSlides[carouselId]].classList.add("bg-white/50");';
        echo '    ';
        echo '    destacadaSlides[carouselId] = (destacadaSlides[carouselId] + direction + totalSlides) % totalSlides;';
        echo '    ';
        echo '    slides[destacadaSlides[carouselId]].classList.remove("opacity-0", "z-0");';
        echo '    slides[destacadaSlides[carouselId]].classList.add("opacity-100", "z-10");';
        echo '    indicators[destacadaSlides[carouselId]].classList.remove("bg-white/50");';
        echo '    indicators[destacadaSlides[carouselId]].classList.add("bg-white");';
        echo '  }';
        echo '  ';
        echo '  function goToDestacadaSlide(carouselId, index) {';
        echo '    if (!destacadaSlides[carouselId]) destacadaSlides[carouselId] = 0;';
        echo '    const carousel = document.getElementById(carouselId);';
        echo '    const slides = carousel.querySelectorAll(".carousel-slide");';
        echo '    const indicators = carousel.querySelectorAll(".carousel-indicator");';
        echo '    ';
        echo '    slides[destacadaSlides[carouselId]].classList.remove("opacity-100", "z-10");';
        echo '    slides[destacadaSlides[carouselId]].classList.add("opacity-0", "z-0");';
        echo '    indicators[destacadaSlides[carouselId]].classList.remove("bg-white");';
        echo '    indicators[destacadaSlides[carouselId]].classList.add("bg-white/50");';
        echo '    ';
        echo '    destacadaSlides[carouselId] = index;';
        echo '    ';
        echo '    slides[destacadaSlides[carouselId]].classList.remove("opacity-0", "z-0");';
        echo '    slides[destacadaSlides[carouselId]].classList.add("opacity-100", "z-10");';
        echo '    indicators[destacadaSlides[carouselId]].classList.remove("bg-white/50");';
        echo '    indicators[destacadaSlides[carouselId]].classList.add("bg-white");';
        echo '  }';
        echo '}';
        echo '</script>';
    }
}
?>
