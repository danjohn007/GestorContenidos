<?php
/**
 * Página de Modo en Construcción
 */
require_once __DIR__ . '/config/bootstrap.php';

$configuracionModel = new Configuracion();
$configGeneral = $configuracionModel->getByGrupo('general');
$configDiseno = $configuracionModel->getByGrupo('diseno');

$nombreSitio = $configGeneral['nombre_sitio']['valor'] ?? 'Portal de Noticias Querétaro';
$logoSitio = $configGeneral['logo_sitio']['valor'] ?? null;
$mensajeConstruccion = $configGeneral['mensaje_construccion']['valor'] ?? 'Estamos mejorando para ti, disponibles muy pronto';
$contactoConstruccion = $configGeneral['contacto_construccion']['valor'] ?? 'Email: contacto@portalqueretaro.mx<br>Tel: 442-123-4567';

$colorPrimario = $configDiseno['color_primario']['valor'] ?? '#1e40af';
$colorSecundario = $configDiseno['color_secundario']['valor'] ?? '#3b82f6';
$colorAcento = $configDiseno['color_acento']['valor'] ?? '#10b981';
$fuentePrincipal = $configDiseno['fuente_principal']['valor'] ?? 'system-ui';
$fuenteTitulos = $configDiseno['fuente_titulos']['valor'] ?? 'system-ui';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>En Construcción - <?php echo e($nombreSitio); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --color-primario: <?php echo e($colorPrimario); ?>;
            --color-secundario: <?php echo e($colorSecundario); ?>;
            --color-acento: <?php echo e($colorAcento); ?>;
        }
        body {
            font-family: <?php echo e($fuentePrincipal); ?>;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: <?php echo e($fuenteTitulos); ?>;
        }
        .gradient-bg {
            background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
        }
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full text-center fade-in">
        <!-- Logo -->
        <div class="mb-8">
            <?php if ($logoSitio): ?>
                <img src="<?php echo e(BASE_URL . $logoSitio); ?>" alt="<?php echo e($nombreSitio); ?>" class="h-24 mx-auto mb-4">
            <?php else: ?>
                <div class="inline-block bg-white rounded-full p-6 mb-4 shadow-2xl">
                    <i class="fas fa-tools text-6xl" style="color: var(--color-primario);"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Título -->
        <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">
            <?php echo e($nombreSitio); ?>
        </h1>

        <!-- Mensaje Principal -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-8">
            <div class="pulse-animation mb-6">
                <i class="fas fa-hard-hat text-6xl" style="color: var(--color-primario);"></i>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--color-primario);">
                En Construcción
            </h2>
            <p class="text-xl text-gray-700 mb-6">
                <?php echo e($mensajeConstruccion); ?>
            </p>
            <div class="flex items-center justify-center space-x-2 text-gray-600">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Trabajando en mejoras...</span>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-white">
            <h3 class="text-2xl font-bold mb-4">
                <i class="fas fa-envelope mr-2"></i>
                Contáctanos
            </h3>
            <div class="text-lg space-y-2">
                <?php echo $contactoConstruccion; ?>
            </div>
        </div>

        <!-- Enlace de Acceso Administrativo -->
        <div class="mt-8">
            <a href="<?php echo url('login.php'); ?>" class="inline-flex items-center text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-lock mr-2"></i>
                <span class="text-sm">Acceso Administrativo</span>
            </a>
        </div>
    </div>
</body>
</html>
