<?php
/**
 * Listado de Usuarios
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('usuarios');

$usuarioModel = new Usuario();

// Obtener usuarios
$usuarios = $usuarioModel->getAll();

$title = 'Gestión de Usuarios';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-users mr-2 text-indigo-600"></i>
            Gestión de Usuarios
        </h1>
        <?php if (hasPermission('usuarios') || hasPermission('all')): ?>
        <a href="<?php echo url('usuario_crear.php'); ?>" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-user-plus mr-2"></i>
            Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>

    <!-- Lista de Usuarios -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($usuarios)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No hay usuarios registrados</p>
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Usuario
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Rol
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Último Acceso
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($usuarios as $usuario): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                                    <span class="text-white font-semibold">
                                        <?php echo strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellidos'], 0, 1)); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo e($usuario['nombre'] . ' ' . $usuario['apellidos']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?php echo e($usuario['email']); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo e($usuario['rol_nombre']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($usuario['activo']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Activo
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i> Inactivo
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php 
                        if ($usuario['ultimo_acceso']) {
                            echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']));
                        } else {
                            echo 'Nunca';
                        }
                        ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?php echo url('usuario_editar.php?id=' . $usuario['id']); ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($usuario['id'] != getCurrentUser()['id']): ?>
                        <a href="<?php echo url('usuario_cambiar_estado.php?id=' . $usuario['id']); ?>" class="text-orange-600 hover:text-orange-900">
                            <i class="fas fa-<?php echo $usuario['activo'] ? 'ban' : 'check'; ?>"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Información sobre Roles -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Roles del Sistema
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Super Administrador:</strong> Control total del sistema</li>
                        <li><strong>Editor General:</strong> Gestión completa de contenidos</li>
                        <li><strong>Editor de Sección:</strong> Gestión de contenidos de su sección</li>
                        <li><strong>Redactor:</strong> Creación y edición de borradores</li>
                        <li><strong>Colaborador:</strong> Creación de borradores para revisión</li>
                        <li><strong>Administrador Técnico:</strong> Configuración técnica del sistema</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
