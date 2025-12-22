<?php
/**
 * Página de Logout
 */
require_once __DIR__ . '/config/bootstrap.php';

// Registrar cierre de sesión
if (isAuthenticated()) {
    $usuarioModel = new Usuario();
    $user = getCurrentUser();
    $usuarioModel->logAccess($user['id'], $user['email'], 'logout', 1, 'Cierre de sesión');
}

// Destruir sesión
session_destroy();

// Redirigir a login
redirect('login.php');
