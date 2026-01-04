<?php
/**
 * Editar Usuario
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('usuarios');

$usuarioModel = new Usuario();
$errors = [];
$success = false;

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

// No permitir editar usuarios de Super Administrador si el usuario actual no es Super Admin
$currentUser = getCurrentUser();
if ($usuario['rol_id'] == 1 && $currentUser['rol_id'] != 1) {
    setFlash('error', 'No tienes permisos para editar este usuario');
    redirect('usuarios.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $rol_id = $_POST['rol_id'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre)) {
        $errors[] = 'El nombre es requerido';
    }
    if (empty($apellidos)) {
        $errors[] = 'Los apellidos son requeridos';
    }
    if (empty($email)) {
        $errors[] = 'El email es requerido';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    } else {
        // Verificar si el email ya existe (para otro usuario)
        $existingUser = $usuarioModel->getByEmail($email);
        if ($existingUser && $existingUser['id'] != $usuarioId) {
            $errors[] = 'El email ya está registrado para otro usuario';
        }
    }
    
    // Validar contraseña solo si se proporciona
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        if ($password !== $password_confirm) {
            $errors[] = 'Las contraseñas no coinciden';
        }
    }
    
    if (empty($rol_id)) {
        $errors[] = 'Debe seleccionar un rol';
    }
    
    // Si no hay errores, actualizar usuario
    if (empty($errors)) {
        $data = [
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'rol_id' => $rol_id,
            'activo' => $activo
        ];
        
        // Solo actualizar contraseña si se proporcionó
        if (!empty($password)) {
            $data['password'] = $password;
        }
        
        if ($usuarioModel->update($usuarioId, $data)) {
            setFlash('success', 'Usuario actualizado exitosamente');
            redirect('usuarios.php');
        } else {
            $errors[] = 'Error al actualizar el usuario. Intente nuevamente.';
        }
    }
}

$title = 'Editar Usuario';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-user-edit mr-2 text-indigo-600"></i>
            Editar Usuario
        </h1>
        <a href="<?php echo url('usuarios.php'); ?>" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver al listado
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    Se encontraron los siguientes errores:
                </h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" required value="<?php echo e($usuario['nombre']); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Apellidos <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="apellidos" required value="<?php echo e($usuario['apellidos']); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" required value="<?php echo e($usuario['email']); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="border-t border-gray-200 pt-4">
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    Deja los campos de contraseña vacíos si no deseas cambiarla
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nueva Contraseña
                        </label>
                        <input type="password" name="password" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="Dejar vacío para mantener la actual">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Nueva Contraseña
                        </label>
                        <input type="password" name="password_confirm" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="Confirma la nueva contraseña">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Rol <span class="text-red-500">*</span>
                </label>
                <select name="rol_id" required 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Selecciona un rol</option>
                    <option value="1" <?php echo $usuario['rol_id'] == '1' ? 'selected' : ''; ?>>Super Administrador</option>
                    <option value="2" <?php echo $usuario['rol_id'] == '2' ? 'selected' : ''; ?>>Editor General</option>
                    <option value="3" <?php echo $usuario['rol_id'] == '3' ? 'selected' : ''; ?>>Editor de Sección</option>
                    <option value="4" <?php echo $usuario['rol_id'] == '4' ? 'selected' : ''; ?>>Redactor</option>
                    <option value="5" <?php echo $usuario['rol_id'] == '5' ? 'selected' : ''; ?>>Colaborador</option>
                    <option value="6" <?php echo $usuario['rol_id'] == '6' ? 'selected' : ''; ?>>Administrador Técnico</option>
                </select>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="activo" id="activo" value="1" <?php echo $usuario['activo'] ? 'checked' : ''; ?>
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="activo" class="ml-2 block text-sm text-gray-900">
                    Usuario activo
                </label>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('usuarios.php'); ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
