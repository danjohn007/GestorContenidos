<?php
/**
 * Logs y Auditoría
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('logs');

$logModel = new Log();
$usuarioModel = new Usuario();

// Obtener tipo de log a mostrar
$tipoLog = $_GET['tipo'] ?? 'acceso';
$usuarioFiltro = $_GET['usuario'] ?? null;
$moduloFiltro = $_GET['modulo'] ?? null;
$exitosoFiltro = isset($_GET['exitoso']) && $_GET['exitoso'] !== '' ? (int)$_GET['exitoso'] : null;
$page = $_GET['page'] ?? 1;
$perPage = 50;

// Obtener logs según el tipo
if ($tipoLog === 'acceso') {
    $logs = $logModel->getLogsAcceso($usuarioFiltro, $exitosoFiltro, $page, $perPage);
    $totalLogs = $logModel->countLogsAcceso($usuarioFiltro, $exitosoFiltro);
} else {
    $logs = $logModel->getLogsAuditoria($usuarioFiltro, $moduloFiltro, $page, $perPage);
    $totalLogs = $logModel->countLogsAuditoria($usuarioFiltro, $moduloFiltro);
}

$totalPages = ceil($totalLogs / $perPage);

// Obtener usuarios para filtro
$usuarios = $usuarioModel->getAll();

// Obtener módulos para filtro (si es auditoría)
$modulos = $logModel->getModulos();

$title = 'Logs y Auditoría';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-file-alt mr-2 text-orange-600"></i>
            Logs y Auditoría
        </h1>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <a href="?tipo=acceso" 
                   class="px-6 py-3 border-b-2 font-medium text-sm <?php echo $tipoLog === 'acceso' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Logs de Acceso
                </a>
                <a href="?tipo=auditoria" 
                   class="px-6 py-3 border-b-2 font-medium text-sm <?php echo $tipoLog === 'auditoria' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Auditoría de Acciones
                </a>
            </nav>
        </div>

        <!-- Filtros -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="tipo" value="<?php echo e($tipoLog); ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                    <select name="usuario" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $usuarioFiltro == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo e($user['nombre'] . ' ' . $user['apellidos']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if ($tipoLog === 'acceso'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="exitoso" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $exitosoFiltro === 1 ? 'selected' : ''; ?>>Exitosos</option>
                        <option value="0" <?php echo $exitosoFiltro === 0 ? 'selected' : ''; ?>>Fallidos</option>
                    </select>
                </div>
                <?php else: ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                    <select name="modulo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <?php foreach ($modulos as $modulo): ?>
                        <option value="<?php echo e($modulo); ?>" <?php echo $moduloFiltro === $modulo ? 'selected' : ''; ?>>
                            <?php echo e(ucfirst($modulo)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i>
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de Logs -->
        <div class="overflow-x-auto">
            <?php if (empty($logs)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No hay registros de logs</p>
            </div>
            <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php if ($tipoLog === 'acceso'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acción
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mensaje
                        </th>
                        <?php else: ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Módulo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acción
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tabla/ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP
                        </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['fecha'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php if ($log['nombre']): ?>
                                <?php echo e($log['nombre'] . ' ' . $log['apellidos']); ?>
                            <?php elseif ($log['email']): ?>
                                <?php echo e($log['email']); ?>
                            <?php else: ?>
                                <span class="text-gray-400">N/A</span>
                            <?php endif; ?>
                        </td>
                        
                        <?php if ($tipoLog === 'acceso'): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($log['accion']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($log['ip']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($log['exitoso']): ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Exitoso
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i> Fallido
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                            <?php echo e($log['mensaje'] ?? ''); ?>
                        </td>
                        <?php else: ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo e(ucfirst($log['modulo'])); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                echo match($log['accion']) {
                                    'crear' => 'bg-green-100 text-green-800',
                                    'modificar' => 'bg-yellow-100 text-yellow-800',
                                    'eliminar' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo e(ucfirst($log['accion'])); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php if ($log['tabla']): ?>
                                <?php echo e($log['tabla']); ?><?php if ($log['registro_id']): ?> #<?php echo $log['registro_id']; ?><?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($log['ip']); ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($page > 1): ?>
                    <a href="?tipo=<?php echo $tipoLog; ?>&page=<?php echo $page - 1; ?>&usuario=<?php echo $usuarioFiltro; ?>&exitoso=<?php echo $exitosoFiltro; ?>&modulo=<?php echo $moduloFiltro; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                    <a href="?tipo=<?php echo $tipoLog; ?>&page=<?php echo $page + 1; ?>&usuario=<?php echo $usuarioFiltro; ?>&exitoso=<?php echo $exitosoFiltro; ?>&modulo=<?php echo $moduloFiltro; ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Siguiente
                    </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando
                            <span class="font-medium"><?php echo (($page - 1) * $perPage) + 1; ?></span>
                            a
                            <span class="font-medium"><?php echo min($page * $perPage, $totalLogs); ?></span>
                            de
                            <span class="font-medium"><?php echo $totalLogs; ?></span>
                            resultados
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                            <a href="?tipo=<?php echo $tipoLog; ?>&page=<?php echo $i; ?>&usuario=<?php echo $usuarioFiltro; ?>&exitoso=<?php echo $exitosoFiltro; ?>&modulo=<?php echo $moduloFiltro; ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium
                                      <?php echo $i === (int)$page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Logs de Acceso -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <i class="fas fa-sign-in-alt text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Logs de Acceso</h3>
                    <p class="text-sm text-gray-600">Registro de inicios de sesión</p>
                </div>
            </div>
            <ul class="space-y-2 text-sm text-gray-600">
                <li><i class="fas fa-check text-green-500 mr-2"></i>Usuario y email</li>
                <li><i class="fas fa-check text-green-500 mr-2"></i>Fecha y hora</li>
                <li><i class="fas fa-check text-green-500 mr-2"></i>IP y navegador</li>
                <li><i class="fas fa-check text-green-500 mr-2"></i>Accesos exitosos y fallidos</li>
            </ul>
        </div>

        <!-- Auditoría de Acciones -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <i class="fas fa-clipboard-list text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Auditoría de Acciones</h3>
                    <p class="text-sm text-gray-600">Registro de acciones administrativas</p>
                </div>
            </div>
            <ul class="space-y-2 text-sm text-gray-600">
                <li><i class="fas fa-check text-green-500 mr-2"></i>Creación de contenidos</li>
                <li><i class="fas fa-check text-green-500 mr-2"></i>Modificaciones</li>
                <li><i class="fas fa-check text-green-500 mr-2"></i>Eliminaciones</li>
                <li><i class="fas fa-check text-green-500 mr-2"></i>Cambios de estado</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>

