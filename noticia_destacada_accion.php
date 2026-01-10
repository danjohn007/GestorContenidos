<?php
/**
 * Acciones sobre Noticias Destacadas
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$noticiaDestacadaImagenModel = new NoticiaDestacadaImagen();
$logModel = new Log();

// Obtener parámetros
$id = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

if (!$id || !$accion) {
    setFlash('error', 'Parámetros inválidos');
    redirect('noticias_destacadas.php');
}

// Obtener noticia destacada
$destacada = $noticiaDestacadaImagenModel->getById($id);

if (!$destacada) {
    setFlash('error', 'Noticia destacada no encontrada');
    redirect('noticias_destacadas.php');
}

// Verificar permisos
if (!hasPermission('noticias') && !hasPermission('all')) {
    setFlash('error', 'No tienes permisos para realizar esta acción');
    redirect('noticias_destacadas.php');
}

// Procesar acción
$currentUser = getCurrentUser();

switch ($accion) {
    case 'toggle':
        // Cambiar estado activo/inactivo
        $result = $noticiaDestacadaImagenModel->toggle($id);
        
        if ($result) {
            $nuevoEstado = $destacada['activo'] ? 0 : 1;
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'noticia_destacada',
                'toggle',
                'noticias_destacadas_imagenes',
                $id,
                ['activo' => $destacada['activo']],
                ['activo' => $nuevoEstado]
            );
            
            $mensaje = $nuevoEstado ? 'Noticia destacada activada' : 'Noticia destacada desactivada';
            setFlash('success', $mensaje);
        } else {
            setFlash('error', 'Error al cambiar el estado');
        }
        break;
        
    case 'eliminar':
        // Eliminar imagen si no es de una noticia
        if (!$destacada['noticia_id'] && $destacada['imagen_url']) {
            $imagePath = __DIR__ . $destacada['imagen_url'];
            if (file_exists($imagePath) && strpos(realpath($imagePath), realpath(__DIR__)) === 0) {
                unlink($imagePath);
            }
        }
        
        // Eliminar registro
        $result = $noticiaDestacadaImagenModel->delete($id);
        
        if ($result) {
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'noticia_destacada',
                'eliminar',
                'noticias_destacadas_imagenes',
                $id,
                ['titulo' => $destacada['titulo']],
                null
            );
            
            setFlash('success', 'Noticia destacada eliminada exitosamente');
        } else {
            setFlash('error', 'Error al eliminar la noticia destacada');
        }
        break;
        
    default:
        setFlash('error', 'Acción no válida');
}

redirect('noticias_destacadas.php');
?>
