<?php
/**
 * Configuración de Datos del Sitio
 */
require_once __DIR__ . '/config/bootstrap.php';
requireAuth();
requirePermission('configuracion');

$configuracionModel = new Configuracion();
$errors = [];
$success = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores = [
        'nombre_sitio' => trim($_POST['nombre_sitio'] ?? ''),
        'slogan_sitio' => trim($_POST['slogan_sitio'] ?? ''),
        'descripcion_sitio' => trim($_POST['descripcion_sitio'] ?? ''),
        'email_sistema' => trim($_POST['email_sistema'] ?? ''),
        'telefono_contacto' => trim($_POST['telefono_contacto'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'zona_horaria' => trim($_POST['zona_horaria'] ?? 'America/Mexico_City'),
        'modo_logo' => trim($_POST['modo_logo'] ?? 'imagen'),
        'tamano_logo' => trim($_POST['tamano_logo'] ?? 'h-10'),
        'texto_footer' => trim($_POST['texto_footer'] ?? ''),
        'aviso_legal' => trim($_POST['aviso_legal'] ?? ''),
        'mostrar_aviso_legal' => isset($_POST['mostrar_aviso_legal']) ? '1' : '0',
        'mostrar_accesos_rapidos' => isset($_POST['mostrar_accesos_rapidos']) ? '1' : '0'
    ];
    
    // Agregar configuraciones del slider si están presentes
    if (isset($_POST['slider_tipo'])) {
        $valores['slider_tipo'] = trim($_POST['slider_tipo']);
    }
    if (isset($_POST['slider_cantidad'])) {
        $valores['slider_cantidad'] = (int)$_POST['slider_cantidad'];
    }
    if (isset($_POST['slider_autoplay'])) {
        $valores['slider_autoplay'] = '1';
    } else if (isset($_POST['slider_tipo'])) {
        // Solo setear a 0 si venimos del formulario de slider
        $valores['slider_autoplay'] = '0';
    }
    if (isset($_POST['slider_intervalo'])) {
        $valores['slider_intervalo'] = (int)$_POST['slider_intervalo'];
    }
    
    // Validaciones
    // Solo validar nombre_sitio si NO viene del formulario de slider
    // Y si viene con datos reales (no solo archivos)
    $soloArchivos = isset($_FILES['logo_footer']) || isset($_FILES['logo']) || isset($_FILES['favicon']);
    $tieneOtrosCampos = !empty($_POST['nombre_sitio']) || !empty($_POST['email_sistema']) || !empty($_POST['slogan_sitio']);
    
    if (empty($valores['nombre_sitio']) && !isset($_POST['slider_tipo']) && $tieneOtrosCampos) {
        $errors[] = 'El nombre del sitio es requerido';
    }
    
    if (!empty($valores['email_sistema']) && !filter_var($valores['email_sistema'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email del sistema no es válido';
    }
    
    // Manejar logo si se sube
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/config/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Formato de logo no permitido';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $errors[] = 'Error al validar el tipo de archivo';
            } else {
                $mimeType = finfo_file($finfo, $_FILES['logo']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = 'Tipo de archivo no válido';
                } else {
                    // Delete old logo if exists
                    if (!empty($config['logo_sitio']['valor'])) {
                        $oldLogoPath = __DIR__ . $config['logo_sitio']['valor'];
                        if (file_exists($oldLogoPath) && is_file($oldLogoPath)) {
                            @unlink($oldLogoPath);
                        }
                    }
                    
                    $filename = 'logo_' . time() . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                        $valores['logo_sitio'] = '/public/uploads/config/' . $filename;
                    } else {
                        $errors[] = 'Error al subir el logo';
                    }
                }
            }
        }
    }
    
    // Manejar logo del footer si se sube
    if (isset($_FILES['logo_footer']) && $_FILES['logo_footer']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/config/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['logo_footer']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Formato de logo del footer no permitido';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $errors[] = 'Error al validar el tipo de archivo';
            } else {
                $mimeType = finfo_file($finfo, $_FILES['logo_footer']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = 'Tipo de archivo no válido';
                } else {
                    // Delete old logo_footer if exists
                    if (!empty($config['logo_footer']['valor'])) {
                        $oldLogoPath = __DIR__ . $config['logo_footer']['valor'];
                        if (file_exists($oldLogoPath) && is_file($oldLogoPath)) {
                            @unlink($oldLogoPath);
                        }
                    }
                    
                    $filename = 'logo_footer_' . time() . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['logo_footer']['tmp_name'], $uploadPath)) {
                        $valores['logo_footer'] = '/public/uploads/config/' . $filename;
                    } else {
                        $errors[] = 'Error al subir el logo del footer';
                    }
                }
            }
        }
    }
    
    // Manejar favicon si se sube
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/public/uploads/config/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['ico', 'png', 'jpg', 'jpeg', 'svg'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Formato de favicon no permitido. Use ICO, PNG, JPG o SVG';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $errors[] = 'Error al validar el tipo de archivo del favicon';
            } else {
                $mimeType = finfo_file($finfo, $_FILES['favicon']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg', 'image/svg+xml'];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    $errors[] = 'Tipo de archivo de favicon no válido';
                } else {
                    // Delete old favicon if exists
                    if (!empty($config['favicon_sitio']['valor'])) {
                        $oldFaviconPath = __DIR__ . $config['favicon_sitio']['valor'];
                        if (file_exists($oldFaviconPath) && is_file($oldFaviconPath)) {
                            if (!unlink($oldFaviconPath)) {
                                error_log("Failed to delete old favicon: " . $oldFaviconPath);
                            }
                        }
                    }
                    
                    $filename = 'favicon_' . time() . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['favicon']['tmp_name'], $uploadPath)) {
                        $valores['favicon_sitio'] = '/public/uploads/config/' . $filename;
                    } else {
                        $errors[] = 'Error al subir el favicon';
                    }
                }
            }
        }
    }
    
    if (empty($errors)) {
        // Guardar valores
        foreach ($valores as $clave => $valor) {
            $configuracionModel->setOrCreate($clave, $valor, 'texto', 'general', '');
        }
        
        // Registrar auditoría
        $logModel = new Log();
        $currentUser = getCurrentUser();
        $logModel->registrarAuditoria(
            $currentUser['id'],
            'configuracion',
            'actualizar',
            'configuracion',
            0,
            null,
            $valores
        );
        
        $success = true;
        setFlash('success', 'Configuración actualizada exitosamente');
        
        // Si viene del formulario de slider, redirigir a página de inicio
        if (isset($_POST['slider_tipo'])) {
            redirect('pagina_inicio.php');
        }
    }
}

