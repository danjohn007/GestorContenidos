<?php
/**
 * Publicador Automático de Noticias Programadas
 * Este script debe ejecutarse periódicamente mediante cron o similar
 * 
 * Ejemplo de configuración cron (cada 15 minutos):
 * */15 * * * * /usr/bin/php /ruta/al/proyecto/publicar_programadas.php >> /var/log/publicador.log 2>&1
 */

require_once __DIR__ . '/config/bootstrap.php';

// Verificar que se ejecute solo desde CLI o con autenticación
$isCLI = php_sapi_name() === 'cli';
$isAuthenticated = isAuthenticated();

if (!$isCLI && !$isAuthenticated) {
    die('Acceso no autorizado');
}

// Obtener fecha/hora actual
$ahora = date('Y-m-d H:i:s');

echo "\n========================================\n";
echo "Publicador Automático de Noticias\n";
echo "Ejecutado: $ahora\n";
echo "========================================\n\n";

try {
    $noticiaModel = new Noticia();
    $logModel = new Log();
    $db = Database::getInstance()->getConnection();
    
    // Buscar noticias programadas para publicar
    $query = "SELECT * FROM noticias 
              WHERE estado = 'publicado'
              AND fecha_programada IS NOT NULL 
              AND fecha_programada <= :ahora
              AND fecha_publicacion IS NULL";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['ahora' => $ahora]);
    $noticiasProgramadas = $stmt->fetchAll();
    
    $contador = 0;
    
    if (empty($noticiasProgramadas)) {
        echo "✓ No hay noticias programadas para publicar en este momento.\n\n";
    } else {
        echo "📰 Encontradas " . count($noticiasProgramadas) . " noticia(s) programada(s) para publicar:\n\n";
        
        foreach ($noticiasProgramadas as $noticia) {
            echo "  • ID: {$noticia['id']}\n";
            echo "    Título: {$noticia['titulo']}\n";
            echo "    Programada: {$noticia['fecha_programada']}\n";
            
            // Actualizar fecha de publicación
            $updateQuery = "UPDATE noticias 
                           SET fecha_publicacion = :fecha_publicacion
                           WHERE id = :id";
            
            $updateStmt = $db->prepare($updateQuery);
            $result = $updateStmt->execute([
                'id' => $noticia['id'],
                'fecha_publicacion' => $ahora
            ]);
            
            if ($result) {
                echo "    ✅ Publicada exitosamente\n\n";
                
                // Registrar en el log
                if (!$isCLI) {
                    $currentUser = getCurrentUser();
                    $userId = $currentUser['id'] ?? null;
                } else {
                    // Si se ejecuta desde CLI, usar ID 1 (sistema) o crear usuario sistema
                    $userId = 1;
                }
                
                $logModel->registrarAuditoria(
                    $userId,
                    'noticias',
                    'auto_publicar',
                    'noticias',
                    $noticia['id'],
                    null,
                    ['fecha_programada' => $noticia['fecha_programada'], 'fecha_publicacion' => $ahora]
                );
                
                $contador++;
            } else {
                echo "    ❌ Error al publicar\n\n";
            }
        }
        
        echo "========================================\n";
        echo "Resumen: $contador noticia(s) publicada(s) de " . count($noticiasProgramadas) . " programada(s)\n";
        echo "========================================\n\n";
    }
    
    // Mostrar próximas publicaciones programadas
    $proximasQuery = "SELECT id, titulo, fecha_programada 
                     FROM noticias 
                     WHERE estado = 'publicado'
                     AND fecha_programada > :ahora
                     AND fecha_publicacion IS NULL
                     ORDER BY fecha_programada ASC
                     LIMIT 5";
    
    $proximasStmt = $db->prepare($proximasQuery);
    $proximasStmt->execute(['ahora' => $ahora]);
    $proximas = $proximasStmt->fetchAll();
    
    if (!empty($proximas)) {
        echo "📅 Próximas publicaciones programadas:\n\n";
        foreach ($proximas as $proxima) {
            echo "  • [{$proxima['fecha_programada']}] {$proxima['titulo']}\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    error_log("Error en publicador automático: " . $e->getMessage());
}

// Si no es CLI, mostrar interfaz HTML
if (!$isCLI && $isAuthenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Publicador Automático</title>
        <meta http-equiv="refresh" content="3;url=<?php echo url('noticias.php'); ?>">
        <style>
            body { font-family: system-ui; padding: 2rem; max-width: 800px; margin: 0 auto; }
            h1 { color: #1e40af; }
            pre { background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; }
        </style>
    </head>
    <body>
        <h1>✅ Publicador Automático Ejecutado</h1>
        <p>Redirigiendo al listado de noticias...</p>
        <p><a href="<?php echo url('noticias.php'); ?>">Ir ahora</a></p>
    </body>
    </html>
    <?php
}
?>
