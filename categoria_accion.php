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
        
    default:
        setFlash('error', 'Acción no válida');
        redirect('categorias.php');
}
?>
