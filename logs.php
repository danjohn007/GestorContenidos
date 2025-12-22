<?php
/**
 * Logs y Auditoría (Placeholder)
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('logs');

$title = 'Logs y Auditoría';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-file-alt mr-2 text-orange-600"></i>
            Logs y Auditoría
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Logs de Acceso -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-sign-in-alt mr-2 text-blue-600"></i>
                    Logs de Acceso
                </h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    Registro de inicios de sesión y accesos al sistema
                </p>
                <div class="space-y-2 text-sm text-gray-500">
                    <p>• Usuario y email</p>
                    <p>• Fecha y hora</p>
                    <p>• IP y navegador</p>
                    <p>• Accesos exitosos y fallidos</p>
                </div>
            </div>
        </div>

        <!-- Logs de Auditoría -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-clipboard-list mr-2 text-green-600"></i>
                    Auditoría de Acciones
                </h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    Registro de acciones administrativas en el sistema
                </p>
                <div class="space-y-2 text-sm text-gray-500">
                    <p>• Creación de contenidos</p>
                    <p>• Modificaciones</p>
                    <p>• Eliminaciones</p>
                    <p>• Cambios de estado</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-database text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Sistema de Logs</h3>
        <p class="text-gray-500 mb-4">
            Visualización y gestión de logs del sistema
        </p>
        <div class="text-left max-w-2xl mx-auto bg-gray-50 p-4 rounded">
            <p class="text-sm text-gray-700 mb-2"><strong>Funcionalidades disponibles:</strong></p>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                <li>Filtros por usuario, fecha y módulo</li>
                <li>Búsqueda de eventos específicos</li>
                <li>Exportación a Excel/CSV</li>
                <li>Retención configurable de logs</li>
                <li>Alertas de seguridad</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
