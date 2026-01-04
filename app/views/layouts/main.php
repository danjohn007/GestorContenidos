<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Dashboard'); ?> - <?php echo SITE_NAME; ?></title>
    <?php
    // Cargar favicon si está configurado
    if (isAuthenticated()) {
        $configuracionModel = new Configuracion();
        $configGeneral = $configuracionModel->getByGrupo('general');
        $faviconSitio = $configGeneral['favicon_sitio']['valor'] ?? null;
        if ($faviconSitio):
            // Determinar tipo MIME según extensión
            $faviconExt = strtolower(pathinfo($faviconSitio, PATHINFO_EXTENSION));
            $faviconType = 'image/x-icon'; // default
            if ($faviconExt === 'png') {
                $faviconType = 'image/png';
            } elseif ($faviconExt === 'jpg' || $faviconExt === 'jpeg') {
                $faviconType = 'image/jpeg';
            } elseif ($faviconExt === 'svg') {
                $faviconType = 'image/svg+xml';
            }
    ?>
    <link rel="icon" type="<?php echo $faviconType; ?>" href="<?php echo e(BASE_URL . $faviconSitio); ?>">
    <link rel="shortcut icon" type="<?php echo $faviconType; ?>" href="<?php echo e(BASE_URL . $faviconSitio); ?>">
    <?php 
        endif;
    }
    ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS - Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <?php
    // Cargar configuración de diseño si está autenticado
    if (isAuthenticated()) {
        $configuracionModel = new Configuracion();
        $configGeneral = $configuracionModel->getByGrupo('general');
        $configDiseno = $configuracionModel->getByGrupo('diseno');
        
        $nombreSitio = $configGeneral['nombre_sitio']['valor'] ?? 'CMS Noticias';
        $logoSitio = $configGeneral['logo_sitio']['valor'] ?? null;
        $colorPrimario = $configDiseno['color_primario']['valor'] ?? '#1e40af';
        $colorSecundario = $configDiseno['color_secundario']['valor'] ?? '#3b82f6';
        $fuentePrincipal = $configDiseno['fuente_principal']['valor'] ?? 'system-ui';
        $fuenteTitulos = $configDiseno['fuente_titulos']['valor'] ?? 'system-ui';
    }
    ?>
    <style>
        <?php if (isAuthenticated()): ?>
        :root {
            --color-primary: <?php echo e($colorPrimario ?? '#1e40af'); ?>;
            --color-secondary: <?php echo e($colorSecundario ?? '#3b82f6'); ?>;
        }
        body {
            font-family: <?php echo e($fuentePrincipal ?? 'system-ui'); ?>;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: <?php echo e($fuenteTitulos ?? 'system-ui'); ?>;
        }
        /* Aplicar colores configurados al sidebar */
        .sidebar-bg {
            background-color: var(--color-primary);
        }
        .sidebar-header-bg {
            background: linear-gradient(to bottom, var(--color-primary), var(--color-secondary));
        }
        .sidebar-link:hover {
            background-color: rgba(0, 0, 0, 0.2);
        }
        <?php else: ?>
        :root {
            --color-primary: #1e40af;
            --color-secondary: #3b82f6;
        }
        /* Aplicar colores configurados al login */
        .login-gradient-bg {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        }
        .login-btn-primary {
            background-color: var(--color-primary);
        }
        .login-btn-primary:hover {
            background-color: var(--color-secondary);
        }
        .login-focus:focus {
            border-color: var(--color-primary);
            --tw-ring-color: var(--color-primary);
        }
        <?php endif; ?>
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?php echo isAuthenticated() ? ($colorPrimario ?? '#1e40af') : '#1e40af'; ?>',
                        secondary: '<?php echo isAuthenticated() ? ($colorSecundario ?? '#3b82f6') : '#3b82f6'; ?>',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    
    <?php if (isAuthenticated()): ?>
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 sidebar-bg text-white transform transition-transform duration-300 ease-in-out z-30" id="sidebar">
            <div class="flex items-center justify-center h-16 sidebar-header-bg">
                <?php if (!empty($logoSitio)): ?>
                <img src="<?php echo e(BASE_URL . $logoSitio); ?>" alt="<?php echo e($nombreSitio); ?>" class="h-10" loading="eager">
                <?php else: ?>
                <h1 class="text-xl font-bold"><?php echo e($nombreSitio ?? 'CMS Noticias'); ?></h1>
                <?php endif; ?>
            </div>
            
            <nav class="mt-8">
                <a href="<?php echo url('dashboard.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-home mr-3"></i>
                    Dashboard
                </a>
                
                <?php if (hasPermission('noticias') || hasPermission('all')): ?>
                <a href="<?php echo url('noticias.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-newspaper mr-3"></i>
                    Noticias
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('categorias') || hasPermission('all')): ?>
                <a href="<?php echo url('categorias.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-folder mr-3"></i>
                    Categorías
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('multimedia') || hasPermission('all')): ?>
                <a href="<?php echo url('multimedia.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-images mr-3"></i>
                    Multimedia
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('usuarios') || hasPermission('all')): ?>
                <a href="<?php echo url('usuarios.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-users mr-3"></i>
                    Usuarios
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('configuracion') || hasPermission('all')): ?>
                <a href="<?php echo url('pagina_inicio.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-home mr-3"></i>
                    Página de Inicio
                </a>
                <a href="<?php echo url('banners.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-ad mr-3"></i>
                    Banners
                </a>
                <a href="<?php echo url('configuracion.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-cog mr-3"></i>
                    Configuración
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('logs') || hasPermission('all')): ?>
                <a href="<?php echo url('logs.php'); ?>" class="flex items-center px-6 py-3 text-gray-200 hover:text-white sidebar-link">
                    <i class="fas fa-file-alt mr-3"></i>
                    Logs
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm h-16 fixed top-0 right-0 left-64 z-20">
                <div class="flex items-center justify-between h-full px-6">
                    <div class="flex items-center">
                        <button id="menuToggle" class="text-gray-500 focus:outline-none lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-user mr-2"></i>
                            <?php 
                            $user = getCurrentUser();
                            echo e($user['nombre'] . ' (' . $user['rol'] . ')'); 
                            ?>
                        </span>
                        <a href="<?php echo url('logout.php'); ?>" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-sign-out-alt mr-1"></i>
                            Salir
                        </a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="pt-20 px-6 pb-6">
                <?php
                // Mostrar mensajes flash
                $success = getFlash('success');
                $error = getFlash('error');
                $info = getFlash('info');
                ?>
                
                <?php if ($success): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo e($success); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo e($error); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($info): ?>
                <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo e($info); ?></span>
                </div>
                <?php endif; ?>
                
                <?php echo $content ?? ''; ?>
            </main>
        </div>
    <?php else: ?>
        <!-- Contenido sin autenticación -->
        <?php echo $content ?? ''; ?>
    <?php endif; ?>

    <script>
        // Toggle mobile menu
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
        
        // Inicializar AOS (Animate On Scroll)
        AOS.init({
            duration: 600,
            easing: 'ease-in-out',
            once: true,
            offset: 50
        });
    </script>
</body>
</html>
