<?php
/**
 * Modelo PaginaInicio
 * Gestión de contenido de página de inicio
 */

class PaginaInicio {
    private $db;
    private $table = 'pagina_inicio';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene elementos por sección
     */
    public function getBySeccion($seccion, $activo = true) {
        $query = "SELECT * FROM {$this->table} WHERE seccion = :seccion";
        
        if ($activo) {
            $query .= " AND activo = 1";
        }
        
        $query .= " ORDER BY orden ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['seccion' => $seccion]);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un elemento por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuevo elemento
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (seccion, titulo, subtitulo, contenido, imagen, url, orden, activo) 
                  VALUES (:seccion, :titulo, :subtitulo, :contenido, :imagen, :url, :orden, :activo)";
        
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            'seccion' => $data['seccion'],
            'titulo' => $data['titulo'] ?? null,
            'subtitulo' => $data['subtitulo'] ?? null,
            'contenido' => $data['contenido'] ?? null,
            'imagen' => $data['imagen'] ?? null,
            'url' => $data['url'] ?? null,
            'orden' => $data['orden'] ?? 0,
            'activo' => $data['activo'] ?? 1
        ]);
    }

    /**
     * Actualiza un elemento
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['seccion', 'titulo', 'subtitulo', 'contenido', 'imagen', 'url', 'orden', 'activo'];
        
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
     * Elimina un elemento
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Obtiene todas las secciones disponibles
     */
    public function getSecciones() {
        $query = "SELECT DISTINCT seccion FROM {$this->table} ORDER BY seccion";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
