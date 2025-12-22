<?php
/**
 * Script de Instalación de Actualizaciones
 * Ejecuta las actualizaciones de base de datos necesarias
 */

require_once __DIR__ . '/config/bootstrap.php';

// Solo permitir ejecución desde localhost o con parámetro de seguridad
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
$hasValidSecret = isset($_GET['secret']) && $_GET['secret'] === 'install123';

if (!$isLocalhost && !$hasValidSecret) {
    die('Acceso no autorizado');
}

$db = Database::getInstance()->getConnection();
$errors = [];
$success = [];

try {
    // Leer el archivo SQL de actualizaciones
    $sqlFile = __DIR__ . '/database_updates.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception('Archivo database_updates.sql no encontrado');
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Dividir por declaraciones (cada sentencia termina con ;)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Instalación de Actualizaciones</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-gray-100'>
        <div class='container mx-auto px-4 py-8 max-w-4xl'>
            <div class='bg-white rounded-lg shadow-lg p-8'>
                <h1 class='text-3xl font-bold text-gray-900 mb-6'>
                    <i class='fas fa-database mr-2 text-blue-600'></i>
                    Instalación de Actualizaciones de Base de Datos
                </h1>";
    
    foreach ($statements as $index => $statement) {
        try {
            $db->exec($statement);
            $success[] = "Declaración " . ($index + 1) . " ejecutada exitosamente";
            echo "<div class='bg-green-50 border border-green-200 rounded-lg p-3 mb-2'>
                    <i class='fas fa-check-circle text-green-500 mr-2'></i>
                    Declaración " . ($index + 1) . " ejecutada exitosamente
                  </div>";
        } catch (PDOException $e) {
            // Ignorar errores de columnas o tablas que ya existen
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), 'already exists') !== false) {
                echo "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2'>
                        <i class='fas fa-exclamation-triangle text-yellow-500 mr-2'></i>
                        Declaración " . ($index + 1) . " - Ya existe (ignorado)
                      </div>";
            } else {
                $errors[] = "Declaración " . ($index + 1) . ": " . $e->getMessage();
                echo "<div class='bg-red-50 border border-red-200 rounded-lg p-3 mb-2'>
                        <i class='fas fa-times-circle text-red-500 mr-2'></i>
                        Error en declaración " . ($index + 1) . ": " . htmlspecialchars($e->getMessage()) . "
                      </div>";
            }
        }
    }
    
    echo "<div class='mt-8 p-6 bg-blue-50 border border-blue-200 rounded-lg'>
            <h2 class='text-xl font-bold text-blue-900 mb-3'>Resumen</h2>
            <p class='text-blue-800'>
                <strong>" . count($success) . "</strong> declaraciones ejecutadas exitosamente<br>
                <strong>" . count($errors) . "</strong> errores encontrados
            </p>
          </div>";
    
    if (empty($errors)) {
        echo "<div class='mt-6 text-center'>
                <a href='" . url('dashboard.php') . "' class='inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors'>
                    <i class='fas fa-arrow-right mr-2'></i>
                    Ir al Dashboard
                </a>
              </div>";
    }
    
    echo "    </div>
        </div>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    </body>
    </html>";
    
} catch (Exception $e) {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
            <h3 class='text-red-800 font-bold mb-2'>Error Fatal</h3>
            <p class='text-red-700'>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}
?>
