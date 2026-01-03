<?php
/**
 * Eliminar Noticia
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$noticiaModel = new Noticia();
$logModel = new Log();

// Obtener ID de la noticia
$noticiaId = $_GET['id'] ?? null;

if (!$noticiaId) {
    setFlash('error', 'ID de noticia no especificado');
    redirect('noticias.php');
}

// Obtener datos de la noticia
$noticia = $noticiaModel->getById($noticiaId);

if (!$noticia) {
    setFlash('error', 'Noticia no encontrada');
    redirect('noticias.php');
}

// Verificar permisos
$currentUser = getCurrentUser();
if (!hasPermission('noticias.eliminar') && !hasPermission('all') && $noticia['autor_id'] != $currentUser['id']) {
    setFlash('error', 'No tienes permisos para eliminar esta noticia');
    redirect('noticias.php');
}

// Eliminar la noticia
$result = $noticiaModel->delete($noticiaId);

if ($result) {
    // Registrar auditoría
    $logModel->registrarAuditoria(
        $currentUser['id'],
        'noticias',
        'eliminar',
        'noticias',
        $noticiaId,
        ['titulo' => $noticia['titulo']],
        null
    );
    
    setFlash('success', 'Noticia eliminada exitosamente');
} else {
    setFlash('error', 'Error al eliminar la noticia');
}

redirect('noticias.php');
?>
