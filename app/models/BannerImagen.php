<?php
/**
 * Modelo BannerImagen
 * Gestión de imágenes adicionales para banners rotativos
 */

class BannerImagen {
    private $db;
    private $table = 'banner_imagenes';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las imágenes de un banner
     */
    public function getByBannerId($bannerId, $activoOnly = true) {
        $query = "SELECT * FROM {$this->table} WHERE banner_id = :banner_id";
        
        if ($activoOnly) {
            $query .= " AND activo = 1";
        }
        
        $query .= " ORDER BY orden ASC, fecha_creacion ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['banner_id' => $bannerId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una imagen por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva imagen de banner
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (banner_id, imagen_url, orden, activo) 
                  VALUES (:banner_id, :imagen_url, :orden, :activo)";
        
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            'banner_id' => $data['banner_id'],
            'imagen_url' => $data['imagen_url'],
            'orden' => $data['orden'] ?? 0,
            'activo' => $data['activo'] ?? 1
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualiza una imagen de banner
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['imagen_url', 'orden', 'activo'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($params);
    }

    /**
     * Elimina una imagen de banner
     */
    public function delete($id) {
        // Obtener la imagen para eliminar el archivo físico
        $imagen = $this->getById($id);
        
        if ($imagen && !empty($imagen['imagen_url'])) {
            // Validar que la ruta de imagen no contiene secuencias peligrosas
            $imagePath = $imagen['imagen_url'];
            if (strpos($imagePath, '..') === false && strpos($imagePath, '/public/uploads/banners/') === 0) {
                $fullPath = __DIR__ . '/../../' . $imagePath;
                
                // Resolver rutas absolutas para validación
                $realFullPath = realpath($fullPath);
                $expectedBasePath = realpath(__DIR__ . '/../../public/uploads/banners');
                
                // Verificar que la ruta real está dentro del directorio permitido
                if ($realFullPath !== false && $expectedBasePath !== false) {
                    if (strpos($realFullPath, $expectedBasePath . DIRECTORY_SEPARATOR) === 0) {
                        if (is_file($realFullPath)) {
                            unlink($realFullPath);
                        }
                    }
                }
            }
        }
        
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Elimina todas las imágenes de un banner
     */
    public function deleteByBannerId($bannerId) {
        // Obtener todas las imágenes para eliminar los archivos físicos
        $imagenes = $this->getByBannerId($bannerId, false);
        
        foreach ($imagenes as $imagen) {
            $this->delete($imagen['id']);
        }
        
        return true;
    }

    /**
     * Cuenta las imágenes de un banner
     */
    public function countByBannerId($bannerId) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} 
                  WHERE banner_id = :banner_id AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['banner_id' => $bannerId]);
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}
