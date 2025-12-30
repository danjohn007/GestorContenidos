<?php
/**
 * Página de Login
 */
require_once __DIR__ . '/config/bootstrap.php';

// Si ya está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect('index.php');
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        setFlash('error', 'Por favor, ingresa tu email y contraseña');
    } else {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->login($email, $password);
        
        if ($usuario) {
            // Establecer sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellidos'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol_nombre'];
            $_SESSION['usuario_rol_id'] = $usuario['rol_id'];
            $_SESSION['usuario_permisos'] = $usuario['permisos'];
            
            // Registrar acceso exitoso
            $usuarioModel->logAccess($usuario['id'], $email, 'login', 1, 'Acceso exitoso');
            
            setFlash('success', '¡Bienvenido! Has iniciado sesión correctamente');
            redirect('index.php');
        } else {
            // Registrar intento fallido
            $usuarioModel->logAccess(null, $email, 'login', 0, 'Credenciales incorrectas');
            setFlash('error', 'Email o contraseña incorrectos');
        }
    }
}

$title = 'Iniciar Sesión';

// Get configuration for login styles (without authentication)
// Note: Loading config on each login page load is acceptable since:
// 1. Login page is accessed infrequently (only during login attempts)
// 2. Ensures colors are always up-to-date without caching issues
// 3. Query is simple and fast (single SELECT by group)
$configuracionModel = new Configuracion();
$configDiseno = $configuracionModel->getByGrupo('diseno');
$colorPrimario = $configDiseno['color_primario']['valor'] ?? '#1e40af';
$colorSecundario = $configDiseno['color_secundario']['valor'] ?? '#3b82f6';

ob_start();
?>

<style>
    .login-gradient-bg {
        background: linear-gradient(135deg, <?php echo e($colorPrimario); ?>, <?php echo e($colorSecundario); ?>);
    }
    .login-btn-primary {
        background-color: <?php echo e($colorPrimario); ?>;
    }
    .login-btn-primary:hover {
        background-color: <?php echo e($colorSecundario); ?>;
    }
    .login-focus:focus {
        border-color: <?php echo e($colorPrimario); ?> !important;
        --tw-ring-color: <?php echo e($colorPrimario); ?> !important;
    }
    .login-checkbox:checked {
        background-color: <?php echo e($colorPrimario); ?>;
    }
</style>

<div class="min-h-screen flex items-center justify-center login-gradient-bg py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900">
                Sistema de Gestión de Contenidos
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Portal de Noticias Querétaro
            </p>
        </div>
        
        <?php
        $error = getFlash('error');
        if ($error):
        ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo e($error); ?></span>
        </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" method="POST" action="">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        required 
                        class="login-focus appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:z-10 sm:text-sm"
                        placeholder="correo@ejemplo.com"
                        value="<?php echo e($_POST['email'] ?? ''); ?>"
                    >
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required 
                        class="login-focus appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:z-10 sm:text-sm"
                        placeholder="••••••••"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        id="remember" 
                        name="remember" 
                        type="checkbox" 
                        class="login-checkbox h-4 w-4 focus:ring-2 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Recordarme
                    </label>
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="login-btn-primary group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-lock text-white opacity-75 group-hover:opacity-100"></i>
                    </span>
                    Iniciar Sesión
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <p class="text-sm text-gray-600">
                <strong>Usuario por defecto:</strong><br>
                Email: admin@gestorcontenidos.mx<br>
                Contraseña: admin123
            </p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
