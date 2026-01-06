<?php
/**
 * Acciones de Categoría
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();

$categoriaModel = new Categoria();
$logModel = new Log();
$currentUser = getCurrentUser();

// Obtener acción
$accion = $_GET['accion'] ?? null;
$id = $_GET['id'] ?? null;

if (!$accion || !$id) {
    setFlash('error', 'Acción o ID no especificado');
    redirect('categorias.php');
}

// Obtener categoría
$categoria = $categoriaModel->getById($id);

if (!$categoria) {
    setFlash('error', 'Categoría no encontrada');
    redirect('categorias.php');
}

// Procesar según la acción
switch ($accion) {
    case 'toggle':
        // Cambiar visibilidad
        $nuevoEstado = $categoria['visible'] ? 0 : 1;
        $data = ['visible' => $nuevoEstado];
        
        if ($categoriaModel->update($id, $data)) {
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'categoria',
                'modificar',
                'categoria',
                $id,
                ['visible' => $categoria['visible']],
                ['visible' => $nuevoEstado]
            );
            
            $mensaje = $nuevoEstado ? 'Categoría mostrada exitosamente' : 'Categoría ocultada exitosamente';
            setFlash('success', $mensaje);
        } else {
            setFlash('error', 'Error al cambiar la visibilidad de la categoría');
        }
        
        redirect('categorias.php');
        break;
        
    case 'eliminar':
        // Verificar si tiene noticias asociadas
        $countNoticias = $categoriaModel->countNoticias($id);
        
        if ($countNoticias > 0) {
            setFlash('error', "No se puede eliminar la categoría porque tiene $countNoticias noticia(s) asociada(s). Primero elimina o reasigna las noticias.");
            redirect('categorias.php');
        }
        
        // Verificar si tiene subcategorías
        $subcategorias = $categoriaModel->getChildren($id);
        
        if (!empty($subcategorias)) {
            setFlash('error', 'No se puede eliminar la categoría porque tiene subcategorías. Primero elimina las subcategorías.');
            redirect('categorias.php');
        }
        
        // Eliminar categoría
        if ($categoriaModel->delete($id)) {
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'categoria',
                'eliminar',
                'categoria',
                $id,
                ['nombre' => $categoria['nombre']],
                null
            );
            
            setFlash('success', 'Categoría eliminada exitosamente');
        } else {
            setFlash('error', 'Error al eliminar la categoría');
        }
        
        redirect('categorias.php');
        break;
        
    case 'eliminar_subcategoria':
        // Verificar que es una subcategoría
        if (!$categoria['padre_id']) {
            setFlash('error', 'Esta no es una subcategoría');
            redirect('categorias.php');
        }
        
        // Verificar si tiene noticias asociadas
        $countNoticias = $categoriaModel->countNoticias($id);
        
        if ($countNoticias > 0) {
            // Si tiene noticias, usar eliminar con reasignación
            if ($categoriaModel->deleteSubcategoriaWithReassign($id)) {
                $logModel->registrarAuditoria(
                    $currentUser['id'],
                    'categoria',
                    'eliminar_subcategoria',
                    'categoria',
                    $id,
                    ['nombre' => $categoria['nombre'], 'noticias_reasignadas' => $countNoticias],
                    null
                );
                
                setFlash('success', "Subcategoría eliminada y $countNoticias noticia(s) reasignada(s) a la categoría padre");
            } else {
                setFlash('error', 'Error al eliminar la subcategoría');
            }
        } else {
            // Si no tiene noticias, eliminar directamente
            if ($categoriaModel->delete($id)) {
                $logModel->registrarAuditoria(
                    $currentUser['id'],
                    'categoria',
                    'eliminar_subcategoria',
                    'categoria',
                    $id,
                    ['nombre' => $categoria['nombre']],
                    null
                );
                
                setFlash('success', 'Subcategoría eliminada exitosamente');
            } else {
                setFlash('error', 'Error al eliminar la subcategoría');
            }
        }
        
        redirect('categorias.php');
        break;
        
    case 'desasociar':
        // Verificar que es una subcategoría
        if (!$categoria['padre_id']) {
            setFlash('error', 'Esta no es una subcategoría');
            redirect('categorias.php');
        }
        
        // Desasociar subcategoría
        if ($categoriaModel->desasociarSubcategoria($id)) {
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'categoria',
                'desasociar',
                'categoria',
                $id,
                ['padre_id' => $categoria['padre_id']],
                ['padre_id' => null]
            );
            
            setFlash('success', 'Subcategoría desasociada exitosamente. Ahora es una categoría principal.');
        } else {
            setFlash('error', 'Error al desasociar la subcategoría');
        }
        
        redirect('categorias.php');
        break;
        
    case 'mover':
        // Verificar que es una subcategoría
        if (!$categoria['padre_id']) {
            setFlash('error', 'Esta no es una subcategoría');
            redirect('categorias.php');
        }
        
        // Obtener nuevo padre del parámetro
        $nuevoPadreId = $_GET['nuevo_padre'] ?? null;
        
        if (!$nuevoPadreId) {
            setFlash('error', 'ID de nueva categoría padre no especificado');
            redirect('categorias.php');
        }
        
        // Verificar que el nuevo padre existe
        $nuevoPadre = $categoriaModel->getById($nuevoPadreId);
        
        if (!$nuevoPadre) {
            setFlash('error', 'Categoría padre no encontrada');
            redirect('categorias.php');
        }
        
        // Mover subcategoría
        if ($categoriaModel->moverSubcategoria($id, $nuevoPadreId)) {
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'categoria',
                'mover',
                'categoria',
                $id,
                ['padre_id' => $categoria['padre_id']],
                ['padre_id' => $nuevoPadreId]
            );
            
            setFlash('success', "Subcategoría movida exitosamente a '{$nuevoPadre['nombre']}'");
        } else {
            setFlash('error', 'Error al mover la subcategoría');
        }
        
        redirect('categorias.php');
        break;
        
    default:
        setFlash('error', 'Acción no válida');
        redirect('categorias.php');
}
?>
