<?php
/**
 * API para tracking de banners (impresiones y clics)
 */
require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$bannerId = $_GET['id'] ?? null;

if (!$bannerId) {
    echo json_encode(['success' => false, 'message' => 'ID no especificado']);
    exit;
}

$bannerModel = new Banner();
$banner = $bannerModel->getById($bannerId);

if (!$banner) {
    echo json_encode(['success' => false, 'message' => 'Banner no encontrado']);
    exit;
}

switch ($action) {
    case 'impression':
        $result = $bannerModel->incrementarImpresiones($bannerId);
        echo json_encode(['success' => $result]);
        break;
        
    case 'click':
        $result = $bannerModel->incrementarClics($bannerId);
        echo json_encode(['success' => $result]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>
