<?php
/**
 * Acciones sobre Banners
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$bannerModel = new Banner();
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? null;

if (!$id) {
    setFlash('error', 'ID de banner no especificado');
    redirect('banners.php');
}

switch ($accion) {
    case 'eliminar':
        if (hasPermission('configuracion') || hasPermission('all')) {
            $banner = $bannerModel->getById($id);
            if ($banner) {
                if ($bannerModel->delete($id)) {
                    setFlash('success', 'Banner eliminado exitosamente');
                } else {
                    setFlash('error', 'Error al eliminar el banner');
                }
            } else {
                setFlash('error', 'Banner no encontrado');
            }
        } else {
            setFlash('error', 'No tienes permisos para eliminar banners');
        }
        break;
        
    case 'toggle':
        if (hasPermission('configuracion') || hasPermission('all')) {
            if ($bannerModel->toggleActivo($id)) {
                setFlash('success', 'Estado del banner actualizado');
            } else {
                setFlash('error', 'Error al actualizar el estado del banner');
            }
        } else {
            setFlash('error', 'No tienes permisos para modificar banners');
        }
        break;
        
    default:
        setFlash('error', 'Acción no válida');
        break;
}

redirect('banners.php');
?>
