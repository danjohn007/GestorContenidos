<?php
/**
 * Página de Aviso Legal
 */
require_once __DIR__ . '/config/bootstrap.php';

// Verificar modo en construcción
$configuracionModel = new Configuracion();
$modoConstruccion = $configuracionModel->get('modo_construccion', '0');
if ($modoConstruccion === '1' && !isAuthenticated()) {
    include __DIR__ . '/construccion.php';
    exit;
}

// Obtener configuración del sitio
$configGeneral = $configuracionModel->getByGrupo('general');
$configDiseno = $configuracionModel->getByGrupo('diseno');

// Valores de configuración
$nombreSitio = $configGeneral['nombre_sitio']['valor'] ?? 'Portal de Noticias';
$logoSitio = $configGeneral['logo_sitio']['valor'] ?? null;
$modoLogo = $configGeneral['modo_logo']['valor'] ?? 'imagen';
$tamanoLogo = $configGeneral['tamano_logo']['valor'] ?? 'h-10';
$colorPrimario = $configDiseno['color_primario']['valor'] ?? '#1e40af';
$colorSecundario = $configDiseno['color_secundario']['valor'] ?? '#3b82f6';
$fuentePrincipal = $configDiseno['fuente_principal']['valor'] ?? 'system-ui';
$fuenteTitulos = $configDiseno['fuente_titulos']['valor'] ?? 'system-ui';
$avisoLegal = $configGeneral['aviso_legal']['valor'] ?? '';
$textoFooter = $configGeneral['texto_footer']['valor'] ?? '&copy; ' . date('Y') . ' ' . $nombreSitio . '. Todos los derechos reservados.';

// Si no hay contenido, redirigir a inicio
if (empty($avisoLegal)) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso Legal - <?php echo e($nombreSitio); ?></title>
    <?php 
    // Cargar favicon si está configurado
    $faviconSitio = $configGeneral['favicon_sitio']['valor'] ?? null;
    if ($faviconSitio): 
        $faviconExt = strtolower(pathinfo($faviconSitio, PATHINFO_EXTENSION));
        $faviconType = 'image/x-icon';
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
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --color-primario: <?php echo e($colorPrimario); ?>;
            --color-secundario: <?php echo e($colorSecundario); ?>;
        }
        body {
            font-family: <?php echo e($fuentePrincipal); ?>;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: <?php echo e($fuenteTitulos); ?>;
        }
        .header-bg {
            background: linear-gradient(to right, var(--color-primario), var(--color-secundario));
        }
        .footer-bg {
            background: linear-gradient(to right, var(--color-primario), var(--color-secundario));
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="header-bg text-white shadow-md">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <?php if ($modoLogo === 'imagen' && $logoSitio): ?>
                    <a href="<?php echo url('index.php'); ?>">
                        <img src="<?php echo e(BASE_URL . $logoSitio); ?>" alt="<?php echo e($nombreSitio); ?>" class="<?php echo e($tamanoLogo); ?>">
                    </a>
                    <?php elseif ($modoLogo === 'texto' || !$logoSitio): ?>
                    <i class="fas fa-newspaper text-3xl"></i>
                    <h1 class="text-2xl font-bold">
                        <a href="<?php echo url('index.php'); ?>"><?php echo e($nombreSitio); ?></a>
                    </h1>
                    <?php endif; ?>
                </div>
                <a href="<?php echo url('index.php'); ?>" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Volver al Inicio
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <article class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-6">
                <i class="fas fa-file-contract mr-3" style="color: var(--color-primario);"></i>
                Aviso Legal
            </h1>
            
            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                <?php echo nl2br(sanitizeSimpleHtml($avisoLegal)); ?>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-calendar mr-2"></i>
                    Última actualización: <?php echo date('d/m/Y'); ?>
                </p>
            </div>
        </article>
    </main>

    <!-- Footer -->
    <footer class="footer-bg text-white mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <div class="mb-4">
                    <?php echo nl2br(e($textoFooter)); ?>
                </div>
                <div class="mt-2">
                    <a href="<?php echo url('index.php'); ?>" class="text-white hover:text-blue-200 underline text-sm">
                        Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
