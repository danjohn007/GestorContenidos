<?php
/**
 * Modelo Multimedia
 * Gestión de archivos multimedia
 */

class Multimedia {
    private $db;
    private $table = 'multimedia';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los archivos multimedia con filtros
     */
    public function getAll($tipo = null, $carpeta = null, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT m.*, u.nombre as usuario_nombre, u.apellidos as usuario_apellidos
                  FROM {$this->table} m
                  INNER JOIN usuarios u ON m.usuario_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($tipo !== null) {
            $query .= " AND m.tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        
        if ($carpeta !== null) {
            $query .= " AND m.carpeta = :carpeta";
            $params['carpeta'] = $carpeta;
        }
        
        $query .= " ORDER BY m.fecha_subida DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de archivos multimedia
     */
    public function count($tipo = null, $carpeta = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($tipo !== null) {
            $query .= " AND tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        
        if ($carpeta !== null) {
            $query .= " AND carpeta = :carpeta";
            $params['carpeta'] = $carpeta;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'];
    }

    /**
     * Obtiene un archivo por ID
     */
    public function getById($id) {
        $query = "SELECT m.*, u.nombre as usuario_nombre, u.apellidos as usuario_apellidos
                  FROM {$this->table} m
                  INNER JOIN usuarios u ON m.usuario_id = u.id
                  WHERE m.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuevo registro de archivo multimedia
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (nombre, nombre_original, tipo, ruta, carpeta, tamanio, extension, 
                   titulo, descripcion, alt_text, usuario_id) 
                  VALUES (:nombre, :nombre_original, :tipo, :ruta, :carpeta, :tamanio, :extension,
                          :titulo, :descripcion, :alt_text, :usuario_id)";
        
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'nombre_original' => $data['nombre_original'],
            'tipo' => $data['tipo'],
            'ruta' => $data['ruta'],
            'carpeta' => $data['carpeta'] ?? 'general',
            'tamanio' => $data['tamanio'],
            'extension' => $data['extension'],
            'titulo' => $data['titulo'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'usuario_id' => $data['usuario_id']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualiza un archivo multimedia
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['titulo', 'descripcion', 'alt_text', 'carpeta'];
        
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
     * Elimina un archivo multimedia
     */
    public function delete($id) {
        // Primero obtener la ruta del archivo para eliminarlo físicamente
        $archivo = $this->getById($id);
        if (!$archivo) {
            return false;
        }
        
        // Eliminar el archivo físico con validación de ruta
        $baseDir = realpath(__DIR__ . '/../../');
        $rutaCompleta = realpath($baseDir . $archivo['ruta']);
        
        // Verificar que la ruta está dentro del directorio permitido
        if ($rutaCompleta && strpos($rutaCompleta, $baseDir) === 0 && file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
        
        // Eliminar el registro de la base de datos
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Obtiene todas las carpetas únicas
     */
    public function getCarpetas() {
        $query = "SELECT DISTINCT carpeta FROM {$this->table} ORDER BY carpeta";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
