<?php
/**
 * Acciones de Página de Inicio
 * Manejo de creación y eliminación de elementos
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$paginaInicioModel = new PaginaInicio();
$logModel = new Log();
$currentUser = getCurrentUser();

// Obtener acción
$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;

if (!$accion) {
    setFlash('error', 'Acción no especificada');
    redirect('pagina_inicio.php');
}

// Procesar según la acción
switch ($accion) {
    case 'crear_slider':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('pagina_inicio.php');
        }
        
        $titulo = trim($_POST['titulo'] ?? '');
        $subtitulo = trim($_POST['subtitulo'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $orden = (int)($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if (empty($titulo)) {
            setFlash('error', 'El título es requerido');
            redirect('pagina_inicio.php');
        }
        
        // Manejar imagen si se sube
        $imagen_url = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/public/uploads/homepage/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Validar tamaño (máximo 5MB)
            $maxFileSize = 5 * 1024 * 1024;
            if ($_FILES['imagen']['size'] <= $maxFileSize) {
                $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($extension, $allowedExtensions)) {
                    $filename = uniqid('slider_') . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                        $imagen_url = '/public/uploads/homepage/' . $filename;
                    }
                }
            }
        }
        
        $data = [
            'seccion' => 'slider',
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'contenido' => $contenido,
            'imagen' => $imagen_url,
            'orden' => $orden,
            'activo' => $activo
        ];
        
        if ($paginaInicioModel->create($data)) {
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'pagina_inicio',
                'crear',
                'pagina_inicio',
                null,
                null,
                $data
            );
            
            setFlash('success', 'Elemento del slider creado exitosamente');
        } else {
            setFlash('error', 'Error al crear el elemento del slider');
        }
        
        redirect('pagina_inicio.php');
        break;
        
    case 'eliminar':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            setFlash('error', 'ID no especificado');
            redirect('pagina_inicio.php');
        }
        
        $elemento = $paginaInicioModel->getById($id);
        if (!$elemento) {
            setFlash('error', 'Elemento no encontrado');
            redirect('pagina_inicio.php');
        }
        
        if ($paginaInicioModel->delete($id)) {
            $logModel->registrarAuditoria(
                $currentUser['id'],
                'pagina_inicio',
                'eliminar',
                'pagina_inicio',
                $id,
                ['titulo' => $elemento['titulo']],
                null
            );
            
            setFlash('success', 'Elemento eliminado exitosamente');
        } else {
            setFlash('error', 'Error al eliminar el elemento');
        }
        
        redirect('pagina_inicio.php');
        break;
        
    default:
        setFlash('error', 'Acción no válida');
        redirect('pagina_inicio.php');
}
?>
