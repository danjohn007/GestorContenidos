<?php
/**
 * Eliminar Usuario
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('usuarios');

$usuarioModel = new Usuario();

// Obtener ID del usuario
$usuarioId = $_GET['id'] ?? null;

if (!$usuarioId) {
    setFlash('error', 'ID de usuario no especificado');
    redirect('usuarios.php');
}

// Obtener datos del usuario
$usuario = $usuarioModel->getById($usuarioId);

if (!$usuario) {
    setFlash('error', 'Usuario no encontrado');
    redirect('usuarios.php');
}

// Evitar eliminar al usuario actual
$currentUser = getCurrentUser();
if ($usuario['id'] == $currentUser['id']) {
    setFlash('error', 'No puedes eliminar tu propia cuenta');
    redirect('usuarios.php');
}

// No permitir eliminar usuarios de Super Administrador si el usuario actual no es Super Admin
if ($usuario['rol_id'] == 1 && $currentUser['rol_id'] != 1) {
    setFlash('error', 'No tienes permisos para eliminar este usuario');
    redirect('usuarios.php');
}

// Confirmación
if (!isset($_GET['confirm']) || $_GET['confirm'] !== '1') {
    $title = 'Confirmar Eliminación';
    ob_start();
    ?>
    
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-times mr-2 text-red-600"></i>
                Confirmar Eliminación de Usuario
            </h1>
            <a href="<?php echo url('usuarios.php'); ?>" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al listado
            </a>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Advertencia
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Esta acción no se puede deshacer. Estás a punto de eliminar permanentemente al usuario:</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Información del Usuario</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($usuario['email']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Rol</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($usuario['rol_nombre']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            <?php if ($usuario['activo']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Activo
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Inactivo
                            </span>
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>
            
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('usuarios.php'); ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <a href="<?php echo url('usuario_eliminar.php?id=' . $usuarioId . '&confirm=1'); ?>" 
                   class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>
                    Confirmar Eliminación
                </a>
            </div>
        </div>
    </div>
    
    <?php
    $content = ob_get_clean();
    include __DIR__ . '/app/views/layouts/main.php';
    exit;
}

// Procesar eliminación confirmada
if ($usuarioModel->delete($usuarioId)) {
    // Registrar auditoría
    $logModel = new Log();
    $logModel->registrarAuditoria(
        $currentUser['id'],
        'usuario',
        'eliminar',
        'usuario',
        $usuarioId,
        ['nombre' => $usuario['nombre'], 'email' => $usuario['email']],
        null
    );
    
    setFlash('success', 'Usuario eliminado exitosamente');
} else {
    setFlash('error', 'Error al eliminar el usuario');
}

redirect('usuarios.php');
?>
