<?php
/**
 * Test de Conexión a Base de Datos y URL Base
 * Este archivo verifica que la configuración esté correcta
 */

// Cargar configuración
require_once __DIR__ . '/config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <div>
                <h2 class="text-center text-3xl font-extrabold text-gray-900">
                    Test de Configuración del Sistema
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Verificación de conexión y configuración
                </p>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Configuración General -->
                <div class="px-6 py-4 bg-gray-800 text-white">
                    <h3 class="text-lg font-semibold">
                        <i class="fas fa-cog mr-2"></i>
                        Configuración General
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Nombre del Sitio:</span>
                        <span class="text-gray-900"><?php echo SITE_NAME; ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Versión:</span>
                        <span class="text-gray-900"><?php echo SITE_VERSION; ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Entorno:</span>
                        <span class="<?php echo ENVIRONMENT === 'development' ? 'text-yellow-600' : 'text-green-600'; ?> font-semibold">
                            <?php echo strtoupper(ENVIRONMENT); ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Zona Horaria:</span>
                        <span class="text-gray-900"><?php echo date_default_timezone_get(); ?></span>
                    </div>
                </div>

                <!-- URL Base -->
                <div class="px-6 py-4 bg-blue-600 text-white">
                    <h3 class="text-lg font-semibold">
                        <i class="fas fa-link mr-2"></i>
                        Configuración de URL
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <?php
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $script = $_SERVER['SCRIPT_NAME'];
                    $path = dirname($script);
                    if ($path === '/' || $path === '\\') {
                        $path = '';
                    }
                    $detectedBaseUrl = $protocol . '://' . $host . $path;
                    ?>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Protocolo:</span>
                        <span class="text-gray-900"><?php echo strtoupper($protocol); ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Host:</span>
                        <span class="text-gray-900"><?php echo $host; ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Ruta Base:</span>
                        <span class="text-gray-900"><?php echo $path ?: '/'; ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">URL Base Detectada:</span>
                        <span class="text-blue-600 font-mono text-sm"><?php echo $detectedBaseUrl; ?></span>
                    </div>
                </div>

                <!-- Test de Base de Datos -->
                <div class="px-6 py-4 bg-green-600 text-white">
                    <h3 class="text-lg font-semibold">
                        <i class="fas fa-database mr-2"></i>
                        Conexión a Base de Datos
                    </h3>
                </div>
                <div class="p-6">
                    <?php
                    try {
                        require_once __DIR__ . '/config/Database.php';
                        $db = Database::getInstance()->getConnection();
                        
                        // Test de conexión
                        $stmt = $db->query("SELECT VERSION() as version");
                        $result = $stmt->fetch();
                        
                        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">';
                        echo '<div class="flex items-center mb-2">';
                        echo '<i class="fas fa-check-circle text-2xl mr-3"></i>';
                        echo '<span class="font-bold text-lg">Conexión Exitosa</span>';
                        echo '</div>';
                        echo '<div class="ml-9 space-y-1">';
                        echo '<p><strong>Host:</strong> ' . DB_HOST . '</p>';
                        echo '<p><strong>Base de Datos:</strong> ' . DB_NAME . '</p>';
                        echo '<p><strong>Usuario:</strong> ' . DB_USER . '</p>';
                        echo '<p><strong>Versión MySQL:</strong> ' . $result['version'] . '</p>';
                        echo '</div>';
                        echo '</div>';
                        
                        // Verificar tablas
                        echo '<div class="mt-4">';
                        echo '<h4 class="font-semibold text-gray-700 mb-2">Verificación de Tablas:</h4>';
                        
                        $tablas = [
                            'usuarios', 'roles', 'categorias', 'noticias', 
                            'multimedia', 'logs_acceso', 'logs_auditoria', 'configuracion'
                        ];
                        
                        $tablasExistentes = [];
                        $tablasFaltantes = [];
                        
                        foreach ($tablas as $tabla) {
                            $stmt = $db->query("SHOW TABLES LIKE '$tabla'");
                            if ($stmt->rowCount() > 0) {
                                $tablasExistentes[] = $tabla;
                            } else {
                                $tablasFaltantes[] = $tabla;
                            }
                        }
                        
                        if (!empty($tablasExistentes)) {
                            echo '<div class="bg-green-50 p-3 rounded mb-2">';
                            echo '<p class="text-green-700 font-medium mb-1">✓ Tablas Encontradas (' . count($tablasExistentes) . '):</p>';
                            echo '<p class="text-sm text-green-600">' . implode(', ', $tablasExistentes) . '</p>';
                            echo '</div>';
                        }
                        
                        if (!empty($tablasFaltantes)) {
                            echo '<div class="bg-yellow-50 p-3 rounded">';
                            echo '<p class="text-yellow-700 font-medium mb-1">⚠ Tablas Faltantes (' . count($tablasFaltantes) . '):</p>';
                            echo '<p class="text-sm text-yellow-600">' . implode(', ', $tablasFaltantes) . '</p>';
                            echo '<p class="text-xs text-yellow-600 mt-2">Ejecuta el archivo database.sql para crear las tablas.</p>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        
                    } catch (PDOException $e) {
                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">';
                        echo '<div class="flex items-center mb-2">';
                        echo '<i class="fas fa-times-circle text-2xl mr-3"></i>';
                        echo '<span class="font-bold text-lg">Error de Conexión</span>';
                        echo '</div>';
                        echo '<div class="ml-9">';
                        echo '<p><strong>Mensaje:</strong> ' . $e->getMessage() . '</p>';
                        echo '<p class="mt-2 text-sm">Verifica la configuración en <code class="bg-red-200 px-1">config/config.php</code></p>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>

                <!-- Información de PHP -->
                <div class="px-6 py-4 bg-purple-600 text-white">
                    <h3 class="text-lg font-semibold">
                        <i class="fas fa-code mr-2"></i>
                        Información de PHP
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Versión PHP:</span>
                        <span class="text-gray-900"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Extensión PDO:</span>
                        <span class="<?php echo extension_loaded('pdo') ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo extension_loaded('pdo') ? '✓ Instalada' : '✗ No instalada'; ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Extensión PDO MySQL:</span>
                        <span class="<?php echo extension_loaded('pdo_mysql') ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo extension_loaded('pdo_mysql') ? '✓ Instalada' : '✗ No instalada'; ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Tamaño Máximo de Upload:</span>
                        <span class="text-gray-900"><?php echo ini_get('upload_max_filesize'); ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium text-gray-700">Tamaño Máximo POST:</span>
                        <span class="text-gray-900"><?php echo ini_get('post_max_size'); ?></span>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="px-6 py-4 bg-gray-100 flex justify-between">
                    <a href="<?php echo $detectedBaseUrl; ?>/login.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Ir al Login
                    </a>
                    <a href="<?php echo $detectedBaseUrl; ?>/test.php" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition-colors">
                        <i class="fas fa-redo mr-2"></i>
                        Recargar Test
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
