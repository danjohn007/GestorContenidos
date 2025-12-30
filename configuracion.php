<?php
/**
 * Configuración General (Placeholder)
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$title = 'Configuración General';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-cog mr-2 text-gray-600"></i>
            Configuración General
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Modo en Construcción -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="bg-orange-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-hard-hat text-orange-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Modo Construcción</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Activa el modo construcción en el sitio público
            </p>
            <a href="<?php echo url('configuracion_construccion.php'); ?>" class="block w-full bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 transition-colors text-center">
                Configurar
            </a>
        </div>

        <!-- Configuración del Sitio -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-globe text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Datos del Sitio</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Configura el nombre, logotipo y datos generales del portal
            </p>
            <a href="<?php echo url('configuracion_sitio.php'); ?>" class="block w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors text-center">
                Configurar
            </a>
        </div>

        <!-- Correo del Sistema -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-envelope text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Correo Sistema</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Configura el correo SMTP para notificaciones automáticas
            </p>
            <a href="<?php echo url('configuracion_correo.php'); ?>" class="block w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors text-center">
                Configurar
            </a>
        </div>

        <!-- Contacto -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="bg-purple-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-phone text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Información Contacto</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Teléfonos y horarios de atención
            </p>
            <a href="<?php echo url('configuracion_sitio.php'); ?>" class="block w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition-colors text-center">
                Configurar
            </a>
        </div>

        <!-- Diseño -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="bg-pink-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-palette text-pink-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Estilos y Colores</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Personaliza los colores principales del sistema público y admin
            </p>
            <a href="<?php echo url('configuracion_estilos.php'); ?>" class="block w-full bg-pink-600 text-white px-4 py-2 rounded hover:bg-pink-700 transition-colors text-center">
                Configurar
            </a>
        </div>

        <!-- Redes Sociales -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="bg-indigo-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-share-alt text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Redes Sociales</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Enlaces a perfiles en redes sociales
            </p>
            <a href="<?php echo url('configuracion_redes_seo.php'); ?>" class="block w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition-colors text-center">
                Configurar
            </a>
        </div>

        <!-- SEO -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="bg-yellow-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-search text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">SEO y Analytics</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Google Analytics y configuración SEO
            </p>
            <a href="<?php echo url('configuracion_redes_seo.php'); ?>" class="block w-full bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition-colors text-center">
                Configurar
            </a>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Configuración del Sistema
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Aquí puedes personalizar todos los aspectos del sistema según tus necesidades.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
