<?php
/**
 * Archivo de Configuración del Sistema
 */

// =====================================================
// CONFIGURACIÓN DE BASE DE DATOS
// =====================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestor_contenidos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// CONFIGURACIÓN GENERAL
// =====================================================
define('SITE_NAME', 'Sistema de Gestión de Contenidos');
define('SITE_VERSION', '1.0.0');

// =====================================================
// ZONA HORARIA
// =====================================================
date_default_timezone_set('America/Mexico_City');

// =====================================================
// CONFIGURACIÓN DE SESIONES
// =====================================================
define('SESSION_LIFETIME', 7200); // 2 horas en segundos
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos en segundos

// =====================================================
// CONFIGURACIÓN DE ARCHIVOS
// =====================================================
define('MAX_FILE_SIZE', 5242880); // 5MB en bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// =====================================================
// ENTORNO DE DESARROLLO
// =====================================================
define('ENVIRONMENT', 'development'); // development o production

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
