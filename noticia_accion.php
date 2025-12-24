<?php
/**
 * Acciones sobre Noticias (Vigencia y Suspensión)
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$noticiaModel = new Noticia();
$logModel = new Log();

// Obtener parámetros
$noticiaId = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

if (!$noticiaId || !$accion) {
    setFlash('error', 'Parámetros inválidos');
    redirect('noticias.php');
}

// Obtener noticia
$noticia = $noticiaModel->getById($noticiaId);

if (!$noticia) {
    setFlash('error', 'Noticia no encontrada');
    redirect('noticias.php');
}

// Verificar permisos
$currentUser = getCurrentUser();
if (!hasPermission('noticias.editar') && !hasPermission('all') && $noticia['autor_id'] != $currentUser['id']) {
    setFlash('error', 'No tienes permisos para realizar esta acción');
    redirect('noticias.php');
}

// Procesar acción
$nuevoEstado = null;
$mensaje = '';

switch ($accion) {
    case 'suspender':
        if ($noticia['estado'] === 'publicado') {
            $nuevoEstado = 'archivado';
            $mensaje = 'Noticia suspendida exitosamente';
        } else {
            setFlash('error', 'Solo se pueden suspender noticias publicadas');
            redirect('noticias.php');
        }
        break;
    
    case 'vigencia':
        if ($noticia['estado'] === 'archivado') {
            $nuevoEstado = 'publicado';
            $mensaje = 'Vigencia de noticia activada exitosamente';
        } else {
            setFlash('error', 'Solo se puede dar vigencia a noticias archivadas');
            redirect('noticias.php');
        }
        break;
    
    default:
        setFlash('error', 'Acción no válida');
        redirect('noticias.php');
}

// Actualizar estado
$result = $noticiaModel->changeStatus($noticiaId, $nuevoEstado, $currentUser['id']);

if ($result) {
    // Registrar auditoría
    $logModel->registrarAuditoria(
        $currentUser['id'],
        'noticias',
        $accion,
        'noticias',
        $noticiaId,
        ['estado' => $noticia['estado']],
        ['estado' => $nuevoEstado]
    );
    
    setFlash('success', $mensaje);
} else {
    setFlash('error', 'Error al realizar la acción');
}

redirect('noticias.php');
?>