// Obtener valores actuales
$config = $configuracionModel->getByGrupo('general');

$title = 'Configuración del Sitio';
ob_start();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-globe mr-2 text-blue-600"></i>
            Datos del Sitio
        </h1>
        <a href="<?php echo url('configuracion.php'); ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Se encontraron errores:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($success || ($flashSuccess = getFlash('success'))): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-800"><?php echo e($flashSuccess ?? 'Configuración guardada exitosamente'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Sitio <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre_sitio" 
                           value="<?php echo e($config['nombre_sitio']['valor'] ?? 'Portal de Noticias'); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Slogan
                    </label>
                    <input type="text" name="slogan_sitio" 
                           value="<?php echo e($config['slogan_sitio']['valor'] ?? ''); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción del Sitio
                </label>
                <textarea name="descripcion_sitio" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($config['descripcion_sitio']['valor'] ?? ''); ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Breve descripción para SEO (160 caracteres máximo recomendado)</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Logo del Sitio</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Modo de Logo
                    </label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center mr-6">
                            <input type="radio" name="modo_logo" value="imagen" 
                                   <?php echo (empty($config['modo_logo']['valor']) || $config['modo_logo']['valor'] === 'imagen') ? 'checked' : ''; ?>
                                   class="form-radio text-blue-600">
                            <span class="ml-2">Mostrar imagen</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="modo_logo" value="texto" 
                                   <?php echo (!empty($config['modo_logo']['valor']) && $config['modo_logo']['valor'] === 'texto') ? 'checked' : ''; ?>
                                   class="form-radio text-blue-600">
                            <span class="ml-2">Mostrar título como texto</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Elige cómo mostrar el logo: como imagen o como el título del sitio en texto
                    </p>
                </div>
                
                <div id="logo-image-section">
                    <?php if (!empty($config['logo_sitio']['valor'])): ?>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Actual</label>
                        <img src="<?php echo e(BASE_URL . $config['logo_sitio']['valor'] . '?v=' . time()); ?>" alt="Logo actual" class="h-16" loading="eager">
                    </div>
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Imagen del Logo
                        </label>
                        <input type="file" name="logo" accept="image/*"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, GIF, SVG, WEBP</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tamaño del Logo
                        </label>
                        <select name="tamano_logo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="h-8" <?php echo (!empty($config['tamano_logo']['valor']) && $config['tamano_logo']['valor'] === 'h-8') ? 'selected' : ''; ?>>Pequeño (32px)</option>
                            <option value="h-10" <?php echo (empty($config['tamano_logo']['valor']) || $config['tamano_logo']['valor'] === 'h-10') ? 'selected' : ''; ?>>Mediano (40px) - Por defecto</option>
                            <option value="h-12" <?php echo (!empty($config['tamano_logo']['valor']) && $config['tamano_logo']['valor'] === 'h-12') ? 'selected' : ''; ?>>Grande (48px)</option>
                            <option value="h-16" <?php echo (!empty($config['tamano_logo']['valor']) && $config['tamano_logo']['valor'] === 'h-16') ? 'selected' : ''; ?>>Extra Grande (64px)</option>
                            <option value="h-20" <?php echo (!empty($config['tamano_logo']['valor']) && $config['tamano_logo']['valor'] === 'h-20') ? 'selected' : ''; ?>>XXL (80px)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Ajusta el tamaño de visualización del logo</p>
                    </div>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const modoLogoRadios = document.querySelectorAll('input[name="modo_logo"]');
                    const logoImageSection = document.getElementById('logo-image-section');
                    
                    function toggleLogoFields() {
                        const modoLogo = document.querySelector('input[name="modo_logo"]:checked').value;
                        if (logoImageSection) {
                            logoImageSection.style.display = (modoLogo === 'texto') ? 'none' : 'block';
                        }
                    }
                    
                    modoLogoRadios.forEach(radio => {
                        radio.addEventListener('change', toggleLogoFields);
                    });
                    
                    toggleLogoFields();
                });
                </script>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Favicon del Sitio</h3>
                
                <?php if (!empty($config['favicon_sitio']['valor'])): ?>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Favicon Actual</label>
                    <img src="<?php echo e(BASE_URL . $config['favicon_sitio']['valor'] . '?v=' . time()); ?>" alt="Favicon actual" class="h-8 border border-gray-300 p-1 bg-white" loading="eager">
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cargar Favicon
                    </label>
                    <input type="file" name="favicon" accept=".ico,.png,.jpg,.jpeg,.svg"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">
                        Formatos permitidos: ICO, PNG, JPG, SVG. Tamaño recomendado: 32x32 o 16x16 píxeles
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Contacto</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email del Sistema
                        </label>
                        <input type="email" name="email_sistema" 
                               value="<?php echo e($config['email_sistema']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Teléfono de Contacto
                        </label>
                        <input type="text" name="telefono_contacto" 
                               value="<?php echo e($config['telefono_contacto']['valor'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección
                    </label>
                    <input type="text" name="direccion" 
                           value="<?php echo e($config['direccion']['valor'] ?? ''); ?>" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pie de Página (Footer)</h3>
                
                <?php if (!empty($config['logo_footer']['valor'])): ?>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo Actual del Footer</label>
                    <img src="<?php echo e(BASE_URL . $config['logo_footer']['valor'] . '?v=' . time()); ?>" alt="Logo footer actual" class="h-12" loading="eager">
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Logo del Footer
                    </label>
                    <input type="file" name="logo_footer" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Logo que se mostrará en el pie de página. Formatos permitidos: JPG, PNG, GIF, SVG, WEBP</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Texto del Footer
                    </label>
                    <textarea name="texto_footer" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="©️ 2026 La Cruda Verdad. Todos los derechos reservados."><?php echo e($config['texto_footer']['valor'] ?? ''); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Texto que aparecerá al final de la página pública</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preferencias de Interfaz</h3>
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="mostrar_accesos_rapidos" id="mostrar_accesos_rapidos" value="1" 
                               <?php echo (!empty($config['mostrar_accesos_rapidos']['valor']) && $config['mostrar_accesos_rapidos']['valor'] === '1') ? 'checked' : ''; ?>
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="mostrar_accesos_rapidos" class="ml-2 block text-sm font-medium text-gray-700">
                            Mostrar bloque de Accesos Rápidos en el sidebar del sitio público
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 ml-6">Desmarcar para ocultar la sección lateral de accesos rápidos</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aviso Legal</h3>
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" name="mostrar_aviso_legal" id="mostrar_aviso_legal" value="1" 
                               <?php echo (!empty($config['mostrar_aviso_legal']['valor']) && $config['mostrar_aviso_legal']['valor'] === '1') ? 'checked' : ''; ?>
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="mostrar_aviso_legal" class="ml-2 block text-sm font-medium text-gray-700">
                            Mostrar enlace de Aviso Legal en el footer
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Contenido del Aviso Legal
                    </label>
                    <textarea name="aviso_legal" rows="8"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                              placeholder="Ingrese el contenido de la página de Aviso Legal..."><?php echo e($config['aviso_legal']['valor'] ?? ''); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Contenido completo de la página de Aviso Legal. Puede usar HTML básico.</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración Técnica</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Zona Horaria
                        </label>
                        <select name="zona_horaria" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="America/Mexico_City" <?php echo ($config['zona_horaria']['valor'] ?? '') === 'America/Mexico_City' ? 'selected' : ''; ?>>
                                Ciudad de México
                            </option>
                            <option value="America/Cancun" <?php echo ($config['zona_horaria']['valor'] ?? '') === 'America/Cancun' ? 'selected' : ''; ?>>
                                Cancún
                            </option>
                            <option value="America/Tijuana" <?php echo ($config['zona_horaria']['valor'] ?? '') === 'America/Tijuana' ? 'selected' : ''; ?>>
                                Tijuana
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="<?php echo url('configuracion.php'); ?>" 
                   class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
