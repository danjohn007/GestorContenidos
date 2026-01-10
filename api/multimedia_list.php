<?php
/**
 * API endpoint to list multimedia files
 */
require_once __DIR__ . '/../config/bootstrap.php';
requireAuth();

header('Content-Type: application/json');

$multimediaModel = new Multimedia();

// Get filters from query parameters
$tipo = $_GET['tipo'] ?? 'imagen'; // Default to images
$carpeta = $_GET['carpeta'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 20;

try {
    $archivos = $multimediaModel->getAll($tipo, $carpeta, $page, $perPage);
    $total = $multimediaModel->count($tipo, $carpeta);
    
    // Format response
    $response = [
        'success' => true,
        'data' => $archivos,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener archivos multimedia'
    ]);
}
?>
