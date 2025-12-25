<?php
/**
 * Bootstrap del Sistema
 * Inicializa configuración, sesiones y autoload
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Regenerar ID de sesión periódicamente para seguridad
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

// Autoload de clases
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../config/',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/controllers/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Función helper para URL base
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    
    // Si está en el directorio raíz, no agregar la ruta
    if ($path === '/' || $path === '\\') {
        $path = '';
    }
    
    return $protocol . '://' . $host . $path;
}

// Definir constante de URL base
if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseUrl());
}

// Función helper para rutas de assets
function asset($path) {
    return BASE_URL . '/public/' . ltrim($path, '/');
}

// Función helper para URLs
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Función helper para redirección
function redirect($path = '') {
    header('Location: ' . url($path));
    exit;
}

// Función helper para escape HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Función helper para obtener sesión
function getSession($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

// Función helper para establecer sesión
function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

// Función helper para eliminar sesión
function unsetSession($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

// Función helper para sanitizar HTML de contenido
// Note: For enhanced security in high-risk environments, consider using HTML Purifier library
// Current implementation provides good protection against common XSS attacks
function sanitizeHtml($html) {
    // Lista de etiquetas permitidas sin atributos peligrosos
    $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><table><thead><tbody><tr><th><td><div><span>';
    
    // Primero, remover todas las etiquetas no permitidas
    $html = strip_tags($html, $allowedTags);
    
    // Remover atributos potencialmente peligrosos usando regex
    // Remover eventos onclick, onerror, onload, etc.
    $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
    
    // Remover javascript: en href y src
    $html = preg_replace('/(href|src)\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);
    
    // Remover data: URLs que podrían ser peligrosas
    $html = preg_replace('/(href|src)\s*=\s*["\']data:[^"\']*["\']/i', '', $html);
    
    return $html;
}

// Función helper para sanitizar HTML simple (solo para contacto)
function sanitizeSimpleHtml($html) {
    $allowedTags = '<br><strong><em><a>';
    $html = strip_tags($html, $allowedTags);
    
    // Remover atributos peligrosos de enlaces
    $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
    $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);
    
    return $html;
}

// Función helper para flash messages
function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

// Función helper para verificar autenticación
function isAuthenticated() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Función helper para obtener usuario actual
function getCurrentUser() {
    if (isAuthenticated()) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
            'rol' => $_SESSION['usuario_rol'] ?? '',
            'rol_id' => $_SESSION['usuario_rol_id'] ?? null,
        ];
    }
    return null;
}

// Función helper para convertir hex a RGB
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    ];
}

// Función helper para obtener string RGB desde hex
function hexToRgbString($hex) {
    $rgb = hexToRgb($hex);
    return $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'];
}

// Función helper para verificar permisos
function hasPermission($permission) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $permisos = json_decode($_SESSION['usuario_permisos'] ?? '[]', true);
    
    // Super admin tiene todos los permisos
    if (in_array('all', $permisos)) {
        return true;
    }
    
    return in_array($permission, $permisos);
}

// Middleware para requerir autenticación
function requireAuth() {
    if (!isAuthenticated()) {
        redirect('login.php');
    }
}

// Middleware para requerir permiso específico
function requirePermission($permission) {
    requireAuth();
    if (!hasPermission($permission)) {
        setFlash('error', 'No tienes permisos para acceder a esta sección');
        redirect('index.php');
    }
}
