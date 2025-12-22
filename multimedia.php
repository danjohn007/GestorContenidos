<?php
/**
 * Gestión de Multimedia (Placeholder)
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$title = 'Gestión de Multimedia';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-images mr-2 text-green-600"></i>
            Gestión de Multimedia
        </h1>
        <button class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
            <i class="fas fa-upload mr-2"></i>
            Subir Archivo
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-images text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Módulo de Multimedia</h3>
        <p class="text-gray-500 mb-4">
            Sistema de gestión de archivos multimedia (imágenes, videos y documentos)
        </p>
        <div class="text-left max-w-2xl mx-auto bg-gray-50 p-4 rounded">
            <p class="text-sm text-gray-700 mb-2"><strong>Características implementables:</strong></p>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                <li>Carga de imágenes, videos y documentos</li>
                <li>Organización por carpetas</li>
                <li>Edición básica de imágenes (recorte, redimensionado)</li>
                <li>Gestión de metadatos (título, descripción, ALT)</li>
                <li>Control de formatos y tamaños permitidos</li>
                <li>Reutilización en múltiples noticias</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
