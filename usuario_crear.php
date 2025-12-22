<?php
/**
 * Crear Usuario
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('usuarios');

$usuarioModel = new Usuario();
$errors = [];
$success = false;

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
        // Verificar si el email ya existe
        $existingUser = $usuarioModel->getByEmail($email);
        if ($existingUser) {
            $errors[] = 'El email ya está registrado';
        }
    }
    if (empty($password)) {
        $errors[] = 'La contraseña es requerida';
    } elseif (strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    if (empty($rol_id)) {
        $errors[] = 'Debe seleccionar un rol';
    }
    
    // Si no hay errores, crear usuario
    if (empty($errors)) {
        $currentUser = getCurrentUser();
        $data = [
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'password' => $password,
            'rol_id' => $rol_id,
            'activo' => $activo,
            'creado_por' => $currentUser['id']
        ];
        
        if ($usuarioModel->create($data)) {
            setFlash('success', 'Usuario creado exitosamente');
            redirect('usuarios.php');
        } else {
            $errors[] = 'Error al crear el usuario. Intente nuevamente.';
        }
    }
}

$title = 'Crear Usuario';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-user-plus mr-2 text-indigo-600"></i>
            Crear Nuevo Usuario
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
                    <input type="text" name="nombre" required value="<?php echo e($_POST['nombre'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Apellidos <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="apellidos" required value="<?php echo e($_POST['apellidos'] ?? ''); ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirm" required 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Rol <span class="text-red-500">*</span>
                </label>
                <select name="rol_id" required 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Selecciona un rol</option>
                    <option value="1" <?php echo ($_POST['rol_id'] ?? '') == '1' ? 'selected' : ''; ?>>Super Administrador</option>
                    <option value="2" <?php echo ($_POST['rol_id'] ?? '') == '2' ? 'selected' : ''; ?>>Editor General</option>
                    <option value="3" <?php echo ($_POST['rol_id'] ?? '') == '3' ? 'selected' : ''; ?>>Editor de Sección</option>
                    <option value="4" <?php echo ($_POST['rol_id'] ?? '') == '4' ? 'selected' : ''; ?>>Redactor</option>
                    <option value="5" <?php echo ($_POST['rol_id'] ?? '') == '5' ? 'selected' : ''; ?>>Colaborador</option>
                    <option value="6" <?php echo ($_POST['rol_id'] ?? '') == '6' ? 'selected' : ''; ?>>Administrador Técnico</option>
                </select>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="activo" id="activo" value="1" <?php echo (empty($_POST) || (isset($_POST['activo']) && $_POST['activo'])) ? 'checked' : ''; ?>
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
                    Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
